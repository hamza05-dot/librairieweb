<?php
if (!function_exists('requireAuth')) require_once __DIR__ . '/../auth/auth.php';
/**
 * Orders endpoints
 *   GET    /orders              → admin = all orders; client = own orders
 *   GET    /orders/{id}         → single order with items
 *   POST   /orders              → place new order  [auth required]
 *   PUT    /orders/{id}/status  → change status    [admin]
 */

function handleOrders(string $method, ?int $id, ?string $sub): void {
    match (true) {
        $method === 'GET'  && $id === null        => ordersIndex(),
        $method === 'GET'                         => ordersShow($id),
        $method === 'POST' && $id === null        => ordersCreate(),
        $method === 'PUT'  && $sub === 'status'   => ordersUpdateStatus($id),
        default                                   => sendError('Method not allowed', 405),
    };
}

function ordersIndex(): void {
    $me   = requireAuth();
    $conn = getConnection();

    if ($me['role'] === 'admin') {
        $result = $conn->query("
            SELECT o.*, u.nom, u.prenom, u.email
            FROM orders o
            JOIN users u ON u.id = o.user_id
            ORDER BY o.id DESC
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT o.*, u.nom, u.prenom, u.email
            FROM orders o
            JOIN users u ON u.id = o.user_id
            WHERE o.user_id = ?
            ORDER BY o.id DESC
        ");
        $stmt->bind_param("i", $me['id']);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();

    sendJson(array_map(fn($r) => formatOrder($r), $rows));
}

function ordersShow(int $id): void {
    $me   = requireAuth();
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT o.*, u.nom, u.prenom, u.email
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) { $conn->close(); sendError('Order not found', 404); }

    // Access control
    if ($me['role'] !== 'admin' && (int)$order['user_id'] !== (int)$me['id']) {
        $conn->close(); sendError('Forbidden', 403);
    }

    // Fetch items
    $iStmt = $conn->prepare("
        SELECT oi.id, oi.quantite, oi.prix_unit,
               b.id AS book_id, b.titre, b.auteur, b.image
        FROM order_items oi
        LEFT JOIN books b ON b.id = oi.book_id
        WHERE oi.order_id = ?
    ");
    $iStmt->bind_param("i", $id);
    $iStmt->execute();
    $items = $iStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $iStmt->close();
    $conn->close();

    $out = formatOrder($order);
    $out['items'] = array_map(fn($i) => [
        'id'        => (int)$i['id'],
        'quantite'  => (int)$i['quantite'],
        'prix_unit' => (float)$i['prix_unit'],
        'sous_total'=> round((int)$i['quantite'] * (float)$i['prix_unit'], 2),
        'book'      => $i['book_id'] ? [
            'id'    => (int)$i['book_id'],
            'titre' => $i['titre'],
            'auteur'=> $i['auteur'],
            'image' => $i['image'],
        ] : null,
    ], $items);

    sendJson($out);
}

function ordersCreate(): void {
    $me   = requireAuth();
    $data = getBody();

    $items = $data['items'] ?? [];
    if (empty($items) || !is_array($items)) {
        sendError('items array is required (e.g. [{"book_id":1,"quantite":2}])');
    }

    $conn = getConnection();
    $conn->begin_transaction();

    try {
        $total = 0.0;
        $resolved = [];

        foreach ($items as $item) {
            $bookId  = (int)($item['book_id']  ?? 0);
            $qty     = (int)($item['quantite'] ?? 0);
            if ($bookId <= 0 || $qty <= 0) throw new Exception("Invalid item: book_id and quantite must be > 0");

            // Fetch price & check stock
            $bStmt = $conn->prepare("SELECT prix, stock FROM books WHERE id = ? FOR UPDATE");
            $bStmt->bind_param("i", $bookId);
            $bStmt->execute();
            $book = $bStmt->get_result()->fetch_assoc();
            $bStmt->close();

            if (!$book) throw new Exception("Book $bookId not found");
            if ($book['stock'] < $qty) throw new Exception("Insufficient stock for book $bookId (available: {$book['stock']})");

            $lineTotal = round($book['prix'] * $qty, 2);
            $total    += $lineTotal;
            $resolved[] = ['book_id' => $bookId, 'quantite' => $qty, 'prix_unit' => $book['prix']];

            // Decrement stock
            $upd = $conn->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
            $upd->bind_param("ii", $qty, $bookId);
            $upd->execute();
            $upd->close();
        }

        // Insert order
        $oStmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
        $oStmt->bind_param("id", $me['id'], $total);
        $oStmt->execute();
        $orderId = $conn->insert_id;
        $oStmt->close();

        // Insert order_items
        foreach ($resolved as $ri) {
            $iStmt = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantite, prix_unit) VALUES (?, ?, ?, ?)");
            $iStmt->bind_param("iiid", $orderId, $ri['book_id'], $ri['quantite'], $ri['prix_unit']);
            $iStmt->execute();
            $iStmt->close();
        }

        $conn->commit();
        $conn->close();
        ordersShow($orderId);

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        sendError($e->getMessage(), 422);
    }
}

function ordersUpdateStatus(int $id): void {
    requireAdmin();
    $data   = getBody();
    $status = $data['statut'] ?? '';
    $valid  = ['en attente', 'confirmee', 'livree', 'annulee'];

    if (!in_array($status, $valid)) {
        sendError('statut must be one of: ' . implode(', ', $valid));
    }

    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE orders SET statut = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected === 0) sendError('Order not found', 404);
    ordersShow($id);
}

function formatOrder(array $r): array {
    return [
        'id'         => (int)$r['id'],
        'statut'     => $r['statut'],
        'total'      => (float)$r['total'],
        'created_at' => $r['created_at'],
        'user'       => [
            'id'     => (int)$r['user_id'],
            'nom'    => $r['nom'],
            'prenom' => $r['prenom'],
            'email'  => $r['email'],
        ],
    ];
}