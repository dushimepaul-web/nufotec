<!-- jQuery (doit être chargé AVANT Bootstrap et autres plugins) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Plugins -->
<script src="<?= base_url() ?>assets/backend/plugins/simplebar/js/simplebar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/metismenu/dist/metisMenu.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.43.0/dist/apexcharts.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/peity/jquery.peity.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Quill Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>

<!-- TinyMCE (API Key nécessaire) -->
<script src="https://cdn.tiny.cloud/1/VOTRE_CLE_API_ICI/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<!-- App JS -->
<script src="<?= base_url() ?>assets/backend/js/app.js"></script>
<script src="<?= base_url() ?>assets/backend/js/index.js"></script>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {

    // === DataTables sécurisées ===
    ['table','example'].forEach(function(id){
        if (document.getElementById(id)) {
            $('#' + id).DataTable({
                language: { 
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                },
                pageLength: 25,
                responsive: true
            });
        }
    });

    // === ApexCharts exemple ===
    if (document.querySelector("#chart1")) {
        var chart1 = new ApexCharts(document.querySelector("#chart1"), {
            chart: { type: 'line' },
            series: [{ name: 'Exemple', data: [10,20,30,40] }],
            xaxis: { categories: ['Jan','Feb','Mar','Apr'] }
        });
        chart1.render();
    }

    // === Summernote ===
    if ($('#summernote').length) {
        $('#summernote').summernote({
            height: 200
        });
    }

    // === TinyMCE ===
    if (document.querySelector("#mytextarea")) {
        tinymce.init({
            selector: '#mytextarea',
            apiKey: 'VOTRE_CLE_API_ICI',  // Remplacer par votre clé TinyMCE
            plugins: 'lists link image',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright'
        });
    }

    // === Auto-hide alerts ===
    setTimeout(function(){ $('.alert').fadeOut('slow'); }, 5000);

});
</script>