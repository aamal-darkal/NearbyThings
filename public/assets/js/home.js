// ==========
// الصفحة الرئيسية: البحث وفلترة البطاقات
// ==========

// تشغيل حركات AOS (مرة وحدة بس لكل عنصر)
// الشرط لحتى ما يوقف الكود اذا المكتبة ما انحملت (بدون انترنت)
if (typeof AOS !== 'undefined') {
  AOS.init({ once: true, duration: 700 });
}

const searchForm = document.getElementById('search-form');
const keywordInput = document.getElementById('search-keyword');
const chips = document.querySelectorAll('.chip');
const categoryButtons = document.querySelectorAll('.category-card');
const cards = document.querySelectorAll('.item-card');

let deal = '';
let category = '';

// لاحقاً: fetch('/api/items/nearby?lat=..&lng=..', ...)
// بتخفي البطاقات اللي ما بتطابق البحث
function filterCards() {
  const keyword = keywordInput.value.trim();

  cards.forEach(function (card) {
    const title = card.querySelector('.item-card__title').textContent;
    let show = true;

    if (keyword !== '' && !title.includes(keyword)) {
      show = false;
    }
    if (deal !== '' && card.dataset.deal !== deal) {
      show = false;
    }
    if (category !== '' && card.dataset.category !== category) {
      show = false;
    }

    card.hidden = !show;
  });
}

// البحث
searchForm.addEventListener('submit', function (event) {
  event.preventDefault();
  filterCards();
});

// بيع / تبادل / تأجير
chips.forEach(function (chip) {
  chip.addEventListener('click', function () {
    chips.forEach(function (c) {
      c.classList.remove('is-active');
    });

    // اذا كبسنا عليه مرة تانية بنلغيه
    if (deal === chip.dataset.deal) {
      deal = '';
    } else {
      deal = chip.dataset.deal;
      chip.classList.add('is-active');
    }

    filterCards();
  });
});

// التصنيفات
categoryButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    categoryButtons.forEach(function (b) {
      b.classList.remove('is-active');
    });

    if (category === button.dataset.category) {
      category = '';
    } else {
      category = button.dataset.category;
      button.classList.add('is-active');
    }

    filterCards();
    document.getElementById('nearby').scrollIntoView();
  });
});
