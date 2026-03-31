<?php 
// ============================================
// PASSER LE PRODUIT AU HEADER POUR LES META TAGS
// ============================================
if (isset($product) && !empty($product)) {
    $this->load->vars(['product' => $product]);
}

include VIEWPATH.'includes/frontend/Header.php'; 
?>

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

/* Page Produit */
.product-detail-page { 
    background: var(--light); 
    min-height: 100vh;
    padding-top: 20px;
}

/* Breadcrumb */
.breadcrumb-item a { 
    color: var(--primary); 
    transition: var(--transition);
    text-decoration: none;
}
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
    border: 1px solid var(--gray-light);
}
#mainProductImage { 
    transition: transform 0.5s ease; 
    min-height: 400px;
}
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
    display: flex;
    align-items: center;
    gap: 8px;
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
    line-height: 1.2;
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

/* Description */
.product-detail-description {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: var(--shadow);
}
.product-detail-description p {
    margin-bottom: 0;
    text-align: justify;
}

/* Boutons */
.btn-whatsapp {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    border: none;
    color: white;
    border-radius: 50px;
    padding: 16px 24px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: var(--transition);
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.btn-whatsapp:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
    color: white;
}
.btn-whatsapp i { font-size: 1.3rem; }

.btn-outline-share {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: 50px;
    padding: 16px 24px;
    font-weight: 600;
    font-size: 1rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.btn-outline-share:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}
.btn-outline-share i { font-size: 1.2rem; }

/* Meta infos */
.product-meta {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: var(--shadow);
}
.product-meta strong {
    font-size: 1.1rem;
}

/* Produits similaires */
.similar-products {
    background: white;
    padding: 40px;
    border-radius: 24px;
    box-shadow: var(--shadow);
    margin-top: 40px;
}
.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}
.similar-product-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    transition: var(--transition);
    box-shadow: var(--shadow);
    height: 100%;
    border: 1px solid var(--gray-light);
}
.similar-product-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}
.card-img-wrapper { 
    height: 200px; 
    overflow: hidden;
    position: relative;
}
.similar-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.similar-product-card:hover .similar-card-img { transform: scale(1.1); }
.card-body { padding: 20px; }
.card-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.card-price {
    color: var(--accent);
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 12px;
}
.btn-view {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    font-size: 0.9rem;
    color: var(--primary);
    text-decoration: none;
    border: 1px solid var(--primary);
    border-radius: 20px;
    transition: var(--transition);
    font-weight: 500;
}
.btn-view:hover {
    background: var(--primary);
    color: white;
}

/* Boutons de partage dans le modal */
.btn-share {
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-weight: 600;
    transition: var(--transition);
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.95rem;
}
.btn-share:hover {
    transform: translateY(-3px);
    filter: brightness(1.1);
    color: white;
}
.whatsapp-share { background: #25D366; }
.facebook-share { background: #1877F2; }
.twitter-share { background: #1DA1F2; }
.pinterest-share { background: #E60023; }
.linkedin-share { background: #0A66C2; }
.telegram-share { background: #0088cc; }
.email-share { background: #6c757d; }

.btn-copy {
    background: var(--primary);
    color: white;
    border: none;
    transition: var(--transition);
    padding: 10px 20px;
    font-weight: 600;
}
.btn-copy:hover {
    background: var(--accent);
    color: var(--primary-dark);
}

/* Modal amélioré */
.modal-content { 
    border-radius: 24px; 
    overflow: hidden;
    border: none;
}
.modal-header {
    padding: 20px 24px;
}
.modal-body { padding: 24px; }
.modal-footer { padding: 20px 24px; }

.btn-whatsapp-send {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    border: none;
    color: white;
    border-radius: 50px;
    padding: 12px 28px;
    font-weight: 600;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-whatsapp-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
    color: white;
}

/* Order info dans le modal */
.order-product-info {
    background: linear-gradient(135deg, var(--primary-lighter, #e8f5f0) 0%, white 100%);
    border: 1px solid var(--gray-light);
    border-radius: 16px;
    padding: 16px;
}

/* Form styling */
.form-control:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
}

/* Responsive */
@media (max-width: 991px) {
    .product-detail-title { font-size: 2rem; }
    .price-large { font-size: 2rem; }
    .btn-whatsapp, .btn-outline-share { 
        padding: 14px 20px; 
        font-size: 1rem;
    }
    .similar-products { padding: 30px 20px; }
}

@media (max-width: 768px) {
    .product-detail-title { font-size: 1.6rem; }
    .price-large { font-size: 1.6rem; }
    .product-detail-page { padding-top: 10px; }
    
    .btn-whatsapp, .btn-outline-share { 
        padding: 12px 16px; 
        font-size: 0.9rem;
        width: 100%;
    }
    .product-cta .d-flex {
        flex-direction: column;
    }
    .similar-products { 
        padding: 20px 15px; 
        border-radius: 16px;
    }
    .section-title { font-size: 1.5rem; }
}

@media (max-width: 576px) {
    .product-detail-title { font-size: 1.4rem; }
    .price-large { font-size: 1.4rem; }
    .main-image-container { border-radius: 16px; }
    #mainProductImage { min-height: 300px; }
}
</style>

<div class="product-detail-page">
    <div class="container py-4">
        <!-- ============================================
        MINI HERO - Simple & Élégante
        ============================================ -->
        <div class="mini-hero">
            <div class="container">
                <div class="mini-hero-content">
                    <?php if (!empty($product)): ?>
                    <!-- Titre principal -->
                    <h1 class="mini-title">
                        <?= htmlspecialchars($product['title']) ?>
                    </h1>
                    
                    <!-- Description courte -->
                    <p class="mini-desc">
                        Découvrez notre gamme de produits naturels, conçus avec passion et expertise pour votre bien-être. Chaque produit est élaboré à partir d'ingrédients soigneusement sélectionnés, respectueux de l'environnement et de votre santé.
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
        /* ============================================
        MINI HERO - Style épuré
        ============================================ */

        .mini-hero {
            background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
            padding: 30px 0 40px;
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
        }

        .mini-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .mini-hero-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .mini-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary, #0f4c3a);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .mini-desc {
            font-size: 1rem;
            color: var(--gray, #6c757d);
            line-height: 1.6;
            max-width: 650px;
            margin: 0 auto 0;
        }

        @media (max-width: 768px) {
            .mini-hero {
                padding: 25px 0 30px;
            }
            .mini-title {
                font-size: 1.5rem;
            }
            .mini-desc {
                font-size: 0.9rem;
                padding: 0 15px;
            }
        }

        @media (max-width: 480px) {
            .mini-title {
                font-size: 1.3rem;
            }
        }
        </style>

        <?php if (!empty($product)): ?>
        
        <!-- DEBUG: Confirmer que le produit est chargé -->
        <!-- Produit: <?= htmlspecialchars($product['title']) ?> | ID: <?= $product['id'] ?> -->
        
        <div class="row g-4 g-lg-5">
            <!-- Colonne Image -->
            <div class="col-lg-6">
                <div class="product-detail-image-wrapper">
                    <div class="main-image-container">
                        <img src="<?= base_url('attachments/Products/'.$product['main_image']) ?>" 
                             id="mainProductImage"
                             class="img-fluid w-100"
                             style="cursor: zoom-in; max-height: 500px; object-fit: contain; background: var(--light);"
                             alt="<?= htmlspecialchars($product['title']) ?>"
                             onerror="this.src='<?= base_url('assets/fro.png') ?>'">
                        
                        <?php if (strtotime($product['created_at'] ?? 'now') > strtotime('-30 days')): ?>
                        <div class="product-badge-detail">
                            <span class="badge">✨ Nouveau</span>
                        </div>
                        <?php endif; ?>
                        
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
                        <h5 class="fw-semibold mb-3" style="color: var(--primary);">
                            <i class="bx bx-info-circle me-2"></i>Description
                        </h5>
                        <p class="text-muted lh-lg">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'Aucune description disponible.')) ?>
                        </p>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="product-cta mt-4">
                        <div class="d-flex gap-3 flex-wrap">
                            <button class="btn btn-whatsapp flex-grow-1" id="openOrderModalBtn" 
                                    data-title="<?= htmlspecialchars($product['title']) ?>"
                                    data-price="<?= htmlspecialchars($product['price']) ?>"
                                    data-id="<?= $product['id'] ?>"
                                    data-image="<?= base_url('attachments/Products/'.$product['main_image']) ?>">
                                <i class="bx bxl-whatsapp"></i> Commander sur WhatsApp
                            </button>
                            
                            <button class="btn btn-outline-share flex-grow-1" id="openShareModalBtn"
                                    data-title="<?= htmlspecialchars($product['title']) ?>"
                                    data-url="<?= base_url('Products/detail/'.($product['slug'] ?? $product['id'])) ?>"
                                    data-image="<?= base_url('attachments/Products/'.$product['main_image']) ?>"
                                    data-price="<?= htmlspecialchars($product['price']) ?>">
                                <i class="bx bx-share-alt"></i> Partager
                            </button>
                        </div>
                    </div>
                    
                    <div class="product-meta mt-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">
                                    <i class="bx bx-calendar me-1"></i>Ajouté le
                                </small>
                                <strong style="color: var(--primary);">
                                    <?= date('d/m/Y', strtotime($product['created_at'])) ?>
                                </strong>
                            </div>
                            <?php if (!empty($product['stock']) && $product['stock'] > 0): ?>
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">
                                    <i class="bx bx-package me-1"></i>Stock
                                </small>
                                <strong style="color: var(--success, #198754);">
                                    <?= $product['stock'] ?> disponible(s)
                                </strong>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Produits similaires -->
        <?php if (!empty($similar_products)): ?>
        <div class="similar-products">
            <div class="section-header text-center mb-4">
                <h2 class="section-title">Produits <span style="color: var(--accent);">similaires</span></h2>
                <div class="title-border" style="width: 80px; height: 3px; background: var(--accent); margin: 15px auto 0; border-radius: 3px;"></div>
            </div>
            <div class="row g-4">
                <?php foreach ($similar_products as $similar): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="similar-product-card h-100">
                        <div class="card-img-wrapper">
                            <img src="<?= base_url('attachments/Products/'.$similar['main_image']) ?>" 
                                 class="similar-card-img" 
                                 alt="<?= htmlspecialchars($similar['title']) ?>"
                                 onerror="this.src='<?= base_url('assets/fro.png') ?>'">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($similar['title']) ?></h6>
                            <p class="card-price mt-auto"><?= htmlspecialchars($similar['price']) ?></p>
                            <a href="<?= base_url('Products/detail/'.($similar['slug'] ?? $similar['id'])) ?>" class="btn-view mt-2">
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
        <!-- Produit non trouvé -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bx bx-package text-muted" style="font-size: 5rem; opacity: 0.5;"></i>
            </div>
            <h3 class="mb-3" style="color: var(--primary);">Produit non trouvé</h3>
            <p class="text-muted mb-4">Le produit que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href="<?= base_url('products') ?>" class="btn btn-lg" style="background: var(--primary); color: white; border-radius: 50px; padding: 12px 30px;">
                <i class="bx bx-arrow-back me-2"></i>Retour aux produits
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL DE COMMANDE -->
<div class="modal fade" id="orderInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: var(--primary); color: white;">
                <h5 class="modal-title">
                    <i class="bx bxl-whatsapp me-2"></i>Finaliser votre commande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <!-- Info produit -->
                <div class="order-product-info mb-4">
                    <div class="d-flex gap-3 align-items-center">
                        <img id="orderProductImage" src="" class="rounded" 
                             style="width: 80px; height: 80px; object-fit: cover; border: 2px solid var(--accent);">
                        <div>
                            <h6 id="orderProductTitle" class="mb-1 fw-bold" style="color: var(--primary); font-size: 1.1rem;"></h6>
                            <span id="orderProductPrice" class="text-accent fw-bold fs-5"></span>
                            <small id="orderProductRef" class="d-block text-muted mt-1"></small>
                        </div>
                    </div>
                </div>
                
                <form id="orderForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" id="customerName" required 
                                       placeholder="Votre nom et prénom">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-phone"></i></span>
                                <input type="tel" class="form-control" id="customerPhone" required 
                                       placeholder="Ex: 25779666439">
                            </div>
                            <small class="text-muted">Numéro WhatsApp pour vous contacter</small>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-globe"></i></span>
                                <input type="text" class="form-control" id="customerCountry" required 
                                       placeholder="Votre pays" value="Burundi">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ville <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-map"></i></span>
                                <input type="text" class="form-control" id="customerCity" required 
                                       placeholder="Votre ville" value="Bujumbura">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label fw-bold">Adresse de livraison complète <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bx bx-home"></i></span>
                            <textarea class="form-control" id="customerAddress" rows="2" required 
                                      placeholder="Quartier, rue, numéro, point de repère..."></textarea>
                        </div>
                    </div>
                    
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Annuler
                </button>
                <button type="button" class="btn btn-whatsapp-send" id="sendWhatsAppOrderBtn">
                    <i class="bx bxl-whatsapp"></i>Envoyer la commande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE PARTAGE -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: var(--primary); color: white;">
                <h5 class="modal-title">
                    <i class="bx bx-share-alt me-2"></i>Partager ce produit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <!-- Info produit -->
                <div class="share-product-info mb-4 text-center">
                    <img id="shareProductImage" src="" class="rounded mb-3" 
                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid var(--accent);">
                    <h6 id="shareProductTitle" class="mb-1 fw-bold" style="color: var(--primary); font-size: 1.1rem;"></h6>
                    <p id="shareProductPrice" class="text-accent fw-bold mb-0 fs-5"></p>
                </div>
                
                <!-- Boutons de partage -->
                <div class="share-buttons mb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="#" id="shareWhatsapp" class="btn btn-share whatsapp-share w-100" target="_blank" rel="noopener">
                                <i class="bx bxl-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareFacebook" class="btn btn-share facebook-share w-100" target="_blank" rel="noopener">
                                <i class="bx bxl-facebook"></i> Facebook
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareTwitter" class="btn btn-share twitter-share w-100" target="_blank" rel="noopener">
                                <i class="bx bxl-twitter"></i> Twitter/X
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="sharePinterest" class="btn btn-share pinterest-share w-100" target="_blank" rel="noopener">
                                <i class="bx bxl-pinterest"></i> Pinterest
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareLinkedIn" class="btn btn-share linkedin-share w-100" target="_blank" rel="noopener">
                                <i class="bx bxl-linkedin"></i> LinkedIn
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareTelegram" class="btn btn-share telegram-share w-100" target="_blank" rel="noopener">
                                <i class="bx bxl-telegram"></i> Telegram
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareEmail" class="btn btn-share email-share w-100">
                                <i class="bx bx-envelope"></i> Email
                            </a>
                        </div>
                        <div class="col-6">
                            <button id="shareNativeBtn" class="btn btn-share w-100" style="background: #6c757d;">
                                <i class="bx bx-dots-horizontal-rounded"></i> Autres apps
                            </button>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <!-- Lien à copier -->
                <div class="share-link">
                    <label class="form-label fw-bold mb-2">
                        <i class="bx bx-link me-1"></i>Lien du produit
                    </label>
                    <div class="input-group input-group-lg">
                        <input type="text" id="shareLink" class="form-control bg-light" readonly>
                        <button class="btn btn-copy" type="button" id="copyLinkBtn">
                            <i class="bx bx-copy me-1"></i>Copier
                        </button>
                    </div>
                </div>
                
                <!-- Astuce Partage Solidaire -->
                <div class="mt-3 p-3 bg-light rounded" style="border-left: 4px solid var(--accent, #d4af37);">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            <i class="bx bx-heart" style="font-size: 24px; color: var(--accent, #d4af37);"></i>
                        </div>
                        <div class="flex-grow-1">
                            <strong style="color: var(--primary, #0f4c3a); display: block; margin-bottom: 5px;">
                                <i class="bx bx-share-alt me-1"></i> Partagez et aidez ceux qui en ont besoin
                            </strong>
                            <p class="text-muted mb-2" style="font-size: 0.85rem;">
                                Connaissez-vous quelqu'un qui pourrait bénéficier de ce produit ? 
                                Un proche, un ami ou une personne vulnérable ? 
                                <strong>Votre partage peut faire la différence !</strong>
                            </p>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="badge bg-success">
                                    <i class="bx bx-group me-1"></i> Aidez un proche
                                </span>
                                <span class="badge bg-warning text-dark">
                                    <i class="bx bx-trending-up me-1"></i> 128 partages cette semaine
                                </span>
                                <span class="badge bg-info text-dark">
                                    <i class="bx bx-smile me-1"></i> Ensemble, on est plus forts
                                </span>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bx bx-question-mark me-1"></i> 
                                Chaque partage permet à plus de personnes de découvrir des solutions naturelles 
                                pour améliorer leur bien-être.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ZOOM -->
<div class="modal fade" id="zoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0 text-center d-flex align-items-center justify-content-center" style="min-height: 80vh;">
                <img id="zoomImage" src="" class="img-fluid" style="max-height: 85vh; max-width: 95%; object-fit: contain;">
            </div>
        </div>
    </div>
</div>



<!-- TOAST PRIX RÉEL - Version simple -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="priceToast" class="toast align-items-center text-white bg-dark border-warning" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="d-flex">
            <div class="toast-body p-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="bx bx-dollar-circle fs-1 text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong class="fs-6 text-warning">
                                <i class="bx bx-info-circle me-1"></i> Information prix
                            </strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Fermer"></button>
                        </div>
                        <p class="mb-2 small text-white-50">
                            Le prix affiché peut ne pas refléter le prix réel. 
                            <strong class="text-warning">Contactez-nous pour connaître le prix exact</strong> et bénéficier de nos offres.
                        </p>
                        <div class="d-flex gap-2 mt-2">
                            <a href="https://wa.me/25779666439?text=Bonjour,%20je%20souhaite%20connaître%20le%20prix%20réel%20de%20<?= urlencode($product['title'] ?? 'ce produit') ?>%20sur%20<?= urlencode(SITE_NAME ?? 'NUFOTEC') ?>" 
                               class="btn btn-sm btn-success" target="_blank">
                                <i class="bx bxl-whatsapp me-1"></i> Demander le prix
                            </a>
                            <a href="tel:25779666439" class="btn btn-sm btn-outline-light">
                                <i class="bx bx-phone me-1"></i> Appeler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher le toast au chargement
    const toastEl = document.getElementById('priceToast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, {
            autohide: false,  // Ne pas se fermer automatiquement
            delay: 5000
        });
        toast.show();
    }
});
</script>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Configuration
    const WHATSAPP_NUMBER = "25779666439";
    const SITE_NAME = "NUFOTEC";
    
    // Variables globales
    let currentProduct = {
        title: '',
        price: '',
        id: '',
        image: '',
        url: ''
    };
    
    // Initialisation des modals Bootstrap
    const orderModal = new bootstrap.Modal(document.getElementById('orderInfoModal'));
    const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
    
    // ==========================================
    // MODAL DE COMMANDE
    // ==========================================
    const openOrderBtn = document.getElementById('openOrderModalBtn');
    const sendOrderBtn = document.getElementById('sendWhatsAppOrderBtn');
    
    if (openOrderBtn) {
        openOrderBtn.addEventListener('click', function() {
            // Récupérer les données du produit
            currentProduct = {
                title: this.dataset.title || '',
                price: this.dataset.price || '',
                id: this.dataset.id || '',
                image: this.dataset.image || '',
                url: window.location.href
            };
            
            // Vérifier que l'ID existe
            if (!currentProduct.id) {
                console.error('ID produit manquant');
                Swal.fire('Erreur', 'Impossible de charger les informations du produit', 'error');
                return;
            }
            
            // Remplir le modal
            document.getElementById('orderProductTitle').textContent = currentProduct.title;
            document.getElementById('orderProductPrice').textContent = currentProduct.price;
            document.getElementById('orderProductImage').src = currentProduct.image;
            document.getElementById('orderProductRef').textContent = 'Réf: #' + currentProduct.id;
            
            // Réinitialiser le formulaire
            document.getElementById('orderForm').reset();
            
            // Focus sur le premier champ
            setTimeout(() => document.getElementById('customerName').focus(), 100);
            
            orderModal.show();
        });
    }
    
    // Envoi WhatsApp
    if (sendOrderBtn) {
        sendOrderBtn.addEventListener('click', function() {
            // Récupérer les valeurs du formulaire
            const formData = {
                name: document.getElementById('customerName').value.trim(),
                phone: document.getElementById('customerPhone').value.trim(),
                country: document.getElementById('customerCountry').value.trim(),
                city: document.getElementById('customerCity').value.trim(),
                address: document.getElementById('customerAddress').value.trim(),
                notes: document.getElementById('customerNotes')?.value.trim() || ''
            };
            
            // Validation
            const requiredFields = {
                name: 'Nom complet',
                phone: 'Téléphone',
                country: 'Pays',
                city: 'Ville',
                address: 'Adresse de livraison'
            };
            
            for (const [field, label] of Object.entries(requiredFields)) {
                if (!formData[field]) {
                    Swal.fire({
                        title: 'Champ requis',
                        text: `Veuillez entrer votre ${label}`,
                        icon: 'warning',
                        confirmButtonColor: '#0f4c3a'
                    });
                    const input = document.getElementById('customer' + field.charAt(0).toUpperCase() + field.slice(1));
                    if (input) {
                        input.focus();
                        input.classList.add('is-invalid');
                        setTimeout(() => input.classList.remove('is-invalid'), 3000);
                    }
                    return;
                }
            }
            
            // Construction du message WhatsApp formaté
            const now = new Date();
            const dateStr = now.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const message = [
                `🛒 *PRODUIT D'INTÉRÊT - ${SITE_NAME}*`,
                ``,
                `📦 *PRODUIT*`,
                `Nom: ${currentProduct.title}`,
                `Prix de référence : ${currentProduct.price}`,
                `Lien: ${currentProduct.url}`,
                ``,
                `👤 *CLIENT*`,
                `Nom: ${formData.name}`,
                `WhatsApp : ${formData.phone}`,
                `Tél mobile: ${formData.phone}`,
                `Pays: ${formData.country}`,
                `Ville: ${formData.city}`,
                `Adresse: ${formData.address}`,
                formData.notes ? `Notes: ${formData.notes}` : '',
                ``,
                `📅 Produit d'intérêt le: ${dateStr}`,
                `⏳ Statut: En attente de confirmation`
            ].filter(Boolean).join('\n');
            
            // Encoder pour URL
            const encodedMessage = encodeURIComponent(message);
            const whatsappUrl = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedMessage}`;
            
            // Fermer le modal
            orderModal.hide();
            
            // Confirmation avant redirection
            Swal.fire({
                title: '✅ Commande prête !',
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Produit:</strong> ${currentProduct.title}</p>
                        <p class="mb-2"><strong>Référence:</strong> #${currentProduct.id}</p>
                        <p class="mb-2"><strong>Client:</strong> ${formData.name}</p>
                        <p class="mb-0 text-muted">Vous allez être redirigé vers WhatsApp...</p>
                    </div>
                `,
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<i class="bx bxl-whatsapp me-1"></i>Ouvrir WhatsApp',
                confirmButtonColor: '#25D366',
                cancelButtonText: 'Fermer',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(whatsappUrl, '_blank');
                }
            });
        });
    }
    
    // ==========================================
    // MODAL DE PARTAGE
    // ==========================================
    const openShareBtn = document.getElementById('openShareModalBtn');
    
    if (openShareBtn) {
        openShareBtn.addEventListener('click', function() {
            const title = this.dataset.title || '';
            const url = this.dataset.url || window.location.href;
            const image = this.dataset.image || '';
            const price = this.dataset.price || '';
            
            currentProduct = { title, url, image, price };
            
            // Remplir le modal
            document.getElementById('shareProductTitle').textContent = title;
            document.getElementById('shareProductPrice').textContent = price;
            document.getElementById('shareProductImage').src = image;
            document.getElementById('shareLink').value = url;
            
            // Encoder pour URLs
            const encodedUrl = encodeURIComponent(url);
            const encodedTitle = encodeURIComponent(`${title} - ${price} sur ${SITE_NAME}`);
            const encodedImage = encodeURIComponent(image);
            const encodedDesc = encodeURIComponent(`Découvrez ${title} sur ${SITE_NAME}`);
            
            // Configurer les liens de partage
            const shareLinks = {
                shareWhatsapp: `https://wa.me/?text=${encodedTitle}%0A%0A${encodedUrl}`,
                shareFacebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodedTitle}`,
                shareTwitter: `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`,
                sharePinterest: `https://pinterest.com/pin/create/button/?url=${encodedUrl}&media=${encodedImage}&description=${encodedTitle}`,
                shareLinkedIn: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
                shareTelegram: `https://t.me/share/url?url=${encodedUrl}&text=${encodedTitle}`,
                shareEmail: `mailto:?subject=${encodedTitle}&body=${encodedDesc}%0A%0A${encodedUrl}`
            };
            
            // Appliquer les liens
            Object.entries(shareLinks).forEach(([id, href]) => {
                const element = document.getElementById(id);
                if (element) element.href = href;
            });
            
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
                        text: `Découvrez ${currentProduct.title} à ${currentProduct.price} sur ${SITE_NAME}`,
                        url: currentProduct.url
                    });
                } catch (err) {
                    console.log('Partage annulé:', err);
                }
            } else {
                // Fallback: copier le lien
                copyToClipboard(currentProduct.url);
                Swal.fire({
                    title: 'Lien copié !',
                    text: 'Le lien a été copié dans votre presse-papiers',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }
    
    // Copier le lien
    const copyBtn = document.getElementById('copyLinkBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const linkInput = document.getElementById('shareLink');
            copyToClipboard(linkInput.value);
            
            // Feedback visuel
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="bx bx-check"></i> Copié !';
            this.style.background = '#25D366';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalHTML;
                this.style.background = '';
                this.disabled = false;
            }, 2000);
        });
    }
    
    // Fonction utilitaire: copier dans le presse-papiers
    async function copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (err) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }
    
    // ==========================================
    // ZOOM IMAGE
    // ==========================================
    const mainImage = document.getElementById('mainProductImage');
    const zoomBtn = document.getElementById('zoomDetailBtn');
    const zoomImage = document.getElementById('zoomImage');
    const zoomModalEl = document.getElementById('zoomModal');
    
    function openZoom() {
        if (mainImage && zoomImage && zoomModalEl) {
            zoomImage.src = mainImage.src;
            const zoomModal = new bootstrap.Modal(zoomModalEl);
            zoomModal.show();
        }
    }
    
    if (mainImage) mainImage.addEventListener('click', openZoom);
    if (zoomBtn) zoomBtn.addEventListener('click', openZoom);
    
    // ==========================================
    // EFFETS VISUELS
    // ==========================================
    
    // Animation au scroll pour les éléments
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observer les cartes de produits similaires
    document.querySelectorAll('.similar-product-card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        observer.observe(card);
    });
    
    console.log('✅ Product Detail View initialized');
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>