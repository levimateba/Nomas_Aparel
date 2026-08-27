<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isAdminUser()) {
            return redirect()->route('admin.login');
        }

        $user->loadMissing(['roles.permissions', 'role.permissions']);

        $routeName = $request->route()?->getName();
        $required = $this->requiredPermission($routeName);

        if ($required === null || $user->isFullAdmin()) {
            return $next($request);
        }

        if ($required === '__full_admin__') {
            abort(403, 'You do not have access to this section.');
        }

        if (! $user->hasPermission($required)) {
            abort(403, 'You do not have permission to do that.');
        }

        return $next($request);
    }

    private function requiredPermission(?string $routeName): ?string
    {
        if (! $routeName) {
            return '__full_admin__';
        }

        $routes = config('admin_permissions.routes', []);
        if (array_key_exists($routeName, $routes)) {
            return $routes[$routeName];
        }

        $prefixes = config('admin_permissions.prefixes', []);
        uksort($prefixes, fn ($a, $b) => strlen((string) $b) <=> strlen((string) $a));
        foreach ($prefixes as $prefix => $permission) {
            if (str_starts_with($routeName, $prefix)) {
                return $permission;
            }
        }

        return '__full_admin__';
    }
}
