<script>
    (function () {
        const detectBtn = document.getElementById('detect-location-btn');
        if (!detectBtn) return;

        const detectBtnHtml = '<i class="fa-solid fa-location-dot"></i> Detect';
        const detectingBtnHtml = '<i class="fa-solid fa-hourglass-half"></i> Detecting...';

        detectBtn.addEventListener('click', function () {
            const btn = this;
            const status = document.getElementById('location-status');
            const input = document.getElementById('location_display');

            if (!navigator.geolocation) {
                status.textContent = 'Geolocation is not supported by your browser.';
                status.style.color = 'red';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = detectingBtnHtml;
            status.style.color = 'var(--text-muted)';
            status.textContent = 'Getting your location...';

            navigator.geolocation.getCurrentPosition(
                async function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    try {
                        const res = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
                            { headers: { 'Accept-Language': 'en' } }
                        );
                        const data = await res.json();

                        const parts = [
                            data.address?.suburb,
                            data.address?.city || data.address?.town || data.address?.village,
                            data.address?.state,
                            data.address?.country
                        ].filter(Boolean);

                        input.value = parts.join(', ');
                        status.innerHTML = '<i class="fa-solid fa-circle-check"></i> Location detected successfully.';
                        status.style.color = 'green';
                    } catch (e) {
                        input.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                        status.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Could not get location name, coordinates saved.';
                        status.style.color = 'orange';
                    }

                    btn.disabled = false;
                    btn.innerHTML = detectBtnHtml;
                },
                function (err) {
                    const messages = {
                        1: 'Permission denied. Please allow location access.',
                        2: 'Location unavailable. Try again.',
                        3: 'Request timed out. Try again.'
                    };
                    status.textContent = messages[err.code] || 'Could not get location.';
                    status.style.color = 'red';
                    btn.disabled = false;
                    btn.innerHTML = detectBtnHtml;
                },
                { timeout: 10000, maximumAge: 60000 }
            );
        });
    })();
</script>
