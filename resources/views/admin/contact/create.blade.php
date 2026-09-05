@extends('layouts.admin')
@section('title', 'Create Contact')

@section('page_header')
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.contact.index') }}" class="hover:text-brand-500">Contacts</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Create</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Create Contact</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a phone, email, address, or text item for the public site.</p>
    </div>
    <a href="{{ route('admin.contact.index') }}" class="ta-btn-outline">← Back</a>
</div>
@endsection

@section('content')
<div class="ta-page">
    <form method="POST" action="{{ route('admin.contact.store') }}" class="grid gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(240px,0.8fr)]">
        @csrf
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-base font-bold text-gray-800 dark:text-white/90">Contact details</h2>
            <div class="mt-5 space-y-4">
                <div class="ta-field">
                    <label for="contact-label">Label</label>
                    <input id="contact-label" class="ta-input" type="text" name="label" value="{{ old('label') }}" maxlength="255" placeholder="e.g. Customer Support" required>
                    <p class="mt-1 text-xs text-gray-500">Name visitors will see for this contact item.</p>
                </div>
                <div class="ta-field">
                    <label for="contact-type">Type</label>
                    <select id="contact-type" class="ta-select" name="type" required>
                        <option value="text" @selected(old('type') === 'text')>Text</option>
                        <option value="email" @selected(old('type') === 'email')>Email</option>
                        <option value="phone" @selected(old('type') === 'phone')>Phone</option>
                        <option value="address" @selected(old('type') === 'address')>Address</option>
                    </select>
                </div>
                <div class="ta-field">
                    <label for="contact-value">Value</label>
                    <input id="contact-value" class="ta-input" type="text" name="value" value="{{ old('value') }}" maxlength="255" placeholder="e.g. info@example.com" required>
                    <p class="mt-1 text-xs text-gray-500" id="contact-value-help">Provide the actual contact value users should use.</p>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-2">
                <button class="ta-btn" type="submit">Create contact</button>
                <a href="{{ route('admin.contact.index') }}" class="ta-btn-outline">Cancel</a>
            </div>
        </div>

        <aside class="rounded-2xl border border-dashed border-gray-300 bg-[#faf8f2] p-6 dark:border-gray-700 dark:bg-brand-500/10">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Live preview</h3>
            <div class="mt-5 flex min-h-[180px] flex-col items-center justify-center gap-3 text-center">
                <span id="contact-type-preview" class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-800 dark:bg-white/10 dark:text-brand-300">Text</span>
                <div id="contact-label-preview" class="text-base font-bold text-gray-800 dark:text-white/90">Contact Label Preview</div>
                <div id="contact-value-preview" class="text-sm text-gray-600 dark:text-gray-300">Contact value preview</div>
            </div>
        </aside>
    </form>
</div>

<script>
(function () {
    var labelInput = document.getElementById('contact-label');
    var valueInput = document.getElementById('contact-value');
    var typeInput = document.getElementById('contact-type');
    var labelPreview = document.getElementById('contact-label-preview');
    var valuePreview = document.getElementById('contact-value-preview');
    var typePreview = document.getElementById('contact-type-preview');
    var valueHelp = document.getElementById('contact-value-help');

    function getTypeHint(type) {
        if (type === 'email') return 'Use a valid email address, for example support@yourdomain.com.';
        if (type === 'phone') return 'Use a phone format users can call, for example +254 700 000 000.';
        if (type === 'address') return 'Use a physical location users can find easily.';
        return 'Provide the actual contact value users should use.';
    }

    function updatePreview() {
        labelPreview.textContent = (labelInput.value || '').trim() || 'Contact Label Preview';
        valuePreview.textContent = (valueInput.value || '').trim() || 'Contact value preview';
        typePreview.textContent = (typeInput.value || 'text').trim();
        valueHelp.textContent = getTypeHint(typeInput.value || 'text');
    }

    [labelInput, valueInput, typeInput].forEach(function (el) {
        if (!el) return;
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });
    updatePreview();
})();
</script>
@endsection
