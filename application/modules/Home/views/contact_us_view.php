<?php include VIEWPATH.'includes/frontend/Header.php';
$site_phone   = $this->Model->get_setting('site_phone', '+257 79 666 439');
$site_email   = $this->Model->get_setting('contact_email_invest', 'nufotecburundi2026@gmail.com');
$site_address = $this->Model->get_setting('adresse_siege', 'Bujumbura, République du Burundi');
$site_hours   = $this->Model->get_setting('horaires_travail', 'Dimanche - Vendredi: 8h00 - 17h00');
$map_query    = urlencode($site_address);
?>

<style>
    .cnt-wrap{background:#fff}
    /* ===== HERO ===== */
    .cnt-hero{position:relative;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;padding:54px 0 74px;overflow:hidden}
    .cnt-hero::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;border-radius:50%;background:rgba(212,160,23,.14);filter:blur(60px)}
    .cnt-hero .breadcrumb-ribbon{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:.82rem;letter-spacing:.4px;color:rgba(255,255,255,.72);margin-bottom:14px}
    .cnt-hero .breadcrumb-ribbon a{color:rgba(255,255,255,.82);text-decoration:none;transition:.2s}
    .cnt-hero .breadcrumb-ribbon a:hover{color:#D4A017}
    .cnt-hero .breadcrumb-ribbon i{font-size:.6rem;color:#D4A017}
    .cnt-hero h1{position:relative;z-index:2;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.4vw,2.5rem);line-height:1.25;margin:0 0 10px}
    .cnt-hero h1 .cnt-brace{color:#D4A017}
    .cnt-hero .cnt-lead{position:relative;z-index:2;font-size:.95rem;line-height:1.8;color:rgba(255,255,255,.85);max-width:720px;margin:0}
    .cnt-goldbar{width:74px;height:4px;background:#D4A017;border-radius:2px;margin-top:16px;position:relative;z-index:2}
    /* ===== CORPS ===== */
    .cnt-body{padding:52px 0 70px}
    .cnt-grid{display:grid;grid-template-columns:5fr 7fr;gap:26px;max-width:1180px;margin:0 auto;align-items:stretch}
    /* -------- Colonne infos (verte) -------- */
    .cnt-info{background:linear-gradient(160deg,#083D2A,#0B5D3B);border-radius:18px;padding:30px 26px;color:#fff;display:flex;flex-direction:column;position:relative;overflow:hidden;box-shadow:0 18px 40px rgba(8,61,42,.28)}
    .cnt-info::before{content:"";position:absolute;top:-70px;right:-70px;width:240px;height:240px;border-radius:50%;background:rgba(212,160,23,.12);filter:blur(50px)}
    .cnt-info .cnt-brand{position:relative;z-index:2;display:flex;align-items:center;gap:12px;margin:0 0 8px}
    .cnt-info .cnt-brand i{width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(212,160,23,.18);color:#D4A017;font-size:1.15rem;border-radius:12px}
    .cnt-info .cnt-brand h2{margin:0;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.25rem;color:#fff;line-height:1.3}
    .cnt-info .cnt-addr{position:relative;z-index:2;font-size:.88rem;color:rgba(255,255,255,.75);margin:0 0 18px;padding-left:56px}
    .cnt-map{position:relative;z-index:2;border-radius:14px;overflow:hidden;border:1px solid rgba(255,255,255,.2);margin:0 0 18px;height:210px;box-shadow:0 12px 30px rgba(0,0,0,.25)}
    .cnt-map iframe{width:100%;height:100%;border:0;display:block}
    .cnt-map .cnt-map-btn{position:absolute;right:10px;bottom:10px;z-index:3;background:#D4A017;color:#083D2A;border:none;font-size:.75rem;font-weight:700;padding:8px 14px;border-radius:50px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 6px 16px rgba(0,0,0,.3);transition:.2s}
    .cnt-map .cnt-map-btn:hover{transform:translateY(-2px);background:#e0b032}
    .cnt-cards{position:relative;z-index:2;display:flex;flex-direction:column;gap:12px;margin-top:auto}
    .cnt-card{display:flex;align-items:center;gap:14px;background:rgba(255,255,255,.09);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.16);border-radius:13px;padding:14px 16px;cursor:pointer;transition:.22s;text-decoration:none}
    .cnt-card:hover{background:rgba(255,255,255,.17);transform:translateX(5px)}
    .cnt-card .cnt-ico{flex:0 0 auto;width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(212,160,23,.16);color:#D4A017;font-size:1.05rem;border-radius:12px}
    .cnt-card .cnt-txt{min-width:0}
    .cnt-card .cnt-txt h4{margin:0;font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.65);font-weight:600}
    .cnt-card .cnt-txt p{margin:2px 0 0;color:#fff;font-weight:600;font-size:.92rem;line-height:1.4;word-break:break-word}
    .cnt-card .cnt-txt small{color:rgba(255,255,255,.6);font-size:.72rem}
    .cnt-badge{display:inline-flex;align-items:center;gap:7px;margin-top:6px;background:rgba(39,174,96,.22);border:1px solid rgba(39,174,96,.45);color:#8ee6ae;font-size:.72rem;font-weight:700;padding:5px 12px;border-radius:50px}
    .cnt-badge.closed{background:rgba(231,76,60,.22);border-color:rgba(231,76,60,.45);color:#f5a3a3}
    /* -------- Colonne formulaire (blanche) -------- */
    .cnt-form-col{background:#fff;border:1px solid #E3EDE6;border-radius:18px;padding:32px 30px;box-shadow:0 14px 36px rgba(8,61,42,.08)}
    .cnt-form-head{margin:0 0 24px;text-align:center}
    .cnt-form-head h2{margin:0 0 6px;font-family:'Poppins',sans-serif;font-weight:700;font-size:clamp(1.35rem,2.2vw,1.7rem);color:#083D2A}
    .cnt-form-head h2 span{color:#D4A017}
    .cnt-form-head p{margin:0;color:#5b6f62;font-size:.9rem}
    .cnt-field{position:relative;margin-bottom:18px}
    .cnt-field .cnt-ico{position:absolute;left:15px;top:17px;color:#0B5D3B;font-size:.95rem;z-index:5;transition:.2s}
    .cnt-field input.form-control,.cnt-field textarea.form-control{width:100%;border:2px solid #E3EDE6;border-radius:12px;padding:.85rem 1rem .85rem 2.8rem;font-size:.92rem;color:#1f2f26;background:#FAFCFB;transition:.2s}
    .cnt-field textarea.form-control{padding-top:.85rem;min-height:130px;resize:none}
    .cnt-field input.form-control:focus,.cnt-field textarea.form-control:focus{border-color:#0B5D3B;background:#fff;box-shadow:0 0 0 4px rgba(11,93,59,.1);outline:none}
    .cnt-field input.form-control:focus ~ .cnt-ico,.cnt-field textarea.form-control:focus ~ .cnt-ico{color:#D4A017}
    .cnt-field input.form-control.is-invalid,.cnt-field textarea.form-control.is-invalid{border-color:#E74C3C;background:#FEF9F9}
    .cnt-field input.form-control.is-valid,.cnt-field textarea.form-control.is-valid{border-color:#27ae60}
    .cnt-count{position:absolute;right:12px;bottom:-17px;font-size:.68rem;color:#8a9a90;font-weight:600}
    .cnt-count.warning{color:#D4A017}
    .cnt-count.error{color:#E74C3C}
    .cnt-err{display:none;color:#E74C3C;font-size:.76rem;margin-top:5px;padding-left:2.8rem;font-weight:500}
    .cnt-field.is-invalid .cnt-err{display:block}
    .cnt-consent{display:flex;align-items:flex-start;gap:11px;background:linear-gradient(135deg,#F5F9F6,#EAF4EE);border:1px solid #C9DFD1;border-radius:12px;padding:14px 16px;margin:4px 0 18px;cursor:pointer;transition:.2s}
    .cnt-consent:hover{border-color:#0B5D3B}
    .cnt-consent input{width:19px;height:19px;accent-color:#0B5D3B;margin-top:1px;flex-shrink:0;cursor:pointer}
    .cnt-consent label{margin:0;font-size:.82rem;color:#3d4f45;line-height:1.5;cursor:pointer}
    .cnt-consent label a{color:#0B5D3B;font-weight:600;text-decoration:none}
    .cnt-consent.is-invalid{border-color:#E74C3C;background:#FEF7F7}
    .cnt-submit{width:100%;padding:15px;border:none;border-radius:12px;background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;font-family:'Poppins',sans-serif;font-weight:600;font-size:.98rem;display:flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;transition:.22s;box-shadow:0 12px 26px rgba(8,61,42,.28)}
    .cnt-submit:hover{transform:translateY(-2px);box-shadow:0 18px 34px rgba(8,61,42,.36)}
    .cnt-submit .spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:cntspin .8s linear infinite}
    .cnt-submit.loading{pointer-events:none;opacity:.8}
    .cnt-submit.loading .spinner{display:block}
    .cnt-submit.loading .cnt-btn-txt{display:none}
    @keyframes cntspin{to{transform:rotate(360deg)}}
    /* ===== TOASTS ===== */
    .cnt-toasts{position:fixed;top:18px;right:18px;z-index:9999;display:flex;flex-direction:column;gap:10px;max-width:calc(100vw - 36px)}
    .cnt-toast{background:#fff;border-radius:12px;padding:13px 16px;box-shadow:0 18px 40px rgba(0,0,0,.18);display:flex;align-items:center;gap:12px;min-width:300px;max-width:100%;border-left:5px solid;animation:cntin .35s cubic-bezier(.68,-.55,.265,1.55)}
    .cnt-toast.success{border-left-color:#27ae60}
    .cnt-toast.error{border-left-color:#E74C3C}
    .cnt-toast.warning{border-left-color:#D4A017}
    .cnt-toast.hiding{animation:cntout .35s ease forwards}
    .cnt-toast .cnt-toast-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
    .cnt-toast.success .cnt-toast-ico{background:#E8F7EE;color:#27ae60}
    .cnt-toast.error .cnt-toast-ico{background:#FDEEEE;color:#E74C3C}
    .cnt-toast.warning .cnt-toast-ico{background:#FBF3E0;color:#D4A017}
    .cnt-toast h4{margin:0;font-size:.9rem;font-weight:700;color:#1f2f26}
    .cnt-toast p{margin:0;font-size:.8rem;color:#5b6f62;line-height:1.4}
    .cnt-toast .cnt-toast-x{margin-left:auto;border:none;background:none;color:#9aa8a0;cursor:pointer;font-size:.9rem;padding:4px}
    @keyframes cntin{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
    @keyframes cntout{to{transform:translateX(120%);opacity:0}}
    /* ===== MODAL ===== */
    .cnt-modal .modal-content{border:none;border-radius:16px;overflow:hidden}
    .cnt-modal .modal-header{background:linear-gradient(135deg,#083D2A,#0B5D3B);color:#fff;border:none;padding:18px 22px}
    .cnt-modal .modal-header h5{font-family:'Poppins',sans-serif;font-weight:600;margin:0}
    .cnt-modal .modal-header .btn-close{filter:invert(1)}
    .cnt-modal .modal-body{padding:26px;color:#3d4f45;font-size:.9rem;line-height:1.7}
    .cnt-success-anim{width:84px;height:84px;background:#E8F7EE;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;position:relative;color:#27ae60;font-size:2.1rem}
    .cnt-success-anim::before{content:"";position:absolute;inset:0;border-radius:50%;background:#E8F7EE;animation:cntripple 1.5s ease-out infinite}
    @keyframes cntripple{to{transform:scale(1.55);opacity:0}}
    .cnt-modal .modal-body h3{font-family:'Poppins',sans-serif;color:#083D2A;font-weight:700}
    .cnt-modal .modal-footer{border:none;padding:0 26px 26px;justify-content:center}
    .cnt-btn-ok{background:#083D2A;color:#fff;border:none;padding:11px 34px;border-radius:50px;font-weight:600;transition:.2s}
    .cnt-btn-ok:hover{background:#0B5D3B;color:#fff}
    @media(max-width:992px){.cnt-grid{grid-template-columns:1fr}.cnt-info{order:2}}
    @media(max-width:576px){.cnt-body{padding:34px 0 50px}.cnt-form-col{padding:24px 18px}.cnt-toast{min-width:auto;width:100%}}
</style>

<!-- Toasts -->
<div id="cntToasts" class="cnt-toasts"></div>

<!-- ===== HERO ===== -->
<div class="cnt-hero">
    <div class="container">
        <div class="breadcrumb-ribbon">
            <a href="<?= base_url() ?>">Accueil</a>
            <i class="fa-solid fa-angle-right"></i>
            <span>Contact</span>
        </div>
        <h1>Contactez-nous <span class="cnt-brace">{</span></h1>
        <p class="cnt-lead">Une question, un projet, une suggestion ? Remplissez le formulaire ci-dessous — notre équipe vous répond sous 24 heures.</p>
        <div class="cnt-goldbar"></div>
    </div>
</div>

<!-- ===== CORPS ===== -->
<section class="cnt-body">
    <div class="container">
        <div class="cnt-grid">

            <!-- Colonne infos (dynamique via settings) -->
            <aside class="cnt-info">
                <div class="cnt-brand">
                    <i class="fa-solid fa-leaf"></i>
                    <h2><?= htmlspecialchars($this->Model->get_setting('site_name', 'NUFOTEC BURUNDI')) ?></h2>
                </div>
                <p class="cnt-addr"><i class="fa-solid fa-location-dot me-1" style="color:#D4A017"></i> <?= htmlspecialchars($site_address) ?></p>

                <div class="cnt-map">
                    <iframe src="https://www.google.com/maps?q=<?= $map_query ?>&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Localisation <?= htmlspecialchars($site_address) ?>"></iframe>
                    <a class="cnt-map-btn" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= $map_query ?>">
                        <i class="fa-solid fa-directions"></i> Itinéraire
                    </a>
                </div>

                <div class="cnt-cards">
                    <div class="cnt-card" onclick="cntCopy('<?= htmlspecialchars($site_phone, ENT_QUOTES) ?>')" role="button" tabindex="0">
                        <div class="cnt-ico"><i class="fa-solid fa-phone"></i></div>
                        <div class="cnt-txt">
                            <h4>Téléphone</h4>
                            <p><?= htmlspecialchars($site_phone) ?></p>
                            <small>Cliquer pour copier</small>
                        </div>
                    </div>
                    <a class="cnt-card" href="mailto:<?= htmlspecialchars($site_email, ENT_QUOTES) ?>">
                        <div class="cnt-ico"><i class="fa-solid fa-envelope"></i></div>
                        <div class="cnt-txt">
                            <h4>Email</h4>
                            <p><?= htmlspecialchars($site_email) ?></p>
                            <small>Cliquer pour envoyer un email</small>
                        </div>
                    </a>
                    <div class="cnt-card" role="tabindex">
                        <div class="cnt-ico"><i class="fa-solid fa-clock"></i></div>
                        <div class="cnt-txt">
                            <h4>Horaires</h4>
                            <p><?= htmlspecialchars($site_hours) ?></p>
                            <span class="cnt-badge" id="cntStatus"><i class="fa-solid fa-circle"></i> <span id="cntStatusTxt">Vérification…</span></span>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Colonne formulaire -->
            <div class="cnt-form-col">
                <div class="cnt-form-head">
                    <h2>Contact <span>{</span> us</h2>
                    <p>Remplissez le formulaire ci-dessous et nous vous répondrons sous 24 heures.</p>
                </div>

                <form id="cntForm" novalidate>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>" id="cntCsrf">

                    <div class="cnt-field" id="fld-fullname">
                        <i class="fa-solid fa-user cnt-ico"></i>
                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="John Doe" required maxlength="250" autocomplete="name">
                        <span class="cnt-count" id="fullnameCount">0/250</span>
                        <div class="cnt-err">Veuillez entrer votre nom complet (min. 2 caractères).</div>
                    </div>

                    <div class="cnt-field" id="fld-email">
                        <i class="fa-solid fa-envelope cnt-ico"></i>
                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required maxlength="250" autocomplete="email">
                        <div class="cnt-err">Veuillez entrer une adresse email valide.</div>
                    </div>

                    <div class="cnt-field" id="fld-phone">
                        <i class="fa-solid fa-phone cnt-ico"></i>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+257 79 666 439" required maxlength="12" autocomplete="tel">
                        <span class="cnt-count" id="phoneCount">0/12</span>
                        <div class="cnt-err">Format invalide (max. 12 caractères, chiffres/espaces/+/-).</div>
                    </div>

                    <div class="cnt-field" id="fld-location">
                        <i class="fa-solid fa-location-dot cnt-ico"></i>
                        <input type="text" class="form-control" id="location" name="location" placeholder="Bujumbura, Burundi" maxlength="200" autocomplete="address-level1">
                        <span class="cnt-count" id="locationCount">0/200</span>
                    </div>

                    <div class="cnt-field" id="fld-subject">
                        <i class="fa-solid fa-tag cnt-ico"></i>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Objet de votre message" required maxlength="250">
                        <span class="cnt-count" id="subjectCount">0/250</span>
                        <div class="cnt-err">L'objet doit contenir au moins 3 caractères.</div>
                    </div>

                    <div class="cnt-field" id="fld-message">
                        <i class="fa-solid fa-comment cnt-ico"></i>
                        <textarea class="form-control" id="message" name="message" placeholder="Votre message…" required></textarea>
                        <span class="cnt-count" id="messageCount">0 caractères</span>
                        <div class="cnt-err">Le message doit contenir au moins 10 caractères.</div>
                    </div>

                    <div class="cnt-consent" id="cntConsent">
                        <input type="checkbox" id="consent" name="consent">
                        <label for="consent">
                            J'accepte que mes données personnelles soient traitées conformément à la
                            <a href="#" data-bs-toggle="modal" data-bs-target="#cntPrivacy">politique de confidentialité</a> *
                        </label>
                    </div>

                    <button type="submit" class="cnt-submit" id="cntSubmit">
                        <span class="spinner"></span>
                        <span class="cnt-btn-txt"><i class="fa-solid fa-paper-plane me-2"></i>Envoyer le message</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- Modal Privacy -->
<div class="modal fade cnt-modal" id="cntPrivacy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Politique de confidentialité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p>Nous collectons vos données personnelles uniquement pour traiter votre demande de contact. Ces informations sont stockées en toute sécurité et ne sont jamais partagées avec des tiers sans votre consentement explicite.</p>
                <p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression de vos données. Contactez-nous à <strong><?= htmlspecialchars($site_email) ?></strong> pour exercer ces droits.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="cnt-btn-ok" data-bs-dismiss="modal">J'ai compris</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Success -->
<div class="modal fade cnt-modal" id="cntSuccess" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5><i class="fa-solid fa-circle-check me-2"></i>Message envoyé !</h5>
            </div>
            <div class="modal-body text-center">
                <div class="cnt-success-anim"><i class="fa-solid fa-paper-plane"></i></div>
                <h3>Merci <span id="cntSuccessName">!</span></h3>
                <p class="mb-0">Votre message a bien été reçu. Notre équipe vous répondra dans les plus brefs délais.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="cnt-btn-ok" onclick="cntCloseSuccess()">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== TOASTS =====
    function cntToast(type, title, message, duration) {
        duration = duration || 4000;
        const wrap = document.getElementById('cntToasts');
        if (!wrap) return;
        const t = document.createElement('div');
        t.className = 'cnt-toast ' + type;
        t.setAttribute('role', 'alert');
        const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation' };
        t.innerHTML = '<div class="cnt-toast-ico"><i class="fa-solid ' + (icons[type] || 'fa-circle-info') + '"></i></div>'
            + '<div><h4>' + title + '</h4><p>' + message + '</p></div>'
            + '<button class="cnt-toast-x" onclick="this.parentElement.remove()" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>';
        wrap.appendChild(t);
        setTimeout(function () {
            t.classList.add('hiding');
            setTimeout(function () { t.remove(); }, 350);
        }, duration);
    }

    // ===== COPIER =====
    function cntCopy(text) {
        function done() { cntToast('success', 'Copié !', 'Numéro copié dans le presse-papiers'); }
        function fail() { cntToast('error', 'Erreur', 'Impossible de copier'); }
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(done).catch(fail);
        } else {
            const ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); done(); } catch (e) { fail(); }
            document.body.removeChild(ta);
        }
    }

    // ===== HEURES D'OUVERTURE (Dimanche - Vendredi : 8h00 - 17h00) =====
    (function cntHours() {
        const badge = document.getElementById('cntStatus');
        const txt = document.getElementById('cntStatusTxt');
        if (!badge || !txt) return;
        const now = new Date();
        const day = now.getDay();      // 0 = dimanche … 6 = samedi
        const hour = now.getHours();
        const open = (day >= 0 && day <= 5) && (hour >= 8 && hour < 17);
        if (open) {
            badge.classList.remove('closed');
            badge.innerHTML = '<i class="fa-solid fa-circle"></i> <span>Ouvert actuellement</span>';
        } else {
            badge.classList.add('closed');
            badge.innerHTML = '<i class="fa-solid fa-circle"></i> <span>Fermé actuellement</span>';
        }
    })();

    // ===== VALIDATION =====
    const cntFields = {
        fullname: { min: 2, max: 250, required: true },
        email: { type: 'email', required: true },
        phone: { max: 12, pattern: /^[0-9+\-\s()]+$/, required: true },
        location: { max: 200 },
        subject: { min: 3, max: 250, required: true },
        message: { min: 10, required: true }
    };

    function cntValidate(id) {
        const el = document.getElementById(id);
        const box = document.getElementById('fld-' + id);
        if (!el || !box) return true;
        const v = el.value.trim();
        const r = cntFields[id];
        el.classList.remove('is-valid', 'is-invalid');
        box.classList.remove('is-invalid');
        let ok = true;
        if (r) {
            if (r.required && !v) ok = false;
            if (ok && r.min && v.length < r.min) ok = false;
            if (ok && r.max && v.length > r.max) ok = false;
            if (ok && r.pattern && !r.pattern.test(v)) ok = false;
            if (ok && r.type === 'email') ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
        }
        el.classList.add(ok ? 'is-valid' : 'is-invalid');
        if (!ok) box.classList.add('is-invalid');
        return ok;
    }

    // Compteurs
    ['fullname', 'phone', 'location', 'subject', 'message'].forEach(function (id) {
        const el = document.getElementById(id);
        const c = document.getElementById(id + 'Count');
        if (!el || !c) return;
        el.addEventListener('input', function () {
            const n = el.value.length;
            const max = el.getAttribute('maxlength');
            if (max) {
                c.textContent = n + '/' + max;
                c.classList.toggle('warning', n > max * 0.8);
                c.classList.toggle('error', n >= max);
            } else {
                c.textContent = n + ' caractères';
            }
            if (el.classList.contains('is-invalid')) cntValidate(id);
        });
        el.addEventListener('blur', function () { cntValidate(id); });
    });

    // ===== SOUMISSION AJAX =====
    document.getElementById('cntForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        let ok = true;
        Object.keys(cntFields).forEach(function (id) { if (!cntValidate(id)) ok = false; });

        const consent = document.getElementById('consent');
        const cWrap = document.getElementById('cntConsent');
        if (consent && !consent.checked) { cWrap.classList.add('is-invalid'); ok = false; }
        else if (cWrap) cWrap.classList.remove('is-invalid');

        if (!ok) {
            cntToast('error', 'Erreur', 'Veuillez corriger les erreurs du formulaire.');
            const first = document.querySelector('.is-invalid');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const btn = document.getElementById('cntSubmit');
        btn.classList.add('loading');
        btn.disabled = true;

        const data = {};
        ['fullname', 'email', 'phone', 'location', 'subject', 'message', 'consent'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) data[id] = el.value;
        });

        let result = null;
        try {
            const resp = await fetch('<?= base_url('Home/Contact/sendMessage') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(data)
            });
            try { result = await resp.json(); }
            catch (err) { throw new Error('Réponse invalide'); }

            if (result.success) {
                const n = document.getElementById('cntSuccessName');
                if (n) n.textContent = (data.fullname || 'Visiteur').split(' ')[0] + ' !';
                const m = document.getElementById('cntSuccess');
                if (m && window.bootstrap) new bootstrap.Modal(m).show();
                this.reset();
                document.querySelectorAll('.is-valid').forEach(function (el) { el.classList.remove('is-valid'); });
                const defs = { fullname: '0/250', phone: '0/12', location: '0/200', subject: '0/250', message: '0 caractères' };
                Object.keys(defs).forEach(function (id) {
                    const c = document.getElementById(id + 'Count');
                    if (c) c.textContent = defs[id];
                });
                cntToast('success', 'Message envoyé', "Nous vous répondrons dans les plus brefs délais.");
            } else {
                if (result.errors) {
                    Object.keys(result.errors).forEach(function (f) {
                        const el = document.getElementById(f);
                        const box = document.getElementById('fld-' + f);
                        if (el) { el.classList.add('is-invalid'); }
                        if (box) { box.classList.add('is-invalid'); const err = box.querySelector('.cnt-err'); if (err) err.textContent = result.errors[f]; }
                    });
                }
                cntToast('error', 'Erreur', result.message || 'Une erreur est survenue.');
            }
        } catch (err) {
            cntToast('error', 'Erreur réseau', 'Impossible de contacter le serveur. Veuillez réessayer.');
        } finally {
            btn.classList.remove('loading');
            btn.disabled = false;
            if (result && result.csrf_token) {
                const cs = document.getElementById('cntCsrf');
                if (cs) cs.value = result.csrf_token;
            }
        }
    });

    function cntCloseSuccess() {
        const m = document.getElementById('cntSuccess');
        if (!m) return;
        const modal = bootstrap.Modal.getInstance(m);
        if (modal) modal.hide(); else m.classList.remove('show');
    }
</script>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
