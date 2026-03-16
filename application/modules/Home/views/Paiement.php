<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    .paiement-container {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--shadow-lg);
        margin: 40px 0;
    }

    .paiement-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .commande-recap {
        background: var(--light);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
    }

    .commande-recap h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 15px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid var(--gray-light);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--dark);
    }

    .info-value {
        color: var(--primary);
        font-weight: 500;
    }

    .payment-instructions {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid var(--gray-light);
    }

    .payment-instructions h4 {
        font-size: 18px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .payment-instructions p {
        margin-bottom: 10px;
        line-height: 1.6;
    }

    .btn-payer {
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
        margin-top: 20px;
    }

    .btn-payer:hover {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .btn-payer:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .alert-info {
        background: rgba(13, 110, 253, 0.1);
        border-left: 4px solid #0d6efd;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
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
</style>

<div class="container py-4">
    <div class="paiement-container">
        <h1 class="paiement-title"><i class="bi bi-credit-card"></i> Paiement de la commande</h1>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="commande-recap">
                    <h3><i class="bi bi-receipt"></i> Récapitulatif commande</h3>
                    <div class="info-row">
                        <span class="info-label">Numéro commande :</span>
                        <span class="info-value"><?php echo $commande->numero_commande; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total TTC :</span>
                        <span class="info-value"><?php echo number_format($commande->total_ttc, 0, ',', ' '); ?> $</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mode de paiement :</span>
                        <span class="info-value"><?php echo $commande->mode_paiement; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="payment-instructions">
                    <?php if ($commande->mode_paiement == 'Lumicash'): ?>
                        <h4><i class="bi bi-phone"></i> Paiement via Lumicash</h4>
                        <div class="alert-info">
                            <i class="bi bi-info-circle"></i> Veuillez suivre les instructions ci-dessous pour effectuer votre paiement.
                        </div>
                        <p><strong>Étape 1 :</strong> Composez le *161# sur votre téléphone.</p>
                        <p><strong>Étape 2 :</strong> Sélectionnez l'option "Paiement".</p>
                        <p><strong>Étape 3 :</strong> Entrez le numéro de marchand : <strong>XXXXX</strong></p>
                        <p><strong>Étape 4 :</strong> Entrez le montant de <?php echo number_format($commande->total_ttc, 0, ',', ' '); ?> $.</p>
                        <p><strong>Étape 5 :</strong> Confirmez votre paiement avec votre code secret.</p>
                        <p><strong>Étape 6 :</strong> Après paiement, vous recevrez un SMS de confirmation contenant un code de transaction. Saisissez-le ci-dessous :</p>
                        
                        <form action="<?php echo base_url('commande/verifier_paiement/' . $commande->id); ?>" method="post">
                            <div class="form-group">
                                <label for="transaction_code">Code de transaction Lumicash</label>
                                <input type="text" class="form-control" id="transaction_code" name="transaction_code" required>
                            </div>
                            <button type="submit" class="btn-payer">Confirmer le paiement</button>
                        </form>

                    <?php elseif ($commande->mode_paiement == 'EcoCash'): ?>
                        <h4><i class="bi bi-phone"></i> Paiement via EcoCash</h4>
                        <p><strong>Étape 1 :</strong> Composez le *151# sur votre téléphone.</p>
                        <p><strong>Étape 2 :</strong> Choisissez l'option "Payer".</p>
                        <p><strong>Étape 3 :</strong> Entrez le numéro de compte marchand : <strong>XXXXX</strong></p>
                        <p><strong>Étape 4 :</strong> Saisissez le montant de <?php echo number_format($commande->total_ttc, 0, ',', ' '); ?> $.</p>
                        <p><strong>Étape 5 :</strong> Confirmez avec votre code secret.</p>
                        <p><strong>Étape 6 :</strong> Saisissez le code de transaction reçu par SMS :</p>
                        <form action="<?php echo base_url('commande/verifier_paiement/' . $commande->id); ?>" method="post">
                            <div class="form-group">
                                <label for="transaction_code">Code de transaction EcoCash</label>
                                <input type="text" class="form-control" id="transaction_code" name="transaction_code" required>
                            </div>
                            <button type="submit" class="btn-payer">Confirmer le paiement</button>
                        </form>

                    <?php elseif ($commande->mode_paiement == 'Carte Bancaire'): ?>
                        <h4><i class="bi bi-credit-card"></i> Paiement par carte bancaire</h4>
                        <p>Vous allez être redirigé vers notre plateforme de paiement sécurisé.</p>
                        <form action="https://secure.paiement.com" method="post">
                            <input type="hidden" name="amount" value="<?php echo $commande->total_ttc; ?>">
                            <input type="hidden" name="currency" value="XAF">
                            <input type="hidden" name="order_id" value="<?php echo $commande->numero_commande; ?>">
                            <button type="submit" class="btn-payer">Payer par carte</button>
                        </form>

                    <?php elseif ($commande->mode_paiement == 'Bancobu Inoti'): ?>
                        <h4><i class="bi bi-bank"></i> Virement bancaire</h4>
                        <p>Veuillez effectuer un virement sur le compte ci-dessous :</p>
                        <ul>
                            <li>Banque : Banque Commerciale du Burundi</li>
                            <li>RIB : 12345 67890 12345678901 12</li>
                            <li>Montant : <?php echo number_format($commande->total_ttc, 0, ',', ' '); ?> $</li>
                            <li>Référence : <?php echo $commande->numero_commande; ?></li>
                        </ul>
                        <p>Dès réception du virement, votre commande sera traitée.</p>

                    <?php else: ?>
                        <h4><i class="bi bi-bank"></i> Autre mode de paiement</h4>
                        <p>Merci de contacter notre service client pour finaliser votre paiement.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>