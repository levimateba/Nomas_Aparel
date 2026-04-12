@extends('layouts.admin')
@section('title', 'Create Vendor')
@section('content')
    <style>
        .vendor-form-wrap { display: grid; gap: 18px; }
        .vendor-form-head h2 { margin: 0; font-size: 1.6rem; color: #121212; }
        .vendor-form-head p { margin: 8px 0 0; color: #5f5f5f; font-size: 0.95rem; }
        .vendor-form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .vendor-section-card { border: 1px solid rgba(0,0,0,0.08); border-radius: 14px; padding: 16px; background: #fff; }
        .vendor-section-title { margin: 0 0 14px; font-size: 1rem; font-weight: 700; color: #1f1f1f; }
        .vendor-field { margin-bottom: 14px; }
        .vendor-field:last-child { margin-bottom: 0; }
        .vendor-label-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        .vendor-label-row label { margin: 0; font-weight: 600; color: #1f1f1f; }
        .vendor-required { color: #8a1f2d; font-size: 0.82rem; font-weight: 600; }
        .vendor-help { color: #666; font-size: 0.84rem; margin-top: 5px; display: block; }
        .vendor-form-grid input[type="text"], .vendor-form-grid input[type="email"], .vendor-form-grid input[type="number"] {
            width: 100%; border: 1px solid #d9dee3; border-radius: 10px; padding: 11px 12px; font-size: 0.95rem; background: #fff;
        }
        .vendor-form-grid textarea { width: 100%; border: 1px solid #d9dee3; border-radius: 10px; padding: 11px 12px; font-size: 0.95rem; background: #fff; }
        .vendor-form-grid input:focus, .vendor-form-grid textarea:focus {
            outline: none; border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,0.16);
        }
        .vendor-switch { display: flex; align-items: center; gap: 10px; margin-top: 4px; font-weight: 600; color: #1f1f1f; }
        .vendor-preview { border: 1px dashed #c9ced3; border-radius: 12px; background: #fafafa; min-height: 200px; display: grid; place-items: center; text-align: center; padding: 14px; gap: 10px; }
        .vendor-status { border-radius: 999px; padding: 6px 12px; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; border: 1px solid transparent; }
        .vendor-status.active { background: rgba(46,125,50,0.15); color: #1b5e20; border-color: rgba(46,125,50,0.3); }
        .vendor-status.inactive { background: rgba(140,140,140,0.15); color: #5f5f5f; border-color: rgba(140,140,140,0.3); }
        .vendor-preview-name { font-weight: 700; color: #1f1f1f; font-size: 1rem; }
        .vendor-preview-email, .vendor-preview-phone { color: #2f2f2f; font-size: 0.92rem; word-break: break-word; }
        .vendor-preview-desc { color: #444; font-size: 0.92rem; margin-top: 8px; }
        .vendor-actions { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
        .btn-outline { border: 1px solid #d4af37; border-radius: 999px; color: #1f1f1f; background: #fff; padding: 9px 15px; text-decoration: none; font-weight: 600; }
        @media (max-width: 960px) { .vendor-form-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="vendor-form-wrap">
        <div class="vendor-form-head">
            <h2>Create Vendor</h2>
            <p>Add a new vendor with contact details, commission rate, and status. All fields can be updated later.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.vendors.store') }}">
                @csrf

                <div class="vendor-form-grid">
                    <div class="vendor-section-card">
                        <h3 class="vendor-section-title">Vendor Details</h3>
                        @include('admin.vendors._form', ['vendor' => new \App\Models\Vendor()])
                    </div>
                    <div class="vendor-section-card">
                        <h3 class="vendor-section-title">Preview</h3>
                        <div class="vendor-preview">
                            <span id="vendor-status" class="vendor-status active">Active</span>
                            <div id="vendor-name-preview" class="vendor-preview-name">Vendor Name Preview</div>
                            <div id="vendor-email-preview" class="vendor-preview-email">vendor@email.com</div>
                            <div id="vendor-phone-preview" class="vendor-preview-phone">+1234567890</div>
                            <div id="vendor-desc-preview" class="vendor-preview-desc">Description preview...</div>
                        </div>
                    </div>
                </div>

                <div class="vendor-actions">
                    <button type="submit">Create Vendor</button>
                    <a class="btn-outline" href="{{ route('admin.vendors.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const nameInput = document.querySelector('input[name="name"]');
            const emailInput = document.querySelector('input[name="email"]');
            const phoneInput = document.querySelector('input[name="phone"]');
            const descInput = document.querySelector('textarea[name="description"]');
            const activeInput = document.querySelector('input[name="is_active"]');
            const namePreview = document.getElementById('vendor-name-preview');
            const emailPreview = document.getElementById('vendor-email-preview');
            const phonePreview = document.getElementById('vendor-phone-preview');
            const descPreview = document.getElementById('vendor-desc-preview');
            const statusPreview = document.getElementById('vendor-status');

            function updatePreview() {
                const nameValue = (nameInput?.value || '').trim();
                const emailValue = (emailInput?.value || '').trim();
                const phoneValue = (phoneInput?.value || '').trim();
                const descValue = (descInput?.value || '').trim();
                const isActive = !!activeInput?.checked;

                namePreview.textContent = nameValue || 'Vendor Name Preview';
                emailPreview.textContent = emailValue || 'vendor@email.com';
                phonePreview.textContent = phoneValue || '+1234567890';
                descPreview.textContent = descValue || 'Description preview...';
                statusPreview.className = 'vendor-status ' + (isActive ? 'active' : 'inactive');
                statusPreview.textContent = isActive ? 'Active' : 'Inactive';
            }

            [nameInput, emailInput, phoneInput, descInput, activeInput].forEach(function (element) {
                if (element) {
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });
            updatePreview();
        })();
    </script>
@endsection
