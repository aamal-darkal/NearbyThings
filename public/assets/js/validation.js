// ==========
// دوال مشتركة للنماذج: رسائل الخطأ وزر إظهار كلمة المرور
// ==========

// بتظهر رسالة الخطأ تحت الحقل
function showError(input, message) {
  const group = input.closest('.form-group');
  group.classList.add('has-error');
  group.querySelector('.form-error').textContent = message;
}

// لما المستخدم يكتب بالحقل منشيل الخطأ
const inputs = document.querySelectorAll('.form-group input');

inputs.forEach(function (input) {
  input.addEventListener('input', function () {
    const group = input.closest('.form-group');
    group.classList.remove('has-error');
    group.querySelector('.form-error').textContent = '';
  });
});

// زر العين: إظهار وإخفاء كلمة المرور
const eyeButtons = document.querySelectorAll('.toggle-password');

eyeButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    const input = button.parentElement.querySelector('input');

    if (input.type === 'password') {
      input.type = 'text';
    } else {
      input.type = 'password';
    }
  });
});
