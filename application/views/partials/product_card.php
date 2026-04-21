<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$is_new = ($prod->date_lancement && strtotime($prod->date_lancement) > strtotime('-30 days'));
$has_discount = ($prod->prix_grossiste && $prod->prix_public > $prod->prix_grossiste * 1.2);
?>
<div class="product-card" data-id="<?php echo $prod->id_produit; ?>">
    <?php if ($prod->est_vedette): ?>
        <span class="product-badge">
            <i class="bi bi-star-fill"></i> <?= t('badge_featured') ?>
        </span>
    <?php elseif ($is_new): ?>
        <span class="product-badge new">
            <i class="bi bi-lightning-charge-fill"></i> <?= t('badge_new') ?>
        </span>
    <?php elseif ($has_discount): ?>
        <span class="product-badge sale">
            <i class="bi bi-tag-fill"></i> <?= t('badge_sale') ?>
        </span>
    <?php endif; ?>

    <div class="product-image-wrapper">
        <a href="<?php echo base_url('boutique/detail/' . $prod->slug); ?>">
            <?php if (!empty($prod->image_principale)): ?>
                <img src="<?php echo base_url('attachments/Produits/' . $prod->image_principale); ?>"
                     alt="<?php echo htmlspecialchars($prod->nom_produit); ?>"
                     class="product-image"
                     loading="lazy">
            <?php else: ?>
                <img src="https://placehold.co/400x400/0f4c3a/d4af37?text=<?php echo urlencode(substr($prod->nom_produit, 0, 3)); ?>"
                     alt="<?php echo htmlspecialchars($prod->nom_produit); ?>"
                     class="product-image">
            <?php endif; ?>
        </a>
        <a href="<?php echo base_url('boutique/detail/' . $prod->slug); ?>" class="product-quick-view">
            <i class="bi bi-eye"></i> <?= t('view_details') ?>
        </a>
    </div>

    <div class="product-info">
        <div class="product-category">
            <i class="bi bi-tag-fill"></i>
            <?php echo $prod->code_categorie; ?> - <?php echo character_limiter($prod->nom_categorie, 20); ?>
        </div>

        <h3 class="product-title">
            <a href="<?php echo base_url('boutique/detail/' . $prod->slug); ?>">
                <?php echo htmlspecialchars($prod->nom_produit); ?>
            </a>
        </h3>

        <div class="product-description">
            <?php echo $prod->description_courte ? character_limiter(strip_tags($prod->description_courte), 80) : t('default_description'); ?>
        </div>

        <div class="product-favorites mb-2">
            <div class="rating-stars" data-rating="<?php echo min(5, ceil(((int)($prod->nb_favoris ?? 0) / 20))); ?>">
                <?php 
                $nbFavoris = (int)($prod->nb_favoris ?? 0);
                $rating = min(5, max(1, ceil($nbFavoris / 20)));
                if ($nbFavoris == 0) $rating = 0;
                for ($i = 1; $i <= 5; $i++): 
                    if ($i <= $rating): 
                ?>
                    <i class="fas fa-star star-filled"></i>
                <?php elseif ($i - 0.5 <= $rating): ?>
                    <i class="fas fa-star-half-alt star-half"></i>
                <?php else: ?>
                    <i class="far fa-star star-empty"></i>
                <?php 
                    endif;
                endfor; 
                ?>
            </div>
            <span class="favorites-text">
                <strong><?php echo $nbFavoris; ?></strong> <?= t('favorite_plural', ['count' => $nbFavoris]) ?>
            </span>
        </div>

        <div class="product-price-wrapper">
            <div class="price-row">
                <?php if ($prod->prix_public): ?>
                    <span class="product-price">
                        <?php echo number_format($prod->prix_public, 0, ',', ' '); ?>
                        <?php echo ($prod->currency); ?>
                    </span>
                    <?php if ($has_discount): ?>
                        <span class="old-price">
                            <?php echo number_format($prod->prix_grossiste * 1.3, 0, ',', ' '); ?> <?php echo ($prod->currency); ?>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="product-price" style="font-size: 16px;">
                        <?= t('price_on_request') ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if ($prod->prix_public): ?>
                <div class="price-note">
                    <i class="bi bi-info-circle"></i> <?= t('price_note_public') ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-actions">
            <button class="btn-add-cart" onclick="addToCart(<?php echo $prod->id_produit; ?>, '<?php echo htmlspecialchars(addslashes($prod->nom_produit)); ?>', this)">
                <i class="bi bi-cart-plus"></i>
                <span><?= t('add_to_cart') ?></span>
            </button>
            <button class="btn-wishlist <?php echo (isset($prod->user_favori) && $prod->user_favori) ? 'active' : ''; ?>"
                    onclick="toggleWishlist(<?php echo $prod->id_produit; ?>, this)"
                    title="<?= t('add_to_wishlist') ?>">
                <i class="bi <?php echo (isset($prod->user_favori) && $prod->user_favori) ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
            </button>
        </div>
    </div>
</div>