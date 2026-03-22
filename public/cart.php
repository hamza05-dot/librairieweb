<?php
$pageTitle = "Panier";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /LibraireWeb/public/login.php?redirect=cart.php');
    exit;
}

// Ensure API token exists in session + DB
if (empty($_SESSION['token'])) {
    require_once '../includes/db.php';
    $token = bin2hex(random_bytes(32));
    $conn  = getConnection();
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS token VARCHAR(64) NULL");
    $upd = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
    $upd->bind_param("si", $token, $_SESSION['user_id']);
    $upd->execute();
    $upd->close();
    $conn->close();
    $_SESSION['token'] = $token;
}

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/LibraireWeb/assets/css/pages/cart.css">

<div class="main-content">

    <!-- Breadcrumb -->
    <nav class="breadcrumb" style="margin-bottom:28px">
        <a href="/LibraireWeb/public/index.php">Accueil</a>
        <span style="color:var(--color-border);margin:0 8px">›</span>
        <span style="color:var(--color-text);font-weight:500">Mon Panier</span>
    </nav>

    <div class="cart-layout" id="cartLayout">

        <!-- ── LEFT: Cart items ── -->
        <div class="cart-items-col">
            <div class="cart-header">
                <h1 class="cart-title">🛒 Mon Panier</h1>
                <span class="cart-badge" id="cartBadge">0 article</span>
            </div>

            <!-- Empty state -->
            <div class="cart-empty" id="cartEmpty" style="display:none">
                <div class="empty-icon-big">🛒</div>
                <h3>Votre panier est vide</h3>
                <p>Ajoutez des livres depuis le catalogue pour commencer.</p>
                <a href="/LibraireWeb/public/catalog.php" class="btn btn-primary">
                    Parcourir le catalogue →
                </a>
            </div>

            <!-- Items list -->
            <div id="cartItemsList"></div>

            <!-- Clear cart -->
            <div id="clearRow" style="display:none;margin-top:16px">
                <button class="btn-clear-cart" onclick="clearCart()">🗑️ Vider le panier</button>
            </div>
        </div>

        <!-- ── RIGHT: Summary ── -->
        <div class="cart-summary-col" id="summaryCol">
            <div class="summary-card">
                <h2 class="summary-title">Récapitulatif</h2>

                <div class="summary-lines" id="summaryLines"></div>

                <div class="summary-divider"></div>

                <div class="summary-total">
                    <span>Total</span>
                    <span id="summaryTotal">0.00 DT</span>
                </div>

                <div class="delivery-note">
                    <?php // Show free delivery note ?>
                    <span id="deliveryMsg">🚚 Livraison offerte dès 30 DT</span>
                </div>

                <button class="btn-order" id="btnOrder" onclick="placeOrder()">
                    <span id="orderBtnText">✅ Passer la commande</span>
                    <span id="orderBtnSpinner" style="display:none">⏳ Traitement…</span>
                </button>

                <a href="/LibraireWeb/public/catalog.php" class="btn-continue">
                    ← Continuer mes achats
                </a>
            </div>
        </div>
    </div>

    <!-- Order success -->
    <div class="order-success" id="orderSuccess" style="display:none">
        <div class="success-icon">🎉</div>
        <h2>Commande passée avec succès !</h2>
        <p>Votre commande a été enregistrée. Vous pouvez suivre son statut dans votre compte.</p>
        <div class="success-actions">
            <a href="/LibraireWeb/public/account.php" class="btn btn-primary">Voir mes commandes →</a>
            <a href="/LibraireWeb/public/catalog.php" class="btn btn-ghost">Continuer mes achats</a>
        </div>
    </div>

</div>

<div class="toast" id="toast"></div>
    <script>
        const API      = '/LibraireWeb/api/index.php';
        const TOKEN    = '<?= $_SESSION['token'] ?? '' ?>';
        let cartData   = []; // [{id, title, qty, prix, image, stock}]

        async function init() {
            const raw = JSON.parse(localStorage.getItem('cart') ?? '[]');
            if (!raw.length) { showEmpty(); return; }

            // Fetch fresh book data for each cart item
            const results = await Promise.all(
                raw.map(item =>
                    fetch(`${API}?path=books/${item.id}`)
                        .then(r => r.json())
                        .then(book => ({ ...item, prix: book.prix, image: book.image, stock: book.stock, titre: book.titre || item.title }))
                        .catch(() => null)
                )
            );

            cartData = results.filter(Boolean);
            renderCart();
        }

        function renderCart() {
            if (!cartData.length) { showEmpty(); return; }

            const list = document.getElementById('cartItemsList');
            list.innerHTML = '';

            cartData.forEach((item, idx) => {
                const div = document.createElement('div');
                div.className = 'cart-item';
                div.id = `item-${item.id}`;
                const lineTotal = (item.qty * parseFloat(item.prix)).toFixed(2);

                div.innerHTML = `
                    <div class="item-cover" data-color="${item.id % 6}">
                        ${item.image
                            ? `<img src="${item.image}" onerror="this.style.display='none'" alt="">`
                            : `<span class="item-cover-label">${esc(item.titre)}</span>`}
                    </div>
                    <div class="item-info">
                        <a class="item-title" href="/LibraireWeb/public/book.php?id=${item.id}">${esc(item.titre)}</a>
                        <div class="item-controls">
                            <div class="qty-ctrl">
                                <button class="qty-btn" onclick="changeQty(${item.id}, -1)">−</button>
                                <span class="qty-num" id="qty-${item.id}">${item.qty}</span>
                                <button class="qty-btn" onclick="changeQty(${item.id}, 1)">+</button>
                            </div>
                            <button class="btn-remove" onclick="removeItem(${item.id})">✕ Retirer</button>
                        </div>
                    </div>
                    <div class="item-price-col">
                        <div class="item-unit-price">${parseFloat(item.prix).toFixed(2)} DT / u</div>
                        <div class="item-total-price" id="linetotal-${item.id}">${lineTotal} DT</div>
                    </div>`;
                list.appendChild(div);
            });

            document.getElementById('clearRow').style.display = 'block';
            renderSummary();
            updateBadge();
        }

        function renderSummary() {
            const lines = document.getElementById('summaryLines');
            lines.innerHTML = '';
            let total = 0;

            cartData.forEach(item => {
                const lt = item.qty * parseFloat(item.prix);
                total += lt;
                const div = document.createElement('div');
                div.className = 'summary-line';
                div.innerHTML = `
                    <span class="line-title">${esc(item.titre)} ×${item.qty}</span>
                    <span class="line-price">${lt.toFixed(2)} DT</span>`;
                lines.appendChild(div);
            });

            document.getElementById('summaryTotal').textContent = total.toFixed(2) + ' DT';

            // Delivery message
            const msg = document.getElementById('deliveryMsg');
            if (total >= 30) {
                msg.textContent = '✅ Livraison gratuite incluse !';
                msg.style.color = 'var(--color-success)';
            } else {
                const remaining = (30 - total).toFixed(2);
                msg.textContent = `🚚 Plus que ${remaining} DT pour la livraison gratuite`;
            }
        }

        function changeQty(id, delta) {
            const item = cartData.find(i => i.id === id);
            if (!item) return;
            const newQty = Math.max(1, Math.min(item.stock ?? 99, item.qty + delta));
            item.qty = newQty;
            document.getElementById(`qty-${id}`).textContent = newQty;
            document.getElementById(`linetotal-${id}`).textContent =
                (newQty * parseFloat(item.prix)).toFixed(2) + ' DT';
            saveCart();
            renderSummary();
            updateBadge();
        }

        function removeItem(id) {
            cartData = cartData.filter(i => i.id !== id);
            const el = document.getElementById(`item-${id}`);
            if (el) { el.style.opacity = '0'; el.style.transform = 'translateX(20px)'; el.style.transition = '0.25s'; setTimeout(() => renderCart(), 250); }
            saveCart();
        }

        function clearCart() {
            cartData = [];
            localStorage.removeItem('cart');
            updateCartCount();
            showEmpty();
        }

        function saveCart() {
            const simplified = cartData.map(i => ({ id: i.id, title: i.titre, qty: i.qty }));
            localStorage.setItem('cart', JSON.stringify(simplified));
            updateCartCount();
        }

        function showEmpty() {
            document.getElementById('cartItemsList').innerHTML = '';
            document.getElementById('clearRow').style.display = 'none';
            document.getElementById('cartEmpty').style.display = 'block';
            document.getElementById('summaryCol').style.display = 'none';
            updateBadge();
        }

        function updateBadge() {
            const total = cartData.reduce((s, i) => s + i.qty, 0);
            document.getElementById('cartBadge').textContent = `${total} article${total !== 1 ? 's' : ''}`;
        }

        // ── Place order ──
        async function placeOrder() {
            if (!cartData.length) return;

            if (!TOKEN) {
                showToast('❌ Session expirée. Reconnectez-vous.');
                setTimeout(() => window.location.href = '/LibraireWeb/public/login.php', 1500);
                return;
            }

            const btn = document.getElementById('btnOrder');
            document.getElementById('orderBtnText').style.display    = 'none';
            document.getElementById('orderBtnSpinner').style.display = 'inline';
            btn.disabled = true;

            const items = cartData.map(i => ({ book_id: i.id, quantite: i.qty }));

            try {
                const res  = await fetch(`${API}?path=orders`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${TOKEN}`
                    },
                    body: JSON.stringify({ items })
                });

                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(parseErr) {
                    console.error('API non-JSON response:', text.substring(0, 400));
                    showToast('❌ Erreur serveur ' + res.status + '. Voir console F12.');
                    document.getElementById('orderBtnText').style.display    = 'inline';
                    document.getElementById('orderBtnSpinner').style.display = 'none';
                    btn.disabled = false;
                    return;
                }

                if (data.error) {
                    showToast(`❌ ${data.error}`);
                    document.getElementById('orderBtnText').style.display    = 'inline';
                    document.getElementById('orderBtnSpinner').style.display = 'none';
                    btn.disabled = false;
                    return;
                }

                // Success
                localStorage.removeItem('cart');
                updateCartCount();
                document.getElementById('cartLayout').style.display   = 'none';
                document.getElementById('orderSuccess').style.display = 'block';

            } catch(e) {
                console.error('placeOrder error:', e);
                showToast('❌ ' + e.message);
                document.getElementById('orderBtnText').style.display    = 'inline';
                document.getElementById('orderBtnSpinner').style.display = 'none';
                btn.disabled = false;
            }
        }

        function updateCartCount() {
            const total = JSON.parse(localStorage.getItem('cart') ?? '[]').reduce((s,i) => s + i.qty, 0);
            const badge = document.getElementById('cartCount');
            if (badge) badge.textContent = total;
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        function esc(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }

        updateCartCount();
        init();
    </script>

<?php require_once '../includes/footer.php'; ?>