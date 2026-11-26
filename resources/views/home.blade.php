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

        {{-- البحث بيفتح صفحة نتائج البحث --}}
        <form class="search-box" method="GET" action="{{ route('search') }}" role="search" data-aos="fade-up" data-aos-delay="400">
          <div class="search-field search-field--keyword">
            <x-icon name="search" />
            <label for="search-keyword" class="sr-only">عن ماذا تبحث؟</label>
            <input type="search" id="search-keyword" name="q" placeholder="عن ماذا تبحث؟ مثال: دراجة، خيمة" autocomplete="off">
          </div>

          <div class="search-field search-field--area">
            <x-icon name="map-pin" />
            <div class="search-field__stack">
              <label for="search-area">ابحث بالقرب من</label>
              <input type="text" id="search-area" value="{{ $center['label'] }}" readonly>
            </div>
          </div>

          <button type="submit" class="btn btn--primary"><x-icon name="search" />بحث</button>

          {{-- نوع العملية (اختياري) --}}
          <div class="deal-filter">
            <span>نوع العملية (اختياري):</span>
            <label class="chip chip--check"><input type="checkbox" name="types[]" value="sale">بيع</label>
            <label class="chip chip--check"><input type="checkbox" name="types[]" value="exchange">تبادل</label>
            <label class="chip chip--check"><input type="checkbox" name="types[]" value="rent">تأجير</label>
          </div>
        </form>

        <ul class="hero__features">
          <li><x-icon name="users" />تعامل مباشر مع جيرانك</li>
          <li><x-icon name="lock" />موقعك تقريبي ليحمي خصوصيتك</li>
          <li><x-icon name="star" />تقييمات من المجتمع المحلي</li>
        </ul>
      </div>
    </section>

    <div class="container">
      <!-- التصنيفات -->
      <section class="section section--first" id="categories-section">
        <div class="section-head" data-aos="fade-up">
          <h2 class="section-title section-title--sm">استكشف حسب التصنيف</h2>
          <a href="{{ route('search') }}" class="link">عرض الكل <x-icon name="chevron-left" /></a>
        </div>
        <div class="categories">
          @foreach ($categories as $category)
            <a class="category-card" href="{{ route('search', ['category' => $category->id]) }}">
              <span class="category-card__icon category-card__icon--emoji">{{ $category->iconLabel() }}</span>
              <span>{{ $category->category_name }}</span>
            </a>
          @endforeach
        </div>
      </section>

      <!-- قريب منك -->
      <section class="section" id="nearby">
        <div class="section-head" data-aos="fade-up">
          <div>
            <h2 class="section-title">قريب منك <x-icon name="map-pin" /></h2>
            <p class="section-subtitle">أغراض متوفرة على بعد خطوات منك ({{ $center['label'] }})</p>
          </div>
          <a href="{{ route('search', ['sort' => 'nearest']) }}" class="link">عرض الكل <x-icon name="chevron-left" /></a>
        </div>
        <div class="items-grid">
          @forelse ($nearby as $item)
            @include('partials.item-card', ['item' => $item])
          @empty
            <div class="empty-state">
              <x-icon name="search" />
              <p>لا توجد إعلانات قريبة منك حالياً. كن أول من يعرض غرضاً في حيك!</p>
            </div>
          @endforelse
        </div>
      </section>

      <!-- أضيف حديثاً -->
      <section class="section" id="latest">
        <div class="section-head" data-aos="fade-up">
          <div>
            <h2 class="section-title">أُضيف حديثاً <x-icon name="clock" /></h2>
            <p class="section-subtitle">أحدث الإعلانات في منطقتك وما حولها</p>
          </div>
          <a href="{{ route('search', ['sort' => 'newest', 'radius' => 20]) }}" class="link">عرض الكل <x-icon name="chevron-left" /></a>
        </div>
        <div class="items-grid">
          @forelse ($latest as $item)
            @include('partials.item-card', ['item' => $item])
          @empty
            <div class="empty-state">
              <x-icon name="package" />
              <p>لا توجد إعلانات بعد.</p>
            </div>
          @endforelse
        </div>
      </section>

      <!-- كيف يعمل غرضك قريب -->
      <section class="how" id="how-it-works">
        <span class="how__eyebrow">خطوات بسيطة وواضحة</span>
        <h2 class="how__title">كيف يعمل غرضك قريب؟</h2>
        <p class="how__subtitle">تمتع بتجربة تداول وتأجير آمنة وسريعة مع جيرانك دون عناء الشحن أو المسافات الطويلة</p>

        <ol class="steps">
          <li class="step" data-aos="fade-up">
            <div class="step__icon"><x-icon name="search" /><span class="step__num">1</span></div>
            <h3>تصفح ما حولك</h3>
            <p>ابحث عن الأغراض المتاحة في حيك أو منطقتك وحدد نوع العملية (شراء، تبادل، أو استئجار).</p>
          </li>
          <li class="step" data-aos="fade-up" data-aos-delay="150">
            <div class="step__icon"><x-icon name="send" /><span class="step__num">2</span></div>
            <h3>أرسل طلباً</h3>
            <p>أرسل طلبك للمالك، وبعد قبوله تظهر بيانات التواصل لتحديد موعد الاستلام.</p>
          </li>
          <li class="step" data-aos="fade-up" data-aos-delay="300">
            <div class="step__icon"><x-icon name="check-circle" /><span class="step__num">3</span></div>
            <h3>استلم وقيّم</h3>
            <p>عاين الغرض، أتمم العملية بأمان بالقرب منك، وشارك تقييمك للمجتمع المحلي.</p>
          </li>
        </ol>

        <div class="cta" data-aos="zoom-in">
          <div class="cta__text">
            <span class="cta__icon"><x-icon name="package" /></span>
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
