"""Build a manual cPanel installation ZIP from tracked application files."""
from pathlib import Path
import argparse
import stat
import subprocess
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parents[1]
PRIVATE = Path("malwareguard_app")
PUBLIC = Path("public_html/malwareguard")


def package(output):
    listing = subprocess.check_output(
        ["git", "ls-tree", "-r", "--name-only", "HEAD", "--",
         "app", "bin", "config/example.php", "database", "public", "tests"],
        cwd=ROOT, text=True,
    )
    with ZipFile(output, "w") as archive:
        for name in listing.splitlines():
            path = Path(name)
            public = path.parts[0] == "public"
            target = PUBLIC / path.relative_to("public") if public else PRIVATE / path
            data = subprocess.check_output(["git", "show", f"HEAD:{name}"], cwd=ROOT)
            if name == "public/index.php":
                data = b"<?php\ndeclare(strict_types=1);\n\nrequire dirname(__DIR__, 2) . '/malwareguard_app/app/Http/workspace.php';\n"
            member = ZipInfo(str(target))
            member.create_system = 3
            member.external_attr = (stat.S_IFREG | (0o644 if public else 0o600)) << 16
            archive.writestr(member, data, compress_type=ZIP_DEFLATED)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("output", type=Path)
    package(parser.parse_args().output)
