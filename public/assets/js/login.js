// ==========
// صفحة تسجيل الدخول: التحقق من الحقول قبل الإرسال
// ==========

const loginForm = document.getElementById('login-form');
const emailInput = document.getElementById('login-email');
const passwordInput = document.getElementById('login-password');

loginForm.addEventListener('submit', function (event) {
  let isValid = true;

  if (!emailInput.value.includes('@')) {
    showError(emailInput, 'أدخل بريداً إلكترونياً صحيحاً');
    isValid = false;
  }

  if (passwordInput.value.length < 8) {
    showError(passwordInput, 'كلمة المرور 8 أحرف على الأقل');
    isValid = false;
  }

  // اذا في خطأ منوقف الإرسال، وإلا الفورم بينبعت للسيرفر
  if (!isValid) {
    event.preventDefault();
  }
});
