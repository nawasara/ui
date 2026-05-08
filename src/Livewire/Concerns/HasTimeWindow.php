<?php

namespace Nawasara\Ui\Livewire\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * Trait for Livewire components that filter their listing by a time window.
 *
 * Companion: <x-nawasara-ui::time-window> renders a segmented preset
 * selector ([Hari ini] [7 hari] [30 hari] [Custom]) and writes back to
 * three properties on the consumer:
 *
 *   $window  string  one of 'today', '7d', '30d', 'custom'
 *   $from    string  YYYY-MM-DD when window === 'custom'
 *   $to      string  YYYY-MM-DD when window === 'custom'
 *
 * The default window is '7d' to give new users a useful baseline without
 * loading the entire history. Page reset on window change is opt-in via
 * the `resetPageOnWindowChange` property (defaults to true to play nice
 * with Livewire WithPagination).
 *
 * Usage:
 *   class Table extends Component {
 *       use WithPagination, HasTimeWindow;
 *
 *       #[Computed]
 *       public function items() {
 *           return Activity::query()
 *               ->tap(fn ($q) => $this->applyTimeWindow($q, 'created_at'))
 *               ->latest()
 *               ->paginate(15);
 *       }
 *   }
 *
 *   <!-- view -->
 *   <x-nawasara-ui::time-window :window="$window" :from="$from" :to="$to" />
 *
 * @property string $window
 * @property string $from
 * @property string $to
 */
trait HasTimeWindow
{
    /**
     * Active preset key. Reactive — Livewire URL-binds it for shareable
     * links. The Blade <x-time-window> component manages this directly via
     * Alpine + $wire.set, so the `updated*` hook stays minimal.
     */
    #[Url(as: 'w')]
    public string $window = '7d';

    /**
     * Custom-range start date (YYYY-MM-DD). Only meaningful when
     * $window === 'custom'. Empty string == not set.
     */
    #[Url(as: 'from')]
    public string $from = '';

    /**
     * Custom-range end date (YYYY-MM-DD). Only meaningful when
     * $window === 'custom'. Empty string == not set.
     */
    #[Url(as: 'to')]
    public string $to = '';

    /**
     * Whether to call $this->resetPage() on window/from/to change. Most
     * consumers want this so paginating to page 5 then narrowing the
     * window doesn't strand the user past the new last page. Override
     * to false in components that don't paginate or want manual control.
     */
    protected bool $resetPageOnWindowChange = true;

    public function updatedWindow(): void
    {
        $this->onTimeWindowChanged();
    }

    public function updatedFrom(): void
    {
        $this->onTimeWindowChanged();
    }

    public function updatedTo(): void
    {
        $this->onTimeWindowChanged();
    }

    /**
     * Reset pagination when the window changes. Override to add custom
     * behaviour (e.g. clear cached aggregates) and call parent.
     */
    protected function onTimeWindowChanged(): void
    {
        if ($this->resetPageOnWindowChange && method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Apply the active time window to a query, filtering by `$column`.
     * No-op when both bounds resolve to null (shouldn't happen with the
     * default 'today'/'7d'/'30d' presets, but defensive).
     *
     * Accept either Eloquent\Builder atau Query\Builder — both expose
     * whereBetween / where dengan signature kompatibel. Union type biar
     * caller bisa pakai DB::table(...) langsung untuk UNION subqueries
     * (lihat ImpersonationLog\Section\Table).
     *
     * Pass directly to ->tap():
     *   $query->tap(fn ($q) => $this->applyTimeWindow($q, 'created_at'))
     */
    public function applyTimeWindow(EloquentBuilder|QueryBuilder $query, string $column): EloquentBuilder|QueryBuilder
    {
        [$from, $to] = $this->resolveTimeWindow();

        if ($from === null && $to === null) {
            return $query;
        }

        if ($from !== null && $to !== null) {
            return $query->whereBetween($column, [$from, $to]);
        }

        if ($from !== null) {
            return $query->where($column, '>=', $from);
        }

        return $query->where($column, '<=', $to);
    }

    /**
     * Resolve the active window into concrete [from, to] Carbon instances.
     * Returns [null, null] only for invalid Custom states (missing/bad
     * dates) — presets always resolve to a valid pair.
     *
     * @return array{0: ?CarbonInterface, 1: ?CarbonInterface}
     */
    public function resolveTimeWindow(): array
    {
        $now = Carbon::now();

        return match ($this->window) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            '30d' => [
                $now->copy()->subDays(30)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'custom' => $this->resolveCustomRange(),
            // Default and '7d' both fall through to 7-day window. Treating
            // unknown values as the safe default avoids a 500 if someone
            // crafts a bad URL.
            default => [
                $now->copy()->subDays(7)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Parse $from / $to as Y-m-d strings into Carbon instances. Returns
     * [null, null] if either is missing or unparseable so applyTimeWindow
     * can short-circuit the filter (= "show everything" until both
     * endpoints are valid).
     *
     * @return array{0: ?CarbonInterface, 1: ?CarbonInterface}
     */
    private function resolveCustomRange(): array
    {
        if ($this->from === '' || $this->to === '') {
            return [null, null];
        }

        try {
            $from = Carbon::parse($this->from)->startOfDay();
            $to = Carbon::parse($this->to)->endOfDay();
        } catch (\Throwable) {
            return [null, null];
        }

        // Swap if user accidentally inverted (defensive).
        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
