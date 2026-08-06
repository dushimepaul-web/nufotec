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

    /* ===== SIDEBAR ===== */
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
        padding: 20px 25px;
        margin-bottom: 25px;
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
        gap: 15px;
        flex-wrap: wrap;
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-count {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .sort-dropdown .btn {
        border: 2px solid var(--gray-light);
        border-radius: 25px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 500;
        color: var(--dark);
        background: white;
        display: flex;
        align-items: center;
        gap: 8px;
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

    /* ===== PRODUCT GRID ===== */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }

    .product-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition-bounce);
        position: relative;
        border: 1px solid rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
        border-color: var(--accent-light);
    }

    .product-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        padding: 6px 14px;
        border-radius: 25px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        z-index: 10;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 5px;
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
        height: 220px;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .product-image-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at center, rgba(212,175,55,0.1) 0%, transparent 70%);
        opacity: 0;
        transition: var(--transition);
    }

    .product-card:hover .product-image-wrapper::before {
        opacity: 1;
    }

    .product-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: var(--transition-bounce);
        position: relative;
        z-index: 1;
    }

    .product-card:hover .product-image {
        transform: scale(1.1);
    }

    .product-quick-view {
        position: absolute;
        bottom: -50px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        color: var(--primary);
        border: none;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: var(--shadow-lg);
        transition: var(--transition-bounce);
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
    }

    .product-card:hover .product-quick-view {
        bottom: 20px;
    }

    .product-quick-view:hover {
        background: var(--accent);
        color: var(--primary-dark);
    }

    .product-info {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-category {
        font-size: 11px;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .product-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 10px;
        line-height: 1.4;
        min-height: 45px;
    }

    .product-title a {
        color: inherit;
        text-decoration: none;
        transition: var(--transition);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-title a:hover {
        color: var(--accent-hover);
    }

    .product-description {
        font-size: 13px;
        color: var(--gray);
        margin-bottom: 15px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 40px;
    }

    .product-price-wrapper {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 15px;
        margin-top: auto;
    }

    .price-row {
        display: flex;
        align-items: baseline;
        gap: 10px;
        flex-wrap: wrap;
    }

    .product-price {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
    }

    .product-price .currency {
        font-size: 14px;
        font-weight: 500;
    }

    .old-price {
        font-size: 14px;
        color: var(--gray);
        text-decoration: line-through;
    }

    .price-note {
        font-size: 11px;
        color: var(--gray);
        margin-top: 5px;
    }

    .product-actions {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
    }

    .btn-add-cart {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 13px;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
    }

    .btn-add-cart:hover {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .btn-add-cart.added {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .btn-wishlist {
        width: 42px;
        height: 42px;
        background: var(--light);
        border: 2px solid var(--gray-light);
        border-radius: 12px;
        color: var(--gray);
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
    }

    .btn-wishlist:hover {
        border-color: #dc3545;
        color: #dc3545;
        background: rgba(220, 53, 69, 0.1);
    }

    .btn-wishlist.active {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 80px 40px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .empty-state-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, var(--accent-light) 0%, var(--accent) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        font-size: 60px;
        color: var(--primary-dark);
        box-shadow: var(--shadow-glow);
    }

    .empty-state h4 {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
    }

    .empty-state p {
        color: var(--gray);
        font-size: 16px;
        margin-bottom: 25px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-back {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
    }

    .btn-back:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-glow);
        color: var(--primary-dark);
    }

    /* ===== PAGINATION ===== */
    .pagination-wrapper {
        margin-top: 50px;
        display: flex;
        justify-content: center;
    }

    .pagination-custom {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination-custom .page-item .page-link {
        border: none;
        background: white;
        color: var(--primary);
        border-radius: 12px;
        padding: 12px 18px;
        font-weight: 500;
        transition: var(--transition);
        box-shadow: var(--shadow);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 45px;
    }

    .pagination-custom .page-item .page-link:hover {
        background: var(--accent);
        color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .pagination-custom .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
    }

    .pagination-custom .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
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

    .custom-toast.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .custom-toast.error {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
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

    /* ===== WORKFLOW SECTION ===== */
    .workflow-section {
        background: white;
        border-radius: 20px;
        padding: 35px 30px;
        box-shadow: var(--shadow-lg);
        margin-top: 40px !important;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }

    .workflow-section::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 1;
    }

    .workflow-header {
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        z-index: 2;
    }

    .workflow-title-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .workflow-badge {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        color: var(--primary-dark);
        padding: 5px 15px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .workflow-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
    }

    .workflow-subtitle {
        color: var(--gray);
        font-size: 15px;
        max-width: 600px;
        margin: 0 auto;
    }

    .workflow-timeline {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
    }

    .workflow-timeline::before {
        content: '';
        position: absolute;
        top: 40px;
        left: 50px;
        right: 50px;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--accent), var(--primary), var(--accent), transparent);
        z-index: 1;
        opacity: 0.3;
    }

    .workflow-step {
        display: flex;
        gap: 25px;
        margin-bottom: 30px;
        position: relative;
        z-index: 2;
        background: white;
        padding: 20px;
        border-radius: 15px;
        transition: var(--transition);
        border: 1px solid transparent;
    }

    .workflow-step:hover {
        border-color: var(--accent-light);
        box-shadow: var(--shadow);
        transform: translateX(5px);
    }

    .step-number {
        width: 60px;
        height: 60px;
        background: var(--light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        position: relative;
        flex-shrink: 0;
        border: 3px solid var(--gray-light);
        transition: var(--transition);
    }

    .step-number.active {
        background: var(--accent);
        border-color: var(--accent-hover);
        color: var(--primary-dark);
        transform: scale(1.1);
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
    }

    .step-number.last {
        background: var(--primary);
        border-color: var(--primary-light);
        color: white;
    }

    .step-number span {
        position: relative;
        z-index: 2;
    }

    .step-number::after {
        content: '';
        position: absolute;
        width: 15px;
        height: 15px;
        background: var(--accent);
        border-radius: 50%;
        bottom: -5px;
        right: -5px;
        opacity: 0;
        transition: var(--transition);
    }

    .step-number.active::after {
        opacity: 1;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .notification-badge {
        background: rgba(212, 175, 55, 0.1);
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        color: var(--accent);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .notification-badge i {
        font-size: 12px;
    }

    .step-description {
        color: var(--gray);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    /* ===== BOUTON VERS PANIER ===== */
    .cart-link {
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
        border: none;
        text-decoration: none;
    }

    .cart-link:hover {
        transform: scale(1.1) rotate(5deg);
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        color: white;
    }

    .cart-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--accent);
        color: var(--primary-dark);
        font-size: 12px;
        font-weight: 700;
        min-width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow);
        padding: 0 6px;
    }

    /* ===== AMÉLIORATIONS MOBILES ===== */
    @media (max-width: 768px) {
        /* Sidebar devient une barre horizontale défilante */
        .col-lg-3 {
            margin-bottom: 20px;
        }

        .search-sidebar,
        .categories-wrapper,
        .filter-section {
            margin-bottom: 15px;
        }

        /* Catégories en défilement horizontal */
        .categories-wrapper {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 5px;
        }

        .categories-wrapper .categories-header {
            display: none; /* On cache l'en-tête pour gagner de la place */
        }

        .categories-list {
            display: flex;
            flex-direction: row;
            padding: 5px;
            max-height: none;
            overflow-y: visible;
        }

        .category-item {
            margin: 0 5px 0 0;
            flex: 0 0 auto;
        }

        .category-link {
            padding: 10px 15px;
            border-radius: 30px;
            background: var(--light);
            border: 1px solid var(--gray-light);
        }

        .category-link.active {
            background: var(--accent);
            color: var(--primary-dark);
        }

        .category-icon {
            width: 24px;
            height: 24px;
            font-size: 14px;
            margin-right: 5px;
        }

        .category-code {
            display: none; /* On cache le code pour économiser l'espace */
        }

        /* Filtres en ligne */
        .filter-section {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            padding: 15px;
        }

        .filter-section .filter-title {
            display: none; /* Cache le titre pour plus de place */
        }

        .filter-option {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 0;
        }

        .filter-label {
            display: inline-flex;
            align-items: center;
            background: var(--light);
            padding: 8px 15px;
            border-radius: 30px;
            border: 1px solid var(--gray-light);
        }

        .filter-checkbox {
            margin-right: 5px;
        }

        /* Grille de produits : 1 seule colonne */
        .product-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .product-image-wrapper {
            height: 160px;
            padding: 10px;
        }

        .product-info {
            padding: 15px;
        }

        .product-title {
            font-size: 14px;
            min-height: 40px;
        }

        .product-description {
            display: none;
        }

        .product-price {
            font-size: 16px;
        }

        .product-price .currency {
            font-size: 12px;
        }

        .old-price {
            font-size: 12px;
        }

        .btn-add-cart {
            padding: 10px;
            font-size: 12px;
        }

        .btn-wishlist {
            width: 38px;
            height: 38px;
            font-size: 14px;
        }

        /* Section header */
        .section-header {
            padding: 15px;
        }

        .section-title {
            font-size: 20px;
        }

        .sort-dropdown .btn {
            padding: 8px 15px;
            font-size: 12px;
        }

        /* Pagination */
        .pagination-custom .page-link {
            padding: 10px 14px;
            min-width: 40px;
        }

        /* Bouton panier plus petit */
        .cart-link {
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        /* Toasts */
        .toast-container {
            bottom: 20px;
            right: 20px;
            left: 20px;
        }
        .custom-toast {
            min-width: auto;
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .product-grid {
            grid-template-columns: 1fr; /* déjà 1 mais on assure */
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

<!-- ===== LOADING OVERLAY ===== -->


<!-- ===== MAIN CONTENT ===== -->
<div class="container py-4">
    <div class="row">
        <!-- Sidebar - devient barre horizontale sur mobile -->
        <div class="col-lg-3 mb-4">
            <div class="search-sidebar">
                <div class="search-sidebar-title">
                    <i class="bi bi-search"></i> Search
                </div>
                <form action="<?php echo base_url('boutique/recherche'); ?>" method="GET" class="search-sidebar-form">
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
                        <input type="checkbox" class="filter-checkbox" id="filterPromo">
                        <span>On sale</span>
                    </label>
                </div>
                <div class="filter-option">
                    <label class="filter-label">
                        <input type="checkbox" class="filter-checkbox" id="filterVedette">
                        <span>Featured</span>
                    </label>
                </div>
                <div class="filter-option">
                    <label class="filter-label">
                        <input type="checkbox" class="filter-checkbox" id="filterNew">
                        <span>New arrivals</span>
                    </label>
                </div>
                <div class="filter-option">
                    <label class="filter-label">
                        <input type="checkbox" class="filter-checkbox" id="filterStock">
                        <span>In stock only</span>
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
                        <span class="product-count"><?php echo $total_produits; ?> item(s)</span>
                    </h2>
                </div>
                
                <div class="sort-dropdown dropdown">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down"></i>
                        Sort by
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-sort="default"><i class="bi bi-arrow-down-up me-2"></i>Default</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="prix_asc"><i class="bi bi-sort-numeric-down me-2"></i>Price low to high</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="prix_desc"><i class="bi bi-sort-numeric-up me-2"></i>Price high to low</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="nom"><i class="bi bi-sort-alpha-down me-2"></i>Name A-Z</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="vedette"><i class="bi bi-star-fill me-2"></i>Popular</a></li>
                    </ul>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="product-grid" id="productsGrid">
                <?php if (!empty($produits)): ?>
                    <?php foreach ($produits as $prod): ?>
                        <?php $this->load->view('partials/product_card', ['prod' => $prod]); ?>
                    <?php endforeach; ?>
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



<script>
// ============================================
// BOUTIQUE.JS - Logique specifique a la boutique
// ============================================

(function() {
    'use strict';
    
    if (window.AGF_Boutique_Loaded) {
        return;
    }
    window.AGF_Boutique_Loaded = true;

    var BASE_URL = '<?php echo base_url(); ?>';
    
    var currentCategory = '<?php echo isset($categorie_active) && $categorie_active ? $categorie_active : 'all'; ?>';
    var currentSearch = '<?php echo isset($query) ? addslashes($query) : ''; ?>';
    var currentSort = 'default';
    var currentFilters = [];
    var currentPage = 0;
    var totalPages = 1;

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

    function showToast(message, type) {
        var container = document.getElementById('toastContainer');
        if (!container) return;
        
        var toast = document.createElement('div');
        toast.className = 'custom-toast ' + (type || 'success');
        toast.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill') + ' fs-5"></i>' +
            '<div><div class="fw-bold">' + (type === 'success' ? 'Success' : 'Error') + '</div>' +
            '<div style="font-size: 14px;">' + message + '</div></div>';
        container.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }

    function initFilters() {
        var categoryLinks = document.querySelectorAll('.category-link');
        for (var i = 0; i < categoryLinks.length; i++) {
            categoryLinks[i].addEventListener('click', function(e) {
                e.preventDefault();
                var href = this.getAttribute('href');
                var parts = href.split('/');
                var categorieId = parts[parts.length - 1];
                currentCategory = (categorieId === 'boutique') ? 'all' : categorieId;
                
                var allLinks = document.querySelectorAll('.category-link');
                for (var j = 0; j < allLinks.length; j++) {
                    allLinks[j].classList.remove('active');
                }
                this.classList.add('active');
                
                currentPage = 0;
                loadProducts();
            });
        }

        var searchInput = document.querySelector('.search-sidebar-input');
        if (searchInput) {
            var searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                var self = this;
                searchTimeout = setTimeout(function() {
                    currentSearch = self.value;
                    currentPage = 0;
                    loadProducts();
                }, 500);
            });
        }

        var filterCheckboxes = document.querySelectorAll('.filter-checkbox');
        for (var k = 0; k < filterCheckboxes.length; k++) {
            filterCheckboxes[k].addEventListener('change', function() {
                updateFilters();
                currentPage = 0;
                loadProducts();
            });
        }

        var sortItems = document.querySelectorAll('.dropdown-item[data-sort]');
        for (var m = 0; m < sortItems.length; m++) {
            sortItems[m].addEventListener('click', function(e) {
                e.preventDefault();
                currentSort = this.dataset.sort;
                currentPage = 0;
                loadProducts();
            });
        }
    }

    function updateFilters() {
        currentFilters = [];
        var checkedBoxes = document.querySelectorAll('.filter-checkbox:checked');
        for (var i = 0; i < checkedBoxes.length; i++) {
            currentFilters.push(checkedBoxes[i].id.replace('filter', '').toLowerCase());
        }
    }

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
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var grid = document.getElementById('productsGrid');
                if (grid) grid.innerHTML = data.html;
                
                var workflow = document.getElementById('workflowContainer');
                if (workflow) workflow.innerHTML = data.workflow_html || '';
                
                totalPages = data.total_pages || 1;
                
                var count = document.querySelector('.product-count');
                if (count) count.textContent = data.total + ' item(s)';
                
                updatePagination();
                updateCategoryTitle();
            } else {
                console.error('Error loading products');
            }
        })
        .catch(function(err) {
            console.error('Error:', err);
        })
        .finally(hideLoading);
    }

    function updatePagination() {
        var wrapper = document.getElementById('paginationWrapper');
        if (!wrapper) return;
        
        var html = '<ul class="pagination-custom">';
        
        if (currentPage > 0) {
            html += '<li class="page-item"><a class="page-link" href="#" onclick="goToPage(' + (currentPage - 1) + '); return false;"><i class="bi bi-chevron-left"></i></a></li>';
        } else {
            html += '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-left"></i></span></li>';
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
        } else {
            html += '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-right"></i></span></li>';
        }
        
        html += '</ul>';
        wrapper.innerHTML = html;
    }

    window.goToPage = function(page) {
        currentPage = page;
        loadProducts();
    };

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
        // Panier désactivé
    }

    window.addToCart = function(productId, productName, btn) {
        showToast('Fonctionnalité panier désactivée', 'info');
    };

    window.toggleWishlist = function(productId, btn) {
        showToast('Favoris temporairement indisponibles', 'info');
    };

    function startCartPolling() {
        // Désactivé
    }

    function init() {
        initFilters();
        updateCartBadge();
        startCartPolling(); // Ajout du polling pour maintenir le badge à jour
    }

    if (document.readyState !== 'loading') {
        setTimeout(init, 100);
    } else {
        document.addEventListener('DOMContentLoaded', () => setTimeout(init, 100));
    }
})();
</script>
<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>