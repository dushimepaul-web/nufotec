<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGF PHYTOMED - Newsletter Internationale</title>
    <style>
        :root {
            --primary: #0f4c3a;
            --primary-light: #1a6b52;
            --primary-dark: #0a3326;
            --accent: #d4af37;
            --accent-hover: #b8962e;
            --accent-light: #e8d5a3;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #dee2e6;
            --error: #dc3545;
            --success: #28a745;
            --info: #17a2b8;
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Section Subscribe */
        .subscribe-section {
            width: 100%;
            max-width: 900px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 20px;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .subscribe-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.15) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .subscribe-container {
            position: relative;
            z-index: 1;
        }

        .subscribe-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .subscribe-icon {
            width: 80px;
            height: 80px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: var(--primary-dark);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .subscribe-title {
            color: var(--accent);
            font-size: 2.2rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .subscribe-subtitle {
            color: var(--gray-light);
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            position: relative;
        }

        .form-label {
            display: block;
            color: var(--accent);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Country Selector */
        .country-selector {
            position: relative;
        }

        .country-display {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            color: var(--light);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
        }

        .country-display:hover {
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.1);
        }

        .country-flag {
            font-size: 24px;
        }

        .country-info {
            flex: 1;
            text-align: left;
        }

        .country-name {
            font-weight: 600;
            color: var(--light);
            font-size: 0.95rem;
        }

        .country-code {
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: bold;
        }

        .country-arrow {
            color: var(--accent);
            transition: transform 0.3s ease;
        }

        .country-selector.active .country-arrow {
            transform: rotate(180deg);
        }

        /* Dropdown */
        .country-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 8px;
            background: var(--primary-dark);
            border: 2px solid var(--accent);
            border-radius: 12px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            box-shadow: var(--shadow-xl);
        }

        .country-dropdown.active {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .country-search {
            padding: 12px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            position: sticky;
            top: 0;
            background: var(--primary-dark);
            z-index: 10;
        }

        .country-search input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--light);
            font-size: 0.9rem;
        }

        .country-search input::placeholder {
            color: rgba(248, 249, 250, 0.5);
        }

        .country-list {
            list-style: none;
            padding: 0;
        }

        .country-item {
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .country-item:hover {
            background: rgba(212, 175, 55, 0.1);
        }

        .country-item.selected {
            background: rgba(212, 175, 55, 0.2);
            border-left: 3px solid var(--accent);
        }

        /* Phone Input Group */
        .phone-input-group {
            display: flex;
            gap: 10px;
        }

        .phone-prefix {
            background: rgba(212, 175, 55, 0.2);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            padding: 16px;
            color: var(--accent);
            font-weight: bold;
            min-width: 80px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-wrapper {
            position: relative;
            flex: 1;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            color: var(--light);
            font-size: 1rem;
            transition: var(--transition);
            outline: none;
        }

        .form-input::placeholder {
            color: rgba(248, 249, 250, 0.5);
        }

        .form-input:focus {
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
        }

        .form-input.valid {
            border-color: var(--success);
            background: rgba(40, 167, 69, 0.1);
        }

        .form-input.invalid {
            border-color: var(--error);
            background: rgba(220, 53, 69, 0.1);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Validation States */
        .validation-message {
            margin-top: 10px;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            display: none;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease;
        }

        .validation-message.show {
            display: flex;
        }

        .validation-message.error {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .validation-message.success {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .validation-message.info {
            background: rgba(23, 162, 184, 0.2);
            color: #15aabf;
            border: 1px solid rgba(23, 162, 184, 0.3);
        }

        /* Submit Button */
        .submit-wrapper {
            text-align: center;
            margin-top: 30px;
        }

        .subscribe-btn {
            padding: 18px 50px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            border: none;
            border-radius: 50px;
            color: var(--primary-dark);
            font-size: 1.1rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .subscribe-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: 0.5s;
        }

        .subscribe-btn:hover::before {
            left: 100%;
        }

        .subscribe-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        .subscribe-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(15, 76, 58, 0.3);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .subscribe-btn.loading .spinner {
            display: inline-block;
        }

        .subscribe-btn.loading .btn-text {
            display: none;
        }

        /* Alternative Option */
        .alternative-option {
            text-align: center;
            margin: 20px 0;
            color: rgba(248, 249, 250, 0.6);
            font-size: 0.9rem;
        }

        .alternative-option button {
            background: none;
            border: none;
            color: var(--accent);
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.9rem;
            margin-left: 5px;
        }

        /* Privacy Note */
        .privacy-note {
            margin-top: 25px;
            text-align: center;
            color: rgba(248, 249, 250, 0.6);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .privacy-note a {
            color: var(--accent);
            text-decoration: none;
        }

        /* Input Mode Toggle */
        .input-mode-toggle {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .mode-btn {
            padding: 10px 25px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 25px;
            color: var(--light);
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mode-btn.active {
            background: rgba(212, 175, 55, 0.2);
            border-color: var(--accent);
            color: var(--accent);
        }

        .mode-btn:hover:not(.active) {
            border-color: rgba(212, 175, 55, 0.6);
        }

        /* Hidden */
        .hidden {
            display: none !important;
        }

        /* Scrollbar */
        .country-dropdown::-webkit-scrollbar {
            width: 8px;
        }

        .country-dropdown::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .country-dropdown::-webkit-scrollbar-thumb {
            background: var(--accent);
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <section class="subscribe-section">
        <div class="subscribe-container">
            <div class="subscribe-header">
                <div class="subscribe-icon">✉</div>
                <h2 class="subscribe-title">Restez Informé</h2>
                <p class="subscribe-subtitle">
                    Inscrivez-vous pour recevoir nos actualités, rapports d'investissement et opportunités commerciales
                </p>
            </div>

            <!-- Toggle Mode -->
            <div class="input-mode-toggle">
                <button type="button" class="mode-btn active" data-mode="phone" onclick="switchMode('phone')">
                    📱 Par Téléphone
                </button>
                <button type="button" class="mode-btn" data-mode="email" onclick="switchMode('email')">
                    📧 Par Email
                </button>
            </div>

            <form id="subscribeForm" novalidate>
                <!-- Phone Mode -->
                <div id="phoneMode" class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Pays</label>
                        <div class="country-selector" id="countrySelector">
                            <div class="country-display" onclick="toggleDropdown()">
                                <span class="country-flag" id="selectedFlag">🇧🇮</span>
                                <div class="country-info">
                                    <div class="country-name" id="selectedCountry">Burundi</div>
                                    <div class="country-code" id="selectedCode">+257</div>
                                </div>
                                <span class="country-arrow">▼</span>
                            </div>
                            <div class="country-dropdown" id="countryDropdown">
                                <div class="country-search">
                                    <input type="text" id="searchCountry" placeholder="Rechercher un pays..." oninput="filterCountries(this.value)">
                                </div>
                                <ul class="country-list" id="countryList">
                                    <!-- Rempli par JavaScript -->
                                </ul>
                            </div>
                            <input type="hidden" name="country_code" id="countryCodeInput" value="+257">
                            <input type="hidden" name="country_iso" id="countryIsoInput" value="BI">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Numéro de Téléphone</label>
                        <div class="phone-input-group">
                            <div class="phone-prefix" id="phonePrefix">+257</div>
                            <div class="input-wrapper">
                                <input 
                                    type="tel" 
                                    class="form-input" 
                                    id="phoneInput" 
                                    name="phone"
                                    placeholder="79 666 438"
                                    oninput="validatePhone(this.value)"
                                    onblur="validatePhone(this.value)"
                                >
                            </div>
                        </div>
                        <div class="validation-message" id="phoneValidation"></div>
                    </div>
                </div>

                <!-- Email Mode (Hidden by default) -->
                <div id="emailMode" class="form-grid hidden">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Adresse Email</label>
                        <div class="input-wrapper">
                            <input 
                                type="email" 
                                class="form-input" 
                                id="emailInput" 
                                name="email"
                                placeholder="exemple@domaine.com"
                                oninput="validateEmail(this.value)"
                                onblur="validateEmail(this.value)"
                            >
                        </div>
                        <div class="validation-message" id="emailValidation"></div>
                    </div>
                </div>

                <div class="alternative-option">
                    <span id="altText">Vous préférez utiliser votre email ?</span>
                    <button type="button" onclick="toggleMode()">Cliquez ici</button>
                </div>

                <div class="submit-wrapper">
                    <button type="submit" class="subscribe-btn" id="submitBtn">
                        <span class="spinner"></span>
                        <span class="btn-text">S'inscrire maintenant</span>
                    </button>
                </div>
            </form>

            <p class="privacy-note">
                <span>🔒</span>
                Vos données sont sécurisées. 
                <a href="#">Politique de confidentialité</a>
            </p>
        </div>
    </section>

    <script>
        // Données des pays (simulation de la réponse PHP)
        const countriesData = [
            { id: "1", name: "Afghanistan", code: "+93", iso: "AF", flag: "🇦🇫" },
            { id: "194", name: "Burundi", code: "+257", iso: "BI", flag: "🇧🇮" },
            { id: "50", name: "États-Unis", code: "+1", iso: "US", flag: "🇺🇸" },
            { id: "94", name: "France", code: "+33", iso: "FR", flag: "🇫🇷" },
            { id: "64", name: "Allemagne", code: "+49", iso: "DE", flag: "🇩🇪" },
            { id: "82", name: "Italie", code: "+39", iso: "IT", flag: "🇮🇹" },
            { id: "184", name: "Royaume-Uni", code: "+44", iso: "GB", flag: "🇬🇧" },
            { id: "204", name: "Chine", code: "+86", iso: "CN", flag: "🇨🇳" },
            { id: "256", name: "Japon", code: "+81", iso: "JP", flag: "🇯🇵" },
            { id: "50", name: "Canada", code: "+1", iso: "CA", flag: "🇨🇦" },
            { id: "50", name: "République Démocratique du Congo", code: "+243", iso: "CD", flag: "🇨🇩" },
            { id: "50", name: "Congo", code: "+242", iso: "CG", flag: "🇨🇬" },
            { id: "50", name: "Rwanda", code: "+250", iso: "RW", flag: "🇷🇼" },
            { id: "50", name: "Tanzanie", code: "+255", iso: "TZ", flag: "🇹🇿" },
            { id: "50", name: "Kenya", code: "+254", iso: "KE", flag: "🇰🇪" },
            { id: "50", name: "Ouganda", code: "+256", iso: "UG", flag: "🇺🇬" },
            { id: "50", name: "Afrique du Sud", code: "+27", iso: "ZA", flag: "🇿🇦" },
            { id: "50", name: "Nigeria", code: "+234", iso: "NG", flag: "🇳🇬" },
            { id: "50", name: "Ghana", code: "+233", iso: "GH", flag: "🇬🇭" },
            { id: "50", name: "Sénégal", code: "+221", iso: "SN", flag: "🇸🇳" },
            { id: "50", name: "Côte d'Ivoire", code: "+225", iso: "CI", flag: "🇨🇮" },
            { id: "50", name: "Cameroun", code: "+237", iso: "CM", flag: "🇨🇲" },
            { id: "50", name: "Maroc", code: "+212", iso: "MA", flag: "🇲🇦" },
            { id: "50", name: "Algérie", code: "+213", iso: "DZ", flag: "🇩🇿" },
            { id: "50", name: "Tunisie", code: "+216", iso: "TN", flag: "🇹🇳" },
            { id: "50", name: "Égypte", code: "+20", iso: "EG", flag: "🇪🇬" },
            { id: "50", name: "Éthiopie", code: "+251", iso: "ET", flag: "🇪🇹" },
            { id: "50", name: "Inde", code: "+91", iso: "IN", flag: "🇮🇳" },
            { id: "50", name: "Brésil", code: "+55", iso: "BR", flag: "🇧🇷" },
            { id: "50", name: "Mexique", code: "+52", iso: "MX", flag: "🇲🇽" },
            { id: "50", name: "Argentine", code: "+54", iso: "AR", flag: "🇦🇷" },
            { id: "50", name: "Australie", code: "+61", iso: "AU", flag: "🇦🇺" },
            { id: "50", name: "Russie", code: "+7", iso: "RU", flag: "🇷🇺" },
            { id: "50", name: "Turquie", code: "+90", iso: "TR", flag: "🇹🇷" },
            { id: "50", name: "Arabie Saoudite", code: "+966", iso: "SA", flag: "🇸🇦" },
            { id: "50", name: "Émirats Arabes Unis", code: "+971", iso: "AE", flag: "🇦🇪" },
            { id: "50", name: "Israël", code: "+972", iso: "IL", flag: "🇮🇱" },
            { id: "50", name: "Espagne", code: "+34", iso: "ES", flag: "🇪🇸" },
            { id: "50", name: "Portugal", code: "+351", iso: "PT", flag: "🇵🇹" },
            { id: "50", name: "Pays-Bas", code: "+31", iso: "NL", flag: "🇳🇱" },
            { id: "50", name: "Belgique", code: "+32", iso: "BE", flag: "🇧🇪" },
            { id: "50", name: "Suisse", code: "+41", iso: "CH", flag: "🇨🇭" },
            { id: "50", name: "Autriche", code: "+43", iso: "AT", flag: "🇦🇹" },
            { id: "50", name: "Suède", code: "+46", iso: "SE", flag: "🇸🇪" },
            { id: "50", name: "Norvège", code: "+47", iso: "NO", flag: "🇳🇴" },
            { id: "50", name: "Danemark", code: "+45", iso: "DK", flag: "🇩🇰" },
            { id: "50", name: "Finlande", code: "+358", iso: "FI", flag: "🇫🇮" },
            { id: "50", name: "Pologne", code: "+48", iso: "PL", flag: "🇵🇱" },
            { id: "50", name: "Ukraine", code: "+380", iso: "UA", flag: "🇺🇦" },
            { id: "50", name: "Roumanie", code: "+40", iso: "RO", flag: "🇷🇴" },
            { id: "50", name: "Grèce", code: "+30", iso: "GR", flag: "🇬🇷" },
            { id: "50", name: "Hongrie", code: "+36", iso: "HU", flag: "🇭🇺" },
            { id: "50", name: "République Tchèque", code: "+420", iso: "CZ", flag: "🇨🇿" },
            { id: "50", name: "Slovaquie", code: "+421", iso: "SK", flag: "🇸🇰" },
            { id: "50", name: "Croatie", code: "+385", iso: "HR", flag: "🇭🇷" },
            { id: "50", name: "Serbie", code: "+381", iso: "RS", flag: "🇷🇸" },
            { id: "50", name: "Bulgarie", code: "+359", iso: "BG", flag: "🇧🇬" },
            { id: "50", name: "Biélorussie", code: "+375", iso: "BY", flag: "🇧🇾" },
            { id: "50", name: "Lituanie", code: "+370", iso: "LT", flag: "🇱🇹" },
            { id: "50", name: "Lettonie", code: "+371", iso: "LV", flag: "🇱🇻" },
            { id: "50", name: "Estonie", code: "+372", iso: "EE", flag: "🇪🇪" },
            { id: "50", name: "Moldavie", code: "+373", iso: "MD", flag: "🇲🇩" },
            { id: "50", name: "Arménie", code: "+374", iso: "AM", flag: "🇦🇲" },
            { id: "50", name: "Géorgie", code: "+995", iso: "GE", flag: "🇬🇪" },
            { id: "50", name: "Azerbaïdjan", code: "+994", iso: "AZ", flag: "🇦🇿" },
            { id: "50", name: "Kazakhstan", code: "+7", iso: "KZ", flag: "🇰🇿" },
            { id: "50", name: "Ouzbékistan", code: "+998", iso: "UZ", flag: "🇺🇿" },
            { id: "50", name: "Pakistan", code: "+92", iso: "PK", flag: "🇵🇰" },
            { id: "50", name: "Bangladesh", code: "+880", iso: "BD", flag: "🇧🇩" },
            { id: "50", name: "Indonésie", code: "+62", iso: "ID", flag: "🇮🇩" },
            { id: "50", name: "Malaisie", code: "+60", iso: "MY", flag: "🇲🇾" },
            { id: "50", name: "Philippines", code: "+63", iso: "PH", flag: "🇵🇭" },
            { id: "50", name: "Singapour", code: "+65", iso: "SG", flag: "🇸🇬" },
            { id: "50", name: "Thaïlande", code: "+66", iso: "TH", flag: "🇹🇭" },
            { id: "50", name: "Vietnam", code: "+84", iso: "VN", flag: "🇻🇳" },
            { id: "50", name: "Corée du Sud", code: "+82", iso: "KR", flag: "🇰🇷" },
            { id: "50", name: "Corée du Nord", code: "+850", iso: "KP", flag: "🇰🇵" },
            { id: "50", name: "Mongolie", code: "+976", iso: "MN", flag: "🇲🇳" },
            { id: "50", name: "Népal", code: "+977", iso: "NP", flag: "🇳🇵" },
            { id: "50", name: "Sri Lanka", code: "+94", iso: "LK", flag: "🇱🇰" },
            { id: "50", name: "Myanmar", code: "+95", iso: "MM", flag: "🇲🇲" },
            { id: "50", name: "Cambodge", code: "+855", iso: "KH", flag: "🇰🇭" },
            { id: "50", name: "Laos", code: "+856", iso: "LA", flag: "🇱🇦" },
            { id: "50", name: "Bhoutan", code: "+975", iso: "BT", flag: "🇧🇹" },
            { id: "50", name: "Maldives", code: "+960", iso: "MV", flag: "🇲🇻" },
            { id: "50", name: "Afghanistan", code: "+93", iso: "AF", flag: "🇦🇫" },
            { id: "50", name: "Iran", code: "+98", iso: "IR", flag: "🇮🇷" },
            { id: "50", name: "Irak", code: "+964", iso: "IQ", flag: "🇮🇶" },
            { id: "50", name: "Syrie", code: "+963", iso: "SY", flag: "🇸🇾" },
            { id: "50", name: "Liban", code: "+961", iso: "LB", flag: "🇱🇧" },
            { id: "50", name: "Jordanie", code: "+962", iso: "JO", flag: "🇯🇴" },
            { id: "50", name: "Yémen", code: "+967", iso: "YE", flag: "🇾🇪" },
            { id: "50", name: "Oman", code: "+968", iso: "OM", flag: "🇴🇲" },
            { id: "50", name: "Qatar", code: "+974", iso: "QA", flag: "🇶🇦" },
            { id: "50", name: "Koweït", code: "+965", iso: "KW", flag: "🇰🇼" },
            { id: "50", name: "Bahreïn", code: "+973", iso: "BH", flag: "🇧🇭" },
            { id: "50", name: "Chypre", code: "+357", iso: "CY", flag: "🇨🇾" },
            { id: "50", name: "Malte", code: "+356", iso: "MT", flag: "🇲🇹" },
            { id: "50", name: "Islande", code: "+354", iso: "IS", flag: "🇮🇸" },
            { id: "50", name: "Irlande", code: "+353", iso: "IE", flag: "🇮🇪" },
            { id: "50", name: "Luxembourg", code: "+352", iso: "LU", flag: "🇱🇺" },
            { id: "50", name: "Monaco", code: "+377", iso: "MC", flag: "🇲🇨" },
            { id: "50", name: "Liechtenstein", code: "+423", iso: "LI", flag: "🇱🇮" },
            { id: "50", name: "Andorre", code: "+376", iso: "AD", flag: "🇦🇩" },
            { id: "50", name: "Saint-Marin", code: "+378", iso: "SM", flag: "🇸🇲" },
            { id: "50", name: "Vatican", code: "+379", iso: "VA", flag: "🇻🇦" },
            { id: "50", name: "Chili", code: "+56", iso: "CL", flag: "🇨🇱" },
            { id: "50", name: "Colombie", code: "+57", iso: "CO", flag: "🇨🇴" },
            { id: "50", name: "Pérou", code: "+51", iso: "PE", flag: "🇵🇪" },
            { id: "50", name: "Venezuela", code: "+58", iso: "VE", flag: "🇻🇪" },
            { id: "50", name: "Équateur", code: "+593", iso: "EC", flag: "🇪🇨" },
            { id: "50", name: "Bolivie", code: "+591", iso: "BO", flag: "🇧🇴" },
            { id: "50", name: "Paraguay", code: "+595", iso: "PY", flag: "🇵🇾" },
            { id: "50", name: "Uruguay", code: "+598", iso: "UY", flag: "🇺🇾" },
            { id: "50", name: "Guyana", code: "+592", iso: "GY", flag: "🇬🇾" },
            { id: "50", name: "Suriname", code: "+597", iso: "SR", flag: "🇸🇷" },
            { id: "50", name: "Guyane française", code: "+594", iso: "GF", flag: "🇬🇫" },
            { id: "50", name: "Guatemala", code: "+502", iso: "GT", flag: "🇬🇹" },
            { id: "50", name: "Belize", code: "+501", iso: "BZ", flag: "🇧🇿" },
            { id: "50", name: "Salvador", code: "+503", iso: "SV", flag: "🇸🇻" },
            { id: "50", name: "Honduras", code: "+504", iso: "HN", flag: "🇭🇳" },
            { id: "50", name: "Nicaragua", code: "+505", iso: "NI", flag: "🇳🇮" },
            { id: "50", name: "Costa Rica", code: "+506", iso: "CR", flag: "🇨🇷" },
            { id: "50", name: "Panama", code: "+507", iso: "PA", flag: "🇵🇦" },
            { id: "50", name: "Cuba", code: "+53", iso: "CU", flag: "🇨🇺" },
            { id: "50", name: "Jamaïque", code: "+1-876", iso: "JM", flag: "🇯🇲" },
            { id: "50", name: "Haïti", code: "+509", iso: "HT", flag: "🇭🇹" },
            { id: "50", name: "République dominicaine", code: "+1-809", iso: "DO", flag: "🇩🇴" },
            { id: "50", name: "Trinité-et-Tobago", code: "+1-868", iso: "TT", flag: "🇹🇹" },
            { id: "50", name: "Barbade", code: "+1-246", iso: "BB", flag: "🇧🇧" },
            { id: "50", name: "Saint-Vincent-et-les-Grenadines", code: "+1-784", iso: "VC", flag: "🇻🇨" },
            { id: "50", name: "Grenade", code: "+1-473", iso: "GD", flag: "🇬🇩" },
            { id: "50", name: "Sainte-Lucie", code: "+1-758", iso: "LC", flag: "🇱🇨" },
            { id: "50", name: "Dominique", code: "+1-767", iso: "DM", flag: "🇩🇲" },
            { id: "50", name: "Antigua-et-Barbuda", code: "+1-268", iso: "AG", flag: "🇦🇬" },
            { id: "50", name: "Saint-Kitts-et-Nevis", code: "+1-869", iso: "KN", flag: "🇰🇳" },
            { id: "50", name: "Bahamas", code: "+1-242", iso: "BS", flag: "🇧🇸" },
            { id: "50", name: "Fidji", code: "+679", iso: "FJ", flag: "🇫🇯" },
            { id: "50", name: "Papouasie-Nouvelle-Guinée", code: "+675", iso: "PG", flag: "🇵🇬" },
            { id: "50", name: "Nouvelle-Zélande", code: "+64", iso: "NZ", flag: "🇳🇿" },
            { id: "50", name: "Vanuatu", code: "+678", iso: "VU", flag: "🇻🇺" },
            { id: "50", name: "Salomon", code: "+677", iso: "SB", flag: "🇸🇧" },
            { id: "50", name: "Samoa", code: "+685", iso: "WS", flag: "🇼🇸" },
            { id: "50", name: "Tonga", code: "+676", iso: "TO", flag: "🇹🇴" },
            { id: "50", name: "Kiribati", code: "+686", iso: "KI", flag: "🇰🇮" },
            { id: "50", name: "Tuvalu", code: "+688", iso: "TV", flag: "🇹🇻" },
            { id: "50", name: "Nauru", code: "+674", iso: "NR", flag: "🇳🇷" },
            { id: "50", name: "Palaos", code: "+680", iso: "PW", flag: "🇵🇼" },
            { id: "50", name: "Marshall", code: "+692", iso: "MH", flag: "🇲🇭" },
            { id: "50", name: "Micronésie", code: "+691", iso: "FM", flag: "🇫🇲" },
            { id: "50", name: "Guam", code: "+1-671", iso: "GU", flag: "🇬🇺" },
            { id: "50", name: "Samoa américaines", code: "+1-684", iso: "AS", flag: "🇦🇸" },
            { id: "50", name: "Îles Mariannes du Nord", code: "+1-670", iso: "MP", flag: "🇲🇵" },
            { id: "50", name: "Porto Rico", code: "+1-787", iso: "PR", flag: "🇵🇷" },
            { id: "50", name: "Îles Vierges des États-Unis", code: "+1-340", iso: "VI", flag: "🇻🇮" },
            { id: "50", name: "Îles Vierges britanniques", code: "+1-284", iso: "VG", flag: "🇻🇬" },
            { id: "50", name: "Anguilla", code: "+1-264", iso: "AI", flag: "🇦🇮" },
            { id: "50", name: "Montserrat", code: "+1-664", iso: "MS", flag: "🇲🇸" },
            { id: "50", name: "Îles Caïmans", code: "+1-345", iso: "KY", flag: "🇰🇾" },
            { id: "50", name: "Turks-et-Caïcos", code: "+1-649", iso: "TC", flag: "🇹🇨" },
            { id: "50", name: "Bermudes", code: "+1-441", iso: "BM", flag: "🇧🇲" },
            { id: "50", name: "Groenland", code: "+299", iso: "GL", flag: "🇬🇱" },
            { id: "50", name: "Îles Féroé", code: "+298", iso: "FO", flag: "🇫🇴" },
            { id: "50", name: "Île de Man", code: "+44", iso: "IM", flag: "🇮🇲" },
            { id: "50", name: "Jersey", code: "+44", iso: "JE", flag: "🇯🇪" },
            { id: "50", name: "Guernesey", code: "+44", iso: "GG", flag: "🇬🇬" },
            { id: "50", name: "Gibraltar", code: "+350", iso: "GI", flag: "🇬🇮" },
            { id: "50", name: "Malte", code: "+356", iso: "MT", flag: "🇲🇹" },
            { id: "50", name: "Chypre", code: "+357", iso: "CY", flag: "🇨🇾" },
            { id: "50", name: "Luxembourg", code: "+352", iso: "LU", flag: "🇱🇺" },
            { id: "50", name: "Monaco", code: "+377", iso: "MC", flag: "🇲🇨" },
            { id: "50", name: "Liechtenstein", code: "+423", iso: "LI", flag: "🇱🇮" },
            { id: "50", name: "Andorre", code: "+376", iso: "AD", flag: "🇦🇩" },
            { id: "50", name: "Saint-Marin", code: "+378", iso: "SM", flag: "🇸🇲" },
            { id: "50", name: "Vatican", code: "+379", iso: "VA", flag: "🇻🇦" }
        ];

        let currentMode = 'phone';
        let selectedCountry = countriesData.find(c => c.iso === 'BI') || countriesData[0];
        let filteredCountries = [...countriesData];

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            renderCountryList();
            
            // Fermer le dropdown en cliquant à l'extérieur
            document.addEventListener('click', function(e) {
                const selector = document.getElementById('countrySelector');
                if (!selector.contains(e.target)) {
                    selector.classList.remove('active');
                    document.getElementById('countryDropdown').classList.remove('active');
                }
            });

            // Formulaire
            document.getElementById('subscribeForm').addEventListener('submit', handleSubmit);
        });

        // Rendu de la liste des pays
        function renderCountryList() {
            const list = document.getElementById('countryList');
            list.innerHTML = filteredCountries.map(country => `
                <li class="country-item ${country.iso === selectedCountry.iso ? 'selected' : ''}" 
                    onclick="selectCountry('${country.iso}')">
                    <span class="country-flag">${country.flag}</span>
                    <div class="country-info">
                        <div class="country-name">${country.name}</div>
                        <div class="country-code">${country.code}</div>
                    </div>
                </li>
            `).join('');
        }

        // Filtrer les pays
        function filterCountries(search) {
            const term = search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            filteredCountries = countriesData.filter(c => 
                c.name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes(term) ||
                c.code.includes(term)
            );
            renderCountryList();
        }

        // Sélectionner un pays
        function selectCountry(iso) {
            selectedCountry = countriesData.find(c => c.iso === iso);
            
            document.getElementById('selectedFlag').textContent = selectedCountry.flag;
            document.getElementById('selectedCountry').textContent = selectedCountry.name;
            document.getElementById('selectedCode').textContent = selectedCountry.code;
            document.getElementById('phonePrefix').textContent = selectedCountry.code;
            document.getElementById('countryCodeInput').value = selectedCountry.code;
            document.getElementById('countryIsoInput').value = selectedCountry.iso;
            
            toggleDropdown();
            renderCountryList(); // Pour mettre à jour la sélection
            
            // Revalider le téléphone si présent
            const phoneInput = document.getElementById('phoneInput');
            if (phoneInput.value) {
                validatePhone(phoneInput.value);
            }
        }

        // Toggle dropdown
        function toggleDropdown() {
            const selector = document.getElementById('countrySelector');
            const dropdown = document.getElementById('countryDropdown');
            selector.classList.toggle('active');
            dropdown.classList.toggle('active');
            
            if (dropdown.classList.contains('active')) {
                document.getElementById('searchCountry').focus();
            }
        }

        // Switch mode (boutons toggle)
        function switchMode(mode) {
            currentMode = mode;
            
            // Update buttons
            document.querySelectorAll('.mode-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.mode === mode);
            });
            
            // Update visibility
            if (mode === 'phone') {
                document.getElementById('phoneMode').classList.remove('hidden');
                document.getElementById('emailMode').classList.add('hidden');
                document.getElementById('altText').textContent = 'Vous préférez utiliser votre email ?';
            } else {
                document.getElementById('phoneMode').classList.add('hidden');
                document.getElementById('emailMode').classList.remove('hidden');
                document.getElementById('altText').textContent = 'Vous préférez utiliser votre téléphone ?';
            }
            
            // Clear validations
            clearValidations();
        }

        // Toggle mode (lien alternatif)
        function toggleMode() {
            switchMode(currentMode === 'phone' ? 'email' : 'phone');
        }

        // Validation téléphone
        function validatePhone(value) {
            const input = document.getElementById('phoneInput');
            const validation = document.getElementById('phoneValidation');
            
            if (!value.trim()) {
                input.classList.remove('valid', 'invalid');
                validation.classList.remove('show');
                return false;
            }
            
            // Nettoyer le numéro
            const cleaned = value.replace(/[\s\-\(\)]/g, '');
            const code = selectedCountry.code.replace('+', '');
            
            // Validation selon le pays
            let isValid = false;
            let message = '';
            
            // Burundi spécifique
            if (selectedCountry.iso === 'BI') {
                const regex = /^(79|68|71|72|75|76|69)\d{6}$/;
                if (regex.test(cleaned)) {
                    isValid = true;
                    message = 'Numéro burundais valide ✓';
                } else {
                    message = 'Format invalide. Utilisez: 79 XXX XXX ou 68 XXX XXX';
                }
            } 
            // USA/Canada
            else if (['US', 'CA'].includes(selectedCountry.iso)) {
                const regex = /^\d{10}$/;
                if (regex.test(cleaned)) {
                    isValid = true;
                    message = 'Numéro valide ✓';
                } else {
                    message = '10 chiffres requis (ex: 234 567 8901)';
                }
            }
            // France
            else if (selectedCountry.iso === 'FR') {
                const regex = /^[1-9]\d{8}$/;
                if (regex.test(cleaned)) {
                    isValid = true;
                    message = 'Numéro français valide ✓';
                } else {
                    message = '9 chiffres après le 0 (ex: 6 12 34 56 78)';
                }
            }
            // RDC
            else if (selectedCountry.iso === 'CD') {
                const regex = /^(81|82|83|84|85|89|90|91|97|98|99)\d{7}$/;
                if (regex.test(cleaned)) {
                    isValid = true;
                    message = 'Numéro RDC valide ✓';
                } else {
                    message = 'Format: 81 XXX XXXX ou 97 XXX XXXX';
                }
            }
            // Générique
            else {
                if (cleaned.length >= 8 && cleaned.length <= 15) {
                    isValid = true;
                    message = 'Format accepté ✓';
                } else {
                    message = 'Entre 8 et 15 chiffres requis';
                }
            }
            
            // UI Update
            input.classList.remove('valid', 'invalid');
            validation.classList.remove('show', 'success', 'error');
            
            if (isValid) {
                input.classList.add('valid');
                validation.classList.add('show', 'success');
            } else {
                input.classList.add('invalid');
                validation.classList.add('show', 'error');
            }
            
            validation.textContent = message;
            return isValid;
        }

        // Validation email
        function validateEmail(value) {
            const input = document.getElementById('emailInput');
            const validation = document.getElementById('emailValidation');
            const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            
            if (!value.trim()) {
                input.classList.remove('valid', 'invalid');
                validation.classList.remove('show');
                return false;
            }
            
            const isValid = regex.test(value);
            
            input.classList.remove('valid', 'invalid');
            validation.classList.remove('show', 'success', 'error');
            
            if (isValid) {
                input.classList.add('valid');
                validation.classList.add('show', 'success');
                validation.textContent = 'Email valide ✓';
            } else {
                input.classList.add('invalid');
                validation.classList.add('show', 'error');
                validation.textContent = 'Format d\'email invalide (ex: nom@domaine.com)';
            }
            
            return isValid;
        }

        // Clear validations
        function clearValidations() {
            document.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('valid', 'invalid');
                input.value = '';
            });
            document.querySelectorAll('.validation-message').forEach(msg => {
                msg.classList.remove('show');
            });
        }

        // Soumission formulaire
        async function handleSubmit(e) {
            e.preventDefault();
            
            let isValid = false;
            let formData = new FormData();
            
            if (currentMode === 'phone') {
                const phone = document.getElementById('phoneInput').value;
                isValid = validatePhone(phone);
                
                if (isValid) {
                    formData.append('type', 'phone');
                    formData.append('country_code', selectedCountry.code);
                    formData.append('country_iso', selectedCountry.iso);
                    formData.append('phone', phone);
                }
            } else {
                const email = document.getElementById('emailInput').value;
                isValid = validateEmail(email);
                
                if (isValid) {
                    formData.append('type', 'email');
                    formData.append('email', email);
                }
            }
            
            if (!isValid) {
                // Shake animation sur le bouton
                const btn = document.getElementById('submitBtn');
                btn.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => btn.style.animation = '', 500);
                return;
            }
            
            // Loading state
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            
            // Simulation envoi AJAX
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Success
            btn.classList.remove('loading');
            btn.querySelector('.btn-text').textContent = 'Inscription réussie ! ✓';
            btn.style.background = 'var(--success)';
            
            // Message success
            const validation = currentMode === 'phone' ? 
                document.getElementById('phoneValidation') : 
                document.getElementById('emailValidation');
                
            validation.className = 'validation-message show success';
            validation.textContent = currentMode === 'phone' ? 
                `Merci ! Vous recevrez nos actualités sur ${selectedCountry.code} ${document.getElementById('phoneInput').value}` :
                `Merci ! Vous recevrez nos actualités sur ${document.getElementById('emailInput').value}`;
            
            // Reset après 4s
            setTimeout(() => {
                btn.querySelector('.btn-text').textContent = 'S\'inscrire maintenant';
                btn.style.background = '';
                btn.disabled = false;
                clearValidations();
            }, 4000);
            
            // Ici, envoyez formData à votre controller PHP
            console.log('Données à envoyer:', Object.fromEntries(formData));
        }
    </script>

</body>
</html>