<!-- Section Nos Produits -->
<section class="products-section py-5">
    <div class="container">
        <!-- En-tête de section -->
        <div class="text-center mb-5">
            <h2 class="section-title position-relative d-inline-block">
                Nos Produits
                <span class="section-title-border"></span>
            </h2>
            <p class="section-subtitle text-muted mt-3">
                Découvrez notre sélection de produits de qualité
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
                        $product_url = base_url('product/' . ($product['slug'] ?? $product['id']));
                ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="product-card card h-100 border-0 shadow-sm">
                        <!-- Badge -->
                        <div class="product-badge">
                            <span class="badge bg-primary">Nouveau</span>
                        </div>
                        
                        <!-- Image du produit avec zoom -->
                        <div class="product-image-wrapper" data-product-id="<?= $product['id'] ?>">
                            <img src="<?= $image_path ?>" 
                                 class="product-image card-img-top" 
                                 alt="<?= htmlspecialchars($product['title']) ?>"
                                 loading="lazy"
                                 data-zoom-image="<?= $image_path ?>"
                                 onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'">
                            <div class="product-overlay">
                                <button class="btn btn-light btn-sm zoom-btn" data-img="<?= $image_path ?>">
                                    <i class="bx bx-search-alt"></i> Zoom
                                </button>
                                <button class="btn btn-light btn-sm quick-view" data-id="<?= $product['id'] ?>">
                                    <i class="bx bx-show"></i> Aperçu
                                </button>
                            </div>
                        </div>

                        <!-- Contenu de la carte -->
                        <div class="card-body d-flex flex-column">
                            <h5 class="product-title card-title mb-2">
                                <?= htmlspecialchars($product['title']) ?>
                            </h5>
                            
                            <p class="product-description text-muted small mb-3">
                                <?= htmlspecialchars(substr($product['description'], 0, 80)) ?>
                                <?= strlen($product['description']) > 80 ? '...' : '' ?>
                            </p>
                            
                            <div class="product-price mt-auto">
                                <span class="price-current h5 text-primary fw-bold mb-0">
                                    <?= htmlspecialchars($product['price']) ?>
                                </span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="product-actions mt-3">
                                <div class="d-flex gap-2">
                                    <a href="<?= $product_url ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                                        <i class="bx bx-show-alt"></i> Détails
                                    </a>
                                    <button class="btn btn-primary btn-sm share-btn" 
                                            data-title="<?= htmlspecialchars($product['title']) ?>"
                                            data-url="<?= $product_url ?>"
                                            data-image="<?= $image_path ?>">
                                        <i class="bx bx-share-alt"></i> Partager
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bx bx-package text-muted" style="font-size: 4rem;"></i>
                    <p class="mt-3 text-muted">Aucun produit disponible pour le moment</p>
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
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="zoom-container">
                    <img id="zoomImage" src="" class="img-fluid w-100" style="cursor: crosshair;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Partage -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="bx bx-share-alt me-2"></i>Partager ce produit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="share-product-info mb-4 text-center">
                    <img id="shareProductImage" src="" class="rounded mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                    <h6 id="shareProductTitle" class="mb-2"></h6>
                </div>
                
                <div class="share-buttons mb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="#" id="shareWhatsapp" class="btn btn-success w-100" target="_blank">
                                <i class="bx bxl-whatsapp me-2"></i>WhatsApp
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareFacebook" class="btn btn-primary w-100" target="_blank">
                                <i class="bx bxl-facebook me-2"></i>Facebook
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareTwitter" class="btn btn-info w-100 text-white" target="_blank">
                                <i class="bx bxl-twitter me-2"></i>Twitter/X
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareInstagram" class="btn btn-danger w-100" target="_blank">
                                <i class="bx bxl-instagram me-2"></i>Instagram
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareTiktok" class="btn btn-dark w-100" target="_blank">
                                <i class="bx bxl-tiktok me-2"></i>TikTok
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" id="shareYoutube" class="btn btn-danger w-100" target="_blank">
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
                        <button class="btn btn-outline-primary copy-link-btn" type="button">
                            <i class="bx bx-copy"></i> Copier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aperçu rapide -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
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
                        <h3 id="quickViewPrice" class="text-primary mb-3"></h3>
                        <p id="quickViewDescription" class="text-muted"></p>
                        <hr>
                        <div class="mt-3">
                            <button class="btn btn-primary w-100 mb-2 share-from-quick" id="shareFromQuick">
                                <i class="bx bx-share-alt me-2"></i>Partager ce produit
                            </button>
                            <button class="btn btn-outline-primary w-100" id="contactFromQuick">
                                <i class="bx bx-envelope me-2"></i>Demander plus d'informations
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles CSS -->
<style>
/* Section principale */
.products-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.section-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1e2a3e;
    padding-bottom: 15px;
}

.section-title-border {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #0d6efd, #0dcaf0);
    border-radius: 3px;
}

.section-subtitle {
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.product-card {
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
}

.product-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
}

.product-badge .badge {
    padding: 5px 12px;
    font-size: 0.7rem;
    font-weight: 500;
    border-radius: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.product-image-wrapper {
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
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

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.quick-view, .zoom-btn {
    transform: translateY(20px);
    transition: transform 0.3s ease;
    border-radius: 30px;
    padding: 8px 16px;
    font-weight: 500;
}

.product-card:hover .quick-view,
.product-card:hover .zoom-btn {
    transform: translateY(0);
}

.card-body {
    padding: 1.25rem;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e2a3e;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-description {
    font-size: 0.85rem;
    line-height: 1.5;
    color: #6c757d;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.price-current {
    font-size: 1.3rem;
    font-weight: 700;
}

.product-actions .btn {
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.product-actions .btn-outline-primary:hover {
    background: #0d6efd;
    color: white;
}

.product-actions .btn-primary {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    border: none;
}

.product-actions .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
}

/* Modal Zoom */
.zoom-container {
    position: relative;
    overflow: hidden;
    cursor: crosshair;
}

#zoomImage {
    transition: transform 0.2s ease;
    max-height: 80vh;
    object-fit: contain;
}

/* Responsive */
@media (max-width: 768px) {
    .section-title {
        font-size: 1.8rem;
    }
    
    .section-subtitle {
        font-size: 0.95rem;
    }
    
    .product-title {
        font-size: 1rem;
    }
    
    .price-current {
        font-size: 1.1rem;
    }
    
    .product-actions .btn {
        font-size: 0.75rem;
        padding: 5px 10px;
    }
}

@media (max-width: 576px) {
    .section-title {
        font-size: 1.5rem;
    }
    
    .product-card {
        border-radius: 12px;
    }
    
    .quick-view, .zoom-btn {
        padding: 4px 10px;
        font-size: 0.7rem;
    }
}

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
$(document).ready(function() {
    let currentShareData = {};
    
    // Zoom sur l'image
    function initZoom() {
        $('.zoom-btn, .product-image-wrapper').on('click', function(e) {
            if (!$(e.target).hasClass('quick-view') && !$(e.target).hasClass('zoom-btn') && $(e.target).closest('.quick-view').length === 0) {
                let imgSrc = $(this).find('.product-image').attr('src');
                if ($(e.target).hasClass('zoom-btn')) {
                    imgSrc = $(e.target).data('img');
                }
                $('#zoomImage').attr('src', imgSrc);
                $('#zoomModal').modal('show');
            }
        });
        
        // Zoom au survol pour le modal
        $('#zoomImage').on('mousemove', function(e) {
            const img = this;
            const naturalWidth = img.naturalWidth;
            const naturalHeight = img.naturalHeight;
            const container = $(this).parent();
            const containerWidth = container.width();
            const containerHeight = container.height();
            
            if (naturalWidth > containerWidth || naturalHeight > containerHeight) {
                const scaleX = naturalWidth / containerWidth;
                const scaleY = naturalHeight / containerHeight;
                const scale = Math.max(scaleX, scaleY);
                
                const mouseX = e.offsetX;
                const mouseY = e.offsetY;
                const xPercent = (mouseX / containerWidth) * 100;
                const yPercent = (mouseY / containerHeight) * 100;
                
                $(this).css({
                    transformOrigin: `${xPercent}% ${yPercent}%`,
                    transform: `scale(${scale})`
                });
            }
        });
        
        $('#zoomImage').on('mouseleave', function() {
            $(this).css({
                transformOrigin: 'center center',
                transform: 'scale(1)'
            });
        });
    }
    
    // Partage
    function initShare() {
        $('.share-btn, #shareFromQuick, .share-from-quick').on('click', function() {
            let title, url, image;
            
            if ($(this).hasClass('share-btn')) {
                title = $(this).data('title');
                url = $(this).data('url');
                image = $(this).data('image');
            } else {
                title = $('#quickViewTitle').text();
                url = window.location.href;
                image = $('#quickViewImage').attr('src');
            }
            
            currentShareData = { title, url, image };
            
            $('#shareProductTitle').text(title);
            $('#shareProductImage').attr('src', image);
            $('#shareLink').val(url);
            
            // Construire les liens de partage
            const encodedUrl = encodeURIComponent(url);
            const encodedTitle = encodeURIComponent(title);
            
            $('#shareWhatsapp').attr('href', `https://wa.me/?text=${encodedTitle} - ${encodedUrl}`);
            $('#shareFacebook').attr('href', `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`);
            $('#shareTwitter').attr('href', `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`);
            $('#shareInstagram').attr('href', `https://www.instagram.com/?url=${encodedUrl}`);
            $('#shareTiktok').attr('href', `https://www.tiktok.com/share?url=${encodedUrl}`);
            $('#shareYoutube').attr('href', `https://www.youtube.com/share?url=${encodedUrl}`);
            
            $('#shareModal').modal('show');
        });
        
        // Copier le lien
        $('.copy-link-btn').on('click', function() {
            const linkInput = $('#shareLink');
            linkInput.select();
            document.execCommand('copy');
            
            Swal.fire({
                title: 'Lien copié !',
                text: 'Le lien du produit a été copié dans votre presse-papier.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
    
    // Aperçu rapide
    function initQuickView() {
        $('.quick-view').on('click', function(e) {
            e.stopPropagation();
            const productCard = $(this).closest('.product-card');
            const title = productCard.find('.product-title').text();
            const price = productCard.find('.price-current').text();
            const description = productCard.find('.product-description').text();
            const image = productCard.find('.product-image').attr('src');
            
            $('#quickViewTitle').text(title);
            $('#quickViewPrice').text(price);
            $('#quickViewDescription').text(description);
            $('#quickViewImage').attr('src', image);
            
            $('#quickViewModal').modal('show');
        });
        
        $('#quickViewImage').on('click', function() {
            const imgSrc = $(this).attr('src');
            $('#zoomImage').attr('src', imgSrc);
            $('#zoomModal').modal('show');
        });
    }
    
    // Contact
    $('#contactFromQuick').on('click', function() {
        const productTitle = $('#quickViewTitle').text();
        Swal.fire({
            title: 'Demande d\'information',
            text: `Vous souhaitez des informations sur "${productTitle}" ?`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Envoyer un message',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url("contact") ?>?product=' + encodeURIComponent(productTitle);
            }
        });
    });
    
    // Initialiser toutes les fonctions
    initZoom();
    initShare();
    initQuickView();
    
    // Animation au scroll
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
});
</script>