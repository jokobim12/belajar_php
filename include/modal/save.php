<div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-labelledby="saveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h2 class="modal-title fs-5" id="saveConfirmModalLabel">Konfirmasi Simpan</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Data akan disimpan. Lanjutkan?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="saveConfirmButton">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let pendingSaveForm = null;
        const saveModalElement = document.getElementById('saveConfirmModal');
        const saveButton = document.getElementById('saveConfirmButton');

        if (!saveModalElement || !saveButton) {
            return;
        }

        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-confirm-save]');

            if (!form) {
                return;
            }

            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            pendingSaveForm = form;
            new bootstrap.Modal(saveModalElement).show();
        });

        saveButton.addEventListener('click', async function () {
            if (!pendingSaveForm) {
                return;
            }

            pendingSaveForm.dataset.confirmed = 'true';

            if (pendingSaveForm.hasAttribute('data-ajax-save')) {
                const response = await fetch(pendingSaveForm.action || window.location.href, {
                    method: pendingSaveForm.method || 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(pendingSaveForm)
                });
                const data = await response.json();

                if (data.success) {
                    const pageResponse = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await pageResponse.text();
                    const page = new DOMParser().parseFromString(html, 'text/html');
                    const currentMain = document.querySelector('main');
                    const nextMain = page.querySelector('main');

                    if (currentMain && nextMain) {
                        currentMain.innerHTML = nextMain.innerHTML;
                    }

                    bootstrap.Modal.getInstance(saveModalElement).hide();
                }

                pendingSaveForm.dataset.confirmed = 'false';
                pendingSaveForm = null;
                return;
            }

            pendingSaveForm.submit();
        });
    });
</script>
