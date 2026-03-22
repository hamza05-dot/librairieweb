<?php
$pageTitle = "Connexion";
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in → redirect
if (isset($_SESSION['user_id'])) {
    header('Location: /LibraireWeb/public/index.php');
    exit;
}

$error    = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if (!$email || !$pass) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, nom, prenom, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$user || !password_verify($pass, $user['password'])) {
            $error = "Email ou mot de passe incorrect.";
        } else {
            // Create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // Store API token
            $token = bin2hex(random_bytes(32));
            $conn2 = getConnection();
            $conn2->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS token VARCHAR(64) NULL");
            $upd = $conn2->prepare("UPDATE users SET token = ? WHERE id = ?");
            $upd->bind_param("si", $token, $user['id']);
            $upd->execute();
            $upd->close();
            $conn2->close();
            $_SESSION['token'] = $token;

            $dest = ($user['role'] === 'admin')
                ? '/LibraireWeb/admin/dashboard.php'
                : '/LibraireWeb/public/' . $redirect;

            header("Location: $dest");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibraireWeb — Connexion</title>
    <link rel="stylesheet" href="/LibraireWeb/assets/css/style.css">
    <style>
        body { background: #0d1b2a; margin: 0; }

        /* ── Two-column layout ── */
        .login-wrap {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* ── LEFT: dark decorative panel ── */
        .login-left {
            position: relative;
            background: linear-gradient(145deg, #0d1b2a 0%, #1a2e45 60%, #0d1b2a 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 70% 50% at 30% 40%, rgba(46,109,164,0.28) 0%, transparent 60%),
                radial-gradient(ellipse 40% 60% at 80% 70%, rgba(26,60,94,0.45) 0%, transparent 55%);
            pointer-events: none;
        }

        /* Floating book spines */
        .book-spines {
            position: absolute;
            right: -10px; top: 50%;
            transform: translateY(-50%);
            display: flex; gap: 10px; align-items: flex-end;
        }

        .spine {
            width: 30px; border-radius: 4px 4px 2px 2px;
            animation: spineFloat 4s ease-in-out infinite;
        }

        .spine:nth-child(1){ height:200px; background:rgba(91,164,212,0.25);  animation-delay:0s; }
        .spine:nth-child(2){ height:270px; background:rgba(46,109,164,0.3);   animation-delay:0.4s; }
        .spine:nth-child(3){ height:180px; background:rgba(91,164,212,0.2);   animation-delay:0.8s; }
        .spine:nth-child(4){ height:300px; background:rgba(26,60,94,0.4);     animation-delay:1.2s; }
        .spine:nth-child(5){ height:230px; background:rgba(46,109,164,0.25);  animation-delay:1.6s; }

        @keyframes spineFloat {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-12px); }
        }

        .left-content { position: relative; z-index: 1; }

        .left-logo {
            font-family: var(--font-display);
            font-size: 1.45rem; font-weight: 700;
            color: white; text-decoration: none;
            display: inline-flex; align-items: center; gap: 10px;
            margin-bottom: 64px;
            transition: opacity 0.2s;
        }
        .left-logo:hover { opacity: 0.85; }

        .left-title {
            font-family: var(--font-display);
            font-size: clamp(1.9rem, 3vw, 2.7rem);
            color: white; line-height: 1.2;
            margin-bottom: 16px;
        }

        .left-title em {
            font-style: italic;
            color: var(--color-accent);
            display: block;
        }

        .left-sub {
            font-family: var(--font-body);
            font-size: 0.97rem; font-weight: 300;
            color: rgba(255,255,255,0.52);
            line-height: 1.75; max-width: 340px;
            margin-bottom: 44px;
        }

        .left-features { list-style: none; display: flex; flex-direction: column; gap: 14px; }

        .left-features li {
            font-family: var(--font-body);
            font-size: 0.88rem;
            color: rgba(255,255,255,0.6);
            display: flex; align-items: center; gap: 12px;
        }

        .feat-icon {
            width: 34px; height: 34px;
            background: rgba(46,109,164,0.25);
            border: 1px solid rgba(91,164,212,0.25);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }

        /* ── RIGHT: form panel ── */
        .login-right {
            background: var(--color-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .login-card {
            width: 100%; max-width: 420px;
            animation: cardIn 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-header { margin-bottom: 32px; }

        .card-header h1 {
            font-family: var(--font-display);
            font-size: 2rem; color: var(--color-primary);
            margin-bottom: 6px;
        }

        .card-header p {
            font-family: var(--font-body);
            font-size: 0.9rem; color: var(--color-text-muted);
        }

        /* Input with icon */
        .input-icon-wrap { position: relative; }

        .input-icon-wrap input {
            width: 100%;
            padding: 11px 14px 11px 44px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 0.95rem;
            background: white; color: var(--color-text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-icon-wrap input:focus {
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 3px rgba(46,109,164,0.12);
        }

        .i-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: 1rem; pointer-events: none;
        }

        /* Password toggle */
        .pass-wrap { position: relative; }

        .pass-wrap input {
            width: 100%;
            padding: 11px 44px 11px 44px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 0.95rem;
            background: white; color: var(--color-text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .pass-wrap input:focus {
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 3px rgba(46,109,164,0.12);
        }

        .pass-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; font-size: 1rem;
            color: var(--color-text-muted);
            padding: 4px; line-height: 1;
            transition: color 0.2s;
        }
        .pass-toggle:hover { color: var(--color-primary); }

        /* Row between password and submit */
        .form-row-opts {
            display: flex; justify-content: space-between;
            align-items: center; margin: 6px 0 22px;
        }

        .remember {
            display: flex; align-items: center; gap: 7px;
            font-family: var(--font-body); font-size: 0.84rem;
            color: var(--color-text-muted); cursor: pointer;
        }

        .remember input { accent-color: var(--color-primary-light); }

        .forgot {
            font-family: var(--font-body);
            font-size: 0.84rem; font-weight: 600;
            color: var(--color-primary-light);
            text-decoration: none; transition: opacity 0.2s;
        }
        .forgot:hover { opacity: 0.7; }

        /* Submit button */
        .btn-login {
            width: 100%; padding: 14px;
            background: var(--color-primary);
            color: white; border: none;
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 1rem; font-weight: 700;
            cursor: pointer; letter-spacing: 0.3px;
            transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-login:hover { background: var(--color-primary-light); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(46,109,164,0.35); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

        /* Divider */
        .or-divider {
            text-align: center;
            font-family: var(--font-body);
            font-size: 0.8rem; color: var(--color-text-muted);
            margin: 24px 0;
            position: relative;
        }

        .or-divider::before, .or-divider::after {
            content: '';
            position: absolute; top: 50%;
            width: 44%; height: 1px;
            background: var(--color-border);
        }

        .or-divider::before { left: 0; }
        .or-divider::after  { right: 0; }

        /* Register link */
        .register-row {
            text-align: center;
            font-family: var(--font-body);
            font-size: 0.9rem; color: var(--color-text-muted);
        }

        .register-row a {
            color: var(--color-primary-light);
            font-weight: 700; text-decoration: none;
        }
        .register-row a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrap { grid-template-columns: 1fr; }
            .login-left  { display: none; }
            body { background: var(--color-bg); }
        }
    </style>
</head>
<body>

<div class="login-wrap">

    <!-- ══ LEFT PANEL ══ -->
    <div class="login-left">
        <div class="book-spines">
            <div class="spine"></div>
            <div class="spine"></div>
            <div class="spine"></div>
            <div class="spine"></div>
            <div class="spine"></div>
        </div>

        <div class="left-content">
            <a href="/LibraireWeb/public/index.php" class="left-logo">
                📚 LibraireWeb
            </a>

            <h1 class="left-title">
                Bon retour
                <em>parmi nous</em>
            </h1>
            <p class="left-sub">
                Connectez-vous pour accéder à votre bibliothèque personnelle, passer des commandes et suivre vos livres favoris.
            </p>

            <ul class="left-features">
                <li><span class="feat-icon">🛒</span> Panier et commandes sauvegardés</li>
                <li><span class="feat-icon">❤️</span> Liste de favoris personnalisée</li>
                <li><span class="feat-icon">📦</span> Suivi de livraison en temps réel</li>
                <li><span class="feat-icon">🎁</span> Offres exclusives pour les membres</li>
            </ul>
        </div>
    </div>

    <!-- ══ RIGHT PANEL ══ -->
    <div class="login-right">
        <div class="login-card">

            <div class="card-header">
                <h1>Connexion</h1>
                <p>Entrez vos identifiants pour accéder à votre compte</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:20px">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success" style="margin-bottom:20px">
                ✅ Compte créé avec succès ! Connectez-vous maintenant.
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <div class="input-icon-wrap">
                        <span class="i-icon">✉️</span>
                        <input
                            type="email" name="email" id="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            placeholder="vous@email.com"
                            required autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="pass-wrap">
                        <span class="i-icon">🔒</span>
                        <input
                            type="password" name="password" id="password"
                            placeholder="Votre mot de passe"
                            required
                        >
                        <button type="button" class="pass-toggle" id="passToggle" onclick="togglePass()">👁️</button>
                    </div>
                </div>

                <div class="form-row-opts">
                    <label class="remember">
                        <input type="checkbox" name="remember"> Se souvenir de moi
                    </label>
                    <a href="forgot_password.php" class="forgot">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span id="btnText">Se connecter →</span>
                    <span id="btnSpinner" style="display:none">⏳ Connexion…</span>
                </button>

            </form>

            <div class="or-divider">ou</div>

            <div class="register-row">
                Pas encore de compte ?
                <a href="/LibraireWeb/public/register.php">Créer un compte gratuit →</a>
            </div>

        </div>
    </div>

</div>

<script>
function togglePass() {
    const input = document.getElementById('password');
    const btn   = document.getElementById('passToggle');
    if (input.type === 'password') {
        input.type      = 'text';
        btn.textContent = '🙈';
    } else {
        input.type      = 'password';
        btn.textContent = '👁️';
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('btnText').style.display    = 'none';
    document.getElementById('btnSpinner').style.display = 'inline';
    document.getElementById('submitBtn').disabled       = true;
});
</script>

</body>
</html>