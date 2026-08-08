</div>
    <!--end page-wrapper (legacy) -->

    </div>
    <!--end app-content-->
</main>
<!--end app-main-->

<!--begin::Footer-->
<footer class="app-footer">
    <div class="d-flex justify-content-center align-items-center py-2">
        <p class="mb-0">Copyright © <?= date('Y') ?>. All right reserved.</p>
    </div>
</footer>
<!--end::Footer-->

</div>
<!--end app-wrapper-->

<script>
// Injection automatique du jeton CSRF dans toutes les requêtes fetch POST
(function() {
    'use strict';
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';
    if (!CSRF_HASH || typeof window.fetch !== 'function' || window.__csrfFetchPatched) return;
    window.__csrfFetchPatched = true;

    var origFetch = window.fetch;
    window.fetch = function(url, options) {
        options = options || {};
        var method = (options.method || 'GET').toUpperCase();
        if (method === 'POST') {
            if (!options.headers) options.headers = {};
            if (typeof options.headers.set === 'function' && !options.headers.has('X-CSRF-TOKEN')) {
                options.headers.set('X-CSRF-TOKEN', CSRF_HASH);
            } else if (typeof options.headers === 'object') {
                options.headers['X-CSRF-TOKEN'] = CSRF_HASH;
            }
            if (typeof options.body === 'string') {
                try {
                    var parsed = JSON.parse(options.body);
                    if (parsed && typeof parsed === 'object' && Array.isArray(parsed) === false && parsed[CSRF_NAME] === undefined) {
                        parsed[CSRF_NAME] = CSRF_HASH;
                        options.body = JSON.stringify(parsed);
                    }
                } catch (e) { /* body non-JSON : laissé tel quel */ }
            }
        }
        return origFetch.call(this, url, options);
    };

    // Injection automatique du jeton CSRF dans toutes les requêtes AJAX POST
    if (window.jQuery && jQuery.ajaxSetup) {
        jQuery.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if ((settings.type || 'GET').toUpperCase() !== 'POST') return;
                if (!CSRF_HASH) return;
                if (xhr && xhr.setRequestHeader) xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_HASH);
                var d = settings.data;
                if (d instanceof FormData) {
                    if (!d.has(CSRF_NAME)) d.append(CSRF_NAME, CSRF_HASH);
                } else if (d && typeof d === 'object') {
                    d[CSRF_NAME] = CSRF_HASH;
                } else if (typeof d === 'string') {
                    settings.data = (d.length ? d + '&' : '') + encodeURIComponent(CSRF_NAME) + '=' + encodeURIComponent(CSRF_HASH);
                } else {
                    settings.data = encodeURIComponent(CSRF_NAME) + '=' + encodeURIComponent(CSRF_HASH);
                }
            }
        });
    }

    // Injection du jeton CSRF dans les formulaires POST soumis en navigation native
    if (window.jQuery) {
        jQuery(document).on('submit', 'form[method="POST"], form[method="post"]', function() {
            var $form = jQuery(this);
            if (!$form.find('input[name="' + CSRF_NAME + '"]').length) {
                var $input = jQuery('<input type="hidden">').attr('name', CSRF_NAME).val(CSRF_HASH);
                $form.append($input);
            }
        });
    }
})();
</script>

<!-- Bootstrap JS -->
<script src="<?= base_url() ?>assets/backend/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE 4 JS -->
<script src="<?= base_url() ?>assets/backend/adminlte/js/adminlte.min.js"></script>

<!-- Plugins -->
<script src="<?= base_url() ?>assets/backend/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/backend/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

<!-- Quill Editor (local) -->
<script src="<?= base_url() ?>assets/backend/cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Custom Scripts -->
<script>
$(document).ready(function() {
    // Initialiser DataTable pour #example
    if ($('#example').length) {
        $('#example').DataTable({
            language: { url: '<?= base_url() ?>assets/backend/plugins/datatable/js/fr-FR.json' }
        });
    }
    
    // Initialiser DataTable pour #example2 avec boutons
    if ($('#example2').length) {
        var table = $('#example2').DataTable({
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print'],
            language: { url: '<?= base_url() ?>assets/backend/plugins/datatable/js/fr-FR.json' }
        });
        
        table.buttons().container()
            .appendTo('#example2_wrapper .col-md-6:eq(0)');
    }
    
    // Dark mode (AdminLTE 4 / data-bs-theme)
    $('#darkModeToggle').on('click', function() {
        var root = document.documentElement;
        var current = root.getAttribute('data-bs-theme') || 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-bs-theme', next);
        localStorage.setItem('adminlte-theme', next);
        var icon = $(this).find('i');
        icon.toggleClass('bi-moon-stars-fill bi-sun-fill');
    });
    
    // Restaurer le dark mode sauvegardé
    var savedTheme = localStorage.getItem('adminlte-theme');
    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        $('#darkModeToggle').find('i').toggleClass('bi-moon-stars-fill bi-sun-fill');
    }
    
    // Initialiser Quill Editor
    if ($('#editor').length && typeof Quill !== 'undefined') {
        new Quill('#editor', {
            theme: 'snow'
        });
    }
});
</script>

</body>
</html>