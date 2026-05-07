{{-- Livewire pagination view override.

     Livewire's WithPagination trait force-sets Paginator::defaultView to
     'livewire::tailwind' on every render, undoing our app-level override.
     We counter that by registering this directory under the 'livewire'
     namespace at higher priority in UiServiceProvider, so when Livewire
     looks up 'livewire::tailwind' it finds OUR template instead.

     This file is just a thin alias - the real markup lives in the
     nawasara-ui::components.pagination component so we have a single
     source of truth for pagination styling. --}}
@include('nawasara-ui::components.pagination', ['paginator' => $paginator, 'elements' => $elements])
