<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --accent: #d4af37;
    --accent-hover: #b8962e;
    --accent-light: #f4d03f;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --gray-light: #dee2e6;
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
    --shadow-glow: 0 0 30px rgba(212, 175, 55, 0.3);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    --header-height: 70px;
    --header-height-mobile: 60px;
    --bottom-nav-height: 64px;
}
</style>

<div class="product-detail-page">
    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>" class="text-decoration-none">Nos Produits</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page"><?= htmlspecialchars($product['title'] ?? 'Détail produit') ?></li>
            </ol>
        </nav>

        <?php if (!empty($product)): ?>
        <div class="row g-5">
            <!-- Colonne Image -->
            <div class="col-lg-6">
                <div class="product-detail-image-wrapper">
                    <div class="main-image-container">
                        <img src="<?= base_url('attachments/Products/'.$product['main_image']) ?>" 
                             id="mainProductImage"
                             class="img-fluid w-100"
                             style="cursor: zoom-in; max-height: 500px; object-fit: contain; background: var(--light);"
                             alt="<?= htmlspecialchars($product['title']) ?>">
                        
                        <div class="product-badge-detail">
                            <span class="badge" style="background: var(--accent); color: var(--primary-dark);">✨ Nouveau</span>
                        </div>
                        
                        <button class="btn zoom-detail-btn" id="zoomDetailBtn">
                            <i class="bx bx-search-alt"></i> Zoom
                        </button>
                    </div>
                </div>
            </div>

            <!-- Colonne Informations -->
            <div class="col-lg-6">
                <div class="product-detail-info">
                    <h1 class="product-detail-title mb-3">
                        <?= htmlspecialchars($product['title']) ?>
                    </h1>
                    
                    <div class="product-detail-price mb-4">
                        <span class="price-large">
                            <?= htmlspecialchars($product['price']) ?>
                        </span>
                    </div>
                    
                    <div class="product-detail-description mb-4">
                        <h5 class="fw-semibold mb-3" style="color: var(--primary);">Description</h5>
                        <p class="text-muted lh-lg">
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                        </p>
                    </div>
                    
                    <!-- Boutons sur la même ligne -->
                    <div class="product-cta mt-4">
                        <div class="d-flex gap-3">
                            <button class="btn btn-whatsapp flex-grow-1" id="openOrderModalBtn" 
        data-title="<?= htmlspecialchars($product['title']) ?>"
        data-price="<?= htmlspecialchars($product['price']) ?>"
        data-id="<?= $product['id'] ?>">
    <i class="bx bxl-whatsapp me-2"></i> Commander
</button>
                            <button class="btn btn-outline-share flex-grow-1 share-product-btn"
                                    data-title="<?= htmlspecialchars($product['title']) ?>"
                                    data-url="<?= base_url('product/'.($product['slug'] ?? $product['id'])) ?>"
                                    data-image="<?= base_url('attachments/Products/'.$product['main_image']) ?>">
                                <i class="bx bx-share-alt me-2"></i> Partager
                            </button>
                        </div>
                    </div>
                    
                    <div class="product-meta mt-4 pt-3" style="border-top: 1px solid var(--gray-light);">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Référence produit</small>
                                <strong style="color: var(--primary);">#<?= $product['id'] ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Date d'ajout</small>
                                <strong style="color: var(--primary);"><?= date('d/m/Y', strtotime($product['created_at'])) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($similar_products)): ?>
        <div class="similar-products mt-5 pt-5">
            <div class="section-header text-center mb-4">
                <h2 class="section-title">Produits <span style="color: var(--accent);">similaires</span></h2>
                <div class="title-border"></div>
            </div>
            <div class="row g-4">
                <?php foreach ($similar_products as $similar): ?>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="similar-product-card">
                        <div class="card-img-wrapper">
                            <img src="<?= base_url('attachments/Products/'.$similar['main_image']) ?>" 
                                 class="similar-card-img" 
                                 alt="<?= htmlspecialchars($similar['title']) ?>">
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($similar['title']) ?></h6>
                            <p class="card-price"><?= htmlspecialchars($similar['price']) ?></p>
                            <a href="<?= base_url('product/'.($similar['slug'] ?? $similar['id'])) ?>" class="btn-view">
                                Voir détails <i class="bx bx-right-arrow-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bx bx-package text-muted" style="font-size: 4rem;"></i>
            <h3 class="mt-3">Produit non trouvé</h3>
            <p class="text-muted">Le produit que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href="<?= base_url('products') ?>" class="btn btn-primary mt-3">
                <i class="bx bx-arrow-back me-2"></i>Retour aux produits
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL DE COLLECTE D'INFORMATIONS POUR COMMANDE -->
<div class="modal fade" id="orderInfoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: var(--primary); color: white; border: none;">
                <h5 class="modal-title">
                    <i class="bx bxl-whatsapp me-2"></i>Informations de livraison
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="order-product-info mb-4 p-3 bg-light rounded">
    <div class="d-flex gap-3 align-items-center">
        <img id="orderProductImage" src="" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
        <div>
            <h6 id="orderProductTitle" class="mb-1 fw-bold" style="color: var(--primary);"></h6>
            <span id="orderProductPrice" class="text-accent fw-bold"></span>
            <small id="orderProductRef" class="d-block text-muted"></small> <!-- AJOUTER CETTE LIGNE -->
        </div>
    </div>
</div>
                
                <form id="orderForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customerName" required placeholder="Entrez votre nom et prénom">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="customerPhone" required placeholder="Ex: 25779666439">
                        <small class="text-muted">Numéro WhatsApp pour vous contacter</small>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerCountry" required placeholder="Pays">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ville <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerCity" required placeholder="Ville">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Adresse de livraison <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="customerAddress" rows="2" required placeholder="Quartier, rue, numéro, point de repère..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Instructions supplémentaires</label>
                        <textarea class="form-control" id="customerNotes" rows="2" placeholder="Horaires de livraison, code d'accès, etc."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-whatsapp-send" id="sendWhatsAppOrderBtn">
                    <i class="bx bxl-whatsapp me-2"></i>Envoyer la commande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Zoom -->
<div class="modal fade" id="zoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomImage" src="" class="img-fluid" style="max-height: 85vh; cursor: crosshair;">
            </div>
        </div>
    </div>
</div>

<!-- Modal Partage -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header" style="background: var(--primary); color: white; border: none;">
                <h5 class="modal-title">
                    <i class="bx bx-share-alt me-2"></i>Partager ce produit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="share-product-info mb-4 text-center">
                    <img id="shareProductImage" src="" class="rounded mb-3" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid var(--accent);">
                    <h6 id="shareProductTitle" class="mb-2" style="color: var(--primary);"></h6>
                </div>
                
                <div class="share-buttons mb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="#" id="shareWhatsapp" class="btn btn-share whatsapp-share w-100" target="_blank">
                                <i class="bx bxl-whatsapp me-2"></i>WhatsApp
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareFacebook" class="btn btn-share facebook-share w-100" target="_blank">
                                <i class="bx bxl-facebook me-2"></i>Facebook
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareTwitter" class="btn btn-share twitter-share w-100" target="_blank">
                                <i class="bx bxl-twitter me-2"></i>Twitter/X
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareInstagram" class="btn btn-share instagram-share w-100" target="_blank">
                                <i class="bx bxl-instagram me-2"></i>Instagram
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareTiktok" class="btn btn-share tiktok-share w-100" target="_blank">
                                <i class="bx bxl-tiktok me-2"></i>TikTok
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareYoutube" class="btn btn-share youtube-share w-100" target="_blank">
                                <i class="bx bxl-youtube me-2"></i>YouTube
                            </a>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="share-link mt-3">
                    <label class="form-label fw-bold">Lien du produit</label>
                    <div class="input-group">
                        <input type="text" id="shareLink" class="form-control bg-light" readonly>
                        <button class="btn btn-copy copy-link-btn" type="button">
                            <i class="bx bx-copy"></i> Copier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Page Produit */
.product-detail-page {
    background: var(--light);
    min-height: 100vh;
}

/* Breadcrumb */
.breadcrumb-item a {
    color: var(--primary);
    transition: var(--transition);
}

.breadcrumb-item a:hover {
    color: var(--accent);
}

.text-accent {
    color: var(--accent) !important;
}

/* Image Container */
.product-detail-image-wrapper {
    position: relative;
}

.main-image-container {
    position: relative;
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

#mainProductImage {
    transition: transform 0.5s ease;
}

#mainProductImage:hover {
    transform: scale(1.02);
}

.product-badge-detail {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 2;
}

.product-badge-detail .badge {
    padding: 8px 20px;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 30px;
    box-shadow: var(--shadow);
}

.zoom-detail-btn {
    position: absolute;
    bottom: 20px;
    right: 20px;
    z-index: 2;
    background: rgba(255,255,255,0.95);
    border: none;
    border-radius: 50px;
    padding: 10px 20px;
    font-weight: 500;
    color: var(--primary);
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.zoom-detail-btn:hover {
    background: var(--accent);
    color: var(--primary-dark);
    transform: scale(1.05);
}

/* Titre Produit */
.product-detail-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary);
    position: relative;
    padding-bottom: 15px;
}

.product-detail-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 80px;
    height: 3px;
    background: var(--accent);
    border-radius: 3px;
}

/* Prix */
.price-large {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--accent);
    background: linear-gradient(135deg, var(--accent), var(--accent-light));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Bouton WhatsApp */
.btn-whatsapp {
    background: #25D366;
    border: none;
    color: white;
    border-radius: 50px;
    padding: 14px;
    font-weight: 600;
    font-size: 1rem;
    transition: var(--transition);
    box-shadow: var(--shadow);
}

.btn-whatsapp:hover {
    background: #128C7E;
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: white;
}

/* Bouton Partage */
.btn-outline-share {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: 50px;
    padding: 14px;
    font-weight: 600;
    font-size: 1rem;
    transition: var(--transition);
}

.btn-outline-share:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

/* Produits similaires */
.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.title-border {
    width: 80px;
    height: 3px;
    background: var(--accent);
    margin: 10px auto 0;
    border-radius: 3px;
}

.similar-product-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    transition: var(--transition);
    box-shadow: var(--shadow);
    height: 100%;
}

.similar-product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.card-img-wrapper {
    height: 180px;
    overflow: hidden;
}

.similar-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.similar-product-card:hover .similar-card-img {
    transform: scale(1.1);
}

.card-body {
    padding: 1rem;
    text-align: center;
}

.card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.card-price {
    color: var(--accent);
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 0.75rem;
}

.btn-view {
    display: inline-block;
    padding: 5px 15px;
    font-size: 0.8rem;
    color: var(--primary);
    text-decoration: none;
    border: 1px solid var(--primary);
    border-radius: 20px;
    transition: var(--transition);
}

.btn-view:hover {
    background: var(--primary);
    color: white;
}

/* Boutons de partage */
.btn-share {
    border: none;
    border-radius: 12px;
    padding: 12px;
    font-weight: 500;
    transition: var(--transition);
    color: white;
}

.btn-share:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
}

.whatsapp-share { background: #25D366; }
.facebook-share { background: #1877F2; }
.twitter-share { background: #1DA1F2; }
.instagram-share { background: linear-gradient(45deg, #f09433, #d62976, #962fbf); }
.tiktok-share { background: #000000; }
.youtube-share { background: #FF0000; }

.btn-copy {
    background: var(--primary);
    color: white;
    border: none;
    transition: var(--transition);
}

.btn-copy:hover {
    background: var(--accent);
    color: var(--primary-dark);
}

/* Modal */
.modal-content {
    border-radius: 24px;
    overflow: hidden;
}

/* Responsive */
@media (max-width: 768px) {
    .product-detail-title {
        font-size: 1.8rem;
    }
    
    .price-large {
        font-size: 1.8rem;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .btn-whatsapp, .btn-outline-share {
        padding: 10px;
        font-size: 0.85rem;
    }
    
    .zoom-detail-btn {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .product-detail-title {
        font-size: 1.5rem;
    }
    
    .price-large {
        font-size: 1.5rem;
    }
}

/* Bouton d'envoi WhatsApp dans le modal */
.btn-whatsapp-send {
    background: #25D366;
    border: none;
    color: white;
    border-radius: 50px;
    padding: 10px 24px;
    font-weight: 600;
    transition: var(--transition);
}

.btn-whatsapp-send:hover {
    background: #128C7E;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
    color: white;
}

/* Animation du modal */
.modal-content {
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Champ de formulaire focus */
.form-control:focus, .form-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
}

/* Badge accent */
.text-accent {
    color: var(--accent) !important;
}

.bg-accent {
    background: var(--accent);
    color: var(--primary-dark);
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Numéro WhatsApp du vendeur
    const whatsappNumber = "25779666439";
    
    // Éléments du modal de commande
    const orderModalElement = document.getElementById('orderInfoModal');
    const orderModal = new bootstrap.Modal(orderModalElement);
    const openOrderBtn = document.getElementById('openOrderModalBtn');
    const sendOrderBtn = document.getElementById('sendWhatsAppOrderBtn');
    
    // Variables pour stocker les infos produit
    let currentProduct = {
        title: '',
        price: '',
        id: '',
        image: ''
    };
    
    // ========== OUVERTURE MODAL DE COMMANDE ==========
if (openOrderBtn) {
    openOrderBtn.addEventListener('click', function() {
        // Récupérer l'ID correctement
        const productId = this.getAttribute('data-id');
        console.log('ID du produit récupéré:', productId); // Pour déboguer
        
        currentProduct.title = this.getAttribute('data-title') || '';
        currentProduct.price = this.getAttribute('data-price') || '';
        currentProduct.id = productId || '';
        currentProduct.image = document.getElementById('mainProductImage').src;
        
        // Remplir les infos dans le modal
        document.getElementById('orderProductTitle').textContent = currentProduct.title;
        document.getElementById('orderProductPrice').textContent = currentProduct.price;
        document.getElementById('orderProductImage').src = currentProduct.image;
        
        // Afficher aussi l'ID dans le modal pour vérification
        console.log('Produit chargé:', currentProduct);
        
        // Réinitialiser le formulaire
        document.getElementById('orderForm').reset();
        
        // Ouvrir le modal
        orderModal.show();
    });
}
    
 // ========== ENVOI VERS WHATSAPP AVEC INFOS COLLECTÉES ==========
if (sendOrderBtn) {
    sendOrderBtn.addEventListener('click', function() {
        // Récupérer les valeurs du formulaire
        const customerName = document.getElementById('customerName').value.trim();
        const customerPhone = document.getElementById('customerPhone').value.trim();
        const customerCountry = document.getElementById('customerCountry').value.trim();
        const customerCity = document.getElementById('customerCity').value.trim();
        const customerAddress = document.getElementById('customerAddress').value.trim();
        const customerNotes = document.getElementById('customerNotes').value.trim();
        
        // Validation
        if (!customerName) {
            Swal.fire('Champ requis', 'Veuillez entrer votre nom complet', 'warning');
            document.getElementById('customerName').focus();
            return;
        }
        
        if (!customerPhone) {
            Swal.fire('Champ requis', 'Veuillez entrer votre numéro de téléphone', 'warning');
            document.getElementById('customerPhone').focus();
            return;
        }
        
        if (!customerCountry) {
            Swal.fire('Champ requis', 'Veuillez entrer votre pays', 'warning');
            document.getElementById('customerCountry').focus();
            return;
        }
        
        if (!customerCity) {
            Swal.fire('Champ requis', 'Veuillez entrer votre ville', 'warning');
            document.getElementById('customerCity').focus();
            return;
        }
        
        if (!customerAddress) {
            Swal.fire('Champ requis', 'Veuillez entrer votre adresse de livraison', 'warning');
            document.getElementById('customerAddress').focus();
            return;
        }
        
        // Vérifier que l'ID du produit existe
        console.log('ID du produit à envoyer:', currentProduct.id);
        
        // Construire le message WhatsApp
        const productUrl = window.location.href;
        const date = new Date().toLocaleString('fr-FR');
        
        let message = '';
        message += '*NOUVELLE COMMANDE - NUFOTEC*%0A';
        message += '========================%0A%0A';
        
        message += '*PRODUIT*%0A';
        message += `Nom : ${encodeURIComponent(currentProduct.title)}%0A`;
        message += `Prix : ${encodeURIComponent(currentProduct.price)}%0A`;
        message += `Lien : ${encodeURIComponent(productUrl)}%0A`;
        
        if (currentProduct.id && currentProduct.id !== '') {
            message += `Reference : #${currentProduct.id}%0A`;
        }
        
        message += `%0A`;
        
        message += '*CLIENT*%0A';
        message += `Nom : ${encodeURIComponent(customerName)}%0A`;
        message += `Tel : ${encodeURIComponent(customerPhone)}%0A`;
        message += `Pays : ${encodeURIComponent(customerCountry)}%0A`;
        message += `Ville : ${encodeURIComponent(customerCity)}%0A`;
        message += `Adresse : ${encodeURIComponent(customerAddress)}%0A`;
        
        if (customerNotes) {
            message += `Notes : ${encodeURIComponent(customerNotes)}%0A`;
        }
        
        message += '%0A========================%0A';
        message += `Date : ${encodeURIComponent(date)}%0A`;
        message += 'Statut : En attente de confirmation%0A%0A';
        message += 'Merci de traiter cette commande.';
        
        // Redirection WhatsApp
        const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + message;
        
        // Fermer le modal
        orderModal.hide();
        
        // Afficher une confirmation
        Swal.fire({
            title: 'Commande preparée',
            html: `Reference: #${currentProduct.id || 'Non specifiee'}`,
            icon: 'success',
            confirmButtonText: 'Continuer',
            confirmButtonColor: '#25D366'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(whatsappUrl, '_blank');
            }
        });
    });
}
    
    // ========== ZOOM SUR L'IMAGE ==========
    const mainImage = document.getElementById('mainProductImage');
    const zoomBtn = document.getElementById('zoomDetailBtn');
    const zoomModal = document.getElementById('zoomModal');
    const zoomImage = document.getElementById('zoomImage');
    
    function openZoomModal() {
        if (mainImage && zoomImage) {
            zoomImage.src = mainImage.src;
            if (zoomModal) {
                const modal = new bootstrap.Modal(zoomModal);
                modal.show();
            }
        }
    }
    
    if (mainImage) mainImage.addEventListener('click', openZoomModal);
    if (zoomBtn) zoomBtn.addEventListener('click', openZoomModal);
    
    // Zoom avec survol
    let zoomEnabled = false;
    if (zoomImage) {
        zoomImage.addEventListener('mousemove', function(e) {
            if (!zoomEnabled) return;
            const naturalWidth = this.naturalWidth;
            const naturalHeight = this.naturalHeight;
            const container = this.parentElement;
            const containerWidth = container.clientWidth;
            const containerHeight = container.clientHeight;
            
            if (naturalWidth > containerWidth || naturalHeight > containerHeight) {
                const scaleX = naturalWidth / containerWidth;
                const scaleY = naturalHeight / containerHeight;
                const scale = Math.max(scaleX, scaleY);
                const rect = this.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;
                const xPercent = (mouseX / containerWidth) * 100;
                const yPercent = (mouseY / containerHeight) * 100;
                
                this.style.transformOrigin = xPercent + '% ' + yPercent + '%';
                this.style.transform = 'scale(' + scale + ')';
            }
        });
        
        zoomImage.addEventListener('mouseleave', function() {
            this.style.transformOrigin = 'center center';
            this.style.transform = 'scale(1)';
        });
    }
    
    if (zoomModal) {
        zoomModal.addEventListener('shown.bs.modal', () => zoomEnabled = true);
        zoomModal.addEventListener('hidden.bs.modal', () => {
            zoomEnabled = false;
            if (zoomImage) {
                zoomImage.style.transformOrigin = 'center center';
                zoomImage.style.transform = 'scale(1)';
            }
        });
    }
    
    // ========== PARTAGE ==========
    const shareBtns = document.querySelectorAll('.share-product-btn');
    const shareModalElement = document.getElementById('shareModal');
    
    function openShareModal(title, url, image) {
        document.getElementById('shareProductTitle').textContent = title;
        document.getElementById('shareProductImage').src = image;
        document.getElementById('shareLink').value = url;
        
        const encodedUrl = encodeURIComponent(url);
        const encodedTitle = encodeURIComponent(title + ' - Découvrez ce produit exceptionnel !');
        
        document.getElementById('shareWhatsapp').href = 'https://wa.me/?text=' + encodedTitle + '%0A%0A' + encodedUrl;
        document.getElementById('shareFacebook').href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl;
        document.getElementById('shareTwitter').href = 'https://twitter.com/intent/tweet?text=' + encodedTitle + '&url=' + encodedUrl;
        document.getElementById('shareInstagram').href = 'https://www.instagram.com/?url=' + encodedUrl;
        document.getElementById('shareTiktok').href = 'https://www.tiktok.com/share?url=' + encodedUrl;
        document.getElementById('shareYoutube').href = 'https://www.youtube.com/share?url=' + encodedUrl;
        
        const modal = new bootstrap.Modal(shareModalElement);
        modal.show();
    }
    
    shareBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const title = this.getAttribute('data-title') || '';
            const url = this.getAttribute('data-url') || window.location.href;
            const image = this.getAttribute('data-image') || '';
            openShareModal(title, url, image);
        });
    });
    
    // ========== COPIER LE LIEN ==========
    document.querySelectorAll('.copy-link-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const linkInput = document.getElementById('shareLink');
            if (linkInput) {
                linkInput.select();
                linkInput.setSelectionRange(0, 99999);
                document.execCommand('copy');
                
                const notification = document.createElement('div');
                notification.textContent = '✓ Lien copié !';
                notification.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#0f4c3a;color:white;padding:12px 24px;border-radius:50px;z-index:9999;font-weight:500;box-shadow:0 4px 15px rgba(0,0,0,0.2);';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            }
        });
    });
    
    console.log('✅ Scripts chargés avec succès');
    console.log('Numéro WhatsApp configuré:', whatsappNumber);
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>