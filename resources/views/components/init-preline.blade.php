{{--
    Init Preline — memuat ulang komponen Preline setelah navigasi Livewire.

    Sudah dipasang di layout aplikasi. Tanpa ini, dropdown dan overlay berhenti
    bekerja setelah berpindah halaman lewat wire:navigate.

    Pemakaian:
        <x-nawasara-ui::init-preline />
--}}
<script>
    document.addEventListener("livewire:navigated", () => {
        window.HSStaticMethods.autoInit();
    });
</script>
