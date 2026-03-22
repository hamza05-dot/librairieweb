<?php
/**
 * Auth endpoints
 *   POST /auth/register
 *   POST /auth/login
 *   POST /auth/forgot-password   → sends 6-digit code by email
 *   POST /auth/verify-reset      → verifies code, returns reset_token
 *   POST /auth/reset-password    → sets new password using reset_token
 */

// ── Token helpers ──

function requireAuth(): array {
    $conn   = getConnection();
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (empty($header)) $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (empty($header) && function_exists('getallheaders')) {
        $all = getallheaders();
        $header = $all['Authorization'] ?? $all['authorization'] ?? '';
    }
    if (empty($header) && !empty($_GET['token']))  $header = 'Bearer ' . $_GET['token'];
    if (empty($header) && !empty($_POST['token'])) $header = 'Bearer ' . $_POST['token'];

    if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
        sendError('Missing or invalid Authorization header', 401);
    }
    $token = $m[1];
    $stmt  = $conn->prepare("SELECT id, nom, prenom, email, role FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    if (!$user) sendError('Invalid or expired token', 401);
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') sendError('Admin access required', 403);
    return $user;
}

// ── Handler ──

function handleAuth(string $method, string $action): void {
    match ($action) {
        'register'        => authRegister($method),
        'login'           => authLogin($method),
        'forgot-password' => authForgotPassword($method),
        'verify-reset'    => authVerifyReset($method),
        'reset-password'  => authResetPassword($method),
        default           => sendError('Unknown auth action', 404),
    };
}

// ── Register ──

function authRegister(string $method): void {
    if ($method !== 'POST') sendError('Method not allowed', 405);

    $data    = getBody();
    $nom     = trim($data['nom']      ?? '');
    $prenom  = trim($data['prenom']   ?? '');
    $email   = trim($data['email']    ?? '');
    $pass    = trim($data['password'] ?? '');
    $confirm = trim($data['confirm']  ?? '');

    $errors = [];
    if (!$nom)    $errors[] = "nom is required";
    if (!$prenom) $errors[] = "prenom is required";
    if (!$email)  $errors[] = "email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "invalid email";
    if (strlen($pass) < 6)   $errors[] = "password min 6 chars";
    if ($pass !== $confirm)  $errors[] = "passwords do not match";
    if ($errors) sendError(implode(', ', $errors));

    $conn = getConnection();
    $chk  = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $chk->bind_param("s", $email);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) sendError('Email already in use', 409);
    $chk->close();

    $hashed = password_hash($pass, PASSWORD_DEFAULT);
    $stmt   = $conn->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, 'client')");
    $stmt->bind_param("ssss", $nom, $prenom, $email, $hashed);
    $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    $conn->close();

    sendJson(['message' => 'Account created', 'user_id' => $newId], 201);
}

// ── Login ──

function authLogin(string $method): void {
    if ($method !== 'POST') sendError('Method not allowed', 405);

    $data  = getBody();
    $email = trim($data['email']    ?? '');
    $pass  = trim($data['password'] ?? '');

    if (!$email || !$pass) sendError('email and password required');

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, nom, prenom, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($pass, $user['password'])) {
        sendError('Invalid credentials', 401);
    }

    $token = bin2hex(random_bytes(32));
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS token VARCHAR(64) NULL");
    $upd = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
    $upd->bind_param("si", $token, $user['id']);
    $upd->execute();
    $upd->close();
    $conn->close();

    unset($user['password']);
    sendJson(['message' => 'Login successful', 'token' => $token, 'user' => $user]);
}

// ── Forgot password: send 6-digit code ──

function authForgotPassword(string $method): void {
    if ($method !== 'POST') sendError('Method not allowed', 405);

    $data  = getBody();
    $email = trim($data['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Email invalide.');
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, nom, prenom FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Always return success to avoid email enumeration
    if (!$user) {
        $conn->close();
        sendJson(['message' => 'Si cet email existe, un code a été envoyé.']);
    }

    // Generate 6-digit code, valid 15 minutes
    $code    = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', time() + 900); // 15 min

    $upd = $conn->prepare("UPDATE users SET reset_code = ?, reset_expires = ? WHERE id = ?");
    $upd->bind_param("ssi", $code, $expires, $user['id']);
    $upd->execute();
    $upd->close();
    $conn->close();

    // ── Send email ──
    $to      = $email;
    $subject = "LibraireWeb - Code de vérification";
    $nom     = $user['prenom'] . ' ' . $user['nom'];
    $body    = "Bonjour $nom,\n\n"
             . "Votre code de vérification pour réinitialiser votre mot de passe est :\n\n"
             . "  $code\n\n"
             . "Ce code expire dans 15 minutes.\n\n"
             . "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n"
             . "LibraireWeb";

    $headers = "From: noreply@libraireweb.tn\r\n"
             . "Reply-To: noreply@libraireweb.tn\r\n"
             . "Content-Type: text/plain; charset=UTF-8";

    // Try to send — on XAMPP may need to configure php.ini SMTP settings
    @mail($to, $subject, $body, $headers);

    // For development: also return the code in response so you can test without email
    sendJson([
        'message'     => 'Code envoyé à ' . $email,
        'dev_code'    => $code, // ← REMOVE THIS IN PRODUCTION
    ]);
}

// ── Verify reset code ──

function authVerifyReset(string $method): void {
    if ($method !== 'POST') sendError('Method not allowed', 405);

    $data  = getBody();
    $email = trim($data['email'] ?? '');
    $code  = trim($data['code']  ?? '');

    if (!$email || !$code) sendError('Email et code requis.');

    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT id FROM users
        WHERE email = ? AND reset_code = ? AND reset_expires > NOW()
    ");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $conn->close();
        sendError('Code invalide ou expiré.', 400);
    }

    // Generate a reset token valid 10 minutes
    $resetToken = bin2hex(random_bytes(32));
    $expires    = date('Y-m-d H:i:s', time() + 600);

    $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ?, reset_code = NULL WHERE id = ?");
    $upd->bind_param("ssi", $resetToken, $expires, $user['id']);
    $upd->execute();
    $upd->close();
    $conn->close();

    sendJson(['reset_token' => $resetToken]);
}

// ── Reset password using token ──

function authResetPassword(string $method): void {
    if ($method !== 'POST') sendError('Method not allowed', 405);

    $data        = getBody();
    $resetToken  = trim($data['reset_token']      ?? '');
    $pass        = trim($data['password']         ?? '');
    $confirm     = trim($data['password_confirm'] ?? '');

    if (!$resetToken || !$pass) sendError('Token et mot de passe requis.');

    // Validate password strength
    if (strlen($pass) < 8)                          sendError('Minimum 8 caractères.');
    if (!preg_match('/[A-Z]/', $pass))              sendError('Au moins une lettre majuscule.');
    if (!preg_match('/[0-9]/', $pass))              sendError('Au moins un chiffre.');
    if (!preg_match('/[^a-zA-Z0-9]/', $pass))       sendError('Au moins un caractère spécial.');
    if ($pass !== $confirm)                         sendError('Les mots de passe ne correspondent pas.');

    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT id FROM users
        WHERE reset_token = ? AND reset_expires > NOW()
    ");
    $stmt->bind_param("s", $resetToken);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $conn->close();
        sendError('Lien de réinitialisation invalide ou expiré.', 400);
    }

    $hashed = password_hash($pass, PASSWORD_DEFAULT);
    $upd = $conn->prepare("
        UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL, reset_code = NULL
        WHERE id = ?
    ");
    $upd->bind_param("si", $hashed, $user['id']);
    $upd->execute();
    $upd->close();
    $conn->close();

    sendJson(['message' => 'Mot de passe réinitialisé avec succès.']);
}