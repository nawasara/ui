<div x-data="{
    id: null,
    name: '',
    modalOpen(p) {
        this.name = p.detail.name;
        this.id = p.detail.id;
        openModal({ id: 'modalConfirmDelete' })
    }
}" @modal-delete.window="modalOpen($event)">
    <x-nawasara-modal::modal id="modalConfirmDelete" title="Delete Confirmation">
        <p class="text-sm text-gray-700 dark:text-neutral-300"> Are you sure you want to delete <strong
                x-text="name"></strong> ? All
            of your data will be permanently removed. This action cannot be undone.</p>

        <x-slot:footer>
            <div wire:loading>
                <x-nawasara-ui::loading />
            </div>
            <x-nawasara-ui::button color="danger"
                @click="Livewire.dispatch('confirm-delete', { id: id, name: name })">Delete</x-nawasara-ui::button>
            <x-nawasara-ui::button color="neutral"
                @click="closeModal('modalConfirmDelete')">Cancel</x-nawasara-ui::button>

        </x-slot:footer>
    </x-nawasara-modal::modal>
</div>
