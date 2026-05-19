/**
 * TWINS Authentication Scripts
 * Centralized JS handlers for all auth pages (Login, Register, Forgot Password, Reset Password, Confirm Password)
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide Icons if available
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Process Session Alerts & Success messages
    handleSessionAlerts();
});

/**
 * Toggle visibility of password inputs
 * @param {string} inputId 
 * @param {string} iconId 
 */
function togglePassword(inputId, iconId) {
    const passInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);
    
    if (passInput && eyeIcon) {
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.setAttribute('data-lucide', 'eye-off');
        } else {
            passInput.type = 'password';
            eyeIcon.setAttribute('data-lucide', 'eye');
        }
        
        if (typeof lucide !== 'undefined') {
            lucide.createIcons(); 
        }
    }
}

/**
 * Close status overlays
 * @param {string} id 
 */
function tutupOverlay(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }
}

/**
 * Login Animation and Validation
 * @param {Event} e 
 */
function mulaiAnimasi(e) {
    const form = document.getElementById('loginForm');
    const emailEl = document.getElementById('inputEmail');
    const passwordEl = document.getElementById('inputConfirm');
    
    if (!form || !emailEl || !passwordEl) return;
    
    const email = emailEl.value;
    const password = passwordEl.value;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    // Only animate if fields are filled and valid
    if (email.trim() !== '' && 
        password.trim() !== '' && 
        emailPattern.test(email) && 
        password.length >= 6) {
        
        e.preventDefault();

        const wrapperDua = document.getElementById('wrapperGambarDua');
        const overlaySukses = document.getElementById('overlaySukses');
        const bar = document.getElementById('progressBar');

        if (wrapperDua) {
            wrapperDua.classList.add('image-slide-out-right');
        }

        setTimeout(() => {
            if (overlaySukses) {
                overlaySukses.style.display = 'flex';
                overlaySukses.style.opacity = '1';
            }

            setTimeout(() => {
                if (bar) {
                    bar.style.width = '100%';
                }
            }, 100);

            setTimeout(() => {
                form.submit();
            }, 2000);

        }, 600);
    }
}

/**
 * Show loading indicator on buttons during form submission
 * @param {HTMLElement} btn 
 */
function tampilkanLoading(btn) {
    const form = btn.closest('form');
    if (form && form.checkValidity()) {
        const text = btn.innerText.trim();
        let loadingText = 'Memproses...';
        
        if (text.includes('Kirim')) {
            loadingText = 'Mengirim...';
        }
        
        btn.innerHTML = loadingText;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'not-allowed';
    }
}

/**
 * Handle session alerts dynamically based on page indicators
 */
function handleSessionAlerts() {
    if (typeof Swal === 'undefined') return;
    
    // 1. Login Page Session Status
    const sessionStatusEl = document.getElementById('session-status');
    const sessionSuccessEl = document.getElementById('session-success');
    
    if (sessionStatusEl || sessionSuccessEl) {
        const statusMsg = sessionStatusEl ? sessionStatusEl.dataset.value : null;
        const successMsg = sessionSuccessEl ? sessionSuccessEl.dataset.value : null;
        const messageToShow = statusMsg || successMsg;

        if (messageToShow) {
            Swal.fire({
                title: 'Berhasil!',
                text: messageToShow,
                icon: 'success',
                confirmButtonColor: '#0477bf',
                timer: 2000,
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                }
            });
        }
    }

    // 2. Generic Pages Session Data (Register, Forgot, Reset, Confirm)
    const sessionDataEl = document.getElementById('session-data');
    if (sessionDataEl) {
        const errors = JSON.parse(sessionDataEl.dataset.errors || '[]');
        const successMessage = sessionDataEl.dataset.success;
        const statusMessage = sessionDataEl.dataset.status;
        const confirmed = sessionDataEl.dataset.confirmed;

        if (errors.length > 0) {
            const isRegisterPage = document.querySelector('form[action*="register"]');
            const isForgotPage = document.getElementById('forgotForm');
            const isResetPage = document.getElementById('resetForm');
            const isConfirmPage = document.querySelector('form[action*="password-confirm"]') || sessionDataEl.dataset.confirmed !== undefined;

            if (isRegisterPage) {
                let listHtml = '<ul style="text-align: left; font-size: 14px; color: #555; list-style-type: none; padding: 0;">';
                errors.forEach(function(msg) {
                    listHtml += `<li style="margin-bottom: 8px; display: flex; align-items: center;">
                        <span style="color: #ef4444; margin-right: 8px;">●</span> ${msg}
                    </li>`;
                });
                listHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal',
                    html: listHtml,
                    confirmButtonColor: '#0477bf',
                    confirmButtonText: 'Coba Lagi',
                    showClass: {
                        popup: 'animate__animated animate__shakeX'
                    }
                });
            } else if (isForgotPage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Opps!',
                    text: errors[0], 
                    confirmButtonColor: '#0477bf',
                    showClass: { popup: 'animate__animated animate__shakeX' }
                });
            } else if (isResetPage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Update',
                    text: errors[0],
                    confirmButtonColor: '#0477bf',
                    showClass: { popup: 'animate__animated animate__shakeX' }
                });
            } else if (isConfirmPage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Konfirmasi Gagal',
                    text: 'Kata sandi yang Anda masukkan salah.',
                    confirmButtonColor: '#0477bf',
                    showClass: { popup: 'animate__animated animate__shakeX' }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errors[0],
                    confirmButtonColor: '#0477bf',
                    showClass: { popup: 'animate__animated animate__shakeX' }
                });
            }
        }

        if (successMessage) {
            Swal.fire({
                title: 'Berhasil!',
                text: successMessage,
                icon: 'success',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__backInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__backOutUp'
                }
            });
        }

        if (statusMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Email Terkirim!',
                text: statusMessage,
                confirmButtonColor: '#0477bf',
                showClass: { popup: 'animate__animated animate__fadeInDown' }
            });
        }

        if (confirmed && !errors.length) {
            const now = Math.floor(Date.now() / 1000);
            if (now - confirmed < 5) { 
                Swal.fire({
                    icon: 'success',
                    title: 'Identitas Terverifikasi',
                    text: 'Akses diberikan. Silakan lanjut.',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
        }
    }
}
