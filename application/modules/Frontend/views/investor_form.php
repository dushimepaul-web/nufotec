<?php
// Initialiser les variables pour éviter les erreurs
$hero = $hero ?? null;
$textes = $textes ?? [];
?>

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
input.invalid-field, select.invalid-field {
  border-color: var(--error);
  background-color: var(--error-light);
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
.check-text { font-size: 0.88rem; }

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
  .step-label { font-size: 0.6rem; }
  .navigation-buttons { flex-direction: column; }
  .nav-btn { text-align: center; }
}
</style>

<!-- SECTION HERO CMS (optionnelle) -->
<?php if (!empty($hero)): ?>
    <section class="page-hero">
        <div class="conteneur position-relative">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1><?= $hero['titre_section'] ?? '' ?></h1>
                    <?php if (!empty($hero['sous_titre'])): ?>
                        <p><?= strip_tags($hero['sous_titre']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="form-page">
  <div class="form-hero">
    <div class="badge">Investisseur</div>
    <h2>Enregistrez votre intérêt</h2>
    <p>Remplissez ce formulaire pour nous indiquer vos préférences d'investissement. Notre équipe vous contactera sous 48h.</p>
  </div>

  <!-- Stepper -->
  <div class="stepper">
    <div class="step active" data-step="1">
      <div class="step-circle"><span>1</span></div>
      <div class="step-label">Identité</div>
    </div>
    <div class="step" data-step="2">
      <div class="step-circle"><span>2</span></div>
      <div class="step-label">Intérêts</div>
    </div>
    <div class="step" data-step="3">
      <div class="step-circle"><span>3</span></div>
      <div class="step-label">Confirmation</div>
    </div>
  </div>

  <form id="investorForm" method="POST" action="<?= base_url('investors/store') ?>">
    
    <!-- ÉTAPE 1 : Identité -->
    <div class="form-section active-section" data-section="1">
      <div class="form-section-title">Informations personnelles</div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Nom complet <span class="req">*</span></label>
          <input type="text" name="full_name" id="full_name" placeholder="Jean Dupont" maxlength="150">
          <div class="error-message-field" id="error-full_name"></div>
        </div>
        <div class="field">
          <label>Organisation</label>
          <input type="text" name="organization" id="organization" placeholder="Nom de votre société" maxlength="150">
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Titre / Poste</label>
          <input type="text" name="position_title" id="position_title" placeholder="Directeur des investissements" maxlength="150">
        </div>
        <div class="field">
          <label>Pays <span class="req">*</span></label>
          <select name="id_pays" id="id_pays">
            <option value="">— Sélectionner —</option>
            <?php foreach($countries as $country): ?>
              <option value="<?= $country->id ?>"><?= htmlspecialchars($country->pays) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="error-message-field" id="error-id_pays"></div>
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Email <span class="req">*</span></label>
          <input type="email" name="email" id="email" placeholder="jean@example.com" maxlength="150">
          <div class="error-message-field" id="error-email"></div>
        </div>
        <div class="field">
          <label>Téléphone</label>
          <input type="tel" name="phone" id="phone" placeholder="+257 79 000 000" maxlength="50">
        </div>
      </div>
    </div>

    <!-- ÉTAPE 2 : Intérêts -->
    <div class="form-section" data-section="2">
      <div class="form-section-title">Type d'engagement souhaité</div>
      <div class="check-grid">
        <label class="check-item"><input type="checkbox" name="interest_equity" value="1"><div class="check-box"></div><span class="check-text">Équité (Equity)</span></label>
        <label class="check-item"><input type="checkbox" name="interest_debt" value="1"><div class="check-box"></div><span class="check-text">Dette structurée</span></label>
        <label class="check-item"><input type="checkbox" name="interest_blended_finance" value="1"><div class="check-box"></div><span class="check-text">Finance mixte</span></label>
        <label class="check-item"><input type="checkbox" name="interest_grant" value="1"><div class="check-box"></div><span class="check-text">Grant / Subvention</span></label>
        <label class="check-item"><input type="checkbox" name="interest_strategic_partnership" value="1"><div class="check-box"></div><span class="check-text">Partenariat stratégique</span></label>
        <label class="check-item"><input type="checkbox" name="interest_technical_collaboration" value="1"><div class="check-box"></div><span class="check-text">Collaboration technique</span></label>
        <label class="check-item"><input type="checkbox" name="interest_offtake_distribution" value="1"><div class="check-box"></div><span class="check-text">Offtake / Distribution</span></label>
      </div>
      <div class="form-row" style="margin-top:16px">
        <div class="field">
          <label>Autre intérêt (préciser)</label>
          <input type="text" name="interest_other" id="interest_other" placeholder="Décrivez votre intérêt spécifique..." maxlength="255">
        </div>
      </div>

      <div class="form-section-title" style="margin-top:24px;">Capacité & Priorités</div>
      <div class="form-row cols-2">
        <div class="field">
          <label>Fourchette d'engagement</label>
          <select name="commitment_range" id="commitment_range">
            <option value="">— Choisir —</option>
            <option value="Below 250K">Below 250K €</option>
            <option value="250K-1M">250K - 1M €</option>
            <option value="1M-5M">1M - 5M €</option>
            <option value="5M+">5M+ €</option>
            <option value="To be discussed">À discuter</option>
          </select>
        </div>
        <div class="field">
          <label>Horizon d'investissement</label>
          <select name="timeline" id="timeline">
            <option value="">— Choisir —</option>
            <option value="Immediate">Immediate</option>
            <option value="3-6 months">3-6 mois</option>
            <option value="6-12 months">6-12 mois</option>
            <option value="Exploratory">Exploratoire</option>
          </select>
        </div>
      </div>

      <p style="font-size:0.82rem;color:var(--muted);margin:16px 0 12px;">Segments d'intérêt</p>
      <div class="check-grid">
        <label class="check-item"><input type="checkbox" name="focus_research_lab" value="1"><div class="check-box"></div><span class="check-text">🔬 Labo de recherche</span></label>
        <label class="check-item"><input type="checkbox" name="focus_gmp_facility" value="1"><div class="check-box"></div><span class="check-text">🏭 Facility GMP</span></label>
        <label class="check-item"><input type="checkbox" name="focus_medicinal_plant" value="1"><div class="check-box"></div><span class="check-text">🌿 Plantes médicinales</span></label>
        <label class="check-item"><input type="checkbox" name="focus_commercialization" value="1"><div class="check-box"></div><span class="check-text">📦 Commercialisation</span></label>
        <label class="check-item"><input type="checkbox" name="focus_full_platform" value="1"><div class="check-box"></div><span class="check-text">🚀 Plateforme complète</span></label>
      </div>

      <div class="form-section-title" style="margin-top:24px;">Message stratégique</div>
      <div class="field">
        <textarea name="strategic_message" id="strategic_message" rows="4" placeholder="Décrivez votre vision, vos attentes ou des questions spécifiques..."></textarea>
      </div>
    </div>

    <!-- ÉTAPE 3 : Confirmation -->
    <div class="form-section" data-section="3">
      <div class="form-section-title">Confirmations</div>
      <label class="check-item" id="confirm-contact-item"><input type="checkbox" name="agree_contact" value="1" class="required-confirm"><div class="check-box"></div><span class="check-text">J'accepte d'être contacté par l'équipe NUFOTEC concernant cet intérêt d'investissement. <span class="req">*</span></span></label>
      <label class="check-item"><input type="checkbox" name="non_binding_confirmation" value="1"><div class="check-box"></div><span class="check-text">Je confirme que cet enregistrement est non-contraignant et exploratoire.</span></label>
      <div class="error-message-field" id="error-confirmations" style="margin-top:12px;"></div>
    </div>

    <!-- Navigation Buttons -->
    <div class="navigation-buttons">
      <button type="button" class="nav-btn nav-btn-prev" id="prevBtn" style="display:none;">← Précédent</button>
      <button type="button" class="nav-btn nav-btn-next" id="nextBtn">Suivant →</button>
      <button type="submit" class="nav-btn nav-btn-submit" id="submitBtn" style="display:none;">📤 Soumettre ma demande</button>
    </div>
  </form>
</div>

<!-- SECTIONS TEXTE CMS (optionnelles) -->
<?php if (!empty($textes) && is_array($textes)): ?>
  <?php foreach ($textes as $texte): ?>
    <section class="section-texte">
      <div class="conteneur">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="text-center">
              <?php if (!empty($texte['titre_section'])): ?>
                <h2><?= htmlspecialchars($texte['titre_section']) ?></h2>
              <?php endif; ?>
              <?php if (!empty($texte['contenu_texte'])): ?>
                <div><?= $texte['contenu_texte'] ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endforeach; ?>
<?php endif; ?>

<footer>
  <p>© 2026 <strong>NUFOTEC</strong> — Plateforme d'investissement phytopharmaceutique africaine</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentStep = 1;
const totalSteps = 3;

// Fonction pour marquer un champ comme invalide
function markInvalid(input, errorElement, message) {
  if(input) {
    input.classList.add('invalid-field');
  }
  if(errorElement) {
    errorElement.textContent = message;
    errorElement.classList.add('show');
  }
}

function markValid(input, errorElement) {
  if(input) {
    input.classList.remove('invalid-field');
  }
  if(errorElement) {
    errorElement.classList.remove('show');
    errorElement.textContent = '';
  }
}

// Validation étape 1
function validateStep1Realtime() {
  let isValid = true;
  
  const fullName = document.getElementById('full_name');
  const email = document.getElementById('email');
  const idPays = document.getElementById('id_pays');
  
  if(!fullName.value.trim()) {
    markInvalid(fullName, document.getElementById('error-full_name'), 'Le nom complet est requis');
    isValid = false;
  } else {
    markValid(fullName, document.getElementById('error-full_name'));
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
  
  if(!idPays.value) {
    markInvalid(idPays, document.getElementById('error-id_pays'), 'Veuillez sélectionner un pays');
    isValid = false;
  } else {
    markValid(idPays, document.getElementById('error-id_pays'));
  }
  
  return isValid;
}

// Validation étape 3
function validateStep3Realtime() {
  const agreeContact = document.querySelector('input[name="agree_contact"]')?.checked;
  const errorConfirm = document.getElementById('error-confirmations');
  
  if(!agreeContact) {
    errorConfirm.textContent = 'Vous devez accepter d\'être contacté';
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
  
  document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'flex';
  document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'flex';
  document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'flex' : 'none';
}

function validateStep(step) {
  if(step === 1) return validateStep1Realtime();
  if(step === 3) return validateStep3Realtime();
  return true;
}

function nextStep() {
  if(validateStep(currentStep)) {
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

// Toggle checkbox
function initCheckboxes() {
  document.querySelectorAll('.check-item').forEach(item => {
    const cb = item.querySelector('input[type="checkbox"]');
    if(cb && cb.checked) item.classList.add('checked');
    
    item.addEventListener('click', (e) => {
      if(e.target !== cb) {
        item.classList.toggle('checked');
        cb.checked = item.classList.contains('checked');
      }
      if(cb.classList.contains('required-confirm')) {
        validateStep3Realtime();
      }
    });
    
    if(cb) {
      cb.addEventListener('change', function() {
        if(this.checked) {
          item.classList.add('checked');
        } else {
          item.classList.remove('checked');
        }
        if(this.classList.contains('required-confirm')) {
          validateStep3Realtime();
        }
      });
    }
  });
}

// Envoi formulaire
const investorForm = document.getElementById('investorForm');
if(investorForm) {
  investorForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    if(!validateStep(3)) return;
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Envoi en cours...';
    
    const formData = new FormData(this);
    
    try {
      const response = await fetch('<?= base_url("investors/store") ?>', {
        method: 'POST', 
        body: formData, 
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const result = await response.json();
      
      if(result.success) {
        Swal.fire({ 
          icon: 'success', 
          title: 'Demande envoyée !', 
          text: result.message, 
          confirmButtonColor: '#1db89e', 
          timer: 3000, 
          timerProgressBar: true 
        }).then(() => { 
          investorForm.reset();
          document.querySelectorAll('.check-item').forEach(i => i.classList.remove('checked'));
          currentStep = 1;
          updateStepper();
          updateStepVisibility();
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

// Event listeners navigation
document.getElementById('nextBtn').addEventListener('click', nextStep);
document.getElementById('prevBtn').addEventListener('click', prevStep);

// Validation temps réel étape 1
['full_name', 'email', 'id_pays'].forEach(id => {
  const input = document.getElementById(id);
  if(input) {
    input.addEventListener('input', () => validateStep1Realtime());
    input.addEventListener('blur', () => validateStep1Realtime());
    input.addEventListener('change', () => validateStep1Realtime());
  }
});

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
  initCheckboxes();
  updateStepper();
  updateStepVisibility();
  validateStep1Realtime();
  validateStep3Realtime();
});
</script>

<?php include VIEWPATH . 'includes/frontend/Footer.php'; ?>