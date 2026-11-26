// ==========
// اختيار الموقع التقريبي على الخريطة (Leaflet + OpenStreetMap)
// بيستخدم بتعديل الملف الشخصي وإضافة غرض
// العنصر: <div class="js-location-picker" data-lat-input="..." data-lng-input="...">
// ==========

// مركز دمشق (اذا ما في موقع محفوظ)
const DEFAULT_CENTER = [33.5138, 36.2765];

const pickerBox = document.querySelector('.js-location-picker');

// الخريطة (صفحات تانية بتحتاجها لتحدث حجمها لما تصير ظاهرة)
let pickerMap = null;

// اذا المكتبة ما انحملت (بدون انترنت) ما منعمل شي
if (pickerBox && typeof L !== 'undefined') {
  const latInput = document.getElementById(pickerBox.dataset.latInput);
  const lngInput = document.getElementById(pickerBox.dataset.lngInput);

  const hasValue = latInput.value !== '' && lngInput.value !== '';
  const start = hasValue ? [parseFloat(latInput.value), parseFloat(lngInput.value)] : DEFAULT_CENTER;

  const map = L.map(pickerBox).setView(start, hasValue ? 14 : 12);
  pickerMap = map;

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap',
  }).addTo(map);

  // دائرة بنصف قطر 500 متر بدل نقطة دقيقة (حماية الخصوصية)
  const circle = L.circle(start, {
    radius: 500,
    color: '#1b7a6e',
    fillColor: '#1b7a6e',
    fillOpacity: 0.2,
  });

  if (hasValue) {
    circle.addTo(map);
  }

  // بتحفظ الموقع بالحقول المخفية وبتحرك الدائرة
  function setLocation(lat, lng) {
    latInput.value = lat.toFixed(5);
    lngInput.value = lng.toFixed(5);
    circle.setLatLng([lat, lng]).addTo(map);
  }

  map.on('click', function (event) {
    setLocation(event.latlng.lat, event.latlng.lng);
  });

  // زر "استخدم موقعي الحالي"
  const locateButton = document.querySelector('.js-locate-me');

  if (locateButton) {
    locateButton.addEventListener('click', function () {
      if (!navigator.geolocation) {
        showToast('المتصفح لا يدعم تحديد الموقع', true);
        return;
      }

      navigator.geolocation.getCurrentPosition(
        function (position) {
          setLocation(position.coords.latitude, position.coords.longitude);
          map.setView([position.coords.latitude, position.coords.longitude], 14);
        },
        function () {
          showToast('تعذر تحديد موقعك، اضغط على الخريطة بدلاً من ذلك', true);
        }
      );
    });
  }
}
