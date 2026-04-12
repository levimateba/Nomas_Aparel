
<div class="vendor-field">
    <div class="vendor-label-row">
        <label for="vendor-name">Name</label>
        <span class="vendor-required">Required</span>
    </div>
    <input id="vendor-name" type="text" name="name" value="{{ old('name', $vendor->name ?? '') }}" maxlength="255" placeholder="e.g. Acme Corp" required>
    <small class="vendor-help">Enter the vendor's display name.</small>
</div>

<div class="vendor-field">
    <div class="vendor-label-row">
        <label for="vendor-slug">Slug (optional)</label>
    </div>
    <input id="vendor-slug" type="text" name="slug" value="{{ old('slug', $vendor->slug ?? '') }}" placeholder="auto-generated-if-empty">
    <small class="vendor-help">Leave blank to auto-generate from name.</small>
</div>

<div class="vendor-field">
    <div class="vendor-label-row">
        <label for="vendor-email">Email</label>
    </div>
    <input id="vendor-email" type="email" name="email" value="{{ old('email', $vendor->email ?? '') }}" placeholder="e.g. vendor@email.com">
    <small class="vendor-help">Contact email for notifications and communication.</small>
</div>

<div class="vendor-field">
    <div class="vendor-label-row">
        <label for="vendor-phone">Phone</label>
    </div>
    <input id="vendor-phone" type="text" name="phone" value="{{ old('phone', $vendor->phone ?? '') }}" placeholder="e.g. +1234567890">
    <small class="vendor-help">Optional phone number for vendor contact.</small>
</div>

<div class="vendor-field">
    <div class="vendor-label-row">
        <label for="vendor-commission">Commission Rate (%)</label>
        <span class="vendor-required">Required</span>
    </div>
    <input id="vendor-commission" type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $vendor->commission_rate ?? 10) }}" required>
    <small class="vendor-help">Set the commission percentage for this vendor (0-100%).</small>
</div>

<div class="vendor-field">
    <div class="vendor-label-row">
        <label for="vendor-description">Description</label>
    </div>
    <textarea id="vendor-description" name="description" rows="4" placeholder="Short description or notes">{{ old('description', $vendor->description ?? '') }}</textarea>
    <small class="vendor-help">Optional. Add a brief description or notes about this vendor.</small>
</div>

<div class="vendor-field">
    <label class="vendor-switch" for="vendor-active">
        <input id="vendor-active" type="checkbox" name="is_active" value="1" {{ old('is_active', $vendor->is_active ?? true) ? 'checked' : '' }}>
        Active vendor
    </label>
    <small class="vendor-help">Inactive vendors are hidden from selection and product assignment.</small>
</div>
