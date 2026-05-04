document.addEventListener('DOMContentLoaded', () => {
  // DOM elements
  const form = document.getElementById('signupForm');
  const firstNameInput = document.getElementById('firstName');
  const lastNameInput = document.getElementById('lastName');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const confirmInput = document.getElementById('confirmPassword');
  const termsCheck = document.getElementById('termsCheckbox');
  const googleBtn = document.getElementById('googleSignupBtn');
  const githubBtn = document.getElementById('githubSignupBtn');
  const signinRedirect = document.getElementById('signinRedirect');
  const termsLink = document.getElementById('termsLink');
  const privacyLink = document.getElementById('privacyLink');
  const strengthSpan = document.getElementById('strengthIndicator');
  const toastEl = document.getElementById('demoToast');

  // Helper: show toast (for client-side feedback)
  function showMessage(msg, isError = false) {
    toastEl.textContent = msg || (isError ? '⚠️ Please check the form' : '✨ Welcome aboard');
    toastEl.classList.add('show');
    setTimeout(() => {
      toastEl.classList.remove('show');
      toastEl.textContent = '✨ Join Dolphin';
    }, 2600);
  }

  // Password strength (client-side only, no blocking)
  function updateStrength(password) {
    if (!password) {
      strengthSpan.textContent = '⚡ strength';
      strengthSpan.style.background = 'var(--color-border)';
      strengthSpan.style.color = 'var(--color-muted)';
      return;
    }
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    if (strength <= 1) {
      strengthSpan.textContent = '🔴 weak';
      strengthSpan.style.background = '#f9e0d4';
      strengthSpan.style.color = '#aa5e3a';
    } else if (strength === 2) {
      strengthSpan.textContent = '🟡 fair';
      strengthSpan.style.background = '#fcf2db';
      strengthSpan.style.color = '#b87c2e';
    } else if (strength === 3) {
      strengthSpan.textContent = '🟢 good';
      strengthSpan.style.background = '#e1f3e6';
      strengthSpan.style.color = '#2c6e4f';
    } else {
      strengthSpan.textContent = '💪 strong';
      strengthSpan.style.background = '#e1e8f0';
      strengthSpan.style.color = '#2d4a6e';
    }
  }

  passwordInput.addEventListener('input', (e) => updateStrength(e.target.value));

  // ✅ Client-side validation only – does NOT block submission
  // If any validation fails, we prevent the form from sending.
  // If all passes, the form will submit normally.
  function validateForm() {
    const firstName = firstNameInput.value.trim();
    const lastName = lastNameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    const terms = termsCheck.checked;

    if (!email) {
      showMessage('📧 Email address is required', true);
      return false;
    }
    if (!email.includes('@') || !email.includes('.') || email.trim().length < 5) {
      showMessage('✉️ Please enter a valid email address', true);
      return false;
    }
    if (!password) {
      showMessage('🔒 Password cannot be empty', true);
      return false;
    }
    if (password.length < 8) {
      showMessage('🔐 Password must be at least 8 characters', true);
      return false;
    }
    if (password !== confirm) {
      showMessage('❌ Passwords do not match', true);
      return false;
    }
    if (!terms) {
      showMessage('📜 Please accept the Terms of Service to continue', true);
      return false;
    }
    return true;
  }

  // ✅ Submit handler – NO preventDefault() here!
  form.addEventListener('submit', (e) => {
    // Let the browser submit the form normally; we just do client-side validation.
    // If validation fails, we prevent the submission.
    if (!validateForm()) {
      e.preventDefault();  // Only prevent if validation fails
    }
    // Otherwise, the form will proceed to the server (Laravel)
  });

  // Social registration – you'll likely redirect to OAuth routes
  function socialSignup(provider) {
    // Redirect to Laravel OAuth routes (e.g., /auth/{provider}/redirect)
    window.location.href = `/auth/${provider.toLowerCase()}/redirect`;
  }
  googleBtn.addEventListener('click', () => socialSignup('Google'));
  githubBtn.addEventListener('click', () => socialSignup('GitHub'));

  // Terms & privacy demo links – you may link to actual pages
  termsLink.addEventListener('click', (e) => {
    e.preventDefault(); // Prevent navigation to # (optional)
    window.location.href = '/terms'; // Change to your actual route
  });
  privacyLink.addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = '/privacy';
  });

  // Keyboard shortcuts (Ctrl+Alt+F fills demo, Ctrl+Alt+S triggers validation but doesn't submit)
  document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.altKey && e.key === 'f') {
      e.preventDefault();
      firstNameInput.value = 'Jamie';
      lastNameInput.value = 'River';
      emailInput.value = 'jamie@dolphin.style';
      passwordInput.value = 'DemoPass123!';
      confirmInput.value = 'DemoPass123!';
      termsCheck.checked = true;
      updateStrength(passwordInput.value);
      showMessage('🎭 Demo registration data filled', false);
      emailInput.focus();
    }
    if (e.ctrlKey && e.altKey && e.key === 's') {
      e.preventDefault();
      // Just validate – do not submit automatically
      validateForm();
    }
  });

  updateStrength('');
  console.log('Sign-up page ready – form will submit to Laravel');
});