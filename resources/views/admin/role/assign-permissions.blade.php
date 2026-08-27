@extends('layouts.admin')
@section('title', 'Assign Permissions')
@section('heading', 'Assign Permissions')
@section('subheading', 'Role: '.$role->name)

@section('content')
    <div class="page-head">
        <h2>Assign permissions to {{ $role->name }}</h2>
        <a class="btn btn-secondary" href="{{ route('admin.roles.index') }}">Back to Roles</a>
    </div>

    <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}" class="perm-assign">
        @csrf
        @method('PUT')

        <div class="card">
            <label class="perm-select-all">
                <input type="checkbox" id="select-all-permissions">
                Select all permissions
            </label>
        </div>

        @foreach($permissionGroups as $group => $items)
            <section class="card perm-group">
                <div class="perm-group-head">
                    <div>
                        <strong>{{ $group }}</strong>
                        <div class="muted">{{ count($items) }} permission(s)</div>
                    </div>
                    <button type="button" class="btn btn-secondary group-select" data-group="{{ \Illuminate\Support\Str::slug($group) }}">Toggle group</button>
                </div>
                <div class="perm-catalog">
                    @foreach($items as $name => $meta)
                        @if($permission = $permissionsByName->get($name))
                            <label class="perm-item" data-group="{{ \Illuminate\Support\Str::slug($group) }}">
                                <input class="permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                <span>
                                    <span class="perm-label">{{ $meta['label'] }}</span>
                                    <span class="perm-key">{{ $name }}</span>
                                    <span class="muted">{{ $meta['description'] }}</span>
                                </span>
                            </label>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach

        @php
            $catalogNames = array_keys(config('permissions.catalog', []));
            $extraPermissions = $permissions->reject(fn ($permission) => in_array($permission->name, $catalogNames, true));
        @endphp

        @if($extraPermissions->isNotEmpty())
            <section class="card perm-group">
                <div class="perm-group-head">
                    <div>
                        <strong>Other permissions</strong>
                        <div class="muted">Not in the catalogue</div>
                    </div>
                </div>
                <div class="perm-catalog">
                    @foreach($extraPermissions as $permission)
                        <label class="perm-item">
                            <input class="permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                            <span>
                                <span class="perm-label">{{ $permission->name }}</span>
                                <span class="perm-key">{{ $permission->slug }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endif

        <button type="submit">Update Permissions</button>
        <a class="btn btn-secondary" href="{{ route('admin.roles.index') }}">Cancel</a>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const selectAll = document.getElementById('select-all-permissions');
        const checkboxes = Array.from(document.querySelectorAll('.permission-checkbox'));
        if (!selectAll || checkboxes.length === 0) return;

        function syncSelectAll() {
            const checkedCount = checkboxes.filter((cb) => cb.checked).length;
            selectAll.checked = checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        selectAll.addEventListener('change', () => {
            checkboxes.forEach((cb) => { cb.checked = selectAll.checked; });
            selectAll.indeterminate = false;
        });
        checkboxes.forEach((cb) => cb.addEventListener('change', syncSelectAll));
        document.querySelectorAll('.group-select').forEach((button) => {
            button.addEventListener('click', () => {
                const group = button.dataset.group;
                const groupBoxes = checkboxes.filter((cb) => cb.closest('[data-group]')?.dataset.group === group);
                const allChecked = groupBoxes.every((cb) => cb.checked);
                groupBoxes.forEach((cb) => { cb.checked = !allChecked; });
                syncSelectAll();
            });
        });
        syncSelectAll();
    })();
</script>
@endpush
