{{--
  بطاقة غرض (تنستخدم بالرئيسية والبحث والمفضلة والملف الشخصي)
  المتغيرات: $item
--}}
<article class="item-card" data-deal="{{ $item->transaction_type }}">
  <div class="item-card__media">
    @if ($item->mainImageUrl())
      <img src="{{ $item->mainImageUrl() }}" alt="{{ $item->title }}" loading="lazy">
    @else
      <span class="item-card__placeholder"><x-icon name="image" /></span>
    @endif
    <span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span>
  </div>
  @php $isFavorite = in_array($item->id, $favoriteIds ?? []); @endphp
  <button @class(['fav-btn', 'js-fav', 'is-active' => $isFavorite]) type="button"
          data-url="{{ route('favorites.toggle', $item) }}"
          aria-pressed="{{ $isFavorite ? 'true' : 'false' }}" aria-label="إضافة إلى المفضلة"><x-icon name="heart" /></button>
  <div class="item-card__body">
    @if ($item->distance !== null)
      <span class="item-card__distance"><x-icon name="navigation" />{{ $item->distanceLabel() }}</span>
    @endif
    <h3 class="item-card__title"><a href="{{ route('items.show', $item) }}">{{ $item->title }}</a></h3>
    <div class="item-card__footer">
      <span class="item-card__price">
        {{ $item->priceNumber() }}
        @if ($item->transaction_type === 'sale')<small>ل.س</small>@endif
        @if ($item->transaction_type === 'rent')<small>ل.س / {{ $item->rentUnitLabel() }}</small>@endif
      </span>
      <span class="item-card__area">{{ $item->areaLabel() }}</span>
    </div>
  </div>
</article>
