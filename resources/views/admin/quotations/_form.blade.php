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

<div class="quotation-form-layout">
    <section class="quotation-section">
        <h3>Client Information</h3>
        <p class="quotation-sub">Capture contact details for the customer receiving this quotation.</p>

        <div class="quotation-grid quotation-grid-two">
            <div class="quotation-field">
                <label for="quotation-client-name">Client Name</label>
                <input id="quotation-client-name" class="quotation-input @error('client_name') is-invalid @enderror" type="text" name="client_name" value="{{ old('client_name', $quotation->client_name ?? '') }}" required>
                @error('client_name')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="quotation-client-email">Client Email</label>
                <input id="quotation-client-email" class="quotation-input @error('client_email') is-invalid @enderror" type="email" name="client_email" value="{{ old('client_email', $quotation->client_email ?? '') }}" required>
                @error('client_email')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="quotation-client-phone">Client Phone</label>
                <input id="quotation-client-phone" class="quotation-input @error('client_phone') is-invalid @enderror" type="text" name="client_phone" value="{{ old('client_phone', $quotation->client_phone ?? '') }}">
                @error('client_phone')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="quotation-timeline">Timeline</label>
                <input id="quotation-timeline" class="quotation-input @error('timeline') is-invalid @enderror" type="text" name="timeline" value="{{ old('timeline', $quotation->timeline ?? '') }}" placeholder="e.g. 4-6 weeks">
                @error('timeline')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field quotation-span-all">
                <label for="quotation-client-address">Client Address</label>
                <input id="quotation-client-address" class="quotation-input @error('client_address') is-invalid @enderror" type="text" name="client_address" value="{{ old('client_address', $quotation->client_address ?? '') }}">
                @error('client_address')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </section>

    <section class="quotation-section">
        <h3>Project Details</h3>
        <p class="quotation-sub">Define project scope and delivery expectations before adding line items.</p>

        <div class="quotation-grid quotation-grid-two">
            <div class="quotation-field quotation-span-all">
                <label for="quotation-project-title">Project Title</label>
                <input id="quotation-project-title" class="quotation-input @error('project_title') is-invalid @enderror" type="text" name="project_title" value="{{ old('project_title', $quotation->project_title ?? '') }}" required>
                @error('project_title')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field quotation-span-all">
                <label for="quotation-description">Description</label>
                <textarea id="quotation-description" class="quotation-input @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $quotation->description ?? '') }}</textarea>
                @error('description')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="quotation-scope">Scope of Work</label>
                <textarea id="quotation-scope" class="quotation-input @error('scope_of_work') is-invalid @enderror" name="scope_of_work" rows="4">{{ old('scope_of_work', $quotation->scope_of_work ?? '') }}</textarea>
                @error('scope_of_work')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="quotation-deliverables">Deliverables</label>
                <textarea id="quotation-deliverables" class="quotation-input @error('deliverables') is-invalid @enderror" name="deliverables" rows="4">{{ old('deliverables', $quotation->deliverables ?? '') }}</textarea>
                @error('deliverables')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </section>

    <section class="quotation-section">
        <div class="quotation-section-head">
            <div>
                <h3>Quotation Items</h3>
                <p class="quotation-sub" style="margin-bottom:0;">Add billable line items and quantities for automatic totals.</p>
            </div>
            <button type="button" class="quotation-btn quotation-btn-secondary" id="add-item-btn">+ Add Item</button>
        </div>

        <div id="quotation-items-wrapper" class="quotation-items-wrapper"></div>

        <template id="quotation-item-template">
            <article class="quotation-item">
                <div class="quotation-item-grid">
                    <div class="quotation-field">
                        <label>Item Name</label>
                        <input class="quotation-input" type="text" data-name="item_name" required>
                    </div>
                    <div class="quotation-field">
                        <label>Description</label>
                        <input class="quotation-input" type="text" data-name="description">
                    </div>
                    <div class="quotation-field">
                        <label>Quantity</label>
                        <input class="quotation-input calc-field" type="number" min="0.01" step="0.01" data-name="quantity" required>
                    </div>
                    <div class="quotation-field">
                        <label>Unit Price</label>
                        <input class="quotation-input calc-field" type="number" min="0" step="0.01" data-name="unit_price" required>
                    </div>
                    <div class="quotation-field">
                        <label>Total</label>
                        <input class="quotation-input line-total" type="number" min="0" step="0.01" data-name="total_price" readonly>
                    </div>
                    <button type="button" class="quotation-btn quotation-btn-danger remove-item-btn">Delete</button>
                </div>
            </article>
        </template>
    </section>

    <section class="quotation-section">
        <h3>Totals & Status</h3>
        <p class="quotation-sub">Review final financial values before saving.</p>

        <div class="quotation-grid quotation-grid-four">
            <div class="quotation-field">
                <label for="subtotal">Subtotal</label>
                <input class="quotation-input @error('subtotal') is-invalid @enderror" type="number" name="subtotal" id="subtotal" min="0" step="0.01" value="{{ old('subtotal', $quotation->subtotal ?? 0) }}" required readonly>
                @error('subtotal')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="tax">Tax</label>
                <input class="quotation-input @error('tax') is-invalid @enderror" type="number" name="tax" id="tax" min="0" step="0.01" value="{{ old('tax', $quotation->tax ?? 0) }}">
                @error('tax')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="total_amount">Total Amount</label>
                <input class="quotation-input @error('total_amount') is-invalid @enderror" type="number" name="total_amount" id="total_amount" min="0" step="0.01" value="{{ old('total_amount', $quotation->total_amount ?? 0) }}" required readonly>
                @error('total_amount')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
            <div class="quotation-field">
                <label for="quotation-status">Status</label>
                <select id="quotation-status" class="quotation-input @error('status') is-invalid @enderror" name="status" required>
                    @foreach(['draft' => 'Draft', 'approved' => 'Approved', 'sent' => 'Sent'] as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $quotation->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <small class="quotation-error">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </section>
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
        const number = parseFloat(value);
        return Number.isFinite(number) ? number : 0;
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
        wrapper.querySelectorAll('.quotation-item').forEach((row, index) => {
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
            if (wrapper.querySelectorAll('.quotation-item').length <= 1) {
                return;
            }
            row.remove();
            updateNames();
            recalculateTotals();
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
