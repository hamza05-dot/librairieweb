<?php
$pageTitle = "Livre";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/LibraireWeb/assets/css/pages/book.css">

<script>
    const IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>

<div class="main-content">

    <!-- Loading state -->
    <div class="book-loading" id="bookLoading">
        <div class="spinner"></div>
        <p style="font-family:var(--font-body);color:var(--color-text-muted)">Chargement du livre…</p>
    </div>

    <!-- Error state -->
    <div class="book-error" id="bookError" style="display:none">
        <div style="font-size:3rem">📭</div>
        <h2>Livre introuvable</h2>
        <p>Ce livre n'existe pas ou a été supprimé.</p>
        <a href="/LibraireWeb/public/catalog.php" class="btn btn-primary" style="margin-top:12px">
            ← Retour au catalogue
        </a>
    </div>

    <!-- Book content -->
    <div id="bookContent" style="display:none">

        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/LibraireWeb/public/index.php">Accueil</a>
            <span class="breadcrumb-sep">›</span>
            <a href="/LibraireWeb/public/catalog.php">Catalogue</a>
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-current" id="breadcrumbTitle">…</span>
        </nav>

        <!-- Main layout -->
        <div class="book-layout">

            <!-- LEFT: Cover -->
            <div class="cover-col">
                <div class="book-cover-wrap" id="coverWrap" data-color="0">
                    <div class="cover-placeholder" id="coverPlaceholder">
                        <div class="cover-placeholder-icon">📖</div>
                        <div class="cover-placeholder-title" id="coverTitle"></div>
                        <div class="cover-placeholder-author" id="coverAuthor"></div>
                    </div>
                    <img id="coverImg" src="" alt="" style="display:none"
                         onerror="this.style.display='none'">
                    <span class="cover-stock" id="coverStock"></span>
                </div>

                <div class="cover-stats">
                    <div class="cover-stat">
                        <div class="cover-stat-value" id="statPrice">—</div>
                        <div class="cover-stat-label">Prix</div>
                    </div>
                    <div class="cover-stat">
                        <div class="cover-stat-value" id="statStock">—</div>
                        <div class="cover-stat-label">En stock</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Info -->
            <div class="info-col">

                <div class="book-category-tag" id="categoryTag">📚 Général</div>

                <h1 class="book-main-title" id="bookTitle">—</h1>
                <p class="book-author-line">par <strong id="bookAuthor">—</strong></p>

                <!-- Price box -->
                <div class="price-box">
                    <div class="price-main">
                        <span class="price-currency">DT </span><span id="priceValue">—</span>
                    </div>
                    <div class="price-divider"></div>
                    <div class="price-meta">
                        <div class="price-stock-text" id="stockText">—</div>
                        <div class="price-stock-qty" id="stockQty"></div>
                    </div>
                </div>

                <!-- Cart row -->
                <div class="cart-row">
                    <div class="qty-control">
                        <button class="qty-btn" id="qtyMinus" onclick="changeQty(-1)">−</button>
                        <input class="qty-input" type="number" id="qtyInput" value="1" min="1" max="99" readonly>
                        <button class="qty-btn" id="qtyPlus" onclick="changeQty(1)">+</button>
                    </div>
                    <button class="btn-add-cart" id="btnAddCart" onclick="addToCart()">
                        🛒 Ajouter au panier
                    </button>
                    <button class="btn-wishlist" id="btnWishlist" onclick="toggleWishlist()" title="Favoris">
                        🤍
                    </button>
                </div>

                <hr class="info-divider">

                <!-- Description -->
                <div>
                    <p class="desc-label">Description</p>
                    <p class="book-description clamped" id="bookDesc">Aucune description disponible.</p>
                    <button class="btn-read-more" id="btnReadMore" onclick="toggleDesc()" style="display:none">
                        Lire plus ↓
                    </button>
                </div>

                <hr class="info-divider">

                <!-- Meta chips -->
                <div class="meta-chips">
                    <div class="meta-chip">📅 Ajouté le <strong id="metaDate">—</strong></div>
                    <div class="meta-chip">🏷️ Catégorie : <strong id="metaCat">—</strong></div>
                    <div class="meta-chip">📦 Stock : <strong id="metaStock">—</strong></div>
                </div>

            </div>
        </div>

        <!-- Related books -->
        <div class="related-section" id="relatedSection" style="display:none">
            <div class="related-header">
                <div>
                    <p class="related-label">Vous aimerez aussi</p>
                    <h2 class="related-title">Dans la même catégorie</h2>
                </div>
                <a href="/LibraireWeb/public/catalog.php" id="relatedCatLink" class="btn btn-ghost btn-sm">
                    Voir tout →
                </a>
            </div>
            <div class="related-grid" id="relatedGrid"></div>
        </div>

    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API         = '/LibraireWeb/api/index.php';
const COLORS      = 6;
const bookId      = new URLSearchParams(window.location.search).get('id');
let currentBook   = null;
let descExpanded  = false;

// ── Boot ──
if (!bookId) {
    showError();
} else {
    loadBook(bookId);
}
updateCartCount();

// ── Load book ──
async function loadBook(id) {
    try {
        const res  = await fetch(`${API}?path=books/${id}`);
        const book = await res.json();
        if (book.error) { showError(); return; }
        currentBook = book;
        renderBook(book);
        if (book.category) loadRelated(book.category.id, book.id);
    } catch(e) {
        showError();
    }
}

function renderBook(b) {
    document.title = `LibraireWeb — ${b.titre}`;
    document.getElementById('breadcrumbTitle').textContent = b.titre;

    // Cover
    const colorIdx = b.id % 6;
    document.getElementById('coverWrap').setAttribute('data-color', colorIdx);
    document.getElementById('coverTitle').textContent  = b.titre;
    document.getElementById('coverAuthor').textContent = b.auteur;

    if (b.image) {
        const img = document.getElementById('coverImg');
        img.src   = b.image; // direct URL (openlibrary or local)
        img.alt   = b.titre;
        img.style.display = 'block';
        img.onload = () => document.getElementById('coverPlaceholder').style.display = 'none';
    }

    const inStock = b.stock > 0;
    const stockEl = document.getElementById('coverStock');
    stockEl.textContent = inStock ? '✓ Disponible' : '✗ Épuisé';
    stockEl.className   = `cover-stock ${inStock ? 'in' : 'out'}`;

    document.getElementById('statPrice').textContent = parseFloat(b.prix).toFixed(2) + ' DT';
    document.getElementById('statStock').textContent = b.stock;

    if (b.category) {
        document.getElementById('categoryTag').textContent = `📚 ${b.category.nom}`;
        document.getElementById('metaCat').textContent     = b.category.nom;
    }

    document.getElementById('bookTitle').textContent  = b.titre;
    document.getElementById('bookAuthor').textContent = b.auteur;
    document.getElementById('priceValue').textContent = parseFloat(b.prix).toFixed(2);

    const stockText = document.getElementById('stockText');
    stockText.textContent = inStock ? '✓ En stock' : '✗ Épuisé';
    stockText.className   = `price-stock-text ${inStock ? 'in' : 'out'}`;
    document.getElementById('stockQty').textContent   = inStock
        ? `${b.stock} exemplaire${b.stock > 1 ? 's' : ''} disponible${b.stock > 1 ? 's' : ''}`
        : 'Revenez bientôt';

    // Cart button state
    if (!inStock) {
        document.getElementById('btnAddCart').disabled    = true;
        document.getElementById('btnAddCart').textContent = '✗ Épuisé';
        document.getElementById('qtyPlus').disabled       = true;
        document.getElementById('qtyMinus').disabled      = true;
    } else {
        document.getElementById('qtyInput').max = b.stock;
    }

    // Description
    const descEl = document.getElementById('bookDesc');
    if (b.description) {
        descEl.textContent = b.description;
        if (b.description.length > 300) {
            document.getElementById('btnReadMore').style.display = 'block';
        } else {
            descEl.classList.remove('clamped');
        }
    }

    const date = b.created_at ? new Date(b.created_at).toLocaleDateString('fr-FR') : '—';
    document.getElementById('metaDate').textContent  = date;
    document.getElementById('metaStock').textContent = b.stock;

    // Wishlist state
    const wishlist = JSON.parse(localStorage.getItem('wishlist') ?? '[]');
    if (wishlist.includes(b.id)) {
        document.getElementById('btnWishlist').textContent = '❤️';
        document.getElementById('btnWishlist').classList.add('active');
    }

    document.getElementById('bookLoading').style.display = 'none';
    document.getElementById('bookContent').style.display = 'block';
}

// ── Related books ──
async function loadRelated(catId, currentId) {
    try {
        const res   = await fetch(`${API}?path=books&category_id=${catId}&limit=8`);
        const data  = await res.json();
        const books = (data.data ?? []).filter(b => b.id !== currentId).slice(0, 5);
        if (!books.length) return;

        const grid = document.getElementById('relatedGrid');
        books.forEach(b => {
            const a = document.createElement('a');
            a.className = 'rel-card';
            a.href = `/LibraireWeb/public/book.php?id=${b.id}`;
            a.innerHTML = `
                <div class="rel-cover" data-color="${b.id % 6}">
                    ${b.image
                        ? `<img src="${b.image}" alt="${esc(b.titre)}" onerror="this.style.display='none'">`
                        : `<span class="rel-cover-label">${esc(b.titre)}</span>`}
                </div>
                <div class="rel-info">
                    <div class="rel-title">${esc(b.titre)}</div>
                    <div class="rel-author">${esc(b.auteur)}</div>
                    <div class="rel-price">${parseFloat(b.prix).toFixed(2)} DT</div>
                </div>`;
            grid.appendChild(a);
        });

        document.getElementById('relatedCatLink').href =
            `/LibraireWeb/public/catalog.php?category_id=${catId}`;
        document.getElementById('relatedSection').style.display = 'block';
    } catch(e) {}
}

// ── Quantity ──
function changeQty(delta) {
    const input = document.getElementById('qtyInput');
    const max   = currentBook ? currentBook.stock : 99;
    let val     = Math.max(1, Math.min(max, parseInt(input.value) + delta));
    input.value = val;
    document.getElementById('qtyMinus').disabled = val <= 1;
    document.getElementById('qtyPlus').disabled  = val >= max;
}

// ── Add to cart ──
function addToCart() {
    if (!IS_LOGGED_IN) {
        showToast('🔒 Connectez-vous pour ajouter au panier');
        setTimeout(() => window.location.href = '/LibraireWeb/public/login.php', 1200);
        return;
    }

    if (!currentBook) return;
    const qty      = parseInt(document.getElementById('qtyInput').value);
    let cart       = JSON.parse(localStorage.getItem('cart') ?? '[]');
    const existing = cart.find(i => i.id === currentBook.id);
    if (existing) {
        existing.qty = Math.min(existing.qty + qty, currentBook.stock);
    } else {
        cart.push({ id: currentBook.id, title: currentBook.titre, qty });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();

    const btn = document.getElementById('btnAddCart');
    btn.classList.add('added');
    btn.textContent = '✓ Ajouté !';
    setTimeout(() => {
        btn.classList.remove('added');
        btn.innerHTML = '🛒 Ajouter au panier';
    }, 2000);

    showToast(`📚 "${currentBook.titre}" ajouté au panier`);
}

// ── Wishlist ──
function toggleWishlist() {
    if (!IS_LOGGED_IN) {
        showToast('🔒 Connectez-vous pour ajouter aux favoris');
        setTimeout(() => window.location.href = '/LibraireWeb/public/login.php', 1200);
        return;
    }

    if (!currentBook) return;
    let wishlist = JSON.parse(localStorage.getItem('wishlist') ?? '[]');
    const btn    = document.getElementById('btnWishlist');
    const idx    = wishlist.indexOf(currentBook.id);
    if (idx === -1) {
        wishlist.push(currentBook.id);
        btn.textContent = '❤️';
        btn.classList.add('active');
        showToast('❤️ Ajouté aux favoris');
    } else {
        wishlist.splice(idx, 1);
        btn.textContent = '🤍';
        btn.classList.remove('active');
        showToast('🤍 Retiré des favoris');
    }
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

// ── Description toggle ──
function toggleDesc() {
    const desc = document.getElementById('bookDesc');
    const btn  = document.getElementById('btnReadMore');
    descExpanded = !descExpanded;
    desc.classList.toggle('clamped', !descExpanded);
    btn.textContent = descExpanded ? 'Lire moins ↑' : 'Lire plus ↓';
}

// ── Helpers ──
function showError() {
    document.getElementById('bookLoading').style.display = 'none';
    document.getElementById('bookError').style.display   = 'flex';
}

function updateCartCount() {
    const total = JSON.parse(localStorage.getItem('cart') ?? '[]').reduce((s,i) => s + i.qty, 0);
    const badge = document.getElementById('cartCount');
    if (badge) badge.textContent = total;
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}
</script>

<?php require_once '../includes/footer.php'; ?>