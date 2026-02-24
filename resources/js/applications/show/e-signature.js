(() => {
    const typedTab = document.getElementById('tab-typed');
    const drawnTab = document.getElementById('tab-drawn');
    const clearBtn = document.getElementById('clear-signature-btn');
    const eSignatureAccordionBtn = document.getElementById('e-signature-btn');

    if (eSignatureAccordionBtn) {
        eSignatureAccordionBtn.addEventListener('click', () => {
            toggleAccordion('e-signature');
        });
    }

    function showSignatureType(type) {
        document.querySelectorAll('.signature-panel').forEach(panel => panel.classList.add('hidden'));
        document.getElementById('signature-' + type).classList.remove('hidden');

        document.querySelectorAll('.signature-tab').forEach(tab => {
            tab.classList.remove('border-indigo-500', 'text-indigo-600');
            tab.classList.add('border-transparent', 'text-gray-500');
            tab.removeAttribute('aria-current');
        });

        const activeTab = document.getElementById('tab-' + type);
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-indigo-500', 'text-indigo-600');
        activeTab.setAttribute('aria-current', 'page');

        // Ensure this hidden input exists in your form
        const typeInput = document.getElementById('signature-type');
        if (typeInput) typeInput.value = type;
    };

    function clearCanvas() {
        const canvas = document.getElementById('signature-canvas');
        const ctx = canvas?.getContext('2d');
        if (confirm('Are you sure you want to clear your signature?')) {
            if (ctx && canvas) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const dataInput = document.getElementById('signature-data');
                if (dataInput) dataInput.value = '';
            }
        }
    };

    // 2. Define internal helper (doesn't need to be global)
    function saveCanvasData() {
        const canvas = document.getElementById('signature-canvas');
        const dataInput = document.getElementById('signature-data');
        if (canvas && dataInput) {
            dataInput.value = canvas.toDataURL('image/png');
        }
    }

    const canvas = document.getElementById('signature-canvas');
    const typedInput = document.getElementById('typed-signature-input');
    const preview = document.getElementById('typed-signature-preview');

    // Typed Logic
    typedInput?.addEventListener('input', (e) => {
        const val = e.target.value.trim();
        preview.textContent = val || 'Your signature will appear here';
        preview.style.color = val ? '#1F2937' : '#9CA3AF';
        const dataInput = document.getElementById('signature-data');
        if (dataInput) dataInput.value = val;
    });

    // Canvas Logic
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        ctx.strokeStyle = '#1F2937';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';

        const getCoords = (e) => {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
        };

        const start = (e) => {
            isDrawing = true;
            const coords = getCoords(e);
            ctx.beginPath();
            ctx.moveTo(coords.x, coords.y);
        };

        const move = (e) => {
            if (!isDrawing) return;
            if (e.touches) e.preventDefault();
            const coords = getCoords(e);
            ctx.lineTo(coords.x, coords.y);
            ctx.stroke();
            saveCanvasData();
        };

        const stop = () => { isDrawing = false; ctx.closePath(); };

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', stop);

        typedTab?.addEventListener('click', () => {
            showSignatureType('typed');
        });

        drawnTab?.addEventListener('click', () => {
            showSignatureType('drawn');
        });

        clearBtn?.addEventListener('click', clearCanvas);
            }
})();
