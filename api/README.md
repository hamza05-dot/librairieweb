# LibraireWeb REST API

Drop the `api/` folder into your project:
```
LibraireWeb/
└── api/
    ├── .htaccess
    ├── index.php        ← entry point / router
    ├── config.php       ← DB config + helpers
    ├── auth/auth.php
    ├── books/books.php
    ├── categories/categories.php
    ├── users/users.php
    └── orders/orders.php
```

Base URL: `http://localhost/LibraireWeb/api/`

---

## Authentication

All protected routes require a Bearer token in the `Authorization` header:
```
Authorization: Bearer <token>
```
Get the token from `POST /auth/login`.

---

## Endpoints

### 🔓 Auth (public)

| Method | URL | Body |
|--------|-----|------|
| POST | `/auth/register` | `{nom, prenom, email, password, confirm}` |
| POST | `/auth/login`    | `{email, password}` → returns `{token, user}` |

---

### 📚 Books

| Method | URL | Auth | Description |
|--------|-----|------|-------------|
| GET | `/books` | — | List books. Query params: `?search=&category_id=&page=&limit=` |
| GET | `/books/{id}` | — | Single book |
| POST | `/books` | admin | Create book |
| PUT | `/books/{id}` | admin | Update book |
| DELETE | `/books/{id}` | admin | Delete book |

**POST/PUT body:**
```json
{
  "titre": "Le Petit Prince",
  "auteur": "Antoine de Saint-Exupéry",
  "description": "...",
  "prix": 12.99,
  "stock": 50,
  "image": "petitprince.jpg",
  "category_id": 2
}
```

**GET /books response:**
```json
{
  "data": [
    {
      "id": 1,
      "titre": "Le Petit Prince",
      "auteur": "Antoine de Saint-Exupéry",
      "description": "...",
      "prix": 12.99,
      "stock": 50,
      "image": "petitprince.jpg",
      "created_at": "2026-03-14 08:58:00",
      "category": { "id": 2, "nom": "Roman" }
    }
  ],
  "pagination": {
    "total": 42,
    "page": 1,
    "limit": 20,
    "total_pages": 3
  }
}
```

---

### 🗂 Categories

| Method | URL | Auth |
|--------|-----|------|
| GET | `/categories` | — |
| GET | `/categories/{id}` | — |
| POST | `/categories` | admin |
| PUT | `/categories/{id}` | admin |
| DELETE | `/categories/{id}` | admin |

**Body:** `{ "nom": "Science-Fiction" }`

---

### 👤 Users

| Method | URL | Auth |
|--------|-----|------|
| GET | `/users` | admin |
| GET | `/users/{id}` | admin or self |
| PUT | `/users/{id}` | admin or self |
| DELETE | `/users/{id}` | admin |

**PUT body** (all fields optional):
```json
{
  "nom": "Arfaoui",
  "prenom": "Hamza",
  "email": "hamza@email.com",
  "password": "newpass123",
  "role": "admin"
}
```

---

### 🛒 Orders

| Method | URL | Auth | Description |
|--------|-----|------|-------------|
| GET | `/orders` | auth | Admin sees all; client sees own |
| GET | `/orders/{id}` | auth | Single order with items |
| POST | `/orders` | auth | Place order (decrements stock, wraps in transaction) |
| PUT | `/orders/{id}/status` | admin | Change statut |

**POST /orders body:**
```json
{
  "items": [
    { "book_id": 1, "quantite": 2 },
    { "book_id": 5, "quantite": 1 }
  ]
}
```

**PUT /orders/{id}/status body:**
```json
{ "statut": "confirmee" }
```
Valid statuts: `en attente`, `confirmee`, `livree`, `annulee`

---

## Error responses

All errors return:
```json
{ "error": "Descriptive message" }
```
with appropriate HTTP status codes (400, 401, 403, 404, 409, 422, 500).

---

## Notes

- Tokens are stored in a `token` column added automatically to `users` on first login.
- For production, migrate to JWT (`firebase/php-jwt`) and use HTTPS.
- Stock is decremented atomically in a transaction when an order is placed.
