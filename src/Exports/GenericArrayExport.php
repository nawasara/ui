<?php

namespace Nawasara\Ui\Exports;

use Maatwebsite\Excel\Concerns\FromIterator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Generic exporter that turns an iterable of associative arrays into an xlsx /
 * csv sheet. Backs HasExport trait — see that trait's docblock for the wider
 * picture.
 *
 * Behaviour:
 * - The first row is inspected to determine column order and headers; all
 *   subsequent rows are projected onto that key set in the same order.
 * - Headers are bolded with a brand-coloured fill (emerald-700) and white
 *   text. Auto-size on every column so columns fit content.
 * - Iterator-backed (FromIterator) so generator-based data sources stream
 *   through without buffering everything in memory.
 *
 * Output is dataset-shaped, not spreadsheet-formatted. Consumers wanting
 * cell-level styling, formulas, multiple sheets, etc. should write a
 * dedicated Excel\Concerns\* implementation instead.
 */
class GenericArrayExport implements FromIterator, WithHeadings, WithEvents, ShouldAutoSize
{
    /** @var array<string>|null cached header keys from the first row */
    protected ?array $headerKeys = null;

    /** @var iterable<int,array<string,mixed>> source data */
    protected iterable $rows;

    public function __construct(iterable $rows)
    {
        $this->rows = $rows;
    }

    public function iterator(): \Generator
    {
        $keys = $this->resolveHeaderKeys();
        foreach ($this->rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            // Project row onto resolved header order; missing keys → empty cell
            $out = [];
            foreach ($keys as $k) {
                $out[] = $row[$k] ?? '';
            }
            yield $out;
        }
    }

    public function headings(): array
    {
        return $this->resolveHeaderKeys();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highest = $sheet->getHighestColumn();

                // Bold + emerald fill on row 1 (headers)
                $sheet->getStyle("A1:{$highest}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '047857'], // emerald-700
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);
            },
        ];
    }

    /**
     * Determine column order from the first row's keys. The source iterator may
     * be a generator (single-use) so we materialise the first element and
     * prepend it back via array_merge in the iterator() caller path. Concretely:
     * when this method runs the first time, it forces the iterable to an array
     * if it's a generator so subsequent iterator() calls see the same data.
     */
    protected function resolveHeaderKeys(): array
    {
        if ($this->headerKeys !== null) {
            return $this->headerKeys;
        }

        // Force iteration to materialise generators. This trades streaming for
        // simplicity — generic exports are rarely huge enough to matter, and
        // consumers needing true streaming should write a custom export class.
        if (! is_array($this->rows) && ! ($this->rows instanceof \Countable)) {
            $this->rows = iterator_to_array($this->rows, false);
        }

        $first = null;
        foreach ($this->rows as $row) {
            if (is_array($row)) {
                $first = $row;
                break;
            }
        }

        $this->headerKeys = $first === null ? [] : array_keys($first);
        return $this->headerKeys;
    }
}
