// Theme management
const THEME_KEY = 'kotiza_theme';

function getTheme() {
  return localStorage.getItem(THEME_KEY) || 'dark';
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem(THEME_KEY, theme);
  const icon = document.getElementById('theme-icon');
  if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
}

function toggleTheme() {
  const current = getTheme();
  applyTheme(current === 'dark' ? 'light' : 'dark');
}

// Animated counters
function animateCounter(el) {
  const target = parseInt(el.dataset.target);
  const suffix = el.dataset.suffix || '';
  const prefix = el.dataset.prefix || '';
  const duration = 2000;
  const start = performance.now();

  function update(now) {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.floor(eased * target);
    el.textContent = prefix + current.toLocaleString('fr-FR') + suffix;
    if (progress < 1) requestAnimationFrame(update);
  }
  requestAnimationFrame(update);
}

// Intersection observer for scroll animations
function setupScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        if (entry.target.classList.contains('counter')) {
          animateCounter(entry.target);
        }
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  document.querySelectorAll('.fade-in, .counter').forEach(el => observer.observe(el));
}

// Toast notifications
function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container') || (() => {
    const c = document.createElement('div');
    c.id = 'toast-container';
    c.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;';
    document.body.appendChild(c);
    return c;
  })();

  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} border-0 show mb-2`;
  toast.setAttribute('role', 'alert');
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

// Payment timer
let paymentTimer = null;

function startPaymentTimer(seconds, onExpire) {
  clearInterval(paymentTimer);
  const display = document.getElementById('payment-timer');
  if (!display) return;

  let remaining = seconds;

  function update() {
    const m = Math.floor(remaining / 60).toString().padStart(2, '0');
    const s = (remaining % 60).toString().padStart(2, '0');
    display.textContent = `${m}:${s}`;
    if (remaining <= 0) {
      clearInterval(paymentTimer);
      if (onExpire) onExpire();
    }
    remaining--;
  }
  update();
  paymentTimer = setInterval(update, 1000);
}

// Poll payment status
function pollPaymentStatus(transactionId, reference, interval = 3000) {
  const poller = setInterval(async () => {
    try {
      const res = await fetch(`/api/poll-status.php?transaction_id=${encodeURIComponent(transactionId)}&reference=${encodeURIComponent(reference)}`);
      const data = await res.json();

      if (data.status === 'success' || data.status === 'completed') {
        clearInterval(poller);
        clearInterval(paymentTimer);
        document.getElementById('payment-status-section')?.classList.add('d-none');
        document.getElementById('payment-success-section')?.classList.remove('d-none');
        setTimeout(() => {
          window.location.href = data.redirect || '/';
        }, 2000);
      } else if (data.status === 'failed') {
        clearInterval(poller);
        clearInterval(paymentTimer);
        document.getElementById('payment-status-section')?.classList.add('d-none');
        document.getElementById('payment-failed-section')?.classList.remove('d-none');
      }
    } catch (e) {}
  }, interval);
  return poller;
}

// Share functions
function shareWhatsApp(url, text) {
  window.open(`https://wa.me/?text=${encodeURIComponent(text + '\n' + url)}`, '_blank');
}
function shareFacebook(url) {
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
}
function shareTelegram(url, text) {
  window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`, '_blank');
}
function shareTikTok(url) {
  navigator.clipboard?.writeText(url).then(() => showToast('Lien copié ! Partagez-le sur TikTok.', 'info'));
}

// Copy to clipboard
function copyToClipboard(text) {
  navigator.clipboard?.writeText(text).then(() => showToast('Copié !', 'success'));
}

// Withdrawal live calc
function setupWithdrawalCalc() {
  const amountInput = document.getElementById('withdraw-amount');
  const commDisplay = document.getElementById('commission-display');
  const netDisplay = document.getElementById('net-display');
  if (!amountInput) return;

  amountInput.addEventListener('input', () => {
    const amount = parseFloat(amountInput.value) || 0;
    const commission = amount * 0.10;
    const net = amount - commission;
    if (commDisplay) commDisplay.textContent = commission.toLocaleString('fr-FR') + ' FCFA';
    if (netDisplay) netDisplay.textContent = net.toLocaleString('fr-FR') + ' FCFA';
  });
}

// Form validation
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return false;
  let valid = true;
  form.querySelectorAll('[required]').forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('is-invalid');
      valid = false;
    } else {
      input.classList.remove('is-invalid');
    }
  });
  return valid;
}

// Image preview
function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  if (!preview || !input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.classList.remove('d-none');
  };
  reader.readAsDataURL(input.files[0]);
}

// Navbar scroll effect
function setupNavbar() {
  const navbar = document.getElementById('main-navbar');
  if (!navbar) return;
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  });
}

// Testimonials slider
function setupSlider() {
  let current = 0;
  const slides = document.querySelectorAll('.testimonial-slide');
  if (!slides.length) return;

  function showSlide(idx) {
    slides.forEach((s, i) => s.classList.toggle('active', i === idx));
  }

  setInterval(() => {
    current = (current + 1) % slides.length;
    showSlide(current);
  }, 4000);
  showSlide(0);
}

document.addEventListener('DOMContentLoaded', () => {
  applyTheme(getTheme());
  setupScrollAnimations();
  setupWithdrawalCalc();
  setupNavbar();
  setupSlider();

  document.getElementById('theme-toggle')?.addEventListener('click', toggleTheme);

  // Auto-hide flash alerts
  document.querySelectorAll('.alert-dismissible').forEach(alert => {
    setTimeout(() => alert.remove(), 5000);
  });
});
