<?php

namespace Nawasara\Ui\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Js;
use Tests\TestCase;

/**
 * Audit keamanan 15 Oktober 2026 (T9): dropdown-menu-action dulu menyambung
 * nama baris ke JavaScript onclick dengan kutip tunggal. Browser men-decode
 * entitas HTML di atribut sebelum menjalankannya, jadi e() tidak menolong:
 * nama `Budi'); alert(1);//` menjalankan skrip di sesi admin.
 *
 * Uji ini merender komponennya, men-decode atribut onclick seperti browser,
 * lalu memastikan tidak ada kutip mentah yang dapat menutup string.
 */
class DropdownMenuActionEscapingTest extends TestCase
{
    /** Bentuk escape JSON_HEX_APOS dan JSON_HEX_QUOT (backslash harfiah). */
    private const APOS = '\u0027';
    private const QUOT = '\u0022';

    private function onclicks(array $items): array
    {
        $html = Blade::render('<x-nawasara-ui::dropdown-menu-action id="7" :items="$items" />', ['items' => $items]);

        preg_match_all('/onclick="([^"]*)"/', $html, $m);

        // Seperti browser: entitas di-decode dulu, baru dijalankan sebagai JS.
        return array_map(fn ($v) => html_entity_decode($v, ENT_QUOTES | ENT_HTML5), $m[1]);
    }

    public function test_nama_pada_hapus_tidak_keluar_dari_string(): void
    {
        [$js] = $this->onclicks([
            ['type' => 'delete', 'label' => 'Hapus', 'name' => "Budi'); alert(document.cookie);//"],
        ]);

        $this->assertStringNotContainsString("Budi')", $js);
        $this->assertStringContainsString('Budi'.self::APOS.'); alert(document.cookie);//', $js);
    }

    public function test_modal_confirm_dan_argumen_click_di_encode(): void
    {
        [$js] = $this->onclicks([
            [
                'type' => 'click', 'label' => 'Ubah',
                'wire:click' => "edit('a\"b', 'c, d')",
                'modal' => "m');alert(1);//",
                'confirm' => "Hapus 'x'?",
            ],
        ]);

        $this->assertStringNotContainsString("m')", $js);
        $this->assertStringNotContainsString("'x'", $js);
        // Koma di dalam string tidak lagi memecah argumen.
        $this->assertStringContainsString('"edit", "a'.self::QUOT.'b", "c, d"', $js);
    }

    public function test_argumen_js_from_kembali_ke_nilai_asli(): void
    {
        [$js] = $this->onclicks([
            ['type' => 'click', 'label' => 'Ubah', 'wire:click' => 'openGroup('.Js::from('whm').', '.Js::from("O'Brien").')'],
        ]);

        $this->assertStringContainsString('"openGroup", "whm", "O'.self::APOS.'Brien"', $js);
    }

    public function test_tipe_angka_boolean_null_dipertahankan(): void
    {
        [$js] = $this->onclicks([
            ['type' => 'click', 'label' => 'X', 'wire:click' => 'edit(12, true, null, 1.5)'],
        ]);

        $this->assertStringContainsString('"edit", 12, true, null, 1.5', $js);
    }

    public function test_href_javascript_ditolak(): void
    {
        $html = Blade::render('<x-nawasara-ui::dropdown-menu-action id="7" :items="$items" />', ['items' => [
            ['type' => 'href', 'label' => 'L', 'href' => " JavaScript:alert(1)"],
        ]]);

        $this->assertStringNotContainsStringIgnoringCase('javascript:', $html);
    }
}
