    </div>
    <!-- End page wrapper -->
    
    <!-- Start overlay -->
    <div class="overlay mobile-toggle-icon"></div>
    <!-- End overlay -->
    
    <!-- Start Back To Top Button -->
    <a href="javaScript:;" class="back-to-top">
        <i class='bx bxs-up-arrow-alt'></i>
    </a>
    <!-- End Back To Top Button -->
    
    <footer class="page-footer" style="height: 20px;">
        <p class="mb-0">Copyright © <?= date('Y') ?>. All right reserved.</p>
    </footer>
</div>
<!--end wrapper-->

<!-- Start Chat Button -->
<button class="btn btn-primary position-fixed bottom-0 end-0 m-3 d-flex align-items-center gap-2 py-4" style="margin-bottom: 100px;" type="button" id="chatToggleBtn">
    <span class="position-relative">
        <i class='bx bx-chat fs-4'></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="chatUnreadBadge" style="font-size: 10px;">0</span>
    </span>
    <span>Chat</span>
</button>




<!-- jQuery (doit être chargé AVANT Bootstrap et autres plugins) -->
<script src="<?= base_url() ?>assets/backend/js/jquery.min.js"></script>

<!-- Bootstrap JS -->
<script src="<?= base_url() ?>assets/backend/js/bootstrap.bundle.min.js"></script>

<!-- Plugins -->
<script src="<?= base_url() ?>assets/backend/plugins/simplebar/js/simplebar.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/apexcharts-bundle/js/apexcharts.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/peity/jquery.peity.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<!-- Quill Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- App JS -->
<script src="<?= base_url() ?>assets/backend/js/app.js"></script>
<script src="<?= base_url() ?>assets/backend/js/index.js"></script>



<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
    // DataTable - Vérifier si la table existe avant d'initialiser
    if (document.getElementById('table')) {
        $('#table').DataTable({
            language: { 
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                emptyTable: "Aucune donnée disponible",
                zeroRecords: "Aucun résultat trouvé"
            },
            order: [[0, 'asc']], // Tri par ordre (colonne 0)
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [4, 6] } // Image et Actions non triables
            ]
        });
    }
    
    // DataTable pour médecins (si table différente)
    if (document.getElementById('example')) {
        $('#example').DataTable({
            language: { 
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
                emptyTable: "Aucun médecin disponible",
                zeroRecords: "Aucun médecin trouvé"
            },
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [1, 9] }
            ]
        });
    }

    // Auto-hide alerts après 5 secondes
    setTimeout(function() { 
        $('.alert').fadeOut('slow'); 
    }, 5000);
});
</script>

</body>
</html>