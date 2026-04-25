<?php

namespace Nawasara\Ui\Services;

use Illuminate\Support\Str;

/**
 * Workspace = one top-level menu group (Cloudflare, WHM, Keycloak, Pengaturan, etc.).
 *
 * Workspace identifier priority:
 *   1. Explicit 'workspace' key in menu config (recommended — supports multiple
 *      workspaces from the same package, e.g. "user-management" + "settings"
 *      both under nawasara-core URL prefix).
 *   2. Fallback: URL prefix of the first submenu item (e.g. "nawasara-cloudflare").
 *
 * Active workspace detection compares the current request path against every
 * submenu URL in each workspace — so workspaces sharing a URL prefix still
 * resolve correctly.
 */
class WorkspaceManager
{
    public function all(): array
    {
        $menus = app()->bound('nawasara.menu') ? app('nawasara.menu') : [];
        $workspaces = [];

        foreach ($menus as $menu) {
            $workspace = $this->toWorkspace($menu);
            if (! $workspace) {
                continue;
            }

            $id = $workspace['id'];

            if (isset($workspaces[$id])) {
                // Merge submenu and paths into the existing workspace so multiple
                // packages can contribute to a shared workspace (e.g. several
                // packages adding pages under "Pengaturan").
                $workspaces[$id]['menu']['submenu'] = array_merge(
                    $workspaces[$id]['menu']['submenu'] ?? [],
                    $workspace['menu']['submenu'] ?? [],
                );
                $workspaces[$id]['paths'] = array_values(array_unique(array_merge(
                    $workspaces[$id]['paths'],
                    $workspace['paths'],
                )));
                $workspaces[$id]['submenu_count'] += $workspace['submenu_count'];
                // Keep first-declared label/icon/permission/first_url; only
                // adopt new first_url if existing one is null.
                if (! $workspaces[$id]['first_url']) {
                    $workspaces[$id]['first_url'] = $workspace['first_url'];
                }
            } else {
                $workspaces[$id] = $workspace;
            }
        }

        return $workspaces;
    }

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
     *
     * Strategy: find the workspace whose submenu URLs most specifically match
     * the current path (longest matching path wins).
     */
    public function current(): ?array
    {
        $path = trim(request()->path(), '/');
        if ($path === '' || $path === '/') {
            return null;
        }

        $best = null;
        $bestLength = 0;

        foreach ($this->all() as $ws) {
            foreach ($ws['paths'] as $wsPath) {
                if ($wsPath !== '' && str_starts_with($path, $wsPath) && strlen($wsPath) > $bestLength) {
                    $best = $ws;
                    $bestLength = strlen($wsPath);
                }
            }
        }

        return $best;
    }

    public function currentId(): ?string
    {
        return $this->current()['id'] ?? null;
    }

    /**
     * Full raw menu entry for the current workspace (for sidebar rendering).
     */
    public function currentMenu(): ?array
    {
        return $this->current()['menu'] ?? null;
    }

    /**
     * Convert a raw menu entry into workspace metadata.
     */
    protected function toWorkspace(array $menu): ?array
    {
        $paths = $this->extractPaths($menu);

        if (empty($paths)) {
            return null;
        }

        // Explicit workspace id wins. Fallback: first-segment of first URL path.
        $id = $menu['workspace'] ?? null;
        if (! $id) {
            $id = explode('/', $paths[0])[0] ?? null;
        }

        if (! $id) {
            return null;
        }

        $submenuCount = count(array_filter(
            $menu['submenu'] ?? [],
            fn ($sub) => ! empty($sub['url'])
        ));

        return [
            'id' => $id,
            'label' => $menu['label'] ?? Str::headline($id),
            'icon' => $menu['icon'] ?? 'lucide-box',
            'permission' => $menu['permission'] ?? null,
            'first_url' => $this->firstAccessibleUrl($menu),
            'submenu_count' => $submenuCount,
            'paths' => $paths,
            'menu' => $menu,
        ];
    }

    /**
     * Extract normalized URL paths (no host, no leading slash) from all submenu
     * items. Used both for prefix detection and for matching current URL.
     */
    protected function extractPaths(array $menu): array
    {
        $paths = [];
        foreach ($menu['submenu'] ?? [] as $sub) {
            if (empty($sub['url'])) {
                continue;
            }
            $path = parse_url($sub['url'], PHP_URL_PATH) ?? $sub['url'];
            $path = trim($path, '/');
            if ($path !== '') {
                $paths[] = $path;
            }
        }
        return $paths;
    }

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

        foreach ($menu['submenu'] ?? [] as $sub) {
            if (! empty($sub['url'])) {
                return $sub['url'];
            }
        }

        return null;
    }
}
