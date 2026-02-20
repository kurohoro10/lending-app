// resources/js/admin/settings.js
// Handles show/hide toggle for password fields on the settings page.

(() => {
    const section = document.getElementById('settings-section') ?? document;

    section.addEventListener('click', (e) => {
        const btn = e.target.closest('.settings-toggle-secret');
        if (!btn) return;

        const targetId = btn.dataset.target;
        const input    = document.getElementById(targetId);
        if (!input) return;

        const isHidden = input.type === 'password';
        input.type     = isHidden ? 'text' : 'password';

        btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
        btn.querySelector('.icon-eye')?.classList.toggle('hidden', isHidden);
        btn.querySelector('.icon-eye-off')?.classList.toggle('hidden', !isHidden);
    });
})();
