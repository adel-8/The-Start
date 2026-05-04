// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // ---- DOM elements ----
    const form = document.getElementById('signinForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberCheck = document.getElementById('rememberMe');
    const forgotLink = document.getElementById('forgotPasswordLink');
    const googleBtn = document.getElementById('googleSigninBtn');
    const githubBtn = document.getElementById('githubSigninBtn');
    const toastEl = document.getElementById('demoToast');

    // ---- Helper: show floating message ----
    function showFloatingMessage(msg, isError = false) {
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.classList.add('show');
        if (isError) {
            toastEl.style.backgroundColor = '#dc3545';
        } else {
            toastEl.style.backgroundColor = 'rgba(45, 42, 53, 0.92)';
        }
        setTimeout(() => {
            toastEl.classList.remove('show');
            toastEl.textContent = '✨ Dolphin access';
            toastEl.style.backgroundColor = '';
        }, 2600);
    }

    // ---- Form submission (AJAX) ----
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                email: emailInput?.value.trim() || '',
                password: passwordInput?.value || '',
                remember: rememberCheck?.checked || false,
                _token: document.querySelector('input[name="_token"]')?.value || ''
            };

            try {
                const response = await fetch('/signin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': data._token
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showFloatingMessage(result.message, false);
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    showFloatingMessage(result.message || 'Login failed', true);
                }
            } catch (error) {
                console.error(error);
                showFloatingMessage('Network error, please try again', true);
            }
        });
    }

    // ---- Forgot password ----
    if (forgotLink) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            showFloatingMessage('📬 Password reset link sent (demo interaction)', false);
        });
    }

    // ---- Social login placeholders ----
    function handleSocial(provider) {
        showFloatingMessage(`🔑 ${provider} authentication demo — OAuth flow would start`, false);
    }
    if (googleBtn) googleBtn.addEventListener('click', () => handleSocial('Google'));
    if (githubBtn) githubBtn.addEventListener('click', () => handleSocial('GitHub'));

    // ---- Hotkey helper ----
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.altKey && e.key === 'd') {
            e.preventDefault();
            if (emailInput) emailInput.value = 'user@example.com';
            if (passwordInput) passwordInput.value = 'password';
            showFloatingMessage('🎭 Demo credentials injected', false);
            if (emailInput) emailInput.focus();
        }
    });

    // ---- Show/Hide Password Toggle ----
    const toggleButtons = document.querySelectorAll('.toggle-password');
    if (toggleButtons.length) {
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const wrapper = this.closest('.password-wrapper');
                if (!wrapper) return;

                const input = wrapper.querySelector('.input-field');
                if (!input) return;

                // Toggle type attribute
                const newType = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', newType);

                // Toggle the eye icon (Font Awesome 6)
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye-slash');
                    icon.classList.toggle('fa-eye');
                }
            });
        });
    } else {
        console.log('No password toggle buttons found on this page.');
    }
});