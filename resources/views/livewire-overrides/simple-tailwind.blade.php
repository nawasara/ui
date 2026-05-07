{{-- Simple pagination override (alias to full pagination view; consumers
     using simplePaginate() get the same look as paginate()). --}}
@include('nawasara-ui::components.pagination', ['paginator' => $paginator, 'elements' => []])
