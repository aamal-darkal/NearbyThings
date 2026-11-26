{{-- روابط التنقل بين صفحات الطلبات --}}
<nav class="account-nav" aria-label="أقسام الطلبات">
  <a href="{{ route('requests.index') }}" @class(['pill', 'is-active' => request()->routeIs('requests.index')])><x-icon name="send" />طلباتي</a>
  <a href="{{ route('requests.incoming') }}" @class(['pill', 'is-active' => request()->routeIs('requests.incoming')])><x-icon name="inbox" />الطلبات الواردة</a>
  <a href="{{ route('items.mine') }}" class="pill"><x-icon name="package" />إعلاناتي</a>
</nav>
