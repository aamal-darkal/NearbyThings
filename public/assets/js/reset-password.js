// ==========
// صفحة كلمة مرور جديدة: التحقق من الحقول قبل الإرسال
// ==========

const resetForm = document.getElementById('reset-form');
const emailInput = document.getElementById('reset-email');
const passwordInput = document.getElementById('reset-password');
const confirmInput = document.getElementById('reset-confirm');

resetForm.addEventListener('submit', function (event) {
  let isValid = true;

  if (!emailInput.value.includes('@')) {
    showError(emailInput, 'أدخل بريداً إلكترونياً صحيحاً');
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

  // اذا في خطأ منوقف الإرسال، وإلا الفورم بينبعت للسيرفر
  if (!isValid) {
    event.preventDefault();
  }
});
