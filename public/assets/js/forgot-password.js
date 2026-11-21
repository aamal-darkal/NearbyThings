// ==========
// صفحة نسيت كلمة المرور: التحقق من البريد قبل الإرسال
// ==========

const forgotForm = document.getElementById('forgot-form');
const emailInput = document.getElementById('forgot-email');

forgotForm.addEventListener('submit', function (event) {
  // اذا البريد غلط منوقف الإرسال، وإلا الفورم بينبعت للسيرفر
  if (!emailInput.value.includes('@')) {
    showError(emailInput, 'أدخل بريداً إلكترونياً صحيحاً');
    event.preventDefault();
  }
});
