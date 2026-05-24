<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h2 class="modal-title fs-5" id="deleteConfirmModalLabel">Konfirmasi Hapus</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Data yang dihapus tidak bisa dikembalikan. Lanjutkan?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" class="btn btn-danger" id="deleteConfirmButton">Hapus</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteModalElement = document.getElementById('deleteConfirmModal');
        const deleteButton = document.getElementById('deleteConfirmButton');
        let pendingDeleteForm = null;
        let pendingDeleteSubmitter = null;

        if (!deleteModalElement || !deleteButton) {
            return;
        }

        document.querySelectorAll('[data-confirm-delete]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                deleteButton.href = trigger.getAttribute('href');
                pendingDeleteForm = null;
                pendingDeleteSubmitter = null;
                new bootstrap.Modal(deleteModalElement).show();
            });
        });

        document.querySelectorAll('[data-confirm-delete-submit]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                deleteButton.href = '#';
                pendingDeleteForm = trigger.closest('form');
                pendingDeleteSubmitter = trigger;
                new bootstrap.Modal(deleteModalElement).show();
            });
        });

        deleteButton.addEventListener('click', function (event) {
            if (!pendingDeleteForm) {
                return;
            }

            event.preventDefault();

            if (pendingDeleteSubmitter && pendingDeleteSubmitter.name) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = pendingDeleteSubmitter.name;
                hiddenInput.value = pendingDeleteSubmitter.value || '1';
                pendingDeleteForm.appendChild(hiddenInput);
            }

            pendingDeleteForm.submit();
        });
    });
</script>
