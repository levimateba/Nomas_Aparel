# Nomas Apparel — System Update Documentation

**Product:** Nomas Apparel Admin & POS  
**Live site:** https://nomas.elgontech.co.ke  
**Update period:** September 2026  
**Audience:** Owners, managers, cashiers, and support staff  

This document summarises the latest system updates: what changed, how to use the new behaviour, and what to verify after go-live.

---

## 1. Summary

| Area | What improved |
|------|----------------|
| **Product catalog** | Brands & product types inline, clearer variants/pricing, live product search, safer edit form |
| **POS / Scan & Sell** | Live search, alias/SKU/barcode/size search, dark mode, scan-to-cart |
| **Admin UI** | Global page search (⌘K), dark mode polish, login autofill fix |
| **Staff app (PWA)** | Install prompt restored; compact card on desktop |

---

## 2. Product catalog & stock

### 2.1 Brands
- Create a brand **from the product form** without leaving the page.
- Brand dropdown lists available brands for selection.

### 2.2 Product Types / Styles
- New catalog item: **Stock & Catalog → Product Types**.
- Examples: T-Shirt, Polo, Jeans, etc.
- Can also be added inline on the Add/Edit Product form.

### 2.3 Pricing & variants
- **Pricing** sits above **Variants**.
- Clear **No / Yes** choice for variants, with generate steps.
- **Apply Pricing to all variants** available when variants are on.
- When variants are enabled, **opening stock** on the parent product is disabled (stock is managed per variant).

### 2.4 Images
- Main product image, then **additional images**.
- **Paste image URL** accepts both `https://…` links and `/storage/…` paths.
- Relative storage paths no longer block **Update Product**.

### 2.5 Product list search
- Search updates as you type (name, brand, SKU, barcode, alias keywords, etc.).
- Cursor stays in the search box so you can keep typing.

### 2.6 Edit Product stability
- **Update Product** no longer risked deleting the product (nested delete form issue fixed).
- Delete remains a separate action outside the main edit form.

---

## 3. Point of Sale (POS) & Scan & Sell

### 3.1 Opening POS
| Action | Shortcut / path |
|--------|------------------|
| POS Terminal | Sidebar **POS Terminal (F2)** or **F2** |
| Scan & Sell | Sidebar **Scan & Sell** or **F2** from POS header flow |
| Focus search | **⌘K** / **Ctrl+K** (POS search) |

### 3.2 Searching products
Typing in the POS search box **filters live** (no need to click Search).

Search matches:
- Product name  
- SKU  
- Barcode (including extra barcodes)  
- **Product Alias / search keywords**  
- Brand, category, shelf/lot (where used)  
- Variant name / SKU / barcode  
- Size & colour attribute values (e.g. **XS**, **XL**, **Blue**)

### 3.3 Size searches (XS, S, M, L, XL…)
- Typing a size **lists products that have that size**.
- It does **not** auto-add a parent product just because the name/SKU contains that size (e.g. “XL Charcoal Business Suit”).
- Matching variants are marked (★) and auto-selected when only one match exists.

### 3.4 Scanning barcode / QR
- Point the scanner at the search field (or use Scan & Sell).
- Exact barcode / SKU / alias token / variant barcode **adds straight to Current Sale**.
- Products with variants still need a **variant barcode** or a chosen size/colour.

### 3.5 Dark mode (POS)
- POS / Scan & Sell follows the same theme as admin (`localStorage` theme).
- Moon/sun toggle in the POS header.
- Current Sale fields (including cash **KES** amount) stay readable in dark mode.

---

## 4. Admin dashboard & navigation

### 4.1 Global search (header)
- Placeholder: **Search pages, products, SKU…**
- Shortcut: **⌘K** / **Ctrl+K**
- Type a letter (e.g. **D**) to see matching **pages** (Dashboard, etc.).
- Arrow keys + Enter, or click a result.
- Option at the bottom: **Search products for “…”** → opens Products list.

Typed text is visible in **dark mode**.

### 4.2 Dark mode elsewhere
- Product forms, suppliers, users, dashboard tiles, backups reminder, and related screens updated for dark mode readability.

### 4.3 Login
- If Sign In fails (wrong autofill password), **email and password stay filled** so you can edit and retry.
- Password field is focused after a failed attempt.

---

## 5. Install app (PWA)

Staff can install **Nomas Apparel POS** on a phone/tablet/desktop browser.

| Device | Install prompt |
|--------|----------------|
| Phone / tablet | Wider bottom banner |
| Laptop / desktop | Small card, **bottom-right** |

- **Install** — browser install dialog (or Share → Add to Home Screen on iPhone).  
- **Not now** — hides the prompt for 14 days.  
- Already installed (standalone) — prompt does not show.

After icon/branding changes: uninstall the old shortcut, reopen the site, install again.

---

## 6. Cashier quick reference (updated)

1. Open a **Cashier Shift** if required.  
2. Open **POS** or **Scan & Sell**.  
3. Scan barcode **or** type name / SKU / alias / size.  
4. For variants, pick size/colour (or scan variant barcode).  
5. Complete sale (Cash / Card / M-Pesa / Other).  
6. Print receipt as needed.  
7. Close shift at end of day.

See also: `docs/CASHIER_TRAINING.md` and in-app **Cashier Training**.

---

## 7. Permissions reminder

New or relevant screens still respect existing roles, for example:
- Products / Product Types / Brands → product management  
- POS / Scan → create sale  
- Reports, users, settings → their existing permissions  

If a menu item is missing, check the user’s role under **Administration → Roles / Users**.

---

## 8. Go-live checklist

- [ ] Hard-refresh admin and POS after deploy (clear old CSS/views).  
- [ ] Dark mode: product form, POS Current Sale, global search text readable.  
- [ ] Global search: type `D` → Dashboard appears; Enter opens it.  
- [ ] Products list: live search by SKU / alias.  
- [ ] POS: type `XL` → filters sizes, does not wrongly auto-add.  
- [ ] POS: scan a real barcode → line appears in Current Sale.  
- [ ] Edit product with `/storage/...` image path → Update succeeds.  
- [ ] Failed login → email/password remain editable.  
- [ ] Desktop: install prompt is a small bottom-right card.  
- [ ] Mobile: install prompt still usable above bottom nav.

---

## 9. Support notes

| Issue | Likely fix |
|-------|------------|
| Old layout / missing styles | Hard refresh; clear compiled views on server |
| Install prompt never shows | Already installed, or dismissed within 14 days; use Chrome/Edge/Safari |
| Size search adds wrong product | Confirm latest POS search is deployed; sizes should only filter |
| Variant required message | Scan variant barcode or choose size/colour before adding |
| 500 after deploy | Check `storage/logs/laravel-*.log`; clear views/config cache |

**Hosting:** Truhost FTP (`nomas.elgontech.co.ke`). Prefer targeted file upload + `view:clear` after Blade/CSS changes. See `TRUHOST_DEPLOYMENT.md`.

---

## 10. Document control

| Field | Value |
|-------|--------|
| Document | System Update — September 2026 |
| System | Nomas Apparel Admin & POS |
| Prepared for | Operational handover / staff briefing |
| Related docs | `docs/CASHIER_TRAINING.md`, `docs/Nomas-Apparel-User-Manual.pdf`, `TRUHOST_DEPLOYMENT.md` |

---

*End of update documentation.*
