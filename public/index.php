<?php
$pageTitle = "Accueil";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/LibraireWeb/assets/css/pages/home.css">

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-deco"></div>
    <div class="hero-inner">
        <div class="hero-text">
            <p class="hero-eyebrow">✦ Votre librairie en ligne</p>
            <h1 class="hero-title">
                Découvrez votre
                <em>prochaine lecture</em>
            </h1>
            <p class="hero-subtitle">
                Des milliers de livres soigneusement sélectionnés, livrés chez vous en quelques jours. Du roman au manuel, trouvez le livre qui vous correspond.
            </p>
            <div class="hero-actions">
                <a href="/LibraireWeb/public/catalog.php" class="btn-hero-primary">
                    Parcourir le catalogue →
                </a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/LibraireWeb/public/register.php" class="btn-hero-ghost">
                    Créer un compte
                </a>
                <?php endif; ?>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number" id="statBooks">—</div>
                    <div class="stat-label">Livres</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="statCats">—</div>
                    <div class="stat-label">Catégories</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24h</div>
                    <div class="stat-label">Livraison</div>
                </div>
            </div>
        </div>

        <div class="hero-cards">
            <div class="hero-card">
                <div class="card-genre">Roman</div>
                <div class="card-title">Le Comte de Monte-Cristo</div>
                <div class="card-author">Alexandre Dumas</div>
                <div class="card-price">18.90 DT</div>
            </div>
            <div class="hero-card">
                <div class="card-genre">Philosophie</div>
                <div class="card-title">L'Étranger</div>
                <div class="card-author">Albert Camus</div>
                <div class="card-price">12.50 DT</div>
            </div>
            <div class="hero-card">
                <div class="card-genre">Science-Fiction</div>
                <div class="card-title">Dune</div>
                <div class="card-author">Frank Herbert</div>
                <div class="card-price">22.00 DT</div>
            </div>
        </div>
    </div>
</section>

<!-- SEARCH BAR -->
<div class="search-section">
    <div class="search-inner">
        <div class="search-wrap">
            <span class="search-icon-label">🔍</span>
            <input type="text" id="searchInput" placeholder="Titre, auteur, mot-clé…">
        </div>
        <select class="search-select" id="catFilter">
            <option value="">Toutes les catégories</option>
        </select>
        <button class="search-submit" onclick="runSearch()">Rechercher</button>
    </div>
</div>

<!-- CATEGORY PILLS -->
<div class="cats-strip" id="catsStrip" style="display:none">
    <div class="cats-inner" id="catsPills"></div>
</div>

<!-- BOOKS -->
<div class="home-section">
    <div class="section-head">
        <div>
            <p class="section-label">Nouveautés</p>
            <h2 class="section-title" id="sectionTitle">Derniers arrivages</h2>
        </div>
        <a href="/LibraireWeb/public/catalog.php" class="section-more">Voir tout →</a>
    </div>
    <div class="books-grid" id="booksGrid">
        <?php for ($i = 0; $i < 8; $i++): ?>
        <div class="skeleton-card">
            <div class="skeleton skeleton-cover"></div>
            <div class="skeleton-body">
                <div class="skeleton skeleton-line w-40"></div>
                <div class="skeleton skeleton-line w-85"></div>
                <div class="skeleton skeleton-line w-60"></div>
                <div class="skeleton skeleton-line w-30" style="margin-top:14px"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- PROMO BANNER -->
<div class="promo-wrap">
    <div class="promo-banner">
        <div class="promo-text">
            <p class="promo-eyebrow">📦 Offre spéciale</p>
            <h3 class="promo-title">Livraison gratuite<br>dès 30 DT d'achat</h3>
            <p class="promo-sub">Commandez vos livres préférés et profitez de la livraison offerte partout en Tunisie.</p>
        </div>
        <a href="/LibraireWeb/public/catalog.php" class="btn-promo">En profiter →</a>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API          = '/LibraireWeb/api/index.php';
const COLORS       = 6;
const IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

async function init() {
    await Promise.all([loadCategories(), loadBooks(null)]);
}

// ── Categories ──
async function loadCategories() {
    try {
        const res  = await fetch(`${API}?path=categories`);
        const cats = await res.json();

        document.getElementById('statCats').textContent = cats.length;

        const sel = document.getElementById('catFilter');
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nom;
            sel.appendChild(opt);
        });

        if (cats.length > 0) {
            document.getElementById('catsStrip').style.display = '';
            const pills = document.getElementById('catsPills');
            pills.appendChild(makePill('Tous', null, true));
            cats.forEach(c => pills.appendChild(makePill(`${c.nom} (${c.books_count})`, c.id, false)));
        }
    } catch(e) { console.error('Categories error', e); }
}

// ── Pills — fetch from API on click ──
function makePill(label, catId, active) {
    const a = document.createElement('a');
    a.className = 'cat-pill' + (active ? ' active' : '');
    a.textContent = label;
    a.href = '#';
    a.onclick = async (e) => {
        e.preventDefault();
        document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
        a.classList.add('active');
        document.getElementById('catFilter').value = catId ?? '';
        // Update section title
        document.getElementById('sectionTitle').textContent =
            catId ? label.replace(/\s*\(\d+\)$/, '') : 'Derniers arrivages';
        await loadBooks(catId);
    };
    return a;
}

// ── Load books from API ──
async function loadBooks(catId) {
    const grid = document.getElementById('booksGrid');

    // Show skeletons while loading
    grid.innerHTML = '';
    for (let i = 0; i < 8; i++) {
        grid.innerHTML += `
        <div class="skeleton-card">
            <div class="skeleton skeleton-cover"></div>
            <div class="skeleton-body">
                <div class="skeleton skeleton-line w-40"></div>
                <div class="skeleton skeleton-line w-85"></div>
                <div class="skeleton skeleton-line w-60"></div>
                <div class="skeleton skeleton-line w-30" style="margin-top:14px"></div>
            </div>
        </div>`;
    }

    try {
        let url = `${API}?path=books&limit=12`;
        if (catId) url += `&category_id=${catId}`;

        const res   = await fetch(url);
        const data  = await res.json();
        const books = data.data ?? [];

        // Update total count on initial load only
        if (!catId) {
            document.getElementById('statBooks').textContent = data.pagination?.total ?? books.length;
        }

        if (!books.length) {
            grid.innerHTML = '<div class="empty-state"><div class="empty-icon">🔍</div><p>Aucun livre dans cette catégorie.</p></div>';
            return;
        }

        grid.innerHTML = '';
        books.forEach((book, i) => grid.appendChild(bookCard(book, i)));

    } catch(e) {
        grid.innerHTML = '<div class="empty-state"><div class="empty-icon">📭</div><p>Impossible de charger les livres.</p></div>';
    }
}

// ── Book card ──
function bookCard(book, idx) {
    const a       = document.createElement('a');
    a.className   = 'book-card';
    a.href        = `/LibraireWeb/public/book.php?id=${book.id}`;
    const inStock = book.stock > 0;

    a.innerHTML = `
        <div class="book-cover" data-color="${idx % COLORS}">
            ${book.image
                ? `<img src="${book.image}" alt="${esc(book.titre)}" onerror="this.style.display='none'">`
                : `<span class="book-cover-label">${esc(book.titre)}</span>`}
            <span class="stock-tag ${inStock ? 'in' : 'out'}">
                ${inStock ? '✓ Disponible' : '✗ Épuisé'}
            </span>
        </div>
        <div class="book-info">
            <p class="book-cat">${esc(book.category?.nom ?? 'Général')}</p>
            <h3 class="book-title">${esc(book.titre)}</h3>
            <p class="book-author">${esc(book.auteur)}</p>
            <div class="book-foot">
                <span class="book-price">${parseFloat(book.prix).toFixed(2)} DT</span>
                ${inStock
                    ? `<button class="btn-cart" onclick="addToCart(event,${book.id},'${esc(book.titre)}')">+ Panier</button>`
                    : `<span style="font-size:0.8rem;color:#bbb">Épuisé</span>`}
            </div>
        </div>`;
    return a;
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

// ── Add to cart ──
function addToCart(e, id, title) {
    e.preventDefault();
    e.stopPropagation();

    if (!IS_LOGGED_IN) {
        showToast('🔒 Connectez-vous pour ajouter au panier');
        setTimeout(() => window.location.href = '/LibraireWeb/public/login.php', 1200);
        return;
    }

    let cart = JSON.parse(localStorage.getItem('cart') ?? '[]');
    const existing = cart.find(i => i.id === id);
    existing ? existing.qty++ : cart.push({ id, title, qty: 1 });
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showToast(`📚 "${title}" ajouté au panier`);
}

function updateCartCount() {
    const total = JSON.parse(localStorage.getItem('cart') ?? '[]').reduce((s,i) => s + i.qty, 0);
    const badge = document.getElementById('cartCount');
    if (badge) badge.textContent = total;
}

// ── Search ──
function runSearch() {
    const q   = document.getElementById('searchInput').value.trim();
    const cat = document.getElementById('catFilter').value;
    let url   = '/LibraireWeb/public/catalog.php?';
    if (q)   url += `search=${encodeURIComponent(q)}&`;
    if (cat) url += `category_id=${cat}&`;
    window.location.href = url;
}

document.getElementById('searchInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') runSearch();
});

// ── Toast ──
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

updateCartCount();
init();
</script>

<?php require_once '../includes/footer.php'; ?>