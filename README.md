# 📚 LibraireWeb

Application web de librairie en ligne développée en PHP/JS dans le cadre d'un projet académique.

**Équipe :**
- **Hamza Arfaoui** — Côté utilisateur (pages publiques + API)
- **Eya Kochbati** — Panel administration

---

## 🛠️ Stack technique

| Côté | Technologies |
|------|-------------|
| Backend | PHP 8, MySQL |
| Frontend | JavaScript Vanilla, CSS custom |
| Serveur local | XAMPP (Apache + MySQL) |
| Versioning | Git / GitHub |

---

## 📁 Structure du projet

```
LibraireWeb/
├── api/                    # REST API PHP
│   ├── index.php           # Routeur (?path=resource)
│   ├── config.php          # Connexion DB + helpers
│   ├── auth/auth.php       # Register, Login, Forgot password
│   ├── books/books.php     # CRUD livres
│   ├── categories/         # CRUD catégories
│   ├── orders/             # Commandes
│   └── users/              # Utilisateurs
├── assets/
│   ├── css/
│   │   ├── style.css       # CSS global (navbar, footer, boutons)
│   │   └── pages/          # CSS spécifique par page
│   └── js/main.js
├── includes/
│   ├── db.php              # Connexion MySQL
│   ├── header.php          # Header HTML partagé
│   └── footer.php          # Footer HTML partagé
├── public/                 # Pages accessibles
│   ├── index.php           # Accueil
│   ├── register.php        # Inscription
│   ├── login.php           # Connexion
│   ├── logout.php          # Déconnexion
│   ├── catalog.php         # Catalogue avec filtres
│   ├── book.php            # Détail d'un livre
│   ├── cart.php            # Panier + commande
│   ├── account.php         # Profil + historique
│   └── forgot_password.php # Réinitialisation mot de passe
├── admin/                  # Panel administration (Eya)
└── Database/
    └── bookdb.sql          # Export complet de la base de données
```

---

## 🗄️ Importer la base de données

### Méthode 1 — phpMyAdmin (recommandée)

1. Démarrez **XAMPP** → lancez **Apache** et **MySQL**
2. Ouvrez [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Cliquez sur **Nouvelle base de données**
4. Nom : `bookdb` → Interclassement : `utf8mb4_general_ci` → cliquez **Créer**
5. Sélectionnez `bookdb` dans la sidebar
6. Cliquez sur l'onglet **Importer**
7. Cliquez **Choisir un fichier** → sélectionnez `Database/bookdb.sql`
8. Cliquez **Importer** en bas de page
9. ✅ La base est importée avec toutes les tables et données

### Méthode 2 — Ligne de commande

```bash
# Windows (Git Bash ou CMD)
cd C:\xampp\mysql\bin

# Créer la base
mysql -u root -e "CREATE DATABASE bookdb CHARACTER SET utf8mb4;"

# Importer le fichier SQL
mysql -u root bookdb < C:\xampp\htdocs\LibraireWeb\Database\bookdb.sql
```

---

## 🚀 Lancer le projet

1. Clonez le dépôt :
```bash
git clone https://github.com/hamza/libraireweb.git
cd libraireweb
```

2. Copiez le dossier dans XAMPP :
```bash
# Windows
xcopy /E /I libraireweb C:\xampp\htdocs\LibraireWeb
```

3. Importez la base de données (voir section ci-dessus)

4. Vérifiez `includes/db.php` :
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // mot de passe MySQL si défini
define('DB_NAME', 'bookdb');
```

5. Démarrez Apache + MySQL dans XAMPP

6. Ouvrez [http://localhost/LibraireWeb/public/index.php](http://localhost/LibraireWeb/public/index.php)

---

## 🔑 Compte admin par défaut

Créez un compte via `/public/register.php` puis passez son rôle en `admin` dans phpMyAdmin :

```sql
UPDATE users SET role = 'admin' WHERE email = 'votre@email.com';
```

---

## 📋 Fonctionnalités

### Côté utilisateur (Hamza)
- ✅ Inscription avec validation
- ✅ Connexion / déconnexion
- ✅ Catalogue avec filtres par catégorie, prix, stock
- ✅ Recherche par titre ou auteur
- ✅ Page détail livre avec panier et favoris
- ✅ Panier avec gestion des quantités
- ✅ Passage de commande (API + gestion du stock)
- ✅ Compte utilisateur : profil, informations personnelles, historique des commandes
- ✅ Réinitialisation de mot de passe par code

### Panel admin (Eya)
- ✅ Dashboard avec statistiques
- ✅ Gestion des livres (CRUD + upload image)
- ✅ Gestion des commandes et statuts
- ✅ Gestion des catégories et utilisateurs

---

## 🌐 API REST

Base URL : `http://localhost/LibraireWeb/api/index.php?path=`

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `?path=books` | Liste des livres |
| GET | `?path=books/{id}` | Détail d'un livre |
| GET | `?path=categories` | Liste des catégories |
| POST | `?path=auth/register` | Inscription |
| POST | `?path=auth/login` | Connexion → token |
| POST | `?path=auth/forgot-password` | Envoi code reset |
| GET | `?path=orders` | Mes commandes (auth) |
| POST | `?path=orders` | Passer commande (auth) |

Les routes protégées nécessitent un header :
```
Authorization: Bearer <token>
```

---

*LibraireWeb | PHP/JS | Hamza Arfaoui & Eya Kochbati*
