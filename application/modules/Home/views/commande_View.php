<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    .checkout-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--shadow-lg);
        margin-bottom: 40px;
    }

    .checkout-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .recap-panier {
        background: var(--light);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
    }

    .recap-panier h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 15px;
    }

    .recap-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--gray-light);
    }

    .recap-item:last-child {
        border-bottom: none;
    }

    .recap-item .produit-nom {
        font-weight: 500;
        color: var(--dark);
    }

    .recap-item .produit-prix {
        font-weight: 700;
        color: var(--primary);
    }

    .recap-total {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 2px solid var(--gray-light);
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: 600;
        color: var(--primary);
        display: block;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--gray-light);
        border-radius: 10px;
        font-size: 14px;
        transition: var(--transition);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    }

    .form-control.error {
        border-color: #dc3545;
    }

    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    .btn-commander {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 16px;
        transition: var(--transition);
        cursor: pointer;
        width: 100%;
    }

    .btn-commander:hover {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .payment-methods {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .payment-method {
        flex: 1;
        min-width: 150px;
    }

    .payment-method label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
        border: 2px solid var(--gray-light);
        border-radius: 10px;
        cursor: pointer;
        transition: var(--transition);
    }

    .payment-method label:hover {
        border-color: var(--accent);
    }

    .payment-method input[type="radio"] {
        accent-color: var(--accent);
        width: 18px;
        height: 18px;
    }

    /* Styles pour la gestion d'adresses */
    .adresse-selector {
        background: var(--light);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .adresse-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border: 1px solid var(--gray-light);
        border-radius: 8px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: var(--transition);
    }
    .adresse-option:hover {
        border-color: var(--accent);
    }
    .adresse-option.selected {
        border-color: var(--accent);
        background: rgba(212, 175, 55, 0.05);
    }
    .adresse-option input[type="radio"] {
        accent-color: var(--accent);
        width: 18px;
        height: 18px;
    }
    .adresse-option .adresse-details {
        flex: 1;
        font-size: 14px;
    }
    .adresse-option .adresse-details strong {
        color: var(--primary);
    }
    .nouvelle-adresse-form {
        display: none;
        margin-top: 20px;
    }
    .nouvelle-adresse-form.active {
        display: block;
    }

    /* Autocomplete pays */
    .pays-autocomplete-container {
        position: relative;
    }

    .pays-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid var(--accent);
        border-radius: 0 0 10px 10px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: var(--shadow-lg);
    }

    .pays-dropdown.active {
        display: block;
    }

    .pays-item {
        padding: 10px 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: var(--transition);
    }

    .pays-item:hover {
        background: var(--light);
    }

    .pays-item .drapeau {
        font-size: 20px;
    }

    .pays-item .nom {
        flex: 1;
        font-weight: 500;
    }

    .pays-item .code {
        color: var(--gray);
        font-size: 12px;
    }

    .pays-item .indicatif {
        background: var(--accent);
        color: var(--primary-dark);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .telephone-group {
        display: flex;
        gap: 10px;
    }

    .telephone-group .indicatif-display {
        background: var(--light);
        border: 2px solid var(--gray-light);
        border-radius: 10px;
        padding: 12px 15px;
        font-weight: 600;
        color: var(--primary);
        min-width: 80px;
        text-align: center;
    }

    .telephone-group input {
        flex: 1;
    }

    .pays-selectionne {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: rgba(212, 175, 55, 0.1);
        border-radius: 8px;
        margin-top: 5px;
    }

    .pays-selectionne .drapeau {
        font-size: 24px;
    }

    .pays-selectionne .infos {
        flex: 1;
    }

    .pays-selectionne .nom {
        font-weight: 600;
        color: var(--primary);
    }

    .pays-selectionne .details {
        font-size: 12px;
        color: var(--gray);
    }
</style>

<div class="container py-4">
    <div class="checkout-container">
        <h1 class="checkout-title"><i class="bi bi-bag-check"></i> Finaliser la commande</h1>

        <?php if ($this->session->flashdata('erreur')): ?>
             <div class="alert alert-danger" id="alerte-erreur">
        <?php echo $this->session->flashdata('erreur'); ?>
    </div>
    
    <script>
        // Disparaît après 5 secondes
        setTimeout(function() {
            document.getElementById('alerte-erreur').style.display = 'none';
        }, 5000);
    </script>
<?php endif; ?>

        <div class="row">
            <!-- Récapitulatif du panier -->
            <div class="col-lg-5 mb-4">
                <div class="recap-panier">
                    <h3>Récapitulatif de votre panier</h3>
                    <?php foreach ($lignes as $ligne): ?>
                        <div class="recap-item">
                            <span class="produit-nom"><?php echo $ligne->nom_produit; ?> (x<?php echo $ligne->quantite; ?>)</span>
                            <span class="produit-prix"><?php echo number_format($ligne->total_ligne_ttc, 0, ',', ' '); ?> $</span>
                        </div>
                    <?php endforeach; ?>   
                    <div class="recap-total">
                        <span>Total Price</span>
                        <span><?php echo number_format($total, 0, ',', ' '); ?> $</span>
                    </div>
                </div>
            </div>

            <!-- Formulaire de commande -->
            <div class="col-lg-7">
                <?php echo form_open('commande/valider', ['id' => 'commandeForm']); ?>

                    <!-- Section adresse de livraison -->
                    <div class="form-group">
                        <label>Adresse de livraison *</label>
                        <input type="radio" name="adresse_option" id="adresse_option_existante" value="existante" checked hidden>
                        <input type="radio" name="adresse_option" id="adresse_option_nouvelle" value="nouvelle" hidden>

                        <div class="adresse-selector">
                            <div class="adresse-option" onclick="document.getElementById('adresse_option_existante').checked=true; showExistingAddresses();">
                                <input type="radio" name="dummy" checked>
                                <i class="bi bi-bookmark-check"></i>
                                <span>Utiliser une adresse existante</span>
                            </div>
                            <div id="existing-addresses" style="margin-left: 30px;">
                                <?php if (!empty($adresses)): ?>
                                    <?php foreach ($adresses as $addr): ?>
                                        <div class="adresse-option" onclick="selectAddress(<?php echo $addr->id; ?>)">
                                            <input type="radio" name="adresse_id" value="<?php echo $addr->id; ?>" <?php echo ($addr->est_principale) ? 'checked' : ''; ?>>
                                            <div class="adresse-details">
                                                <strong><?php echo $addr->nom_complet; ?></strong><br>
                                                <?php echo $addr->adresse_ligne1; ?><br>
                                                <?php if (!empty($addr->adresse_ligne2)) echo $addr->adresse_ligne2 . '<br>'; ?>
                                                <?php echo $addr->code_postal . ' ' . $addr->ville; ?><br>
                                                <small><?php echo $addr->telephone; ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Aucune adresse enregistrée.</p>
                                <?php endif; ?>
                            </div>

                            <div class="adresse-option" onclick="document.getElementById('adresse_option_nouvelle').checked=true; showNewAddressForm();">
                                <input type="radio" name="dummy">
                                <i class="bi bi-plus-circle"></i>
                                <span>Add a new address</span>
                            </div>
                        </div>

                        <!-- Formulaire nouvelle adresse -->
                        <div id="nouvelle-adresse" class="nouvelle-adresse-form">
                            <div class="form-group">
                                <label for="nom_complet">Full Name *</label>
                                <input type="text" class="form-control" id="nom_complet" name="nom_complet" value="<?php echo set_value('nom_complet'); ?>">
                                <div class="error-message" id="error_nom_complet"></div>
                            </div>
                            <div class="form-group">
                                <label for="entreprise">Entreprise</label>
                                <input type="text" class="form-control" id="entreprise" name="entreprise" value="<?php echo set_value('entreprise'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="tva_intracom">TVA intracommunautaire</label>
                                <input type="text" class="form-control" id="tva_intracom" name="tva_intracom" value="<?php echo set_value('tva_intracom'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="adresse_ligne1">Adresse ligne 1 *</label>
                                <input type="text" class="form-control" id="adresse_ligne1" name="adresse_ligne1" value="<?php echo set_value('adresse_ligne1'); ?>">
                                <div class="error-message" id="error_adresse_ligne1"></div>
                            </div>
                            <div class="form-group">
                                <label for="adresse_ligne2">Adresse ligne 2 (optionnel)</label>
                                <input type="text" class="form-control" id="adresse_ligne2" name="adresse_ligne2" value="<?php echo set_value('adresse_ligne2'); ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="code_postal">Code postal *</label>
                                        <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo set_value('code_postal'); ?>">
                                        <div class="error-message" id="error_code_postal"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ville">Ville *</label>
                                        <input type="text" class="form-control" id="ville" name="ville" value="<?php echo set_value('ville'); ?>">
                                        <div class="error-message" id="error_ville"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="region">Région/State</label>
                                        <input type="text" class="form-control" id="region" name="region" value="<?php echo set_value('region'); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Champ Pays avec autocomplete -->
                            <div class="form-group">
                                <label for="pays_search">Country *</label>
                                <div class="pays-autocomplete-container">
                                    <input type="text" 
                                           class="form-control" 
                                           id="pays_search" 
                                           placeholder="Number without dial code..." 
                                           autocomplete="off">
                                    <input type="hidden" id="pays_id" name="pays_id" value="<?php echo set_value('pays_id'); ?>">
                                    <input type="hidden" id="pays_code" name="pays_code" value="<?php echo set_value('pays_code'); ?>">
                                    <input type="hidden" id="pays_indicatif" name="pays_indicatif" value="<?php echo set_value('pays_indicatif'); ?>">
                                    
                                    <!-- Dropdown des résultats -->
                                    <div class="pays-dropdown" id="paysDropdown"></div>
                                </div>
                                <div class="error-message" id="error_pays_id"></div>
                                
                                <!-- Affichage du pays sélectionné -->
                                <div class="pays-selectionne d-none" id="paysSelectionne">
                                    <span class="drapeau" id="selectedDrapeau">🇫🇷</span>
                                    <div class="infos">
                                        <div class="nom" id="selectedNom">France</div>
                                        <div class="details">
                                            Code: <span id="selectedCode">FR</span> | 
                                            Indicatif: <span id="selectedIndicatif">+33</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetPays()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Téléphone avec indicatif -->
                            <div class="form-group">
                                <label for="telephone">Phone *</label>
                                <div class="telephone-group">
                                    <div class="indicatif-display" id="indicatifDisplay">+XXX</div>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="telephone" 
                                           name="telephone" 
                                           placeholder="Number without dial code"
                                           value="<?php echo set_value('telephone'); ?>">
                                </div>
                                <div class="error-message" id="error_telephone"></div>
                            </div>

                            <div class="form-group">
                                <label for="instructions">Delivery Instructions</label>
                                <textarea class="form-control" id="instructions" name="instructions" rows="2"><?php echo set_value('instructions'); ?></textarea>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="definir_principale" name="definir_principale" value="1">
                                <label class="form-check-label" for="definir_principale">Set as primary address</label>
                            </div>
                        </div>
                    </div>

                    <!-- Mode de paiement dynamique depuis la base -->
                    <div class="form-group">
                        <label>Payment Method *</label>
                        <div class="payment-methods">
                            <?php foreach ($modes_paiement as $mode): ?>
                            <div class="payment-method">
                                <label>
                                    <input type="radio" name="mode_paiement" value="<?php echo $mode->id_mode_payement; ?>" <?php echo set_radio('mode_paiement', $mode->id_mode_payement); ?>>
                                    <?php echo $mode->description; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="error-message" id="error_mode_paiement"></div>
                    </div>

                    <button type="submit" class="btn-commander">
                        <i class="bi bi-check-lg"></i> Confirm Order
                    </button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
// Données des pays (injectées par PHP)
const paysData = <?php echo json_encode($pays); ?>;

// Variables globales
let paysSelectionne = null;

// Fonction de validation du formulaire
function validateForm() {
    let isValid = true;
    const adresseOption = document.querySelector('input[name="adresse_option"]:checked').value;

    // Réinitialiser les erreurs
    document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));

    if (adresseOption === 'nouvelle') {
        // Validation des champs requis
        const requiredFields = [
            { id: 'nom_complet', name: 'Nom complet' },
            { id: 'adresse_ligne1', name: 'Adresse ligne 1' },
            { id: 'code_postal', name: 'Code postal' },
            { id: 'ville', name: 'Ville' },
            { id: 'pays_id', name: 'Pays' },
            { id: 'telephone', name: 'Téléphone' }
        ];

        requiredFields.forEach(field => {
            const el = document.getElementById(field.id);
            let value = '';
            if (field.id === 'pays_id') {
                value = el.value; // hidden input
                if (!value) {
                    showError('error_pays_id', 'Veuillez sélectionner un pays');
                    isValid = false;
                }
            } else {
                value = el.value.trim();
                if (value === '') {
                    showError('error_' + field.id, 'Le champ ' + field.name + ' est requis');
                    el.classList.add('error');
                    isValid = false;
                } else {
                    // Vérifier les caractères dangereux (injection)
                    if (/[<>]/.test(value)) {
                        showError('error_' + field.id, 'Le champ contient des caractères non autorisés');
                        el.classList.add('error');
                        isValid = false;
                    }
                }
            }
        });

        // Validation spécifique téléphone (chiffres, espaces, +)
        const tel = document.getElementById('telephone').value.trim();
        if (tel !== '') {
            // On autorise +, chiffres, espaces, tirets
            if (!/^[0-9\s+\-]+$/.test(tel)) {
                showError('error_telephone', 'Le téléphone contient des caractères invalides');
                document.getElementById('telephone').classList.add('error');
                isValid = false;
            }
        }

        // Code postal (alphanumérique, peut contenir des tirets ou espaces)
        const cp = document.getElementById('code_postal').value.trim();
        if (cp !== '') {
            if (!/^[A-Za-z0-9\s\-]+$/.test(cp)) {
                showError('error_code_postal', 'Code postal invalide');
                document.getElementById('code_postal').classList.add('error');
                isValid = false;
            }
        }
    }

    // Validation du mode de paiement
    const modePaiement = document.querySelector('input[name="mode_paiement"]:checked');
    if (!modePaiement) {
        showError('error_mode_paiement', 'Veuillez choisir un mode de paiement');
        isValid = false;
    }

    return isValid;
}

function showError(elementId, message) {
    const errorEl = document.getElementById(elementId);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }
}

// Soumission du formulaire
document.getElementById('commandeForm').addEventListener('submit', function(e) {
    if (!validateForm()) {
        e.preventDefault();
        return false;
    }
});

// ========== Gestion des adresses ==========
function showExistingAddresses() {
    document.getElementById('nouvelle-adresse').classList.remove('active');
    document.querySelectorAll('input[name="adresse_id"]').forEach(el => el.disabled = false);
}

function showNewAddressForm() {
    document.getElementById('nouvelle-adresse').classList.add('active');
    document.querySelectorAll('input[name="adresse_id"]').forEach(el => {
        el.checked = false;
        el.disabled = true;
    });
}

function selectAddress(id) {
    document.querySelectorAll('input[name="adresse_id"]').forEach(el => {
        el.checked = (el.value == id);
    });
    document.getElementById('adresse_option_existante').checked = true;
    showExistingAddresses();
}

// ========== Autocomplétion pays ==========
document.addEventListener('DOMContentLoaded', function() {
    const paysSearch = document.getElementById('pays_search');
    const paysDropdown = document.getElementById('paysDropdown');
    const paysIdInput = document.getElementById('pays_id');
    const paysCodeInput = document.getElementById('pays_code');
    const paysIndicatifInput = document.getElementById('pays_indicatif');
    const indicatifDisplay = document.getElementById('indicatifDisplay');
    const telephoneInput = document.getElementById('telephone');

    // Si valeur pré-remplie (erreur de validation), restaurer l'affichage
    if (paysIdInput.value) {
        const pays = paysData.find(p => p.id == paysIdInput.value);
        if (pays) selectPays(pays);
    }

    paysSearch.addEventListener('input', function() {
        const query = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        
        if (query.length < 1) {
            paysDropdown.classList.remove('active');
            return;
        }

        const filtered = paysData.filter(p => {
            const nom = (p.pays || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const code = (p.ISO_3166_1_2_Letter_Code || '').toLowerCase();
            const indicatif = (p.ITU_T_Telephone_Code || '').replace('+', '');
            return nom.includes(query) || code.includes(query) || indicatif.includes(query);
        }).slice(0, 10);

        if (filtered.length > 0) {
            paysDropdown.innerHTML = filtered.map(p => `
                <div class="pays-item" onclick='selectPays(${JSON.stringify(p).replace(/'/g, "&#39;")})'>
                    <span class="drapeau">${getFlagEmoji(p.ISO_3166_1_2_Letter_Code)}</span>
                    <span class="nom">${p.pays}</span>
                    <span class="code">${p.ISO_3166_1_2_Letter_Code}</span>
                    <span class="indicatif">${p.ITU_T_Telephone_Code || '+XXX'}</span>
                </div>
            `).join('');
            paysDropdown.classList.add('active');
        } else {
            paysDropdown.innerHTML = '<div class="pays-item"><span>Aucun pays trouvé</span></div>';
            paysDropdown.classList.add('active');
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.pays-autocomplete-container')) {
            paysDropdown.classList.remove('active');
        }
    });

    paysSearch.addEventListener('focus', function() {
        if (this.value.length > 0) {
            paysDropdown.classList.add('active');
        }
    });

    telephoneInput.addEventListener('input', function(e) {
        // Nettoyer le numéro : garder chiffres, espaces, +, -
        let cleaned = this.value.replace(/[^\d\s\+\-]/g, '');
        this.value = cleaned;
    });
});

function selectPays(pays) {
    paysSelectionne = pays;
    document.getElementById('pays_id').value = pays.id;
    document.getElementById('pays_code').value = pays.ISO_3166_1_2_Letter_Code || '';
    document.getElementById('pays_indicatif').value = pays.ITU_T_Telephone_Code || '';
    document.getElementById('pays_search').value = pays.pays;
    document.getElementById('paysDropdown').classList.remove('active');
    
    document.getElementById('selectedDrapeau').textContent = getFlagEmoji(pays.ISO_3166_1_2_Letter_Code);
    document.getElementById('selectedNom').textContent = pays.pays;
    document.getElementById('selectedCode').textContent = pays.ISO_3166_1_2_Letter_Code || 'N/A';
    document.getElementById('selectedIndicatif').textContent = pays.ITU_T_Telephone_Code || '+XXX';
    document.getElementById('paysSelectionne').classList.remove('d-none');
    
    const indicatif = pays.ITU_T_Telephone_Code || '+XXX';
    document.getElementById('indicatifDisplay').textContent = indicatif;
    document.getElementById('telephone').focus();
}

function resetPays() {
    paysSelectionne = null;
    document.getElementById('pays_id').value = '';
    document.getElementById('pays_code').value = '';
    document.getElementById('pays_indicatif').value = '';
    document.getElementById('pays_search').value = '';
    document.getElementById('paysSelectionne').classList.add('d-none');
    document.getElementById('indicatifDisplay').textContent = '+XXX';
}

function getFlagEmoji(countryCode) {
    if (!countryCode) return '🏳️';
    const codePoints = countryCode.toUpperCase().split('').map(char => 127397 + char.charCodeAt());
    return String.fromCodePoint(...codePoints);
}

// Initialisation
<?php if (empty($adresses)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showNewAddressForm();
        document.getElementById('adresse_option_nouvelle').checked = true;
    });
<?php else: ?>
    document.addEventListener('DOMContentLoaded', function() {
        showExistingAddresses();
        document.getElementById('adresse_option_existante').checked = true;
    });
<?php endif; ?>
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>