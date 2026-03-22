<?php
$pageTitle = "Inscription";
require_once '../includes/db.php';
require_once '../includes/header.php';

$errors  = [];
$success = '';

// ── Tunisian governorates ──────────────────────────────────────────────────────
$villes = [
    'Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba',
    'Kairouan','Kasserine','Kébili','La Manouba','Le Kef','Mahdia',
    'Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana',
    'Sousse','Tataouine','Tozeur','Tunis','Zaghouan',
];

// ── Password strength helper ──────────────────────────────────────────────────
function checkPassword(string $pass): ?string {
    if (strlen($pass) < 8)            return "Minimum 8 caractères.";
    if (!preg_match('/[A-Z]/', $pass)) return "Au moins une lettre majuscule.";
    if (!preg_match('/[0-9]/', $pass)) return "Au moins un chiffre.";
    if (!preg_match('/[^a-zA-Z0-9]/', $pass)) return "Au moins un caractère spécial (!@#\$%…).";
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom           = trim($_POST['nom']            ?? '');
    $prenom        = trim($_POST['prenom']         ?? '');
    $email         = trim($_POST['email']          ?? '');
    $pass          = $_POST['password']             ?? '';
    $confirm       = $_POST['confirm']              ?? '';
    $telephone     = trim($_POST['telephone']      ?? '') ?: null;
    $adresse       = trim($_POST['adresse']        ?? '') ?: null;
    $ville         = trim($_POST['ville']          ?? '') ?: null;
    $dateNaissance = trim($_POST['date_naissance'] ?? '') ?: null;

    // ── Validation ──
    if (!$nom)                                       $errors[] = "Le nom est obligatoire.";
    if (!$prenom)                                    $errors[] = "Le prénom est obligatoire.";
    if (!$email)                                     $errors[] = "L'email est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = "Email invalide.";
    $pwErr = checkPassword($pass);
    if ($pwErr)                                      $errors[] = $pwErr;
    if ($pass !== $confirm)                          $errors[] = "Les mots de passe ne correspondent pas.";
    if ($ville && !in_array($ville, $villes))        $errors[] = "Ville invalide.";

    if (empty($errors)) {
        $conn = getConnection();

        // Ensure columns exist (safety net)
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS telephone     VARCHAR(20)  NULL");
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS adresse       TEXT         NULL");
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS ville         VARCHAR(100) NULL");
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS date_naissance DATE         NULL");

        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $errors[] = "Cet email est déjà utilisé.";
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (nom, prenom, email, password, role,
                                   telephone, adresse, ville, date_naissance)
                VALUES (?, ?, ?, ?, 'client', ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssssss",
                $nom, $prenom, $email, $hashed,
                $telephone, $adresse, $ville, $dateNaissance
            );
            if ($stmt->execute()) {
                $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
            } else {
                $errors[] = "Erreur lors de la création du compte.";
            }
            $stmt->close();
        }
        $chk->close();
        $conn->close();
    }
}

// Helper: repopulate text fields after failed submit
function old(string $key): string {
    return htmlspecialchars($_POST[$key] ?? '');
}
?>
<link rel="stylesheet" href="/LibraireWeb/assets/css/auth.css">

<div class="auth-container">
<div class="auth-card" style="max-width:560px">

    <h2>📝 Créer un compte</h2>
    <p class="auth-subtitle">Rejoignez LibraireWeb dès aujourd'hui</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom:20px">
            <?php foreach ($errors as $e): ?>
                <p style="margin:3px 0">❌ <?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <p>✅ <?= $success ?></p>
            <a href="/LibraireWeb/public/login.php" class="btn btn-primary" style="margin-top:10px">
                Se connecter
            </a>
        </div>
    <?php else: ?>

    <form method="POST" id="registerForm" novalidate>

        <!-- ── Identity ──────────────────────────────────── -->
        <p class="section-label">👤 Identité</p>

        <div class="form-row">
            <div class="form-group">
                <label>Nom <span class="req">*</span></label>
                <input type="text" name="nom" value="<?= old('nom') ?>"
                       placeholder="Arfaoui" required>
            </div>
            <div class="form-group">
                <label>Prénom <span class="req">*</span></label>
                <input type="text" name="prenom" value="<?= old('prenom') ?>"
                       placeholder="Hamza" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email <span class="req">*</span></label>
                <input type="email" name="email" value="<?= old('email') ?>"
                       placeholder="hamza@email.com" required>
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <input type="date" name="date_naissance"
                       value="<?= old('date_naissance') ?>"
                       max="<?= date('Y-m-d', strtotime('-10 years')) ?>">
            </div>
        </div>

        <!-- ── Contact ───────────────────────────────────── -->
        <p class="section-label" style="margin-top:18px">📞 Contact & Adresse</p>

        <div class="form-group">
            <label>Numéro de téléphone</label>
            <div class="input-prefix-wrap">
                <span class="input-prefix">🇹🇳 +216</span>
                <input type="tel" name="telephone"
                       value="<?= old('telephone') ?>"
                       placeholder="20 000 000"
                       maxlength="12"
                       pattern="[0-9 ]{7,12}"
                       style="padding-left:90px">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Gouvernorat / Ville</label>
                <select name="ville">
                    <option value="">— Choisir —</option>
                    <?php foreach ($villes as $v): ?>
                        <option value="<?= $v ?>"
                            <?= (old('ville') === $v) ? 'selected' : '' ?>>
                            <?= $v ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Adresse (rue, n°…)</label>
                <input type="text" name="adresse" value="<?= old('adresse') ?>"
                       placeholder="Ex : 12 Rue de la Liberté">
            </div>
        </div>

        <!-- ── Password ──────────────────────────────────── -->
        <p class="section-label" style="margin-top:18px">🔒 Mot de passe</p>

        <div class="pass-rules">
            Votre mot de passe doit contenir :
            <ul>
                <li id="r-len">8 caractères minimum</li>
                <li id="r-upper">1 lettre majuscule</li>
                <li id="r-digit">1 chiffre</li>
                <li id="r-special">1 caractère spécial (!@#$%…)</li>
            </ul>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Mot de passe <span class="req">*</span></label>
                <div class="input-eye-wrap">
                    <input type="password" name="password" id="password"
                           placeholder="Nouveau mot de passe" required
                           oninput="liveRules(this.value)">
                    <button type="button" class="eye-btn" onclick="toggleEye('password',this)">👁</button>
                </div>
            </div>
            <div class="form-group">
                <label>Confirmer <span class="req">*</span></label>
                <div class="input-eye-wrap">
                    <input type="password" name="confirm" id="confirm"
                           placeholder="Répétez le mot de passe" required
                           oninput="liveConfirm()">
                    <button type="button" class="eye-btn" onclick="toggleEye('confirm',this)">👁</button>
                </div>
                <small id="confirmHint" style="font-size:0.8rem;margin-top:4px;display:block"></small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
            Créer mon compte →
        </button>

        <p class="auth-link">
            Déjà un compte ? <a href="/LibraireWeb/public/login.php">Se connecter</a>
        </p>

    </form>
    <?php endif; ?>

</div>
</div>

<style>
/* ── Section labels ── */
.section-label {
    font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.6px; color: var(--color-text-muted);
    margin-bottom: 14px;
}
.req { color: #e53e3e; }

/* ── Phone prefix ── */
.input-prefix-wrap { position: relative; }
.input-prefix {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    font-size: 0.85rem; color: var(--color-text-muted);
    pointer-events: none; white-space: nowrap;
}

/* ── Show/hide eye ── */
.input-eye-wrap { position: relative; }
.eye-btn {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; font-size: 1rem;
    padding: 0; line-height: 1; opacity: 0.6;
}
.eye-btn:hover { opacity: 1; }

/* ── Password rules ── */
.pass-rules {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: 10px 14px; font-size: 0.82rem;
    color: var(--color-text-muted); margin-bottom: 14px;
}
.pass-rules ul { margin: 5px 0 0 16px; }
.pass-rules li { margin-bottom: 3px; transition: color .2s; list-style: disc; }
.pass-rules li.ok { color: #166534; font-weight: 600; list-style: none; }
.pass-rules li.ok::before { content: "✅ "; }

/* ── Select styling ── */
select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    font-family: var(--font-body); font-size: 0.9rem;
    background: white; color: var(--color-text);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    cursor: pointer;
}
select:focus { outline: none; border-color: var(--color-primary-light); box-shadow: 0 0 0 3px rgba(46,109,164,.15); }

/* ── Full-width button ── */
.btn-full { width: 100%; margin-top: 8px; }
</style>

<script>
// ── Password rule indicators ──────────────────────────────────────────────────
function liveRules(val) {
    mark('r-len',     val.length >= 8);
    mark('r-upper',   /[A-Z]/.test(val));
    mark('r-digit',   /[0-9]/.test(val));
    mark('r-special', /[^a-zA-Z0-9]/.test(val));
    liveConfirm();
}
function mark(id, ok) {
    document.getElementById(id).classList.toggle('ok', ok);
}

// ── Confirm match ─────────────────────────────────────────────────────────────
function liveConfirm() {
    const pass    = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const hint    = document.getElementById('confirmHint');
    if (!confirm) { hint.textContent = ''; return; }
    if (confirm === pass) {
        hint.textContent = '✅ Mots de passe identiques';
        hint.style.color = '#166534';
    } else {
        hint.textContent = '❌ Ne correspond pas';
        hint.style.color = '#991b1b';
    }
}

// ── Show/hide password ────────────────────────────────────────────────────────
function toggleEye(inputId, btn) {
    const input = document.getElementById(inputId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.style.opacity = isText ? '0.6' : '1';
}

// ── Client-side submit guard ──────────────────────────────────────────────────
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const pass    = document.getElementById('password').value;
    const confirm = document.getElementById('confirm').value;
    const errors  = [];

    if (pass.length < 8)             errors.push("Mot de passe : minimum 8 caractères.");
    if (!/[A-Z]/.test(pass))         errors.push("Mot de passe : au moins une majuscule.");
    if (!/[0-9]/.test(pass))         errors.push("Mot de passe : au moins un chiffre.");
    if (!/[^a-zA-Z0-9]/.test(pass))  errors.push("Mot de passe : au moins un caractère spécial.");
    if (pass !== confirm)            errors.push("Les mots de passe ne correspondent pas.");

    if (errors.length) {
        e.preventDefault();
        // Inject errors above the form
        let box = document.getElementById('jsErrors');
        if (!box) {
            box = document.createElement('div');
            box.id = 'jsErrors';
            box.className = 'alert alert-error';
            box.style.marginBottom = '16px';
            document.getElementById('registerForm').before(box);
        }
        box.innerHTML = errors.map(er => `<p style="margin:3px 0">❌ ${er}</p>`).join('');
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>