#!/usr/bin/env python3
"""Sync Nomas Apparel to Truhost via FTP (preserves server .env)."""
import ftplib
import os
import sys
from pathlib import Path

HOST = "102.212.247.98"
USER = "dgezuyer"
# Password passed via env to avoid leaving in repo
PASS = os.environ.get("TRUHOST_FTP_PASS", "")
REMOTE_BASE = "/nomas.elgontech.co.ke"
LOCAL_ROOT = Path(__file__).resolve().parent.parent

SKIP_DIRS = {
    ".git", "node_modules", "tests", "agent-tools", "agent-transcripts",
    ".cursor", "vendor",  # lock unchanged — skip large vendor upload
    "dist",  # legacy admin assets already on server (~28MB)
    "cache",  # never overwrite bootstrap/cache from local machine
}
SKIP_FILES = {
    ".env", "nomas-apparel-deploy.zip", ".DS_Store",
    "database.sqlite", "database.sqlite-journal",
}
UPLOAD_PATHS = [
    "app", "bootstrap", "config", "database", "resources", "routes", "scripts",
    "public/build", "public/css", "public/pwa", "public/docs", "public/images",
    "public/.htaccess", "public/deploy-run.php",
    "artisan", "composer.json", "composer.lock",
    "TRUHOST_DEPLOYMENT.md", ".env.production.example", "deploy.sh",
]


def ensure_dir(ftp: ftplib.FTP, path: str) -> None:
    parts = [p for p in path.strip("/").split("/") if p]
    current = ""
    for part in parts:
        current += "/" + part
        try:
            ftp.cwd(current)
        except ftplib.error_perm:
            ftp.mkd(current)


def upload_file(ftp: ftplib.FTP, local: Path, remote: str) -> None:
    ensure_dir(ftp, os.path.dirname(remote))
    with open(local, "rb") as f:
        ftp.storbinary(f"STOR {remote}", f)
    print(f"  uploaded {remote}")


def upload_tree(ftp: ftplib.FTP, local_dir: Path, remote_dir: str) -> int:
    count = 0
    for root, dirs, files in os.walk(local_dir):
        dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
        rel = Path(root).relative_to(local_dir)
        remote_sub = remote_dir if str(rel) == "." else f"{remote_dir}/{rel}".replace("\\", "/")
        for name in files:
            if name in SKIP_FILES:
                continue
            local_file = Path(root) / name
            remote_file = f"{remote_sub}/{name}".replace("//", "/")
            upload_file(ftp, local_file, remote_file)
            count += 1
    return count


def main() -> int:
    if not PASS:
        print("Set TRUHOST_FTP_PASS environment variable", file=sys.stderr)
        return 1

    ftp = ftplib.FTP()
    ftp.connect(HOST, 21, timeout=30)
    ftp.login(USER, PASS)
    print(f"Connected. Syncing to {REMOTE_BASE}")

    total = 0
    for item in UPLOAD_PATHS:
        local = LOCAL_ROOT / item
        remote = f"{REMOTE_BASE}/{item}".replace("\\", "/")
        if not local.exists():
            print(f"skip missing {item}")
            continue
        if local.is_dir():
            print(f"dir {item}/")
            total += upload_tree(ftp, local, remote)
        else:
            upload_file(ftp, local, remote)
            total += 1

    ftp.quit()
    print(f"\nUpload complete: {total} files")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
