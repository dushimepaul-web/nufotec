<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================================
   PAGE : VISION, MISSION ET OBJECTIFS (statique)
   NUFOTEC-PHYTOMED INDUSTRIES
   ============================================================ */
.vmm-page {
    font-family: 'Poppins', sans-serif;
    background: #fff;
    color: #33403A;
    overflow-x: hidden;
}
.vmm-page * { font-family: 'Poppins', sans-serif; }

/* En-tête de page */
.vmm-page-head {
    position: relative;
    background: linear-gradient(135deg, #083D2A, #0B5D3B);
    padding: 84px 0 64px;
    text-align: center;
    overflow: hidden;
}
.vmm-page-head::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -60px;
    width: 340px; height: 340px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212,160,23,.18) 0%, transparent 70%);
}
.vmm-page-head .vmm-crumb {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.85); font-size: .8rem; font-weight: 500;
    padding: 7px 16px; border-radius: 50px; margin-bottom: 22px;
}
.vmm-page-head .vmm-crumb a { color: rgba(255,255,255,.85); text-decoration: none; }
.vmm-page-head .vmm-crumb a:hover { color: #D4A017; }
.vmm-page-title {
    color: #fff;
    font-size: clamp(1.8rem, 4vw, 2.9rem);
    font-weight: 800;
    line-height: 1.25;
    margin: 0;
    position: relative;
    z-index: 1;
}
.vmm-page-title span { color: #D4A017; }

/* Contenu */
.vmm-content { max-width: 1180px; margin: 0 auto; padding: 72px 24px 30px; }
.vmm-content p { font-size: 1.03rem; line-height: 1.95; color: #33403A; margin-bottom: 22px; text-align: justify; }
.vmm-content p strong { color: #0B5D3B; }

/* Cartes Vision / Mission */
.vmm-card {
    position: relative;
    background: #fff;
    border: 1px solid #E3EBE6;
    border-radius: 24px;
    box-shadow: 0 14px 36px rgba(8,61,42,.08);
    padding: 44px 40px;
    height: 100%;
    overflow: hidden;
    transition: all .35s ease;
}
.vmm-card:hover { transform: translateY(-8px); box-shadow: 0 24px 52px rgba(11,93,59,.16); border-color: #BBD8C6; }
.vmm-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, #D4A017, #0B5D3B);
}
.vmm-card-icon {
    width: 76px; height: 76px; border-radius: 22px;
    background: linear-gradient(135deg, #EAF6EF, #D8EFE3);
    color: #0B5D3B; font-size: 2.1rem;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 28px;
}
.vmm-card-label {
    display: inline-flex; align-items: center; gap: 10px;
    background: #EAF6EF; border: 1px solid #BBD8C6; color: #0B5D3B;
    font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 8px 22px; border-radius: 50px; margin-bottom: 18px;
}
.vmm-card-title { font-size: 1.55rem; font-weight: 800; color: #083D2A; margin-bottom: 20px; }

/* Positionnement */
.vmm-pos {
    position: relative;
    background: linear-gradient(135deg, #F1F7F3, #F8FAF8);
    border: 1px solid #DCE8E0;
    border-radius: 24px;
    padding: 48px 44px;
    margin-top: 30px;
}
.vmm-pos-icon {
    width: 64px; height: 64px; border-radius: 18px;
    background: linear-gradient(135deg, #0B5D3B, #0E7A4E);
    color: #fff; font-size: 1.7rem;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 24px;
}

/* CTA */
.vmm-cta-block { padding: 96px 0 40px; }
.vmm-cta-head {
    position: relative;
    background: linear-gradient(135deg, #083D2A, #0B5D3B);
    border-radius: 28px;
    padding: 64px 40px;
    text-align: center;
    overflow: hidden;
}
.vmm-cta-head::before {
    content: ''; position: absolute; top: -70%; right: -20%; width: 520px; height: 520px;
    background: radial-gradient(circle, rgba(212,160,23,.20) 0%, transparent 65%);
}
.vmm-cta-head .vmm-cta-tag {
    display: inline-flex; align-items: center; gap: 10px;
    background: #D4A017; color: #083D2A;
    font-size: .78rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    padding: 9px 22px; border-radius: 50px; margin-bottom: 22px;
    position: relative; z-index: 1;
}
.vmm-cta-head h2 { color: #fff; font-size: clamp(1.7rem, 3.2vw, 2.6rem); font-weight: 800; margin-bottom: 14px; position: relative; z-index: 1; }
.vmm-cta-head p { color: rgba(255,255,255,.85); font-size: 1.1rem; margin: 0; position: relative; z-index: 1; }

.vmm-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 28px; }
.vmm-action {
    position: relative;
    background: #fff;
    border: 1px solid #E3EBE6;
    border-radius: 22px;
    padding: 36px 28px;
    text-align: center;
    box-shadow: 0 10px 28px rgba(8,61,42,.07);
    transition: all .3s ease;
    display: flex; flex-direction: column;
}
.vmm-action:hover { transform: translateY(-8px); box-shadow: 0 22px 46px rgba(11,93,59,.14); border-color: #BBD8C6; }
.vmm-action-icon {
    width: 66px; height: 66px; border-radius: 20px;
    background: linear-gradient(135deg, #EAF6EF, #D8EFE3);
    color: #0B5D3B; font-size: 1.8rem;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 22px;
}
.vmm-action h3 { font-size: 1.15rem; font-weight: 800; color: #083D2A; margin-bottom: 6px; }
.vmm-action .vmm-action-sub { font-size: .85rem; font-weight: 600; color: #D4A017; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
.vmm-action p { font-size: .92rem; color: #5C6B64; line-height: 1.65; margin-bottom: 24px; text-align: center; flex: 1; }
.vmm-action .vmm-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    background: #0B5D3B; color: #fff;
    font-size: .92rem; font-weight: 600;
    padding: 13px 22px; border-radius: 50px;
    text-decoration: none; transition: all .3s ease;
}
.vmm-action .vmm-btn:hover { background: #D4A017; color: #083D2A; transform: translateY(-2px); }

@media (max-width: 991.98px) {
    .vmm-actions { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 575.98px) {
    .vmm-page-head { padding: 64px 0 48px; }
    .vmm-content { padding: 52px 18px 20px; }
    .vmm-card { padding: 34px 26px; }
    .vmm-pos { padding: 34px 26px; }
    .vmm-cta-head { padding: 48px 24px; }
    .vmm-actions { grid-template-columns: 1fr; }
}
</style>

<main class="vmm-page">

    <!-- En-tête de page -->
    <div class="vmm-page-head">
        <div class="vmm-crumb">
            <i class="bi bi-house-door"></i>
            <a href="<?= base_url() ?>">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Corporate</span>
            <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
            <span>Vision &amp; Mission</span>
        </div>
        <h1 class="vmm-page-title">Vision, mission et <span>objectifs</span></h1>
    </div>

    <div class="vmm-content">

        <!-- ══════════════ VISION / MISSION ══════════════ -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="vmm-card">
                    <div class="vmm-card-icon"><i class="bi bi-eye"></i></div>
                    <span class="vmm-card-label">Vision</span>
                    <h2 class="vmm-card-title">Déclaration de vision stratégique</h2>
                    <p>
                        Devenir un modèle de référence basé au Burundi pour l'agriculture biologique à grande échelle
                        sans intrants chimiques, dédiée aux cultures biologiques et aux plantes médicinales, ainsi
                        qu'une plateforme industrielle de fabrication de MTCA (Médecines Traditionnelles,
                        Complémentaires et Alternatives) innovantes et standardisées, de nutraceutiques/compléments
                        alimentaires, de phytomédicaments et de phytopharmaceutiques soumis à des essais précliniques
                        et cliniques via notre laboratoire de recherche scientifique et notre unité d'élevage
                        d'animaux de laboratoire.
                    </p>
                    <p>
                        L'entreprise produira également des engrais organiques riches en nutriments, des produits
                        alimentaires fortifiés clean-label et des boissons à base de fruits — reconnue à travers le
                        continent africain et à l'échelle mondiale pour la qualité, la sécurité, la durabilité et sa
                        contribution à la transformation socioéconomique durable.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="vmm-card">
                    <div class="vmm-card-icon"><i class="bi bi-bullseye"></i></div>
                    <span class="vmm-card-label">Mission</span>
                    <h2 class="vmm-card-title">Déclaration de mission</h2>
                    <p>
                        Pratiquer une agriculture biologique commerciale à grande échelle de plantes médicinales clés,
                        de cultures fonctionnelles, de fruits et de légumes sur <strong>plus de 2 000 hectares</strong>,
                        et transformer les récoltes en MTCA conformes aux Bonnes Pratiques de Fabrication (BPF) et aux
                        normes ISO, en nutraceutiques/compléments alimentaires, en phytomédicaments et
                        phytopharmaceutiques soumis à des essais précliniques et cliniques via notre laboratoire de
                        recherche scientifique et notre unité d'élevage d'animaux de laboratoire, ainsi qu'en produits
                        alimentaires fortifiés clean-label, boissons à base de fruits et engrais organiques riches en
                        nutriments.
                    </p>
                </div>
            </div>
        </div>

        <!-- ══════════════ POSITIONNEMENT STRATÉGIQUE ══════════════ -->
        <div class="vmm-pos">
            <div class="vmm-pos-icon"><i class="bi bi-compass"></i></div>
            <span class="vmm-card-label">Autre</span>
            <h2 class="vmm-card-title" style="font-size:1.4rem;">Positionnement stratégique</h2>
            <p style="margin-bottom:0;">
                NUFOTEC-PHYTOMED INDUSTRIES se positionne comme une entreprise agro-industrielle intégrée
                verticalement, axée sur les cultures médicinales et fonctionnelles à forte valeur ajoutée. Grâce à
                des partenariats structurés, à la création de valeur et à une transformation orientée vers
                l'exportation, l'entreprise convertit l'agriculture biologique commerciale en une production
                industrielle évolutive (MTCA, nutraceutiques/compléments alimentaires, phytomédicaments et
                phytopharmaceutiques soumis à des essais précliniques et cliniques via notre laboratoire de recherche
                scientifique et notre unité d'élevage d'animaux de laboratoire, ainsi que des produits alimentaires
                fortifiés clean-label et des boissons à base de fruits).
            </p>
        </div>

    </div>

    <!-- ══════════════ CTA : TAKE ACTION NOW ══════════════ -->
    <div class="vmm-cta-block">
        <div class="container">
            <div class="vmm-cta-head">
                <div class="vmm-cta-tag"><i class="bi bi-lightning-charge"></i> Take Action Now</div>
                <h2>Our Calls to Action</h2>
                <p>Rejoignez la vision : investissez, devenez courtier, achetez ou prenez rendez-vous.</p>
            </div>

            <div class="vmm-actions">
                <div class="vmm-action">
                    <div class="vmm-action-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <h3>Investisseurs</h3>
                    <div class="vmm-action-sub">Investisseurs et donateurs</div>
                    <p>Soumettez votre manifestation d'intérêt</p>
                    <a href="<?= base_url('investor') ?>" class="vmm-btn">Postuler <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="vmm-action">
                    <div class="vmm-action-icon"><i class="bi bi-briefcase"></i></div>
                    <h3>Courtiers</h3>
                    <div class="vmm-action-sub">Courtiers internationaux</div>
                    <p>Postulez pour un accord de courtage stratégique</p>
                    <a href="<?= base_url('broker') ?>" class="vmm-btn">Postuler maintenant <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="vmm-action">
                    <div class="vmm-action-icon"><i class="bi bi-bag"></i></div>
                    <h3>Acheteurs</h3>
                    <div class="vmm-action-sub">Acheteurs</div>
                    <p>Demander le catalogue de produits (conforme GMP/ISO)</p>
                    <a href="<?= base_url('Products') ?>" class="vmm-btn">Visiter la boutique <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="vmm-action">
                    <div class="vmm-action-icon"><i class="bi bi-heart-pulse"></i></div>
                    <h3>Patients</h3>
                    <div class="vmm-action-sub">Patients</div>
                    <p>Réserver une consultation en télésanté</p>
                    <a href="<?= base_url('Home/Contact') ?>" class="vmm-btn">Prendre rendez-vous <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

</main>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
