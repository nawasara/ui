{{--
    Sync info bar — baris kecil berisi waktu sinkronisasi terakhir, jumlah
    perubahan tertunda, dan tautan ke log.

    Pemakaian:
        <x-nawasara-ui::sync-info-bar service="keycloak" />
--}}
@props([
    /**
     * Human-friendly last-sync timestamp ("3 menit yang lalu", etc).
     * Pass `null` to render the "never synced" warning state. Most consumers
     * compute this via a Livewire #[Computed] that calls
     * `$repo->lastSyncedAt()?->diffForHumans()`.
     */
    'lastSyncedAt' => null,
    /**
     * Optional pending-sync count. Renders an animated indicator when > 0,
     * helps operators see in-flight work without opening Sync Jobs.
     */
    'pendingCount' => null,
    /**
     * URL for the "Lihat Sync Jobs →" right-side link. Defaults to the
     * global admin sync-jobs page. Pass a service-scoped URL when the
     * surrounding page is single-service (e.g. ?service=proxmox).
     */
    'syncJobsUrl' => null,
    /**
     * Override copy for the "never synced" warning. Default copy nudges
     * users to click the sync button on the same page.
     */
    'neverSyncedMessage' => 'Belum pernah di-sync. Klik "Sync Sekarang".',
])

{{-- <x-sync-info-bar> — single-line status strip showing data freshness.

     Replaces 7+ inline copies of the same "last sync · pending · sync jobs"
     pattern across DNS / Zone / Keycloak User & Client / WHM Account & Email
     / Proxmox Node. The original copies drifted: some had pending count,
     some didn't; some used 'admin/sync/jobs' some had service-scoped URLs.
     Centralising here normalises the layout and gives consumers a single
     prop surface to opt into pending count.

     Usage (basic):
       <x-nawasara-ui::sync-info-bar :lastSyncedAt="$this->lastSyncedAt" />

     With pending sync indicator:
       <x-nawasara-ui::sync-info-bar
           :lastSyncedAt="$this->lastSyncedAt"
           :pendingCount="$this->pendingCount" />

     Service-scoped Sync Jobs link:
       <x-nawasara-ui::sync-info-bar
           :lastSyncedAt="$this->lastSyncedAt"
           syncJobsUrl="{{ url('admin/sync/jobs?service=proxmox') }}" /> --}}

@php
    // Default to the global admin route when consumer doesn't pass an
    // explicit URL. We don't compute this in props because the url() helper
    // isn't available during attribute parsing.
    $resolvedSyncJobsUrl = $syncJobsUrl ?? url('admin/sync/jobs');
@endphp

<div {{ $attributes->merge(['class' => 'mb-3 flex items-center justify-between text-xs text-gray-500 dark:text-neutral-400']) }}>
    <div class="flex items-center gap-3">
        @if ($lastSyncedAt)
            <span>
                <x-lucide-clock class="size-3 inline" />
                Last sync: {{ $lastSyncedAt }}
            </span>
        @else
            <span class="text-amber-700 dark:text-amber-400">
                {{ $neverSyncedMessage }}
            </span>
        @endif

        @if (! is_null($pendingCount) && (int) $pendingCount > 0)
            {{-- Subtle in-progress signal. Pulse keeps it from blending into
                 the static last-sync text without screaming for attention. --}}
            <span class="inline-flex items-center gap-1 text-cyan-700 dark:text-cyan-400 animate-pulse">
                <x-lucide-loader class="size-3" />
                {{ $pendingCount }} pending sync
            </span>
        @endif
    </div>

    <a href="{{ $resolvedSyncJobsUrl }}" wire:navigate
        class="text-emerald-700 dark:text-emerald-400 hover:underline font-medium whitespace-nowrap">
        Lihat Sync Jobs →
    </a>
</div>
