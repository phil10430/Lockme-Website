document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('userDetailsForm');
    const dialog = document.getElementById('userDetailsDialog');

    if (!form) return;

    const avatar = document.getElementById('userAvatar');
    const toggle = document.getElementById('profileVisibilityToggle');
    const hiddenInput = document.getElementById('profileVisibilityValue');
    const error = document.getElementById('userDetailsError');

    // ==========================================
    // PROFILE VISIBILITY
    // ==========================================

    function syncProfileVisibility() {
        if (!toggle || !hiddenInput) return;

        const visibility =
            window.profileVisibility === 'public'
                ? 'public'
                : 'private';

        toggle.checked = visibility === 'public';
        hiddenInput.value = visibility;
    }

    // Gespeicherten Status beim Laden setzen
    syncProfileVisibility();

    // Beim Öffnen des Dialogs erneut synchronisieren
    dialog?.addEventListener('beforetoggle', (event) => {
        if (event.newState === 'open') {
            syncProfileVisibility();
        }
    });

    // Beim Umschalten nur den Formularwert ändern
    toggle?.addEventListener('change', () => {
        if (hiddenInput) {
            hiddenInput.value = toggle.checked
                ? 'public'
                : 'private';
        }
    });

    // ==========================================
    // AVATAR PREVIEW
    // ==========================================

    avatar?.addEventListener('change', function () {
        const file = this.files?.[0];

        if (!file) return;

        if (
            !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
            file.size > 2 * 1024 * 1024
        ) {
            alert('Only JPG, PNG or WebP images up to 2 MB are allowed.');
            this.value = '';
            return;
        }

        const preview = document.getElementById('userAvatarPreview');

        if (preview) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });

    // ==========================================
    // SAVE PROFILE
    // ==========================================

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();

        if (error) {
            error.style.display = 'none';
            error.textContent = '';
        }

        // Sichtbarkeit vor dem Speichern synchronisieren
        if (toggle && hiddenInput) {
            hiddenInput.value = toggle.checked
                ? 'public'
                : 'private';
        }

        const data = new FormData(form);
        data.set('saveUserDetails', '1');

        const button = form.querySelector('[type="submit"]');

        if (button) {
            button.disabled = true;
        }

        try {
            const res = await fetch(location.href, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            });

            const result = await res.json();

            if (!res.ok || !result.success) {
                throw new Error(
                    result.message || 'Save failed.'
                );
            }

            // Gespeicherten Status übernehmen
            window.profileVisibility = hiddenInput?.value || 'private';

            // Seite neu laden:
            // - Badge aktualisieren
            // - Username aktualisieren
            // - Avatar aktualisieren
            location.reload();

        } catch (e) {
            if (error) {
                error.textContent = e.message;
                error.style.display = 'block';
            }
        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    });

    // ==========================================
    // CANCEL / CLOSE
    // ==========================================

    dialog?.addEventListener('close', () => {
        // Nicht gespeicherte Änderungen am Toggle verwerfen
        syncProfileVisibility();
    });
});