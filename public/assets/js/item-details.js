// ==========
// صفحة تفاصيل الغرض: معرض الصور ونافذة إرسال الطلب
// ==========

// معرض الصور: لما تكبس على صورة صغيرة بتصير هي الكبيرة
const mainImage = document.getElementById('gallery-main');
const thumbs = document.querySelectorAll('.thumb');

thumbs.forEach(function (thumb) {
  thumb.addEventListener('click', function () {
    mainImage.src = thumb.dataset.src;

    thumbs.forEach(function (t) {
      t.classList.remove('is-active');
    });
    thumb.classList.add('is-active');
  });
});

// عرض الصورة بملء الشاشة
const lightbox = document.getElementById('lightbox');
const lightboxImage = document.getElementById('lightbox-image');

document.getElementById('gallery-expand').addEventListener('click', function () {
  lightboxImage.src = mainImage.src;
  lightbox.classList.add('is-open');
});

lightbox.addEventListener('click', function () {
  lightbox.classList.remove('is-open');
});

// نافذة إرسال الطلب
const modal = document.getElementById('request-modal');
const requestButton = document.getElementById('request-btn');
const requestForm = document.getElementById('request-form');
const closeButtons = document.querySelectorAll('.js-close-modal');

requestButton.addEventListener('click', function () {
  modal.classList.add('is-open');
});

closeButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    modal.classList.remove('is-open');
  });
});

requestForm.addEventListener('submit', function (event) {
  event.preventDefault();
  // لاحقاً: fetch('/api/requests', { method: 'POST', ... })
  modal.classList.remove('is-open');
  requestButton.textContent = 'تم إرسال طلبك';
  requestButton.disabled = true;
});
