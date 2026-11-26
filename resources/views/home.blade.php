{{-- الصفحة الرئيسية --}}
@extends('layouts.site')

@section('title', 'الرئيسية')
@section('description', 'غرضك قريب: منصة محلية للبيع والتبادل والتأجير بين الجيران')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
  <!-- مكتبة AOS للحركة -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
@endpush

@section('content')
  <main>
    <!-- القسم الترحيبي + البحث -->
    <section class="hero">
      <!-- صور طايرة للزينة على الجانبين -->
      <img class="hero__float hero__float--right" src="{{ asset('assets/images/items/coffee.jpg') }}" alt="" aria-hidden="true">
      <img class="hero__float hero__float--left" src="{{ asset('assets/images/items/helmet.jpg') }}" alt="" aria-hidden="true">
      <img class="hero__float hero__float--left-small" src="{{ asset('assets/images/items/camera.jpg') }}" alt="" aria-hidden="true">

      <div class="container">
        <h1 class="hero__title" data-aos="fade-up">غرضك <span class="highlight">قريب</span> منك</h1>
        <p class="hero__subtitle" data-aos="fade-up" data-aos-delay="200">تصفح الأغراض المستعملة المعروضة للبيع أو التبادل أو التأجير في حيك مباشرة</p>

        <form class="search-box" id="search-form" role="search" data-aos="fade-up" data-aos-delay="400">
          <div class="search-field search-field--keyword">
            <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
            <label for="search-keyword" class="sr-only">عن ماذا تبحث؟</label>
            <input type="search" id="search-keyword" placeholder="عن ماذا تبحث؟ مثال: دراجة، خيمة" autocomplete="off">
          </div>

          <div class="search-field search-field--area">
            <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <div class="search-field__stack">
              <label for="search-area">ابحث في منطقة</label>
              <input type="text" id="search-area" value="حي الياسمين">
            </div>
          </div>

          <button type="submit" class="btn btn--primary"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>بحث</button>
        </form>

        <div class="deal-filter">
          <span>نوع العملية (اختياري):</span>
          <button type="button" class="chip" data-deal="sale">بيع</button>
          <button type="button" class="chip" data-deal="exchange">تبادل</button>
          <button type="button" class="chip" data-deal="rent">تأجير</button>
        </div>

        <ul class="hero__features">
          <li><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M20 6 9 17l-5-5"></path></svg>تعامل مباشر مع جيرانك</li>
          <li><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M20 6 9 17l-5-5"></path></svg>موقعك تقريبي ليحمي خصوصيتك</li>
          <li><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M20 6 9 17l-5-5"></path></svg>تقييمات من المجتمع المحلي</li>
        </ul>
      </div>
    </section>

    <div class="container">
      <!-- التصنيفات -->
      <section class="section section--first" id="categories-section">
        <div class="section-head" data-aos="fade-up">
          <h2 class="section-title section-title--sm">استكشف حسب التصنيف</h2>
          <a href="#nearby" class="link js-show-all">عرض الكل <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg></a>
        </div>
        <div class="categories">
          <button class="category-card" type="button" data-category="furniture">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"></path><path d="M2 16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v1.5a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5V11a2 2 0 0 0-4 0z"></path><path d="M4 18v2"></path><path d="M20 18v2"></path><path d="M12 4v9"></path></svg></span>
            <span>أثاث ومنزل</span>
          </button>
          <button class="category-card" type="button" data-category="electronics">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M18 8V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h8"></path><path d="M10 19v-3.96 3.15"></path><path d="M7 19h5"></path><rect width="6" height="10" x="16" y="12" rx="2"></rect></svg></span>
            <span>أجهزة وإلكترونيات</span>
          </button>
          <button class="category-card" type="button" data-category="sports">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg></span>
            <span>دراجات ورياضة</span>
          </button>
          <button class="category-card" type="button" data-category="tools">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M12 12h.01"></path><path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"></path><path d="M22 13a18.15 18.15 0 0 1-20 0"></path><rect width="20" height="14" x="2" y="6" rx="2"></rect></svg></span>
            <span>عدد وأدوات</span>
          </button>
          <button class="category-card" type="button" data-category="books">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M12 7v14"></path><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path></svg></span>
            <span>كتب وهوايات</span>
          </button>
          <button class="category-card" type="button" data-category="kids">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M12 8V4H8"></path><rect width="16" height="12" x="4" y="8" rx="2"></rect><path d="M2 14h2"></path><path d="M20 14h2"></path><path d="M15 13v2"></path><path d="M9 13v2"></path></svg></span>
            <span>ألعاب وأطفال</span>
          </button>
          <button class="category-card" type="button" data-category="camping">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M3.5 21 14 3"></path><path d="M20.5 21 10 3"></path><path d="M15.5 21 12 15l-3.5 6"></path><path d="M2 21h20"></path></svg></span>
            <span>تخييم ورحلات</span>
          </button>
          <button class="category-card" type="button" data-category="fashion">
            <span class="category-card__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path></svg></span>
            <span>أزياء واكسسوارات</span>
          </button>
        </div>
      </section>

      <!-- قريب منك -->
      <section class="section" id="nearby">
        <div class="section-head" data-aos="fade-up">
          <div>
            <h2 class="section-title">قريب منك <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"></path><circle cx="12" cy="12" r="10"></circle></svg></h2>
            <p class="section-subtitle">أغراض متوفرة على بعد خطوات منك</p>
          </div>
          <a href="#nearby" class="link js-show-all">عرض الكل <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg></a>
        </div>
        <div class="items-grid">
          <article class="item-card" data-deal="sale" data-category="furniture">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/desk.jpg') }}" alt="مكتب خشبي كلاسيكي بحالة ممتازة" loading="lazy">
              <span class="badge badge--sale">بيع</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="1" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>1.2 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 1) }}">مكتب خشبي كلاسيكي بحالة ممتازة</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">7,000 ل.س</span>
                <span class="item-card__area">حي الروضة</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="rent" data-category="electronics">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/camera.jpg') }}" alt="كاميرا احترافية مع عدسة 50mm" loading="lazy">
              <span class="badge badge--rent">تأجير</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="2" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>800 متر</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 2) }}">كاميرا احترافية مع عدسة 50mm</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">600 ل.س<small>/ يوم</small></span>
                <span class="item-card__area">حي المالكي</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="sale" data-category="electronics">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/coffee.jpg') }}" alt="ماكينة قهوة ديلونجي ديديكا مع ملحقاتها" loading="lazy">
              <span class="badge badge--sale">بيع</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="3" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>3.1 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 3) }}">ماكينة قهوة ديلونجي ديديكا مع ملحقاتها</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">17,000 ل.س</span>
                <span class="item-card__area">حي الشعلان</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="exchange" data-category="sports">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/bike-mountain.jpg') }}" alt="دراجة جبلية مقاس 26 بحالة شبه جديدة" loading="lazy">
              <span class="badge badge--exchange">تبادل</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="4" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>2.5 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 4) }}">دراجة جبلية مقاس 26 بحالة شبه جديدة</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">متاح للتبادل</span>
                <span class="item-card__area">حي المزة</span>
              </div>
            </div>
          </article>

          <div class="empty-state" hidden>
            <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"></path><path d="m7.5 4.27 9 5.15"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><line x1="12" x2="12" y1="22" y2="12"></line><circle cx="18.5" cy="15.5" r="2.5"></circle><path d="M20.27 17.27 22 19"></path></svg>
            <p>لا توجد إعلانات مطابقة لبحثك حالياً. جرّب كلمة أخرى أو نوع عملية مختلف.</p>
          </div>
        </div>
      </section>

      <!-- أضيف حديثاً -->
      <section class="section" id="latest">
        <div class="section-head" data-aos="fade-up">
          <div>
            <h2 class="section-title">أُضيف حديثاً <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></h2>
            <p class="section-subtitle">أحدث الإعلانات في منطقتك وما حولها</p>
          </div>
          <a href="#latest" class="link js-show-all">عرض الكل <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg></a>
        </div>
        <div class="items-grid">
          <article class="item-card" data-deal="exchange" data-category="sports">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/helmet.jpg') }}" alt="خوذة دراجات هوائية" loading="lazy">
              <span class="badge badge--exchange">تبادل</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="5" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>1.8 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 5) }}">خوذة دراجات هوائية</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">متاح للتبادل</span>
                <span class="item-card__area">حي الميدان</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="rent" data-category="sports">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/scooter.jpg') }}" alt="سكوتر كهربائي Pro متاح للتأجير" loading="lazy">
              <span class="badge badge--rent">تأجير</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="6" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>950 متر</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 6) }}">سكوتر كهربائي Pro متاح للتأجير</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">350 ل.س<small>/ يوم</small></span>
                <span class="item-card__area">حي كفرسوسة</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="sale" data-category="sports">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/lock.jpg') }}" alt="قفل دراجة فولاذي فائق الأمان" loading="lazy">
              <span class="badge badge--sale">بيع</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="7" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>2.4 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 7) }}">قفل دراجة فولاذي فائق الأمان</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">1,500 ل.س</span>
                <span class="item-card__area">حي أبو رمانة</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="rent" data-category="tools">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/pump.jpg') }}" alt="منفاخ دراجات أرضي أصلي" loading="lazy">
              <span class="badge badge--rent">تأجير</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="8" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>3.1 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 8) }}">منفاخ دراجات أرضي أصلي</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">500 ل.س<small>/ يوم</small></span>
                <span class="item-card__area">حي دمر</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="rent" data-category="electronics">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/camera.jpg') }}" alt="كاميرا تصوير للمناسبات بالأجرة" loading="lazy">
              <span class="badge badge--rent">تأجير</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="9" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>1.2 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 9) }}">كاميرا تصوير للمناسبات بالأجرة</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">250 ل.س<small>/ يوم</small></span>
                <span class="item-card__area">حي القصور</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="sale" data-category="furniture">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/desk.jpg') }}" alt="طاولة دراسة خشبية مع درج" loading="lazy">
              <span class="badge badge--sale">بيع</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="10" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>2.7 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 10) }}">طاولة دراسة خشبية مع درج</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">3,000 ل.س</span>
                <span class="item-card__area">حي النفق</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="exchange" data-category="sports">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/bike-mountain.jpg') }}" alt="دراجة هوائية للتبادل بدراجة أصغر" loading="lazy">
              <span class="badge badge--exchange">تبادل</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="11" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>3.8 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 11) }}">دراجة هوائية للتبادل بدراجة أصغر</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">متاح للتبادل</span>
                <span class="item-card__area">حي حطين</span>
              </div>
            </div>
          </article>

          <article class="item-card" data-deal="sale" data-category="electronics">
            <div class="item-card__media">
              <img src="{{ asset('assets/images/items/coffee.jpg') }}" alt="ماكينة قهوة منزلية بحالة جيدة" loading="lazy">
              <span class="badge badge--sale">بيع</span>
            </div>
            <button class="fav-btn js-fav" type="button" data-id="12" aria-label="إضافة إلى المفضلة"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></button>
            <div class="item-card__body">
              <span class="item-card__distance"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>1.5 كم</span>
              <h3 class="item-card__title"><a href="{{ route('items.show', 12) }}">ماكينة قهوة منزلية بحالة جيدة</a></h3>
              <div class="item-card__footer">
                <span class="item-card__price">4,000 ل.س</span>
                <span class="item-card__area">حي الربيع</span>
              </div>
            </div>
          </article>

          <div class="empty-state" hidden>
            <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"></path><path d="m7.5 4.27 9 5.15"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><line x1="12" x2="12" y1="22" y2="12"></line><circle cx="18.5" cy="15.5" r="2.5"></circle><path d="M20.27 17.27 22 19"></path></svg>
            <p>لا توجد إعلانات مطابقة لبحثك حالياً. جرّب كلمة أخرى أو نوع عملية مختلف.</p>
          </div>
        </div>
      </section>

      <!-- كيف يعمل غرضك قريب -->
      <section class="how" id="how-it-works">
        <span class="how__eyebrow">خطوات بسيطة وواضحة</span>
        <h2 class="how__title">كيف يعمل غرضك قريب؟</h2>
        <p class="how__subtitle">تمتع بتجربة تداول وتأجير آمنة وسريعة مع جيرانك دون عناء الشحن أو المسافات الطويلة</p>

        <ol class="steps">
          <li class="step" data-aos="fade-up">
            <div class="step__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m8 11 2 2 4-4"></path><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg><span class="step__num">1</span></div>
            <h3>تصفح ما حولك</h3>
            <p>ابحث عن الأغراض المتاحة في حيك أو منطقتك وحدد نوع العملية (شراء، تبادل، أو استئجار).</p>
          </li>
          <li class="step" data-aos="fade-up" data-aos-delay="150">
            <div class="step__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg><span class="step__num">2</span></div>
            <h3>أرسل طلباً</h3>
            <p>أرسل طلبك للمالك، وبعد قبوله تظهر بيانات التواصل لتحديد موعد الاستلام.</p>
          </li>
          <li class="step" data-aos="fade-up" data-aos-delay="300">
            <div class="step__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"></path><path d="m9 12 2 2 4-4"></path></svg><span class="step__num">3</span></div>
            <h3>استلم وقيّم</h3>
            <p>عاين الغرض، أتمم العملية بأمان بالقرب منك، وشارك تقييمك للمجتمع المحلي.</p>
          </li>
        </ol>

        <div class="cta" data-aos="zoom-in">
          <div class="cta__text">
            <span class="cta__icon"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m11 17 2 2a1 1 0 1 0 3-3"></path><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"></path><path d="m21 3 1 11h-2"></path><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"></path><path d="M3 4h8"></path></svg></span>
            <div>
              <h3>لديك غرض لا تستخدمه؟</h3>
              <p>شاركه مع جيرانك الآن واكسب دخلاً إضافياً أو بادله بما يفيدك</p>
            </div>
          </div>
          <a href="{{ route('items.create') }}" class="btn btn--primary">ابدأ بإضافة غرضك</a>
        </div>
      </section>
    </div>
  </main>
@endsection

@push('libraries')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/home.js') }}"></script>
@endpush
