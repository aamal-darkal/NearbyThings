// ==========
// صفحة تعديل الملف الشخصي: التحقق من الحقول قبل الإرسال
// ==========

const editForm = document.getElementById('edit-form');
const nameInput = document.getElementById('edit-name');
const emailInput = document.getElementById('edit-email');
const phoneInput = document.getElementById('edit-phone');
const areaInput = document.getElementById('edit-area');
const currentInput = document.getElementById('edit-current');
const passwordInput = document.getElementById('edit-password');
const confirmInput = document.getElementById('edit-confirm');

editForm.addEventListener('submit', function (event) {
  let isValid = true;

  if (nameInput.value.trim() === '') {
    showError(nameInput, 'أدخل اسمك الكامل');
    isValid = false;
  }

  if (!emailInput.value.includes('@')) {
    showError(emailInput, 'أدخل بريداً إلكترونياً صحيحاً');
    isValid = false;
  }

  // رقم سوري: بيبلش بـ 09 و 10 أرقام
  if (!phoneInput.value.startsWith('09') || phoneInput.value.length !== 10) {
    showError(phoneInput, 'رقم الهاتف يجب أن يبدأ بـ 09 ويتكون من 10 أرقام');
    isValid = false;
  }

  if (areaInput.value.trim() === '') {
    showError(areaInput, 'أدخل منطقتك');
    isValid = false;
  }

  // كلمة المرور اختيارية: منتحقق منها بس اذا المستخدم كتب كلمة جديدة
  if (passwordInput.value !== '') {
    if (currentInput.value === '') {
      showError(currentInput, 'أدخل كلمة المرور الحالية');
      isValid = false;
    }

    if (passwordInput.value.length < 8) {
      showError(passwordInput, 'كلمة المرور 8 أحرف على الأقل');
      isValid = false;
    }

    if (confirmInput.value !== passwordInput.value) {
      showError(confirmInput, 'كلمتا المرور غير متطابقتين');
      isValid = false;
    }
  }

  // اذا في خطأ منوقف الإرسال، وإلا الفورم بينبعت للسيرفر
  if (!isValid) {
    event.preventDefault();
  }
});
