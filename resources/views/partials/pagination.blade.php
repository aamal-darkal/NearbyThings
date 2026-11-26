{{--
  ترقيم الصفحات
  المتغيرات: $paginator (نتيجة paginate)، $label (مثل: طلباً)
--}}
@if ($paginator->total() > 0)
  <nav class="pagination" aria-label="ترقيم الصفحات">
    <span>عرض {{ $paginator->firstItem() }} إلى {{ $paginator->lastItem() }} من أصل {{ $paginator->total() }} {{ $label }}</span>

    @if ($paginator->hasPages())
      <div class="pagination__links">
        @if ($paginator->onFirstPage())
          <span class="is-disabled">السابق</span>
        @else
          <a href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a>
        @endif

        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
          @if ($page === $paginator->currentPage())
            <span class="is-active" aria-current="page">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach

        @if ($paginator->hasMorePages())
          <a href="{{ $paginator->nextPageUrl() }}" rel="next">التالي</a>
        @else
          <span class="is-disabled">التالي</span>
        @endif
      </div>
    @endif
  </nav>
@endif
