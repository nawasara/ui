# Nawasara UI

UI components, layouts, theme, and shared Livewire concerns used by every package in the Nawasara superapp framework.

## What's inside

### Blade components (`<x-nawasara-ui::*>`)

| Component | Purpose |
|-----------|---------|
| `button` | Variant + color + size system, optional icon slot, permission gate |
| `table` | Standardised table shell with title, action slot, and search |
| `modal` | Alpine-driven modal with header/footer slots, opened via `modal-open:{id}` event |
| `modal-confirm-delete` | Pre-built confirmation modal |
| `dropdown-menu-action` | Per-row action dropdown with permission filtering |
| `filter-bar` | Search input + filter chips slot, used above every list page |
| `filter-dropdown` | Wired filter dropdown that resets pagination |
| `filter-chip` | Removable chip showing the active filter value |
| `bulk-action-bar` | Shows up when rows are selected; hosts bulk action buttons |
| `skeleton`, `skeleton-stats`, `skeleton-table` | Loading placeholders that match the real layout |
| `dark-mode-toggle` | Sun/moon toggle wired to a session preference |
| `workspace-switcher` | Top-level workspace dropdown for grouped sidebar navigation |
| `brand-logo` | Renders the configured brand logo with a height variant |
| `page.title`, `page.container` | Layout helpers for consistent page structure |
| `form.input`, `form.select`, `form.textarea`, `form.checkbox`, `form.label` | Themed form primitives |

### Layouts

- `layouts.app` — main authenticated layout with sidebar + topbar + toaster
- `layouts.guest` — login / public layout

### Livewire concerns

- `Nawasara\Ui\Livewire\Concerns\HasBrowserToast` — gives any Livewire component `toastSuccess / toastError / toastWarning / toastInfo` methods that fire dual-channel (Livewire event + JS payload) so toasts always show, even in AJAX-only requests where session flash never reaches the page

### Services

- `WorkspaceManager` — resolves the active workspace from the request URL, merges menu entries from every package that declares the same `workspace` key, and exposes `accessible()` for permission-filtered nav

## Installation

```bash
composer require nawasara/ui
```

Auto-discovered. Most consumers will register Tailwind to scan this package's views:

```js
// tailwind.config.js
content: [
    './resources/**/*.blade.php',
    './vendor/nawasara/*/resources/**/*.blade.php',
],
```

## Author

**Pringgo J. Saputro** &lt;odyinggo@gmail.com&gt;

## License

MIT
