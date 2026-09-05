<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nomas Apparel — System User Manual</title>
    <style>
        @page { margin: 28px 32px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #1f2937;
        }
        h1 { font-size: 22px; color: #121212; margin: 0 0 8px; }
        h2 {
            font-size: 15px;
            color: #121212;
            margin: 22px 0 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #d4af37;
            page-break-after: avoid;
        }
        h3 {
            font-size: 12.5px;
            color: #111827;
            margin: 14px 0 6px;
            page-break-after: avoid;
        }
        p { margin: 0 0 8px; }
        ul, ol { margin: 0 0 10px 18px; padding: 0; }
        li { margin-bottom: 4px; }
        .cover {
            text-align: center;
            padding: 80px 20px 40px;
        }
        .cover .badge {
            display: inline-block;
            background: #121212;
            color: #d4af37;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }
        .cover h1 { font-size: 28px; margin-bottom: 6px; }
        .cover .sub { color: #6b7280; font-size: 13px; margin-bottom: 28px; }
        .cover .meta {
            margin-top: 40px;
            font-size: 11px;
            color: #4b5563;
        }
        .gold { color: #b8942d; }
        .box {
            border: 1px solid #e5e7eb;
            background: #fafafa;
            padding: 10px 12px;
            margin: 8px 0 12px;
            border-radius: 4px;
        }
        .note {
            border-left: 3px solid #d4af37;
            background: #fffbeb;
            padding: 8px 10px;
            margin: 8px 0 12px;
        }
        .warn {
            border-left: 3px solid #dc2626;
            background: #fef2f2;
            padding: 8px 10px;
            margin: 8px 0 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 14px;
            font-size: 10.5px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th { background: #121212; color: #d4af37; font-weight: bold; }
        tr:nth-child(even) td { background: #f9fafb; }
        .toc a { color: #111827; text-decoration: none; }
        .page-break { page-break-before: always; }
        .footer-note { color: #9ca3af; font-size: 9px; margin-top: 18px; }
        .step-num {
            display: inline-block;
            width: 16px;
            height: 16px;
            background: #d4af37;
            color: #121212;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border-radius: 50%;
            margin-right: 4px;
        }
        code, .mono {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10px;
            background: #f3f4f6;
            padding: 1px 4px;
        }
    </style>
</head>
<body>

{{-- COVER --}}
<div class="cover">
    <div class="badge">NOMAS APPAREL</div>
    <h1>System User Manual</h1>
    <div class="sub">Admin · Manager · Cashier · Stock Manager · Customer</div>
    <p>Complete guide to the Nomas Apparel online store, Point of Sale (POS), and back-office operations.</p>
    <div class="meta">
        <div><strong>Website:</strong> https://nomas.elgontech.co.ke</div>
        <div><strong>Admin / POS:</strong> https://nomas.elgontech.co.ke/admin</div>
        <div><strong>Version:</strong> 1.0 · {{ now()->format('d M Y') }}</div>
    </div>
</div>

<div class="page-break"></div>

{{-- TOC --}}
<h2>Table of Contents</h2>
<ol class="toc">
    <li>Introduction &amp; System Overview</li>
    <li>Roles &amp; Access Levels</li>
    <li>Getting Started (Staff Login)</li>
    <li>Admin / Super Admin Guide</li>
    <li>Manager Guide</li>
    <li>Cashier Guide (POS)</li>
    <li>Stock Manager Guide</li>
    <li>Customer (Online Shop) Guide</li>
    <li>Orders, Returns &amp; Cancellations</li>
    <li>Reports &amp; Shifts</li>
    <li>Tips, Security &amp; Troubleshooting</li>
</ol>

{{-- 1 --}}
<h2>1. Introduction &amp; System Overview</h2>
<p>Nomas Apparel is an integrated retail platform with:</p>
<ul>
    <li><strong>Online storefront</strong> — customers browse, wishlist, cart, checkout, track orders, leave reviews.</li>
    <li><strong>Admin dashboard</strong> — manage products, categories, orders, users, content, settings, and reports.</li>
    <li><strong>Point of Sale (POS)</strong> — in-store sales on phone/tablet/desktop, with receipts and WhatsApp sharing.</li>
    <li><strong>Inventory tools</strong> — stock levels, stock takes, vendors, coupons, and returns.</li>
</ul>
<div class="box">
    <strong>Live URLs</strong><br>
    Store: <span class="mono">https://nomas.elgontech.co.ke</span><br>
    Staff login: <span class="mono">https://nomas.elgontech.co.ke/admin/login</span>
</div>

{{-- 2 --}}
<h2>2. Roles &amp; Access Levels</h2>
<table>
    <thead>
        <tr>
            <th style="width:22%">Role</th>
            <th>What they can do</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Super Admin</strong></td>
            <td>Full access to every module, including users, roles, permissions, backups, and system settings.</td>
        </tr>
        <tr>
            <td><strong>Admin</strong></td>
            <td>Full store operations and user administration (create/edit staff, assign roles, manage settings).</td>
        </tr>
        <tr>
            <td><strong>Manager</strong></td>
            <td>Day-to-day store operations: POS, products, stock, reports, coupons, vendors. Limited user/role changes. No database restore.</td>
        </tr>
        <tr>
            <td><strong>Cashier</strong></td>
            <td>POS sales, view sales/orders, process returns, open/close cashier shifts. Dashboard access.</td>
        </tr>
        <tr>
            <td><strong>Stock Manager</strong></td>
            <td>Products, categories, stock takes, and inventory-related reports.</td>
        </tr>
        <tr>
            <td><strong>Customer (User)</strong></td>
            <td>Shop online, wishlist, checkout (login required), My Orders, cancel/return requests, reviews, Q&amp;A.</td>
        </tr>
    </tbody>
</table>
<div class="note">
    <strong>Note:</strong> Menus you see depend on your role. If a button or page is missing, ask an Admin to check your permissions.
</div>

{{-- 3 --}}
<div class="page-break"></div>
<h2>3. Getting Started (Staff Login)</h2>
<ol>
    <li>Open <span class="mono">https://nomas.elgontech.co.ke/admin/login</span></li>
    <li>Enter your staff <strong>email</strong> and <strong>password</strong>.</li>
    <li>You land on the <strong>Dashboard</strong> (or POS, depending on role).</li>
    <li>Use the sidebar (or mobile menu) to open modules.</li>
    <li>Tap your <strong>avatar</strong> (top right) for profile or logout.</li>
</ol>
<div class="box">
    <strong>Install as an App (recommended for cashiers)</strong><br>
    On Android Chrome, open the admin/POS site and use <em>Install app</em> / Add to Home Screen.
    The launcher icon uses the Nomas logo. Uninstall and reinstall if you still see an old placeholder icon.
</div>
<h3>Update your profile</h3>
<ol>
    <li>Open <strong>My profile</strong> from the avatar menu.</li>
    <li>Update name or password.</li>
    <li>Save changes.</li>
</ol>

{{-- 4 --}}
<h2>4. Admin / Super Admin Guide</h2>
<p>Admins configure the store, catalogue, staff, and online content.</p>

<h3>4.1 Dashboard</h3>
<ul>
    <li>Quick actions (Add Product, Sales History, POS, etc.)</li>
    <li>KPIs: orders, revenue signals, stock alerts, blog/contacts</li>
    <li>Recent orders and low-stock products</li>
</ul>

<h3>4.2 Products &amp; Categories</h3>
<ol>
    <li>Go to <strong>Products</strong> → <strong>Add Product</strong>.</li>
    <li>Enter name, price, sale price (optional), stock, SKU/barcode, category, image, description.</li>
    <li>Save. Product appears in the online shop and POS.</li>
    <li>Use <strong>Categories</strong> to organise products (Suits, Shoes, Bags, etc.).</li>
    <li>Use bulk update / CSV export tools when available for mass changes.</li>
</ol>
<div class="note">Keep SKU/barcode accurate — cashiers scan these in POS.</div>

<h3>4.3 Orders (Online &amp; POS)</h3>
<ol>
    <li>Open <strong>Orders</strong> to see all sales.</li>
    <li>Filter by status (pending, paid, shipped, delivered, cancellation/return requested, cancelled).</li>
    <li>Open an order to update status, view items, invoice/email options, and customer notes.</li>
    <li>Watch for <strong>Cancellation requested</strong> and <strong>Return requested</strong> banners from customers.</li>
</ol>

<h3>4.4 Coupons</h3>
<ol>
    <li>Open <strong>Coupons</strong> → create a code, discount type/amount, and validity rules.</li>
    <li>Customers can apply codes at online checkout; cashiers can apply them in POS.</li>
</ol>

<h3>4.5 Vendors &amp; Payouts</h3>
<ul>
    <li>Manage supplier/vendor records under <strong>Vendors</strong>.</li>
    <li>Record and mark vendor payouts; export CSV when needed.</li>
</ul>

<h3>4.6 Content &amp; Storefront</h3>
<ul>
    <li><strong>Blog</strong> — publish posts customers can read/comment on.</li>
    <li><strong>Reviews / Questions</strong> — approve or remove product reviews and Q&amp;A.</li>
    <li><strong>Settings</strong> — site name, logo, tagline, colours, favicon.</li>
    <li><strong>Contacts / About / Services</strong> — store contact details and site content.</li>
    <li><strong>Enquiries &amp; Newsletter</strong> — view customer messages and subscribers.</li>
</ul>

<h3>4.7 Users, Roles &amp; Permissions</h3>
<ol>
    <li>Open <strong>Users</strong> → create staff accounts (email + password).</li>
    <li>Assign a role: Super Admin, Admin, Manager, Cashier, Stock Manager.</li>
    <li>Use <strong>Roles</strong> / <strong>Permissions</strong> only if you need custom access (Super Admin).</li>
</ol>
<div class="warn">
    Never share Super Admin credentials. Create separate accounts per staff member.
</div>

<h3>4.8 Backups &amp; Settings</h3>
<ul>
    <li>Create database backups regularly from the Backups module (if enabled for your role).</li>
    <li>Restore is restricted to high-privilege roles — use carefully.</li>
</ul>

{{-- 5 --}}
<div class="page-break"></div>
<h2>5. Manager Guide</h2>
<p>Managers run daily operations without full system administration.</p>
<ul>
    <li>Use <strong>POS</strong> for walk-in sales.</li>
    <li>Manage <strong>Products</strong>, <strong>Categories</strong>, <strong>Stock Takes</strong>, <strong>Coupons</strong>, <strong>Vendors</strong>.</li>
    <li>Process <strong>Orders</strong> and <strong>Returns</strong>.</li>
    <li>Review <strong>Reports</strong> and cashier performance.</li>
    <li>May create/edit some users and assign roles (depending on setup).</li>
    <li>Cannot restore database backups.</li>
</ul>

{{-- 6 --}}
<h2>6. Cashier Guide (Point of Sale)</h2>
<p>Cashiers complete in-store sales quickly on mobile or desktop.</p>

<h3>6.1 Open POS</h3>
<ol>
    <li>Login at Admin → open <strong>POS</strong> (or go to <span class="mono">/admin/pos</span>).</li>
    <li>Optional: open a <strong>Cashier Shift</strong> before selling (Shifts module).</li>
    <li>The black header stays sticky and shows today’s sales summary.</li>
</ol>

<h3>6.2 Make a sale</h3>
<ol>
    <li>Search by name / SKU / barcode, or tap a category chip.</li>
    <li>Tap <strong>Add</strong> on products (2 per row on phone).</li>
    <li>Open <strong>View Cart &amp; Checkout</strong> (bottom bar on mobile).</li>
    <li>Adjust quantities with − / +, or remove items with ×.</li>
    <li>Optional: enter coupon code → <strong>Apply</strong>.</li>
    <li>Optional: customer name/phone under Customer Information.</li>
    <li>Choose payment method: Cash, M-Pesa, Card, Bank transfer.</li>
    <li>For <strong>Cash</strong>: amount received auto-fills with total; enter more only if giving change.</li>
    <li>Tap <strong>Complete sale</strong>.</li>
</ol>

<h3>6.3 Receipt</h3>
<ul>
    <li><strong>Print Receipt</strong> — use 80mm thermal paper; disable browser headers/footers.</li>
    <li><strong>WhatsApp</strong> — share receipt text to the customer.</li>
    <li><strong>Share</strong> — phone share sheet (Telegram, email, etc.) or copy text.</li>
    <li><strong>SMS</strong> — open SMS with receipt text.</li>
    <li><strong>New Sale</strong> — start the next transaction.</li>
    <li><strong>View Order</strong> — open the saved order in admin.</li>
</ul>

<h3>6.4 Returns (Cashier)</h3>
<ol>
    <li>Open <strong>Returns</strong> → Create.</li>
    <li>Find the order by receipt/order number.</li>
    <li>Select items/qty, reason, refund method.</li>
    <li>Submit — stock is restored according to the return process.</li>
</ol>

<h3>6.5 Shifts</h3>
<ol>
    <li>Open <strong>Shifts</strong> → start shift with opening cash.</li>
    <li>Process sales during the shift.</li>
    <li>Close shift with actual cash counted; system compares expected vs actual.</li>
</ol>

{{-- 7 --}}
<div class="page-break"></div>
<h2>7. Stock Manager Guide</h2>
<ol>
    <li><strong>Products</strong> — update stock quantities, prices, images, barcodes.</li>
    <li><strong>Categories</strong> — keep catalogue structure clean.</li>
    <li><strong>Stock Takes</strong>:
        <ul>
            <li>Create a stock take (optionally filter by category/vendor).</li>
            <li>Start counting → enter physical counts.</li>
            <li>Send for review → Admin/approver <strong>Approves</strong> to apply inventory adjustments.</li>
            <li>Cancel if the count should be discarded (stock unchanged until approve).</li>
        </ul>
    </li>
    <li>Check <strong>Reports</strong> for inventory-related insights.</li>
</ol>
<div class="note">Physical stock does not change until a stock take is approved.</div>

{{-- 8 --}}
<h2>8. Customer (Online Shop) Guide</h2>

<h3>8.1 Browse &amp; search</h3>
<ul>
    <li>Open the store homepage.</li>
    <li>Use Search, All Categories, Shop, Blog, Contact.</li>
    <li>Flash Sale and Featured Products show View + Add to Cart.</li>
</ul>

<h3>8.2 Account</h3>
<ol>
    <li><strong>Register</strong> or <strong>Login</strong> (required before checkout).</li>
    <li>When logged in, tap the round avatar (top right) for My Orders, Wishlist, Logout.</li>
    <li>Account quick link opens My Orders.</li>
</ol>

<h3>8.3 Cart &amp; Checkout</h3>
<ol>
    <li>Add products to cart from listing or product page.</li>
    <li>Open Cart → update quantities or apply coupon.</li>
    <li>Proceed to Checkout (login/register if needed).</li>
    <li>Confirm name, email, phone, shipping address.</li>
    <li>Choose payment: Cash on Delivery, Mobile Money, Bank Transfer, or Card.</li>
    <li>Place Order and keep your order number.</li>
</ol>

<h3>8.4 Wishlist, reviews, tracking</h3>
<ul>
    <li><strong>Wishlist</strong> — heart icon on products (login required).</li>
    <li><strong>Reviews</strong> — rate with clickable stars ★ and leave a comment.</li>
    <li><strong>Track Order</strong> — public tracking page with order details.</li>
    <li><strong>My Orders</strong> — view order history on mobile-friendly cards.</li>
</ul>

<h3>8.5 Cancel vs Return (important)</h3>
<table>
    <thead>
        <tr>
            <th>Order status</th>
            <th>Customer action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Pending / Processing</td>
            <td>Request <strong>cancellation</strong></td>
        </tr>
        <tr>
            <td>Paid / Shipped / Delivered</td>
            <td>Request a <strong>return</strong> only (cannot cancel)</td>
        </tr>
    </tbody>
</table>
<p>Staff then process the request in Admin → Orders / Returns.</p>

{{-- 9 --}}
<div class="page-break"></div>
<h2>9. Orders, Returns &amp; Cancellations</h2>
<h3>Staff workflow</h3>
<ol>
    <li>Customer submits cancel or return request from My Orders.</li>
    <li>Order status becomes <em>cancellation_requested</em> or <em>return_requested</em>.</li>
    <li>Admin/Manager opens the order, reviews reason, then updates status or processes a return.</li>
    <li>For returns, use <strong>Returns → Process Return</strong> to restore stock and record refund method.</li>
</ol>

{{-- 10 --}}
<h2>10. Reports &amp; Shifts</h2>
<ul>
    <li><strong>Reports</strong> — sales summaries, exports, vendor commissions CSV.</li>
    <li><strong>Cashier performance</strong> — compare cashier sales/returns.</li>
    <li><strong>Shifts</strong> — opening cash, expected cash, closing variance.</li>
</ul>

{{-- 11 --}}
<h2>11. Tips, Security &amp; Troubleshooting</h2>
<table>
    <thead>
        <tr>
            <th style="width:34%">Issue</th>
            <th>What to try</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Cannot see a menu</td>
            <td>Your role lacks permission — ask Admin to assign the correct role.</td>
        </tr>
        <tr>
            <td>Checkout blocked</td>
            <td>Customer must login/register first.</td>
        </tr>
        <tr>
            <td>POS page cut off on phone</td>
            <td>Hard-refresh or reinstall the PWA. Use latest POS layout (2 products per row).</td>
        </tr>
        <tr>
            <td>Wrong app icon</td>
            <td>Uninstall staff app → reopen site → Install again.</td>
        </tr>
        <tr>
            <td>Receipt won’t print correctly</td>
            <td>Select 80mm paper; turn off headers/footers in the browser print dialog.</td>
        </tr>
        <tr>
            <td>Forgot password</td>
            <td>Ask an Admin to reset the staff password from Users.</td>
        </tr>
        <tr>
            <td>Stock wrong after sale</td>
            <td>Confirm the sale completed; check Returns and Stock Takes.</td>
        </tr>
    </tbody>
</table>

<div class="box">
    <strong>Security checklist</strong>
    <ul>
        <li>Use unique staff accounts — do not share logins.</li>
        <li>Logout on shared tablets after shifts.</li>
        <li>Change default passwords immediately.</li>
        <li>Only Super Admin should manage roles/permissions and restores.</li>
    </ul>
</div>

<h2>Quick Reference — Common Paths</h2>
<table>
    <thead>
        <tr>
            <th>Task</th>
            <th>Where</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>Staff login</td><td>/admin/login</td></tr>
        <tr><td>Dashboard</td><td>/admin</td></tr>
        <tr><td>POS</td><td>/admin/pos</td></tr>
        <tr><td>Orders</td><td>/admin/orders</td></tr>
        <tr><td>Products</td><td>/admin/products</td></tr>
        <tr><td>Stock takes</td><td>/admin/stock-takes</td></tr>
        <tr><td>Returns</td><td>/admin/returns</td></tr>
        <tr><td>Reports</td><td>/admin/reports</td></tr>
        <tr><td>Customer shop</td><td>/</td></tr>
        <tr><td>Customer login</td><td>/login</td></tr>
        <tr><td>My Orders</td><td>/my-orders</td></tr>
        <tr><td>Track order</td><td>/track-order</td></tr>
    </tbody>
</table>

<p class="footer-note">
    © {{ now()->year }} Nomas Apparel · System User Manual v1.0 · Generated {{ now()->format('d M Y H:i') }}<br>
    For support, contact your system administrator.
</p>

</body>
</html>
