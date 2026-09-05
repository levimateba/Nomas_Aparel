@php
    /** @var \App\Models\Employee $employee */
    $isEdit = $employee->exists;
@endphp

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    {{-- Photo --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Photo</h3>
        <p class="mt-1 text-xs text-gray-500">JPG, PNG or WebP · max 2 MB</p>
        <div class="mt-4 flex flex-col items-center gap-3">
            <div class="flex h-36 w-36 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-white/[0.02]">
                @if($employee->photoUrl())
                    <img id="emp-photo-preview" src="{{ $employee->photoUrl() }}" alt="" class="h-full w-full object-cover">
                @else
                    <img id="emp-photo-preview" src="" alt="" class="hidden h-full w-full object-cover">
                    <span id="emp-photo-placeholder" class="text-xs text-gray-400">No photo</span>
                @endif
            </div>
            <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="ta-input" onchange="previewEmployeePhoto(this)">
            @if($employee->photoUrl())
                <label class="ta-check">
                    <input type="checkbox" name="remove_photo" value="1">
                    <span>Remove photo</span>
                </label>
            @endif
        </div>
    </div>

    <div class="space-y-5 xl:col-span-2">
        {{-- Identity --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Personal details</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <div class="ta-field">
                    <label>Employee number *</label>
                    <input type="text" name="employee_number" class="ta-input" required value="{{ old('employee_number', $employee->employee_number) }}">
                </div>
                <div class="ta-field">
                    <label>Gender</label>
                    <select name="gender" class="ta-select">
                        <option value="">—</option>
                        @foreach(\App\Models\Employee::GENDERS as $key => $label)
                            <option value="{{ $key }}" @selected(old('gender', $employee->gender) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ta-field">
                    <label>First name *</label>
                    <input type="text" name="first_name" class="ta-input" required value="{{ old('first_name', $employee->first_name) }}">
                </div>
                <div class="ta-field">
                    <label>Last name *</label>
                    <input type="text" name="last_name" class="ta-input" required value="{{ old('last_name', $employee->last_name) }}">
                </div>
                <div class="ta-field sm:col-span-2">
                    <label>Other names</label>
                    <input type="text" name="other_names" class="ta-input" value="{{ old('other_names', $employee->other_names) }}">
                </div>
                <div class="ta-field">
                    <label>Date of birth</label>
                    <input type="date" name="date_of_birth" class="ta-input" value="{{ old('date_of_birth', optional($employee->date_of_birth)->format('Y-m-d')) }}">
                </div>
                <div class="ta-field">
                    <label>National ID / Passport</label>
                    <input type="text" name="national_id" class="ta-input" value="{{ old('national_id', $employee->national_id) }}">
                </div>
                <div class="ta-field">
                    <label>KRA PIN</label>
                    <input type="text" name="kra_pin" class="ta-input" value="{{ old('kra_pin', $employee->kra_pin) }}">
                </div>
                <div class="ta-field">
                    <label>NHIF number</label>
                    <input type="text" name="nhif_number" class="ta-input" value="{{ old('nhif_number', $employee->nhif_number) }}">
                </div>
                <div class="ta-field">
                    <label>NSSF number</label>
                    <input type="text" name="nssf_number" class="ta-input" value="{{ old('nssf_number', $employee->nssf_number) }}">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Contact &amp; address</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <div class="ta-field">
                    <label>Phone</label>
                    <input type="text" name="phone" class="ta-input" value="{{ old('phone', $employee->phone) }}">
                </div>
                <div class="ta-field">
                    <label>Alt phone</label>
                    <input type="text" name="alt_phone" class="ta-input" value="{{ old('alt_phone', $employee->alt_phone) }}">
                </div>
                <div class="ta-field sm:col-span-2">
                    <label>Email</label>
                    <input type="email" name="email" class="ta-input" value="{{ old('email', $employee->email) }}" placeholder="Required if creating a login">
                </div>
                <div class="ta-field sm:col-span-2">
                    <label>Address</label>
                    <input type="text" name="address" class="ta-input" value="{{ old('address', $employee->address) }}">
                </div>
                <div class="ta-field">
                    <label>City</label>
                    <input type="text" name="city" class="ta-input" value="{{ old('city', $employee->city) }}">
                </div>
                <div class="ta-field">
                    <label>County</label>
                    <input type="text" name="county" class="ta-input" value="{{ old('county', $employee->county) }}">
                </div>
                <div class="ta-field">
                    <label>Postal code</label>
                    <input type="text" name="postal_code" class="ta-input" value="{{ old('postal_code', $employee->postal_code) }}">
                </div>
            </div>
        </div>

        {{-- Job --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Employment</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <div class="ta-field">
                    <label>Department</label>
                    <input type="text" name="department" class="ta-input" value="{{ old('department', $employee->department) }}" placeholder="e.g. Sales, Warehouse">
                </div>
                <div class="ta-field">
                    <label>Job title</label>
                    <input type="text" name="job_title" class="ta-input" value="{{ old('job_title', $employee->job_title) }}" placeholder="e.g. Cashier">
                </div>
                <div class="ta-field">
                    <label>Employment type</label>
                    <select name="employment_type" class="ta-select">
                        <option value="">—</option>
                        @foreach(\App\Models\Employee::EMPLOYMENT_TYPES as $key => $label)
                            <option value="{{ $key }}" @selected(old('employment_type', $employee->employment_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ta-field">
                    <label>Basic salary (KES)</label>
                    <input type="number" step="0.01" min="0" name="basic_salary" class="ta-input" value="{{ old('basic_salary', $employee->basic_salary) }}">
                </div>
                <div class="ta-field">
                    <label>Hire date</label>
                    <input type="date" name="hire_date" class="ta-input" value="{{ old('hire_date', optional($employee->hire_date)->format('Y-m-d')) }}">
                </div>
                <div class="ta-field">
                    <label>Termination date</label>
                    <input type="date" name="termination_date" class="ta-input" value="{{ old('termination_date', optional($employee->termination_date)->format('Y-m-d')) }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="ta-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employee->is_active ?? true))>
                        <span>Active employee</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Bank & emergency --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Bank &amp; emergency contact</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <div class="ta-field">
                    <label>Bank name</label>
                    <input type="text" name="bank_name" class="ta-input" value="{{ old('bank_name', $employee->bank_name) }}">
                </div>
                <div class="ta-field">
                    <label>Bank branch</label>
                    <input type="text" name="bank_branch" class="ta-input" value="{{ old('bank_branch', $employee->bank_branch) }}">
                </div>
                <div class="ta-field sm:col-span-2">
                    <label>Account number</label>
                    <input type="text" name="bank_account" class="ta-input" value="{{ old('bank_account', $employee->bank_account) }}">
                </div>
                <div class="ta-field">
                    <label>Emergency contact name</label>
                    <input type="text" name="emergency_contact_name" class="ta-input" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                </div>
                <div class="ta-field">
                    <label>Emergency contact phone</label>
                    <input type="text" name="emergency_contact_phone" class="ta-input" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                </div>
                <div class="ta-field sm:col-span-2">
                    <label>Relationship</label>
                    <input type="text" name="emergency_contact_relation" class="ta-input" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}" placeholder="e.g. Spouse, Parent">
                </div>
                <div class="ta-field sm:col-span-2">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" class="ta-input">{{ old('notes', $employee->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- System user --}}
        @if(! $employee->user_id)
            <div class="rounded-2xl border border-brand-200 bg-brand-50/40 dark:border-brand-500/20 dark:bg-brand-500/5" x-data="{ asUser: {{ old('create_as_user') ? 'true' : 'false' }} }">
                <div class="border-b border-brand-100 px-5 py-4 dark:border-brand-500/20">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">System login</h3>
                    <p class="mt-1 text-xs text-gray-600">Mark as a user to generate admin/POS login credentials.</p>
                </div>
                <div class="space-y-4 p-5">
                    <label class="ta-check">
                        <input type="hidden" name="create_as_user" value="0">
                        <input type="checkbox" name="create_as_user" value="1" x-model="asUser" @checked(old('create_as_user'))>
                        <span>Create login account for this employee</span>
                    </label>
                    <div class="ta-field max-w-md" x-show="asUser" x-cloak>
                        <label>Role *</label>
                        <select name="role_id" class="ta-select" :required="asUser">
                            <option value="">Select role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">A temporary password will be generated and shown once after save.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-success-200 bg-success-50/50 px-5 py-4 text-sm text-success-800 dark:border-success-500/20">
                Linked system user: <strong>{{ $employee->user?->email }}</strong>
                · <a href="{{ route('admin.users.edit', $employee->user) }}" class="font-semibold underline">Edit user</a>
            </div>
        @endif
    </div>
</div>

<script>
function previewEmployeePhoto(input) {
    const img = document.getElementById('emp-photo-preview');
    const placeholder = document.getElementById('emp-photo-placeholder');
    if (!img || !input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        img.src = e.target.result;
        img.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
