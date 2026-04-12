@php
    $items = old('items', isset($quotation) ? $quotation->items->map(function ($item) {
        return [
            'item_name' => $item->item_name,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'total_price' => $item->total_price,
        ];
    })->toArray() : []);

    if (empty($items)) {
        $items = [[
            'item_name' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'total_price' => 0,
        ]];
    }
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
        <div>
            <label>Client Name</label>
            <input type="text" name="client_name" value="{{ old('client_name', $quotation->client_name ?? '') }}" required>
        </div>
        <div>
            <label>Client Email</label>
            <input type="email" name="client_email" value="{{ old('client_email', $quotation->client_email ?? '') }}" required>
        </div>
        <div>
            <label>Client Phone</label>
            <input type="text" name="client_phone" value="{{ old('client_phone', $quotation->client_phone ?? '') }}">
        </div>
        <div>
            <label>Timeline</label>
            <input type="text" name="timeline" value="{{ old('timeline', $quotation->timeline ?? '') }}" placeholder="e.g. 4-6 weeks">
        </div>
        <div style="grid-column:1/-1;">
            <label>Client Address</label>
            <input type="text" name="client_address" value="{{ old('client_address', $quotation->client_address ?? '') }}">
        </div>
        <div style="grid-column:1/-1;">
            <label>Project Title</label>
            <input type="text" name="project_title" value="{{ old('project_title', $quotation->project_title ?? '') }}" required>
        </div>
        <div style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description', $quotation->description ?? '') }}</textarea>
        </div>
        <div>
            <label>Scope of Work</label>
            <textarea name="scope_of_work" rows="4">{{ old('scope_of_work', $quotation->scope_of_work ?? '') }}</textarea>
        </div>
        <div>
            <label>Deliverables</label>
            <textarea name="deliverables" rows="4">{{ old('deliverables', $quotation->deliverables ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
        <h3 style="margin:0;color:var(--primary-dark);">Quotation Items</h3>
        <button type="button" class="btn btn-secondary" id="add-item-btn">+ Add Item</button>
    </div>

    <div id="quotation-items-wrapper"></div>

    <template id="quotation-item-template">
        <div class="quotation-item" style="border:1px solid #e5e8eb;border-radius:12px;padding:14px;margin-bottom:12px;">
            <div style="display:grid;grid-template-columns:2fr 1.2fr 1fr 1fr 1fr auto;gap:10px;align-items:end;">
                <div>
                    <label>Item Name</label>
                    <input type="text" data-name="item_name" required>
                </div>
                <div>
                    <label>Description</label>
                    <input type="text" data-name="description">
                </div>
                <div>
                    <label>Quantity</label>
                    <input type="number" min="0.01" step="0.01" data-name="quantity" class="calc-field" required>
                </div>
                <div>
                    <label>Unit Price</label>
                    <input type="number" min="0" step="0.01" data-name="unit_price" class="calc-field" required>
                </div>
                <div>
                    <label>Total</label>
                    <input type="number" min="0" step="0.01" data-name="total_price" class="line-total" readonly>
                </div>
                <button type="button" class="btn remove-item-btn" style="background:#dc3545;">Delete</button>
            </div>
        </div>
    </template>
</div>

<div class="card">
    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;">
        <div>
            <label>Subtotal</label>
            <input type="number" name="subtotal" id="subtotal" min="0" step="0.01" value="{{ old('subtotal', $quotation->subtotal ?? 0) }}" required readonly>
        </div>
        <div>
            <label>Tax</label>
            <input type="number" name="tax" id="tax" min="0" step="0.01" value="{{ old('tax', $quotation->tax ?? 0) }}">
        </div>
        <div>
            <label>Total Amount</label>
            <input type="number" name="total_amount" id="total_amount" min="0" step="0.01" value="{{ old('total_amount', $quotation->total_amount ?? 0) }}" required readonly>
        </div>
        <div>
            <label>Status</label>
            <select name="status" required>
                @foreach(['draft' => 'Draft', 'approved' => 'Approved', 'sent' => 'Sent'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $quotation->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<script>
(function () {
    const initialItems = @json($items);
    const wrapper = document.getElementById('quotation-items-wrapper');
    const template = document.getElementById('quotation-item-template');
    const addBtn = document.getElementById('add-item-btn');
    const taxInput = document.getElementById('tax');
    const subtotalInput = document.getElementById('subtotal');
    const totalAmountInput = document.getElementById('total_amount');

    function toNumber(value) {
        const n = parseFloat(value);
        return Number.isFinite(n) ? n : 0;
    }

    function recalculateTotals() {
        const rows = wrapper.querySelectorAll('.quotation-item');
        let subtotal = 0;

        rows.forEach((row) => {
            const qty = toNumber(row.querySelector('[data-name="quantity"]').value);
            const unit = toNumber(row.querySelector('[data-name="unit_price"]').value);
            const line = qty * unit;
            row.querySelector('[data-name="total_price"]').value = line.toFixed(2);
            subtotal += line;
        });

        subtotalInput.value = subtotal.toFixed(2);
        const tax = toNumber(taxInput.value);
        totalAmountInput.value = (subtotal + tax).toFixed(2);
    }

    function updateNames() {
        const rows = wrapper.querySelectorAll('.quotation-item');
        rows.forEach((row, index) => {
            row.querySelectorAll('[data-name]').forEach((input) => {
                input.name = `items[${index}][${input.dataset.name}]`;
            });
        });
    }

    function createRow(data = {}) {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.quotation-item');

        row.querySelector('[data-name="item_name"]').value = data.item_name ?? '';
        row.querySelector('[data-name="description"]').value = data.description ?? '';
        row.querySelector('[data-name="quantity"]').value = data.quantity ?? 1;
        row.querySelector('[data-name="unit_price"]').value = data.unit_price ?? 0;
        row.querySelector('[data-name="total_price"]').value = data.total_price ?? 0;

        row.querySelectorAll('.calc-field').forEach((field) => {
            field.addEventListener('input', recalculateTotals);
        });

        row.querySelector('.remove-item-btn').addEventListener('click', () => {
            if (wrapper.querySelectorAll('.quotation-item').length > 1) {
                row.remove();
                updateNames();
                recalculateTotals();
            }
        });

        wrapper.appendChild(row);
        updateNames();
        recalculateTotals();
    }

    addBtn.addEventListener('click', () => createRow());
    taxInput.addEventListener('input', recalculateTotals);

    initialItems.forEach((item) => createRow(item));
})();
</script>
