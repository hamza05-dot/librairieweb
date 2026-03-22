<?php
/**
 * Books endpoints
 *   GET    /books          → list (filter: ?category_id=&search=&page=&limit=)
 *   GET    /books/{id}     → single book
 *   POST   /books          → create  [admin]
 *   PUT    /books/{id}     → update  [admin]
 *   DELETE /books/{id}     → delete  [admin]
 */

function handleBooks(string $method, ?int $id): void {
    match (true) {
        $method === 'GET'    && $id === null => booksIndex(),
        $method === 'GET'                   => booksShow($id),
        $method === 'POST'                  => booksCreate(),
        $method === 'PUT'                   => booksUpdate($id),
        $method === 'DELETE'                => booksDelete($id),
        default                             => sendError('Method not allowed', 405),
    };
}

function booksIndex(): void {
    $conn = getConnection();

    $where  = [];
    $params = [];
    $types  = '';

    if (!empty($_GET['category_id'])) {
        $where[]  = 'b.category_id = ?';
        $params[] = (int)$_GET['category_id'];
        $types   .= 'i';
    }

    if (!empty($_GET['search'])) {
        $like     = '%' . $_GET['search'] . '%';
        $where[]  = '(b.titre LIKE ? OR b.auteur LIKE ?)';
        $params[] = $like;
        $params[] = $like;
        $types   .= 'ss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Pagination
    $page  = max(1, (int)($_GET['page']  ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    // Count total
    $countSql = "SELECT COUNT(*) FROM books b $whereClause";
    $cStmt = $conn->prepare($countSql);
    if ($types) $cStmt->bind_param($types, ...$params);
    $cStmt->execute();
    $cStmt->bind_result($total);
    $cStmt->fetch();
    $cStmt->close();

    // Fetch rows
    $sql = "
        SELECT b.id, b.titre, b.auteur, b.description, b.prix, b.stock,
               b.image, b.created_at,
               c.id AS category_id, c.nom AS category_nom
        FROM books b
        LEFT JOIN categories c ON c.id = b.category_id
        $whereClause
        ORDER BY b.id DESC
        LIMIT ? OFFSET ?
    ";
    $params[] = $limit;
    $params[] = $offset;
    $types   .= 'ii';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    // Shape output
    $books = array_map(fn($r) => formatBook($r), $rows);

    sendJson([
        'data'       => $books,
        'pagination' => [
            'total'        => (int)$total,
            'page'         => $page,
            'limit'        => $limit,
            'total_pages'  => (int)ceil($total / $limit),
        ],
    ]);
}

function booksShow(int $id): void {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT b.id, b.titre, b.auteur, b.description, b.prix, b.stock,
               b.image, b.created_at,
               c.id AS category_id, c.nom AS category_nom
        FROM books b
        LEFT JOIN categories c ON c.id = b.category_id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$row) sendError('Book not found', 404);
    sendJson(formatBook($row));
}

function booksCreate(): void {
    requireAdmin();
    $data = getBody();

    $titre  = trim($data['titre']  ?? '');
    $auteur = trim($data['auteur'] ?? '');
    $prix   = (float)($data['prix'] ?? 0);

    if (!$titre || !$auteur || $prix <= 0) {
        sendError('titre, auteur and prix (> 0) are required');
    }

    $description  = $data['description']  ?? null;
    $stock        = (int)($data['stock']  ?? 0);
    $image        = $data['image']        ?? null;
    $category_id  = isset($data['category_id']) ? (int)$data['category_id'] : null;

    $conn = getConnection();
    $stmt = $conn->prepare("
        INSERT INTO books (titre, auteur, description, prix, stock, image, category_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssdiis", $titre, $auteur, $description, $prix, $stock, $image, $category_id);
    $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    $conn->close();

    // Return the created book
    booksShow($newId);
}

function booksUpdate(int $id): void {
    requireAdmin();
    $data = getBody();

    $conn = getConnection();

    // Fetch existing
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$existing) { $conn->close(); sendError('Book not found', 404); }

    $titre       = trim($data['titre']       ?? $existing['titre']);
    $auteur      = trim($data['auteur']      ?? $existing['auteur']);
    $description = $data['description']      ?? $existing['description'];
    $prix        = (float)($data['prix']     ?? $existing['prix']);
    $stock       = (int)($data['stock']      ?? $existing['stock']);
    $image       = $data['image']            ?? $existing['image'];
    $category_id = isset($data['category_id']) ? (int)$data['category_id'] : $existing['category_id'];

    $upd = $conn->prepare("
        UPDATE books SET titre=?, auteur=?, description=?, prix=?, stock=?, image=?, category_id=?
        WHERE id=?
    ");
    $upd->bind_param("sssdisii", $titre, $auteur, $description, $prix, $stock, $image, $category_id, $id);
    $upd->execute();
    $upd->close();
    $conn->close();

    booksShow($id);
}

function booksDelete(int $id): void {
    requireAdmin();
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected === 0) sendError('Book not found', 404);
    sendJson(['message' => 'Book deleted', 'id' => $id]);
}

// ── Helper ──
function formatBook(array $r): array {
    return [
        'id'          => (int)$r['id'],
        'titre'       => $r['titre'],
        'auteur'      => $r['auteur'],
        'description' => $r['description'],
        'prix'        => (float)$r['prix'],
        'stock'       => (int)$r['stock'],
        'image'       => $r['image'],
        'created_at'  => $r['created_at'],
        'category'    => $r['category_id'] ? [
            'id'  => (int)$r['category_id'],
            'nom' => $r['category_nom'],
        ] : null,
    ];
}
