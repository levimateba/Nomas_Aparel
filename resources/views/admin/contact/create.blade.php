@extends('layouts.admin')
@section('title','Create Contact')
@section('content')
    <style>
        .contact-form-wrap {
            display: grid;
            gap: 18px;
        }
        .contact-form-head h2 {
            margin: 0;
            font-size: 1.6rem;
            color: #121212;
        }
        .contact-form-head p {
            margin: 8px 0 0;
            color: #5f5f5f;
            font-size: 0.95rem;
        }
        .contact-form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .contact-section-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            padding: 16px;
            background: #ffffff;
        }
        .contact-section-title {
            margin: 0 0 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #1f1f1f;
        }
        .contact-field {
            margin-bottom: 14px;
        }
        .contact-field:last-child {
            margin-bottom: 0;
        }
        .contact-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .contact-label-row label {
            margin: 0;
            font-weight: 600;
            color: #1f1f1f;
        }
        .contact-required {
            color: #8a1f2d;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .contact-help {
            color: #666;
            font-size: 0.84rem;
            margin-top: 5px;
            display: block;
        }
        .contact-form-grid input[type="text"],
        .contact-form-grid select {
            width: 100%;
            border: 1px solid #d9dee3;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 0.95rem;
            background: #fff;
        }
        .contact-form-grid input:focus,
        .contact-form-grid select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.16);
        }
        .contact-preview {
            border: 1px dashed #c9ced3;
            border-radius: 12px;
            background: #fafafa;
            min-height: 200px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 14px;
            gap: 10px;
        }
        .contact-badge {
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(212, 175, 55, 0.2);
            color: #121212;
            border: 1px solid rgba(212, 175, 55, 0.36);
        }
        .contact-preview-title {
            font-weight: 700;
            color: #1f1f1f;
            font-size: 1rem;
        }
        .contact-preview-value {
            color: #2f2f2f;
            font-size: 0.95rem;
            word-break: break-word;
        }
        .contact-actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
            flex-wrap: wrap;
        }
        .btn-outline {
            border: 1px solid #d4af37;
            border-radius: 999px;
            color: #1f1f1f;
            background: #fff;
            padding: 9px 15px;
            text-decoration: none;
            font-weight: 600;
        }
        @media (max-width: 960px) {
            .contact-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="contact-form-wrap">
        <div class="contact-form-head">
            <h2>Create Contact</h2>
            <p>Add a clear contact item for your frontend pages with a label, value, and type that visitors can understand quickly.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.contact.store') }}">
                @csrf

                <div class="contact-form-grid">
                    <div class="contact-section-card">
                        <h3 class="contact-section-title">Contact Details</h3>

                        <div class="contact-field">
                            <div class="contact-label-row">
                                <label for="contact-label">Label</label>
                                <span class="contact-required">Required</span>
                            </div>
                            <input id="contact-label" type="text" name="label" value="{{ old('label') }}" maxlength="255" placeholder="e.g. Customer Support" required>
                            <small class="contact-help">This is the name users will see for this contact item.</small>
                        </div>

                        <div class="contact-field">
                            <div class="contact-label-row">
                                <label for="contact-type">Type</label>
                                <span class="contact-required">Required</span>
                            </div>
                            <select id="contact-type" name="type" required>
                                <option value="text" {{ old('type')=='text' ? 'selected' : '' }}>Text</option>
                                <option value="email" {{ old('type')=='email' ? 'selected' : '' }}>Email</option>
                                <option value="phone" {{ old('type')=='phone' ? 'selected' : '' }}>Phone</option>
                                <option value="address" {{ old('type')=='address' ? 'selected' : '' }}>Address</option>
                            </select>
                        </div>

                        <div class="contact-field">
                            <div class="contact-label-row">
                                <label for="contact-value">Value</label>
                                <span class="contact-required">Required</span>
                            </div>
                            <input id="contact-value" type="text" name="value" value="{{ old('value') }}" maxlength="255" placeholder="e.g. info@example.com" required>
                            <small class="contact-help" id="contact-value-help">Provide the actual contact value users should use.</small>
                        </div>
                    </div>

                    <div class="contact-section-card">
                        <h3 class="contact-section-title">Preview</h3>

                        <div class="contact-preview">
                            <span class="contact-badge" id="contact-type-preview">Text</span>
                            <div class="contact-preview-title" id="contact-label-preview">Contact Label Preview</div>
                            <div class="contact-preview-value" id="contact-value-preview">Contact value preview</div>
                        </div>
                    </div>
                </div>

                <div class="contact-actions">
                    <button type="submit">Create Contact</button>
                    <a class="btn-outline" href="{{ route('admin.contact.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const labelInput = document.getElementById('contact-label');
            const valueInput = document.getElementById('contact-value');
            const typeInput = document.getElementById('contact-type');
            const labelPreview = document.getElementById('contact-label-preview');
            const valuePreview = document.getElementById('contact-value-preview');
            const typePreview = document.getElementById('contact-type-preview');
            const valueHelp = document.getElementById('contact-value-help');

            function getTypeHint(type) {
                if (type === 'email') {
                    return 'Use a valid email address, for example support@yourdomain.com.';
                }
                if (type === 'phone') {
                    return 'Use a phone format users can call, for example +254 700 000 000.';
                }
                if (type === 'address') {
                    return 'Use a physical location users can find easily.';
                }
                return 'Provide the actual contact value users should use.';
            }

            function updatePreview() {
                const labelValue = (labelInput.value || '').trim();
                const valueValue = (valueInput.value || '').trim();
                const typeValue = (typeInput.value || 'text').trim();

                labelPreview.textContent = labelValue || 'Contact Label Preview';
                valuePreview.textContent = valueValue || 'Contact value preview';
                typePreview.textContent = typeValue;
                valueHelp.textContent = getTypeHint(typeValue);
            }

            [labelInput, valueInput, typeInput].forEach(function (element) {
                if (element) {
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });

            updatePreview();
        })();
    </script>
@endsection