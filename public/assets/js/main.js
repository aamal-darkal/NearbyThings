// ==========
// كود مشترك لكل الصفحات: قائمة الموبايل، المفضلة، أزرار "قريباً"
// ==========

// زر القائمة بالموبايل
const menuButton = document.querySelector('.menu-toggle');
const mainNav = document.querySelector('.main-nav');

menuButton.addEventListener('click', function () {
  mainNav.classList.toggle('is-open');
});

// زر المفضلة (القلب)
// لاحقاً: fetch('/api/favorites', ...)
const favButtons = document.querySelectorAll('.js-fav');

favButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    button.classList.toggle('is-active');
  });
});

// أزرار لصفحات لسا ما انعملت
const soonButtons = document.querySelectorAll('.js-soon');

soonButtons.forEach(function (button) {
  button.addEventListener('click', function (event) {
    event.preventDefault();
    alert(button.dataset.message);
  });
});
