<?php
$pageTitle = "Catalogue";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/LibraireWeb/assets/css/pages/cataloge.css">

<!-- ══ BANNER ══ -->
<div class="catalog-banner">
    <div class="catalog-banner-inner">
        <div>
            <h1>📚 Catalogue</h1>
            <p id="bannerSub">Chargement…</p>
        </div>
        <div class="catalog-search-row">
            <div class="catalog-search-wrap">
                <span class="catalog-search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Titre, auteur…">
            </div>
            <button class="catalog-search-btn" onclick="applySearch()">Rechercher</button>
        </div>
    </div>
</div>

<!-- ══ LAYOUT ══ -->
<div class="catalog-layout">

    <!-- ── SIDEBAR ── -->
    <aside class="sidebar">
        <div class="sidebar-head">
            <h3>🎛️ Filtres</h3>
            <button class="btn-clear" onclick="clearAll()">Tout effacer</button>
        </div>

        <!-- Categories -->
        <div class="filter-block">
            <span class="filter-block-label">Catégories</span>
            <ul class="cat-list" id="catList">
                <li>
                    <button onclick="setCat(null)" class="active" id="catAll">
                        Toutes <span class="cat-count" id="catAllCount">…</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Price range -->
        <div class="filter-block">
            <span class="filter-block-label">Prix (DT)</span>
            <div class="price-row">
                <input type="number" id="priceMin" placeholder="Min" min="0">
                <input type="number" id="priceMax" placeholder="Max" min="0">
            </div>
            <button class="btn-apply" onclick="applyPrice()">Appliquer</button>
        </div>

        <!-- Stock -->
        <div class="filter-block">
            <span class="filter-block-label">Disponibilité</span>
            <label class="stock-label">
                <span class="sw">
                    <input type="checkbox" id="stockOnly" onchange="fetchBooks()">
                    <span class="sw-track"></span>
                </span>
                En stock uniquement
            </label>
        </div>

        <!-- Sort -->
        <div class="filter-block">
            <span class="filter-block-label">Trier par</span>
            <select class="sort-sel" id="sortSel" onchange="fetchBooks()">
                <option value="id_desc">Plus récents</option>
                <option value="id_asc">Plus anciens</option>
                <option value="prix_asc">Prix croissant</option>
                <option value="prix_desc">Prix décroissant</option>
                <option value="titre_asc">Titre A→Z</option>
                <option value="titre_desc">Titre Z→A</option>
            </select>
        </div>
    </aside>

    <!-- ── MAIN ── -->
    <div class="catalog-main">

        <!-- Toolbar -->
        <div class="catalog-toolbar">
            <div class="results-info" id="resultsInfo">Chargement…</div>
        </div>

        <!-- Active filter chips -->
        <div class="active-filters" id="activeFilters"></div>

        <!-- Books grid -->
        <div class="catalog-grid" id="catalogGrid">
            <?php for ($i = 0; $i < 12; $i++): ?>
            <div class="cat-skel-card">
                <div class="skeleton cat-skel-cover"></div>
                <div class="cat-skel-body">
                    <div class="skeleton cat-skel-line" style="width:45%"></div>
                    <div class="skeleton cat-skel-line" style="width:88%"></div>
                    <div class="skeleton cat-skel-line" style="width:65%"></div>
                    <div class="skeleton cat-skel-line" style="width:35%;margin-top:12px"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination"></div>

    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API          = '/LibraireWeb/api/index.php';
const COLORS       = 6;
const IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
const LIMIT        = 20;

// ── State ──
let state = {
    page:       1,
    catId:      null,
    catName:    null,
    search:     '',
    priceMin:   '',
    priceMax:   '',
    stockOnly:  false,
    sort:       'id_desc',
    total:      0,
    totalPages: 1,
};

// ── Read URL params on load ──
function readUrlParams() {
    const p = new URLSearchParams(window.location.search);
    if (p.get('category_id')) { state.catId   = p.get('category_id'); }
    if (p.get('search'))      { state.search  = p.get('search'); document.getElementById('searchInput').value = state.search; }
    if (p.get('page'))        { state.page    = parseInt(p.get('page')) || 1; }
}

// ── Init ──
async function init() {
    readUrlParams();
    await loadCategories();
    await fetchBooks();
}

// ── Load categories ──
async function loadCategories() {
    try {
        const res  = await fetch(`${API}?path=categories`);
        const cats = await res.json();
        const list = document.getElementById('catList');

        // Update "all" count
        const total = cats.reduce((s, c) => s + c.books_count, 0);
        document.getElementById('catAllCount').textContent = total;

        cats.forEach(c => {
            const li  = document.createElement('li');
            const btn = document.createElement('button');
            btn.id = `cat-btn-${c.id}`;
            btn.innerHTML = `${esc(c.nom)} <span class="cat-count">${c.books_count}</span>`;
            btn.onclick = () => setCat(c.id, c.nom);
            if (String(c.id) === String(state.catId)) {
                btn.classList.add('active');
                state.catName = c.nom;
                document.getElementById('catAll').classList.remove('active');
            }
            li.appendChild(btn);
            list.appendChild(li);
        });
    } catch(e) {}
}

// ── Fetch books ──
async function fetchBooks() {
    state.sort      = document.getElementById('sortSel').value;
    state.stockOnly = document.getElementById('stockOnly').checked;

    const grid = document.getElementById('catalogGrid');
    showSkeletons(grid);

    let url = `${API}?path=books&limit=${LIMIT}&page=${state.page}`;
    if (state.catId)    url += `&category_id=${state.catId}`;
    if (state.search)   url += `&search=${encodeURIComponent(state.search)}`;

    // Price filter — we'll filter client-side since API doesn't support it natively
    // Sort
    const [sortField, sortDir] = state.sort.split('_');

    try {
        const res  = await fetch(url);
        const data = await res.json();
        let books  = data.data ?? [];

        // Client-side price filter
        if (state.priceMin !== '') books = books.filter(b => parseFloat(b.prix) >= parseFloat(state.priceMin));
        if (state.priceMax !== '') books = books.filter(b => parseFloat(b.prix) <= parseFloat(state.priceMax));

        // Client-side stock filter
        if (state.stockOnly) books = books.filter(b => b.stock > 0);

        // Client-side sort
        books.sort((a, b) => {
            if (sortField === 'prix') {
                return sortDir === 'asc' ? a.prix - b.prix : b.prix - a.prix;
            } else if (sortField === 'titre') {
                return sortDir === 'asc'
                    ? a.titre.localeCompare(b.titre)
                    : b.titre.localeCompare(a.titre);
            } else { // id
                return sortDir === 'asc' ? a.id - b.id : b.id - a.id;
            }
        });

        state.total      = data.pagination?.total ?? books.length;
        state.totalPages = data.pagination?.total_pages ?? 1;

        renderBooks(books);
        renderPagination();
        updateInfo(books.length, data.pagination?.total ?? books.length);
        updateChips();
        updateBanner();

    } catch(e) {
        grid.innerHTML = `<div class="catalog-empty">
            <div class="ei">📭</div>
            <h3>Erreur de chargement</h3>
            <p>Impossible de charger les livres.</p>
        </div>`;
    }
}

// ── Render books ──
function renderBooks(books) {
    const grid = document.getElementById('catalogGrid');
    if (!books.length) {
        grid.innerHTML = `<div class="catalog-empty">
            <div class="ei">🔍</div>
            <h3>Aucun résultat</h3>
            <p>Essayez d'autres filtres ou une autre recherche.</p>
            <button class="btn btn-ghost" onclick="clearAll()">Effacer les filtres</button>
        </div>`;
        return;
    }
    grid.innerHTML = '';
    books.forEach((book, i) => grid.appendChild(bookCard(book, i)));
}

function bookCard(book, idx) {
    const a       = document.createElement('a');
    a.className   = 'cat-book-card';
    a.href        = `/LibraireWeb/public/book.php?id=${book.id}`;
    const inStock = book.stock > 0;

    a.innerHTML = `
        <div class="cat-cover" data-color="${idx % COLORS}">
            ${book.image
                ? `<img src="${book.image}" alt="${esc(book.titre)}" onerror="this.style.display='none'">`
                : `<span class="cat-cover-label">${esc(book.titre)}</span>`}
            <span class="cat-stock-tag ${inStock ? 'in' : 'out'}">
                ${inStock ? '✓' : '✗'} ${inStock ? 'Dispo' : 'Épuisé'}
            </span>
        </div>
        <div class="cat-book-info">
            <p class="cat-book-cat">${esc(book.category?.nom ?? 'Général')}</p>
            <h3 class="cat-book-title">${esc(book.titre)}</h3>
            <p class="cat-book-author">${esc(book.auteur)}</p>
            <div class="cat-book-foot">
                <span class="cat-book-price">${parseFloat(book.prix).toFixed(2)} DT</span>
                ${inStock
                    ? `<button class="btn-cat-cart" onclick="addToCart(event,${book.id},'${esc(book.titre)}')">+ Panier</button>`
                    : `<span style="font-size:0.75rem;color:#bbb">Épuisé</span>`}
            </div>
        </div>`;
    return a;
}

// ── Pagination ──
function renderPagination() {
    const pag = document.getElementById('pagination');
    pag.innerHTML = '';
    if (state.totalPages <= 1) return;

    const prev = btn('←', state.page <= 1, () => goPage(state.page - 1));
    pag.appendChild(prev);

    // Page numbers: show window around current
    const start = Math.max(1, state.page - 2);
    const end   = Math.min(state.totalPages, state.page + 2);

    if (start > 1) { pag.appendChild(btn('1', false, () => goPage(1))); if (start > 2) pag.appendChild(dots()); }

    for (let i = start; i <= end; i++) {
        const b = btn(i, false, () => goPage(i));
        if (i === state.page) b.classList.add('active');
        pag.appendChild(b);
    }

    if (end < state.totalPages) { if (end < state.totalPages - 1) pag.appendChild(dots()); pag.appendChild(btn(state.totalPages, false, () => goPage(state.totalPages))); }

    const next = btn('→', state.page >= state.totalPages, () => goPage(state.page + 1));
    pag.appendChild(next);

    const info = document.createElement('span');
    info.className = 'page-info';
    info.textContent = `Page ${state.page} / ${state.totalPages}`;
    pag.appendChild(info);
}

function btn(label, disabled, onClick) {
    const b = document.createElement('button');
    b.className = 'page-btn';
    b.textContent = label;
    b.disabled = disabled;
    b.onclick = onClick;
    return b;
}

function dots() {
    const s = document.createElement('span');
    s.className = 'page-info'; s.textContent = '…';
    return s;
}

function goPage(p) {
    state.page = p;
    fetchBooks();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Filters ──
function setCat(id, name = null) {
    state.catId   = id;
    state.catName = name;
    state.page    = 1;

    document.querySelectorAll('.cat-list button').forEach(b => b.classList.remove('active'));
    if (id === null) {
        document.getElementById('catAll').classList.add('active');
    } else {
        const btn = document.getElementById(`cat-btn-${id}`);
        if (btn) btn.classList.add('active');
    }
    fetchBooks();
}

function applySearch() {
    state.search = document.getElementById('searchInput').value.trim();
    state.page   = 1;
    fetchBooks();
}

function applyPrice() {
    state.priceMin = document.getElementById('priceMin').value;
    state.priceMax = document.getElementById('priceMax').value;
    state.page     = 1;
    fetchBooks();
}

function clearAll() {
    state.catId    = null;
    state.catName  = null;
    state.search   = '';
    state.priceMin = '';
    state.priceMax = '';
    state.stockOnly= false;
    state.page     = 1;

    document.getElementById('searchInput').value  = '';
    document.getElementById('priceMin').value     = '';
    document.getElementById('priceMax').value     = '';
    document.getElementById('stockOnly').checked  = false;
    document.getElementById('sortSel').value      = 'id_desc';

    document.querySelectorAll('.cat-list button').forEach(b => b.classList.remove('active'));
    document.getElementById('catAll').classList.add('active');

    fetchBooks();
}

// ── Active filter chips ──
function updateChips() {
    const wrap = document.getElementById('activeFilters');
    wrap.innerHTML = '';

    if (state.catName) {
        wrap.appendChild(chip(`📚 ${state.catName}`, () => setCat(null)));
    }
    if (state.search) {
        wrap.appendChild(chip(`🔍 "${state.search}"`, () => {
            state.search = ''; document.getElementById('searchInput').value = ''; fetchBooks();
        }));
    }
    if (state.priceMin || state.priceMax) {
        const label = `💰 ${state.priceMin || '0'} – ${state.priceMax || '∞'} DT`;
        wrap.appendChild(chip(label, () => {
            state.priceMin = ''; state.priceMax = '';
            document.getElementById('priceMin').value = '';
            document.getElementById('priceMax').value = '';
            fetchBooks();
        }));
    }
    if (state.stockOnly) {
        wrap.appendChild(chip('✅ En stock', () => {
            state.stockOnly = false;
            document.getElementById('stockOnly').checked = false;
            fetchBooks();
        }));
    }
}

function chip(label, onRemove) {
    const div = document.createElement('div');
    div.className = 'filter-chip';
    div.innerHTML = `${esc(label)} <button onclick="">✕</button>`;
    div.querySelector('button').onclick = onRemove;
    return div;
}

// ── Info text ──
function updateInfo(shown, total) {
    document.getElementById('resultsInfo').innerHTML =
        `<strong>${total}</strong> livre${total !== 1 ? 's' : ''} trouvé${total !== 1 ? 's' : ''}`;
}

function updateBanner() {
    document.getElementById('bannerSub').textContent =
        state.catName
            ? `Catégorie : ${state.catName}`
            : `${state.total} livres disponibles`;
}

// ── Skeletons ──
function showSkeletons(grid) {
    grid.innerHTML = '';
    for (let i = 0; i < 12; i++) {
        grid.innerHTML += `
        <div class="cat-skel-card">
            <div class="skeleton cat-skel-cover"></div>
            <div class="cat-skel-body">
                <div class="skeleton cat-skel-line" style="width:45%"></div>
                <div class="skeleton cat-skel-line" style="width:88%"></div>
                <div class="skeleton cat-skel-line" style="width:65%"></div>
                <div class="skeleton cat-skel-line" style="width:35%;margin-top:10px"></div>
            </div>
        </div>`;
    }
}

// ── Cart ──
function addToCart(e, id, title) {
    e.preventDefault(); e.stopPropagation();

    if (!IS_LOGGED_IN) {
        showToast('🔒 Connectez-vous pour ajouter au panier');
        setTimeout(() => window.location.href = '/LibraireWeb/public/login.php', 1200);
        return;
    }

    let cart = JSON.parse(localStorage.getItem('cart') ?? '[]');
    const ex = cart.find(i => i.id === id);
    ex ? ex.qty++ : cart.push({ id, title, qty: 1 });
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showToast(`📚 "${title}" ajouté au panier`);
}

function updateCartCount() {
    const total = JSON.parse(localStorage.getItem('cart') ?? '[]').reduce((s,i) => s + i.qty, 0);
    const badge = document.getElementById('cartCount');
    if (badge) badge.textContent = total;
}

// ── Search on Enter ──
document.getElementById('searchInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') applySearch();
});

// ── Toast ──
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

// ── Boot ──
updateCartCount();
init();
</script>

<?php require_once '../includes/footer.php'; ?>