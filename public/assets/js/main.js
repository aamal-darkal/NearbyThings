// ==========
// كود مشترك لكل الصفحات: قائمة الموبايل، الرسائل القصيرة (Toast)، أزرار "قريباً"، تأكيد الحذف
// ==========

// زر القائمة بالموبايل
const menuButton = document.querySelector('.menu-toggle');
const mainNav = document.querySelector('.main-nav');

if (menuButton && mainNav) {
  menuButton.addEventListener('click', function () {
    const isOpen = mainNav.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', isOpen);
  });
}

// رسالة قصيرة أسفل الشاشة بتختفي بعد 3 ثواني
const toast = document.getElementById('toast');
let toastTimer;

function showToast(message, isError) {
  if (!toast) {
    return;
  }

  toast.textContent = message;
  toast.classList.toggle('toast--error', isError === true);
  toast.hidden = false;

  clearTimeout(toastTimer);
  toastTimer = setTimeout(function () {
    toast.hidden = true;
  }, 3000);
}

// اذا الخادم بعت رسالة بعد عملية (حفظ، حذف...) منعرضها
if (toast && toast.dataset.message) {
  showToast(toast.dataset.message, toast.classList.contains('toast--error'));
}

// أزرار لصفحات لسا ما انعملت
const soonButtons = document.querySelectorAll('.js-soon');

soonButtons.forEach(function (button) {
  button.addEventListener('click', function (event) {
    event.preventDefault();
    showToast(button.dataset.message);
  });
});

// نماذج بتطلب تأكيد قبل الإرسال (مثل الحذف): <form class="js-confirm" data-confirm="...">
const confirmForms = document.querySelectorAll('.js-confirm');

confirmForms.forEach(function (form) {
  form.addEventListener('submit', function (event) {
    if (!window.confirm(form.dataset.confirm)) {
      event.preventDefault();
    }
  });
});

// زر المفضلة (القلب)
// لاحقاً: fetch('/favorites/{id}', { method: 'POST' })
const favButtons = document.querySelectorAll('.js-fav');

favButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    button.classList.toggle('is-active');
  });
});
