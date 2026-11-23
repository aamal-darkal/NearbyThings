// ==========
// صفحة إنشاء حساب: التحقق من الحقول قبل الإرسال
// ==========

const registerForm = document.getElementById('register-form');
const nameInput = document.getElementById('reg-name');
const emailInput = document.getElementById('reg-email');
const phoneInput = document.getElementById('reg-phone');
const passwordInput = document.getElementById('reg-password');
const confirmInput = document.getElementById('reg-confirm');
const areaInput = document.getElementById('reg-area');

registerForm.addEventListener('submit', function (event) {
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

  if (passwordInput.value.length < 8) {
    showError(passwordInput, 'كلمة المرور 8 أحرف على الأقل');
    isValid = false;
  }

  if (confirmInput.value !== passwordInput.value) {
    showError(confirmInput, 'كلمتا المرور غير متطابقتين');
    isValid = false;
  }

  if (areaInput.value.trim() === '') {
    showError(areaInput, 'أدخل منطقتك');
    isValid = false;
  }

  // اذا في خطأ منوقف الإرسال، وإلا الفورم بينبعت للسيرفر
  if (!isValid) {
    event.preventDefault();
  }
});
