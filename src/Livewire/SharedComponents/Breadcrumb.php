<?php

namespace Nawasara\Ui\Livewire\SharedComponents;

use Livewire\Component;

class Breadcrumb extends Component
{
    public array $items = [];

    public function render()
    {
        return view('nawasara-ui::livewire.shared-components.breadcrumb');
    }
}
