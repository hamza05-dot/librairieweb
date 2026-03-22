<?php
if (!function_exists('requireAuth')) require_once __DIR__ . '/../auth/auth.php';

/**
 * Users endpoints
 *   GET    /users         → list all  [admin]
 *   GET    /users/{id}    → single    [admin | self]
 *   PUT    /users/{id}    → update    [admin | self]
 *   DELETE /users/{id}    → delete    [admin]
 */

function handleUsers(string $method, ?int $id): void {
    match (true) {
        $method === 'GET'    && $id === null => usersIndex(),
        $method === 'GET'                   => usersShow($id),
        $method === 'PUT'                   => usersUpdate($id),
        $method === 'DELETE'                => usersDelete($id),
        default                             => sendError('Method not allowed', 405),
    };
}

function usersIndex(): void {
    requireAdmin();
    $conn   = getConnection();
    $result = $conn->query("
        SELECT id, nom, prenom, email, role, telephone, adresse, ville, date_naissance, created_at
        FROM users ORDER BY id DESC
    ");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    sendJson(array_map(fn($r) => formatUser($r), $rows));
}

function usersShow(int $id): void {
    $me = requireAuth();
    if ($me['role'] !== 'admin' && (int)$me['id'] !== $id) {
        sendError('Forbidden', 403);
    }

    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT id, nom, prenom, email, role, telephone, adresse, ville, date_naissance, created_at
        FROM users WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$row) sendError('User not found', 404);
    sendJson(formatUser($row));
}

function usersUpdate(int $id): void {
    $me = requireAuth();
    if ($me['role'] !== 'admin' && (int)$me['id'] !== $id) {
        sendError('Forbidden', 403);
    }

    $data = getBody();
    $conn = getConnection();

    // Fetch existing
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$existing) { $conn->close(); sendError('User not found', 404); }

    $nom            = trim($data['nom']            ?? $existing['nom']);
    $prenom         = trim($data['prenom']         ?? $existing['prenom']);
    $email          = trim($data['email']          ?? $existing['email']);
    $telephone      = $data['telephone']           ?? $existing['telephone']      ?? null;
    $adresse        = $data['adresse']             ?? $existing['adresse']        ?? null;
    $ville          = $data['ville']               ?? $existing['ville']          ?? null;
    $date_naissance = $data['date_naissance']      ?? $existing['date_naissance'] ?? null;

    // Validate date_naissance format if provided
    if ($date_naissance && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_naissance)) {
        $date_naissance = null;
    }

    // Only admin can change role
    $role = ($me['role'] === 'admin' && isset($data['role']))
        ? $data['role']
        : $existing['role'];

    if (!in_array($role, ['client', 'admin'])) sendError('Invalid role');

    // Password change
    if (!empty($data['password'])) {
        $pass = $data['password'];
        // Validate: min 8 chars, 1 uppercase, 1 digit, 1 special char
        if (strlen($pass) < 8) {
            sendError('Le mot de passe doit contenir au moins 8 caractères.');
        }
        if (!preg_match('/[A-Z]/', $pass)) {
            sendError('Le mot de passe doit contenir au moins une lettre majuscule.');
        }
        if (!preg_match('/[0-9]/', $pass)) {
            sendError('Le mot de passe doit contenir au moins un chiffre.');
        }
        if (!preg_match('/[!@#$%^&*()\-_=+\[\]{};:\'"|,.<>\/?]/', $pass)) {
            sendError('Le mot de passe doit contenir au moins un caractère spécial.');
        }

        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $upd = $conn->prepare("
            UPDATE users
            SET nom=?, prenom=?, email=?, role=?, password=?,
                telephone=?, adresse=?, ville=?, date_naissance=?
            WHERE id=?
        ");
        $upd->bind_param("sssssssssi",
            $nom, $prenom, $email, $role, $hashed,
            $telephone, $adresse, $ville, $date_naissance, $id
        );
    } else {
        $upd = $conn->prepare("
            UPDATE users
            SET nom=?, prenom=?, email=?, role=?,
                telephone=?, adresse=?, ville=?, date_naissance=?
            WHERE id=?
        ");
        $upd->bind_param("ssssssssi",
            $nom, $prenom, $email, $role,
            $telephone, $adresse, $ville, $date_naissance, $id
        );
    }

    if (!$upd->execute()) {
        $conn->close();
        sendError('Erreur lors de la mise à jour: ' . $upd->error, 500);
    }
    $upd->close();
    $conn->close();

    usersShow($id);
}

function usersDelete(int $id): void {
    requireAdmin();
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected === 0) sendError('User not found', 404);
    sendJson(['message' => 'User deleted', 'id' => $id]);
}

function formatUser(array $r): array {
    return [
        'id'             => (int)$r['id'],
        'nom'            => $r['nom'],
        'prenom'         => $r['prenom'],
        'email'          => $r['email'],
        'role'           => $r['role'],
        'telephone'      => $r['telephone']      ?? null,
        'adresse'        => $r['adresse']        ?? null,
        'ville'          => $r['ville']          ?? null,
        'date_naissance' => $r['date_naissance'] ?? null,
        'created_at'     => $r['created_at'],
    ];
}