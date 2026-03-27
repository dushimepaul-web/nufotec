<?php include VIEWPATH.'includes/frontend/Header.php'; ?>
    
    <style>
        
        
        /* Header Styles - Mobile First */
        .header-gras {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1.5rem 1rem;
            color: white;
            margin-bottom: 1rem;
            margin-top: 10px;
        }
        
        .header-gras h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .header-gras p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        /* Search Bar - Mobile First */
        .search-section {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            margin: 0 0.75rem 1.5rem 0.75rem;
        }
        
        .search-input-group {
            position: relative;
            width: 100%;
        }
        
        .search-input-group input {
            height: 44px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding-left: 40px;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .search-input-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1rem;
            z-index: 10;
        }
        
        .search-input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        /* Category Filter - Mobile First (Scroll horizontal) */
        .category-filter-wrapper {
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            margin-top: 1rem;
        }
        
        .category-filter {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            padding-bottom: 8px;
            min-width: min-content;
        }
        
        .category-btn {
            padding: 6px 16px;
            border-radius: 20px;
            background: white;
            border: 1.5px solid #e0e0e0;
            color: #666;
            font-weight: 500;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .category-btn i {
            font-size: 0.75rem;
            margin-right: 4px;
        }
        
        .category-btn:hover, .category-btn.active {
            background: #667eea;
            border-color: #667eea;
            color: white;
            transform: translateY(-1px);
        }
        
        /* Per Page Selector - Mobile First */
        .per-page-selector {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            margin-top: 1rem;
        }
        
        .per-page-selector label {
            margin: 0;
            color: #666;
            font-size: 0.8rem;
        }
        
        .per-page-selector select {
            padding: 5px 8px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background: white;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        /* Product Card - Mobile First */
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:active {
            transform: scale(0.98);
        }
        
        .product-image {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            background: #f5f5f5;
        }
        
        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.65rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .product-info {
            padding: 0.75rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5rem;
        }
        
        .product-title a {
            color: #333;
            text-decoration: none;
        }
        
        .product-description {
            color: #777;
            font-size: 0.75rem;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        
        .product-price {
            font-size: 1rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.75rem;
        }
        
        .btn-detail {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-detail:active {
            transform: scale(0.96);
        }
        
        /* Empty State - Mobile First */
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            background: white;
            border-radius: 12px;
            margin: 1rem;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 0.75rem;
        }
        
        .empty-state h3 {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            font-size: 0.85rem;
            color: #999;
        }
        
        /* Loading Spinner */
        .loading-spinner {
            text-align: center;
            padding: 2rem;
            display: none;
        }
        
        /* Pagination - Mobile First */
        .pagination-container {
            margin: 1rem 0;
        }
        
        .pagination {
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
            padding: 0 0.5rem;
        }
        
        .page-item .page-link {
            border-radius: 8px;
            color: #667eea;
            border: 1px solid #e0e0e0;
            padding: 6px 10px;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
        }
        
        .page-item .page-link:active {
            transform: scale(0.95);
        }
        
        /* Results info */
        .results-info {
            color: #666;
            font-size: 0.8rem;
            margin-top: 0.75rem;
            text-align: center;
        }
        
        /* Footer */
        footer {
            background: #2d3748;
            color: white;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.8rem;
        }
        
        /* Grid adjustments - Mobile First */
        .row {
            margin: 0 0.5rem;
        }
        
        .col-6 {
            padding: 0 0.5rem;
        }
        
        /* ==================== */
        /* Tablette et Desktop */
        /* ==================== */
        
        @media (min-width: 768px) {
            .header-gras {
                padding: 2rem 0;
                margin-bottom: 2rem;
            }
            
            .header-gras h1 {
                font-size: 2.2rem;
            }
            
            .header-gras p {
                font-size: 1rem;
            }
            
            .search-section {
                margin: 0 0 2rem 0;
                padding: 1.5rem;
            }
            
            .category-filter-wrapper {
                overflow-x: visible;
                margin-top: 0;
            }
            
            .category-filter {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
            
            .category-btn {
                padding: 8px 20px;
                font-size: 0.85rem;
            }
            
            .per-page-selector {
                justify-content: flex-end;
                margin-top: 0;
            }
            
            .product-info {
                padding: 1rem;
            }
            
            .product-title {
                font-size: 1rem;
                min-height: 3rem;
            }
            
            .product-description {
                font-size: 0.8rem;
                -webkit-line-clamp: 3;
            }
            
            .product-price {
                font-size: 1.1rem;
            }
            
            .btn-detail {
                padding: 10px 16px;
                font-size: 0.85rem;
            }
            
            .row {
                margin: 0;
            }
            
            .col-6 {
                padding: 0 0.75rem;
            }
        }
        
        @media (min-width: 1024px) {
            .header-gras h1 {
                font-size: 2.5rem;
            }
            
            .header-gras p {
                font-size: 1.1rem;
            }
            
            .product-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            
            .product-card:hover .product-image img {
                transform: scale(1.05);
            }
            
            .btn-detail:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }
        }
        
        /* Améliorations pour le tactile */
        @media (hover: none) and (pointer: coarse) {
            .category-btn:active {
                transform: scale(0.96);
            }
            
            .btn-detail:active {
                transform: scale(0.96);
            }
        }
        
        /* Animation de chargement */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .product-card {
            animation: fadeInUp 0.3s ease-out;
        }
    </style>


<!-- Header -->
<div class="header-gras">
    <div class="container-fluid px-3 px-md-4">
        <div class="row">
            <div class="col-12 text-center">
                <i class="fas fa-store fa-2x fa-md-3x mb-2 mb-md-3"></i>
                <h1>Notre Catalogue</h1>
                <p>Découvrez notre sélection de produits de qualité</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- Search and Filter Section -->
    <div class="search-section">
        <div class="row g-2">
            <div class="col-12">
                <div class="search-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un produit..." autocomplete="off">
                </div>
            </div>
            <div class="col-12 col-md-7">
                <div class="category-filter-wrapper">
                    <div class="category-filter" id="categoryFilter">
                        <button class="category-btn active" data-category="all">
                            <i class="fas fa-th-large"></i> Tous
                        </button>
                        <?php foreach ($categories as $cat): ?>
                            <button class="category-btn" data-category="<?= $cat['id'] ?>">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($cat['name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-5">
                <div class="per-page-selector">
                    <label><i class="fas fa-eye"></i> Afficher :</label>
                    <select id="perPageSelect">
                        <option value="12">12</option>
                        <option value="24">24</option>
                        <option value="36">36</option>
                        <option value="48">48</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="row g-2 g-md-3" id="productsGrid"></div>
    
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="pagination-container" id="paginationContainer" style="display: none;">
        <nav aria-label="Page navigation">
            <ul class="pagination" id="pagination"></ul>
        </nav>
        <div class="results-info" id="resultsInfo"></div>
    </div>
</div>

<!-- Footer -->
<footer>
    <div class="container-fluid px-3">
        <div class="text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> Notre Boutique. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    let currentCategory = 'all';
    let currentSearch = '';
    let currentPage = 1;
    let perPage = 12;
    let totalPages = 1;
    let searchTimeout;
    
    // Charger les produits au chargement
    loadProducts();
    
    // Gestionnaire de recherche avec debounce
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        currentSearch = $(this).val();
        currentPage = 1;
        
        searchTimeout = setTimeout(function() {
            loadProducts();
        }, 500);
    });
    
    // Gestionnaire de catégorie
    $('.category-btn').on('click', function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        currentCategory = $(this).data('category');
        currentPage = 1;
        loadProducts();
    });
    
    // Gestionnaire du nombre d'éléments par page
    $('#perPageSelect').on('change', function() {
        perPage = parseInt($(this).val());
        currentPage = 1;
        loadProducts();
    });
    
    // Fonction de chargement des produits
    function loadProducts() {
        $('#loadingSpinner').show();
        $('#productsGrid').hide();
        $('#paginationContainer').hide();
        
        $.ajax({
            url: '<?= base_url("products/get_products_ajax") ?>',
            method: 'GET',
            data: {
                category: currentCategory,
                search: currentSearch,
                page: currentPage,
                per_page: perPage
            },
            dataType: 'json',
            success: function(response) {
                displayProducts(response.products);
                totalPages = response.total_pages;
                updatePagination(response.current_page, response.total_pages);
                updateResultsInfo(response.total_products, response.current_page, response.per_page);
                $('#loadingSpinner').hide();
                $('#productsGrid').fadeIn(200);
                if (response.total_pages > 1) {
                    $('#paginationContainer').show();
                } else {
                    $('#paginationContainer').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors du chargement des produits:', error);
                $('#loadingSpinner').hide();
                $('#productsGrid').html(`
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            <h3>Erreur de chargement</h3>
                            <p>Une erreur est survenue. Veuillez réessayer.</p>
                        </div>
                    </div>
                `).fadeIn(200);
            }
        });
    }
    
    // Affichage des produits
    function displayProducts(products) {
        const productsGrid = $('#productsGrid');
        productsGrid.empty();
        
        if (!products || products.length === 0) {
            productsGrid.html(`
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>Aucun produit trouvé</h3>
                        <p>Essayez de modifier votre recherche ou consultez d'autres catégories.</p>
                    </div>
                </div>
            `);
            return;
        }
        
        products.forEach(function(product) {
            const badgeHtml = product.in_vedette == 1 ? 
                `<div class="product-badge"><i class="fas fa-star"></i> Vedette</div>` : '';
            
            const productCard = `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-image">
                            ${badgeHtml}
                            <img src="${product.image}" alt="${escapeHtml(product.title)}" loading="lazy" onerror="this.src='<?= base_url('attachments/Products/default-product.png') ?>'">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="<?= base_url('products/detail/') ?>${product.slug}">
                                    ${escapeHtml(product.title)}
                                </a>
                            </h3>
                            <div class="product-description">
                                ${escapeHtml(product.description)}
                            </div>
                            <div class="product-price">
                                ${escapeHtml(product.price)}
                            </div>
                            <a href="<?= base_url('products/detail/') ?>${product.slug}" class="btn-detail">
                                <i class="fas fa-eye me-1"></i> Voir détails
                            </a>
                        </div>
                    </div>
                </div>
            `;
            productsGrid.append(productCard);
        });
    }
    
    // Mise à jour de la pagination
    function updatePagination(currentPage, totalPages) {
        const pagination = $('#pagination');
        pagination.empty();
        
        if (totalPages <= 1) return;
        
        // Bouton Précédent
        const prevDisabled = currentPage <= 1 ? 'disabled' : '';
        pagination.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" ${prevDisabled ? 'tabindex="-1"' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `);
        
        // Calcul des pages à afficher
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            pagination.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
                ${startPage > 2 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
            `);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            pagination.append(`
                <li class="page-item ${activeClass}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        
        if (endPage < totalPages) {
            pagination.append(`
                ${endPage < totalPages - 1 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                </li>
            `);
        }
        
        // Bouton Suivant
        const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
        pagination.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" ${nextDisabled ? 'tabindex="-1"' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `);
        
        // Gestionnaire de clic sur la pagination
        $('.page-link').on('click', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                currentPage = page;
                loadProducts();
                // Scroll vers le haut avec animation douce
                $('html, body').animate({
                    scrollTop: $('#productsGrid').offset().top - 80
                }, 300);
            }
        });
    }
    
    // Mise à jour des informations de résultats
    function updateResultsInfo(total, currentPage, perPage) {
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, total);
        $('#resultsInfo').html(`<i class="fas fa-chart-line me-1"></i> Affichage de <strong>${start}</strong> à <strong>${end}</strong> sur <strong>${total}</strong> produits`);
    }
    
    // Échapper les caractères HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>