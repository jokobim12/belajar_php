<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h2 class="modal-title fs-5" id="logoutConfirmModalLabel">Konfirmasi Logout</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Kamu yakin ingin keluar dari akun ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" class="btn btn-success" id="logoutConfirmButton">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const logoutModalElement = document.getElementById('logoutConfirmModal');
        const logoutButton = document.getElementById('logoutConfirmButton');

        if (!logoutModalElement || !logoutButton) {
            return;
        }

        document.querySelectorAll('[data-confirm-logout]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                logoutButton.href = trigger.getAttribute('href');
                new bootstrap.Modal(logoutModalElement).show();
            });
        });
    });
</script>
