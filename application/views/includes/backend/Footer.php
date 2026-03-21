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
    
    <footer class="page-footer">
        <p class="mb-0">Copyright © <?= date('Y') ?>. All right reserved.</p>
    </footer>
</div>
<!--end wrapper-->

<!-- Start switcher -->
<button class="btn btn-primary position-fixed bottom-0 end-0 m-3 d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop">
    <i class='bx bx-cog bx-spin'></i>Customize
</button>

<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="staticBackdrop">
    <div class="offcanvas-header border-bottom h-60">
        <div>
            <h5 class="mb-0 text-uppercase">Theme Customizer</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div>
            <p>Theme variation</p>
            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <input type="radio" class="btn-check" name="theme-options" id="LightTheme" checked>
                    <label class="btn btn-outline-secondary d-flex flex-column gap-2 align-items-center justify-content-center p-3" for="LightTheme">
                        <span><i class='bx bx-sun fs-2'></i></span>
                        <span>Light</span>
                    </label>
                </div>
                <div class="col-12 col-xl-6">
                    <input type="radio" class="btn-check" name="theme-options" id="DarkTheme">
                    <label class="btn btn-outline-secondary d-flex flex-column gap-2 align-items-center justify-content-center p-3" for="DarkTheme">
                        <span><i class='bx bx-moon fs-2'></i></span>
                        <span>Dark</span>
                    </label>
                </div>
                <div class="col-12 col-xl-6">
                    <input type="radio" class="btn-check" name="theme-options" id="SemiDarkTheme">
                    <label class="btn btn-outline-secondary d-flex flex-column gap-2 align-items-center justify-content-center p-3" for="SemiDarkTheme">
                        <span><i class='bx bx-brightness-half fs-2'></i></span>
                        <span>Semi Dark</span>
                    </label>
                </div>
                <div class="col-12 col-xl-6">
                    <input type="radio" class="btn-check" name="theme-options" id="BorderedTheme">
                    <label class="btn btn-outline-secondary d-flex flex-column gap-2 align-items-center justify-content-center p-3" for="BorderedTheme">
                        <span><i class='bx bx-border-all fs-2'></i></span>
                        <span>Bordered</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End switcher -->

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

<!-- CKEditor (si nécessaire) -->
<!-- <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script> -->

<!-- App JS -->
<script src="<?= base_url() ?>assets/backend/js/app.js"></script>
<script src="<?= base_url() ?>assets/backend/js/index.js"></script>

<!-- Custom Scripts -->
<script>
// Attendre que le DOM soit chargé
$(document).ready(function() {
    // Initialiser MetisMenu
    if ($("#menu").length) {
        $("#menu").metisMenu();
    }
    
    // Initialiser DataTable pour #example
    if ($('#example').length) {
        $('#example').DataTable();
    }
    
    // Initialiser DataTable pour #example2 avec boutons
    if ($('#example2').length) {
        var table = $('#example2').DataTable({
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print']
        });
        
        table.buttons().container()
            .appendTo('#example2_wrapper .col-md-6:eq(0)');
    }
    
    // Initialiser les graphiques Peity
    if ($(".data-attributes span").length) {
        $(".data-attributes span").peity("donut");
    }
    
    // Thème personnalisé
    $('input[name="theme-options"]').change(function() {
        var theme = $(this).attr('id');
        switch(theme) {
            case 'LightTheme':
                document.body.setAttribute('data-bs-theme', 'light');
                break;
            case 'DarkTheme':
                document.body.setAttribute('data-bs-theme', 'dark');
                break;
            case 'SemiDarkTheme':
                document.body.setAttribute('data-bs-theme', 'semi-dark');
                break;
            case 'BorderedTheme':
                document.body.setAttribute('data-bs-theme', 'bordered');
                break;
        }
        localStorage.setItem('selectedTheme', theme);
    });
    
    // Restaurer le thème sauvegardé
    var savedTheme = localStorage.getItem('selectedTheme');
    if (savedTheme) {
        $('#' + savedTheme).prop('checked', true).trigger('change');
    }
    
    // Bouton back-to-top
    $('.back-to-top').click(function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, 500);
    });
    
    // Initialiser Quill Editor
    if ($('#editor').length && typeof Quill !== 'undefined') {
        var quill = new Quill('#editor', {
            theme: 'snow'
        });
    }
    
    // Initialiser CKEditor (décommentez si nécessaire)
    /*
    if (typeof CKEDITOR !== 'undefined' && $('#editor').length) {
        CKEDITOR.replace('editor', {
            toolbarGroups: [
                { name: 'clipboard', groups: ['clipboard', 'undo'] },
                { name: 'editing', groups: ['find', 'selection', 'spellchecker'] },
                { name: 'links' },
                { name: 'insert' },
                { name: 'forms' },
                { name: 'tools' },
                { name: 'document', groups: ['mode', 'document', 'doctools'] },
                { name: 'others' },
                { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
                { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'] },
                { name: 'styles' },
                { name: 'colors' },
                { name: 'about' }
            ],
            extraPlugins: 'colorbutton,justify,indentblock,indentlist,find,pagebreak,font,iframe,showblocks,preview',
            removeButtons: 'Source,Save,NewPage,Preview,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Language,Anchor,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,About',
            language: 'en'
        });
    }
    */
});
</script>



</body>
</html>