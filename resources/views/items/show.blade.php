{{-- صفحة تفاصيل الغرض --}}
@extends('layouts.site')

@section('title', 'تفاصيل الغرض')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/item-details.css') }}">
@endpush

@section('content')
  <main class="page">
    <div class="container">
      <!-- مسار التنقل -->
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg> الرئيسية</a>
        <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m15 18-6-6 6-6"></path></svg>
        <a href="{{ route('home') }}#categories-section">دراجات ومعدات رياضية</a>
        <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m15 18-6-6 6-6"></path></svg>
        <span class="current">دراجة هوائية جبلية ماركة ترينكس مقاس 26 - ألمنيوم خفيف</span>
      </nav>

      <div class="item-layout">
        <!-- العمود الأول: المعرض والوصف -->
        <div>
          <section class="panel gallery" aria-label="صور الغرض">
            <div class="gallery__main">
              <img id="gallery-main" src="{{ asset('assets/images/item-gallery/bike-1.jpg') }}" alt="صورة الغرض">
              <span class="badge badge--sale"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle></svg>للبيع</span>
              <span class="gallery__counter"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"></path><circle cx="12" cy="13" r="3"></circle></svg><span id="gallery-counter">1 من 4 صور</span></span>
              <button class="gallery__expand" id="gallery-expand" type="button" aria-label="عرض بملء الشاشة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M8 3H5a2 2 0 0 0-2 2v3"></path><path d="M21 8V5a2 2 0 0 0-2-2h-3"></path><path d="M3 16v3a2 2 0 0 0 2 2h3"></path><path d="M16 21h3a2 2 0 0 0 2-2v-3"></path></svg></button>
            </div>
            <div class="gallery__thumbs">
              <button class="thumb is-active" type="button" data-src="{{ asset('assets/images/item-gallery/bike-1.jpg') }}" aria-label="الصورة 1">
                <img src="{{ asset('assets/images/item-gallery/bike-1.jpg') }}" alt="">
              </button>
              <button class="thumb" type="button" data-src="{{ asset('assets/images/item-gallery/bike-2.jpg') }}" aria-label="الصورة 2">
                <img src="{{ asset('assets/images/item-gallery/bike-2.jpg') }}" alt="">
              </button>
              <button class="thumb" type="button" data-src="{{ asset('assets/images/item-gallery/bike-3.jpg') }}" aria-label="الصورة 3">
                <img src="{{ asset('assets/images/item-gallery/bike-3.jpg') }}" alt="">
              </button>
              <button class="thumb" type="button" data-src="{{ asset('assets/images/item-gallery/bike-4.jpg') }}" aria-label="الصورة 4">
                <img src="{{ asset('assets/images/item-gallery/bike-4.jpg') }}" alt="">
              </button>
            </div>
          </section>

          <section class="panel description">
            <h2>وصف الغرض</h2>
            <p>دراجة هوائية جبلية ماركة ترينكس Trinx بحالة ممتازة جداً ونظيفة. شاسي مصنوع من الألمنيوم 6061 خفيف الوزن ومقاوم للصدأ، مزودة بـ 21 سرعة بنظام غيارات شيمانو Shimano أصلي، فرامل ديسك مزدوجة أمامية وخلفية بحالة ممتازة، كوشوك جديد عريض مقاس 26 بوصة مع مساعدات أمامية لامتصاص الصدمات. تم استخدامها لفترة وجيزة للمشاوير القريبة داخل الحي. جاهزة تماماً للاستخدام الفوري والمعاينة في حي المزة.</p>
          </section>

          <section class="specs" aria-label="المواصفات">
          <div class="panel spec">
            <div class="spec__label">المقاس</div>
            <div class="spec__value">26 بوصة</div>
            <div class="spec__note">مناسب للبالغين</div>
          </div>
          <div class="panel spec">
            <div class="spec__label">هيكل الدراجة</div>
            <div class="spec__value">ألمنيوم 6061</div>
            <div class="spec__note">خفيف ومقاوم للصدأ</div>
          </div>
          <div class="panel spec">
            <div class="spec__label">ناقل الحركة</div>
            <div class="spec__value">21 سرعة</div>
            <div class="spec__note">Shimano أصلي</div>
          </div>
          <div class="panel spec">
            <div class="spec__label">نوع الفرامل</div>
            <div class="spec__value">ديسك مزدوج</div>
            <div class="spec__note">أمامي وخلفي</div>
          </div>
          </section>

          <aside class="safe-meetup">
            <span class="safe-meetup__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg></span>
            <div>
              <h3>معاينة آمنة وجهاً لوجه</h3>
              <p>المالك مستعد للقاء المباشر للمعاينة والتجربة في نقطة مأهولة بحي المزة (قرب حديقة الطلائع).</p>
            </div>
          </aside>
        </div>

        <!-- العمود الثاني: بطاقة المعلومات -->
        <aside class="panel info-card">
          <div class="tags">
            <span class="tag"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>دراجات ومعدات رياضية</span>
            <span class="tag tag--accent"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle></svg>شبه جديد (استعمال خفيف)</span>
          </div>
          <p class="posted"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>منذ يومين</p>

          <h1>دراجة هوائية جبلية ماركة ترينكس مقاس 26 - ألمنيوم خفيف</h1>

          <div class="price-box">
            <strong>12,000</strong><span>ل.س</span>
          </div>

          <div class="location-box">
            <div class="location-box__head">
              <span><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>حي المزة (قرب حديقة الطلائع)، دمشق</span>
              <span class="tag tag--accent">يبعد عنك 1.2 كم فقط</span>
            </div>
            <div class="map-preview">
              <img src="{{ asset('assets/images/map-preview.jpg') }}" alt="خريطة تقريبية لموقع الغرض">
            </div>
          </div>
          <p class="location-note">الموقع تقريبي حفاظاً على الخصوصية. تظهر بيانات التواصل بعد قبول المالك لطلبك.</p>

          <div class="owner">
            <span class="avatar">أ</span>
            <div>
              <div class="owner__name">أحمد م.</div>
              <div class="owner__meta">14 صفقة ناجحة</div>
            </div>
            <div class="owner__rating">
              <strong><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>4.9</strong>
              <div class="owner__meta">18 تقييم</div>
            </div>
          </div>

          <div class="actions">
            <button class="btn btn--primary" id="request-btn" type="button"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg>أرسل طلباً</button>
            <button class="btn btn--soft js-fav" id="fav-btn" type="button" data-id="101" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
          </div>
          <a href="#" class="report-link js-soon" data-message="صفحة الإبلاغ عن مخالفة ضمن المرحلة القادمة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" x2="4" y1="22" y2="15"></line></svg>إبلاغ</a>
        </aside>
      </div>

      <!-- أغراض قريبة -->
      <section class="section related">
        <div class="section-head">
          <div>
            <h2 class="section-title section-title--sm">أغراض رياضية أخرى قريبة في حي المزة</h2>
            <p class="section-subtitle">معروضة من جيرانك على بعد أقل من 3 كم</p>
          </div>
          <a href="{{ route('home') }}#nearby" class="link">عرض المزيد <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg></a>
        </div>
        <div class="items-grid">
          <article class="item-card" data-deal="sale">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/helmet.jpg') }}" alt="خوذة دراجات هوائية" loading="lazy">
              <span class="badge badge--sale">بيع</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="201" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>800 متر</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 201) }}">خوذة دراجات هوائية</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">950 ل.س</span>
                <span class="item-card__area">المزة فيلات غربية</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="sale">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/lock.jpg') }}" alt="قفل دراجة فولاذي فائق الأمان" loading="lazy">
              <span class="badge badge--sale">بيع</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="202" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>2.5 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 202) }}">قفل دراجة فولاذي فائق الأمان</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">1,200 ل.س</span>
                <span class="item-card__area">المزة 86</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="rent">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/scooter.jpg') }}" alt="سكوتر كهربائي Pro متاح للتأجير" loading="lazy">
              <span class="badge badge--rent">تأجير</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="203" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>2.1 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 203) }}">سكوتر كهربائي Pro متاح للتأجير</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">350 ل.س<small>/ يوم</small></span>
                <span class="item-card__area">المزة شيخ سعد</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="exchange">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/pump.jpg') }}" alt="منفاخ دراجات أرضي أصلي" loading="lazy">
              <span class="badge badge--exchange">تبادل</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="204" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>1.5 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 204) }}">منفاخ دراجات أرضي أصلي</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">متاح للتبادل</span>
                <span class="item-card__area">المزة أوتوستراد</span>
              </div>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
@endsection

@push('modals')
  <!-- نافذة إرسال الطلب -->
  <div class="modal" id="request-modal" role="dialog" aria-modal="true" aria-labelledby="request-title">
    <div class="modal__dialog">
      <div class="modal__header">
        <span class="modal__header-icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg></span>
        <div>
          <h2 id="request-title">إرسال طلب إلى المالك</h2>
          <p>سيصل طلبك للمالك ليقبله أو يرفضه</p>
        </div>
        <button class="modal__close js-close-modal" type="button" aria-label="إغلاق"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></button>
      </div>

      <form id="request-form">
        <div class="modal__body">
          <div class="request-summary">
            <img src="{{ asset('assets/images/item-gallery/bike-1.jpg') }}" alt="">
            <div>
              <span class="badge badge--sale">بيع</span>
              <span class="price">12,000 ل.س</span>
              <h3>دراجة هوائية جبلية ماركة ترينكس مقاس 26 - ألمنيوم خفيف</h3>
              <p>المالك: أحمد م. • حي المزة</p>
            </div>
          </div>

          <div class="form-label-row">
            <label class="form-label" for="request-message">رسالة أو ملاحظة للمالك (اختياري)</label>
          </div>
          <textarea class="textarea" id="request-message" maxlength="250" placeholder="مثال: متى يناسبك التسليم؟"></textarea>
          <p class="form-note"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>سيصلك إشعار عند رد المالك على طلبك</p>

          <div class="privacy-box">
            <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <div>
              <strong>ملاحظة هامة لحماية الخصوصية:</strong>
              حفاظاً على الخصوصية، تظهر بيانات التواصل المباشر (رقم الهاتف وموقع اللقاء الدقيق) فور قبول المالك لطلبك واعتماد الموعد.
            </div>
          </div>
        </div>

        <div class="modal__footer">
          <button type="submit" class="btn btn--primary btn--lg"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"></path><path d="m9 12 2 2 4-4"></path></svg>تأكيد وإرسال الطلب</button>
          <button type="button" class="btn btn--soft btn--lg js-close-modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>

  <!-- عارض الصور بملء الشاشة -->
  <div class="lightbox" id="lightbox" role="dialog" aria-label="عرض الصورة">
    <button class="lightbox__close" type="button" aria-label="إغلاق"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></button>
    <img id="lightbox-image" src="{{ asset('assets/images/item-gallery/bike-1.jpg') }}" alt="صورة الغرض بحجم كامل">
  </div>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/item-details.js') }}"></script>
@endpush
