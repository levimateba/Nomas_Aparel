@extends('layouts.admin')
@section('title','Edit About')
@section('content')
    <style>
        .about-edit-wrap {
            display: grid;
            gap: 18px;
        }
        .about-edit-head h2 {
            margin: 0;
            font-size: 1.6rem;
            color: #121212;
        }
        .about-edit-head p {
            margin: 8px 0 0;
            color: #5f5f5f;
            font-size: 0.95rem;
        }
        .about-form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .about-section-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            padding: 16px;
            background: #ffffff;
        }
        .about-section-title {
            margin: 0 0 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #1f1f1f;
        }
        .about-field {
            margin-bottom: 14px;
        }
        .about-field:last-child {
            margin-bottom: 0;
        }
        .about-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .about-label-row label {
            margin: 0;
            font-weight: 600;
            color: #1f1f1f;
        }
        .about-required {
            color: #8a1f2d;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .about-help {
            color: #666;
            font-size: 0.84rem;
            margin-top: 5px;
            display: block;
        }
        .about-form-grid input[type="text"],
        .about-form-grid input[type="file"],
        .about-form-grid textarea {
            width: 100%;
            border: 1px solid #d9dee3;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 0.95rem;
            background: #fff;
        }
        .about-form-grid input:focus,
        .about-form-grid textarea:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.16);
        }
        .about-form-grid textarea {
            min-height: 190px;
            resize: vertical;
        }
        .about-preview-box {
            border: 1px dashed #c9ced3;
            border-radius: 12px;
            background: #fafafa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            overflow: hidden;
        }
        .about-preview-box img {
            max-width: 100%;
            max-height: 280px;
            border-radius: 10px;
            object-fit: cover;
        }
        .about-preview-placeholder {
            color: #7a7a7a;
            font-size: 0.9rem;
            text-align: center;
            line-height: 1.4;
        }
        .about-actions {
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
            .about-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="about-edit-wrap">
        <div class="about-edit-head">
            <h2>Edit About Section</h2>
            <p>Refine your headline, message, and image to keep your homepage introduction modern and professional.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.about.update', $section) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="about-form-grid">
                    <div class="about-section-card">
                        <h3 class="about-section-title">Section Content</h3>

                        <div class="about-field">
                            <div class="about-label-row">
                                <label for="about-title">Title</label>
                                <span class="about-required">Required</span>
                            </div>
                            <input id="about-title" type="text" name="title" value="{{ old('title', $section->title) }}" maxlength="255" placeholder="e.g. Who We Are" required>
                            <small class="about-help">Use a concise headline that clearly explains your company or mission.</small>
                        </div>

                        <div class="about-field">
                            <div class="about-label-row">
                                <label for="about-content">Content</label>
                                <span class="about-required">Required</span>
                            </div>
                            <textarea id="about-content" name="content" rows="8" placeholder="Write a professional summary of your business, values, and impact..." required>{{ old('content', $section->content) }}</textarea>
                            <small class="about-help">Tip: keep the first 2 lines strong and value-focused for better readability.</small>
                        </div>
                    </div>

                    <div class="about-section-card">
                        <h3 class="about-section-title">Visual</h3>

                        <div class="about-field">
                            <label for="about-image">Replace image (optional)</label>
                            <input id="about-image" type="file" name="image_url" accept="image/jpeg,image/png,image/jpg,image/gif">
                            <small class="about-help">Max file size: 2MB. Supported: JPEG, PNG, JPG, GIF.</small>
                        </div>

                        <div class="about-preview-box" id="about-preview-box">
                            @if($section->image_url)
                                <img id="about-preview-image" src="{{ $section->image_url }}" alt="Current image preview">
                                <div class="about-preview-placeholder" id="about-preview-placeholder" style="display:none;">Image preview will appear here after you choose a file.</div>
                            @else
                                <img id="about-preview-image" alt="Selected image preview" style="display:none;">
                                <div class="about-preview-placeholder" id="about-preview-placeholder">No image currently set. Choose a file to preview it here.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="about-actions">
                    <button type="submit">Update About Section</button>
                    <a class="btn-outline" href="{{ route('admin.about.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const imageInput = document.getElementById('about-image');
            const previewImage = document.getElementById('about-preview-image');
            const placeholder = document.getElementById('about-preview-placeholder');

            if (!imageInput || !previewImage || !placeholder) {
                return;
            }

            imageInput.addEventListener('change', function (event) {
                const file = event.target.files && event.target.files[0];
                if (!file) {
                    if (!previewImage.getAttribute('src')) {
                        previewImage.style.display = 'none';
                        placeholder.style.display = 'block';
                    }
                    return;
                }

                const objectUrl = URL.createObjectURL(file);
                previewImage.src = objectUrl;
                previewImage.style.display = 'block';
                placeholder.style.display = 'none';
            });
        })();
    </script>
@endsection
