// assets/js/profile.js

(function () {
    'use strict';

    // ── Password show/hide toggles ────────────────────────────────────────
    document.querySelectorAll('.toggle-pwd').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.input-group').querySelector('input');
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // ── Password match hint ───────────────────────────────────────────────
    const newPwd     = document.getElementById('new_password');
    const confirmPwd = document.getElementById('confirm_password');
    const matchHint  = document.getElementById('pwdMatchHint');

    function checkMatch() {
        if (!newPwd || !confirmPwd || !matchHint) return;
        if (confirmPwd.value === '') {
            matchHint.textContent = '';
            matchHint.className = 'form-hint';
            return;
        }
        if (newPwd.value === confirmPwd.value) {
            matchHint.textContent = 'Mật khẩu khớp.';
            matchHint.className = 'form-hint hint-ok';
        } else {
            matchHint.textContent = 'Mật khẩu chưa khớp.';
            matchHint.className = 'form-hint hint-error';
        }
    }

    if (newPwd)     newPwd.addEventListener('input', checkMatch);
    if (confirmPwd) confirmPwd.addEventListener('input', checkMatch);

    // ── Auto-dismiss flash alerts after 4s ───────────────────────────────
    document.querySelectorAll('.profile-alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

})();
