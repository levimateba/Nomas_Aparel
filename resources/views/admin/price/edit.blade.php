@extends('layouts.admin')
@section('title','Edit Pricing')
@section('content')
    <h2>Edit Pricing</h2>
    @php
        $initialTemplate = collect($pricingTemplates)->first(function ($template) use ($price) {
            return isset($template['title']) && $template['title'] === $price->title;
        });
        $initialTemplateKey = $initialTemplate ? collect($pricingTemplates)->search($initialTemplate) : 'custom';
    @endphp
    <div class="card">
        <form method="POST" action="{{ route('admin.price.update', $price) }}">
            @csrf
            @method('PUT')
            <label>Plan Template</label>
            <select name="plan_template" id="plan_template" required>
                @foreach($pricingTemplates as $key => $template)
                    <option value="{{ $key }}" {{ old('plan_template', $initialTemplateKey) === $key ? 'selected' : '' }}>{{ $template['label'] }}</option>
                @endforeach
            </select>

            <div id="custom_title_wrap" style="display:{{ old('plan_template', $initialTemplateKey) === 'custom' ? 'block' : 'none' }};">
                <label>Custom Plan Title</label>
                <input type="text" name="custom_title" value="{{ old('custom_title', $initialTemplateKey === 'custom' ? $price->title : '') }}" placeholder="e.g. NGO Systems Package">
            </div>

            <label>Plan Subtitle</label>
            <input type="text" name="subtitle" id="price_subtitle" value="{{ old('subtitle', $price->subtitle) }}" placeholder="e.g. Ideal for startups & small businesses">

            <label>Description</label>
            <textarea name="description" id="price_description" rows="3">{{ old('description', $price->description) }}</textarea>
            <label>Amount (Kshs.)</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $price->amount) }}" required>
            <label>Display Price (shown on card)</label>
            <input type="text" name="display_price" id="display_price" value="{{ old('display_price', $price->display_price) }}" placeholder="e.g. KES 15,000 - 50,000">
            <label>Billing Period</label>
            <input type="text" name="billing_period" value="{{ old('billing_period', $price->billing_period) }}" required>
            
            <label style="margin-top: 20px;">Features (one per line)</label>
            <textarea name="features_text" id="features_text" rows="6">{{ old('features_text', is_array($price->features) ? implode("\n", collect($price->features)->map(fn($v, $k) => is_int($k) ? $v : "$k: $v")->values()->all()) : '') }}</textarea>
            <small style="display: block; color: #666; margin-top: 5px;">Use plain bullet lines, or optional key:value format for comparison tables.</small>
            <small id="amount_hint" style="display: block; color: #165752; margin-top: 8px;"></small>
            
            <label style="margin-top: 15px;"><input type="checkbox" name="featured" {{ old('featured', $price->featured) ? 'checked' : '' }}> Featured Plan (highlight this plan)</label>
            <button type="submit">Update</button>
        </form>
    </div>

    <script>
        (function () {
            const templates = @json($pricingTemplates);
            const templateSelect = document.getElementById('plan_template');
            const customWrap = document.getElementById('custom_title_wrap');
            const subtitle = document.getElementById('price_subtitle');
            const desc = document.getElementById('price_description');
            const features = document.getElementById('features_text');
            const displayPrice = document.getElementById('display_price');
            const hint = document.getElementById('amount_hint');

            function applyTemplate() {
                const key = templateSelect.value;
                const template = templates[key] || null;

                customWrap.style.display = key === 'custom' ? 'block' : 'none';

                if (!template) {
                    return;
                }

                hint.textContent = template.amount_hint ? ('Recommended range: ' + template.amount_hint) : '';

                if (!displayPrice.value.trim()) {
                    displayPrice.value = template.amount_hint || '';
                }

                if (!subtitle.value.trim()) {
                    subtitle.value = template.subtitle || '';
                }

                if (!desc.value.trim()) {
                    desc.value = template.description || '';
                }

                if (!features.value.trim() && template.features) {
                    const lines = Array.isArray(template.features)
                        ? template.features
                        : Object.keys(template.features).map(function(label) {
                            return label + ': ' + template.features[label];
                        });
                    features.value = lines.join('\n');
                }
            }

            templateSelect.addEventListener('change', applyTemplate);
            applyTemplate();
        })();
    </script>
@endsection