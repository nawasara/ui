<?php

namespace Nawasara\Ui\Livewire\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Nawasara\Ui\Exports\GenericArrayExport;

/**
 * Trait for Livewire components that need to export data to xlsx / csv / json.
 *
 * The companion <x-nawasara-ui::export-button> calls $wire.export($format) where
 * $format is one of 'xlsx', 'csv', 'json'. This trait wires up that action and
 * delegates to two abstract-style methods the consumer must implement:
 *
 *   protected function exportFilename(): string
 *       Base filename without extension. Example: 'dns-records-ponorogo-go-id'.
 *       The trait appends the timestamp + format extension.
 *
 *   protected function exportData(): iterable
 *       Iterable of associative arrays. The keys of the FIRST row become the
 *       column headers (they are uppercased + spaces inserted on camelCase by
 *       GenericArrayExport for human readability). Use Collections, generators,
 *       or arrays; large datasets should yield to keep memory bounded.
 *
 * Per spec, the export reflects the FULL dataset, not the active filter view.
 * Consumers fetch raw data inside exportData(), bypassing $this->records etc.
 *
 * Usage:
 *   class Table extends Component {
 *       use HasExport;
 *
 *       protected function exportFilename(): string {
 *           return 'dns-records-' . str_replace('.', '-', $this->zone ?: 'all');
 *       }
 *
 *       protected function exportData(): iterable {
 *           return CloudflareDnsRecord::query()
 *               ->forZone($this->zone)
 *               ->get()
 *               ->map(fn ($r) => [
 *                   'Type' => $r->type,
 *                   'Name' => $r->name,
 *                   'Content' => $r->content,
 *                   ...
 *               ]);
 *       }
 *   }
 */
trait HasExport
{
    /**
     * Public Livewire action invoked by <x-export-button>.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    public function export(string $format = 'xlsx')
    {
        $format = strtolower($format);
        if (! in_array($format, ['xlsx', 'csv', 'json'], true)) {
            $format = 'xlsx';
        }

        $base = $this->exportFilename();
        $stamp = Carbon::now()->format('Ymd-His');
        $filename = "{$base}-{$stamp}.{$format}";

        $rows = $this->exportData();

        // JSON: stream straight to browser without involving Excel package.
        // Cheaper for developer-targeted exports and avoids xlsx encoding cost.
        if ($format === 'json') {
            $payload = is_array($rows) ? $rows : iterator_to_array($rows);
            return Response::streamDownload(
                fn () => print json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                $filename,
                ['Content-Type' => 'application/json'],
            );
        }

        $writer = $format === 'csv' ? Excel::CSV : Excel::XLSX;

        return ExcelFacade::download(
            new GenericArrayExport($rows),
            $filename,
            $writer,
        );
    }

    /**
     * Override: base filename (without extension or timestamp).
     */
    abstract protected function exportFilename(): string;

    /**
     * Override: rows to export. Each row is an associative array; keys of the
     * first row determine column headers.
     */
    abstract protected function exportData(): iterable;
}
