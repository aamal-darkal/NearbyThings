{{-- صفحة أغراضي وإعلاناتي (للمالك) --}}
@extends('layouts.site')

@section('title', 'أغراضي وإعلاناتي')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/my-items.css') }}">
@endpush

@php
  $tabs = [
      'all' => 'الكل',
      'active' => 'نشط',
      'deal' => 'قيد التفاوض والاتفاق',
      'inactive' => 'موقوف مؤقتاً',
      'completed' => 'مكتملة',
  ];
@endphp

@section('content')
  <main class="page">
    <div class="container">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        <a href="{{ route('profile.show') }}">حسابي</a>
        <x-icon name="chevron-left" />
        <span class="current">أغراضي وإعلاناتي</span>
      </nav>

      <div class="page-head">
        <div>
          <h1>أغراضي وإعلاناتي</h1>
          <p>إدارة ومتابعة معروضاتك المتاحة للبيع أو التأجير أو التبادل بين الجيران</p>
        </div>
        <a href="{{ route('items.create') }}" class="btn btn--primary"><x-icon name="plus-circle" />أضف غرضاً جديداً</a>
      </div>

      <!-- الإحصائيات -->
      <section class="stat-cards" aria-label="إحصائيات أغراضي">
        <div class="stat-card">
          <div><span class="stat-card__label">إجمالي الأغراض</span><strong class="stat-card__value">{{ $counts['all'] }}</strong></div>
          <span class="stat-card__icon"><x-icon name="package" /></span>
        </div>
        <div class="stat-card">
          <div><span class="stat-card__label">إعلانات نشطة</span><strong class="stat-card__value">{{ $counts['active'] }}</strong></div>
          <span class="stat-card__icon"><x-icon name="check-circle" /></span>
        </div>
        <div class="stat-card">
          <div><span class="stat-card__label">صفقة جارية</span><strong class="stat-card__value">{{ $counts['deal'] }}</strong></div>
          <span class="stat-card__icon stat-card__icon--accent"><x-icon name="handshake" /></span>
        </div>
        <div class="stat-card">
          <div><span class="stat-card__label">موقوف مؤقتاً</span><strong class="stat-card__value">{{ $counts['inactive'] }}</strong></div>
          <span class="stat-card__icon"><x-icon name="pause" /></span>
        </div>
        <div class="stat-card">
          <div><span class="stat-card__label">صفقات مكتملة</span><strong class="stat-card__value">{{ $counts['completed'] }}</strong></div>
          <span class="stat-card__icon"><x-icon name="check" /></span>
        </div>
      </section>

      <!-- التبويبات والبحث -->
      <div class="panel toolbar">
        <nav class="chips" aria-label="تصفية حسب الحالة">
          @foreach ($tabs as $key => $label)
            <a href="{{ route('items.mine', ['tab' => $key, 'q' => request('q'), 'sort' => request('sort')]) }}"
               @class(['chip', 'is-active' => $tab === $key])>
              {{ $label }} <span class="chip__count">{{ $counts[$key] }}</span>
            </a>
          @endforeach
        </nav>

        <form class="toolbar__search" method="GET" action="{{ route('items.mine') }}">
          <input type="hidden" name="tab" value="{{ $tab }}">
          <label class="sr-only" for="mine-search">ابحث في معروضاتك</label>
          <div class="input-wrap">
            <x-icon name="search" />
            <input type="search" id="mine-search" name="q" value="{{ request('q') }}" placeholder="ابحث في معروضاتك...">
          </div>
          <label class="sr-only" for="mine-sort">الترتيب</label>
          <select class="select select--sm js-auto-submit" id="mine-sort" name="sort">
            <option value="newest" @selected(request('sort') !== 'oldest')>الأحدث أولاً</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>الأقدم أولاً</option>
          </select>
        </form>
      </div>

      <!-- قائمة الأغراض -->
      <section class="my-items" aria-label="أغراضي">
        @forelse ($items as $item)
          @php
            $isDeal = $item->open_requests_count > 0;
            $statusClass = match (true) {
                $isDeal => 'status--warning',
                $item->state === 'active' => 'status--success',
                $item->state === 'banned' => 'status--danger',
                in_array($item->state, ['sold', 'rented']) => 'status--info',
                default => '',
            };
            $statusText = $isDeal ? 'قيد التفاوض والاتفاق' : $item->stateLabel();
          @endphp

          <article @class(['my-item', 'is-deal' => $isDeal])>
            <div class="my-item__media">
              @if ($item->mainImageUrl())
                <img src="{{ $item->mainImageUrl() }}" alt="{{ $item->title }}" loading="lazy">
              @endif
              <span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span>
              <span class="status {{ $statusClass }}">{{ $statusText }}</span>
            </div>

            <div class="my-item__body">
              <div class="my-item__top">
                <span class="my-item__area"><x-icon name="map-pin" />{{ $item->areaLabel() }}</span>
                <span class="muted">نُشر {{ \App\Support\Dates::ago($item->created_at) }}</span>
              </div>
              <h2 class="my-item__title"><a href="{{ route('items.show', $item) }}">{{ $item->title }}</a></h2>
              <p class="my-item__desc">{{ \Illuminate\Support\Str::limit($item->description, 140) }}</p>

              @if ($isDeal)
                <div class="alert alert--warning">
                  <x-icon name="info" />
                  <div><strong>صفقة جارية قيد التنفيذ:</strong> لديك طلبات على هذا الغرض. تم تجميد التعديلات لحماية أمان الصفقة.</div>
                </div>
              @elseif ($item->state === 'banned')
                <div class="alert alert--danger">
                  <x-icon name="ban" />
                  <div><strong>إعلان محجوب:</strong> تم حجب هذا الإعلان من قبل الإدارة لمخالفته سياسة الاستخدام.</div>
                </div>
              @endif

              <div class="my-item__footer">
                <div>
                  <span class="muted">
                    @if ($item->transaction_type === 'exchange') طبيعة العرض: @elseif ($item->transaction_type === 'rent') سعر الإيجار المحدد: @else السعر المطلوب: @endif
                  </span>
                  <strong class="my-item__price">
                    {{ $item->priceLabel() }}
                    @if (! empty($item->variants['exchange_for']))<small>(مقابل {{ $item->variants['exchange_for'] }})</small>@endif
                  </strong>
                </div>

                @if ($isDeal)
                  <span class="my-item__meta"><x-icon name="message" />{{ $item->open_requests_count }} طلبات جارية</span>
                @elseif ($item->state === 'inactive')
                  <span class="my-item__meta muted"><x-icon name="eye-off" />مخفي من البحث</span>
                @endif

                <div class="my-item__actions">
                  @if (in_array($item->state, ['active', 'inactive']) && ! $isDeal)
                    <a href="{{ route('items.edit', $item) }}" class="btn btn--soft btn--xs"><x-icon name="pencil" />تعديل</a>

                    <form method="POST" action="{{ route('items.toggle', $item) }}">
                      @csrf
                      @method('PATCH')
                      @if ($item->state === 'active')
                        <button type="submit" class="btn btn--soft btn--xs"><x-icon name="pause" />إيقاف مؤقت</button>
                      @else
                        <button type="submit" class="btn btn--primary btn--xs"><x-icon name="play" />تفعيل الإعلان</button>
                      @endif
                    </form>
                  @else
                    <span class="btn btn--soft btn--xs is-locked" title="لا يمكن التعديل حالياً"><x-icon name="lock" />مقفل</span>
                  @endif

                  <form method="POST" action="{{ route('items.destroy', $item) }}" class="js-confirm" data-confirm="هل أنت متأكد من حذف هذا الإعلان؟">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--text-danger btn--xs" aria-label="حذف الإعلان"><x-icon name="trash" /></button>
                  </form>
                </div>
              </div>
            </div>
          </article>
        @empty
          <div class="empty-state">
            <x-icon name="package" />
            <p>لا توجد أغراض في هذا القسم.</p>
            <a href="{{ route('items.create') }}" class="btn btn--primary"><x-icon name="plus" />أضف غرضك الأول</a>
          </div>
        @endforelse
      </section>

      <!-- إرشادات الأمان -->
      <aside class="safety-banner">
        <span class="safety-banner__icon"><x-icon name="shield" /></span>
        <div>
          <h2>إرشادات الأمان بين أهل الحي</h2>
          <p>احرص دائماً على تسليم واستلام الأغراض في أماكن عامة معروفة داخل الحي أو عند مدخل البناء، والتأكد من فحص الأداة برضا الطرفين.</p>
        </div>
      </aside>
    </div>
  </main>
@endsection
