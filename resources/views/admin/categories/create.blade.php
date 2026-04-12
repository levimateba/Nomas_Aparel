@extends('layouts.admin')
@section('title', 'Create Category')
@section('content')
    <style>
        .category-form-wrap {
            display: grid;
            gap: 18px;
        }
        .category-form-head h2 {
            margin: 0;
            font-size: 1.6rem;
            color: #121212;
        }
        .category-form-head p {
            margin: 8px 0 0;
            color: #5f5f5f;
            font-size: 0.95rem;
        }
        .category-form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .category-section-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            padding: 16px;
            background: #fff;
        }
        .category-section-title {
            margin: 0 0 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #1f1f1f;
        }
        .category-field {
            margin-bottom: 14px;
        }
        .category-field:last-child {
            margin-bottom: 0;
        }
        .category-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .category-label-row label {
            margin: 0;
            font-weight: 600;
            color: #1f1f1f;
        }
        .category-required {
            color: #8a1f2d;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .category-help {
            color: #666;
            font-size: 0.84rem;
            margin-top: 5px;
            display: block;
        }
        .category-form-grid input[type="text"] {
            width: 100%;
            border: 1px solid #d9dee3;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 0.95rem;
            background: #fff;
        }
        .category-form-grid input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.16);
        }
        .category-switch {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 4px;
            font-weight: 600;
            color: #1f1f1f;
        }
        .category-preview {
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
        .category-status {
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .category-status.active {
            background: rgba(46, 125, 50, 0.15);
            color: #1b5e20;
            border-color: rgba(46, 125, 50, 0.3);
        }
        .category-status.inactive {
            background: rgba(140, 140, 140, 0.15);
            color: #5f5f5f;
            border-color: rgba(140, 140, 140, 0.3);
        }
        .category-preview-name {
            font-weight: 700;
            color: #1f1f1f;
            font-size: 1rem;
        }
        .category-preview-slug {
            color: #2f2f2f;
            font-size: 0.92rem;
            word-break: break-word;
        }
        .category-actions {
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
            .category-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="category-form-wrap">
        <div class="category-form-head">
            <h2>Create Category</h2>
            <p>Add a product category with a clean name and URL slug so navigation stays organized.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                <div class="category-form-grid">
                    <div class="category-section-card">
                        <h3 class="category-section-title">Category Details</h3>

                        <div class="category-field">
                            <div class="category-label-row">
                                <label for="category-name">Name</label>
                                <span class="category-required">Required</span>
                            </div>
                            <input id="category-name" type="text" name="name" value="{{ old('name') }}" maxlength="255" placeholder="e.g. Electronics" required>
                            <small class="category-help">Use a short, recognizable label your customers understand.</small>
                        </div>

                        <div class="category-field">
                            <div class="category-label-row">
                                <label for="category-slug">Slug (optional)</label>
                            </div>
                            <input id="category-slug" type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-generated-if-empty">
                            <small class="category-help">Leave blank to auto-generate from name.</small>
                        </div>

                        <div class="category-field">
                            <label class="category-switch" for="category-active">
                                <input id="category-active" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                Active category
                            </label>
                            <small class="category-help">Inactive categories are hidden from active browsing flows.</small>
                        </div>
                    </div>

                    <div class="category-section-card">
                        <h3 class="category-section-title">Preview</h3>
                        <div class="category-preview">
                            <span id="category-status" class="category-status active">Active</span>
                            <div id="category-name-preview" class="category-preview-name">Category Name Preview</div>
                            <div id="category-slug-preview" class="category-preview-slug">/category/slug-preview</div>
                        </div>
                    </div>
                </div>

                <div class="category-actions">
                    <button type="submit">Create Category</button>
                    <a class="btn-outline" href="{{ route('admin.categories.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const nameInput = document.getElementById('category-name');
            const slugInput = document.getElementById('category-slug');
            const activeInput = document.getElementById('category-active');
            const namePreview = document.getElementById('category-name-preview');
            const slugPreview = document.getElementById('category-slug-preview');
            const statusPreview = document.getElementById('category-status');

            function toSlug(value) {
                return value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }

            function updatePreview() {
                const nameValue = (nameInput.value || '').trim();
                const slugValue = (slugInput.value || '').trim();
                const finalSlug = slugValue || toSlug(nameValue);
                const isActive = !!activeInput.checked;

                namePreview.textContent = nameValue || 'Category Name Preview';
                slugPreview.textContent = finalSlug ? '/category/' + finalSlug : '/category/slug-preview';

                statusPreview.className = 'category-status ' + (isActive ? 'active' : 'inactive');
                statusPreview.textContent = isActive ? 'Active' : 'Inactive';
            }

            [nameInput, slugInput, activeInput].forEach(function (element) {
                if (element) {
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });

            updatePreview();
        })();
    </script>
@endsection
