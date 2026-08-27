<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            ->intersect(['admin', 'super admin', 'manager', 'staff', 'cashier', 'stock manager'])
            ->isNotEmpty();
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

        $matches = function ($query) use ($permission) {
            $query->where(function ($inner) use ($permission) {
                $inner->where('name', $permission)->orWhere('slug', $permission);
            });
        };

        if ($this->role && $this->role->permissions()->where($matches)->exists()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', $matches)
            ->exists();
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

        return in_array($name, ['admin', 'super admin'], true)
            || in_array($role->slug, ['admin', 'super-admin'], true);
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
}
