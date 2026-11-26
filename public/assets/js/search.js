// ==========
// صفحة نتائج البحث: زر "حددني"، والتبديل بين الشبكة والخريطة (Leaflet)
// ==========

const searchForm = document.getElementById('search-form');

// زر "حددني": بياخد موقع الجهاز وبيعيد البحث حوله
const locateButton = document.querySelector('.js-locate');

locateButton.addEventListener('click', function () {
  if (!navigator.geolocation) {
    showToast('المتصفح لا يدعم تحديد الموقع', true);
    return;
  }

  navigator.geolocation.getCurrentPosition(
    function (position) {
      // منقرب الموقع لحماية الخصوصية
      document.getElementById('search-lat').value = position.coords.latitude.toFixed(3);
      document.getElementById('search-lng').value = position.coords.longitude.toFixed(3);
      searchForm.submit();
    },
    function () {
      showToast('تعذر تحديد موقعك، تأكد من السماح للمتصفح بالوصول للموقع', true);
    }
  );
});

// ===== الخريطة =====
const mapBox = document.getElementById('results-map');
const grid = document.getElementById('results-grid');
const viewButtons = document.querySelectorAll('.js-view');
let resultsMap = null;

// بترسم الخريطة أول مرة بس
function buildMap() {
  if (resultsMap || typeof L === 'undefined') {
    return;
  }

  const center = [parseFloat(mapBox.dataset.lat), parseFloat(mapBox.dataset.lng)];
  const radiusKm = parseFloat(mapBox.dataset.radius);

  resultsMap = L.map(mapBox).setView(center, radiusKm <= 1 ? 15 : radiusKm <= 5 ? 13 : 11);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap',
  }).addTo(resultsMap);

  // دائرة نطاق البحث
  L.circle(center, {
    radius: radiusKm * 1000,
    color: '#1b7a6e',
    fillColor: '#1b7a6e',
    fillOpacity: 0.08,
  }).addTo(resultsMap);

  // كل غرض: دائرة صغيرة بمكانه التقريبي، وبالضغط عليها بتفتح صفحته
  document.querySelectorAll('.js-map-item').forEach(function (item) {
    if (item.dataset.lat === '' || item.dataset.lng === '') {
      return;
    }

    const marker = L.circleMarker([parseFloat(item.dataset.lat), parseFloat(item.dataset.lng)], {
      radius: 9,
      color: '#ffffff',
      weight: 2,
      fillColor: '#0f5f55',
      fillOpacity: 0.9,
    }).addTo(resultsMap);

    marker.bindTooltip(item.dataset.title);
    marker.on('click', function () {
      window.location.href = item.dataset.url;
    });
  });
}

viewButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    const showMap = button.dataset.view === 'map';

    viewButtons.forEach(function (b) {
      b.classList.toggle('is-active', b === button);
    });

    mapBox.hidden = !showMap;
    grid.hidden = showMap;

    if (showMap) {
      buildMap();

      if (resultsMap) {
        resultsMap.invalidateSize();
      } else {
        showToast('تعذر تحميل الخريطة، تأكد من اتصالك بالإنترنت', true);
      }
    }
  });
});
