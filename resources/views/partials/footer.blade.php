{{-- الفوتر --}}
  <footer class="site-footer">
    <div class="container">
      <div class="site-footer__top">
        <div class="site-footer__about">
          <a href="{{ route('home') }}" class="brand">
            <img src="{{ asset('assets/images/logo.png') }}" alt="">
            <span>غرضك قريب</span>
          </a>
          <p>منصتك المحلية للبيع، التبادل والتأجير بكل سهولة وأمان في حيك ومحيطك</p>
        </div>
        <nav class="footer-links" aria-label="روابط الموقع">
          <a href="{{ route('home') }}">الرئيسية</a>
          <a href="{{ route('home') }}#how-it-works">كيف يعمل الموقع</a>
          <a href="#terms">الشروط والأحكام</a>
          <a href="#privacy">الخصوصية</a>
          <a href="#contact">تواصل معنا</a>
        </nav>
      </div>
      <div class="site-footer__bottom">
        <span>جميع الحقوق محفوظة © 2026 غرضك قريب</span>
        <span>بيع وتبادل وتأجير بين الجيران</span>
      </div>
    </div>
  </footer>
