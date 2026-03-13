(() => {
    const clearBtn            = document.getElementById('clear-signature-btn');
    const eSignatureAccordionBtn = document.getElementById('e-signature-btn');

    if (eSignatureAccordionBtn) {
        eSignatureAccordionBtn.addEventListener('click', () => {
            toggleAccordion('e-signature');
        });
    }

    function saveCanvasData() {
        const canvas    = document.getElementById('signature-canvas');
        const dataInput = document.getElementById('signature-data');
        if (canvas && dataInput) {
            dataInput.value = canvas.toDataURL('image/png');
        }
    }

    function clearCanvas() {
        const canvas = document.getElementById('signature-canvas');
        const ctx    = canvas?.getContext('2d');
        if (confirm('Are you sure you want to clear your signature?')) {
            if (ctx && canvas) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const dataInput = document.getElementById('signature-data');
                if (dataInput) dataInput.value = '';
            }
        }
    }

    const canvas = document.getElementById('signature-canvas');

    if (canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        ctx.strokeStyle = '#1F2937';
        ctx.lineWidth   = 3;
        ctx.lineCap     = 'round';

        const getCoords = (e) => {
            const rect   = canvas.getBoundingClientRect();
            const scaleX = canvas.width  / rect.width;
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

        canvas.addEventListener('mousedown',  start);
        canvas.addEventListener('mousemove',  move);
        window.addEventListener('mouseup',    stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove',  move,  { passive: false });
        canvas.addEventListener('touchend',   stop);
    }

    clearBtn?.addEventListener('click', clearCanvas);
})();