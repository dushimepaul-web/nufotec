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
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Styles existants conservés... */
.product-detail-page { background: var(--light); min-height: 100vh; }
.breadcrumb-item a { color: var(--primary); transition: var(--transition); }
.breadcrumb-item a:hover { color: var(--accent); }
.text-accent { color: var(--accent) !important; }

/* Image Container */
.product-detail-image-wrapper { position: relative; }
.main-image-container {
    position: relative;
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}
#mainProductImage { transition: transform 0.5s ease; }
#mainProductImage:hover { transform: scale(1.02); }

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
    background: var(--accent);
    color: var(--primary-dark);
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

/* Titre et Prix */
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
.price-large {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--accent);
    background: linear-gradient(135deg, var(--accent), var(--accent-light));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Boutons */
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

.btn-outline-share {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: 50px;
    padding: 14px;
    font-weight: 600;
    transition: var(--transition);
}
.btn-outline-share:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

/* Produits similaires */
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
.card-img-wrapper { height: 180px; overflow: hidden; }
.similar-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.similar-product-card:hover .similar-card-img { transform: scale(1.1); }

/* Boutons de partage dans le modal */
.btn-share {
    border: none;
    border-radius: 12px;
    padding: 12px;
    font-weight: 500;
    transition: var(--transition);
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-share:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
    color: white;
}
.whatsapp-share { background: #25D366; }
.facebook-share { background: #1877F2; }
.twitter-share { background: #1DA1F2; }
.instagram-share { background: linear-gradient(45deg, #f09433, #d62976, #962fbf); }
.tiktok-share { background: #000000; }
.pinterest-share { background: #E60023; }
.linkedin-share { background: #0A66C2; }

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
.modal-content { border-radius: 24px; overflow: hidden; }
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
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .product-detail-title { font-size: 1.8rem; }
    .price-large { font-size: 1.8rem; }
    .btn-whatsapp, .btn-outline-share { padding: 10px; font-size: 0.85rem; }
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
                             alt="<?= htmlspecialchars($product['title']) ?>"
                             data-image-url="<?= base_url('attachments/Products/'.$product['main_image']) ?>">
                        
                        <div class="product-badge-detail">
                            <span class="badge">✨ Nouveau</span>
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
                        <span class="price-large"><?= htmlspecialchars($product['price']) ?></span>
                    </div>
                    
                    <div class="product-detail-description mb-4">
                        <h5 class="fw-semibold mb-3" style="color: var(--primary);">Description</h5>
                        <p class="text-muted lh-lg">
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                        </p>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="product-cta mt-4">
                        <div class="d-flex gap-3">
                            <button class="btn btn-whatsapp flex-grow-1" id="openOrderModalBtn" 
                                    data-title="<?= htmlspecialchars($product['title']) ?>"
                                    data-price="<?= htmlspecialchars($product['price']) ?>"
                                    data-id="<?= $product['id'] ?>"
                                    data-image="<?= base_url('attachments/Products/'.$product['main_image']) ?>">
                                <i class="bx bxl-whatsapp me-2"></i> Commander
                            </button>
                            
                            <button class="btn btn-outline-share flex-grow-1" id="openShareModalBtn"
                                    data-title="<?= htmlspecialchars($product['title']) ?>"
                                    data-url="<?= base_url('product/'.($product['slug'] ?? $product['id'])) ?>"
                                    data-image="<?= base_url('attachments/Products/'.$product['main_image']) ?>"
                                    data-price="<?= htmlspecialchars($product['price']) ?>">
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
        
        <!-- Produits similaires -->
        <?php if (!empty($similar_products)): ?>
        <div class="similar-products mt-5 pt-5">
            <div class="section-header text-center mb-4">
                <h2 class="section-title">Produits <span style="color: var(--accent);">similaires</span></h2>
                <div class="title-border" style="width: 80px; height: 3px; background: var(--accent); margin: 10px auto; border-radius: 3px;"></div>
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
                        <div class="card-body p-3 text-center">
                            <h6 class="card-title text-truncate"><?= htmlspecialchars($similar['title']) ?></h6>
                            <p class="card-price text-accent fw-bold"><?= htmlspecialchars($similar['price']) ?></p>
                            <a href="<?= base_url('product/'.($similar['slug'] ?? $similar['id'])) ?>" class="btn btn-sm btn-outline-primary rounded-pill">
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

<!-- MODAL DE COMMANDE -->
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
                            <small id="orderProductRef" class="d-block text-muted"></small>
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

<!-- MODAL DE PARTAGE -->
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
                    <img id="shareProductImage" src="" class="rounded mb-3" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--accent);">
                    <h6 id="shareProductTitle" class="mb-1 fw-bold" style="color: var(--primary);"></h6>
                    <p id="shareProductPrice" class="text-accent fw-bold mb-0"></p>
                </div>
                
                <div class="share-buttons mb-4">
                    <div class="row g-3">
                        <!-- WhatsApp -->
                        <div class="col-6">
                            <a href="#" id="shareWhatsapp" class="btn btn-share whatsapp-share w-100" target="_blank">
                                <i class="bx bxl-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                        <!-- Facebook -->
                        <div class="col-6">
                            <a href="#" id="shareFacebook" class="btn btn-share facebook-share w-100" target="_blank">
                                <i class="bx bxl-facebook"></i> Facebook
                            </a>
                        </div>
                        <!-- Twitter/X -->
                        <div class="col-6">
                            <a href="#" id="shareTwitter" class="btn btn-share twitter-share w-100" target="_blank">
                                <i class="bx bxl-twitter"></i> Twitter/X
                            </a>
                        </div>
                        <!-- Pinterest -->
                        <div class="col-6">
                            <a href="#" id="sharePinterest" class="btn btn-share pinterest-share w-100" target="_blank">
                                <i class="bx bxl-pinterest"></i> Pinterest
                            </a>
                        </div>
                        <!-- LinkedIn -->
                        <div class="col-6">
                            <a href="#" id="shareLinkedIn" class="btn btn-share linkedin-share w-100" target="_blank">
                                <i class="bx bxl-linkedin"></i> LinkedIn
                            </a>
                        </div>
                        <!-- Native Share (Mobile) -->
                        <div class="col-6">
                            <button id="shareNativeBtn" class="btn btn-share w-100" style="background: #6c757d;">
                                <i class="bx bx-share-alt"></i> Plus d'options
                            </button>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="share-link mt-3">
                    <label class="form-label fw-bold">Lien du produit</label>
                    <div class="input-group">
                        <input type="text" id="shareLink" class="form-control bg-light" readonly>
                        <button class="btn btn-copy" type="button" id="copyLinkBtn">
                            <i class="bx bx-copy"></i> Copier
                        </button>
                    </div>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        <strong>Astuce :</strong> Sur mobile, utilisez "Plus d'options" pour partager directement sur Instagram ou TikTok.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ZOOM -->
<div class="modal fade" id="zoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomImage" src="" class="img-fluid" style="max-height: 85vh;">
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Numéro WhatsApp du vendeur
    const WHATSAPP_NUMBER = "25779666439";
    
    // Variables globales
    let currentProduct = {
        title: '',
        price: '',
        id: '',
        image: '',
        url: ''
    };
    
    // Initialisation des modals
    const orderModal = new bootstrap.Modal(document.getElementById('orderInfoModal'));
    const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
    const zoomModalElement = document.getElementById('zoomModal');
    
    // ========== MODAL DE COMMANDE ==========
    const openOrderBtn = document.getElementById('openOrderModalBtn');
    const sendOrderBtn = document.getElementById('sendWhatsAppOrderBtn');
    
    if (openOrderBtn) {
        openOrderBtn.addEventListener('click', function() {
            currentProduct = {
                title: this.getAttribute('data-title') || '',
                price: this.getAttribute('data-price') || '',
                id: this.getAttribute('data-id') || '',
                image: this.getAttribute('data-image') || '',
                url: window.location.href
            };
            
            // Remplir le modal
            document.getElementById('orderProductTitle').textContent = currentProduct.title;
            document.getElementById('orderProductPrice').textContent = currentProduct.price;
            document.getElementById('orderProductImage').src = currentProduct.image;
            document.getElementById('orderProductRef').textContent = 'Réf: #' + currentProduct.id;
            
            // Réinitialiser le formulaire
            document.getElementById('orderForm').reset();
            
            orderModal.show();
        });
    }
    
    // Envoi de la commande WhatsApp
    if (sendOrderBtn) {
        sendOrderBtn.addEventListener('click', function() {
            const formData = {
                name: document.getElementById('customerName').value.trim(),
                phone: document.getElementById('customerPhone').value.trim(),
                country: document.getElementById('customerCountry').value.trim(),
                city: document.getElementById('customerCity').value.trim(),
                address: document.getElementById('customerAddress').value.trim(),
                notes: document.getElementById('customerNotes').value.trim()
            };
            
            // Validation
            const requiredFields = ['name', 'phone', 'country', 'city', 'address'];
            const fieldNames = {
                name: 'Nom complet',
                phone: 'Téléphone',
                country: 'Pays',
                city: 'Ville',
                address: 'Adresse de livraison'
            };
            
            for (let field of requiredFields) {
                if (!formData[field]) {
                    Swal.fire({
                        title: 'Champ requis',
                        text: `Veuillez entrer votre ${fieldNames[field]}`,
                        icon: 'warning',
                        confirmButtonColor: '#0f4c3a'
                    });
                    document.getElementById('customer' + field.charAt(0).toUpperCase() + field.slice(1)).focus();
                    return;
                }
            }
            
            // Construction du message
            const date = new Date().toLocaleString('fr-FR');
            const productUrl = currentProduct.url;
            
            let message = `*🛒 NOUVELLE COMMANDE - NUFOTEC*%0A`;
            message += `═══════════════════════%0A%0A`;
            
            message += `*📦 PRODUIT*%0A`;
            message += `Nom: ${currentProduct.title}%0A`;
            message += `Prix: ${currentProduct.price}%0A`;
            message += `Référence: #${currentProduct.id}%0A`;
            message += `Lien: ${productUrl}%0A%0A`;
            
            message += `*👤 CLIENT*%0A`;
            message += `Nom: ${formData.name}%0A`;
            message += `Téléphone: ${formData.phone}%0A`;
            message += `Pays: ${formData.country}%0A`;
            message += `Ville: ${formData.city}%0A`;
            message += `Adresse: ${formData.address}%0A`;
            
            if (formData.notes) {
                message += `Notes: ${formData.notes}%0A`;
            }
            
            message += `%0A═══════════════════════%0A`;
            message += `📅 Date: ${date}%0A`;
            message += `⏳ Statut: En attente de confirmation%0A%0A`;
            message += `Merci de traiter cette commande rapidement.`;
            
            // URL WhatsApp
            const whatsappUrl = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
            
            // Fermer le modal et rediriger
            orderModal.hide();
            
            Swal.fire({
                title: '✅ Commande prête !',
                text: `Référence: #${currentProduct.id}`,
                icon: 'success',
                confirmButtonText: 'Ouvrir WhatsApp',
                confirmButtonColor: '#25D366',
                showCancelButton: true,
                cancelButtonText: 'Fermer'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(whatsappUrl, '_blank');
                }
            });
        });
    }
    
    // ========== MODAL DE PARTAGE ==========
    const openShareBtn = document.getElementById('openShareModalBtn');
    
    if (openShareBtn) {
        openShareBtn.addEventListener('click', function() {
            const title = this.getAttribute('data-title') || '';
            const url = this.getAttribute('data-url') || window.location.href;
            const image = this.getAttribute('data-image') || '';
            const price = this.getAttribute('data-price') || '';
            
            // Stocker pour usage natif
            currentProduct = { title, url, image, price };
            
            // Remplir le modal
            document.getElementById('shareProductTitle').textContent = title;
            document.getElementById('shareProductPrice').textContent = price;
            document.getElementById('shareProductImage').src = image;
            document.getElementById('shareLink').value = url;
            
            // Préparer les liens de partage
            const encodedUrl = encodeURIComponent(url);
            const encodedTitle = encodeURIComponent(`${title} - ${price} sur NUFOTEC`);
            const encodedImage = encodeURIComponent(image);
            
            // WhatsApp
            document.getElementById('shareWhatsapp').href = 
                `https://wa.me/?text=${encodedTitle}%0A%0A${encodedUrl}`;
            
            // Facebook
            document.getElementById('shareFacebook').href = 
                `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodedTitle}`;
            
            // Twitter/X
            document.getElementById('shareTwitter').href = 
                `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`;
            
            // Pinterest (nécessite une image)
            document.getElementById('sharePinterest').href = 
                `https://pinterest.com/pin/create/button/?url=${encodedUrl}&media=${encodedImage}&description=${encodedTitle}`;
            
            // LinkedIn
            document.getElementById('shareLinkedIn').href = 
                `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`;
            
            shareModal.show();
        });
    }
    
    // Partage natif (Web Share API)
    const nativeShareBtn = document.getElementById('shareNativeBtn');
    if (nativeShareBtn) {
        nativeShareBtn.addEventListener('click', async function() {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: currentProduct.title,
                        text: `Découvrez ${currentProduct.title} à ${currentProduct.price} sur NUFOTEC`,
                        url: currentProduct.url
                    });
                } catch (err) {
                    console.log('Partage annulé');
                }
            } else {
                Swal.fire({
                    title: 'Partage',
                    text: 'Copiez le lien ci-dessus pour partager sur Instagram ou TikTok',
                    icon: 'info'
                });
            }
        });
    }
    
    // Copier le lien
    const copyBtn = document.getElementById('copyLinkBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', async function() {
            const linkInput = document.getElementById('shareLink');
            
            try {
                await navigator.clipboard.writeText(linkInput.value);
                
                // Feedback visuel
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bx bx-check"></i> Copié !';
                this.style.background = '#25D366';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.background = '';
                }, 2000);
                
            } catch (err) {
                // Fallback pour anciens navigateurs
                linkInput.select();
                document.execCommand('copy');
            }
        });
    }
    
    // ========== ZOOM IMAGE ==========
    const mainImage = document.getElementById('mainProductImage');
    const zoomBtn = document.getElementById('zoomDetailBtn');
    const zoomImage = document.getElementById('zoomImage');
    
    function openZoom() {
        if (mainImage && zoomImage) {
            zoomImage.src = mainImage.src;
            const zoomModal = new bootstrap.Modal(zoomModalElement);
            zoomModal.show();
        }
    }
    
    if (mainImage) mainImage.addEventListener('click', openZoom);
    if (zoomBtn) zoomBtn.addEventListener('click', openZoom);
    
    console.log('✅ Product Detail scripts loaded');
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>