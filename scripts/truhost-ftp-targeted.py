#!/usr/bin/env python3
"""Targeted FTP upload of changed Nomas files."""
import ftplib
import os
import sys
import time
from pathlib import Path

HOST = "102.212.247.98"
USER = "dgezuyer"
PASS = os.environ.get("TRUHOST_FTP_PASS", "")
REMOTE_BASE = "/nomas.elgontech.co.ke"
ROOT = Path(__file__).resolve().parent.parent

FILES = [
    # M-PESA
    "app/Http/Controllers/MpesaController.php",
    "app/Models/MpesaTransaction.php",
    "app/Services/Mpesa/DarajaService.php",
    "app/Services/Pos/PosSaleCompleter.php",
    "config/mpesa.php",
    "database/migrations/2026_09_19_120000_create_mpesa_transactions_table.php",
    "docs/mpesa-integration.md",
    # POS / orders
    "app/Http/Controllers/Admin/PosController.php",
    "app/Models/Order.php",
    "resources/views/admin/pos/index.blade.php",
    "resources/views/layouts/pos.blade.php",
    # Catalog / admin
    "app/Http/Controllers/Admin/AuthController.php",
    "app/Http/Controllers/Admin/BrandController.php",
    "app/Http/Controllers/Admin/ProductController.php",
    "app/Http/Controllers/Admin/ProductStyleController.php",
    "app/Models/Product.php",
    "config/admin_permissions.php",
    "config/pwa.php",
    ".env.example",
    "database/seeders/ApparelCatalogSeeder.php",
    "resources/css/admin.css",
    "resources/views/admin/audit-logs/index.blade.php",
    "resources/views/admin/auth/login.blade.php",
    "resources/views/admin/brands/index.blade.php",
    "resources/views/admin/cashier/home.blade.php",
    "resources/views/admin/dashboard.blade.php",
    "resources/views/admin/partials/backup-reminder.blade.php",
    "resources/views/admin/partials/tailadmin/header.blade.php",
    "resources/views/admin/partials/tailadmin/nav-groups.blade.php",
    "resources/views/admin/product-styles/index.blade.php",
    "resources/views/admin/products/_apparel_sections.blade.php",
    "resources/views/admin/products/_location_stock.blade.php",
    "resources/views/admin/products/_pricing_section.blade.php",
    "resources/views/admin/products/create.blade.php",
    "resources/views/admin/products/edit.blade.php",
    "resources/views/admin/products/index.blade.php",
    "resources/views/admin/products/partials/form.blade.php",
    "resources/views/admin/suppliers/form.blade.php",
    "resources/views/admin/user/create.blade.php",
    "resources/views/admin/user/edit.blade.php",
    "resources/views/layouts/admin.blade.php",
    "resources/views/layouts/frontend.blade.php",
    "resources/views/partials/pwa-install.blade.php",
    "routes/web.php",
    "docs/SYSTEM_UPDATE_2026-09.md",
    "public/product-lookup.php",
]


def connect():
    ftp = ftplib.FTP()
    ftp.connect(HOST, 21, timeout=60)
    ftp.login(USER, PASS)
    ftp.set_pasv(True)
    return ftp


def ensure_dir(ftp, path: str) -> None:
    parts = [p for p in path.strip("/").split("/") if p]
    cur = ""
    for part in parts:
        cur += "/" + part
        try:
            ftp.cwd(cur)
        except ftplib.error_perm:
            try:
                ftp.mkd(cur)
            except ftplib.error_perm:
                pass


def upload(ftp, local: Path, remote: str, retries: int = 4):
    for attempt in range(1, retries + 1):
        try:
            ensure_dir(ftp, os.path.dirname(remote))
            with open(local, "rb") as f:
                ftp.storbinary(f"STOR {remote}", f, blocksize=128 * 1024)
            print(f"  ok {remote}", flush=True)
            return ftp
        except Exception as e:
            print(f"  retry {attempt}/{retries} {remote}: {e}", flush=True)
            time.sleep(1.5 * attempt)
            try:
                ftp.close()
            except Exception:
                pass
            ftp = connect()
    raise RuntimeError(f"Failed to upload {remote}")


def main() -> int:
    if not PASS:
        print("Set TRUHOST_FTP_PASS environment variable", file=sys.stderr)
        return 1

    # Also upload built Vite assets if present
    build_dir = ROOT / "public" / "build"
    extra = []
    if build_dir.is_dir():
        for p in build_dir.rglob("*"):
            if p.is_file():
                extra.append(str(p.relative_to(ROOT)))

    files = FILES + extra
    ftp = connect()
    print(f"Connected. Uploading {len(files)} files to {REMOTE_BASE}")
    try:
        for rel in files:
            local = ROOT / rel
            if not local.is_file():
                print(f"  skip missing {rel}", flush=True)
                continue
            remote = f"{REMOTE_BASE}/{rel}".replace("\\", "/")
            ftp = upload(ftp, local, remote)
    finally:
        try:
            ftp.close()
        except Exception:
            pass
    print("Done.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
