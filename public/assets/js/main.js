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

// قوائم بترسل النموذج مباشرة لما تتغير (مثل الترتيب والفلاتر)
document.querySelectorAll('.js-auto-submit').forEach(function (field) {
  field.addEventListener('change', function () {
    field.form.submit();
  });
});

// زر المفضلة (القلب): بيرسل الطلب للسيرفر وبيلون القلب حسب الرد
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const favButtons = document.querySelectorAll('.js-fav');

favButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    // بطاقات تجريبية بدون رابط: بس منلون القلب
    if (!button.dataset.url) {
      button.classList.toggle('is-active');
      return;
    }

    fetch(button.dataset.url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
    })
      .then(function (response) {
        // الزائر لازم يسجل دخول أولاً
        if (response.status === 401) {
          window.location.href = '/login';
          return null;
        }
        return response.json().then(function (data) {
          return { ok: response.ok, data: data };
        });
      })
      .then(function (result) {
        if (!result) {
          return;
        }

        showToast(result.data.message, !result.ok);

        if (result.ok) {
          button.classList.toggle('is-active', result.data.favorited);
          button.setAttribute('aria-pressed', result.data.favorited);

          // بصفحة المفضلة: منخفي البطاقة لما تنشال من المفضلة
          const card = button.closest('.js-favorite-card');
          if (card && !result.data.favorited) {
            card.hidden = true;
          }
        }
      })
      .catch(function () {
        showToast('حدث خطأ في الاتصال، حاول مجدداً', true);
      });
  });
});
