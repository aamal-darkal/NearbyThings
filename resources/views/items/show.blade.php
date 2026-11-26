{{-- صفحة تفاصيل الغرض --}}
@extends('layouts.site')

@section('title', $item->title)

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="{{ asset('assets/css/item-details.css') }}">
@endpush

@php
  // أسماء المواصفات الإضافية (عمود variants)
  $variantNames = ['color' => 'اللون', 'storage' => 'السعة', 'size' => 'المقاس', 'lens' => 'العدسة', 'capacity' => 'الحجم'];
  $specs = collect($item->variants ?? [])->except('exchange_for');
  $isAvailable = $item->state === 'active' && ! $item->owner->is_blocked;
  $images = $item->images;

  // طلب مفتوح للمستخدم على هالغرض (اذا موجود منوديه لصفحة الطلب بدل ما يرسل طلب جديد)
  $openRequest = auth()->check()
      ? \App\Models\ItemRequest::where('item_id', $item->id)
          ->where('requester_id', auth()->id())
          ->whereIn('status', \App\Models\ItemRequest::OPEN_STATUSES)
          ->first()
      : null;
@endphp

@section('content')
  <main class="page">
    <div class="container">
      <!-- مسار التنقل -->
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}"><x-icon name="home" /> الرئيسية</a>
        <x-icon name="chevron-left" />
        <a href="{{ route('search', ['category' => $item->category_id]) }}">{{ $item->category->category_name }}</a>
        <x-icon name="chevron-left" />
        <span class="current">{{ $item->title }}</span>
      </nav>

      @unless ($isAvailable)
        <div class="alert alert--warning item-alert">
          <x-icon name="info" />
          <div>
            <strong>هذا الإعلان غير متاح حالياً:</strong>
            @if ($item->state === 'banned') محجوب من قبل الإدارة.
            @elseif ($item->state === 'sold') تمت الصفقة على هذا الغرض.
            @elseif ($item->state === 'rented') الغرض مؤجر حالياً.
            @else موقوف مؤقتاً من قبل المالك.
            @endif
          </div>
        </div>
      @endunless

      <div class="item-layout">
        <!-- العمود الأول: المعرض والوصف -->
        <div>
          <section class="panel gallery" aria-label="صور الغرض">
            <div class="gallery__main">
              @if ($images->isNotEmpty())
                <img id="gallery-main" src="{{ $images->first()->url() }}" alt="{{ $item->title }}">
              @else
                <span class="item-card__placeholder"><x-icon name="image" /></span>
              @endif
              <span class="badge badge--{{ $item->transaction_type }}"><x-icon name="tag" />{{ $item->typeLabel() }}</span>
              @if ($images->count() > 0)
                <span class="gallery__counter"><x-icon name="camera" /><span id="gallery-counter">1 من {{ $images->count() }} صور</span></span>
                <button class="gallery__expand" id="gallery-expand" type="button" aria-label="عرض بملء الشاشة"><x-icon name="maximize" /></button>
              @endif
            </div>
            @if ($images->count() > 1)
              <div class="gallery__thumbs">
                @foreach ($images as $image)
                  <button @class(['thumb', 'is-active' => $loop->first]) type="button" data-src="{{ $image->url() }}" data-index="{{ $loop->iteration }}" aria-label="الصورة {{ $loop->iteration }}">
                    <img src="{{ $image->url() }}" alt="">
                  </button>
                @endforeach
              </div>
            @endif
          </section>

          <section class="panel description">
            <h2>وصف الغرض</h2>
            <p>{{ $item->description }}</p>
          </section>

          <section class="specs" aria-label="المواصفات">
            <div class="panel spec">
              <div class="spec__label">الحالة</div>
              <div class="spec__value">{{ $item->condition->condition_name }}</div>
            </div>
            <div class="panel spec">
              <div class="spec__label">الكمية المتاحة</div>
              <div class="spec__value">{{ $item->count }}</div>
            </div>
            @if ($item->transaction_type === 'rent')
              <div class="panel spec">
                <div class="spec__label">أقصى مدة للتأجير</div>
                <div class="spec__value">{{ $item->max_rent_duration }} {{ $item->rentUnitLabel() }}</div>
              </div>
            @endif
            @if (! empty($item->variants['exchange_for']))
              <div class="panel spec">
                <div class="spec__label">المقابل المطلوب</div>
                <div class="spec__value">{{ $item->variants['exchange_for'] }}</div>
              </div>
            @endif
            @foreach ($specs as $key => $value)
              <div class="panel spec">
                <div class="spec__label">{{ $variantNames[$key] ?? $key }}</div>
                <div class="spec__value">{{ $value }}</div>
              </div>
            @endforeach
          </section>

          <aside class="safe-meetup">
            <span class="safe-meetup__icon"><x-icon name="shield" /></span>
            <div>
              <h3>معاينة آمنة وجهاً لوجه</h3>
              <p>يتم الاتفاق على مكان اللقاء بعد قبول طلبك. اختر دائماً نقطة عامة ومأهولة في {{ $item->areaLabel() ?: 'الحي' }}.</p>
            </div>
          </aside>
        </div>

        <!-- العمود الثاني: بطاقة المعلومات -->
        <aside class="panel info-card">
          <div class="tags">
            <span class="tag"><x-icon name="tag" />{{ $item->category->category_name }}</span>
            <span class="tag tag--accent"><x-icon name="check-circle" />{{ $item->condition->condition_name }}</span>
          </div>
          <p class="posted"><x-icon name="clock" />نُشر {{ \App\Support\Dates::ago($item->created_at) }}</p>

          <h1>{{ $item->title }}</h1>

          <div class="price-box">
            @if ($item->transaction_type === 'exchange')
              <strong>متاح للتبادل</strong>
            @else
              <strong>{{ number_format($item->price) }}</strong><span>ل.س @if ($item->transaction_type === 'rent') / {{ $item->rentUnitLabel() }} @endif</span>
            @endif
          </div>

          <div class="location-box">
            <div class="location-box__head">
              <span><x-icon name="map-pin" />{{ $item->areaLabel() }}</span>
              @if ($item->distance !== null)
                <span class="tag tag--accent">يبعد عنك {{ $item->distanceLabel() }}</span>
              @endif
            </div>
            @if ($approxLat !== null)
              <div class="map-preview item-map" id="item-map" data-lat="{{ $approxLat }}" data-lng="{{ $approxLng }}"></div>
            @endif
          </div>
          <p class="location-note">الموقع تقريبي حفاظاً على الخصوصية. تظهر بيانات التواصل بعد قبول المالك لطلبك.</p>

          <div class="owner">
            <span class="avatar">{{ $item->owner->initial() }}</span>
            <div>
              <div class="owner__name">{{ $item->owner->shortName() }}</div>
              <div class="owner__meta">{{ $ownerStats['deals'] }} صفقة ناجحة</div>
            </div>
            <div class="owner__rating">
              <strong><x-icon name="star" />{{ number_format($item->owner->rate, 1) }}</strong>
              <div class="owner__meta">{{ $ownerStats['reviews'] }} تقييم</div>
            </div>
          </div>

          <div class="actions">
            @if ($isOwner)
              <a href="{{ route('items.mine') }}" class="btn btn--primary"><x-icon name="package" />إدارة إعلاناتي</a>
            @elseif (! $isAvailable)
              <button class="btn btn--primary" type="button" disabled>غير متاح حالياً</button>
            @elseif (! auth()->check())
              <a href="{{ route('login') }}" class="btn btn--primary"><x-icon name="send" />سجّل الدخول لإرسال طلب</a>
            @elseif ($openRequest)
              <a href="{{ route('requests.show', $openRequest) }}" class="btn btn--primary"><x-icon name="eye" />لديك طلب على هذا الغرض</a>
            @else
              <button class="btn btn--primary" id="request-btn" type="button"><x-icon name="send" />أرسل طلباً</button>
            @endif

            @unless ($isOwner)
              @php $isFavorite = auth()->check() && auth()->user()->favoriteItems()->whereKey($item->id)->exists(); @endphp
              <button @class(['btn', 'btn--soft', 'js-fav', 'is-active' => $isFavorite]) type="button"
                      data-url="{{ route('favorites.toggle', $item) }}" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}" aria-label="إضافة إلى المفضلة"><x-icon name="heart" /></button>
            @endunless
          </div>
          @unless ($isOwner)
            @auth
              <a href="{{ route('reports.create', ['item' => $item->id]) }}" class="report-link"><x-icon name="flag" />إبلاغ عن مخالفة</a>
            @endauth
          @endunless
        </aside>
      </div>

      <!-- أغراض قريبة -->
      @if ($related->isNotEmpty())
        <section class="section related">
          <div class="section-head">
            <div>
              <h2 class="section-title section-title--sm">أغراض أخرى قريبة من نفس التصنيف</h2>
              <p class="section-subtitle">معروضة من جيرانك في {{ $item->category->category_name }}</p>
            </div>
            <a href="{{ route('search', ['category' => $item->category_id]) }}" class="link">عرض المزيد <x-icon name="chevron-left" /></a>
          </div>
          <div class="items-grid">
            @foreach ($related as $relatedItem)
              @include('partials.item-card', ['item' => $relatedItem])
            @endforeach
          </div>
        </section>
      @endif
    </div>
  </main>
@endsection

@push('modals')
  @if (auth()->check() && ! $isOwner && $isAvailable && ! $openRequest)
    @include('requests.partials.request-modal')
  @endif

  <!-- عارض الصور بملء الشاشة -->
  @if ($images->isNotEmpty())
    <div class="lightbox" id="lightbox" role="dialog" aria-label="عرض الصورة">
      <button class="lightbox__close" type="button" aria-label="إغلاق"><x-icon name="x" /></button>
      <img id="lightbox-image" src="{{ $images->first()->url() }}" alt="صورة الغرض بحجم كامل">
    </div>
  @endif
@endpush

@push('libraries')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/item-details.js') }}"></script>
@endpush
