<!-- ============================================
   SECTION NOS PRODUITS - Style cohérent avec page produit
   ============================================ -->
<section class="products-section py-5">
    <div class="container">
        <!-- En-tête de section -->
        <div class="text-center mb-5">
            <h2 class="section-title position-relative d-inline-block">
                <?= t('our_products') ?>
                <span class="section-title-border"></span>
            </h2>
            <p class="section-subtitle text-muted mt-3">
                <?= t('products_subtitle') ?>
            </p>
        </div>

        <!-- Grille des produits -->
        <div class="products-grid">
            <div class="row g-4">
                <?php if (!empty($products)): 
                    foreach ($products as $product): 
                        $image_path = !empty($product['main_image']) 
                            ? base_url('attachments/Products/'.$product['main_image']) 
                            : base_url('attachments/Products/default-product.png');
                        $product_url = base_url($lang . '/product/' . ($product['slug'] ?? $product['id']));
                        $is_new = (strtotime($product['created_at'] ?? 'now') > strtotime('-30 days'));
                ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="product-card card h-100 border-0 shadow-sm">
                        <?php if ($is_new): ?>
                        <div class="product-badge">
                            <span class="badge">✨ <?= t('badge_new') ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-image-wrapper">
                            <img src="<?= $image_path ?>" 
                                 class="product-image card-img-top" 
                                 alt="<?= htmlspecialchars($product['title']) ?>"
                                 loading="lazy"
                                 onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'">
                            <div class="product-overlay">
                                <button class="btn zoom-btn" data-img="<?= $image_path ?>">
                                    <i class="bx bx-search-alt"></i> <?= t('zoom') ?>
                                </button>
                                <button class="btn quick-view" data-id="<?= $product['id'] ?>">
                                    <i class="bx bx-show"></i> <?= t('quick_view') ?>
                                </button>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="product-title card-title mb-2">
                                <?= htmlspecialchars($product['title']) ?>
                            </h5>
                            
                            <p class="product-description text-muted small mb-3">
                                <?= htmlspecialchars(substr($product['description'] ?? '', 0, 80)) ?>
                                <?= strlen($product['description'] ?? '') > 80 ? '...' : '' ?>
                            </p>
                            
                            <div class="product-price mt-auto">
                                <span class="price-current mb-0">
                                    <?= htmlspecialchars($product['price']) ?>
                                </span>
                            </div>
                            
                            <div class="product-actions mt-3">
                                <a href="<?= $product_url ?>" class="btn btn-detail w-100">
                                    <i class="bx bx-show-alt me-2"></i><?= t('view_details') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bx bx-package text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                    <p class="mt-3 text-muted"><?= t('no_products') ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Modal Zoom Image -->
<div class="modal fade" id="zoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center d-flex align-items-center justify-content-center" style="min-height: 80vh;">
                <img id="zoomImage" src="" class="img-fluid" style="max-height: 85vh; max-width: 95%; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- Modal Aperçu rapide -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: var(--primary, #0f4c3a); color: white;">
                <h5 class="modal-title">
                    <i class="bx bx-package me-2"></i>
                    <span id="quickViewTitle"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="quick-view-image-wrapper">
                            <img id="quickViewImage" src="" class="img-fluid rounded shadow-sm" style="width: 100%; cursor: pointer;">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h3 id="quickViewPrice" class="mb-3" style="color: var(--accent, #d4af37); font-weight: 700;"></h3>
                        <p id="quickViewDescription" class="text-muted"></p>
                        <hr>
                        <div class="mt-3">
                            <a href="#" id="quickViewDetailBtn" class="btn btn-detail w-100 mb-2">
                                <i class="bx bx-show-alt me-2"></i><?= t('view_full_details') ?>
                            </a>
                            <button class="btn btn-outline-share w-100" id="shareFromQuick">
                                <i class="bx bx-share-alt me-2"></i><?= t('share_product') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles CSS - Utilise les mêmes couleurs que la page produit -->
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

/* Section principale */
.products-section {
    background: var(--light);
    padding: 60px 0;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    padding-bottom: 15px;
}

.section-title-border {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--accent);
    border-radius: 3px;
}

.section-subtitle {
    font-size: 1rem;
    color: var(--gray);
    max-width: 600px;
    margin: 0 auto;
}

/* Cartes produits */
.product-card {
    border-radius: 24px;
    overflow: hidden;
    transition: var(--transition);
    background: white;
    position: relative;
    border: 1px solid var(--gray-light) !important;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl) !important;
}

/* Badge */
.product-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 2;
}

.product-badge .badge {
    background: var(--accent);
    color: var(--primary-dark);
    padding: 6px 14px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 30px;
    box-shadow: var(--shadow);
}

/* Image wrapper */
.product-image-wrapper {
    position: relative;
    overflow: hidden;
    background: var(--light);
    aspect-ratio: 1 / 1;
    cursor: pointer;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

/* Overlay avec boutons */
.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 76, 58, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.zoom-btn, .quick-view {
    transform: translateY(20px);
    transition: transform 0.3s ease;
    border-radius: 30px;
    padding: 8px 18px;
    font-weight: 500;
    font-size: 0.8rem;
    border: none;
    cursor: pointer;
}

.zoom-btn {
    background: white;
    color: var(--primary);
}

.zoom-btn:hover {
    background: var(--accent);
    color: var(--primary-dark);
    transform: translateY(0) scale(1.05);
}

.quick-view {
    background: var(--accent);
    color: var(--primary-dark);
}

.quick-view:hover {
    background: var(--accent-hover);
    transform: translateY(0) scale(1.05);
}

.product-card:hover .zoom-btn,
.product-card:hover .quick-view {
    transform: translateY(0);
}

/* Corps de la carte */
.card-body {
    padding: 1.25rem;
}

.product-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 48px;
}

.product-description {
    font-size: 0.8rem;
    line-height: 1.5;
    color: var(--gray);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 40px;
}

.product-price {
    margin-top: auto;
}

.price-current {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--accent);
}

/* Bouton Voir détails */
.btn-detail {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.btn-detail:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(15, 76, 58, 0.3);
    color: white;
}

.btn-outline-share {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: 50px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-outline-share:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

/* Modal Zoom */
.modal-content.bg-dark {
    background: rgba(0, 0, 0, 0.95) !important;
}

#zoomImage {
    cursor: crosshair;
    transition: transform 0.2s ease;
}

/* Responsive */
@media (max-width: 991px) {
    .products-section {
        padding: 40px 0;
    }
    .section-title {
        font-size: 1.8rem;
    }
    .product-title {
        font-size: 0.95rem;
    }
    .price-current {
        font-size: 1.1rem;
    }
}

@media (max-width: 768px) {
    .products-section {
        padding: 30px 0;
    }
    .section-title {
        font-size: 1.5rem;
    }
    .section-subtitle {
        font-size: 0.9rem;
    }
    .product-card {
        border-radius: 16px;
    }
    .zoom-btn, .quick-view {
        padding: 5px 12px;
        font-size: 0.7rem;
    }
    .btn-detail {
        padding: 8px 16px;
        font-size: 0.85rem;
    }
}

@media (max-width: 576px) {
    .product-title {
        font-size: 0.9rem;
        min-height: 40px;
    }
    .product-description {
        font-size: 0.75rem;
    }
    .price-current {
        font-size: 1rem;
    }
    .btn-detail {
        padding: 8px 12px;
        font-size: 0.8rem;
    }
}

/* Animation fadeInUp */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.product-card {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
}

.product-card:nth-child(1) { animation-delay: 0.05s; }
.product-card:nth-child(2) { animation-delay: 0.1s; }
.product-card:nth-child(3) { animation-delay: 0.15s; }
.product-card:nth-child(4) { animation-delay: 0.2s; }
.product-card:nth-child(5) { animation-delay: 0.25s; }
.product-card:nth-child(6) { animation-delay: 0.3s; }
.product-card:nth-child(7) { animation-delay: 0.35s; }
.product-card:nth-child(8) { animation-delay: 0.4s; }
</style>

<!-- Scripts JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Variables globales
    let currentQuickProduct = {
        title: '',
        price: '',
        description: '',
        image: '',
        url: ''
    };
    
    // ==========================================
    // ZOOM SUR L'IMAGE
    // ==========================================
    function initZoom() {
        // Zoom via les boutons
        document.querySelectorAll('.zoom-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const imgSrc = this.getAttribute('data-img');
                const zoomImage = document.getElementById('zoomImage');
                if (zoomImage && imgSrc) {
                    zoomImage.src = imgSrc;
                    const zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));
                    zoomModal.show();
                }
            });
        });
        
        // Zoom au survol dans le modal
        const zoomImage = document.getElementById('zoomImage');
        if (zoomImage) {
            zoomImage.addEventListener('mousemove', function(e) {
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
                    
                    this.style.transformOrigin = `${xPercent}% ${yPercent}%`;
                    this.style.transform = `scale(${scale})`;
                }
            });
            
            zoomImage.addEventListener('mouseleave', function() {
                this.style.transformOrigin = 'center center';
                this.style.transform = 'scale(1)';
            });
        }
    }
    
    // ==========================================
    // APERÇU RAPIDE
    // ==========================================
    function initQuickView() {
        document.querySelectorAll('.quick-view').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const productCard = this.closest('.product-card');
                const title = productCard.querySelector('.product-title')?.textContent || '';
                const price = productCard.querySelector('.price-current')?.textContent || '';
                const description = productCard.querySelector('.product-description')?.textContent || '';
                const image = productCard.querySelector('.product-image')?.src || '';
                const detailLink = productCard.querySelector('.btn-detail')?.getAttribute('href') || '';
                
                currentQuickProduct = { title, price, description, image, url: detailLink };
                
                // Remplir le modal
                document.getElementById('quickViewTitle').textContent = title;
                document.getElementById('quickViewPrice').textContent = price;
                document.getElementById('quickViewDescription').textContent = description;
                document.getElementById('quickViewImage').src = image;
                
                // Configurer le bouton "Voir la fiche complète"
                const detailBtn = document.getElementById('quickViewDetailBtn');
                if (detailBtn && detailLink) {
                    detailBtn.setAttribute('href', detailLink);
                }
                
                const quickModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
                quickModal.show();
            });
        });
        
        // Zoom sur l'image dans l'aperçu rapide
        const quickImage = document.getElementById('quickViewImage');
        if (quickImage) {
            quickImage.addEventListener('click', function() {
                const imgSrc = this.src;
                const zoomImage = document.getElementById('zoomImage');
                if (zoomImage && imgSrc) {
                    zoomImage.src = imgSrc;
                    const zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));
                    zoomModal.show();
                }
            });
        }
    }
    
    // ==========================================
    // PARTAGE DEPUIS L'APERÇU
    // ==========================================
    function initShare() {
        const shareBtn = document.getElementById('shareFromQuick');
        if (shareBtn) {
            shareBtn.addEventListener('click', function() {
                const productTitle = currentQuickProduct.title;
                const productUrl = currentQuickProduct.url;
                const productImage = currentQuickProduct.image;
                
                if (productTitle && productUrl) {
                    // Construire le message pour WhatsApp
                    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(productTitle + ' - ' + productUrl)}`;
                    window.open(whatsappUrl, '_blank');
                } else {
                    Swal.fire({
                        title: 'Information',
                        text: 'Lien du produit non disponible',
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    }
    
    // ==========================================
    // ANIMATION AU SCROLL
    // ==========================================
    function initScrollAnimation() {
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
        
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.5s ease';
            observer.observe(card);
        });
    }
    
    // Initialisation
    initZoom();
    initQuickView();
    initShare();
    initScrollAnimation();
    
    console.log('✅ Products section initialized');
});
</script>