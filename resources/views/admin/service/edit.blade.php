@extends('layouts.admin')
@section('title','Edit Service')
@section('content')
    <style>
        .service-form-wrap {
            display: grid;
            gap: 18px;
        }
        .service-form-head h2 {
            margin: 0;
            font-size: 1.6rem;
            color: #121212;
        }
        .service-form-head p {
            margin: 8px 0 0;
            color: #5f5f5f;
            font-size: 0.95rem;
        }
        .service-form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .service-section-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            padding: 16px;
            background: #ffffff;
        }
        .service-section-title {
            margin: 0 0 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #1f1f1f;
        }
        .service-field {
            margin-bottom: 14px;
        }
        .service-field:last-child {
            margin-bottom: 0;
        }
        .service-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .service-label-row label {
            margin: 0;
            font-weight: 600;
            color: #1f1f1f;
        }
        .service-required {
            color: #8a1f2d;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .service-help {
            color: #666;
            font-size: 0.84rem;
            margin-top: 5px;
            display: block;
        }
        .service-form-grid input[type="text"],
        .service-form-grid select,
        .service-form-grid textarea {
            width: 100%;
            border: 1px solid #d9dee3;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 0.95rem;
            background: #fff;
        }
        .service-form-grid input:focus,
        .service-form-grid select:focus,
        .service-form-grid textarea:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.16);
        }
        .service-form-grid textarea {
            min-height: 190px;
            resize: vertical;
        }
        .service-preview {
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
        .service-preview-icon {
            width: 74px;
            height: 74px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 2rem;
            color: #121212;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.2), rgba(184, 148, 45, 0.2));
            border: 1px solid rgba(212, 175, 55, 0.36);
        }
        .service-preview-title {
            font-weight: 700;
            color: #1f1f1f;
        }
        .service-preview-meta {
            color: #6b6b6b;
            font-size: 0.86rem;
        }
        .service-actions {
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
            .service-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="service-form-wrap">
        <div class="service-form-head">
            <h2>Edit Service</h2>
            <p>Update the service details to keep your catalog accurate, clear, and professionally presented.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.service.update', $service) }}">
                @csrf
                @method('PUT')

                <div class="service-form-grid">
                    <div class="service-section-card">
                        <h3 class="service-section-title">Service Details</h3>

                        <div class="service-field">
                            <div class="service-label-row">
                                <label for="service-title">Title</label>
                                <span class="service-required">Required</span>
                            </div>
                            <input id="service-title" type="text" name="title" value="{{ old('title', $service->title) }}" maxlength="255" placeholder="e.g. Network Infrastructure" required>
                            <small class="service-help">Use a concise and specific service name.</small>
                        </div>

                        <div class="service-field">
                            <div class="service-label-row">
                                <label for="service-category">Category</label>
                                <span class="service-required">Required</span>
                            </div>
                            <select id="service-category" name="category" required>
                                <option value="">Select category</option>
                                @foreach($serviceCategories as $category)
                                    <option value="{{ $category }}" {{ old('category', $service->category) === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="service-field">
                            <div class="service-label-row">
                                <label for="service-description">Description</label>
                                <span class="service-required">Required</span>
                            </div>
                            <textarea id="service-description" name="description" rows="8" placeholder="Describe what the service includes, outcomes, and value to clients..." required>{{ old('description', $service->description) }}</textarea>
                            <small class="service-help">Keep it client-focused and outcome-driven.</small>
                        </div>
                    </div>

                    <div class="service-section-card">
                        <h3 class="service-section-title">Icon & Preview</h3>

                        <div class="service-field">
                            <label for="service-icon">Icon (Font Awesome)</label>
                            <input id="service-icon" type="text" name="icon" value="{{ old('icon', $service->icon) }}" placeholder="fa-code, fa-mobile-alt, fa-cogs, fa-star">
                            <small class="service-help">Examples: fa-code, fa-mobile-alt, fa-cogs, fa-server. See <a href="https://fontawesome.com/icons" target="_blank" rel="noopener">Font Awesome Icons</a>.</small>
                        </div>

                        <div class="service-preview">
                            <div class="service-preview-icon"><i id="service-icon-preview" class="fa fa-cogs" aria-hidden="true"></i></div>
                            <div class="service-preview-title" id="service-title-preview">Service Title Preview</div>
                            <div class="service-preview-meta" id="service-category-preview">Category preview</div>
                        </div>
                    </div>
                </div>

                <div class="service-actions">
                    <button type="submit">Update Service</button>
                    <a class="btn-outline" href="{{ route('admin.service.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const titleInput = document.getElementById('service-title');
            const categoryInput = document.getElementById('service-category');
            const iconInput = document.getElementById('service-icon');
            const titlePreview = document.getElementById('service-title-preview');
            const categoryPreview = document.getElementById('service-category-preview');
            const iconPreview = document.getElementById('service-icon-preview');

            function updatePreview() {
                const iconClass = (iconInput.value || '').trim();
                const titleValue = (titleInput.value || '').trim();
                const categoryValue = (categoryInput.value || '').trim();

                titlePreview.textContent = titleValue || 'Service Title Preview';
                categoryPreview.textContent = categoryValue || 'Category preview';

                iconPreview.className = '';
                if (iconClass) {
                    iconPreview.className = 'fa ' + iconClass;
                } else {
                    iconPreview.className = 'fa fa-cogs';
                }
            }

            [titleInput, categoryInput, iconInput].forEach(function (element) {
                if (element) {
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });

            updatePreview();
        })();
    </script>
@endsection