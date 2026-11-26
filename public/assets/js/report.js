// ==========
// صفحة الإبلاغ: عداد أحرف التفاصيل
// ==========

const reasonInput = document.getElementById('report-reason');
const reasonCount = document.getElementById('reason-count');

function updateReasonCount() {
  reasonCount.textContent = reasonInput.value.length + ' / 500';
}

reasonInput.addEventListener('input', updateReasonCount);
updateReasonCount();
