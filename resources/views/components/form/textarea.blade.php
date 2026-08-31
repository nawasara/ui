{{--
    Form textarea — isian teks beberapa baris.

    Pemakaian:
        <x-nawasara-ui::form.textarea label="Catatan" wire:model="notes" :rows="3"
            hint="Opsional." />
--}}
@props([
    'label' => null,
    'name' => null,
    'rows' => 3,
    'placeholder' => '',
    'hint' => null,
])

<div class="flex flex-col gap-1">
    {{-- ⚠️ Dibaca dari $label, BUKAN $attributes.
         `label` dideklarasikan di @props, dan prop yang dideklarasikan
         DIKELUARKAN dari $attributes — jadi `$attributes->has('label')`
         selalu false dan labelnya tidak pernah tergambar. Lima belas
         pemakaian di sepuluh paket kehilangan labelnya karena ini, dan
         tidak ada galat apa pun yang menandainya. --}}
    @if ($label)
        <x-nawasara-ui::form.label :value="$label" />
    @endif

    <textarea @if($name) id="{{ $name }}" name="{{ $name }}" @endif rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' =>
                'w-full py-3 px-4 rounded-md border border-gray-300 text-sm transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-emerald-700/80 focus:!border-transparent outline-none dark:bg-neutral-900 dark:border-gray-800 text-gray-900 dark:text-neutral-100',
        ]) }}>{{ $name ? old($name) : '' }}</textarea>

    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @if ($name)
        @error($name)
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
