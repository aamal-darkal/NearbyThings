{{-- صفحة تفاصيل العملية (للطرفين): المراحل، الإجراء المطلوب، الغرض، الطرف الآخر، الموعد، السجل --}}
@extends('layouts.site')

@use('App\Support\Dates')

@php
  $item = $deal->item;
  $isRent = $item->transaction_type === 'rent';
  $rental = $deal->rental;
  $status = $deal->status;
  // التقييمات: تقييمي للطرف الآخر، وتقييمه إلي
  $myReview = \App\Models\Review::where('request_id', $deal->id)->where('reviewer_id', $user->id)->first();
  $theirReview = \App\Models\Review::where('request_id', $deal->id)->where('reviewed_user_id', $user->id)->first();

  $titles = ['sale' => 'تفاصيل عملية البيع', 'rent' => 'تفاصيل عملية التأجير', 'exchange' => 'تفاصيل عملية التبادل'];

  // ===== مراحل العملية =====
  $appointmentConfirmed = $deal->appointments->contains('status', 'confirmed') || in_array($status, ['received', 'finished']);
  $returned = $rental && in_array($rental->return_status, ['confirmed', 'auto_confirmed', 'disputed']);

  $steps = [
      ['label' => 'تم القبول', 'done' => in_array($status, ['approved', 'received', 'finished'])],
      ['label' => 'تأكيد موعد التسليم', 'done' => $appointmentConfirmed],
      ['label' => 'تم الاستلام', 'done' => in_array($status, ['received', 'finished'])],
  ];
  if ($isRent) {
      $steps[] = ['label' => 'تم الإرجاع', 'done' => $returned];
  }
  $steps[] = ['label' => 'التقييم', 'done' => $myReview !== null];

  // أول مرحلة ما خلصت هي المرحلة الحالية
  $currentStep = collect($steps)->search(fn ($step) => ! $step['done']);

  // ===== تكلفة التأجير =====
  if ($rental) {
      $days = $rental->days();
      $units = (int) ceil($days / ['day' => 1, 'month' => 30, 'year' => 365][$item->rent_unit ?? 'day']);
      $total = $units * $item->price;
  }

  $proposerId = $appointment ? ($appointment->proposed_by ?? $item->owner_id) : null;
  $canRespondAppointment = $appointment && $status === 'approved' && $appointment->status === 'new' && $proposerId !== $user->id;
@endphp

@section('title', $titles[$item->transaction_type])

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/requests.css') }}">
@endpush

@section('content')
  <main class="page">
    <div class="container">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        @if ($isOwner)
          <a href="{{ route('requests.incoming') }}">الطلبات الواردة</a>
        @else
          <a href="{{ route('requests.index') }}">طلباتي</a>
        @endif
        <x-icon name="chevron-left" />
        <span class="current">تفاصيل العملية</span>
      </nav>

      <div class="page-head">
        <div>
          <h1>{{ $titles[$item->transaction_type] }} <span class="status {{ $deal->statusClass() }}">{{ $deal->statusLabel() }}</span></h1>
          <p>متابعة استلام {{ $isRent ? 'وإرجاع ' : '' }}الغرض والتنسيق مع {{ $isOwner ? 'الجار' : 'المالك' }} مباشرة</p>
        </div>
        <span class="tag"><x-icon name="shield" />تعامل يداً بيد</span>
      </div>

      {{-- ===== مراحل العملية ===== --}}
      @if (! in_array($status, ['rejected', 'canceled', 'waiting']))
        <ol class="panel deal-steps">
          @foreach ($steps as $index => $step)
            <li @class(['deal-step', 'is-done' => $step['done'], 'is-current' => $index === $currentStep])>
              <span class="deal-step__dot">
                @if ($step['done'])<x-icon name="check" />@else{{ $index + 1 }}@endif
              </span>
              <strong>{{ $index + 1 }}. {{ $step['label'] }}</strong>
              <small>{{ $step['done'] ? 'مكتمل' : ($index === $currentStep ? 'الخطوة الحالية' : 'قادم') }}</small>
            </li>
          @endforeach
        </ol>
      @endif

      {{-- ===== الإجراء المطلوب حالياً ===== --}}
      <section class="panel deal-action">
        @if ($status === 'waiting')
          @if ($isOwner)
            <span class="deal-action__icon"><x-icon name="inbox" /></span>
            <div class="deal-action__text">
              <h2>طلب جديد بانتظار ردك</h2>
              <p>راجع الطلب، ثم اقبله مع تحديد موعد ومكان التسليم، أو ارفضه مع ملاحظة.</p>
            </div>
            <div class="deal-action__buttons">
              <a href="{{ route('requests.incoming') }}" class="btn btn--primary">الرد على الطلب</a>
            </div>
          @else
            <span class="deal-action__icon"><x-icon name="clock" /></span>
            <div class="deal-action__text">
              <h2>بانتظار رد المالك</h2>
              <p>سيصلك إشعار فور قبول المالك أو رفضه لطلبك. يمكنك إلغاء الطلب قبل قبوله.</p>
            </div>
            <form method="POST" action="{{ route('requests.cancel', $deal) }}" class="js-confirm" data-confirm="هل تريد إلغاء هذا الطلب؟">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn btn--text-danger">إلغاء الطلب</button>
            </form>
          @endif

        @elseif ($status === 'rejected')
          <span class="deal-action__icon deal-action__icon--danger"><x-icon name="x-circle" /></span>
          <div class="deal-action__text">
            <h2>تم رفض الطلب</h2>
            <p>ملاحظة المالك: {{ $deal->owner_note ?: '—' }}</p>
          </div>

        @elseif ($status === 'canceled')
          <span class="deal-action__icon"><x-icon name="ban" /></span>
          <div class="deal-action__text">
            <h2>تم إلغاء الطلب</h2>
            <p>ألغى الباحث هذا الطلب قبل قبوله.</p>
          </div>

        @elseif ($status === 'approved')
          @if ($canRespondAppointment)
            <span class="deal-action__icon"><x-icon name="calendar" /></span>
            <div class="deal-action__text">
              <h2>{{ $isOwner ? 'اقترح الجار موعداً جديداً' : 'حدد المالك موعد التسليم' }}</h2>
              <p>{{ Dates::dateTime($appointment->meeting_date) }} في {{ $appointment->meeting_location }}. أكد الموعد أو اقترح موعداً آخر.</p>
            </div>
            <div class="deal-action__buttons">
              <form method="POST" action="{{ route('requests.appointment.confirm', $deal) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn--primary"><x-icon name="check" />تأكيد الموعد</button>
              </form>
              <button type="button" class="btn btn--soft js-open-modal" data-modal="appointment-modal">اقتراح موعد جديد</button>
            </div>
          @elseif ($isOwner)
            <span class="deal-action__icon"><x-icon name="clock" /></span>
            <div class="deal-action__text">
              <h2>{{ $appointment && $appointment->status === 'confirmed' ? 'الموعد مؤكد، بانتظار الاستلام' : 'بانتظار تأكيد الجار للموعد' }}</h2>
              <p>بعد اللقاء وتسليم الغرض، سيؤكد الجار الاستلام من حسابه لإتمام العملية.</p>
            </div>
            <div class="deal-action__buttons">
              <button type="button" class="btn btn--soft js-open-modal" data-modal="appointment-modal">تعديل الموعد</button>
            </div>
          @else
            <span class="deal-action__icon"><x-icon name="handshake" /></span>
            <div class="deal-action__text">
              <h2>{{ $appointment && $appointment->status === 'confirmed' ? 'الموعد مؤكد' : 'بانتظار تأكيد المالك للموعد الجديد' }}</h2>
              <p>عاين الغرض عند اللقاء، ثم أكد الاستلام هنا {{ $isRent ? 'لتبدأ فترة التأجير' : 'لإتمام العملية' }}.</p>
            </div>
            <div class="deal-action__buttons">
              <form method="POST" action="{{ route('requests.receive', $deal) }}" class="js-confirm" data-confirm="هل استلمت الغرض فعلاً؟">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn--primary"><x-icon name="check-circle" />تأكيد الاستلام</button>
              </form>
              <button type="button" class="btn btn--soft js-open-modal" data-modal="appointment-modal">اقتراح موعد جديد</button>
            </div>
          @endif

        @elseif ($status === 'received' && $rental)
          @if (! $rental->return_reported_at)
            <span class="deal-action__icon"><x-icon name="package" /></span>
            <div class="deal-action__text">
              <h2>{{ $isOwner ? 'الغرض لدى المستأجر حالياً' : 'الغرض بحوزتك حالياً' }} <span class="tag"><x-icon name="clock" />موعد الإرجاع: {{ Dates::full($rental->expected_return_date) }}</span></h2>
              <p>{{ $isOwner ? 'سيصلك إشعار عندما يُبلغ المستأجر عن إرجاع الغرض.' : 'يرجى الحفاظ عليه وإرجاعه بالموعد المحدد بنفس الحالة. عند إرجاعه اضغط الزر لإعلام المالك.' }}</p>
            </div>
            @unless ($isOwner)
              <form method="POST" action="{{ route('requests.return', $deal) }}" class="js-confirm" data-confirm="هل أرجعت الغرض للمالك؟">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn--primary"><x-icon name="refresh" />إشعار المالك بالإرجاع</button>
              </form>
            @endunless
          @else
            <span class="deal-action__icon"><x-icon name="refresh" /></span>
            <div class="deal-action__text">
              @if ($isOwner)
                <h2>أبلغ المستأجر عن إرجاع الغرض</h2>
                <p>أكد الإرجاع أو اعترض قبل {{ Dates::dateTime($rental->confirmDeadline()) }}، وإلا يُعتبر الغرض مُرجعاً تلقائياً.</p>
              @else
                <h2>بانتظار تأكيد المالك للإرجاع</h2>
                <p>إذا لم يرد المالك قبل {{ Dates::dateTime($rental->confirmDeadline()) }} يُعتبر الغرض مُرجعاً تلقائياً.</p>
              @endif
            </div>
            @if ($isOwner)
              <div class="deal-action__buttons">
                <form method="POST" action="{{ route('requests.return.confirm', $deal) }}">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn--primary"><x-icon name="check" />تأكيد الإرجاع</button>
                </form>
                <button type="button" class="btn btn--soft btn--danger-text js-open-modal" data-modal="dispute-modal"><x-icon name="flag" />اعتراض</button>
              </div>
            @endif
          @endif

        @elseif ($status === 'finished')
          <span class="deal-action__icon"><x-icon name="check-circle" /></span>
          <div class="deal-action__text">
            <h2>تمت العملية بنجاح</h2>
            <p>
              @if ($rental && $rental->return_status === 'disputed')
                تم تسجيل اعتراض على الإرجاع وتحويله لبلاغ يراجعه المشرف.
              @elseif ($rental && $rental->return_status === 'auto_confirmed')
                تم تأكيد الإرجاع تلقائياً بعد مرور 48 ساعة.
              @else
                شكراً لتعاملك مع جيرانك.
              @endif
            </p>
          </div>
        @endif
      </section>

      <div class="deal-grid">
        <div class="deal-grid__main">
          {{-- ملخص الغرض --}}
          <section class="panel deal-card">
            <div class="deal-card__head">
              <h2><x-icon name="package" />ملخص الغرض</h2>
              <span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span>
            </div>
            <div class="deal-item">
              <div>
                <h3><a href="{{ route('items.show', $item) }}">{{ $item->title }}</a></h3>
                <p class="muted">{{ $item->category->category_name }}</p>
                <strong class="deal-item__price">{{ $item->priceLabel() }}</strong>
              </div>
              @if ($item->mainImageUrl())
                <img src="{{ $item->mainImageUrl() }}" alt="{{ $item->title }}">
              @endif
            </div>

            @if ($rental)
              <dl class="deal-facts">
                <div><dt>فترة التأجير:</dt><dd>من {{ Dates::full($rental->start_date) }} إلى {{ Dates::full($rental->expected_return_date) }}</dd></div>
                <div><dt>المدة المحتسبة:</dt><dd>{{ Dates::count($days, 'يوم', 'يومين', 'أيام') }}</dd></div>
                <div><dt>حالة الإرجاع:</dt><dd>{{ $rental->returnStatusLabel() }}</dd></div>
                <div class="deal-facts__total"><dt>إجمالي المبلغ المتفق عليه:</dt><dd>{{ number_format($total) }} ل.س</dd></div>
              </dl>
            @endif

            @if ($deal->exchangeOffer)
              <p class="alert"><x-icon name="repeat" /><span><strong>المقابل المعروض:</strong> {{ $deal->exchangeOffer->label() }}</span></p>
            @endif

            @if ($deal->requester_note)
              <p class="alert alert--plain"><x-icon name="message" /><span><strong>رسالة الباحث:</strong> {{ $deal->requester_note }}</span></p>
            @endif

            <p class="deal-note"><x-icon name="handshake" />الدفع نقداً يداً بيد عند المعاينة والاستلام</p>
          </section>

          {{-- تفاصيل التسليم --}}
          @if ($appointment)
            <section class="panel deal-card">
              <div class="deal-card__head">
                <h2><x-icon name="calendar" />تفاصيل التسليم</h2>
                <span class="tag">{{ $appointment->statusLabel() }}</span>
              </div>
              <div class="deal-box">
                <span class="muted">الموعد المحدد {{ $proposerId === $user->id ? 'من قبلك' : ($proposerId === $item->owner_id ? 'من قبل المالك' : 'من قبل الجار') }}</span>
                <strong>{{ Dates::dateTime($appointment->meeting_date) }}</strong>
              </div>
              <div class="deal-box">
                <span class="muted"><x-icon name="map-pin" />موقع اللقاء</span>
                <strong>{{ $appointment->meeting_location }}</strong>
                @if ($deal->owner_note)
                  <p>ملاحظة المالك: {{ $deal->owner_note }}</p>
                @endif
              </div>
            </section>
          @endif
        </div>

        <aside class="deal-grid__side">
          {{-- الطرف الآخر --}}
          <section class="panel deal-card">
            <div class="deal-card__head">
              <h2><x-icon name="user" />{{ $isOwner ? 'الباحث' : 'المالك' }} (الطرف الآخر)</h2>
              <span class="tag">{{ $isOwner ? ($isRent ? 'مستأجر' : 'مشتري') : ($isRent ? 'مؤجّر الغرض' : 'بائع') }}</span>
            </div>
            <div class="party">
              <span class="avatar">{{ $other->initial() }}</span>
              <div>
                <strong>{{ $other->full_name }}</strong>
                <span class="muted"><span class="rating-inline"><x-icon name="star" />{{ number_format($other->rate, 1) }}</span> • {{ $other->address ?: '—' }}</span>
              </div>
            </div>

            {{-- بيانات التواصل: بس بعد قبول الطلب --}}
            @if ($deal->contactVisible())
              <div class="contact-box">
                <span class="muted">رقم الهاتف المباشر:</span>
                <a href="tel:{{ $other->phone }}" class="contact-box__phone"><x-icon name="phone" />{{ $other->phone ?: 'غير متوفر' }}</a>
                <a href="mailto:{{ $other->email }}" class="contact-box__mail"><x-icon name="mail" />{{ $other->email }}</a>
              </div>
              <p class="alert"><x-icon name="lock" />ظهرت بيانات التواصل بعد قبول الطلب لضمان الخصوصية وسرعة التنسيق.</p>
            @else
              <p class="alert alert--plain"><x-icon name="lock" />تظهر بيانات التواصل للطرفين فقط بعد قبول الطلب.</p>
            @endif
          </section>

          <section class="panel deal-tip">
            <x-icon name="lightbulb" />
            <div>
              <strong>نصيحة الجيرة الآمنة</strong>
              <p>اللقاء في الأماكن العامة نهاراً يضمن راحة وأمان كلا الجارين.</p>
            </div>
          </section>
        </aside>
      </div>

      {{-- ===== التقييم المتبادل (بعد انتهاء العملية) ===== --}}
      @if ($status === 'finished' && $deal->isParty($user))
        <section class="panel deal-card deal-review" id="review">
          <div class="deal-card__head">
            <h2><x-icon name="star" />التقييم المتبادل</h2>
          </div>

          @if ($myReview)
            <div class="review-given">
              <span class="muted">تقييمك لـ {{ $other->full_name }}:</span>
              <span class="stars" aria-label="{{ $myReview->rating }} من 5">
                @for ($i = 1; $i <= 5; $i++)<x-icon name="star" @class(['is-empty' => $i > $myReview->rating]) />@endfor
              </span>
              @if ($myReview->comment)<p>"{{ $myReview->comment }}"</p>@endif
            </div>
          @else
            <form method="POST" action="{{ route('reviews.store', $deal) }}" class="review-form">
              @csrf
              <p>كيف كانت تجربتك مع <strong>{{ $other->full_name }}</strong>؟ تقييمك يساعد الجيران على التعامل بثقة.</p>
              <fieldset class="star-input @error('rating') has-error @enderror">
                <legend class="sr-only">التقييم من 1 إلى 5</legend>
                {{-- النجوم بترتيب عكسي حتى يشتغل تلوين النجوم السابقة بـ CSS فقط --}}
                @for ($i = 5; $i >= 1; $i--)
                  <input type="radio" id="star-{{ $i }}" name="rating" value="{{ $i }}" @checked(old('rating') == $i)>
                  <label for="star-{{ $i }}" aria-label="{{ $i }} نجوم"><x-icon name="star" /></label>
                @endfor
              </fieldset>
              @error('rating')<p class="form-error form-error--show">{{ $message }}</p>@enderror
              <div class="form-group">
                <label class="form-label" for="review-comment">تعليق (اختياري)</label>
                <textarea class="textarea" id="review-comment" name="comment" maxlength="500" placeholder="مثال: تعامل راقٍ والغرض مطابق للوصف">{{ old('comment') }}</textarea>
              </div>
              <button type="submit" class="btn btn--primary"><x-icon name="star" />إرسال التقييم</button>
            </form>
          @endif

          @if ($theirReview)
            <div class="review-given review-given--theirs">
              <span class="muted">تقييم {{ $other->full_name }} لك:</span>
              <span class="stars" aria-label="{{ $theirReview->rating }} من 5">
                @for ($i = 1; $i <= 5; $i++)<x-icon name="star" @class(['is-empty' => $i > $theirReview->rating]) />@endfor
              </span>
              @if ($theirReview->comment)<p>"{{ $theirReview->comment }}"</p>@endif
            </div>
          @endif
        </section>
      @endif

      @if ($deal->isParty($user) && ! in_array($status, ['waiting', 'canceled']))
        <p class="deal-report"><a href="{{ route('reports.create', ['request' => $deal->id]) }}" class="link link--danger"><x-icon name="flag" />الإبلاغ عن مشكلة في هذه العملية</a></p>
      @endif

      {{-- ===== سجل نشاط العملية ===== --}}
      <section class="panel deal-card deal-log">
        <div class="deal-card__head">
          <h2><x-icon name="history" />سجل نشاط العملية</h2>
        </div>
        <ol class="timeline">
          <li class="is-done"><strong>تم إرسال الطلب من قبل {{ $deal->requester->full_name }}</strong><small>{{ Dates::dateTime($deal->request_date) }}</small></li>

          @foreach ($deal->appointments->sortBy('id') as $timelineAppointment)
            <li @class(['is-done' => $timelineAppointment->status !== 'canceled', 'is-muted' => $timelineAppointment->status === 'canceled'])>
              <strong>
                {{ $loop->first ? 'تم قبول الطلب وتحديد موعد التسليم' : 'اقتراح موعد جديد' }}
                @if ($timelineAppointment->status === 'canceled') (استُبدل) @endif
                @if ($timelineAppointment->status === 'confirmed') — موعد مؤكد @endif
              </strong>
              <small>{{ Dates::dateTime($timelineAppointment->created_at) }} • الموعد: {{ Dates::dateTime($timelineAppointment->meeting_date) }}</small>
            </li>
          @endforeach

          @if (in_array($status, ['rejected', 'canceled']))
            <li class="is-muted"><strong>{{ $status === 'rejected' ? 'تم رفض الطلب' : 'تم إلغاء الطلب' }}</strong><small>{{ Dates::dateTime($deal->updated_at) }}</small></li>
          @endif

          @if (in_array($status, ['received', 'finished']))
            <li class="is-done"><strong>تم استلام الغرض{{ $isRent ? ' وبدء فترة التأجير' : '' }}</strong></li>
          @endif

          @if ($rental && $rental->return_reported_at)
            <li class="is-done"><strong>أبلغ المستأجر عن إرجاع الغرض</strong><small>{{ Dates::dateTime($rental->return_reported_at) }}</small></li>
          @endif

          @if ($status === 'finished')
            <li class="is-done"><strong>{{ $rental ? 'انتهت العملية: '.$rental->returnStatusLabel() : 'اكتملت العملية' }}</strong><small>{{ Dates::dateTime($deal->updated_at) }}</small></li>
          @elseif ($isRent && in_array($status, ['approved', 'received']))
            <li class="is-pending"><strong>إرجاع الغرض وإنهاء العملية</strong>@if ($rental)<small>متوقع في {{ Dates::full($rental->expected_return_date) }}</small>@endif</li>
          @endif
        </ol>
      </section>
    </div>
  </main>
@endsection

@push('modals')
  {{-- نافذة اقتراح موعد جديد --}}
  @if ($status === 'approved')
    <div class="modal" id="appointment-modal" role="dialog" aria-modal="true" aria-labelledby="appointment-title"
         @if ($errors->hasAny(['meeting_date', 'meeting_location'])) data-open="true" @endif>
      <div class="modal__dialog">
        <form method="POST" action="{{ route('requests.appointment.propose', $deal) }}">
          @csrf
          <div class="modal__header">
            <span class="modal__header-icon"><x-icon name="calendar" /></span>
            <div>
              <h2 id="appointment-title">اقتراح موعد جديد</h2>
              <p>سيصل الموعد للطرف الآخر ليؤكده</p>
            </div>
            <button type="button" class="modal__close js-close-modal" aria-label="إغلاق"><x-icon name="x" /></button>
          </div>
          <div class="modal__body">
            <div class="form-group @error('meeting_date') has-error @enderror">
              <label class="form-label" for="new-date">الموعد <span class="required">*</span></label>
              <input class="input" type="datetime-local" id="new-date" name="meeting_date" value="{{ old('meeting_date') }}">
              <p class="form-error">@error('meeting_date'){{ $message }}@enderror</p>
            </div>
            <div class="form-group @error('meeting_location') has-error @enderror">
              <label class="form-label" for="new-location">مكان اللقاء <span class="required">*</span></label>
              <input class="input" type="text" id="new-location" name="meeting_location" maxlength="150" value="{{ old('meeting_location', $appointment?->meeting_location) }}">
              <p class="form-error">@error('meeting_location'){{ $message }}@enderror</p>
            </div>
          </div>
          <div class="modal__footer">
            <button type="submit" class="btn btn--primary">إرسال الموعد</button>
            <button type="button" class="btn btn--soft js-close-modal">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  {{-- نافذة الاعتراض على الإرجاع (للمالك) --}}
  @if ($isOwner && $rental && $rental->awaitingOwner())
    <div class="modal" id="dispute-modal" role="dialog" aria-modal="true" aria-labelledby="dispute-title" @error('reason') data-open="true" @enderror>
      <div class="modal__dialog">
        <form method="POST" action="{{ route('requests.return.dispute', $deal) }}">
          @csrf
          @method('PATCH')
          <div class="modal__header">
            <span class="modal__header-icon"><x-icon name="flag" /></span>
            <div>
              <h2 id="dispute-title">الاعتراض على الإرجاع</h2>
              <p>سيتحول الاعتراض إلى بلاغ يراجعه مشرف النظام</p>
            </div>
            <button type="button" class="modal__close js-close-modal" aria-label="إغلاق"><x-icon name="x" /></button>
          </div>
          <div class="modal__body">
            <div class="form-group @error('reason') has-error @enderror">
              <label class="form-label" for="dispute-reason">سبب الاعتراض <span class="required">*</span></label>
              <textarea class="textarea" id="dispute-reason" name="reason" maxlength="500" placeholder="مثال: أُرجع الغرض ناقصاً أو تالفاً...">{{ old('reason') }}</textarea>
              <p class="form-error">@error('reason'){{ $message }}@enderror</p>
            </div>
          </div>
          <div class="modal__footer">
            <button type="submit" class="btn btn--danger">إرسال الاعتراض</button>
            <button type="button" class="btn btn--soft js-close-modal">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  @endif
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/modal.js') }}"></script>
@endpush
