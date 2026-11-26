// ==========
// لوحة المشرف: القائمة الجانبية بالموبايل، والنوافذ المنبثقة (إضافة / تعديل)
// ==========

// القائمة الجانبية بالموبايل
const sidebar = document.getElementById('admin-sidebar');
const sidebarToggle = document.querySelector('.admin-topbar .menu-toggle');

sidebarToggle.addEventListener('click', function () {
  const isOpen = sidebar.classList.toggle('is-open');
  sidebarToggle.setAttribute('aria-expanded', isOpen);
});

// ===== النوافذ المنبثقة =====
// زر الفتح: <button class="js-open-modal" data-modal="id" data-field-xxx="قيمة" data-action="رابط">
// بيعبي حقول النموذج من data-field-* (اسم الحقل بعد field-)

function openModal(button) {
  const modal = document.getElementById(button.dataset.modal);
  const form = modal.querySelector('form');

  // رابط الإرسال (للتعديل بيختلف عن الإضافة)
  if (button.dataset.action) {
    form.action = button.dataset.action;

    // منحفظ الرابط بحقل مخفي حتى لو رجع السيرفر بأخطاء تفتح النافذة على نفس الرابط
    const actionInput = form.querySelector('input[name="_action"]');
    if (actionInput) {
      actionInput.value = button.dataset.action;
    }
  }

  // طريقة الإرسال (PUT للتعديل، POST للإضافة)
  const methodInput = form.querySelector('input[name="_method"]');
  if (methodInput) {
    methodInput.value = button.dataset.method || 'POST';
  }

  // عنوان النافذة
  const title = modal.querySelector('.js-modal-title');
  if (title && button.dataset.title) {
    title.textContent = button.dataset.title;
  }

  // تعبئة الحقول
  Object.keys(button.dataset).forEach(function (key) {
    if (!key.startsWith('field')) {
      return;
    }

    const name = key.replace('field', '').replace(/^./, function (letter) {
      return letter.toLowerCase();
    }).replace(/[A-Z]/g, function (letter) {
      return '_' + letter.toLowerCase();
    });
    const value = button.dataset[key];

    form.querySelectorAll('[name="' + name + '"]').forEach(function (field) {
      if (field.type === 'radio') {
        field.checked = field.value === value;
      } else {
        field.value = value;
      }
    });
  });

  // نصوص للعرض داخل النافذة: data-text-xxx بيعبي العنصر .js-text-xxx
  Object.keys(button.dataset).forEach(function (key) {
    if (!key.startsWith('text')) {
      return;
    }

    const target = modal.querySelector('.js-' + key.replace(/[A-Z]/g, function (letter) {
      return '-' + letter.toLowerCase();
    }));
    if (target) {
      target.textContent = button.dataset[key];
    }
  });

  modal.classList.add('is-open');
}

document.querySelectorAll('.js-open-modal').forEach(function (button) {
  button.addEventListener('click', function () {
    openModal(button);
  });
});

document.querySelectorAll('.js-close-modal').forEach(function (button) {
  button.addEventListener('click', function () {
    button.closest('.modal').classList.remove('is-open');
  });
});

// اذا السيرفر رجع أخطاء بنموذج، منفتح نافذته من جديد
const modalWithErrors = document.querySelector('.modal[data-open="true"]');
if (modalWithErrors) {
  modalWithErrors.classList.add('is-open');
}
