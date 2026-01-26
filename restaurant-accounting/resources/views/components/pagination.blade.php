{{--
    Standardized Pagination Component
    
    Usage: <x-pagination :items="$items" />
    
    This component ensures consistent Bootstrap pagination across the entire application.
    All pagination styling is defined globally in layouts/app.blade.php
--}}

@if ($items->hasPages())
<div class="d-flex justify-content-center mt-4 mb-3">
    {{ $items->links() }}
</div>
@endif
