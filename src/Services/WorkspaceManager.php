<?php

namespace Nawasara\Ui\Services;

use Illuminate\Support\Str;

/**
 * Workspace = one top-level menu group (Cloudflare, WHM, Keycloak, etc.).
 *
 * Each workspace is derived from the merged menu config. Workspace identifier
 * is the URL prefix of its first submenu item (e.g. "nawasara-cloudflare").
 */
class WorkspaceManager
{
    /**
     * All workspaces, ignoring permissions.
     */
    public function all(): array
    {
        $menus = app()->bound('nawasara.menu') ? app('nawasara.menu') : [];
        $workspaces = [];

        foreach ($menus as $menu) {
            $workspace = $this->toWorkspace($menu);
            if ($workspace) {
                $workspaces[$workspace['id']] = $workspace;
            }
        }

        return $workspaces;
    }

    /**
     * Workspaces the current user can access (by permission).
     */
    public function accessible(): array
    {
        $user = auth()->user();

        return collect($this->all())
            ->filter(function ($ws) use ($user) {
                if (empty($ws['permission'])) {
                    return true;
                }
                return $user && $user->can($ws['permission']);
            })
            ->all();
    }

    /**
     * Detect the active workspace from the current request URL.
     */
    public function current(): ?array
    {
        $path = trim(request()->path(), '/');
        if ($path === '' || $path === '/') {
            return null;
        }

        foreach ($this->all() as $ws) {
            if (str_starts_with($path, $ws['id'])) {
                return $ws;
            }
        }

        return null;
    }

    public function currentId(): ?string
    {
        return $this->current()['id'] ?? null;
    }

    /**
     * Submenu items for the current workspace (filtered by user permissions).
     * Returns full top-level menu item for sidebar rendering.
     */
    public function currentMenu(): ?array
    {
        $current = $this->current();
        if (! $current) {
            return null;
        }

        // Pull full raw menu entry for this workspace.
        $menus = app()->bound('nawasara.menu') ? app('nawasara.menu') : [];
        foreach ($menus as $menu) {
            $ws = $this->toWorkspace($menu);
            if ($ws && $ws['id'] === $current['id']) {
                return $menu;
            }
        }

        return null;
    }

    /**
     * Convert a raw menu entry into workspace metadata.
     */
    protected function toWorkspace(array $menu): ?array
    {
        // Workspace id = URL prefix of first submenu item (e.g. "nawasara-cloudflare").
        $prefix = $this->extractPrefix($menu);
        if (! $prefix) {
            return null;
        }

        $submenuCount = count(array_filter(
            $menu['submenu'] ?? [],
            fn ($sub) => ! empty($sub['url'])
        ));

        return [
            'id' => $prefix,
            'label' => $menu['label'] ?? Str::headline($prefix),
            'icon' => $menu['icon'] ?? 'lucide-box',
            'permission' => $menu['permission'] ?? null,
            'first_url' => $this->firstAccessibleUrl($menu),
            'submenu_count' => $submenuCount,
        ];
    }

    /**
     * Extract URL prefix from first submenu (e.g. "nawasara-cloudflare").
     */
    protected function extractPrefix(array $menu): ?string
    {
        $firstUrl = null;
        foreach ($menu['submenu'] ?? [] as $sub) {
            if (! empty($sub['url'])) {
                $firstUrl = $sub['url'];
                break;
            }
        }

        if (! $firstUrl) {
            return null;
        }

        // Parse path from full URL, take first segment.
        $path = parse_url($firstUrl, PHP_URL_PATH) ?? $firstUrl;
        $path = trim($path, '/');
        $segments = explode('/', $path);

        return $segments[0] ?? null;
    }

    /**
     * Get the first submenu URL the current user has permission to access.
     */
    protected function firstAccessibleUrl(array $menu): ?string
    {
        $user = auth()->user();

        foreach ($menu['submenu'] ?? [] as $sub) {
            if (empty($sub['url'])) {
                continue;
            }

            $permission = $sub['permission'] ?? null;
            if (! $permission || ($user && $user->can($permission))) {
                return $sub['url'];
            }
        }

        // Fallback to first URL even if no permission (will be blocked by middleware).
        foreach ($menu['submenu'] ?? [] as $sub) {
            if (! empty($sub['url'])) {
                return $sub['url'];
            }
        }

        return null;
    }
}
