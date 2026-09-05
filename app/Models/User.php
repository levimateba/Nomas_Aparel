<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'job_title',
        'phone',
        'employee_code',
        'password',
        'is_admin',
        'role_id',
        'user_group_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function group()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id');
    }

    public function isFullAdmin(): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->rolesCollection()->contains(fn (Role $role) => $this->roleIsFullAdmin($role));
    }

    public function isAdminUser(): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }

        return $this->allRoleNames()
            ->map(fn ($name) => Str::lower($name))
            ->intersect(['admin', 'super admin', 'manager', 'store manager', 'staff', 'cashier', 'stock manager'])
            ->isNotEmpty()
            || $this->rolesCollection()->contains(fn (Role $role) => in_array($role->slug, [
                'admin', 'super-admin', 'manager', 'staff', 'cashier', 'stock-manager',
            ], true));
    }

    /**
     * Front-line cashier: can sell, cannot manage catalogue/settings/inventory.
     */
    public function isFrontlineCashier(): bool
    {
        if ($this->isFullAdmin()) {
            return false;
        }

        return $this->hasPermission('create_sale')
            && ! $this->hasPermission('manage_products')
            && ! $this->hasPermission('manage_system_settings')
            && ! $this->hasPermission('view_inventory')
            && ! $this->hasPermission('manage_purchases');
    }

    /**
     * Stock-focused user without POS selling rights.
     */
    public function isStockKeeper(): bool
    {
        if ($this->isFullAdmin() || $this->hasPermission('create_sale')) {
            return false;
        }

        return $this->hasPermission('view_inventory') || $this->hasPermission('manage_products');
    }

    public function preferredAdminHomeRoute(): string
    {
        if ($this->isFrontlineCashier()) {
            return 'admin.cashier.home';
        }

        if ($this->isStockKeeper() && $this->hasPermission('view_inventory')) {
            return 'admin.stock-overview.index';
        }

        if ($this->isStockKeeper() && $this->hasPermission('manage_products')) {
            return 'admin.products.index';
        }

        if ($this->hasPermission('view_dashboard')) {
            return 'admin.dashboard';
        }

        if ($this->hasPermission('create_sale')) {
            return 'admin.cashier.home';
        }

        return 'admin.dashboard';
    }

    public function hasAnyRole(array $names): bool
    {
        $need = collect($names)->map(fn ($name) => Str::lower($name));

        return $this->allRoleNames()
            ->map(fn ($name) => Str::lower($name))
            ->intersect($need)
            ->isNotEmpty();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }

        foreach ($this->rolesCollection() as $role) {
            $perms = $role->relationLoaded('permissions')
                ? $role->permissions
                : $role->permissions()->get(['name', 'slug']);

            if ($perms->contains(fn ($p) => $p->name === $permission || $p->slug === $permission)) {
                return true;
            }
        }

        return false;
    }

    public function applyRoleIds(array $roleIds): void
    {
        $ids = collect($roleIds)->filter()->unique()->values()->all();
        $this->roles()->sync($ids);
        $roles = Role::query()->whereIn('id', $ids)->get();
        $primary = $roles->firstWhere('id', $this->role_id) ?: $roles->first();

        $this->forceFill([
            'role_id' => $primary?->id,
            'is_admin' => $roles->contains(fn (Role $role) => $this->roleIsFullAdmin($role)),
        ])->save();
    }

    public function allRoleNames()
    {
        return $this->rolesCollection()
            ->pluck('name')
            ->filter()
            ->unique()
            ->values();
    }

    public function rolesCollection()
    {
        $roles = $this->relationLoaded('roles') ? $this->roles : $this->roles()->get();

        return collect([$this->role])
            ->merge($roles)
            ->filter()
            ->unique('id')
            ->values();
    }

    private function roleIsFullAdmin(Role $role): bool
    {
        $name = Str::lower($role->name);

        // Only Super Admin bypasses permission checks. Role "Admin" uses its assigned permissions.
        return in_array($name, ['super admin'], true)
            || in_array($role->slug, ['super-admin'], true);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cashierShifts(): HasMany
    {
        return $this->hasMany(CashierShift::class);
    }

    public function avatarStoragePath(): ?string
    {
        $path = method_exists($this, 'getRawOriginal')
            ? $this->getRawOriginal('avatar')
            : ($this->attributes['avatar'] ?? null);
        $path = trim((string) ($path ?: ''));

        return $path !== '' ? $path : null;
    }

    public function avatarUrl(): ?string
    {
        $path = $this->avatarStoragePath();
        if (! $path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/storage/')
        ) {
            return $path;
        }

        return \App\Support\PublicStorageUrl::fromPath($path);
    }

    public function initials(): string
    {
        return strtoupper(substr((string) $this->name, 0, 1)) ?: 'U';
    }

    public function ensureEmployeeCode(): string
    {
        if (filled($this->employee_code)) {
            return (string) $this->employee_code;
        }

        $code = 'EMP-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
        $this->forceFill(['employee_code' => $code])->save();

        return $code;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
