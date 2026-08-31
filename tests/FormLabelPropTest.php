<?php

declare(strict_types=1);

namespace Nawasara\Ui\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Komponen form yang mendeklarasikan `label` di @props HARUS membacanya dari
 * `$label`, bukan dari `$attributes`.
 *
 * Blade MENGELUARKAN prop yang dideklarasikan dari `$attributes`. Jadi
 * komponen yang menulis keduanya —
 *
 *     @props(['label' => null])
 *     @if ($attributes->has('label'))
 *
 * — memiliki kondisi yang SELALU false, dan labelnya tidak pernah tergambar.
 * Tidak ada galat, tidak ada peringatan; kolomnya hanya muncul tanpa nama.
 *
 * Terjadi pada textarea, select, dan select-dropdown sekaligus: 15 pemakaian
 * di 10 paket kehilangan labelnya, dan baru ketahuan ketika seseorang
 * membuka formulir dan bertanya "ini kolom apa".
 */
class FormLabelPropTest extends TestCase
{
    /** @return list<string> */
    private function formComponents(): array
    {
        $dir = dirname(__DIR__).'/resources/views/components/form';

        return glob($dir.'/*.blade.php') ?: [];
    }

    public function test_komponen_form_tidak_membaca_label_dari_attributes(): void
    {
        $files = $this->formComponents();

        $this->assertNotEmpty($files, 'tidak ada komponen form terdeteksi');

        $offenders = [];

        foreach ($files as $file) {
            $src = (string) file_get_contents($file);

            // Hanya berlaku bila `label` memang dideklarasikan sebagai prop.
            $declaresProp = (bool) preg_match("/@props\(\[[^\]]*'label'/s", $src);

            if (! $declaresProp) {
                continue;
            }

            // Komentar Blade dibuang lebih dulu — komentar yang MENJELASKAN
            // jebakan ini memuat teks yang sama dengan kodenya, dan tes yang
            // mencocokkan teks mentah akan menuduh berkas yang justru sudah
            // benar.
            $code = preg_replace('/\{\{--.*?--\}\}/s', '', $src) ?? $src;

            $readsAttributes = str_contains($code, "\$attributes->has('label')")
                || str_contains($code, "\$attributes['label']");

            if ($readsAttributes) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'komponen ini mendeklarasikan label di @props tetapi membacanya dari $attributes — '
            .'kondisinya selalu false dan labelnya tidak akan tergambar',
        );
    }

    /**
     * Komponen yang menerima label harus benar-benar merendernya.
     *
     * Menangkap kebalikannya: prop yang dideklarasikan lalu dilupakan.
     *
     * Checkbox dan radio dikecualikan — keduanya membungkus kotaknya dengan
     * <label> sendiri supaya teksnya ikut mengaktifkan, bukan menempatkan
     * label terpisah di atasnya. Itu pola yang benar untuk kolom pilihan.
     */
    public function test_komponen_yang_punya_prop_label_merendernya(): void
    {
        $missing = [];

        foreach ($this->formComponents() as $file) {
            $src = (string) file_get_contents($file);

            if (! preg_match("/@props\(\[[^\]]*'label'/s", $src)) {
                continue;
            }

            if (in_array(basename($file), ['checkbox.blade.php', 'radio.blade.php'], true)) {
                continue;
            }

            if (! str_contains($src, 'form.label')) {
                $missing[] = basename($file);
            }
        }

        $this->assertSame([], $missing, 'komponen ini punya prop label tetapi tidak pernah merendernya');
    }
}
