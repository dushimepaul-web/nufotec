// ============================================
// PANIER.JS - Fichier global pour toutes les pages
// ============================================

(function() {
    'use strict';
    
    // Protection contre le double chargement STRICTE
    if (window.AGF_Panier_Loaded) {
        console.log('Panier.js deja charge, skip');
        return;
    }
    window.AGF_Panier_Loaded = true;

    // ============================================
    // CONFIGURATION
    // ============================================
    window.AGF_Panier = window.AGF_Panier || {};
    
    var BASE_URL = window.AGF_Panier.BASE_URL || '/';

    // ============================================
    // VARIABLES D'ETAT (privees)
    // ============================================
    var cartOffcanvas = null;
    var cartUpdateTimeout = null;
    var isCartLoading = false;
    var isInitialized = false;

    // ============================================
    // FONCTIONS UTILITAIRES
    // ============================================
    
    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('toastContainer');
        if (!container) {
            console.warn('Toast container not found');
            return;
        }
        
        // Eviter les doublons de toasts
        var existingToasts = container.querySelectorAll('.custom-toast');
        for (var i = 0; i < existingToasts.length; i++) {
            if (existingToasts[i].textContent.indexOf(message) !== -1) {
                existingToasts[i].remove();
            }
        }
        
        var toast = document.createElement('div');
        toast.className = 'custom-toast ' + type;
        
        var icon = 'info-circle-fill';
        var title = 'Info';
        
        if (type === 'success') {
            icon = 'check-circle-fill';
            title = 'Success';
        } else if (type === 'error') {
            icon = 'exclamation-triangle-fill';
            title = 'Error';
        }

        toast.innerHTML = '<i class="bi bi-' + icon + ' fs-5"></i>' +
            '<div><div class="fw-bold">' + title + '</div>' +
            '<div style="font-size: 14px; opacity: 0.9;">' + message + '</div></div>';

        container.appendChild(toast);

        setTimeout(function() {
            if (toast && toast.style) {
                toast.style.animation = 'slideIn 0.3s ease-out reverse';
            }
            setTimeout(function() { 
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }, 3000);
    }

    // ============================================
    // GESTION DU BADGE
    // ============================================
    
    function updateCartBadge(count) {
        console.log('Updating badge to:', count);
        
        var badge = document.getElementById('cartFloatBadge');
        
        if (!badge) {
            badge = document.querySelector('.cart-badge-float');
        }
        
        if (badge) {
            badge.textContent = count;
            
            // Animation avec protection
            try {
                if (badge.style) {
                    badge.style.transform = 'scale(1.3)';
                    setTimeout(function() {
                        if (badge && badge.style) {
                            badge.style.transform = 'scale(1)';
                        }
                    }, 200);
                }
            } catch (e) {
                console.error('Animation error:', e);
            }
        } else {
            console.warn('Badge element not found');
        }
        
        // Mettre a jour tous les autres badges
        var otherBadges = document.querySelectorAll('.cart-count');
        for (var j = 0; j < otherBadges.length; j++) {
            if (otherBadges[j]) {
                otherBadges[j].textContent = count;
            }
        }
    }

    // ============================================
    // INITIALISATION OFFCANVAS (UNE SEULE FOIS)
    // ============================================
    
    function initOffcanvas() {
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap JS not loaded');
            return false;
        }
        
        // Si deja initialise, ne pas recreer
        if (cartOffcanvas !== null) {
            return true;
        }
        
        var element = document.getElementById('offcanvasCart');
        if (!element) {
            console.error('Offcanvas element #offcanvasCart not found');
            return false;
        }
        
        try {
            // Verifier si Bootstrap a deja une instance sur cet element
            var existingInstance = bootstrap.Offcanvas.getInstance(element);
            if (existingInstance) {
                cartOffcanvas = existingInstance;
                console.log('Offcanvas instance recuperee');
            } else {
                cartOffcanvas = new bootstrap.Offcanvas(element);
                console.log('Offcanvas instance creee');
            }
            
            // Ajouter l'ecouteur d'evenement pour le chargement
            element.removeEventListener('show.bs.offcanvas', onOffcanvasShow);
            element.addEventListener('show.bs.offcanvas', onOffcanvasShow);
            
            return true;
        } catch (e) {
            console.error('Error initializing offcanvas:', e);
            return false;
        }
    }
    
    function onOffcanvasShow() {
        console.log('Offcanvas opening - loading cart');
        loadCart();
    }

    // ============================================
    // OUVERTURE DU PANIER
    // ============================================
    
    window.AGF_Panier.openCart = function() {
        console.log('openCart called');
        if (!initOffcanvas()) {
            showToast('Unable to open cart', 'error');
            return;
        }
        if (cartOffcanvas && cartOffcanvas.show) {
            cartOffcanvas.show();
        }
    };

    // ============================================
    // CHARGEMENT DU PANIER
    // ============================================
    
    function loadCart() {
        if (isCartLoading) {
            console.log('Cart already loading, skip');
            return;
        }
        isCartLoading = true;
        
        var loadingEl = document.getElementById('cartLoading');
        var contentEl = document.getElementById('cartContent');
        var emptyEl = document.getElementById('cartEmpty');
        var footerEl = document.getElementById('cartFooter');
        
        if (loadingEl && loadingEl.style) {
            loadingEl.style.display = 'block';
        }
        if (contentEl && contentEl.style) {
            contentEl.style.display = 'none';
        }
        if (emptyEl && emptyEl.style) {
            emptyEl.style.display = 'none';
        }
        if (footerEl && footerEl.style) {
            footerEl.style.display = 'none';
        }

        console.log('Fetching cart from:', BASE_URL + 'panier/get_cart');
        
        fetch(BASE_URL + 'panier/get_cart')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function(data) {
                isCartLoading = false;
                console.log('Cart data received:', data);
                
                if (loadingEl && loadingEl.style) {
                    loadingEl.style.display = 'none';
                }
                
                var nbArticles = parseInt(data.nb_articles) || 0;
                
                // MISE A JOUR DU BADGE
                updateCartBadge(nbArticles);
                
                if (nbArticles > 0 && data.lignes && data.lignes.length > 0) {
                    renderCartItems(data.lignes, data.total_formatted || '0 USD');
                    if (contentEl && contentEl.style) {
                        contentEl.style.display = 'block';
                    }
                    if (footerEl && footerEl.style) {
                        footerEl.style.display = 'block';
                    }
                    if (emptyEl && emptyEl.style) {
                        emptyEl.style.display = 'none';
                    }
                } else {
                    if (contentEl && contentEl.style) {
                        contentEl.style.display = 'none';
                    }
                    if (footerEl && footerEl.style) {
                        footerEl.style.display = 'none';
                    }
                    if (emptyEl && emptyEl.style) {
                        emptyEl.style.display = 'block';
                    }
                }
            })
            .catch(function(error) {
                isCartLoading = false;
                console.error('Error loading cart:', error);
                
                if (loadingEl && loadingEl.style) {
                    loadingEl.style.display = 'none';
                }
                if (emptyEl && emptyEl.style) {
                    emptyEl.style.display = 'block';
                }
                showToast('Erreur de chargement du panier', 'error');
            });
    }

    // ============================================
    // AFFICHAGE DES ARTICLES
    // ============================================
    
    function renderCartItems(lignes, total) {
        var container = document.getElementById('cartContent');
        if (!container) {
            console.error('cartContent not found');
            return;
        }
        
        var html = '';
        
        for (var i = 0; i < lignes.length; i++) {
            var ligne = lignes[i];
            var image = ligne.image_principale 
                ? BASE_URL + 'attachments/Produits/' + ligne.image_principale 
                : 'https://placehold.co/200x200/0f4c3a/d4af37?text=AGF';
            
            var prixUnitaire = parseFloat(ligne.prix_unitaire_ht) || 0;
            var quantite = parseInt(ligne.quantite) || 1;
            var totalLigne = parseFloat(ligne.total_ligne_ttc) || 0;
            var currency = ligne.currency || 'USD';

            html += '<div class="cart-item" data-ligne-id="' + ligne.id + '">' +
                '<div class="cart-item-image"><img src="' + image + '" alt="' + escapeHtml(ligne.nom_produit || 'Product') + '"></div>' +
                '<div class="cart-item-details">' +
                '<div class="cart-item-title">' + escapeHtml(ligne.nom_produit || 'Product') + '</div>' +
                '<div class="cart-item-price">' + prixUnitaire.toLocaleString('en-US') + ' ' + currency + '</div>' +
                '<div class="cart-item-actions">' +
                '<div class="quantity-control">' +
                '<button type="button" onclick="AGF_Panier.updateQuantity(' + ligne.id + ', ' + Math.max(1, quantite - 1) + ')">-</button>' +
                '<input type="number" value="' + quantite + '" min="1" max="99" readonly>' +
                '<button type="button" onclick="AGF_Panier.updateQuantity(' + ligne.id + ', ' + (quantite + 1) + ')">+</button>' +
                '</div>' +
                '<button class="btn-remove" onclick="AGF_Panier.deleteLine(' + ligne.id + ')" title="Remove">' +
                '<i class="bi bi-trash"></i></button></div></div>' +
                '<div class="item-total">' + totalLigne.toLocaleString('en-US') + ' ' + currency + '</div></div>';
        }
        
        container.innerHTML = html;
        
        var totalEl = document.getElementById('cartTotal');
        if (totalEl) {
            totalEl.textContent = total;
        }
        
        console.log('Cart items rendered:', lignes.length);
    }

    function escapeHtml(text) {
        if (!text) {
            return '';
        }
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================
    // ACTIONS SUR LE PANIER (EXPOSEES GLOBALEMENT)
    // ============================================
    
    window.AGF_Panier.updateQuantity = function(ligneId, newQty) {
        newQty = parseInt(newQty);
        if (isNaN(newQty) || newQty < 1) {
            AGF_Panier.deleteLine(ligneId);
            return;
        }

        if (cartUpdateTimeout) {
            clearTimeout(cartUpdateTimeout);
        }

        cartUpdateTimeout = setTimeout(function() {
            fetch(BASE_URL + 'panier/update_quantity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'ligne_id=' + encodeURIComponent(ligneId) + '&quantite=' + encodeURIComponent(newQty)
            })
            .then(function(response) { 
                return response.json(); 
            })
            .then(function(data) {
                if (data.success) {
                    var nbArticles = parseInt(data.nb_articles) || 0;
                    
                    if (nbArticles > 0 && data.lignes) {
                        renderCartItems(data.lignes, data.total_formatted || '0 USD');
                        updateCartBadge(nbArticles);
                    } else {
                        var contentEl = document.getElementById('cartContent');
                        var footerEl = document.getElementById('cartFooter');
                        var emptyEl = document.getElementById('cartEmpty');
                        
                        if (contentEl && contentEl.style) {
                            contentEl.style.display = 'none';
                        }
                        if (footerEl && footerEl.style) {
                            footerEl.style.display = 'none';
                        }
                        if (emptyEl && emptyEl.style) {
                            emptyEl.style.display = 'block';
                        }
                        
                        updateCartBadge(0);
                    }
                } else {
                    showToast(data.message || 'Update error', 'error');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                showToast('Connection error', 'error');
            });
        }, 300);
    };

    window.AGF_Panier.deleteLine = function(ligneId) {
        fetch(BASE_URL + 'panier/delete_line', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'ligne_id=' + encodeURIComponent(ligneId)
        })
        .then(function(response) { 
            return response.json(); 
        })
        .then(function(data) {
            if (data.success) {
                var nbArticles = parseInt(data.nb_articles) || 0;
                
                if (nbArticles > 0 && data.lignes) {
                    renderCartItems(data.lignes, data.total_formatted || '0 USD');
                    updateCartBadge(nbArticles);
                } else {
                    var contentEl = document.getElementById('cartContent');
                    var footerEl = document.getElementById('cartFooter');
                    var emptyEl = document.getElementById('cartEmpty');
                    
                    if (contentEl && contentEl.style) {
                        contentEl.style.display = 'none';
                    }
                    if (footerEl && footerEl.style) {
                        footerEl.style.display = 'none';
                    }
                    if (emptyEl && emptyEl.style) {
                        emptyEl.style.display = 'block';
                    }
                    
                    updateCartBadge(0);
                }
                
                showToast('Item removed from cart', 'success');
            } else {
                showToast(data.message || 'Delete error', 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('Connection error', 'error');
        });
    };

    window.AGF_Panier.addToCart = function(productId, productName, btn) {
        if (!btn || btn.disabled) {
            return;
        }
        
        var originalContent = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;

        fetch(BASE_URL + 'panier/ajouter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id=' + encodeURIComponent(productId) + '&quantite=1'
        })
        .then(function(response) { 
            return response.json(); 
        })
        .then(function(data) {
            if (data.success) {
                if (btn && btn.classList) {
                    btn.classList.add('added');
                }
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Added';
                
                showToast(productName + ' added to cart', 'success');
                
                if (data.nb_articles !== undefined) {
                    updateCartBadge(data.nb_articles);
                }
                
                setTimeout(function() {
                    if (btn && btn.classList) {
                        btn.classList.remove('added');
                    }
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }, 2000);
            } else {
                showToast(data.message || 'Error', 'error');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('Connection error', 'error');
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
    };

    window.AGF_Panier.toggleWishlist = function(productId, btn) {
        if (!btn || btn.disabled) {
            return;
        }
        btn.disabled = true;

        fetch(BASE_URL + 'panier/toggle_favori', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded' 
            },
            body: 'produit_id=' + encodeURIComponent(productId)
        })
        .then(function(response) { 
            return response.json(); 
        })
        .then(function(data) {
            if (data.success) {
                if (data.action === 'added') {
                    if (btn && btn.classList) {
                        btn.classList.add('active');
                    }
                    btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
                    showToast('Added to favorites', 'success');
                } else {
                    if (btn && btn.classList) {
                        btn.classList.remove('active');
                    }
                    btn.innerHTML = '<i class="bi bi-heart"></i>';
                    showToast('Removed from favorites', 'success');
                }
            } else {
                showToast(data.message || 'Error', 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showToast('Connection error', 'error');
        })
        .finally(function() {
            setTimeout(function() { 
                btn.disabled = false; 
            }, 500);
        });
    };

    // ============================================
    // INITIALISATION AU CHARGEMENT
    // ============================================
    
    function initApp() {
        if (isInitialized) {
            console.log('Panier deja initialise');
            return;
        }
        
        console.log('Initialisation du panier...');
        
        // Verifier que tous les elements necessaires existent
        var requiredElements = ['offcanvasCart', 'cartLoading', 'cartContent', 'cartEmpty', 'cartFooter', 'cartTotal'];
        var missing = [];
        for (var k = 0; k < requiredElements.length; k++) {
            if (!document.getElementById(requiredElements[k])) {
                missing.push(requiredElements[k]);
            }
        }
        
        if (missing.length > 0) {
            console.warn('Elements manquants:', missing);
        }
        
        initOffcanvas();
        
        // Charger le panier en arriere-plan pour mettre a jour le badge
        fetch(BASE_URL + 'panier/get_cart')
            .then(function(r) { 
                return r.json(); 
            })
            .then(function(data) {
                if (data && typeof data.nb_articles === 'number') {
                    updateCartBadge(data.nb_articles);
                    console.log('Badge mis a jour:', data.nb_articles);
                }
            })
            .catch(function(e) {
                console.error('Erreur init badge:', e);
            });
        
        isInitialized = true;
    }

    // Attendre que le DOM soit pret avec delai pour eviter race conditions
    function ready(fn) {
        if (document.readyState !== 'loading') {
            setTimeout(fn, 50);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(fn, 50);
            });
        }
    }

    ready(initApp);
    
    // Exposer la fonction de mise a jour du badge pour d'autres scripts
    window.AGF_Panier.updateCartBadge = updateCartBadge;
    window.AGF_Panier.loadCart = loadCart;
    window.AGF_Panier.showToast = showToast;
    
})();