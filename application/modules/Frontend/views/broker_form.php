<?php include VIEWPATH . 'includes/frontend/Header.php'; ?>
<style>
:root {
  --deep: #f8f9fa;
  --surface: #edf0f3;
  --card: #ffffff;
  --border: #dee2e6;
  --primary: #0f4c3a;
  --gold: #d4af37;
  --teal: #1db89e;
  --teal-light: #2ddfc4;
  --text: #212529;
  --muted: #6c757d;
  --radius: 14px;
  --error: #e05c6a;
  --error-light: #fce4e4;
  --success: #2ddfc4;
}
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--deep);
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-weight: 300;
  min-height: 100vh;
  overflow-x: hidden;
}

body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: radial-gradient(ellipse 80% 60% at 10% 10%, rgba(29,184,158,0.07) 0%, transparent 60%);
  pointer-events: none;
  z-index: 0;
}

header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(10,15,30,0.92);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
  padding: 0 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 72px;
}

.logo {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem;
  font-weight: 900;
  letter-spacing: 2px;
  color: var(--gold);
}
.logo span { color: var(--teal); }

.back-btn {
  background: rgba(29,184,158,0.12);
  border: 1px solid rgba(29,184,158,0.3);
  cursor: pointer;
  color: var(--teal-light);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.9rem;
  padding: 8px 20px;
  border-radius: 8px;
  transition: all 0.2s;
}
.back-btn:hover {
  background: rgba(29,184,158,0.2);
  transform: translateY(-1px);
}

.form-page {
  max-width: 820px;
  margin: 0 auto;
  padding: 60px 40px;
  position: relative;
  z-index: 1;
}

.form-hero {
  margin-bottom: 48px;
  text-align: center;
}

.form-hero .badge {
  display: inline-block;
  background: rgba(212,168,67,0.1);
  border: 1px solid rgba(212,168,67,0.3);
  color: var(--gold);
  font-size: 0.78rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 16px;
}

.form-hero h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.form-hero p {
  color: var(--muted);
  line-height: 1.7;
}

/* Stepper */
.stepper {
  display: flex;
  justify-content: space-between;
  margin-bottom: 40px;
  position: relative;
}
.stepper::before {
  content: '';
  position: absolute;
  top: 20px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--border);
  z-index: 0;
}
.step {
  position: relative;
  z-index: 1;
  text-align: center;
  flex: 1;
  transition: all 0.3s ease;
}
.step.hidden-step {
  display: none;
}
.step-circle {
  width: 40px;
  height: 40px;
  background: var(--surface);
  border: 2px solid var(--border);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 8px;
  font-weight: 600;
  color: var(--muted);
  transition: all 0.3s;
}
.step.active .step-circle {
  background: var(--teal);
  border-color: var(--teal);
  color: white;
  transform: scale(1.05);
}
.step.completed .step-circle {
  background: var(--success);
  border-color: var(--success);
  color: white;
}
.step.completed .step-circle::after {
  content: '✓';
  font-size: 1rem;
}
.step.completed .step-circle span {
  display: none;
}
.step-label {
  font-size: 0.75rem;
  color: var(--muted);
}
.step.active .step-label {
  color: var(--teal);
  font-weight: 600;
}

/* Form Sections */
.form-section {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 32px;
  margin-bottom: 24px;
  display: none;
  transition: all 0.3s ease;
}
.form-section.active-section {
  display: block;
  animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateX(30px); }
  to { opacity: 1; transform: translateX(0); }
}

.form-section-title {
  font-size: 0.75rem;
  font-weight: 500;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--teal);
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.form-section-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

.form-row {
  display: grid;
  gap: 16px;
  margin-bottom: 16px;
}
.form-row.cols-2 { grid-template-columns: 1fr 1fr; }
.form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }

.field {
  display: flex;
  flex-direction: column;
  gap: 7px;
}
label {
  font-size: 0.85rem;
  color: var(--muted);
  font-weight: 400;
}
label span.req {
  color: var(--gold);
  margin-left: 2px;
}

input[type="text"], input[type="email"], input[type="tel"],
select, textarea {
  background: var(--surface);
  border: 2px solid var(--border);
  border-radius: 9px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.95rem;
  padding: 12px 16px;
  outline: none;
  transition: all 0.2s;
  width: 100%;
}
input:focus, select:focus, textarea:focus {
  border-color: var(--teal);
  box-shadow: 0 0 0 3px rgba(29,184,158,0.15);
}
/* Style pour les champs invalides */
input.invalid-field, select.invalid-field, .country-search-container.invalid-field input {
  border-color: var(--error);
  background-color: var(--error-light);
  box-shadow: 0 0 0 2px rgba(224,92,106,0.2);
}
.error-message-field {
  color: var(--error);
  font-size: 0.7rem;
  margin-top: 4px;
  display: none;
}
.error-message-field.show {
  display: block;
}

/* Country Search */
.country-search-container { position: relative; }
.country-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 9px;
  max-height: 250px;
  overflow-y: auto;
  z-index: 100;
  display: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.country-dropdown.show { display: block; }
.country-option {
  padding: 10px 16px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}
.country-option:hover {
  background: rgba(29,184,158,0.1);
  color: var(--teal);
}
.country-option.selected {
  background: var(--teal);
  color: white;
}

/* Checkbox Groups */
.check-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 10px;
}
.check-item {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 9px;
  padding: 12px 14px;
  cursor: pointer;
  transition: all 0.2s;
  user-select: none;
}
.check-item:hover { border-color: rgba(29,184,158,0.4); }
.check-item input[type="checkbox"] { display: none; }
.check-box {
  width: 20px;
  height: 20px;
  border: 2px solid var(--border);
  border-radius: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s;
}
.check-item.checked .check-box {
  background: var(--teal);
  border-color: var(--teal);
}
.check-item.checked .check-box::after {
  content: '✓';
  font-size: 0.8rem;
  color: #fff;
  font-weight: 700;
}
.check-item.checked {
  border-color: rgba(29,184,158,0.4);
  background: rgba(29,184,158,0.06);
}

/* Confirm Items */
.confirm-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  transition: all 0.2s;
}
.confirm-item:last-child { border-bottom: none; }
.confirm-item input[type="checkbox"] { display: none; }
.confirm-box {
  width: 22px;
  height: 22px;
  border: 2px solid var(--border);
  border-radius: 5px;
  flex-shrink: 0;
  margin-top: 1px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.confirm-item.checked .confirm-box {
  background: var(--gold);
  border-color: var(--gold);
}
.confirm-item.checked .confirm-box::after {
  content: '✓';
  font-size: 0.8rem;
  color: #0a0f1e;
  font-weight: 800;
}
.confirm-item.invalid-confirm {
  background: var(--error-light);
  border-radius: 8px;
  padding: 12px;
  margin: 4px 0;
}

/* Phone Input */
.phone-input-group {
  display: flex;
  gap: 8px;
  align-items: center;
}
.phone-prefix {
  background: var(--surface);
  border: 2px solid var(--border);
  border-radius: 9px;
  padding: 12px 16px;
  font-family: monospace;
  font-size: 0.95rem;
  color: var(--teal);
  font-weight: 500;
  white-space: nowrap;
}

/* Navigation Buttons */
.navigation-buttons {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-top: 16px;
}
.nav-btn {
  padding: 14px 32px;
  border-radius: 10px;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}
.nav-btn-prev {
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
}
.nav-btn-prev:hover {
  background: var(--border);
  transform: translateX(-2px);
}
.nav-btn-next, .nav-btn-submit {
  background: linear-gradient(135deg, var(--teal), #14927d);
  color: white;
}
.nav-btn-next:hover, .nav-btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(29,184,158,0.4);
}
.nav-btn-submit {
  background: linear-gradient(135deg, var(--gold), #b8962e);
}
.nav-btn-submit:hover {
  box-shadow: 0 4px 12px rgba(212,175,55,0.4);
}
.nav-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

footer {
  border-top: 1px solid var(--border);
  text-align: center;
  padding: 32px;
  color: var(--muted);
  font-size: 0.85rem;
  position: relative;
  z-index: 1;
}
footer strong { color: var(--gold); }

@media (max-width: 640px) {
  header { padding: 0 20px; }
  .logo { font-size: 1.2rem; }
  .form-page { padding: 40px 20px; }
  .form-row.cols-2, .form-row.cols-3 { grid-template-columns: 1fr; }
  .form-section { padding: 24px; }
  .phone-input-group { flex-direction: column; align-items: stretch; }
  .step-label { font-size: 0.6rem; }
  .navigation-buttons { flex-direction: column; }
  .nav-btn { text-align: center; }
}
</style>

<div class="form-page">
  <div class="form-hero">
    <div class="badge">Courtier / Agent</div>
    <h2>Enregistrez votre profil</h2>
    <p>Complétez votre profil professionnel pour entrer dans notre réseau de courtiers qualifiés.</p>
  </div>

  <!-- Stepper -->
  <div class="stepper">
    <div class="step active" data-step="1">
      <div class="step-circle"><span>1</span></div>
      <div class="step-label">Identité</div>
    </div>
    <div class="step" data-step="2">
      <div class="step-circle"><span>2</span></div>
      <div class="step-label">Capacités</div>
    </div>
    <div class="step" data-step="3">
      <div class="step-circle"><span>3</span></div>
      <div class="step-label">Mandat & Confirmation</div>
    </div>
  </div>

  <form id="brokerForm" method="POST" action="<?= base_url('broker/store') ?>">
    
    <!-- ÉTAPE 1 : Identité professionnelle -->
    <div class="form-section active-section" data-section="1">
      <div class="form-section-title">Identité professionnelle</div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Nom complet <span class="req">*</span></label>
          <input type="text" name="full_name" id="full_name" placeholder="Marie Kamana" maxlength="150">
          <div class="error-message-field" id="error-full_name"></div>
        </div>
        <div class="field">
          <label>Nom de la firme <span class="req">*</span></label>
          <input type="text" name="firm_name" id="firm_name" placeholder="Capital Africa Ltd" maxlength="200">
          <div class="error-message-field" id="error-firm_name"></div>
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Juridiction d'incorporation</label>
          <input type="text" name="jurisdiction_of_incorporation" id="jurisdiction_of_incorporation" placeholder="Mauritius, Luxembourg..." maxlength="150">
        </div>
        <div class="field">
          <label>Numéro d'enregistrement</label>
          <input type="text" name="registration_number" id="registration_number" placeholder="REG-12345" maxlength="100">
        </div>
      </div>
      <div class="form-row cols-3">
        <div class="field">
          <label>Statut réglementaire</label>
          <select name="regulatory_status" id="regulatory_status">
            <option value="">— Choisir —</option>
            <option value="Licensed">Licensed</option>
            <option value="Exempt">Exempt</option>
            <option value="Unlicensed">Unlicensed</option>
          </select>
        </div>
        <div class="field">
          <label>Autorité régulatrice</label>
          <input type="text" name="regulatory_authority" id="regulatory_authority" placeholder="CMA, FSC, AMF..." maxlength="150">
        </div>
        <div class="field">
          <label>Pays <span class="req">*</span></label>
          <div class="country-search-container">
            <input type="text" id="countrySearchInput" class="country-search-input" placeholder="Rechercher un pays..." autocomplete="off">
            <input type="hidden" name="id_pays" id="selectedCountryId">
            <div id="countryDropdown" class="country-dropdown"></div>
          </div>
          <div class="error-message-field" id="error-country"></div>
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Email professionnel <span class="req">*</span></label>
          <input type="email" name="email" id="email" placeholder="contact@firm.com" maxlength="150">
          <div class="error-message-field" id="error-email"></div>
        </div>
        <div class="field">
          <label>Téléphone mobile</label>
          <div class="phone-input-group">
            <div class="phone-prefix" id="phonePrefixDisplay">+XXX</div>
            <input type="tel" name="mobile_phone" id="mobile_phone" placeholder="XX XXX XXX" style="flex:1">
          </div>
          <input type="hidden" name="mobile_phone_full" id="mobile_phone_full">
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="field">
          <label>WhatsApp</label>
          <div class="phone-input-group">
            <div class="phone-prefix" id="whatsappPrefixDisplay">+XXX</div>
            <input type="tel" name="whatsapp" id="whatsapp" placeholder="XX XXX XXX" style="flex:1">
          </div>
          <input type="hidden" name="whatsapp_full" id="whatsapp_full">
        </div>
        <div class="field">
          <label>Site web</label>
          <input type="text" name="corporate_website" id="corporate_website" placeholder="https://votresite.com" maxlength="200">
        </div>
      </div>
    </div>

    <!-- ÉTAPE 2 : Capacités & Profil investisseur -->
    <div class="form-section" data-section="2">
      <div class="form-section-title">Capacités & Profil investisseur</div>
      <p style="font-size:0.82rem;color:var(--muted);margin-bottom:12px;">Votre capacité professionnelle</p>
      <div class="check-grid">
        <label class="check-item"><input type="checkbox" name="capacity_investment_broker" value="1"><div class="check-box"></div><span class="check-text">Broker d'investissement</span></label>
        <label class="check-item"><input type="checkbox" name="capacity_placement_agent" value="1"><div class="check-box"></div><span class="check-text">Agent de placement</span></label>
        <label class="check-item"><input type="checkbox" name="capacity_corporate_finance_advisor" value="1"><div class="check-box"></div><span class="check-text">Conseiller Corp. Finance</span></label>
        <label class="check-item"><input type="checkbox" name="capacity_fund_manager" value="1"><div class="check-box"></div><span class="check-text">Gestionnaire de fonds</span></label>
        <label class="check-item"><input type="checkbox" name="capacity_family_office_rep" value="1"><div class="check-box"></div><span class="check-text">Family Office Rep.</span></label>
        <label class="check-item"><input type="checkbox" name="capacity_esg_advisor" value="1"><div class="check-box"></div><span class="check-text">Conseiller ESG</span></label>
        <label class="check-item"><input type="checkbox" name="capacity_independent_introducer" value="1"><div class="check-box"></div><span class="check-text">Introducteur indépendant</span></label>
      </div>
      <div class="form-row" style="margin-top:16px">
        <div class="field">
          <label>Autre capacité (préciser)</label>
          <input type="text" name="capacity_other" id="capacity_other" placeholder="Décrivez votre autre capacité..." maxlength="255">
        </div>
      </div>
      <p style="font-size:0.82rem;color:var(--muted);margin:20px 0 12px;">Type d'investisseurs que vous représentez</p>
      <div class="check-grid">
        <label class="check-item"><input type="checkbox" name="investor_private_equity" value="1"><div class="check-box"></div><span class="check-text">Private Equity</span></label>
        <label class="check-item"><input type="checkbox" name="investor_venture_capital" value="1"><div class="check-box"></div><span class="check-text">Venture Capital</span></label>
        <label class="check-item"><input type="checkbox" name="investor_esg_impact" value="1"><div class="check-box"></div><span class="check-text">ESG / Impact</span></label>
        <label class="check-item"><input type="checkbox" name="investor_dfi" value="1"><div class="check-box"></div><span class="check-text">DFI</span></label>
        <label class="check-item"><input type="checkbox" name="investor_institutional" value="1"><div class="check-box"></div><span class="check-text">Institutionnel</span></label>
        <label class="check-item"><input type="checkbox" name="investor_hnwi" value="1"><div class="check-box"></div><span class="check-text">HNWI</span></label>
        <label class="check-item"><input type="checkbox" name="investor_sovereign" value="1"><div class="check-box"></div><span class="check-text">Fonds souverain</span></label>
      </div>
    </div>

    <!-- ÉTAPE 3 : Mandat & Confirmations -->
    <div class="form-section" data-section="3">
      <div class="form-section-title">Mandat & Engagement</div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Ticket typique</label>
          <input type="text" name="typical_ticket_size" id="typical_ticket_size" placeholder="ex: 500K - 5M USD" maxlength="150">
        </div>
        <div class="field">
          <label>Couverture géographique</label>
          <input type="text" name="geographic_coverage" id="geographic_coverage" placeholder="ex: Afrique de l'Est, Europe..." maxlength="255">
        </div>
      </div>
      <p style="font-size:0.82rem;color:var(--muted);margin-bottom:12px;">Types de mandat acceptés</p>
      <div class="check-grid">
        <label class="check-item"><input type="checkbox" name="mandate_equity" value="1"><div class="check-box"></div><span class="check-text">Équité</span></label>
        <label class="check-item"><input type="checkbox" name="mandate_structured_debt" value="1"><div class="check-box"></div><span class="check-text">Dette structurée</span></label>
        <label class="check-item"><input type="checkbox" name="mandate_blended_finance" value="1"><div class="check-box"></div><span class="check-text">Finance mixte</span></label>
        <label class="check-item"><input type="checkbox" name="mandate_grant" value="1"><div class="check-box"></div><span class="check-text">Grant</span></label>
        <label class="check-item"><input type="checkbox" name="mandate_strategic_partnership" value="1"><div class="check-box"></div><span class="check-text">Partenariat stratégique</span></label>
        <label class="check-item"><input type="checkbox" name="mandate_full_program" value="1"><div class="check-box"></div><span class="check-text">Programme complet</span></label>
      </div>
      <div class="form-row" style="margin-top:20px">
        <div class="field">
          <label>Modèle d'engagement préféré</label>
          <select name="engagement_model" id="engagement_model">
            <option value="">— Choisir —</option>
            <option value="Success Commission">Success Commission</option>
            <option value="Retainer + Success Fee">Retainer + Success Fee</option>
            <option value="Referral Arrangement">Referral Arrangement</option>
            <option value="To be negotiated">To be negotiated</option>
          </select>
        </div>
      </div>
      
      <div class="form-section-title" style="margin-top:24px;">Déclarations & Confirmations</div>
      <label class="confirm-item" id="confirm-authorized-item"><input type="checkbox" name="confirm_authorized" value="1" class="required-confirm"><div class="confirm-box"></div><span class="confirm-text">Je confirme être autorisé à agir en tant qu'intermédiaire financier selon la réglementation applicable. <span class="req">*</span></span></label>
      <label class="confirm-item" id="confirm-aml-item"><input type="checkbox" name="confirm_aml_kyc" value="1" class="required-confirm"><div class="confirm-box"></div><span class="confirm-text">Je confirme respecter les obligations AML/KYC applicables à mon activité. <span class="req">*</span></span></label>
      <label class="confirm-item"><input type="checkbox" name="acknowledge_no_exclusivity" value="1"><div class="confirm-box"></div><span class="confirm-text">Je reconnais que cet enregistrement ne constitue pas un accord d'exclusivité avec NUFOTEC.</span></label>
      <label class="confirm-item"><input type="checkbox" name="understand_formal_mandate_required" value="1"><div class="confirm-box"></div><span class="confirm-text">Je comprends qu'un mandat formel sera requis avant toute mission officielle.</span></label>
      <div class="error-message-field" id="error-confirmations" style="margin-top:12px;"></div>
    </div>

    <!-- Navigation Buttons -->
    <div class="navigation-buttons">
      <button type="button" class="nav-btn nav-btn-prev" id="prevBtn" style="display:none;">← Précédent</button>
      <button type="button" class="nav-btn nav-btn-next" id="nextBtn">Suivant →</button>
      <button type="submit" class="nav-btn nav-btn-submit" id="submitBtn" style="display:none;">📤 Soumettre mon profil</button>
    </div>
  </form>
</div>

<footer>
  <p>© 2026 <strong>NUFOTEC</strong> — Plateforme d'investissement phytopharmaceutique africaine</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Données des pays
const countries = <?= json_encode($countries ?? []) ?>;
const phonePrefixes = {};
<?php foreach($countries as $country): ?>
  <?php if(!empty($country->ITU_T_Telephone_Code)): ?>
    phonePrefixes[<?= $country->id ?>] = '<?= preg_replace('/[^0-9+]/', '', $country->ITU_T_Telephone_Code) ?>';
  <?php endif; ?>
<?php endforeach; ?>

let currentStep = 1;
const totalSteps = 3;
let selectedCountryId = '';
let selectedCountryCode = '';

// Fonction pour marquer un champ comme invalide
function markInvalid(input, errorElement, message) {
  if(input) {
    input.classList.add('invalid-field');
    if(input.parentElement && input.parentElement.classList.contains('country-search-container')) {
      input.parentElement.classList.add('invalid-field');
    }
  }
  if(errorElement) {
    errorElement.textContent = message;
    errorElement.classList.add('show');
  }
}

function markValid(input, errorElement) {
  if(input) {
    input.classList.remove('invalid-field');
    if(input.parentElement && input.parentElement.classList.contains('country-search-container')) {
      input.parentElement.classList.remove('invalid-field');
    }
  }
  if(errorElement) {
    errorElement.classList.remove('show');
    errorElement.textContent = '';
  }
}

// Validation en temps réel de l'étape 1
function validateStep1Realtime() {
  let isValid = true;
  
  const fullName = document.getElementById('full_name');
  const firmName = document.getElementById('firm_name');
  const email = document.getElementById('email');
  const countryId = document.getElementById('selectedCountryId');
  
  if(!fullName.value.trim()) {
    markInvalid(fullName, document.getElementById('error-full_name'), 'Le nom complet est requis');
    isValid = false;
  } else {
    markValid(fullName, document.getElementById('error-full_name'));
  }
  
  if(!firmName.value.trim()) {
    markInvalid(firmName, document.getElementById('error-firm_name'), 'Le nom de la firme est requis');
    isValid = false;
  } else {
    markValid(firmName, document.getElementById('error-firm_name'));
  }
  
  const emailValue = email.value.trim();
  const emailRegex = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
  if(!emailValue) {
    markInvalid(email, document.getElementById('error-email'), 'L\'email est requis');
    isValid = false;
  } else if(!emailRegex.test(emailValue)) {
    markInvalid(email, document.getElementById('error-email'), 'Email invalide');
    isValid = false;
  } else {
    markValid(email, document.getElementById('error-email'));
  }
  
  if(!countryId.value) {
    const countryContainer = document.querySelector('.country-search-container');
    const countryInput = document.getElementById('countrySearchInput');
    markInvalid(countryContainer, document.getElementById('error-country'), 'Veuillez sélectionner un pays');
    markInvalid(countryInput, null, '');
    isValid = false;
  } else {
    markValid(document.querySelector('.country-search-container'), document.getElementById('error-country'));
    markValid(document.getElementById('countrySearchInput'), null);
  }
  
  return isValid;
}

// Validation étape 3 temps réel - CORRIGÉE
function validateStep3Realtime() {
  const confirmAuthorized = document.querySelector('input[name="confirm_authorized"]')?.checked;
  const confirmAmlKyc = document.querySelector('input[name="confirm_aml_kyc"]')?.checked;
  const confirmAuthorizedItem = document.getElementById('confirm-authorized-item');
  const confirmAmlItem = document.getElementById('confirm-aml-item');
  const errorConfirm = document.getElementById('error-confirmations');
  
  // Supprimer la classe invalid-confirm quand la case est cochée
  if(confirmAuthorized) {
    confirmAuthorizedItem.classList.remove('invalid-confirm');
  } else {
    confirmAuthorizedItem.classList.add('invalid-confirm');
  }
  
  if(confirmAmlKyc) {
    confirmAmlItem.classList.remove('invalid-confirm');
  } else {
    confirmAmlItem.classList.add('invalid-confirm');
  }
  
  if(!confirmAuthorized || !confirmAmlKyc) {
    errorConfirm.textContent = 'Vous devez accepter toutes les confirmations obligatoires (*)';
    errorConfirm.classList.add('show');
    return false;
  } else {
    errorConfirm.classList.remove('show');
    return true;
  }
}

// Navigation
function updateStepper() {
  for(let i = 1; i <= totalSteps; i++) {
    const step = document.querySelector(`.step[data-step="${i}"]`);
    const section = document.querySelector(`.form-section[data-section="${i}"]`);
    if(i < currentStep) {
      step.classList.add('completed');
      step.classList.remove('active');
    } else if(i === currentStep) {
      step.classList.add('active');
      step.classList.remove('completed');
      if(section) section.classList.add('active-section');
    } else {
      step.classList.remove('active', 'completed');
      if(section) section.classList.remove('active-section');
    }
  }
  
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  
  if(prevBtn) prevBtn.style.display = currentStep === 1 ? 'none' : 'flex';
  if(nextBtn) nextBtn.style.display = currentStep === totalSteps ? 'none' : 'flex';
  if(submitBtn) submitBtn.style.display = currentStep === totalSteps ? 'flex' : 'none';
}

function validateStep(step) {
  if(step === 1) {
    return validateStep1Realtime();
  }
  if(step === 3) {
    return validateStep3Realtime();
  }
  return true;
}

function nextStep() {
  if(validateStep(currentStep)) {
    if(currentStep === 1) {
      buildFullNumbers();
    }
    currentStep++;
    updateStepper();
    updateStepVisibility();
  }
}

function prevStep() {
  if(currentStep > 1) {
    currentStep--;
    updateStepper();
    updateStepVisibility();
  }
}

function updateStepVisibility() {
  for(let i = 1; i <= totalSteps; i++) {
    const step = document.querySelector(`.step[data-step="${i}"]`);
    if(i < currentStep) {
      step.classList.add('hidden-step');
    } else {
      step.classList.remove('hidden-step');
    }
  }
}

// Initialisation pays
function initCountrySearch() {
  const searchInput = document.getElementById('countrySearchInput');
  const dropdown = document.getElementById('countryDropdown');
  const hiddenInput = document.getElementById('selectedCountryId');
  if(!searchInput) return;
  
  searchInput.addEventListener('focus', () => filterCountries(searchInput.value));
  searchInput.addEventListener('input', (e) => filterCountries(e.target.value));
  document.addEventListener('click', (e) => {
    if(!searchInput.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.remove('show');
  });
  
  function filterCountries(searchTerm) {
    const filtered = countries.filter(c => c.pays && c.pays.toLowerCase().includes(searchTerm.toLowerCase()));
    if(filtered.length === 0) {
      dropdown.innerHTML = '<div class="country-option">Aucun pays trouvé</div>';
    } else {
      dropdown.innerHTML = filtered.map(c => `<div class="country-option" data-id="${c.id}" data-code="${phonePrefixes[c.id] || '+XXX'}">${c.pays} <span style="color:var(--gold);font-size:0.75rem;">${phonePrefixes[c.id] || '+XXX'}</span></div>`).join('');
      dropdown.querySelectorAll('.country-option').forEach(opt => {
        opt.addEventListener('click', () => {
          selectedCountryId = opt.dataset.id;
          selectedCountryCode = opt.dataset.code;
          searchInput.value = opt.childNodes[0].nodeValue.trim();
          hiddenInput.value = selectedCountryId;
          document.getElementById('phonePrefixDisplay').innerHTML = selectedCountryCode;
          document.getElementById('whatsappPrefixDisplay').innerHTML = selectedCountryCode;
          dropdown.classList.remove('show');
          markValid(searchInput, document.getElementById('error-country'));
          markValid(hiddenInput, null);
        });
      });
    }
    dropdown.classList.add('show');
  }
  filterCountries('');
}

// Toggle checkbox - VERSION CORRIGÉE
function initCheckboxes() {
  // Pour les check-items normaux
  document.querySelectorAll('.check-item').forEach(item => {
    const cb = item.querySelector('input[type="checkbox"]');
    if(cb && cb.checked) item.classList.add('checked');
    
    item.addEventListener('click', (e) => {
      if(e.target !== cb) {
        item.classList.toggle('checked');
        cb.checked = item.classList.contains('checked');
      }
    });
  });
  
  // Pour les confirm-items (avec validation spéciale)
  document.querySelectorAll('.confirm-item').forEach(item => {
    const cb = item.querySelector('input[type="checkbox"]');
    if(cb && cb.checked) item.classList.add('checked');
    
    // Clic sur l'élément parent
    item.addEventListener('click', (e) => {
      if(e.target !== cb) {
        item.classList.toggle('checked');
        cb.checked = item.classList.contains('checked');
      }
      // Déclencher la validation immédiatement
      validateStep3Realtime();
    });
    
    // Écouteur direct sur la checkbox (important !)
    if(cb) {
      cb.addEventListener('change', function() {
        if(this.checked) {
          item.classList.add('checked');
        } else {
          item.classList.remove('checked');
        }
        validateStep3Realtime();
      });
    }
  });
}

// Construction numéros
function buildFullNumbers() {
  const phonePrefix = document.getElementById('phonePrefixDisplay');
  const mobilePhone = document.getElementById('mobile_phone');
  const whatsappPrefix = document.getElementById('whatsappPrefixDisplay');
  const whatsappNumber = document.getElementById('whatsapp');
  const mobilePhoneFull = document.getElementById('mobile_phone_full');
  const whatsappFull = document.getElementById('whatsapp_full');
  
  if(phonePrefix && mobilePhone && mobilePhoneFull) {
    const prefix = phonePrefix.innerText;
    const number = mobilePhone.value;
    if(number && prefix !== '+XXX') {
      mobilePhoneFull.value = prefix + number.replace(/[\s\-\(\)]/g, '');
    }
  }
  
  if(whatsappPrefix && whatsappNumber && whatsappFull) {
    const prefix = whatsappPrefix.innerText;
    const number = whatsappNumber.value;
    if(number && prefix !== '+XXX') {
      whatsappFull.value = prefix + number.replace(/[\s\-\(\)]/g, '');
    }
  }
}

// Envoi formulaire
const brokerForm = document.getElementById('brokerForm');
if(brokerForm) {
  brokerForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    if(!validateStep(3)) return;
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Envoi en cours...';
    
    buildFullNumbers();
    const formData = new FormData(this);
    
    try {
      const response = await fetch('<?= base_url("broker/store") ?>', {
        method: 'POST', 
        body: formData, 
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const result = await response.json();
      
      if(result.success) {
        Swal.fire({ 
          icon: 'success', 
          title: 'Inscription réussie !', 
          text: result.message, 
          confirmButtonColor: '#1db89e', 
          timer: 3000, 
          timerProgressBar: true 
        }).then(() => { 
          window.location.href = result.redirect || '<?= base_url("broker/set_password_view") ?>'; 
        });
      } else {
        let errorMsg = result.message || 'Erreur lors de l\'enregistrement.';
        if(result.errors) errorMsg = Object.values(result.errors).join('\n');
        Swal.fire({ icon: 'error', title: 'Erreur', text: errorMsg, confirmButtonColor: '#1db89e' });
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    } catch(error) {
      console.error('Erreur:', error);
      Swal.fire({ icon: 'error', title: 'Erreur réseau', text: 'Impossible de contacter le serveur.', confirmButtonColor: '#1db89e' });
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    }
  });
}

// Event listeners pour la navigation
const nextBtn = document.getElementById('nextBtn');
const prevBtn = document.getElementById('prevBtn');
if(nextBtn) nextBtn.addEventListener('click', nextStep);
if(prevBtn) prevBtn.addEventListener('click', prevStep);

// Validation temps réel sur les champs étape 1
['full_name', 'firm_name', 'email'].forEach(id => {
  const input = document.getElementById(id);
  if(input) {
    input.addEventListener('input', () => validateStep1Realtime());
    input.addEventListener('blur', () => validateStep1Realtime());
  }
});

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
  initCountrySearch();
  initCheckboxes();
  updateStepper();
  updateStepVisibility();
  validateStep1Realtime();
  validateStep3Realtime(); // Validation initiale des confirmations
});
</script>
<?php include VIEWPATH . 'includes/frontend/Footer.php'; ?>