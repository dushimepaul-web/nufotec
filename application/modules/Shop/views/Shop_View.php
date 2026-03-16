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

    /* ===== MOBILE FIRST BASE ===== */
    * {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumb-wrapper {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 15px 0;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .breadcrumb-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1));
        transform: skewX(-20deg);
    }

    .breadcrumb {
        margin: 0;
        font-size: 13px;
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 6px;
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

    /* ===== MOBILE BOTTOM NAV ===== */
    .mobile-bottom-nav {
        display: flex;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        z-index: 1030;
        padding: 8px 0;
        border-top: 1px solid var(--gray-light);
    }

    .mobile-nav-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 8px;
        color: var(--gray);
        text-decoration: none;
        font-size: 11px;
        font-weight: 500;
        transition: var(--transition);
        gap: 4px;
    }

    .mobile-nav-item i {
        font-size: 20px;
    }

    .mobile-nav-item.active,
    .mobile-nav-item:hover {
        color: var(--primary);
    }

    .mobile-nav-item .badge {
        position: absolute;
        top: 4px;
        right: calc(50% - 20px);
        background: var(--accent);
        color: var(--primary-dark);
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 700;
    }

    /* ===== SEARCH BAR MOBILE ===== */
    .mobile-search-bar {
        display: flex;
        gap: 10px;
        padding: 15px;
        background: white;
        margin-bottom: 15px;
        border-radius: 15px;
        box-shadow: var(--shadow);
    }

    .mobile-search-input {
        flex: 1;
        border: 2px solid var(--gray-light);
        border-radius: 25px;
        padding: 12px 20px;
        font-size: 16px; /* Prevents zoom on iOS */
        transition: var(--transition);
    }

    .mobile-search-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    }

    .mobile-search-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--accent);
        border: none;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    /* ===== MOBILE FILTER CHIPS ===== */
    .mobile-filter-chips {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px 15px;
        margin-bottom: 15px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .mobile-filter-chips::-webkit-scrollbar {
        display: none;
    }

    .filter-chip {
        flex-shrink: 0;
        padding: 10px 20px;
        background: white;
        border: 2px solid var(--gray-light);
        border-radius: 25px;
        font-size: 14px;
        font-weight: 500;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
        white-space: nowrap;
    }

    .filter-chip.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .filter-chip i {
        font-size: 16px;
    }

    /* ===== SIDEBAR (DESKTOP) ===== */
    .search-sidebar {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
    }

    .search-sidebar-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-sidebar-form {
        position: relative;
    }

    .search-sidebar-input {
        width: 100%;
        padding: 12px 45px 12px 15px;
        border: 2px solid var(--gray-light);
        border-radius: 25px;
        font-size: 14px;
        transition: var(--transition);
    }

    .search-sidebar-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    }

    .search-sidebar-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--accent);
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .search-sidebar-btn:hover {
        background: var(--accent-hover);
        transform: translateY(-50%) scale(1.1);
    }

    .categories-wrapper {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow);
        position: sticky;
        top: 100px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .categories-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 20px;
        font-weight: 600;
        font-size: 16px;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
    }

    .categories-header::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3));
        transform: skewX(-20deg) translateX(20px);
    }

    .categories-list {
        list-style: none;
        padding: 10px;
        margin: 0;
        max-height: 500px;
        overflow-y: auto;
    }

    .categories-list::-webkit-scrollbar {
        width: 6px;
    }

    .categories-list::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 3px;
    }

    .category-item {
        margin: 5px 0;
    }

    .category-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: var(--dark);
        text-decoration: none;
        border-radius: 12px;
        transition: var(--transition);
        font-weight: 500;
        font-size: 13px;
        position: relative;
        overflow: hidden;
    }

    .category-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: var(--accent);
        transform: scaleY(0);
        transition: var(--transition);
        border-radius: 0 2px 2px 0;
    }

    .category-link:hover::before,
    .category-link.active::before {
        transform: scaleY(1);
    }

    .category-link:hover,
    .category-link.active {
        background: linear-gradient(90deg, rgba(15, 76, 58, 0.08) 0%, transparent 100%);
        color: var(--primary);
    }

    .category-link.active {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.15) 0%, transparent 100%);
        font-weight: 600;
        color: var(--primary-dark);
    }

    .category-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 76, 58, 0.1);
        border-radius: 8px;
        margin-right: 10px;
        color: var(--primary);
        font-size: 16px;
        transition: var(--transition);
        flex-shrink: 0;
    }

    .category-link:hover .category-icon,
    .category-link.active .category-icon {
        background: var(--accent);
        color: var(--primary-dark);
        transform: scale(1.1) rotate(5deg);
    }

    .category-name {
        flex: 1;
        line-height: 1.3;
    }

    .category-code {
        font-size: 10px;
        background: var(--light);
        padding: 3px 8px;
        border-radius: 12px;
        color: var(--gray);
        font-weight: 600;
        margin-left: 5px;
    }

    .category-link.active .category-code {
        background: var(--accent);
        color: var(--primary-dark);
    }

    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-top: 20px;
        box-shadow: var(--shadow);
    }

    .filter-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--accent-light);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-option {
        margin-bottom: 12px;
    }

    .filter-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--accent);
        margin-right: 10px;
        cursor: pointer;
    }

    .filter-label {
        cursor: pointer;
        font-size: 13px;
        color: var(--dark);
        display: flex;
        align-items: center;
        transition: var(--transition);
    }

    .filter-label:hover {
        color: var(--primary);
    }

    /* ===== SECTION HEADER ===== */
    .section-header {
        background: white;
        border-radius: 15px;
        padding: 15px 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .section-title-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .product-count {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .sort-dropdown .btn {
        border: 2px solid var(--gray-light);
        border-radius: 25px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        color: var(--dark);
        background: white;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sort-dropdown .btn:hover {
        border-color: var(--accent);
        color: var(--primary);
    }

    .sort-dropdown .dropdown-menu {
        border: none;
        box-shadow: var(--shadow-lg);
        border-radius: 12px;
        padding: 10px;
    }

    .sort-dropdown .dropdown-item {
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 13px;
        transition: var(--transition);
    }

    .sort-dropdown .dropdown-item:hover {
        background: rgba(212, 175, 55, 0.1);
        color: var(--primary);
    }

    /* ===== PRODUCT GRID MOBILE FIRST ===== */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding: 0 10px;
    }

    .product-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition-bounce);
        position: relative;
        border: 1px solid rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
    }

    .product-card:active {
        transform: scale(0.98);
    }

    .product-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        z-index: 10;
        box-shadow: var(--shadow);
    }

    .product-badge.new {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .product-badge.sale {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
        color: white;
    }

    .product-image-wrapper {
        height: 160px;
        padding: 15px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .product-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: var(--transition-bounce);
    }

    .product-info {
        padding: 15px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-category {
        font-size: 10px;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .product-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 36px;
    }

    .product-title a {
        color: inherit;
        text-decoration: none;
    }

    .product-price-wrapper {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 12px;
        margin-top: auto;
    }

    .price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        flex-wrap: wrap;
    }

    .product-price {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary);
    }

    .product-price .currency {
        font-size: 12px;
        font-weight: 500;
    }

    .old-price {
        font-size: 12px;
        color: var(--gray);
        text-decoration: line-through;
    }

    .product-actions {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
    }

    .btn-add-cart {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 12px;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 40px;
    }

    .btn-add-cart:active {
        transform: scale(0.95);
    }

    .btn-add-cart.added {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .btn-wishlist {
        width: 40px;
        height: 40px;
        background: var(--light);
        border: 2px solid var(--gray-light);
        border-radius: 10px;
        color: var(--gray);
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .btn-wishlist:active {
        transform: scale(0.9);
    }

    .btn-wishlist.active {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    /* ===== TABLET AND UP ===== */
    @media (min-width: 576px) {
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            padding: 0;
        }

        .product-image-wrapper {
            height: 200px;
            padding: 20px;
        }

        .product-title {
            font-size: 15px;
            min-height: 40px;
        }

        .product-price {
            font-size: 18px;
        }

        .btn-add-cart {
            font-size: 13px;
            padding: 12px;
        }
    }

    /* ===== DESKTOP ===== */
    @media (min-width: 992px) {
        .mobile-bottom-nav,
        .mobile-search-bar,
        .mobile-filter-chips {
            display: none;
        }

        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .section-title {
            font-size: 24px;
        }

        .section-header {
            padding: 20px 25px;
        }
    }

    /* ===== MOBILE ONLY (< 992px) ===== */
    @media (max-width: 991.98px) {
        .search-sidebar,
        .categories-wrapper,
        .filter-section {
            display: none;
        }

        .container {
            padding-bottom: 80px;
        }

        .section-header {
            margin: 0 10px 15px;
        }

        .col-lg-9 {
            padding: 0;
        }
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
        margin: 0 10px;
    }

    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--accent-light) 0%, var(--accent) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 50px;
        color: var(--primary-dark);
        box-shadow: var(--shadow-glow);
    }

    .empty-state h4 {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 10px;
    }

    .empty-state p {
        color: var(--gray);
        font-size: 14px;
        margin-bottom: 20px;
    }

    .btn-back {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        border: none;
        padding: 12px 25px;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
    }

    /* ===== PAGINATION ===== */
    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        padding: 0 10px;
    }

    .pagination-custom {
        display: flex;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-custom .page-item .page-link {
        border: none;
        background: white;
        color: var(--primary);
        border-radius: 10px;
        padding: 10px 15px;
        font-weight: 500;
        transition: var(--transition);
        box-shadow: var(--shadow);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        font-size: 14px;
    }

    .pagination-custom .page-item .page-link:hover {
        background: var(--accent);
        color: var(--primary-dark);
    }

    .pagination-custom .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
    }

    .pagination-custom .page-item.disabled .page-link {
        opacity: 0.5;
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.95);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .loading-overlay.active {
        display: flex;
    }

    .spinner-ring {
        width: 50px;
        height: 50px;
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
        bottom: 90px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        width: 90%;
        max-width: 400px;
    }

    .custom-toast {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: var(--shadow-xl);
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .custom-toast.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .custom-toast.error {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
    }

    /* ===== HERO SECTION ===== */
    .hero-section {
        position: relative;
        height: 220px;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        margin-bottom: 20px;
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
        padding: 20px;
    }

    .hero-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.2;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1rem;
        font-weight: 500;
        color: var(--accent);
        margin-bottom: 10px;
    }

    .hero-text {
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
        opacity: 0.95;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        background: var(--accent);
        color: var(--primary-dark);
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    @media (min-width: 768px) {
        .hero-section {
            height: 280px;
        }
        .hero-title {
            font-size: 2rem;
        }
        .hero-subtitle {
            font-size: 1.2rem;
        }
    }

    /* ===== WORKFLOW SECTION ===== */
    .workflow-section {
        background: white;
        border-radius: 20px;
        padding: 25px 20px;
        box-shadow: var(--shadow-lg);
        margin: 30px 10px !important;
        position: relative;
        overflow: hidden;
    }

    /* ===== FLOATING CART BUTTON ===== */
    .cart-float-btn {
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: var(--shadow-xl);
        cursor: pointer;
        z-index: 998;
        transition: var(--transition-bounce);
        border: none;
    }

    .cart-float-btn:active {
        transform: scale(0.9);
    }

    .cart-badge-float {
        position: absolute;
        top: -4px;
        right: -4px;
        background: var(--accent);
        color: var(--primary-dark);
        font-size: 11px;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow);
        padding: 0 5px;
    }

    @media (min-width: 992px) {
        .cart-float-btn {
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
        }
    }

    /* ===== CART ITEMS ===== */
    .cart-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-light);
    }

    .cart-item-image {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        background: var(--light);
        border-radius: 8px;
        overflow: hidden;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .cart-item-details {
        flex: 1;
        min-width: 0;
    }

    .cart-item-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cart-item-price {
        font-size: 13px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 6px;
    }

    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        border: 1px solid var(--gray-light);
        border-radius: 20px;
        overflow: hidden;
    }

    .quantity-control button {
        width: 28px;
        height: 28px;
        background: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
    }

    .quantity-control input {
        width: 35px;
        height: 28px;
        border: none;
        text-align: center;
        font-weight: 600;
        font-size: 13px;
    }

    .btn-remove {
        background: none;
        border: none;
        color: var(--gray);
        font-size: 16px;
        padding: 4px;
    }

    .item-total {
        font-size: 13px;
        font-weight: 700;
        color: var(--primary);
        margin-left: auto;
        align-self: center;
    }

    /* ===== PULL TO REFRESH ===== */
    .pull-to-refresh {
        text-align: center;
        padding: 20px;
        color: var(--gray);
        font-size: 14px;
        display: none;
    }

    .pull-to-refresh.visible {
        display: block;
    }

    .pull-to-refresh i {
        display: block;
        font-size: 24px;
        margin-bottom: 8px;
        animation: bounce 1s infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
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

<!-- ===== MOBILE SEARCH BAR ===== -->
<div class="mobile-search-bar d-lg-none">
    <input type="text" 
           class="mobile-search-input" 
           placeholder="Search products..."
           id="mobileSearchInput"
           value="<?php echo isset($query) ? htmlspecialchars($query) : ''; ?>">
    <button class="mobile-search-btn" onclick="handleMobileSearch()">
        <i class="bi bi-search"></i>
    </button>
</div>

<!-- ===== MOBILE FILTER CHIPS ===== -->
<div class="mobile-filter-chips d-lg-none">
    <button class="filter-chip <?php echo !isset($categorie_active) || !$categorie_active ? 'active' : ''; ?>" onclick="filterByCategory('all')">
        <i class="bi bi-grid"></i> All
    </button>
    <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
        <button class="filter-chip <?php echo (isset($categorie_active) && $categorie_active == $cat->id_categorie) ? 'active' : ''; ?>" onclick="filterByCategory('<?php echo $cat->id_categorie; ?>')">
            <i class="bi bi-<?php echo $cat->icone ?: 'box-seam'; ?>"></i> <?php echo character_limiter($cat->nom_categorie, 15); ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- ===== TOAST CONTAINER ===== -->
<div class="toast-container" id="toastContainer"></div>

<!-- ===== LOADING OVERLAY ===== -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-ring"></div>
</div>

<!-- ===== PULL TO REFRESH ===== -->
<div class="pull-to-refresh" id="pullToRefresh">
    <i class="bi bi-arrow-down"></i>
    Pull to refresh
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="container py-3 py-lg-4">
    <div class="row">
        <!-- Sidebar (Desktop Only) -->
        <div class="col-lg-3 mb-4 d-none d-lg-block">
            <div class="search-sidebar">
                <div class="search-sidebar-title">
                    <i class="bi bi-search"></i> Search
                </div>
                <form action="<?php echo base_url('boutique/recherche'); ?>" method="GET" class="search-sidebar-form" id="desktopSearchForm">
                    <input type="text" 
                           name="q" 
                           class="search-sidebar-input" 
                           placeholder="Product, category..."
                           value="<?php echo isset($query) ? htmlspecialchars($query) : ''; ?>"
                           required>
                    <button type="submit" class="search-sidebar-btn">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

            <div class="categories-wrapper">
                <div class="categories-header">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                    CATEGORIES
                </div>
                <ul class="categories-list">
                    <li class="category-item">
                        <a href="<?php echo base_url('boutique'); ?>" 
                           class="category-link <?php echo !isset($categorie_active) || !$categorie_active ? 'active' : ''; ?>">
                            <span class="category-icon"><i class="bi bi-grid"></i></span>
                            <span class="category-name">All Products</span>
                            <span class="category-code">ALL</span>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): 
                        $is_active = (isset($categorie_active) && $categorie_active == $cat->id_categorie);
                        $icon = $cat->icone ?: 'box-seam';
                    ?>
                        <li class="category-item">
                            <a href="<?php echo base_url('boutique/categorie/' . $cat->id_categorie); ?>" 
                               class="category-link <?php echo $is_active ? 'active' : ''; ?>"
                               title="<?php echo htmlspecialchars($cat->nom_categorie); ?>">
                                <span class="category-icon"><i class="bi bi-<?php echo $icon; ?>"></i></span>
                                <span class="category-name"><?php echo character_limiter($cat->nom_categorie, 25); ?></span>
                                <span class="category-code"><?php echo $cat->code_categorie; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-section">
                <h5 class="filter-title">
                    <i class="bi bi-funnel-fill"></i> FILTERS
                </h5>
                <div class="filter-option">
                    <label class="filter-label">
                        <input type="checkbox" class="filter-checkbox" id="filterPromo" onchange="applyFilter('promo')">
                        <span>On Sale</span>
                    </label>
                </div>
                <div class="filter-option">
                    <label class="filter-label">
                        <input type="checkbox" class="filter-checkbox" id="filterVedette" onchange="applyFilter('featured')">
                        <span>Featured</span>
                    </label>
                </div>
                <div class="filter-option">
                    <label class="filter-label">
                        <input type="checkbox" class="filter-checkbox" id="filterNew" onchange="applyFilter('new')">
                        <span>New Arrivals</span>
                    </label>
                </div>
                <div class="filter-option">
                    <label class="filter-label">
                        <input type="checkbox" class="filter-checkbox" id="filterStock" onchange="applyFilter('stock')">
                        <span>In Stock Only</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="col-lg-9">
            <div class="section-header">
                <div class="section-title-wrapper">
                    <h2 class="section-title">
                        <?php if (isset($categorie_info) && $categorie_info): ?>
                            <i class="bi bi-<?php echo $categorie_info->icone ?: 'collection'; ?>"></i>
                            <?php echo $categorie_info->nom_categorie; ?>
                        <?php elseif (isset($query) && $query): ?>
                            <i class="bi bi-search"></i>
                            Results for "<?php echo htmlspecialchars($query); ?>"
                        <?php else: ?>
                            <i class="bi bi-shop"></i>
                            Our Products
                        <?php endif; ?>
                        <span class="product-count"><?php echo $total_produits; ?> items</span>
                    </h2>
                </div>
                
                <div class="sort-dropdown dropdown">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down"></i>
                        <span class="d-none d-sm-inline">Sort by</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-sort="default" onclick="sortProducts('default')"><i class="bi bi-arrow-down-up me-2"></i>Default</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="prix_asc" onclick="sortProducts('price_asc')"><i class="bi bi-sort-numeric-down me-2"></i>Price: Low to High</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="prix_desc" onclick="sortProducts('price_desc')"><i class="bi bi-sort-numeric-up me-2"></i>Price: High to Low</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="nom" onclick="sortProducts('name')"><i class="bi bi-sort-alpha-down me-2"></i>Name: A-Z</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="vedette" onclick="sortProducts('popular')"><i class="bi bi-star-fill me-2"></i>Most Popular</a></li>
                    </ul>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="product-grid" id="productsGrid">
                <?php if (!empty($produits)): ?>
                    <?php foreach ($produits as $prod): ?>
                        <?php $this->load->view('partials/product_card', ['prod' => $prod]); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state w-100">
                        <div class="empty-state-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h4>No products found</h4>
                        <p>Try adjusting your search or filters to find what you're looking for.</p>
                        <a href="<?php echo base_url('boutique'); ?>" class="btn-back">
                            <i class="bi bi-arrow-left"></i> View All Products
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper" id="paginationWrapper">
                <?php if (isset($pagination) && !empty($pagination)): ?>
                    <?php echo $pagination; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== WORKFLOW SECTION ===== -->
<div id="workflowContainer">
    <?php if (isset($categorie_info) && $categorie_info && isset($workflow) && !empty($workflow)): ?>
        <?php $this->load->view('partials/workflow_section', ['categorie_info' => $categorie_info, 'workflow' => $workflow]); ?>
    <?php endif; ?>
</div>

<!-- ===== PANIER (OFFCANVAS) ===== -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
    <div class="offcanvas-header" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: white;">
        <h5 class="offcanvas-title" id="offcanvasCartLabel">
            <i class="bi bi-cart-fill me-2"></i> My Cart
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="cartOffcanvasBody">
        <div class="text-center py-5" id="cartLoading">
            <div class="spinner-ring"></div>
            <p class="mt-3">Loading cart...</p>
        </div>
        <div id="cartContent" style="display: none;"></div>
        <div id="cartEmpty" class="text-center py-5" style="display: none;">
            <i class="bi bi-cart-x" style="font-size: 60px; color: var(--gray-light);"></i>
            <h5 class="mt-3">Your cart is empty</h5>
            <p class="text-muted">Discover our products and add them to your cart.</p>
            <button class="btn btn-primary" data-bs-dismiss="offcanvas">Continue Shopping</button>
        </div>
        <div id="cartFooter" style="display: none; position: sticky; bottom: 0; background: white; padding-top: 15px; border-top: 1px solid var(--gray-light);">
            <div class="d-flex justify-content-between mb-3">
                <span class="fw-bold">Total:</span>
                <span class="fw-bold fs-5" style="color: var(--primary);" id="cartTotal">0 USD</span>
            </div>
            <button class="btn btn-block w-100 py-3" style="background: var(--accent); color: var(--primary-dark); font-weight: 600; border-radius: 12px;" onclick="window.location.href='<?php echo base_url('commande'); ?>'">
                <i class="bi bi-bag-check me-2"></i> Checkout Now
            </button>
        </div>
    </div>
</div>

<!-- ===== FLOATING CART BUTTON ===== -->
<div class="cart-float-btn" onclick="openCartSafe()" id="cartFloatBtn">
    <i class="bi bi-cart-fill"></i>
    <span class="cart-badge-float" id="cartFloatBadge">0</span>
</div>

<!-- ===== MOBILE BOTTOM NAVIGATION ===== -->
<nav class="mobile-bottom-nav d-lg-none">
    <a href="<?php echo base_url(); ?>" class="mobile-nav-item">
        <i class="bi bi-house-fill"></i>
        <span>Home</span>
    </a>
    <a href="<?php echo base_url('boutique'); ?>" class="mobile-nav-item active">
        <i class="bi bi-shop"></i>
        <span>Shop</span>
    </a>
    <a href="javascript:void(0)" class="mobile-nav-item" onclick="openCartSafe()">
        <i class="bi bi-cart-fill"></i>
        <span>Cart</span>
        <span class="badge" id="mobileCartBadge">0</span>
    </a>
    <a href="<?php echo base_url('compte'); ?>" class="mobile-nav-item">
        <i class="bi bi-person-fill"></i>
        <span>Account</span>
    </a>
</nav>

<script>
// ============================================
// SHOP.JS - Mobile-first shop functionality
// ============================================

(function() {
    'use strict';
    
    // Prevent double loading
    if (window.AGF_Shop_Loaded) {
        return;
    }
    window.AGF_Shop_Loaded = true;

    // Configuration
    var BASE_URL = '<?php echo base_url(); ?>';
    
    // State
    var currentCategory = '<?php echo isset($categorie_active) && $categorie_active ? $categorie_active : 'all'; ?>';
    var currentSearch = '<?php echo isset($query) ? addslashes($query) : ''; ?>';
    var currentSort = 'default';
    var currentFilters = [];
    var currentPage = 0;
    var totalPages = 1;
    var touchStartY = 0;
    var isRefreshing = false;

    // ============================================
    // SAFE CART OPENING
    // ============================================
    
    window.openCartSafe = function() {
        console.log('openCartSafe called');
        if (typeof AGF_Panier !== 'undefined' && typeof AGF_Panier.openCart === 'function') {
            AGF_Panier.openCart();
        } else {
            console.error('AGF_Panier not available');
            // Fallback to Bootstrap
            try {
                var offcanvasElement = document.getElementById('offcanvasCart');
                if (offcanvasElement && typeof bootstrap !== 'undefined') {
                    var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                    if (!bsOffcanvas) {
                        bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);
                    }
                    bsOffcanvas.show();
                }
            } catch (e) {
                console.error('Fallback error:', e);
                showToast('Cart not available', 'error');
            }
        }
    };

    // ============================================
    // MOBILE SEARCH
    // ============================================
    
    window.handleMobileSearch = function() {
        var input = document.getElementById('mobileSearchInput');
        if (input && input.value.trim()) {
            currentSearch = input.value.trim();
            currentPage = 0;
            loadProducts();
        }
    };

    // ============================================
    // MOBILE FILTER CHIPS
    // ============================================
    
    window.filterByCategory = function(catId) {
        currentCategory = catId;
        currentPage = 0;
        
        // Update active state on chips
        var chips = document.querySelectorAll('.filter-chip');
        for (var i = 0; i < chips.length; i++) {
            chips[i].classList.remove('active');
        }
        event.target.closest('.filter-chip').classList.add('active');
        
        loadProducts();
    };

    // ============================================
    // FILTERS
    // ============================================
    
    window.applyFilter = function(filterType) {
        var index = currentFilters.indexOf(filterType);
        if (index > -1) {
            currentFilters.splice(index, 1);
        } else {
            currentFilters.push(filterType);
        }
        currentPage = 0;
        loadProducts();
    };

    // ============================================
    // SORTING
    // ============================================
    
    window.sortProducts = function(sortType) {
        currentSort = sortType;
        currentPage = 0;
        loadProducts();
    };

    // ============================================
    // PAGINATION
    // ============================================
    
    window.goToPage = function(page) {
        if (page < 0 || page >= totalPages) return;
        currentPage = page;
        loadProducts();
        // Scroll to top of products
        var productsGrid = document.getElementById('productsGrid');
        if (productsGrid) {
            productsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ============================================
    // LOADING UTILITIES
    // ============================================
    
    function showLoading() {
        var el = document.getElementById('loadingOverlay');
        if (el && el.classList) {
            el.classList.add('active');
        }
    }

    function hideLoading() {
        var el = document.getElementById('loadingOverlay');
        if (el && el.classList) {
            el.classList.remove('active');
        }
    }

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    
    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('toastContainer');
        if (!container) return;
        
        var toast = document.createElement('div');
        toast.className = 'custom-toast ' + type;
        
        var icon = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
        var title = type === 'success' ? 'Success' : 'Error';
        
        toast.innerHTML = '<i class="bi bi-' + icon + ' fs-5"></i>' +
            '<div><div class="fw-bold">' + title + '</div>' +
            '<div style="font-size: 14px;">' + message + '</div></div>';

        container.appendChild(toast);

        setTimeout(function() {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }

    // ============================================
    // PRODUCT LOADING
    // ============================================
    
    function loadProducts() {
        showLoading();
        
        var params = new URLSearchParams();
        params.append('categorie_id', currentCategory);
        params.append('search', currentSearch);
        params.append('sort', currentSort);
        params.append('page', currentPage);
        for (var i = 0; i < currentFilters.length; i++) {
            params.append('filters[]', currentFilters[i]);
        }

        fetch(BASE_URL + 'boutique/ajax_get_products', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded', 
                'X-Requested-With': 'XMLHttpRequest' 
            },
            body: params.toString()
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                var grid = document.getElementById('productsGrid');
                if (grid) {
                    grid.innerHTML = data.html;
                }
                
                var workflow = document.getElementById('workflowContainer');
                if (workflow) {
                    workflow.innerHTML = data.workflow_html || '';
                }
                
                totalPages = data.total_pages || 1;
                
                var count = document.querySelector('.product-count');
                if (count) {
                    count.textContent = data.total + ' items';
                }
                
                updatePagination(data.total);
                updateCategoryTitle();
            } else {
                showToast('Error loading products', 'error');
            }
        })
        .catch(function(err) {
            console.error('Error:', err);
            showToast('Connection error', 'error');
        })
        .finally(function() {
            hideLoading();
            isRefreshing = false;
            var pullRefresh = document.getElementById('pullToRefresh');
            if (pullRefresh) {
                pullRefresh.classList.remove('visible');
            }
        });
    }

    function updatePagination(total) {
        var wrapper = document.getElementById('paginationWrapper');
        if (!wrapper || totalPages <= 1) {
            if (wrapper) wrapper.innerHTML = '';
            return;
        }
        
        var html = '<ul class="pagination-custom">';
        
        if (currentPage > 0) {
            html += '<li class="page-item"><a class="page-link" href="#" onclick="goToPage(' + (currentPage - 1) + '); return false;"><i class="bi bi-chevron-left"></i></a></li>';
        }
        
        var maxPages = 5;
        var start = Math.max(0, currentPage - Math.floor(maxPages / 2));
        var end = Math.min(totalPages - 1, start + maxPages - 1);
        
        if (end - start + 1 < maxPages) {
            start = Math.max(0, end - maxPages + 1);
        }
        
        for (var i = start; i <= end; i++) {
            html += '<li class="page-item ' + (i === currentPage ? 'active' : '') + '"><a class="page-link" href="#" onclick="goToPage(' + i + '); return false;">' + (i + 1) + '</a></li>';
        }
        
        if (currentPage < totalPages - 1) {
            html += '<li class="page-item"><a class="page-link" href="#" onclick="goToPage(' + (currentPage + 1) + '); return false;"><i class="bi bi-chevron-right"></i></a></li>';
        }
        
        html += '</ul>';
        wrapper.innerHTML = html;
    }

    function updateCategoryTitle() {
        var titleEl = document.querySelector('.section-title');
        if (!titleEl) return;
        
        if (currentSearch) {
            titleEl.innerHTML = '<i class="bi bi-search"></i> Results for "' + escapeHtml(currentSearch) + '" <span class="product-count">' + document.querySelector('.product-count').textContent + '</span>';
        }
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================
    // PULL TO REFRESH (MOBILE)
    // ============================================
    
    function initPullToRefresh() {
        var container = document.body;
        var pullRefresh = document.getElementById('pullToRefresh');
        
        container.addEventListener('touchstart', function(e) {
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        container.addEventListener('touchmove', function(e) {
            if (isRefreshing) return;
            
            var touchY = e.touches[0].clientY;
            var diff = touchY - touchStartY;
            
            if (window.scrollY === 0 && diff > 100) {
                if (pullRefresh) {
                    pullRefresh.classList.add('visible');
                }
                if (diff > 150) {
                    isRefreshing = true;
                    currentPage = 0;
                    loadProducts();
                }
            }
        }, { passive: true });
    }

    // ============================================
    // CART UPDATES
    // ============================================
    
    function updateCartBadges(count) {
        var floatBadge = document.getElementById('cartFloatBadge');
        var mobileBadge = document.getElementById('mobileCartBadge');
        
        if (floatBadge) floatBadge.textContent = count;
        if (mobileBadge) mobileBadge.textContent = count;
        
        // Hide badges if 0
        if (count === 0) {
            if (floatBadge) floatBadge.style.display = 'none';
            if (mobileBadge) mobileBadge.style.display = 'none';
        } else {
            if (floatBadge) floatBadge.style.display = 'flex';
            if (mobileBadge) mobileBadge.style.display = 'flex';
        }
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize pull to refresh on mobile
        if ('ontouchstart' in window) {
            initPullToRefresh();
        }
        
        // Mobile search input enter key
        var mobileSearchInput = document.getElementById('mobileSearchInput');
        if (mobileSearchInput) {
            mobileSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    handleMobileSearch();
                }
            });
        }
        
        // Desktop search form
        var desktopForm = document.getElementById('desktopSearchForm');
        if (desktopForm) {
            desktopForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var input = this.querySelector('input[name="q"]');
                if (input) {
                    currentSearch = input.value.trim();
                    currentPage = 0;
                    loadProducts();
                }
            });
        }
        
        // Listen for cart updates from other scripts
        document.addEventListener('cartUpdated', function(e) {
            if (e.detail && typeof e.detail.count !== 'undefined') {
                updateCartBadges(e.detail.count);
            }
        });
        
        // Initial cart badge update
        if (typeof AGF_Panier !== 'undefined' && typeof AGF_Panier.getCount === 'function') {
            updateCartBadges(AGF_Panier.getCount());
        }
    });

    // Expose necessary functions globally
    window.AGF_Shop = {
        loadProducts: loadProducts,
        updateCartBadges: updateCartBadges,
        showToast: showToast
    };

})();
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>