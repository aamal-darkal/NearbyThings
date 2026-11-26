// ==========
// نموذج إضافة / تعديل غرض: التنقل بين الخطوات، معاينة الصور، معاينة البطاقة،
// إظهار حقول السعر حسب نوع العرض، واقتراح تصنيف جديد
// ==========

const itemForm = document.getElementById('item-form');
const steps = document.querySelectorAll('.form-step');
const stepperItems = document.querySelectorAll('.stepper__step');
let currentStep = parseInt(itemForm.dataset.startStep, 10);

// ===== التنقل بين الخطوات =====

function showStep(number) {
  currentStep = number;

  steps.forEach(function (step) {
    step.hidden = parseInt(step.dataset.step, 10) !== number;
  });

  stepperItems.forEach(function (item) {
    const stepNumber = parseInt(item.dataset.step, 10);
    item.classList.toggle('is-active', stepNumber === number);
    item.classList.toggle('is-done', stepNumber < number);
  });

  // الخريطة بتحتاج تحديث حجمها لما تصير ظاهرة
  if (number === 3 && typeof pickerMap !== 'undefined' && pickerMap) {
    setTimeout(function () {
      pickerMap.invalidateSize();
    }, 50);
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// رسالة خطأ تحت الحقل (نفس شكل أخطاء السيرفر)
function setFieldError(field, message) {
  const group = field.closest('.form-group');
  group.classList.add('has-error');
  group.querySelector('.form-error').textContent = message;
}

function clearFieldErrors(step) {
  step.querySelectorAll('.form-group.has-error').forEach(function (group) {
    group.classList.remove('has-error');
  });
}

// تحقق بسيط قبل الانتقال للخطوة التالية (السيرفر بيتحقق كمان)
function validateStep(number) {
  const step = document.querySelector('.form-step[data-step="' + number + '"]');
  clearFieldErrors(step);
  let isValid = true;

  if (number === 1) {
    const photosCount = countPhotos();
    if (photosCount === 0) {
      setFieldError(document.getElementById('dropzone'), 'أضف صورة واحدة على الأقل');
      isValid = false;
    }

    const title = document.getElementById('item-title');
    if (title.value.trim().length < 3) {
      setFieldError(title, 'أدخل عنواناً واضحاً للإعلان (3 أحرف على الأقل)');
      isValid = false;
    }

    const category = document.getElementById('item-category');
    const suggest = document.getElementById('item-suggest');
    if (category.disabled ? suggest.value.trim().length < 2 : category.value === '') {
      setFieldError(category, 'اختر التصنيف أو اقترح تصنيفاً جديداً');
      isValid = false;
    }

    if (!itemForm.querySelector('input[name="condition_id"]:checked')) {
      setFieldError(itemForm.querySelector('input[name="condition_id"]'), 'اختر حالة الغرض');
      isValid = false;
    }

    const description = document.getElementById('item-description');
    if (description.value.trim().length < 10) {
      setFieldError(description, 'اكتب وصفاً للغرض (10 أحرف على الأقل)');
      isValid = false;
    }
  }

  if (number === 2) {
    const type = selectedType();
    const price = document.getElementById('item-price');
    if (type !== 'exchange' && !(parseFloat(price.value) > 0)) {
      setFieldError(price, 'أدخل سعراً صحيحاً');
      isValid = false;
    }

    const maxRent = document.getElementById('item-max-rent');
    if (type === 'rent' && !(parseInt(maxRent.value, 10) > 0)) {
      setFieldError(maxRent, 'أدخل أقصى مدة للتأجير');
      isValid = false;
    }
  }

  return isValid;
}

document.querySelectorAll('.js-next').forEach(function (button) {
  button.addEventListener('click', function () {
    if (validateStep(currentStep)) {
      showStep(currentStep + 1);
    }
  });
});

document.querySelectorAll('.js-prev').forEach(function (button) {
  button.addEventListener('click', function () {
    showStep(currentStep - 1);
  });
});

// قبل الإرسال منتحقق من كل الخطوات
itemForm.addEventListener('submit', function (event) {
  for (let number = 1; number <= 2; number++) {
    if (!validateStep(number)) {
      event.preventDefault();
      showStep(number);
      return;
    }
  }
});

// ===== الصور =====

const photosCountLabel = document.getElementById('photos-count');
const previewImage = document.getElementById('preview-image');
const previewPlaceholder = document.getElementById('preview-placeholder');

// عدد الصور: المحفوظة (اللي ما انحذفت) + الجديدة
function countPhotos() {
  let count = 0;

  document.querySelectorAll('.photo-slot[data-existing]').forEach(function (slot) {
    if (!slot.querySelector('.js-remove-existing').checked) {
      count++;
    }
  });

  document.querySelectorAll('.js-photo-input').forEach(function (input) {
    if (input.files.length > 0) {
      count++;
    }
  });

  return count;
}

// بتحدث العداد والصورة الرئيسية بالمعاينة
function updatePhotos() {
  photosCountLabel.textContent = 'تم تحميل ' + countPhotos() + ' من 5';

  // أول صورة ظاهرة هي الرئيسية
  let firstSrc = '';
  document.querySelectorAll('.photo-slot img').forEach(function (img) {
    const slot = img.closest('.photo-slot');
    const removed = slot.querySelector('.js-remove-existing');
    if (firstSrc === '' && !img.hidden && img.getAttribute('src') && !(removed && removed.checked)) {
      firstSrc = img.src;
    }
  });

  previewImage.hidden = firstSrc === '';
  previewPlaceholder.hidden = firstSrc !== '';
  if (firstSrc !== '') {
    previewImage.src = firstSrc;
  }
}

// اختيار صورة جديدة بمربع: منعرض معاينة لها
document.querySelectorAll('.js-photo-input').forEach(function (input) {
  input.addEventListener('change', function () {
    const slot = input.closest('.photo-slot');
    const img = slot.querySelector('img');
    const empty = slot.querySelector('.photo-slot__empty');
    const clearButton = slot.querySelector('.js-clear-photo');

    if (input.files.length > 0) {
      img.src = URL.createObjectURL(input.files[0]);
      img.hidden = false;
      empty.hidden = true;
      clearButton.hidden = false;
      slot.classList.add('has-image');
    }

    updatePhotos();
  });
});

// زر إزالة صورة جديدة
document.querySelectorAll('.js-clear-photo').forEach(function (button) {
  button.addEventListener('click', function () {
    const slot = button.closest('.photo-slot');
    const input = slot.querySelector('.js-photo-input');

    input.value = '';
    slot.querySelector('img').hidden = true;
    slot.querySelector('img').removeAttribute('src');
    slot.querySelector('.photo-slot__empty').hidden = false;
    button.hidden = true;
    slot.classList.remove('has-image');

    updatePhotos();
  });
});

// حذف صورة محفوظة (عند التعديل)
document.querySelectorAll('.js-remove-existing').forEach(function (checkbox) {
  checkbox.addEventListener('change', function () {
    checkbox.closest('.photo-slot').classList.toggle('is-removed', checkbox.checked);
    updatePhotos();
  });
});

// الصندوق الكبير: بيفتح أول مربع فاضي
document.getElementById('dropzone').addEventListener('click', function () {
  const emptyInput = Array.from(document.querySelectorAll('.js-photo-input')).find(function (input) {
    return input.files.length === 0;
  });

  if (emptyInput) {
    emptyInput.click();
  } else {
    showToast('وصلت للحد الأقصى: 5 صور', true);
  }
});

// ===== اقتراح تصنيف جديد =====

const categorySelect = document.getElementById('item-category');
const suggestBox = document.getElementById('suggest-box');
const suggestInput = document.getElementById('item-suggest');
const suggestToggle = document.getElementById('suggest-toggle');

suggestToggle.addEventListener('click', function () {
  const isSuggesting = suggestBox.hidden;

  suggestBox.hidden = !isSuggesting;
  suggestInput.disabled = !isSuggesting;
  categorySelect.disabled = isSuggesting;
  suggestToggle.textContent = isSuggesting ? 'اختر من التصنيفات الموجودة' : 'اقترح تصنيفاً جديداً';
  updatePreview();
});

// ===== نوع العرض: إظهار الحقول المناسبة =====

const typeInputs = itemForm.querySelectorAll('input[name="transaction_type"]');
const priceLabel = document.querySelector('.js-price-label');

function selectedType() {
  const checked = itemForm.querySelector('input[name="transaction_type"]:checked');
  return checked ? checked.value : 'sale';
}

function updateTypeFields() {
  const type = selectedType();

  document.querySelectorAll('.js-for-type').forEach(function (block) {
    const visible = block.dataset.types.split(' ').includes(type);
    block.hidden = !visible;

    // الحقول المخفية ما بتنبعت
    block.querySelectorAll('input, select').forEach(function (field) {
      field.disabled = !visible;
    });
  });

  priceLabel.textContent = type === 'rent' ? 'سعر التأجير (ل.س)' : 'السعر المطلوب (ل.س)';
  updatePreview();
}

typeInputs.forEach(function (input) {
  input.addEventListener('change', updateTypeFields);
});

// ===== الموقع: عنوان الحساب أو موقع آخر =====

const customLocation = document.querySelector('.js-custom-location');

itemForm.querySelectorAll('input[name="location_mode"]').forEach(function (input) {
  input.addEventListener('change', function () {
    const isCustom = input.value === 'custom';
    customLocation.hidden = !isCustom;

    if (isCustom && typeof pickerMap !== 'undefined' && pickerMap) {
      setTimeout(function () {
        pickerMap.invalidateSize();
      }, 50);
    }
  });
});

// ===== معاينة البطاقة المباشرة =====

const typeNames = { sale: 'بيع', exchange: 'تبادل', rent: 'تأجير' };
const unitNames = { day: 'يوم', month: 'شهر', year: 'سنة' };

function updatePreview() {
  const type = selectedType();
  const title = document.getElementById('item-title').value.trim();
  const price = parseFloat(document.getElementById('item-price').value);
  const unit = document.getElementById('item-rent-unit').value;
  const area = document.getElementById('item-area').value.trim();

  document.getElementById('preview-title').textContent = title || 'عنوان الإعلان';
  document.getElementById('preview-area').textContent = area;

  const badge = document.getElementById('preview-badge');
  badge.textContent = typeNames[type];
  badge.className = 'badge badge--' + type;

  let priceText = '—';
  if (type === 'exchange') {
    priceText = 'متاح للتبادل';
  } else if (price > 0) {
    priceText = price.toLocaleString('en-US') + ' ل.س';
    if (type === 'rent') {
      priceText += ' / ' + unitNames[unit];
    }
  }
  document.getElementById('preview-price').textContent = priceText;

  let categoryText = 'التصنيف';
  if (categorySelect.disabled) {
    categoryText = suggestInput.value.trim() || 'تصنيف مقترح';
  } else if (categorySelect.value !== '') {
    categoryText = categorySelect.options[categorySelect.selectedIndex].text;
  }
  document.getElementById('preview-category').textContent = categoryText;
}

['item-title', 'item-price', 'item-rent-unit', 'item-area', 'item-category', 'item-suggest'].forEach(function (id) {
  document.getElementById(id).addEventListener('input', updatePreview);
  document.getElementById(id).addEventListener('change', updatePreview);
});

// ===== عداد أحرف الوصف =====

const description = document.getElementById('item-description');
const descriptionCount = document.getElementById('description-count');

function updateDescriptionCount() {
  descriptionCount.textContent = description.value.length + ' حرف';
}

description.addEventListener('input', updateDescriptionCount);

// ===== أول تحميل للصفحة =====
updateTypeFields();
updateDescriptionCount();
updatePhotos();
showStep(currentStep);
