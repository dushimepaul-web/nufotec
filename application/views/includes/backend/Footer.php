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

<!-- ============================================ -->
<!-- ÉDITEURS TEXTE - VERSION SANS CLÉ API       -->
<!-- ============================================ -->

<!-- TinyMCE (version Community - pas de clé nécessaire) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>

<!-- OU utiliser Summernote (plus simple et sans clé) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>

<!-- App JS -->
<script src="<?= base_url() ?>assets/backend/js/app.js"></script>
<!-- <script src="<?= base_url() ?>assets/backend/js/index.js"></script> --> <!-- Commenté si erreurs ApexCharts -->

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {

    // === DataTables sécurisées avec fallback local ===
    ['table','example'].forEach(function(id){
        if (document.getElementById(id)) {
            $('#' + id).DataTable({
                language: { 
                    // Fallback: si le CDN échoue, utiliser traduction locale
                    url: '<?= base_url("assets/datatables/fr-FR.json") ?>'
                },
                pageLength: 25,
                responsive: true
            });
        }
    });

    // === ApexCharts avec vérification d'existence ===
    if (document.querySelector("#chart1")) {
        try {
            var chart1 = new ApexCharts(document.querySelector("#chart1"), {
                chart: { type: 'line' },
                series: [{ name: 'Exemple', data: [10,20,30,40] }],
                xaxis: { categories: ['Jan','Feb','Mar','Apr'] }
            });
            chart1.render();
        } catch(e) {
            console.log('⚠️ Graphique non initialisé:', e);
        }
    }

    // === Summernote (recommandé - pas de clé API) ===
    if ($('#summernote').length) {
        $('#summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }

    // === TinyMCE sans clé API (version Community) ===
    if (document.querySelector("#mytextarea")) {
        tinymce.init({
            selector: '#mytextarea',
            // PAS de apiKey nécessaire avec version CDN
            plugins: 'lists link image preview code',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | link image | code preview',
            height: 300,
            menubar: false,
            branding: false, // Enlever le branding Tiny
            promotion: false // Enlever la promotion IA
        });
    }

    // === Alternative à TinyMCE - CKEditor (pas de clé) ===
    if (document.querySelector("#ckeditor")) {
        // Décommentez si besoin
        // ClassicEditor.create(document.querySelector('#ckeditor')).catch(console.error);
    }

    // === Auto-hide alerts ===
    setTimeout(function(){ $('.alert').fadeOut('slow'); }, 5000);

    // === Vérifier les éléments manquants ===
    console.log('✅ Scripts chargés avec succès');
    

});
</script>

<!-- ============================================ -->
<!-- CHARGEMENT DES FICHIERS DE TRADUCTION LOCAUX -->
<!-- ============================================ -->
<!-- Téléchargez et placez ce fichier dans assets/datatables/fr-FR.json
     wget https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json -O assets/datatables/fr-FR.json
-->