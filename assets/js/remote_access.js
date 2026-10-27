document.addEventListener('DOMContentLoaded', () => {
    const config = window.remoteAccessConfig;
    const dialog = document.getElementById('remoteAccessDialog');
    const form = document.getElementById('remoteGrantForm');
    const users = document.getElementById('remoteAccessUsers');
    const error = document.getElementById('remoteAccessError');
    const success = document.getElementById('remoteAccessSuccess');

    if (!config || !dialog || !form || !users) return;

    let currentBoxId = null;

    const permissions = config.permissions || {};

    // ==========================================
    // NOTIFICATIONS
    // ==========================================

    function notify(message, isError = false) {
        error.style.display = isError ? 'block' : 'none';
        success.style.display = isError ? 'none' : 'block';

        (isError ? error : success).textContent = message;
    }

    // ==========================================
    // RENDER USERS WITH REMOTE ACCESS
    // ==========================================

    function render() {
        users.replaceChildren();

        const list = permissions[currentBoxId] || [];

        if (!list.length) {
            const empty = document.createElement('p');

            empty.className = 'settings-muted';
            empty.textContent = 'No other users have access.';

            users.appendChild(empty);
            return;
        }

        for (const permission of list) {
            const row = document.createElement('div');
            row.className = 'remote-access-user';

            const name = document.createElement('span');
            name.textContent = permission.username;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn-modern';
            button.textContent = 'Revoke';

            button.addEventListener('click', async () => {
                if (!confirm(`Revoke remote access for ${permission.username}?`)) {
                    return;
                }

                button.disabled = true;

                try {
                    await send({
                        revokeRemoteAccess: '1',
                        boxId: currentBoxId,
                        targetUserId: permission.user_id
                    });

                    permissions[currentBoxId] =
                        permissions[currentBoxId].filter(
                            p => String(p.user_id) !== String(permission.user_id)
                        );

                    render();
                    notify('Remote access revoked.');

                } catch (e) {
                    notify(e.message, true);

                } finally {
                    button.disabled = false;
                }
            });

            row.append(name, button);
            users.appendChild(row);
        }
    }

    // ==========================================
    // SEND REQUEST TO PHP
    // ==========================================

    async function send(fields) {
        const body = new URLSearchParams({
            ...fields,
            csrf_token: config.csrfToken
        });

        const response = await fetch(location.href, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body
        });

        let result;

        try {
            result = await response.json();

        } catch {
            throw new Error('Unexpected server response.');
        }

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Request failed.');
        }

        return result;
    }

    // ==========================================
    // OPEN MANAGE ACCESS DIALOG
    // ==========================================

    document.querySelectorAll('[data-remote-manage-box]').forEach(button => {
        button.addEventListener('click', () => {
            currentBoxId = button.dataset.remoteManageBox;

            document.getElementById('remoteAccessBoxLabel').textContent =
                currentBoxId;

            form.reset();

            error.style.display = 'none';
            success.style.display = 'none';

            render();

            dialog.showModal();
        });
    });

    // ==========================================
    // GRANT REMOTE ACCESS
    // ==========================================

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const username = form.elements.username.value.trim();

        if (!username || !currentBoxId) return;

        const button = form.querySelector('[type="submit"]');
        button.disabled = true;

        try {
            const result = await send({
                grantRemoteAccess: '1',
                boxId: currentBoxId,
                username
            });

            permissions[currentBoxId] ||= [];

            permissions[currentBoxId] =
                permissions[currentBoxId].filter(
                    p => String(p.user_id) !== String(result.user_id)
                );

            permissions[currentBoxId].push({
                user_id: result.user_id,
                username: result.username
            });

            permissions[currentBoxId].sort(
                (a, b) => a.username.localeCompare(b.username)
            );

            form.reset();

            render();

            notify('Remote access granted.');

        } catch (e) {
            notify(e.message, true);

        } finally {
            button.disabled = false;
        }
    });

    // ==========================================
    // CLOSE DIALOG
    // ==========================================

    document.getElementById('remoteAccessClose')
        ?.addEventListener('click', () => dialog.close());
});