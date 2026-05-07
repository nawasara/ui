@props([
    /**
     * Lucide icon name (without 'lucide-' prefix is fine, we add it).
     * Examples: 'refresh-cw', 'link', 'download', 'trash-2'.
     */
    'icon' => 'circle',
    /**
     * Tooltip text shown on hover. Required for accessibility — without
     * a tooltip the icon-only button has no label for screen readers.
     */
    'tooltip' => null,
    /**
     * Aria-label override. Defaults to the tooltip text since 99% of the
     * time they should match. Pass explicitly when you want a longer or
     * differently-worded screen-reader label.
     */
    'ariaLabel' => null,
    /**
     * Color theme:
     *   'default' (gray border, white bg) — most refresh / utility actions
     *   'emerald'                          — primary highlight (sync-to-registry, link actions)
     */
    'color' => 'default',
    /**
     * When set, renders Tailwind's `wire:loading.class="animate-spin"` on
     * the icon AND `wire:loading.attr="disabled"` on the button. Pass a
     * Livewire action method name (e.g. 'refreshUsers') so loading state
     * scopes to *that* action, not any in-flight Livewire call.
     */
    'loadingTarget' => null,
    /**
     * Tooltip placement. Defaults to 'bottom' which is the toolbar norm.
     */
    'placement' => 'bottom',
])

{{-- <x-icon-button> — square 40px icon-only button with tooltip + optional
     spinner. The toolbar workhorse: every page that has a "Sync sekarang"
     or "Sync ke Registry" button used to ship 11+ lines of duplicated
     button markup. This wraps it.

     Usage (refresh / sync):
       <x-nawasara-ui::icon-button
           icon="refresh-cw"
           tooltip="Sync ulang dari WHM"
           wire:click="refreshAccounts"
           loadingTarget="refreshAccounts" />

     Usage (link / sync-to-registry, emerald accent):
       <x-nawasara-ui::icon-button
           icon="link"
           tooltip="Sync zone ke Registry aset"
           color="emerald"
           wire:click="syncRegistry"
           loadingTarget="syncRegistry"
           wire:confirm="Sinkronkan zone ke Registry aset?" />

     Usage (link / external, no Livewire action):
       <x-nawasara-ui::icon-button
           icon="external-link"
           tooltip="Buka dashboard"
           href="https://example.com"
           target="_blank" /> --}}

@php
    // Color tokens — keep both branches above the merge so Tailwind JIT
    // picks them up at build time. 'default' matches the standard utility
    // border-gray-200 bg-white pattern; 'emerald' is the primary accent
    // used for sync-to-registry and similar link-the-data actions.
    $colorClass = match ($color) {
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 dark:text-emerald-400 dark:hover:bg-emerald-900/40',
        default => 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700',
    };

    $base = 'inline-flex items-center justify-center size-10 rounded-lg border shadow-sm transition-colors disabled:opacity-50 disabled:pointer-events-none';

    $resolvedAriaLabel = $ariaLabel ?? $tooltip ?? 'Action';

    // Decide tag based on whether there's an href in the attribute bag.
    // <a> for links, <button type="button"> for everything else.
    $isLink = $attributes->whereStartsWith('href')->isNotEmpty();
    $tag = $isLink ? 'a' : 'button';

    // Strip our props from attribute bag so they don't leak into the DOM.
    // The remaining bag carries wire:click / wire:confirm / target / etc.
    $forwarded = $attributes->except(['icon', 'tooltip', 'ariaLabel', 'color', 'loadingTarget', 'placement']);

    // Resolve icon component name. Accept either bare lucide name ('link',
    // 'refresh-cw') or a fully-qualified ('lucide-link') — both work the
    // same. We MUST NOT use ltrim() here because ltrim is character-based,
    // not prefix-based: ltrim('link', 'lucide-') strips l + i + n? no —
    // strips l, i, then stops at n, leaving 'nk'. That silently broke the
    // 'link' icon (and any name starting with letters in 'lucide-').
    // Use str_starts_with() + substr() for actual prefix removal.
    $iconName = str_starts_with($icon, 'lucide-')
        ? substr($icon, 7)   // strip the 'lucide-' prefix once
        : $icon;
@endphp

<x-nawasara-ui::tooltip :text="$tooltip" :placement="$placement">
    <{{ $tag }}
        @if (! $isLink) type="button" @endif
        aria-label="{{ $resolvedAriaLabel }}"
        @if ($loadingTarget) wire:loading.attr="disabled" wire:target="{{ $loadingTarget }}" @endif
        {{ $forwarded->merge(['class' => trim($base.' '.$colorClass)]) }}>
        <x-dynamic-component
            :component="'lucide-'.$iconName"
            class="size-4"
            @if ($loadingTarget) wire:loading.class="animate-spin" wire:target="{{ $loadingTarget }}" @endif />
    </{{ $tag }}>
</x-nawasara-ui::tooltip>
