// ==========
// النوافذ المنبثقة لصفحات الطلبات (قبول، رفض، موعد جديد، اعتراض...)
// زر الفتح: <button class="js-open-modal" data-modal="id" data-action="رابط" data-text-xxx="نص">
// data-text-xxx بيعبي العنصر .js-text-xxx داخل النافذة
// ==========

function openRequestModal(button) {
  const modal = document.getElementById(button.dataset.modal);
  const form = modal.querySelector('form');

  // رابط الإرسال (كل طلب إله رابط مختلف)
  if (form && button.dataset.action) {
    form.action = button.dataset.action;

    // منحفظه كمان بحقل مخفي، حتى لو رجعت أخطاء تنفتح النافذة على نفس الطلب
    const actionInput = form.querySelector('input[name="_action"]');
    if (actionInput) {
      actionInput.value = button.dataset.action;
    }
  }

  // النصوص اللي بتتغير حسب الطلب (اسم الباحث، اسم الغرض...)
  Object.keys(button.dataset).forEach(function (key) {
    if (!key.startsWith('text')) {
      return;
    }

    const className = '.js-' + key.replace(/[A-Z]/g, function (letter) {
      return '-' + letter.toLowerCase();
    });

    modal.querySelectorAll(className).forEach(function (element) {
      element.textContent = button.dataset[key];
    });
  });

  modal.classList.add('is-open');
}

document.querySelectorAll('.js-open-modal').forEach(function (button) {
  button.addEventListener('click', function () {
    openRequestModal(button);
  });
});

document.querySelectorAll('.js-close-modal').forEach(function (button) {
  button.addEventListener('click', function () {
    button.closest('.modal').classList.remove('is-open');
  });
});

// اذا السيرفر رجع أخطاء بنموذج، منفتح نافذته من جديد
document.querySelectorAll('.modal[data-open="true"]').forEach(function (modal) {
  modal.classList.add('is-open');
});
