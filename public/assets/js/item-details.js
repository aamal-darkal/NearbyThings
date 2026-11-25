// ==========
// صفحة تفاصيل الغرض: معرض الصور، الخريطة التقريبية، ونافذة إرسال الطلب
// ==========

// ===== معرض الصور: لما تكبس على صورة صغيرة بتصير هي الكبيرة =====
const mainImage = document.getElementById('gallery-main');
const galleryCounter = document.getElementById('gallery-counter');
const thumbs = document.querySelectorAll('.thumb');

thumbs.forEach(function (thumb) {
  thumb.addEventListener('click', function () {
    mainImage.src = thumb.dataset.src;
    galleryCounter.textContent = thumb.dataset.index + ' من ' + thumbs.length + ' صور';

    thumbs.forEach(function (t) {
      t.classList.remove('is-active');
    });
    thumb.classList.add('is-active');
  });
});

// عرض الصورة بملء الشاشة
const lightbox = document.getElementById('lightbox');
const expandButton = document.getElementById('gallery-expand');

if (lightbox && expandButton) {
  expandButton.addEventListener('click', function () {
    document.getElementById('lightbox-image').src = mainImage.src;
    lightbox.classList.add('is-open');
  });

  lightbox.addEventListener('click', function () {
    lightbox.classList.remove('is-open');
  });
}

// ===== الخريطة: دائرة حول الموقع التقريبي (بدون نقطة دقيقة) =====
const itemMap = document.getElementById('item-map');

if (itemMap && typeof L !== 'undefined') {
  const point = [parseFloat(itemMap.dataset.lat), parseFloat(itemMap.dataset.lng)];
  const map = L.map(itemMap, { scrollWheelZoom: false }).setView(point, 14);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 16,
    attribution: '&copy; OpenStreetMap',
  }).addTo(map);

  L.circle(point, {
    radius: 600,
    color: '#1b7a6e',
    fillColor: '#1b7a6e',
    fillOpacity: 0.25,
  }).addTo(map);
}

// ===== نافذة إرسال الطلب =====
const modal = document.getElementById('request-modal');
const requestButton = document.getElementById('request-btn');

if (modal && requestButton) {
  requestButton.addEventListener('click', function () {
    modal.classList.add('is-open');
  });

  modal.querySelectorAll('.js-close-modal').forEach(function (button) {
    button.addEventListener('click', function () {
      modal.classList.remove('is-open');
    });
  });

  // اذا السيرفر رجع أخطاء بالنموذج منفتح النافذة من جديد
  if (modal.dataset.open === 'true') {
    modal.classList.add('is-open');
  }
}

// ===== التأجير: حساب المدة والتكلفة التقديرية =====
const rentDates = document.querySelector('.rent-dates');

if (rentDates) {
  const startInput = document.getElementById('request-start');
  const endInput = document.getElementById('request-end');
  const totalLabel = document.getElementById('rent-total');
  const price = parseFloat(rentDates.dataset.price);
  // عدد الأيام بكل وحدة تأجير
  const unitDays = { day: 1, month: 30, year: 365 };
  const unitNames = { day: 'يوم', month: 'شهر', year: 'سنة' };

  function updateRentTotal() {
    const start = new Date(startInput.value);
    const end = new Date(endInput.value);
    const days = Math.round((end - start) / 86400000) + 1;

    if (!startInput.value || !endInput.value || days < 1) {
      totalLabel.textContent = 'اختر تاريخ بداية وتاريخ إرجاع صحيحين';
      return;
    }

    const units = Math.ceil(days / unitDays[rentDates.dataset.unit]);
    const total = units * price;
    totalLabel.textContent = 'المدة: ' + days + ' يوم • التكلفة التقديرية: ' + total.toLocaleString('en-US') + ' ل.س (' + units + ' ' + unitNames[rentDates.dataset.unit] + ')';
  }

  startInput.addEventListener('change', function () {
    endInput.min = startInput.value;
    updateRentTotal();
  });
  endInput.addEventListener('change', updateRentTotal);
  updateRentTotal();
}
