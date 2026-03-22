<?php
/**
 * Categories endpoints
 *   GET    /categories        → list all
 *   GET    /categories/{id}   → single (includes books count)
 *   POST   /categories        → create  [admin]
 *   PUT    /categories/{id}   → update  [admin]
 *   DELETE /categories/{id}   → delete  [admin]
 */

function handleCategories(string $method, ?int $id): void {
    match (true) {
        $method === 'GET'    && $id === null => categoriesIndex(),
        $method === 'GET'                   => categoriesShow($id),
        $method === 'POST'                  => categoriesCreate(),
        $method === 'PUT'                   => categoriesUpdate($id),
        $method === 'DELETE'                => categoriesDelete($id),
        default                             => sendError('Method not allowed', 405),
    };
}

function categoriesIndex(): void {
    $conn = getConnection();
    $result = $conn->query("
        SELECT c.id, c.nom, COUNT(b.id) AS books_count
        FROM categories c
        LEFT JOIN books b ON b.category_id = c.id
        GROUP BY c.id
        ORDER BY c.nom ASC
    ");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();

    sendJson(array_map(fn($r) => [
        'id'          => (int)$r['id'],
        'nom'         => $r['nom'],
        'books_count' => (int)$r['books_count'],
    ], $rows));
}

function categoriesShow(int $id): void {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT c.id, c.nom, COUNT(b.id) AS books_count
        FROM categories c
        LEFT JOIN books b ON b.category_id = c.id
        WHERE c.id = ?
        GROUP BY c.id
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$row) sendError('Category not found', 404);
    sendJson([
        'id'          => (int)$row['id'],
        'nom'         => $row['nom'],
        'books_count' => (int)$row['books_count'],
    ]);
}

function categoriesCreate(): void {
    requireAdmin();
    $data = getBody();
    $nom  = trim($data['nom'] ?? '');
    if (!$nom) sendError('nom is required');

    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO categories (nom) VALUES (?)");
    $stmt->bind_param("s", $nom);
    $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    $conn->close();

    categoriesShow($newId);
}

function categoriesUpdate(int $id): void {
    requireAdmin();
    $data = getBody();
    $nom  = trim($data['nom'] ?? '');
    if (!$nom) sendError('nom is required');

    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE categories SET nom = ? WHERE id = ?");
    $stmt->bind_param("si", $nom, $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected === 0) sendError('Category not found', 404);
    categoriesShow($id);
}

function categoriesDelete(int $id): void {
    requireAdmin();
    $conn = getConnection();

    // Check if any books use this category
    $chk = $conn->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
    $chk->bind_param("i", $id);
    $chk->execute();
    $chk->bind_result($count);
    $chk->fetch();
    $chk->close();

    if ($count > 0) {
        $conn->close();
        sendError("Cannot delete: $count book(s) still use this category", 409);
    }

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected === 0) sendError('Category not found', 404);
    sendJson(['message' => 'Category deleted', 'id' => $id]);
}
