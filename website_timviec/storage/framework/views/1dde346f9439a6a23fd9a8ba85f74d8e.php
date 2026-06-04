<script>
function togglePass(inputId, iconId) {
  const p = document.getElementById(inputId);
  const i = document.getElementById(iconId);
  p.type = p.type === 'password' ? 'text' : 'password';
  i.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

// Password strength meter
const pwdInput = document.getElementById('password');
const strengthBar = document.getElementById('strength-bar');
if (pwdInput && strengthBar) {
  pwdInput.addEventListener('input', function () {
    const val = this.value;
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
      { pct: '0%',   color: '#e9ecef', label: '' },
      { pct: '25%',  color: '#dc3545', label: 'Rất yếu' },
      { pct: '50%',  color: '#fd7e14', label: 'Yếu' },
      { pct: '75%',  color: '#ffc107', label: 'Trung bình' },
      { pct: '100%', color: '#28a745', label: 'Mạnh' },
    ];
    const lv = levels[score] || levels[0];
    strengthBar.style.setProperty('--strength', lv.pct);
    strengthBar.style.setProperty('--strength-color', lv.color);
    strengthBar.title = lv.label;
  });
}

// Password match check
const pwd2 = document.getElementById('password2');
const matchMsg = document.getElementById('match-msg');
if (pwd2 && matchMsg) {
  pwd2.addEventListener('input', function () {
    const match = this.value === pwdInput.value;
    matchMsg.style.display = this.value ? 'block' : 'none';
    matchMsg.style.color = match ? '#28a745' : '#dc3545';
    matchMsg.innerHTML = match
      ? '<i class="fas fa-check-circle"></i> Mật khẩu khớp'
      : '<i class="fas fa-times-circle"></i> Mật khẩu chưa khớp';
  });
}

// Terms checkbox — enable/disable submit button
const termsCheckbox = document.getElementById('terms-checkbox');
const submitBtn = document.getElementById('submit-btn');

function updateSubmitState() {
  if (!termsCheckbox || !submitBtn) return;
  if (termsCheckbox.checked) {
    submitBtn.disabled = false;
    submitBtn.style.opacity = '1';
    submitBtn.style.cursor = 'pointer';
  } else {
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.5';
    submitBtn.style.cursor = 'not-allowed';
  }
}

if (termsCheckbox) {
  // Set initial state (handles old('terms') = checked)
  updateSubmitState();
  termsCheckbox.addEventListener('change', updateSubmitState);
}

// Loading state on submit
const form = document.getElementById('register-form');
if (form && submitBtn) {
  form.addEventListener('submit', function () {
    if (!termsCheckbox || !termsCheckbox.checked) return;
    submitBtn.querySelector('.btn-text').style.display = 'none';
    submitBtn.querySelector('.btn-loading').style.display = 'inline';
    submitBtn.disabled = true;
  });
}
</script>
<?php /**PATH F:\dl\website_timviec_v15 (1)\website_timviec_v15 (1)\website_timviec\resources\views/auth/_auth-scripts.blade.php ENDPATH**/ ?>