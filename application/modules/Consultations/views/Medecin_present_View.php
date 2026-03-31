<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style type="text/css">
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --accent: #d4af37;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --success: #28a745;
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    --radius: 12px;
    --radius-sm: 8px;
}

/* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    overflow-x: hidden;
    background: #f5f7fa;
}

/* ============================================
   HEADER SECTION - OPTIMISÉ MOBILE
   ============================================ */
.teleconsultation-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    padding: 40px 0 60px;
    position: relative;
    overflow: hidden;
}

.teleconsultation-header::before {
    content: '';
    position: absolute;
    top: -30%;
    right: -30%;
    width: 300px;
    height: 300px;
    background: rgba(212, 175, 55, 0.1);
    border-radius: 50%;
    animation: pulse 4s ease-in-out infinite;
}

@media (min-width: 768px) {
    .teleconsultation-header::before {
        width: 600px;
        height: 600px;
        top: -50%;
        right: -10%;
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.header-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: white;
    padding: 0 16px;
}

.header-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(212, 175, 55, 0.2);
    border: 1px solid var(--accent);
    color: var(--accent);
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 16px;
    backdrop-filter: blur(10px);
}

@media (min-width: 768px) {
    .header-badge {
        padding: 8px 20px;
        font-size: 14px;
        margin-bottom: 20px;
    }
}

.header-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 12px;
    line-height: 1.2;
}

@media (min-width: 768px) {
    .header-title {
        font-size: 48px;
        margin-bottom: 16px;
    }
}

.header-subtitle {
    font-size: 14px;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.5;
    padding: 0 16px;
}

@media (min-width: 768px) {
    .header-subtitle {
        font-size: 18px;
    }
}

/* ============================================
   FILTERS SECTION - MOBILE OPTIMISÉ
   ============================================ */
.filters-section {
    background: white;
    padding: 16px 0;
    border-bottom: 1px solid #e9ecef;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: var(--shadow);
}

.filters-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
    padding: 0 16px;
}

@media (min-width: 768px) {
    .filters-container {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 0 20px;
    }
}

.filter-group {
    width: 100%;
}

.filter-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 50px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230f4c3a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

@media (min-width: 768px) {
    .filter-select {
        width: auto;
        min-width: 220px;
        padding: 14px 24px;
    }
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(15, 76, 58, 0.1);
}

/* ============================================
   STATS BAR - MOBILE OPTIMISÉ
   ============================================ */
.stats-bar {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: white;
    margin: 16px 16px 0;
    border-radius: 16px;
    box-shadow: var(--shadow);
}

@media (min-width: 576px) {
    .stats-bar {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        margin: 20px 20px 0;
    }
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--gray);
    flex: 1;
    justify-content: center;
}

@media (min-width: 576px) {
    .stat-item {
        justify-content: flex-start;
        font-size: 14px;
    }
}

.stat-item i {
    color: var(--accent);
    font-size: 18px;
}

.stat-item strong {
    color: var(--primary);
    font-size: 18px;
    font-weight: 700;
}

/* ============================================
   DOCTORS GRID - MOBILE OPTIMISÉ
   ============================================ */
.doctors-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0;
    padding: 16px;
    gap: 16px;
}

.doctor-col {
    flex: 0 0 100%;
    max-width: 100%;
}

@media (min-width: 768px) {
    .doctor-col {
        flex: 0 0 calc(50% - 16px);
        max-width: calc(50% - 16px);
    }
}

@media (min-width: 1200px) {
    .doctor-col {
        flex: 0 0 calc(33.333% - 16px);
        max-width: calc(33.333% - 16px);
    }
}

.doctor-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.doctor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.12);
    border-color: var(--primary-light);
}

/* Doctor Header */
.doctor-header {
    padding: 20px;
    background: white;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    border-bottom: 1px solid #f0f2f4;
}

.doctor-avatar {
    position: relative;
    flex-shrink: 0;
}

.doctor-avatar img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@media (min-width: 768px) {
    .doctor-avatar img {
        width: 80px;
        height: 80px;
        border-width: 4px;
    }
}

.status-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid white;
    background: var(--success);
    animation: pulse-status 2s infinite;
}

.status-badge.offline {
    background: #dc3545;
    animation: none;
}

@keyframes pulse-status {
    0%, 100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    50% { box-shadow: 0 0 0 6px rgba(40, 167, 69, 0); }
}

.doctor-main-info {
    flex: 1;
    min-width: 0;
}

.doctor-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

@media (min-width: 768px) {
    .doctor-name {
        font-size: 20px;
    }
}

.verified-badge {
    color: var(--accent);
    font-size: 14px;
}

.doctor-specialty {
    color: var(--primary);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.doctor-rating {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.stars {
    color: #ffc107;
    font-size: 11px;
}

.rating-text {
    font-size: 11px;
    color: var(--gray);
}

/* Doctor Body */
.doctor-body {
    padding: 16px 20px;
    flex: 1;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f0f2f4;
    font-size: 13px;
    color: var(--gray);
}

.info-list li i {
    color: var(--primary);
    font-size: 14px;
    width: 20px;
}

.info-list li strong {
    color: var(--dark);
    font-weight: 600;
    margin-left: auto;
}

/* Horaires Preview */
.horaires-preview {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f4;
}

.horaires-preview h6 {
    font-size: 11px;
    color: var(--primary);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.horaires-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.horaire-tag {
    background: rgba(15, 76, 58, 0.08);
    color: var(--primary);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}

.horaire-tag.more {
    background: var(--accent);
    color: var(--primary-dark);
}

/* Doctor Footer - PRIX AMÉLIORÉ */
.doctor-footer {
    padding: 16px 20px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: auto;
    border-top: 1px solid #e9ecef;
}

@media (min-width: 576px) {
    .doctor-footer {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
}

.price-block {
    text-align: center;
    flex: 1;
}

@media (min-width: 576px) {
    .price-block {
        text-align: left;
    }
}

.price-label {
    font-size: 11px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
    font-weight: 600;
}

.price-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--primary);
    display: flex;
    align-items: baseline;
    gap: 4px;
    justify-content: center;
    margin-bottom: 8px;
}

@media (min-width: 576px) {
    .price-value {
        justify-content: flex-start;
        font-size: 32px;
    }
}

.price-value span {
    font-size: 16px;
    color: var(--gray);
    font-weight: 600;
}

/* ÉQUIVALENTS - DESIGN VISIBLE */
.price-equivalents-container {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 8px;
    margin: 8px 0 4px;
    justify-content: center;
}

@media (min-width: 576px) {
    .price-equivalents-container {
        justify-content: flex-start;
    }
}

.price-equivalent-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.15) 0%, rgba(212, 175, 55, 0.05) 100%);
    padding: 6px 12px;
    border-radius: 40px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
    border: 1px solid rgba(212, 175, 55, 0.3);
}

.price-equivalent-item i {
    font-size: 14px;
    color: var(--accent);
}

.price-equivalent-item span {
    color: var(--primary-dark);
    font-weight: 700;
}

.next-slot {
    font-size: 11px;
    color: var(--success);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
    justify-content: center;
    margin-top: 6px;
}

@media (min-width: 576px) {
    .next-slot {
        justify-content: flex-start;
    }
}

.doctor-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.btn-consult {
    padding: 10px 16px;
    border-radius: 40px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
    white-space: nowrap;
}

@media (min-width: 768px) {
    .btn-consult {
        padding: 12px 20px;
        font-size: 14px;
    }
}

.btn-consult-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(15, 76, 58, 0.3);
}

.btn-consult-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15, 76, 58, 0.4);
}

.btn-consult-secondary {
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-consult-secondary:hover {
    background: var(--primary);
    color: white;
}

/* ============================================
   MODAL - OPTIMISÉ MOBILE
   ============================================ */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.85);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
    padding: 16px;
    overflow-y: auto;
}

.modal-overlay.active {
    display: flex;
}

.modal-container {
    background: white;
    border-radius: 24px;
    max-width: 95%;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: slideUp 0.3s ease;
}

@media (min-width: 768px) {
    .modal-container {
        max-width: 800px;
    }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    padding: 20px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
    border-radius: 24px 24px 0 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
    padding-right: 40px;
}

@media (min-width: 768px) {
    .modal-header h3 {
        font-size: 24px;
    }
}

.modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 20px;
}

@media (min-width: 768px) {
    .modal-body {
        padding: 30px;
    }
}

.doctor-detail-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
    text-align: center;
}

@media (min-width: 576px) {
    .doctor-detail-header {
        flex-direction: row;
        text-align: left;
        gap: 20px;
    }
}

.doctor-detail-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

@media (min-width: 768px) {
    .doctor-detail-avatar {
        width: 120px;
        height: 120px;
    }
}

.doctor-detail-info h2 {
    margin: 0 0 8px;
    color: var(--primary-dark);
    font-size: 22px;
}

.row {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

@media (min-width: 768px) {
    .row {
        flex-direction: row;
        gap: 30px;
    }
    .col-md-6 {
        flex: 1;
    }
}

.detail-section {
    margin-bottom: 24px;
}

.detail-section h4 {
    color: var(--primary);
    margin-bottom: 12px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.horaires-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.horaires-table th,
.horaires-table td {
    padding: 10px 8px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.horaires-table th {
    background: #f8fafc;
    color: var(--primary);
    font-weight: 600;
}

.badge-actif {
    background: var(--success);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
}

.badge-inactif {
    background: #6c757d;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
}

/* Prix dans le modal */
.tarif-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 20px;
    padding: 24px;
    text-align: center;
    color: white;
}

.tarif-card .price-main {
    font-size: 48px;
    font-weight: 800;
    margin-bottom: 8px;
}

.tarif-card .price-main small {
    font-size: 20px;
    font-weight: 500;
}

.modal-equivalents {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 16px;
    flex-wrap: wrap;
}

.modal-equivalent {
    background: rgba(255,255,255,0.15);
    padding: 8px 16px;
    border-radius: 40px;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.modal-equivalent i {
    font-size: 16px;
}

.taux-info {
    margin-top: 12px;
    font-size: 11px;
    opacity: 0.8;
}

/* Why Choose Section */
.why-choose-section {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    padding: 60px 20px;
    margin-top: 40px;
    color: white;
}

.why-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

@media (min-width: 576px) {
    .why-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }
}

@media (min-width: 992px) {
    .why-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }
}

.why-item {
    text-align: center;
    padding: 24px 20px;
    background: rgba(255,255,255,0.05);
    border-radius: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
}

.why-item:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.1);
    border-color: var(--accent);
}

.why-icon {
    width: 60px;
    height: 60px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
    color: var(--primary-dark);
}

.why-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}

.why-text {
    font-size: 13px;
    opacity: 0.9;
    line-height: 1.5;
}

/* Utility Classes */
.text-muted { color: var(--gray); }
.text-success { color: var(--success); }
.text-warning { color: #ffc107; }
.bg-light { background: #f8fafc; }
.rounded { border-radius: 12px; }
.p-3 { padding: 16px; }
.mb-2 { margin-bottom: 8px; }
.mt-3 { margin-top: 16px; }
.mt-4 { margin-top: 24px; }
.text-center { text-align: center; }
.fw-bold { font-weight: 700; }
.small { font-size: 12px; }

/* Animation */
.spinner-border {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner 0.75s linear infinite;
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

.me-2 { margin-right: 8px; }

/* Doctor Footer - OPTIMISÉ POUR MOBILE */
.doctor-footer {
    padding: 16px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: auto;
    border-top: 1px solid #e9ecef;
}

@media (min-width: 480px) {
    .doctor-footer {
        padding: 16px 20px;
    }
}

@media (min-width: 640px) {
    .doctor-footer {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
}

.price-block {
    text-align: center;
    flex-shrink: 0;
}

@media (min-width: 640px) {
    .price-block {
        text-align: left;
        min-width: 140px;
    }
}

.price-label {
    font-size: 10px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
    font-weight: 600;
}

.price-value {
    font-size: 24px;
    font-weight: 800;
    color: var(--primary);
    display: flex;
    align-items: baseline;
    gap: 4px;
    justify-content: center;
    margin-bottom: 6px;
}

@media (min-width: 480px) {
    .price-value {
        font-size: 28px;
    }
}

@media (min-width: 640px) {
    .price-value {
        justify-content: flex-start;
    }
}

.price-value span {
    font-size: 14px;
    color: var(--gray);
    font-weight: 600;
}

/* ÉQUIVALENTS */
.price-equivalents-container {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 6px;
    margin: 6px 0 4px;
    justify-content: center;
}

@media (min-width: 640px) {
    .price-equivalents-container {
        justify-content: flex-start;
    }
}

.price-equivalent-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(212, 175, 55, 0.12);
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 600;
}

@media (min-width: 480px) {
    .price-equivalent-item {
        padding: 5px 12px;
        font-size: 11px;
        gap: 6px;
    }
}

.price-equivalent-item i {
    font-size: 11px;
    color: var(--accent);
}

.price-equivalent-item span {
    color: var(--primary-dark);
    font-weight: 700;
}

.next-slot {
    font-size: 10px;
    color: var(--success);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: center;
    margin-top: 4px;
}

@media (min-width: 640px) {
    .next-slot {
        justify-content: flex-start;
    }
}

/* ACTIONS - BOUTONS VISIBLES */
.doctor-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-consult {
    padding: 10px 16px;
    border-radius: 40px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
    white-space: nowrap;
    min-width: 110px;
}

@media (max-width: 480px) {
    .btn-consult {
        padding: 8px 12px;
        font-size: 12px;
        min-width: 95px;
        gap: 6px;
    }
}

@media (min-width: 768px) {
    .btn-consult {
        padding: 12px 20px;
        font-size: 14px;
        min-width: 130px;
    }
}

.btn-consult-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(15, 76, 58, 0.3);
}

.btn-consult-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15, 76, 58, 0.4);
}

.btn-consult-secondary {
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-consult-secondary:hover {
    background: var(--primary);
    color: white;
}

/* Version compacte pour très petits écrans */
@media (max-width: 380px) {
    .doctor-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-consult {
        width: 100%;
        justify-content: center;
    }
    
    .price-equivalent-item {
        font-size: 9px;
        padding: 3px 8px;
    }
    
    .price-value {
        font-size: 22px;
    }
}
</style>

<!-- Header -->
<section class="teleconsultation-header">
    <div class="container">
        <div class="header-content">
            <div class="header-badge">
                <i class="bi bi-camera-video-fill"></i>
                Service disponible 24h/24
            </div>
            <h1 class="header-title">Nos Médecins</h1>
            <p class="header-subtitle">
                Consultez nos médecins spécialistes en ligne. 
                Prenez rendez-vous facilement et rapidement.
            </p>
        </div>
    </div>
</section>

<!-- Filtres -->
<section class="filters-section">
    <div class="container">
        <div class="filters-container">
            <div class="filter-group">
                <select class="filter-select" id="specialtyFilter">
                    <option value="">Toutes les spécialités</option>
                    <?php 
                    $specialites_uniques = [];
                    foreach ($medecins as $medecin) {
                        $spec = $medecin['specialite'] ?? 'Généraliste';
                        if (!in_array($spec, $specialites_uniques)) {
                            $specialites_uniques[] = $spec;
                            echo '<option value="'.htmlspecialchars($spec).'">'.htmlspecialchars($spec).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Contenu principal -->
<section class="container">
    <!-- Statistiques -->
    <div class="stats-bar">
        <div class="stat-item">
            <i class="bi bi-people-fill"></i>
            <div><strong><?= count($medecins) ?></strong> médecins</div>
        </div>
        <div class="stat-item">
            <i class="bi bi-circle-fill text-success"></i>
            <div><strong><?= $online_count ?? 0 ?></strong> disponibles</div>
        </div>
    </div>

    <!-- Grille médecins -->
    <div class="doctors-row" id="doctorsGrid">
        <?php if (!empty($medecins)): ?>
            <?php foreach ($medecins as $medecin): 
                $is_online = !empty($medecin['est_disponible']);
                $photo = !empty($medecin['photo']) ? base_url('attachments/Users/'.$medecin['photo']) : base_url('assets/images/default-doctor.png');
                $nom_complet = htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? ''));
                $specialite_nom = htmlspecialchars($medecin['specialite'] ?? 'Médecin Généraliste');
                $note = number_format($medecin['note_moyenne'] ?? 0, 1);
                $nb_avis = $medecin['nombre_avis'] ?? 0;
                $experience = ($medecin['annees_experience'] ?? 0) . ' ans';
                $langues = !empty($medecin['langues_parlees']) ? $medecin['langues_parlees'] : 'Français';
                
                $prix_usd = $medecin['honoraires_consultation'] ?? 50;
                $taux = $this->config->item('taux_devise');
                if (!$taux) {
                    $taux = ['USD_TO_EUR' => 0.92, 'USD_TO_BIF' => 2900];
                }
                $prix_eur = $prix_usd * ($taux['USD_TO_EUR'] ?? 0.92);
                $prix_bif = $prix_usd * ($taux['USD_TO_BIF'] ?? 2900);

                $horaires_list = [];
                if (!empty($medecin['horaires'])) {
                    foreach ($medecin['horaires'] as $h) {
                        if ($h['est_actif'] == 1) {
                            $jours_fr = ['monday'=>'Lundi','tuesday'=>'Mardi','wednesday'=>'Mercredi','thursday'=>'Jeudi','friday'=>'Vendredi','saturday'=>'Samedi','sunday'=>'Dimanche'];
                            $jour_fr = $jours_fr[strtolower($h['jour_semaine'])] ?? ucfirst($h['jour_semaine']);
                            $horaires_list[] = [
                                'jour' => $jour_fr,
                                'debut' => substr($h['heure_debut'], 0, 5),
                                'fin' => substr($h['heure_fin'], 0, 5)
                            ];
                        }
                    }
                }

                $prochain_slot = 'Sur rendez-vous';
                if (!empty($horaires_list)) {
                    $jours_fr_auj = ['Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi','Sunday'=>'Dimanche'];
                    $aujourdhui = $jours_fr_auj[date('l')] ?? '';
                    foreach ($horaires_list as $h) {
                        if ($h['jour'] === $aujourdhui) {
                            $prochain_slot = "Aujourd'hui à " . $h['debut'];
                            break;
                        }
                    }
                }
            ?>
            
            <div class="doctor-col" data-specialty="<?= strtolower(str_replace(' ', '-', $specialite_nom)) ?>">
                <div class="doctor-card">
                    <div class="doctor-header">
                        <div class="doctor-avatar">
                            <img src="<?= $photo ?>" alt="<?= $nom_complet ?>" onerror="this.src='<?= base_url('assets/images/default-doctor.png') ?>'">
                            <span class="status-badge <?= $is_online ? '' : 'offline' ?>"></span>
                        </div>
                        <div class="doctor-main-info">
                            <h3 class="doctor-name">
                                <?= $nom_complet ?>
                                <?php if (!empty($medecin['est_verifie'])): ?>
                                    <i class="bi bi-patch-check-fill verified-badge"></i>
                                <?php endif; ?>
                            </h3>
                            <div class="doctor-specialty"><?= $specialite_nom ?></div>
                            <div class="doctor-rating">
                                <span class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= round($note) ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="rating-text">(<?= $nb_avis ?> avis)</span>
                            </div>
                        </div>
                    </div>

                    <div class="doctor-body">
                        <ul class="info-list">
                            <li><i class="bi bi-award"></i> Expérience <strong><?= $experience ?></strong></li>
                            <li><i class="bi bi-globe"></i> Langues <strong><?= htmlspecialchars($langues) ?></strong></li>
                        </ul>
                        
                        <?php if (!empty($horaires_list)): ?>
                        <div class="horaires-preview">
                            <h6><i class="bi bi-clock"></i> Horaires</h6>
                            <div class="horaires-tags">
                                <?php foreach(array_slice($horaires_list, 0, 3) as $h): ?>
                                    <span class="horaire-tag"><?= $h['jour'] ?> <?= $h['debut'] ?></span>
                                <?php endforeach; ?>
                                <?php if(count($horaires_list) > 3): ?>
                                    <span class="horaire-tag more">+<?= count($horaires_list)-3 ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="doctor-footer">
                        <div class="price-block">
    <div class="price-label">Consultation</div>
    <div class="price-value">
        <?= number_format($prix_usd, 2) ?> <span>USD/EUR</span>
    </div>
    
    <!-- Prix Burundi -->
    <div class="burundi-price" style="margin-top: 10px; padding: 10px 12px; background: #e8f5f0; border-radius: 8px; border-left: 4px solid #0f4c3a; color: #0f4c3a; font-size: 0.95rem;">
        <i class="bi bi-geo-alt-fill" style="color: #d4af37; margin-right: 6px;"></i>
        <strong>Patients résidant au Burundi: 40 000 Fbu </strong>
    </div>
    
    
    
    <?php if($is_online): ?>
    <div class="next-slot"><i class="bi bi-lightning-charge-fill"></i> <?= $prochain_slot ?></div>
    <?php endif; ?>
</div>
                        
                        <div class="doctor-actions">
                            <button class="btn-consult btn-consult-secondary" onclick="openDetailModal(<?= $medecin['id'] ?>)">
                                <i class="bi bi-eye"></i> Détails
                            </button>
                            <?php if($is_online): ?>
                                <?php if($this->session->userdata('user_id')): ?>
                                    <a href="<?= base_url('Consultations/PatientForm?doctor_uuid='.$medecin['uuid']) ?>" class="btn-consult btn-consult-primary">
                                        <i class="bi bi-calendar-plus"></i> RDV
                                    </a>
                                <?php else: ?>
                                    <form method="POST" action="<?= base_url('Auth') ?>" class="d-inline">
                                        <input type="hidden" name="selected_doctor_uuid" value="<?= $medecin['uuid'] ?>">
                                        <button type="submit" class="btn-consult btn-consult-primary">
                                            <i class="bi bi-calendar-plus"></i> RDV
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn-consult btn-consult-secondary" disabled>
                                    <i class="bi bi-clock"></i> Indisponible
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal-overlay" id="detailModal<?= $medecin['id'] ?>">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="bi bi-person-badge"></i> Profil du Médecin</h3>
                            <button class="modal-close" onclick="closeModal('detailModal<?= $medecin['id'] ?>')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="doctor-detail-header">
                                <img src="<?= $photo ?>" class="doctor-detail-avatar">
                                <div class="doctor-detail-info">
                                    <h2><?= $nom_complet ?></h2>
                                    <p class="text-muted"><?= $specialite_nom ?></p>
                                    <div>
                                        <?php if(!empty($medecin['est_verifie'])): ?>
                                            <span class="badge-actif"><i class="bi bi-check-circle"></i> Vérifié</span>
                                        <?php endif; ?>
                                        <span class="badge-<?= $is_online ? 'actif' : 'inactif' ?>">
                                            <i class="bi bi-circle-fill"></i> <?= $is_online ? 'Disponible' : 'Indisponible' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-section">
                                        <h4><i class="bi bi-info-circle"></i> Informations</h4>
                                        <table class="table">
                                            <tr><td class="text-muted">Expérience</td><td class="fw-bold"><?= $experience ?></td></tr>
                                            <tr><td class="text-muted">Langues</td><td class="fw-bold"><?= htmlspecialchars($langues) ?></td></tr>
                                            <tr><td class="text-muted">Licence</td><td class="fw-bold"><?= htmlspecialchars($medecin['numero_licence'] ?? 'N/A') ?></td></tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-section">
                                        <h4><i class="bi bi-clock"></i> Horaires</h4>
                                        <?php if(!empty($horaires_list)): ?>
                                            <table class="horaires-table">
                                                <thead><tr><th>Jour</th><th>Début</th><th>Fin</th></tr></thead>
                                                <tbody>
                                                    <?php foreach($horaires_list as $h): ?>
                                                    <tr><td><?= $h['jour'] ?></td><td><?= $h['debut'] ?></td><td><?= $h['fin'] ?></td></tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="alert alert-info">Aucun horaire défini</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                           <div class="detail-section">
    <h4><i class="bi bi-currency-exchange"></i> Tarifs</h4>
    <div class="tarif-card">
        <div class="price-main"><?= number_format($prix_usd, 2) ?> <small>USD/EUR</small></div>
        
        <div class="burundi-price" style="margin-top: 10px; padding: 12px; background: #d4edda; border-radius: 8px; border-left: 4px solid #28a745; color: #155724;">
            <i class="bi bi-geo-alt-fill" style="color: #28a745;"></i>
            <strong>Patients résidant au Burundi: 40 000 Fbu</strong>
        </div>
    </div>
</div>

                            <div class="text-center mt-4">
                                <?php if($is_online): ?>
                                    <?php if($this->session->userdata('user_id')): ?>
                                        <a href="<?= base_url('Consultations/PatientForm?doctor_uuid='.$medecin['uuid']) ?>" class="btn-consult btn-consult-primary" style="padding: 14px 28px;">
                                            <i class="bi bi-calendar-plus"></i> Prendre Rendez-vous (<?= number_format($prix_usd, 2) ?> USD)
                                        </a>
                                    <?php else: ?>
                                        <form method="POST" action="<?= base_url('Auth') ?>">
                                            <input type="hidden" name="selected_doctor_uuid" value="<?= $medecin['uuid'] ?>">
                                            <button type="submit" class="btn-consult btn-consult-primary" style="padding: 14px 28px;">
                                                <i class="bi bi-calendar-plus"></i> Prendre Rendez-vous (<?= number_format($prix_usd, 2) ?> USD)
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn-consult btn-consult-secondary" disabled style="padding: 14px 28px;">
                                        <i class="bi bi-clock"></i> Médecin indisponible
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-emoji-frown" style="font-size: 48px; color: #ccc;"></i>
                <p class="mt-3 text-muted">Aucun médecin disponible pour le moment</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Section -->
<section class="why-choose-section">
    <div class="container">
        <div class="why-grid">
            <div class="why-item"><div class="why-icon"><i class="bi bi-clock-history"></i></div><h3 class="why-title">24h/24</h3><p class="why-text">Consultations à tout moment</p></div>
            <div class="why-item"><div class="why-icon"><i class="bi bi-shield-check"></i></div><h3 class="why-title">Certifiés</h3><p class="why-text">Médecins qualifiés</p></div>
            <div class="why-item"><div class="why-icon"><i class="bi bi-currency-exchange"></i></div><h3 class="why-title">Prix abordables</h3><p class="why-text">Tarifs adaptés</p></div>
            <div class="why-item"><div class="why-icon"><i class="bi bi-lock"></i></div><h3 class="why-title">Confidentiel</h3><p class="why-text">Données protégées</p></div>
        </div>
    </div>
</section>



<?php 
        // Vérifier si la variable $products existe et contient des données
        if (!isset($products)) {
            // Si les produits ne sont pas encore chargés, on les récupère
            $products = $this->Model->read('advertise_product', null, 'id', 'DESC');
        }
        ?>
<?php include VIEWPATH.'sections/Products_Section.php'; ?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    window.openDetailModal = function(id) {
        document.getElementById('detailModal' + id).classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    window.closeModal = function(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    };
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => {
                m.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });
    const specialtyFilter = document.getElementById('specialtyFilter');
    if (specialtyFilter) {
        specialtyFilter.addEventListener('change', function() {
            const specialty = this.value.toLowerCase().replace(/\s+/g, '-');
            document.querySelectorAll('.doctor-col').forEach(col => {
                col.style.display = (!specialty || col.dataset.specialty === specialty) ? 'block' : 'none';
            });
        });
    }
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>