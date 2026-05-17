<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    function showToast(message, type = 'success') {
        const styles = {
            success: { background: '#22c55e' },
            error: { background: '#e53e3e' },
            warning: { background: '#f59e0b' },
            info: { background: '#3b82f6' }
        };

        if (typeof Toastify !== 'function') {
            const toast = document.createElement('div');
            toast.textContent = message;
            Object.assign(toast.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                zIndex: '100000',
                color: '#fff',
                borderRadius: '6px',
                fontSize: '14px',
                fontWeight: '500',
                padding: '12px 18px',
                boxShadow: '0 2px 12px rgba(0,0,0,0.15)',
                fontFamily: 'inherit',
                background: styles[type]?.background || styles.info.background
            });
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
            return;
        }

        Toastify({
            text: message,
            duration: 3000,
            gravity: 'top',
            position: 'right',
            stopOnFocus: true,
            style: {
                ...styles[type],
                borderRadius: '6px',
                fontSize: '14px',
                fontWeight: '500',
                padding: '12px 18px',
                boxShadow: '0 2px 12px rgba(0,0,0,0.15)',
                fontFamily: 'inherit'
            }
        }).showToast();
    }
</script>
