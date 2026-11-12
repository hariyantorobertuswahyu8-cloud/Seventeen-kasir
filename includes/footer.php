            </main>
            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-center text-muted small mt-auto">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong><?= htmlspecialchars($toko['nama_toko']) ?></strong> &copy; <?= date('Y') ?> - Sistem Kasir Berbasis Web (Metode Waterfall)
                    </div>
                    <div>
                        <span>Status Sistem: <span class="badge bg-success"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Online</span></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Local Bootstrap 5 JS Bundle (Offline Ready) -->
    <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar on mobile
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
</body>
</html>
