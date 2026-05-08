<!-- Toastify CSS + JS via CDN -->
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