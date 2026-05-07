<?php

namespace Nawasara\Ui\Livewire\Concerns;

/**
 * Coerces specified Livewire properties to arrays after component mount.
 *
 * Why this exists:
 *   When migrating filter properties from string-typed (single-select) to
 *   array-typed (multi-select via filter-panel), URLs that legacy users have
 *   bookmarked still carry scalar values like `?nodeFilter=pve-2`.
 *   Livewire 3 hydrates `#[Url]` properties via direct assignment, and PHP
 *   refuses `string -> array` on a typed property — the page crashes with
 *   `TypeError: Cannot assign string to property ...::$nodeFilter of type
 *   array`.
 *
 * Fix:
 *   Drop the `array` type hint on the property (keep PHPDoc for docs/IDE),
 *   then `use HasArrayFilters` and list the properties via the
 *   `arrayFilters` method. The trait's mount() hook normalises any scalar
 *   value to a single-element array (or [] for empty/null) so downstream
 *   code that expects array-shape works regardless of whether the URL
 *   carries a string or an array.
 *
 * Usage:
 *   class Table extends Component {
 *       use HasArrayFilters;
 *
 *       #[Url]
 *       public $nodeFilter = [];        // no `array` type hint
 *
 *       #[Url]
 *       public $statusFilter = [];
 *
 *       protected function arrayFilters(): array {
 *           return ['nodeFilter', 'statusFilter'];
 *       }
 *   }
 *
 * Note: this only matters for properties that may receive scalar values
 * from the request URL. Properties that are set programmatically by the
 * filter-panel are always already arrays.
 */
trait HasArrayFilters
{
    /**
     * Override to list array-shaped Livewire properties that need legacy
     * scalar-URL coercion. Default empty list = no-op.
     *
     * @return array<int, string>
     */
    protected function arrayFilters(): array
    {
        return [];
    }

    /**
     * Livewire calls each booted hook before mount(). We coerce here so
     * filters land as arrays before any computed property reads them.
     *
     * Cases handled:
     *   array       → kept as-is.
     *   ''          → []         (empty string from cleared filter).
     *   null        → []         (URL param absent / unset).
     *   'pve-2'     → ['pve-2']  (legacy single-select string).
     *   '0'         → ['0']      (numeric strings preserved as strings).
     *
     * Comma-splitting is intentionally NOT done. Livewire serialises
     * arrays to repeated `?foo[]=a&foo[]=b` query params, never CSV.
     * If we split on comma we'd misinterpret legitimate single values
     * containing a comma.
     */
    public function bootedHasArrayFilters(): void
    {
        foreach ($this->arrayFilters() as $prop) {
            $value = $this->{$prop} ?? null;

            if (is_array($value)) {
                continue;
            }

            if ($value === '' || $value === null) {
                $this->{$prop} = [];
                continue;
            }

            $this->{$prop} = [(string) $value];
        }
    }
}
