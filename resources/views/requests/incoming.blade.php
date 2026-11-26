{{-- صفحة الطلبات الواردة (للمالك): الطلبات مجمعة حسب الغرض --}}
@extends('layouts.site')

@section('title', 'الطلبات الواردة')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/requests.css') }}">
@endpush

@php
  $tabs = ['all' => 'الكل', 'waiting' => 'قيد الانتظار', 'approved' => 'مقبول', 'received' => 'مستلم', 'rejected' => 'مرفوض', 'canceled' => 'ملغى', 'finished' => 'مكتمل'];
  $groups = $requests->groupBy('item_id');

  // رابط النموذج بعد رجوع أخطاء التحقق (بس روابط الطلبات)
  $oldAction = str_starts_with((string) old('_action'), url('/requests/')) ? old('_action') : '';
@endphp

@section('content')
  <main class="page">
    <div class="container container--narrow">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        <span class="current">الطلبات الواردة</span>
      </nav>

      <div class="page-head">
        <div>
          <h1>الطلبات الواردة</h1>
          <p>الطلبات التي أرسلها الجيران على إعلاناتك المعروضة داخل الحي</p>
        </div>
        @if ($counts['waiting'] > 0)
          <span class="tag tag--accent"><x-icon name="inbox" />{{ $counts['waiting'] }} طلبات جديدة بانتظار ردك</span>
        @endif
      </div>

      @include('requests.partials.nav')

      <nav class="chips requests-tabs" aria-label="تصفية حسب الحالة">
        @foreach ($tabs as $key => $label)
          @if ($key === 'all' || $counts[$key] > 0)
            <a href="{{ route('requests.incoming', ['tab' => $key]) }}" @class(['chip', 'is-active' => $tab === $key])>{{ $label }} ({{ $counts[$key] }})</a>
          @endif
        @endforeach
      </nav>

      <section class="incoming-list">
        @forelse ($groups as $itemRequests)
          @php $item = $itemRequests->first()->item; @endphp
          <article class="panel incoming-group">
            <header class="incoming-group__head">
              <div>
                <span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span>
                <h2><a href="{{ route('items.show', $item) }}">{{ $item->title }}</a></h2>
                <p class="muted"><x-icon name="map-pin" />{{ $item->areaLabel() }} • <strong class="request-card__price">{{ $item->priceLabel() }}</strong></p>
              </div>
              <span class="tag">عدد الطلبات: {{ $itemRequests->count() }}</span>
            </header>

            @foreach ($itemRequests as $deal)
              <div class="incoming-request">
                <div class="incoming-request__head">
                  <span class="avatar">{{ $deal->requester->initial() }}</span>
                  <div>
                    <strong>{{ $deal->requester->full_name }}</strong>
                    <span class="muted">
                      <span class="rating-inline"><x-icon name="star" />{{ number_format($deal->requester->rate, 1) }}</span>
                      • {{ $deal->requester->address ?: '—' }} • {{ \App\Support\Dates::ago($deal->request_date) }}
                    </span>
                  </div>
                  <span class="status {{ $deal->statusClass() }}">{{ $deal->statusLabel() }}</span>
                </div>

                @if ($deal->rental)
                  <div class="incoming-request__line">@include('requests.partials.rent-line', ['deal' => $deal])</div>
                @endif

                @if ($deal->exchangeOffer)
                  <div class="incoming-request__box">
                    <span class="muted"><x-icon name="repeat" />الغرض المعروض للمبادلة:</span>
                    <p @class(['is-struck' => $deal->status === 'rejected'])>{{ $deal->exchangeOffer->label() }}</p>
                  </div>
                @endif

                @if ($deal->requester_note)
                  <div class="incoming-request__box">
                    <span class="muted">رسالة الجار:</span>
                    <p>"{{ $deal->requester_note }}"</p>
                  </div>
                @endif

                <div class="incoming-request__actions">
                  @if ($deal->status === 'waiting')
                    <button type="button" class="btn btn--primary btn--sm js-open-modal" data-modal="accept-modal"
                            data-action="{{ route('requests.accept', $deal) }}"
                            data-text-subtitle="طلب من {{ $deal->requester->full_name }} • {{ $item->title }}">
                      <x-icon name="check" />قبول الطلب
                    </button>
                    <button type="button" class="btn btn--soft btn--sm btn--danger-text js-open-modal" data-modal="reject-modal"
                            data-action="{{ route('requests.reject', $deal) }}"
                            data-text-subtitle="طلب من {{ $deal->requester->full_name }} • {{ $item->title }}">
                      <x-icon name="x" />رفض
                    </button>
                  @elseif (in_array($deal->status, ['approved', 'received', 'finished']))
                    <a href="{{ route('requests.show', $deal) }}" class="btn btn--soft btn--sm"><x-icon name="eye" />عرض العملية</a>
                  @endif
                </div>
              </div>
            @endforeach
          </article>
        @empty
          <div class="empty-state">
            <x-icon name="inbox" />
            <p>لا توجد طلبات واردة في هذا القسم.</p>
          </div>
        @endforelse
      </section>

      <aside class="safety-banner">
        <span class="safety-banner__icon"><x-icon name="shield" /></span>
        <div>
          <h2>إرشادات الأمان بين أهل الحي</h2>
          <p>احرص دائماً على تسليم واستلام الأغراض في أماكن عامة معروفة داخل الحي لضمان راحة وطمأنينة الجميع.</p>
        </div>
      </aside>
    </div>
  </main>
@endsection

@push('modals')
  {{-- نافذة قبول الطلب: الموعد والمكان وملاحظة --}}
  <div class="modal" id="accept-modal" role="dialog" aria-modal="true" aria-labelledby="accept-title"
       @if ($errors->hasAny(['meeting_date', 'meeting_location'])) data-open="true" @endif>
    <div class="modal__dialog">
      <form method="POST" action="{{ $oldAction }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="_action" value="{{ $oldAction }}">
        <div class="modal__header">
          <span class="modal__header-icon"><x-icon name="check-circle" /></span>
          <div>
            <h2 id="accept-title">قبول الطلب</h2>
            <p class="js-text-subtitle"></p>
          </div>
          <button type="button" class="modal__close js-close-modal" aria-label="إغلاق"><x-icon name="x" /></button>
        </div>
        <div class="modal__body">
          <div class="form-grid-2">
            <div class="form-group @error('meeting_date') has-error @enderror">
              <label class="form-label" for="accept-date"><x-icon name="calendar" /> وقت التسليم المقترح <span class="required">*</span></label>
              <input class="input" type="datetime-local" id="accept-date" name="meeting_date" value="{{ old('meeting_date') }}">
              <p class="form-error">@error('meeting_date'){{ $message }}@enderror</p>
            </div>
            <div class="form-group @error('meeting_location') has-error @enderror">
              <label class="form-label" for="accept-location"><x-icon name="map-pin" /> مكان اللقاء <span class="required">*</span></label>
              <input class="input" type="text" id="accept-location" name="meeting_location" maxlength="150" value="{{ old('meeting_location') }}" placeholder="مثال: أمام مدخل البناء">
              <p class="form-error">@error('meeting_location'){{ $message }}@enderror</p>
            </div>
          </div>
          <div class="form-group @error('owner_note') has-error @enderror">
            <label class="form-label" for="accept-note">ملاحظة للجار (اختياري)</label>
            <textarea class="textarea" id="accept-note" name="owner_note" maxlength="250" placeholder="اكتب ملاحظة أو نقطة الالتقاء بدقة، مثلاً: أمام مدخل البناء...">{{ old('owner_note') }}</textarea>
            <p class="form-error">@error('owner_note'){{ $message }}@enderror</p>
          </div>
          <p class="alert"><x-icon name="lock" />ستظهر بيانات تواصلكما للطرفين بعد القبول</p>
        </div>
        <div class="modal__footer">
          <button type="submit" class="btn btn--primary"><x-icon name="check" />تأكيد القبول</button>
          <button type="button" class="btn btn--soft js-close-modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>

  {{-- نافذة رفض الطلب: ملاحظة إجبارية --}}
  <div class="modal" id="reject-modal" role="dialog" aria-modal="true" aria-labelledby="reject-title" @error('owner_note') data-open="true" @enderror>
    <div class="modal__dialog">
      <form method="POST" action="{{ $oldAction }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="_action" value="{{ $oldAction }}">
        <div class="modal__header">
          <span class="modal__header-icon"><x-icon name="x-circle" /></span>
          <div>
            <h2 id="reject-title">رفض الطلب</h2>
            <p class="js-text-subtitle"></p>
          </div>
          <button type="button" class="modal__close js-close-modal" aria-label="إغلاق"><x-icon name="x" /></button>
        </div>
        <div class="modal__body">
          <div class="form-group @error('owner_note') has-error @enderror">
            <label class="form-label" for="reject-note">ملاحظة للجار <span class="required">*</span></label>
            <textarea class="textarea" id="reject-note" name="owner_note" maxlength="250" placeholder="مثال: عذراً، تم الاتفاق مع جار آخر سبقك بالطلب.">{{ old('owner_note') }}</textarea>
            <p class="form-error">@error('owner_note'){{ $message }}@enderror</p>
          </div>
        </div>
        <div class="modal__footer">
          <button type="submit" class="btn btn--danger">تأكيد الرفض</button>
          <button type="button" class="btn btn--soft js-close-modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/modal.js') }}"></script>
@endpush
