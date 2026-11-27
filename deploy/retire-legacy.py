"""Archive the v1.1/v1.2 layout after verifying the canonical live tree."""
import filecmp
import os
from pathlib import Path
import re
import shutil
import subprocess
import tarfile
import tempfile

site = Path.home() / "thitruongmmo.com"
project = site / "malwareguard_app"
web = site / "public_html/malwareguard"
state = site / ".mg-deploy"
repo = state / "source"
if not (state / "mirrored").is_file() or not (project / "public").samefile(web):
    raise SystemExit("Deploy the canonical layout before retiring legacy files")
if not filecmp.cmp(str(web / "index.php"), str(repo / "public/index.php"), shallow=False):
    raise SystemExit("Update the private checkout to the deployed revision first")

old = "4859b8622f93db49d4f6648e9b7c676c44aef8ba"
def tracked(rev, path):
    return subprocess.check_output(["git", "-C", str(repo), "show", rev + ":" + path])

retire = []
for name in ("app.css", "scanner.js", "startup.js", "mark.svg"):
    path = web / name
    if path.is_file() and not path.is_symlink() and path.read_bytes() == tracked(old, "public/" + name):
        retire.append(path)

for name in ("admin.php", "history.php", "report.php", "scan-art.php"):
    relative = "app/Views/" + name
    path = project / relative
    if path.is_file() and not path.is_symlink() and path.read_bytes() == tracked(old, relative):
        retire.append(path)

# A legacy release has a full Git SHA for its name. Compare all its regular
# files with the exact corresponding tree before retiring it.
for parent in (web / "assets", state / "releases"):
    if not parent.is_dir():
        continue
    for folder in parent.iterdir():
        if folder.is_symlink() or not folder.is_dir() or not re.fullmatch("[0-9a-f]{40}", folder.name):
            continue
        prefix = "public/assets/" if parent == web / "assets" else ""
        result = subprocess.run(["git", "-C", str(repo), "ls-tree", "-rz", "--name-only",
                                 folder.name, "--", prefix or "."], stdout=subprocess.PIPE)
        if result.returncode:
            continue
        paths = {name.decode()[len(prefix):] for name in result.stdout.split(b"\0") if name}
        actual = set()
        safe = True
        for path in folder.rglob("*"):
            name = path.relative_to(folder).as_posix()
            if path.is_symlink():
                if prefix or name != "config/local.php" or path.resolve() != (project / name).resolve():
                    safe = False
            elif path.is_file():
                actual.add(name)
                if name not in paths or path.read_bytes() != tracked(folder.name, prefix + name):
                    safe = False
        if safe and paths == actual:
            retire.append(folder)
        else:
            print("Preserved modified directory", folder)
if retire:
    # Keep one private compressed recovery copy, verify file contents, then remove duplicates.
    backups = state / "backups"
    backups.mkdir(mode=0o700, exist_ok=True)
    fd, archive = tempfile.mkstemp(dir=str(backups), prefix="legacy-layout-", suffix=".tar.gz")
    os.close(fd)
    with tarfile.open(archive, "w:gz") as tar:
        for path in retire:
            tar.add(str(path), arcname=path.relative_to(site).as_posix())
    with tarfile.open(archive, "r:gz") as tar:
        for entry in tar:
            path = site / entry.name
            if entry.isfile() and tar.extractfile(entry).read() != path.read_bytes():
                raise RuntimeError("Backup verification failed")
    for path in retire:
        if path.is_dir():
            shutil.rmtree(str(path))
        else:
            path.unlink()
        print("Archived", path.relative_to(site))
