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
    /**
     * Display order of the sidebar groups. A workspace's 'group' key (from its
     * menu config) places it under one of these headings; anything without a
     * group falls into 'Lainnya' at the end. Groups with no accessible
     * workspaces are skipped at render time.
     */
    public const GROUP_ORDER = [
        'Layanan',
        'Observability',
        'Keamanan',
        'Aset & Registry',
        'Pengaturan',
        'Lainnya',
    ];

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

        // Entri tanpa `url` bukan tautan — ia penanda seksi (['section' =>
        // 'Hibah']) yang dirender sidebar sebagai judul di dalam workspace.
        // Tidak ikut dihitung: badge jumlah menu harus menyebut halaman yang
        // dapat dibuka, bukan judulnya.
        $submenuCount = count(array_filter(
            $menu['submenu'] ?? [],
            fn ($sub) => ! empty($sub['url'])
        ));

        return [
            'id' => $id,
            'label' => $menu['label'] ?? Str::headline($id),
            'icon' => $menu['icon'] ?? 'lucide-box',
            'group' => $menu['group'] ?? null,
            'permission' => $menu['permission'] ?? null,
            'first_url' => $this->firstAccessibleUrl($menu),
            'submenu_count' => $submenuCount,
            'paths' => $paths,
            'menu' => $menu,
        ];
    }

    /**
     * Accessible workspaces organised into sidebar groups.
     *
     * Returns an ordered map: group label => list of workspaces, following
     * GROUP_ORDER. A workspace with no 'group' (or an unknown one) lands in
     * 'Lainnya'. Within each group, workspaces are sorted by label. Empty
     * groups are omitted, so the caller can just iterate the result.
     *
     * @return array<string, array<int, array>>
     */
    public function grouped(): array
    {
        $buckets = [];

        foreach ($this->accessible() as $ws) {
            $group = $ws['group'] ?? null;
            if (! in_array($group, self::GROUP_ORDER, true)) {
                $group = 'Lainnya';
            }
            $buckets[$group][] = $ws;
        }

        $ordered = [];
        foreach (self::GROUP_ORDER as $group) {
            if (empty($buckets[$group])) {
                continue;
            }
            usort($buckets[$group], fn ($a, $b) => strcasecmp($a['label'], $b['label']));
            $ordered[$group] = $buckets[$group];
        }

        return $ordered;
    }

    /**
     * Flat, permission-filtered list of every navigable page for the command
     * palette (⌘K). One entry per accessible submenu item, carrying its parent
     * workspace label so the palette can show "Hibah › Pengajuan" context.
     *
     * Both the workspace permission and the per-item permission are honoured,
     * so an item a user can't reach never appears in search.
     *
     * @return array<int, array{label:string, url:string, icon:?string, group:string}>
     */
    public function navItems(): array
    {
        $user = auth()->user();
        $items = [];

        foreach ($this->accessible() as $ws) {
            $wsLabel = $ws['label'];
            foreach ($ws['menu']['submenu'] ?? [] as $sub) {
                if (empty($sub['url'])) {
                    continue;
                }
                $perm = $sub['permission'] ?? null;
                if ($perm && ! ($user && $user->can($perm))) {
                    continue;
                }
                $items[] = [
                    'label' => $sub['label'] ?? '',
                    'url' => $sub['url'],
                    'icon' => $sub['icon'] ?? null,
                    'group' => $wsLabel,
                ];
            }
        }

        return $items;
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
