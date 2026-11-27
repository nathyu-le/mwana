"""Publish the Git tree to the existing private project and its cPanel document root."""
import filecmp
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile


def install(source, target, mode):
    target.parent.mkdir(parents=True, exist_ok=True)
    fd, name = tempfile.mkstemp(dir=str(target.parent), prefix=".deploy-")
    os.close(fd)
    try:
        shutil.copyfile(str(source), name)
        os.chmod(name, mode)
        os.replace(name, str(target))
    finally:
        if os.path.exists(name):
            os.unlink(name)


def publish(site, stage, revision):
    project = site / "malwareguard_app"
    web = site / "public_html/malwareguard"
    link = project / "public"
    if link.is_symlink():
        if link.resolve() != web.resolve():
            raise RuntimeError("Unexpected project/public target")
    elif link.exists():
        raise RuntimeError("Existing project/public needs review")
    else:
        link.symlink_to(web, target_is_directory=True)

    state = site / ".mg-deploy"
    files = sorted(p.relative_to(stage) for p in stage.rglob("*") if p.is_file())
    # Install the public entry point last. Old PHP requests can finish in their old release.
    index = Path("public/index.php")
    files.remove(index)
    files.append(index)
    before = []
    with tempfile.TemporaryDirectory(dir=str(state)) as temp:
        backup = Path(temp)
        try:
            for path in files:
                src, dst = stage / path, project / path
                if dst.is_symlink():
                    raise RuntimeError("Unexpected file symlink: " + str(path))
                if dst.is_file() and filecmp.cmp(str(src), str(dst), shallow=False):
                    continue
                prior_mode = None
                if dst.is_file():
                    previous = backup / path
                    previous.parent.mkdir(parents=True, exist_ok=True)
                    shutil.copy2(str(dst), str(previous))
                    prior_mode = dst.stat().st_mode & 0o777
                if path.parts[0] == "public":
                    folder = dst.parent
                    while folder != link:
                        folder.mkdir(mode=0o755, parents=True, exist_ok=True)
                        folder.chmod(0o755)
                        folder = folder.parent
                    mode = 0o644
                else:
                    mode = 0o700 if src.stat().st_mode & 0o111 else 0o600
                install(src, dst, mode)
                before.append((path, prior_mode))

            def get(url):
                return subprocess.check_output(["curl", "-fsS", "--max-time", "25", url])

            base = "https://thitruongmmo.com/"
            for path in files:
                if path.parts[:2] == ("public", "assets"):
                    live = get(base + path.relative_to("public").as_posix() + "?v=" + revision)
                    if live != (stage / path).read_bytes():
                        raise RuntimeError("Asset mismatch: " + str(path))
            login = get(base + "?release=" + revision)
            if b'assets/css/base.css' not in login or b'autocomplete="current-password"' not in login:
                raise RuntimeError("Login verification failed")
            for path in files:
                if not filecmp.cmp(str(project / path), str(stage / path), shallow=False):
                    raise RuntimeError("Git tree differs: " + str(path))
        except BaseException:
            for path, mode in reversed(before):
                dst = project / path
                if mode is None:
                    dst.unlink()
                else:
                    install(backup / path, dst, mode)
            raise
    manifest = state / "published-files"
    old = manifest.read_text().splitlines() if manifest.exists() else []
    current = {p.as_posix() for p in files}
    # Preserve a recoverable private copy of retired tracked paths.
    for name in set(old) - current:
        path = Path(name)
        if path.is_absolute() or ".." in path.parts or name == "config/local.php":
            raise RuntimeError("Invalid previous manifest")
        dst = project / path
        if dst.is_file() and not dst.is_symlink():
            retired = state / "retired" / path
            retired.parent.mkdir(exist_ok=True, parents=True)
            os.replace(str(dst), str(retired))
    next_manifest = state / "published-files.new"
    next_manifest.write_text("\n".join(sorted(current)) + "\n")
    os.replace(str(next_manifest), str(manifest))


if __name__ == "__main__":
    publish(Path(sys.argv[1]), Path(sys.argv[2]), sys.argv[3])
