<?php

namespace Nawasara\Ui\Livewire\SharedComponents;

use Livewire\Component;

class UniversalModal extends Component
{
    public $id;
    public $open = false;
    public $title = '';
    public $component = null;
    public $params = [];

    public function load($payload)
    {
        $this->title = $payload['title'] ?? '';
        $this->component = $payload['component'] ?? null;
        $this->params = $payload['params'] ?? [];
    }

    public function render()
    {
        return view('nawasara-ui::livewire.shared-components.universal-modal');
    }
}
