// ==========
// صفحة الملف الشخصي: التبويبات
// ==========

// التبويبات: إعلاناتي / التقييمات
const tabs = document.querySelectorAll('.tab');

tabs.forEach(function (tab) {
  tab.addEventListener('click', function () {
    // منخفي كل الأقسام
    tabs.forEach(function (t) {
      t.classList.remove('is-active');
      document.getElementById(t.dataset.panel).hidden = true;
    });

    // ومنظهر القسم تبع التبويب اللي انكبس
    tab.classList.add('is-active');
    document.getElementById(tab.dataset.panel).hidden = false;
  });
});
