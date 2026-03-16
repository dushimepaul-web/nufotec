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
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumb-wrapper {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 20px 0;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .breadcrumb-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1));
        transform: skewX(-20deg);
    }

    .breadcrumb {
        margin: 0;
        font-size: 14px;
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .breadcrumb-item a:hover {
        color: var(--accent);
    }

    .breadcrumb-item.active {
        color: var(--accent);
        font-weight: 600;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255,255,255,0.5);
    }

    /* ===== HERO SECTION ===== */
    .hero-section {
        position: relative;
        height: 300px;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .hero-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .hero-bg-image img {
        object-fit: cover;
        object-position: center;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 76, 58, 0.85);
        z-index: 2;
    }

    .hero-content-wrapper {
        position: relative;
        z-index: 3;
        width: 100%;
        padding: 20px 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1.2;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.3rem;
        font-weight: 500;
        color: var(--accent);
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .hero-text {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 20px;
        opacity: 0.95;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        background: var(--accent);
        color: var(--primary-dark);
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .hero-btn:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
        color: var(--primary-dark);
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .loading-overlay.active {
        display: flex;
    }

    .spinner-ring {
        width: 60px;
        height: 60px;
        border: 4px solid var(--gray-light);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== TOAST NOTIFICATION ===== */
    .toast-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
    }

    .custom-toast {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: var(--shadow-xl);
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
        animation: slideIn 0.3s ease-out;
        min-width: 300px;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .custom-toast.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .custom-toast.error {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
    }

    /* ===== CART FLOATING BUTTON ===== */
    .cart-float-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: var(--shadow-xl);
        cursor: pointer;
        z-index: 999;
        transition: var(--transition-bounce);
    }

    .cart-float-btn:hover {
        transform: scale(1.1) rotate(5deg);
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    }

    .cart-badge-float {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--accent);
        color: var(--primary-dark);
        font-size: 12px;
        font-weight: 700;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow);
    }

    /* ===== PRODUIT DETAIL ===== */
    .product-detail {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--shadow-lg);
        margin-bottom: 40px;
    }

    .product-gallery {
        position: relative;
    }

    .product-gallery .main-image {
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--light);
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 15px;
        border: 1px solid var(--gray-light);
    }

    .product-gallery .main-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .product-gallery .thumbnail-list {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 5px;
    }

    .product-gallery .thumbnail {
        width: 80px;
        height: 80px;
        flex-shrink: 0;
        background: var(--light);
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: var(--transition);
    }

    .product-gallery .thumbnail:hover,
    .product-gallery .thumbnail.active {
        border-color: var(--accent);
        transform: scale(1.05);
    }

    .product-gallery .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-info {
        padding-left: 30px;
    }

    .product-info .product-category {
        font-size: 14px;
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .product-info .product-title {
        font-size: 32px;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 15px;
        line-height: 1.2;
    }

    .product-info .product-price-box {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
    }

    .product-info .product-price {
        font-size: 36px;
        font-weight: 700;
        color: var(--primary);
        display: flex;
        align-items: baseline;
        gap: 15px;
        flex-wrap: wrap;
    }

    .product-info .product-price .old-price {
        font-size: 20px;
        color: var(--gray);
        text-decoration: line-through;
        font-weight: 400;
    }

    .product-info .product-price .price-note {
        font-size: 14px;
        font-weight: 400;
        color: var(--gray);
    }

    .product-info .product-favorites {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 20px;
        font-size: 14px;
        color: var(--gray);
    }

    .product-info .product-favorites i {
        color: #dc3545;
        font-size: 18px;
    }

    .product-info .product-description {
        margin-bottom: 30px;
        line-height: 1.8;
        color: var(--dark);
    }

    .product-info .product-details {
        margin-bottom: 30px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .product-info .detail-item {
        background: var(--light);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid var(--accent);
    }

    .product-info .detail-item .detail-label {
        font-weight: 700;
        color: var(--primary);
        display: block;
        margin-bottom: 8px;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .product-info .detail-item .detail-value {
        color: var(--gray);
        line-height: 1.6;
        font-size: 15px;
    }

    .product-info .product-actions {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 30px;
    }

    .product-info .btn-add-cart {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 16px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .product-info .btn-add-cart:hover {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .product-info .btn-add-cart.added {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .product-info .btn-wishlist {
        width: 55px;
        height: 55px;
        background: var(--light);
        border: 2px solid var(--gray-light);
        border-radius: 50%;
        color: var(--gray);
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
    }

    .product-info .btn-wishlist:hover {
        border-color: #dc3545;
        color: #dc3545;
        background: rgba(220, 53, 69, 0.1);
    }

    .product-info .btn-wishlist.active {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    .certifications-section {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 30px;
    }

    .certifications-section h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .certifications-section h3 i {
        color: var(--accent);
    }

    .certification-list {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .certification-item {
        background: var(--light);
        padding: 10px 20px;
        border-radius: 30px;
        font-size: 14px;
        color: var(--primary);
        border: 1px solid var(--gray-light);
    }

    /* ===== DESCRIPTION SOUS LA GALERIE ===== */
    .product-extra-details {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-top: 20px;
    }

    .product-extra-details h4 {
        font-size: 18px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .product-extra-details p {
        color: var(--gray);
        line-height: 1.8;
        font-size: 15px;
        margin-bottom: 0;
    }

    .product-extra-details .extra-item:not(:last-child) {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--gray-light);
    }

    .related-products-section {
        margin-top: 40px;
    }

    .related-products-section h3 {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .related-products-section h3 i {
        color: var(--accent);
    }

    .related-products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    .related-product-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border: 1px solid var(--gray-light);
    }

    .related-product-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-color: var(--accent);
    }

    .related-product-card a {
        text-decoration: none;
        color: inherit;
    }

    .related-product-image {
        height: 150px;
        padding: 15px;
        background: var(--light);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .related-product-image img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .related-product-info {
        padding: 15px;
    }

    .related-product-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 5px;
        line-height: 1.3;
        height: 36px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .related-product-price {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary);
    }

    /* ===== AMÉLIORATIONS MOBILES ===== */
    @media (max-width: 991px) {
        .product-info {
            padding-left: 0;
            margin-top: 30px;
        }
    }

    @media (max-width: 768px) {
        .product-gallery .main-image {
            height: 250px;
        }
        .product-info .product-title {
            font-size: 24px;
        }
        .product-info .product-price {
            font-size: 28px;
        }
        .product-actions {
            flex-direction: column;
            gap: 10px;
        }
        .btn-add-cart, .btn-wishlist {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
        .btn-wishlist {
            height: 50px;
            border-radius: 50px;
            width: 100%;
        }
        .related-products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .related-product-image {
            height: 120px;
        }
        .certification-list {
            gap: 10px;
        }
        .certification-item {
            padding: 8px 15px;
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .related-products-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .product-gallery .main-image {
            height: 200px;
        }
        .product-info .product-title {
            font-size: 20px;
        }
        .product-info .product-price {
            font-size: 24px;
        }
    }
</style>

<!-- ===== HERO SECTION ===== -->
<?php if (isset($hero_section) && !empty($hero_section)): ?>
<div class="hero-section position-relative overflow-hidden">
    <?php if (!empty($hero_section['image_url'])): ?>
    <div class="hero-bg-image">
        <img src="<?php echo base_url($hero_section['image_url']); ?>" 
             alt="<?php echo isset($hero_section['titre_section']) ? $hero_section['titre_section'] : 'Hero background'; ?>"
             class="w-100 h-100 object-fit-cover">
    </div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <?php if (!empty($hero_section['titre_section'])): ?>
                        <h1 class="hero-title animate__animated animate__fadeInUp">
                            <?php echo $hero_section['titre_section']; ?>
                        </h1>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['sous_titre'])): ?>
                        <h2 class="hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                            <?php echo $hero_section['sous_titre']; ?>
                        </h2>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['contenu_texte'])): ?>
                        <p class="hero-text animate__animated animate__fadeInUp animate__delay-2s">
                            <?php echo $hero_section['contenu_texte']; ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($hero_section['bouton_texte']) && !empty($hero_section['bouton_lien'])): ?>
                        <a href="<?php echo base_url($hero_section['bouton_lien']); ?>" 
                           class="hero-btn animate__animated animate__fadeInUp animate__delay-3s">
                            <?php echo $hero_section['bouton_texte']; ?>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<?php endif; ?>

<!-- ===== TOAST CONTAINER ===== -->
<div class="toast-container" id="toastContainer"></div>

<!-- ===== PRODUCT DETAIL ===== -->
<div class="container py-4">
    <div class="product-detail">
        <div class="row">
            <!-- Left column: Gallery + Long description + Instructions -->
            <div class="col-lg-6">
                <!-- Gallery -->
                <div class="product-gallery">
                    <div class="main-image" id="mainImageContainer">
                        <?php 
                        $mainImage = null;
                        if (!empty($images)) {
                            foreach ($images as $img) {
                                if ($img->est_principale == 1) {
                                    $mainImage = $img;
                                    break;
                                }
                            }
                            if (!$mainImage) {
                                $mainImage = $images[0];
                            }
                        }
                        ?>
                        <?php if ($mainImage): ?>
                            <img src="<?php echo base_url('attachments/Produits/Images/' . $mainImage->nom_fichier); ?>" 
                                 alt="<?php echo $mainImage->alt_text ?: $produit->nom_produit; ?>" 
                                 id="mainProductImage">
                        <?php elseif (!empty($produit->image_principale)): ?>
                            <img src="<?php echo base_url('attachments/Produits/Images/' . $produit->image_principale); ?>" 
                                 alt="<?php echo $produit->nom_produit; ?>" 
                                 id="mainProductImage">
                        <?php else: ?>
                            <img src="https://placehold.co/600x400/0f4c3a/d4af37?text=<?php echo urlencode(substr($produit->nom_produit, 0, 3)); ?>" 
                                 alt="<?php echo $produit->nom_produit; ?>"
                                 id="mainProductImage">
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($images) && count($images) > 1): ?>
                    <div class="thumbnail-list">
                        <?php foreach ($images as $index => $img): ?>
                            <div class="thumbnail <?php echo ($img->est_principale == 1 || $index == 0) ? 'active' : ''; ?>" 
                                 onclick="changeMainImage('<?php echo base_url('attachments/Produits/Images/' . $img->nom_fichier); ?>', this)">
                                <img src="<?php echo base_url('attachments/Produits/Images/' . $img->nom_fichier); ?>" 
                                     alt="<?php echo $img->alt_text ?: 'Thumbnail ' . ($index+1); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Long description and instructions (below images) -->
                <?php if (!empty($produit->description_longue) || !empty($produit->mode_emploi)): ?>
                <div class="product-extra-details">
                    <?php if (!empty($produit->description_longue)): ?>
                        <div class="extra-item">
                            <h4><i class="bi bi-file-text"></i> Detailed description</h4>
                            <p><?php echo nl2br($produit->description_longue); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($produit->mode_emploi)): ?>
                        <div class="extra-item">
                            <h4><i class="bi bi-clock-history"></i> Instructions for use</h4>
                            <p><?php echo nl2br($produit->mode_emploi); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right column: Product information -->
            <div class="col-lg-6">
                <div class="product-info">
                    <div class="product-category">
                        <i class="bi bi-tag-fill"></i>
                        <?php echo $produit->code_categorie; ?> - <?php echo $produit->nom_categorie; ?>
                    </div>

                    <h1 class="product-title"><?php echo $produit->nom_produit; ?></h1>

                    <div class="product-price-box">
                        <div class="product-price">
                            <?php if ($produit->prix_public): ?>
                                <?php echo number_format($produit->prix_public, 0, ',', ' '); ?> <?php echo $produit->currency; ?>
                                <?php if ($produit->prix_grossiste): ?>
                                    <span class="old-price"><?php echo number_format($produit->prix_grossiste, 0, ',', ' '); ?> <?php echo $produit->currency; ?></span>
                                <?php endif; ?>
                                <span class="price-note">Price</span>
                            <?php else: ?>
                                Price on request
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="product-favorites">
                        <i class="bi bi-heart-fill"></i>
                        <span><?php echo (int)($produit->nb_favoris ?? 0); ?> people have favorited this product</span>
                    </div>

                    <?php if (!empty($produit->description_courte)): ?>
                        <div class="product-description">
                            <strong>Quick description:</strong><br>
                            <?php echo nl2br($produit->description_courte); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Details (composition, indications, contraindications, etc.) without instructions -->
                    <div class="product-details">
                        <?php if (!empty($produit->composition)): ?>
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-capsule me-2"></i>Composition</span>
                                <div class="detail-value"><?php echo nl2br($produit->composition); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($produit->indications)): ?>
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-heart-pulse me-2"></i>Indications</span>
                                <div class="detail-value"><?php echo nl2br($produit->indications); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($produit->contre_indications)): ?>
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-exclamation-triangle me-2"></i>Contraindications</span>
                                <div class="detail-value"><?php echo nl2br($produit->contre_indications); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($produit->presentation)): ?>
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-box me-2"></i>Presentation</span>
                                <div class="detail-value"><?php echo $produit->presentation; ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($produit->conditionnement)): ?>
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-box-seam me-2"></i>Packaging</span>
                                <div class="detail-value"><?php echo $produit->conditionnement; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-actions">
                        <button class="btn-add-cart" 
                                data-product-id="<?php echo $produit->id_produit; ?>"
                                data-product-name="<?php echo htmlspecialchars(addslashes($produit->nom_produit)); ?>"
                                onclick="addToCart(<?php echo $produit->id_produit; ?>, '<?php echo htmlspecialchars(addslashes($produit->nom_produit)); ?>', this)">
                            <i class="bi bi-cart-plus"></i>
                            Add to cart
                        </button>
                        <button class="btn-wishlist <?php echo (isset($produit->user_favori) && $produit->user_favori) ? 'active' : ''; ?>" 
                                data-product-id="<?php echo $produit->id_produit; ?>"
                                onclick="toggleWishlist(<?php echo $produit->id_produit; ?>, this)" 
                                title="Add to favorites">
                            <i class="bi <?php echo (isset($produit->user_favori) && $produit->user_favori) ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                        </button>
                    </div>

                    <?php if (!empty($produit->fiche_technique_url)): ?>
                        <a href="<?php echo base_url($produit->fiche_technique_url); ?>" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-file-pdf"></i> Download technical sheet
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Certifications -->
    <?php if (!empty($certifications)): ?>
    <div class="certifications-section">
        <h3><i class="bi bi-patch-check-fill"></i> Certifications</h3>
        <div class="certification-list">
            <?php foreach ($certifications as $certif): ?>
                <span class="certification-item"><?php echo $certif; ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Similar products -->
    <?php if (!empty($produits_similaires)): ?>
    <div class="related-products-section">
        <h3><i class="bi bi-stars"></i> Similar products</h3>
        <div class="related-products-grid">
            <?php foreach ($produits_similaires as $similaire): ?>
                <div class="related-product-card">
                    <a href="<?php echo base_url('boutique/detail/' . $similaire->slug); ?>">
                        <div class="related-product-image">
                            <?php if (!empty($similaire->image_principale)): ?>
                                <img src="<?php echo base_url('attachments/Produits/' . $similaire->image_principale); ?>" 
                                     alt="<?php echo $similaire->nom_produit; ?>">
                            <?php else: ?>
                                <img src="https://placehold.co/200x200/0f4c3a/d4af37?text=<?php echo urlencode(substr($similaire->nom_produit, 0, 3)); ?>" 
                                     alt="<?php echo $similaire->nom_produit; ?>">
                            <?php endif; ?>
                        </div>
                        <div class="related-product-info">
                            <div class="related-product-title">
                                <?= htmlspecialchars($similaire->nom_produit); ?>
                            </div>
                            <div class="related-product-price">
                                <?php if (!empty($similaire->prix_public) && $similaire->prix_public > 0): ?>
                                    <span class="price-amount">
                                        <?= number_format($similaire->prix_public, 0, ',', ' '); ?>
                                    </span>
                                    <span class="price-currency">
                                        <?= !empty($similaire->currency) ? $similaire->currency : 'USD'; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="price-quote">On request</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    // Fonction pour changer l'image principale
    function changeMainImage(src, thumbnail) {
        document.getElementById('mainProductImage').src = src;
        document.querySelectorAll('.thumbnail').forEach(el => el.classList.remove('active'));
        thumbnail.classList.add('active');
    }

    // Sécurité : si les fonctions globales ne sont pas définies (par le footer), on les définit localement
    window.addToCart = window.addToCart || function(productId, productName, btn) {
        console.error('addToCart function is not defined globally. Make sure footer script is loaded.');
        alert('Add to cart function not available');
    };

    window.toggleWishlist = window.toggleWishlist || function(productId, btn) {
        console.error('toggleWishlist function is not defined globally.');
        alert('Wishlist function not available');
    };

    // Optionnel : on peut aussi ajouter des écouteurs en plus des onclick
    document.addEventListener('DOMContentLoaded', function() {
        // Rien de nécessaire ici car les onclick sont déjà présents
    });
      function updateCategoryTitle() {
        var title = document.querySelector('.section-title');
        if (!title) return;
        
        var activeLink = document.querySelector('.category-link.active .category-name');
        var newTitle = currentCategory !== 'all' 
            ? (activeLink ? activeLink.textContent : 'Products') 
            : 'Our Products';
        
        var icon = title.querySelector('i');
        var countEl = document.querySelector('.product-count');
        var count = countEl ? countEl.outerHTML : '';
        
        title.innerHTML = icon ? (icon.outerHTML + ' ' + newTitle + ' ' + count) : (newTitle + ' ' + count);
    }

    function updateCartBadge() {
        fetch(BASE_URL + 'panier/get_cart')
            .then(r => r.json())
            .then(data => {
                var badge = document.getElementById('cartBadge');
                if (badge) badge.textContent = data.nb_articles || 0;
            })
            .catch(() => {});
    }
    / Exposer les fonctions globales pour les boutons
    window.addToCart = function(productId, productName, btn) {
        if (!btn || btn.disabled) return;
        
        var originalContent = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;

        fetch(BASE_URL + 'panier/ajouter', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(productId) + '&quantite=1'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.classList.add('added');
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Added';
                showToast(productName + ' added to cart', 'success');
                updateCartBadge();
                setTimeout(() => {
                    btn.classList.remove('added');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }, 2000);
            } else {
                showToast(data.message || 'Error', 'error');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        })
        .catch(() => {
            showToast('Connection error', 'error');
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
    };

    window.toggleWishlist = function(productId, btn) {
        if (!btn || btn.disabled) return;
        btn.disabled = true;

        fetch(BASE_URL + 'panier/toggle_favori', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'produit_id=' + encodeURIComponent(productId)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'added') {
                    btn.classList.add('active');
                    btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
                    showToast('Added to favorites', 'success');
                } else {
                    btn.classList.remove('active');
                    btn.innerHTML = '<i class="bi bi-heart"></i>';
                    showToast('Removed from favorites', 'success');
                }
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(() => {
            showToast('Connection error', 'error');
        })
        .finally(() => {
            setTimeout(() => { btn.disabled = false; }, 500);
        });
    };

    // Polling pour mettre à jour le badge du panier toutes les 2 secondes (optionnel)
    function startCartPolling() {
        setInterval(updateCartBadge, 2000);
    }
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>