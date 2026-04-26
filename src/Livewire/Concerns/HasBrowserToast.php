<?php

namespace Nawasara\Ui\Livewire\Concerns;

/**
 * Trait for Livewire components that need to fire toast notifications
 * via browser-side JavaScript (window.Toast).
 *
 * Use this when the standard `toaster_success()`/`toaster_error()` helpers
 * (session-flash based) don't fire — typical for Livewire AJAX requests
 * that don't trigger a full page reload.
 *
 * Usage:
 *   $this->toastSuccess('Saved successfully');
 *   $this->toastError('Failed to save');
 *
 * Requires nawasara/toaster package's `window.Toast` to be loaded
 * (already in default `nawasara-ui::components.layouts.app`).
 */
trait HasBrowserToast
{
    public function toastSuccess(string $message): void
    {
        $this->browserToast('success', $message);
    }

    public function toastError(string $message): void
    {
        $this->browserToast('error', $message);
    }

    public function toastWarning(string $message): void
    {
        $this->browserToast('warning', $message);
    }

    public function toastInfo(string $message): void
    {
        $this->browserToast('info', $message);
    }

    protected function browserToast(string $type, string $message): void
    {
        // Two-channel dispatch:
        // 1. $this->js() runs after DOM patch in any Livewire response.
        // 2. Livewire browser event 'toast' is also listened to by the
        //    nawasara-toaster Alpine manager — covers cases where the
        //    js() payload is dropped (no-op responses, error pages).
        $this->dispatch('toast', type: $type, message: $message);

        $js = sprintf(
            'if (window.Toast && typeof window.Toast[%s] === "function") { window.Toast[%s](%s); }',
            json_encode($type),
            json_encode($type),
            json_encode($message)
        );

        $this->js($js);
    }
}
