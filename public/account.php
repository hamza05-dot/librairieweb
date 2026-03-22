<?php
$pageTitle = "Mon Compte";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /LibraireWeb/public/login.php?redirect=account.php');
    exit;
}

// ── Always regenerate token to guarantee DB match ──
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

require_once '../includes/header.php';
?>

<div class="main-content">

    <!-- Page header -->
    <div class="account-header">
        <div class="account-avatar">
            <?= strtoupper(substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1)) ?>
        </div>
        <div>
            <h1 class="account-name">
                Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?> 👋
            </h1>
            <p class="account-email"><?= htmlspecialchars($_SESSION['email']) ?></p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="account-tabs">
        <button class="tab-btn active" onclick="showTab('orders')" id="tab-orders">📦 Mes commandes</button>
        <button class="tab-btn" onclick="showTab('profile')" id="tab-profile">👤 Mon profil</button>
    </div>

    <!-- ══ ORDERS TAB ══ -->
    <div id="panel-orders">

        <div class="orders-loading" id="ordersLoading">
            <div class="spinner"></div>
            <p>Chargement de vos commandes…</p>
        </div>

        <div id="ordersList"></div>

        <div class="empty-orders" id="emptyOrders" style="display:none">
            <div style="font-size:3rem;margin-bottom:14px">📭</div>
            <h3>Aucune commande</h3>
            <p>Vous n'avez pas encore passé de commande.</p>
            <a href="/LibraireWeb/public/catalog.php" class="btn btn-primary" style="margin-top:12px">
                Parcourir le catalogue →
            </a>
        </div>
    </div>

    <!-- ══ PROFILE TAB ══ -->
    <div id="panel-profile" style="display:none">

        <div class="profile-card">
            <!-- ── Personal Info ── -->
            <h2 class="profile-section-title">Informations personnelles</h2>
            <div id="profileMsg"></div>

            <form id="profileForm" onsubmit="return false">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" id="pNom" value="<?= htmlspecialchars($_SESSION['nom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" id="pPrenom" value="<?= htmlspecialchars($_SESSION['prenom']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="pEmail" value="<?= htmlspecialchars($_SESSION['email']) ?>" required>
                </div>

                <hr style="border:none;border-top:1px solid var(--color-border);margin:20px 0">
                <p class="profile-section-sub">Informations supplémentaires</p>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date de naissance</label>
                        <input type="date" id="pDateNaissance">
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="tel" id="pTelephone" placeholder="Ex : +216 20 000 000">
                    </div>
                </div>
                <div class="form-group">
                    <label>Gouvernorat / Ville</label>
                    <select id="pVille">
                        <option value="">— Choisir —</option>
                        <option value="Ariana">Ariana</option>
                        <option value="Béja">Béja</option>
                        <option value="Ben Arous">Ben Arous</option>
                        <option value="Bizerte">Bizerte</option>
                        <option value="Gabès">Gabès</option>
                        <option value="Gafsa">Gafsa</option>
                        <option value="Jendouba">Jendouba</option>
                        <option value="Kairouan">Kairouan</option>
                        <option value="Kasserine">Kasserine</option>
                        <option value="Kébili">Kébili</option>
                        <option value="La Manouba">La Manouba</option>
                        <option value="Le Kef">Le Kef</option>
                        <option value="Mahdia">Mahdia</option>
                        <option value="Médenine">Médenine</option>
                        <option value="Monastir">Monastir</option>
                        <option value="Nabeul">Nabeul</option>
                        <option value="Sfax">Sfax</option>
                        <option value="Sidi Bouzid">Sidi Bouzid</option>
                        <option value="Siliana">Siliana</option>
                        <option value="Sousse">Sousse</option>
                        <option value="Tataouine">Tataouine</option>
                        <option value="Tozeur">Tozeur</option>
                        <option value="Tunis">Tunis</option>
                        <option value="Zaghouan">Zaghouan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Adresse</label>
                    <textarea id="pAdresse" rows="3" placeholder="Rue, numéro, code postal…"
                              style="width:100%;resize:vertical;padding:10px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-family:var(--font-body);font-size:0.9rem"></textarea>
                </div>

                <button type="button" class="btn btn-primary" onclick="updateProfile()">
                    Enregistrer les modifications
                </button>
            </form>

            <hr style="border:none;border-top:1px solid var(--color-border);margin:28px 0">

            <!-- ── Password Change ── -->
            <h2 class="profile-section-title">Changer le mot de passe</h2>
            <div id="passMsg"></div>

            <div class="pass-rules">
                Le mot de passe doit contenir au moins :
                <ul>
                    <li id="rule-len">8 caractères</li>
                    <li id="rule-upper">1 lettre majuscule</li>
                    <li id="rule-digit">1 chiffre</li>
                    <li id="rule-special">1 caractère spécial (!@#$%…)</li>
                </ul>
            </div>

            <form id="passForm" onsubmit="return false">
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" id="pPass" placeholder="Minimum 8 caractères"
                           oninput="checkRules(this.value)">
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" id="pConfirm" placeholder="Répétez le mot de passe">
                </div>
                <button type="button" class="btn btn-primary" onclick="updatePassword()">
                    Changer le mot de passe
                </button>
                <div style="margin-top:12px;font-size:0.85rem;color:var(--color-text-muted)">
                    Mot de passe oublié ?
                    <a href="/LibraireWeb/public/forgot-password.php"
                       style="color:var(--color-primary-light);font-weight:600">Cliquez ici</a>
                </div>
            </form>
        </div>
    </div>

</div>

<div class="toast" id="toast"></div>

<style>
select {
    width: 100%; padding: 10px 12px;
    border: 1px solid var(--color-border); border-radius: var(--radius-md);
    font-family: var(--font-body); font-size: 0.9rem;
    background: white; color: var(--color-text); appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center; cursor: pointer;
}
select:focus { outline: none; border-color: var(--color-primary-light); box-shadow: 0 0 0 3px rgba(46,109,164,.15); }

.account-header { display: flex; align-items: center; gap: 20px; margin-bottom: 32px; }
.account-avatar {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-size: 1.4rem; color: white; font-weight: 700; flex-shrink: 0;
}
.account-name  { font-family: var(--font-display); font-size: 1.8rem; color: var(--color-primary); }
.account-email { font-family: var(--font-body); font-size: 0.9rem; color: var(--color-text-muted); margin-top: 2px; }

.account-tabs { display: flex; gap: 4px; border-bottom: 2px solid var(--color-border); margin-bottom: 28px; }
.tab-btn { padding: 10px 20px; background: none; border: none; font-family: var(--font-body); font-size: 0.92rem; font-weight: 500; color: var(--color-text-muted); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; }
.tab-btn:hover  { color: var(--color-primary); }
.tab-btn.active { color: var(--color-primary); border-bottom-color: var(--color-primary); font-weight: 700; }

.orders-loading { display: flex; align-items: center; gap: 14px; padding: 40px 0; color: var(--color-text-muted); font-family: var(--font-body); font-size: 0.9rem; }
.spinner { width: 28px; height: 28px; border: 3px solid var(--color-border); border-top-color: var(--color-primary-light); border-radius: 50%; animation: spin 0.8s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

.order-card { background: white; border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 16px; box-shadow: var(--shadow-sm); transition: box-shadow 0.2s; }
.order-card:hover { box-shadow: var(--shadow-md); }
.order-card-head { padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid var(--color-border); background: var(--color-bg); cursor: pointer; }
.order-num { font-family: var(--font-body); font-size: 0.82rem; color: var(--color-text-muted); }
.order-num strong { color: var(--color-text); }
.order-date { font-family: var(--font-body); font-size: 0.82rem; color: var(--color-text-muted); }
.order-status { font-family: var(--font-body); font-size: 0.76rem; font-weight: 700; padding: 4px 12px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.5px; }
.status-en-attente { background: #fef3c7; color: #92400e; }
.status-confirmee  { background: #dbeafe; color: #1e40af; }
.status-livree     { background: #dcfce7; color: #166534; }
.status-annulee    { background: #fee2e2; color: #991b1b; }
.order-total  { font-family: var(--font-display); font-size: 1.05rem; font-weight: 700; color: var(--color-primary); }
.order-toggle { font-size: 0.8rem; color: var(--color-text-muted); transition: transform 0.2s; display: inline-block; }
.order-toggle.open { transform: rotate(180deg); }
.order-items      { display: none; padding: 16px 20px; }
.order-items.open { display: block; }
.order-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--color-border); }
.order-item:last-child { border-bottom: none; }
.order-item-img { width: 44px; height: 58px; border-radius: 6px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; text-align: center; }
.order-item-img[data-color="0"] { background: linear-gradient(145deg, #1A3C5E, #2E6DA4); }
.order-item-img[data-color="1"] { background: linear-gradient(145deg, #2c3e50, #3498db); }
.order-item-img[data-color="2"] { background: linear-gradient(145deg, #6c3483, #a569bd); }
.order-item-img[data-color="3"] { background: linear-gradient(145deg, #1e8449, #52be80); }
.order-item-img[data-color="4"] { background: linear-gradient(145deg, #784212, #d68910); }
.order-item-img[data-color="5"] { background: linear-gradient(145deg, #7b241c, #e74c3c); }
.order-item-img img { width:100%; height:100%; object-fit:cover; }
.order-item-info  { flex: 1; min-width: 0; }
.order-item-title { font-family: var(--font-display); font-size: 0.9rem; color: var(--color-primary); }
.order-item-meta  { font-family: var(--font-body); font-size: 0.78rem; color: var(--color-text-muted); }
.order-item-price { font-family: var(--font-display); font-size: 0.95rem; font-weight: 700; color: var(--color-primary); white-space: nowrap; }

.empty-orders { text-align: center; padding: 60px 20px; background: white; border-radius: var(--radius-lg); border: 1px solid var(--color-border); }
.empty-orders h3 { font-family: var(--font-display); font-size: 1.3rem; color: var(--color-primary); margin-bottom: 8px; }
.empty-orders p  { font-family: var(--font-body); color: var(--color-text-muted); }

.profile-card { background: white; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 28px; max-width: 620px; box-shadow: var(--shadow-sm); }
.profile-section-title { font-family: var(--font-display); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 20px; }
.profile-section-sub { font-family: var(--font-body); font-size: 0.85rem; color: var(--color-text-muted); font-weight: 600; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; }

.pass-rules { background: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; font-size: 0.84rem; color: var(--color-text-muted); margin-bottom: 16px; }
.pass-rules ul { margin: 6px 0 0 18px; }
.pass-rules li { margin-bottom: 3px; transition: color 0.2s; }
.pass-rules li.ok { color: #166534; font-weight: 600; }
.pass-rules li.ok::marker { content: "✅ "; }
</style>

<script>
const API   = '/LibraireWeb/api/index.php';
const TOKEN = '<?= $_SESSION['token'] ?>';
const UID   = <?= (int)$_SESSION['user_id'] ?>;

// ── Tab switching ──
function showTab(tab) {
    ['orders', 'profile'].forEach(t => {
        document.getElementById(`panel-${t}`).style.display = t === tab ? 'block' : 'none';
        document.getElementById(`tab-${t}`).classList.toggle('active', t === tab);
    });
    if (tab === 'profile') loadProfileData();
}

// ── Load current user data into form ──
async function loadProfileData() {
    try {
        const res  = await fetch(`${API}?path=users/${UID}`, {
            headers: { 'Authorization': `Bearer ${TOKEN}` }
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) {
            console.error('loadProfileData non-JSON:', text.substring(0, 300));
            return;
        }
        if (data.error) return;

        document.getElementById('pNom').value    = data.nom    ?? '';
        document.getElementById('pPrenom').value = data.prenom ?? '';
        document.getElementById('pEmail').value  = data.email  ?? '';
        if (data.date_naissance) document.getElementById('pDateNaissance').value = data.date_naissance;
        if (data.telephone)      document.getElementById('pTelephone').value     = data.telephone;
        if (data.ville)          document.getElementById('pVille').value         = data.ville;
        if (data.adresse)        document.getElementById('pAdresse').value       = data.adresse;
    } catch(e) {
        console.error('loadProfileData error:', e);
    }
}

// ── Load orders ──
async function loadOrders() {
    try {
        const res    = await fetch(`${API}?path=orders`, {
            headers: { 'Authorization': `Bearer ${TOKEN}` }
        });
        const text   = await res.text();
        let orders;
        try { orders = JSON.parse(text); } catch(e) {
            console.error('loadOrders non-JSON:', text.substring(0, 300));
            document.getElementById('ordersLoading').innerHTML =
                '<p style="color:var(--color-danger)">❌ Erreur serveur. Voir console F12.</p>';
            return;
        }

        document.getElementById('ordersLoading').style.display = 'none';

        if (!Array.isArray(orders) || !orders.length) {
            document.getElementById('emptyOrders').style.display = 'block';
            return;
        }
        const list = document.getElementById('ordersList');
        orders.forEach(order => list.appendChild(orderCard(order)));
    } catch(e) {
        console.error('loadOrders error:', e);
        document.getElementById('ordersLoading').innerHTML =
            `<p style="color:var(--color-danger)">❌ ${e.message}</p>`;
    }
}

function orderCard(order) {
    const div = document.createElement('div');
    div.className = 'order-card';
    const date = new Date(order.created_at).toLocaleDateString('fr-FR');
    const statusClass  = 'status-' + order.statut.replace(' ', '-');
    const statusLabels = {
        'en attente': '⏳ En attente', 'confirmee': '✅ Confirmée',
        'livree': '📦 Livrée', 'annulee': '❌ Annulée'
    };
    div.innerHTML = `
        <div class="order-card-head" onclick="toggleOrder(${order.id})">
            <div>
                <div class="order-num">Commande <strong>#${order.id}</strong></div>
                <div class="order-date">📅 ${date}</div>
            </div>
            <span class="order-status ${statusClass}">${statusLabels[order.statut] ?? order.statut}</span>
            <span class="order-total">${parseFloat(order.total).toFixed(2)} DT</span>
            <span class="order-toggle" id="toggle-${order.id}">▼</span>
        </div>
        <div class="order-items" id="items-${order.id}">
            <div style="text-align:center;padding:20px;color:var(--color-text-muted);font-size:0.85rem">Chargement…</div>
        </div>`;
    return div;
}

async function toggleOrder(id) {
    const panel  = document.getElementById(`items-${id}`);
    const toggle = document.getElementById(`toggle-${id}`);
    const isOpen = panel.classList.contains('open');
    panel.classList.toggle('open', !isOpen);
    toggle.classList.toggle('open', !isOpen);

    if (!isOpen && panel.dataset.loaded !== 'true') {
        panel.dataset.loaded = 'true';
        try {
            const res   = await fetch(`${API}?path=orders/${id}`, {
                headers: { 'Authorization': `Bearer ${TOKEN}` }
            });
            const order = await res.json();
            panel.innerHTML = '';
            (order.items ?? []).forEach(item => {
                const el = document.createElement('div');
                el.className = 'order-item';
                const bookId = item.book?.id ?? 0;
                el.innerHTML = `
                    <div class="order-item-img" data-color="${bookId % 6}">
                        ${item.book?.image ? `<img src="${item.book.image}" onerror="this.style.display='none'" alt="">` : '📖'}
                    </div>
                    <div class="order-item-info">
                        <div class="order-item-title">${esc(item.book?.titre ?? 'Livre supprimé')}</div>
                        <div class="order-item-meta">Qté : ${item.quantite} × ${parseFloat(item.prix_unit).toFixed(2)} DT</div>
                    </div>
                    <div class="order-item-price">${parseFloat(item.sous_total).toFixed(2)} DT</div>`;
                panel.appendChild(el);
            });
        } catch(e) {
            panel.innerHTML = '<p style="padding:12px;color:var(--color-danger)">Erreur de chargement.</p>';
        }
    }
}

// ── Update profile ──
async function updateProfile() {
    const nom    = document.getElementById('pNom').value.trim();
    const prenom = document.getElementById('pPrenom').value.trim();
    const email  = document.getElementById('pEmail').value.trim();
    const msg    = document.getElementById('profileMsg');

    if (!nom || !prenom || !email) {
        msg.innerHTML = '<div class="alert alert-error">❌ Nom, prénom et email sont obligatoires.</div>';
        return;
    }

    const payload = {
        nom, prenom, email,
        date_naissance : document.getElementById('pDateNaissance').value || null,
        telephone      : document.getElementById('pTelephone').value.trim() || null,
        ville          : document.getElementById('pVille').value || null,
        adresse        : document.getElementById('pAdresse').value.trim() || null,
    };

    try {
        const res  = await fetch(`${API}?path=users/${UID}`, {
            method : 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${TOKEN}` },
            body   : JSON.stringify(payload),
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(pe) {
            console.error('updateProfile server response:', text.substring(0, 400));
            msg.innerHTML = '<div class="alert alert-error">❌ Erreur serveur. Voir console F12.</div>';
            return;
        }
        if (data.error) {
            msg.innerHTML = `<div class="alert alert-error">❌ ${esc(data.error)}</div>`;
            return;
        }
        msg.innerHTML = '<div class="alert alert-success">✅ Profil mis à jour !</div>';
        setTimeout(() => msg.innerHTML = '', 3000);
    } catch(e) {
        console.error('updateProfile error:', e);
        msg.innerHTML = `<div class="alert alert-error">❌ ${e.message}</div>`;
    }
}

// ── Password rules live checker ──
function checkRules(val) {
    toggle('rule-len',     val.length >= 8);
    toggle('rule-upper',   /[A-Z]/.test(val));
    toggle('rule-digit',   /[0-9]/.test(val));
    toggle('rule-special', /[^a-zA-Z0-9]/.test(val));
}
function toggle(id, ok) {
    document.getElementById(id).classList.toggle('ok', ok);
}

function strongPassword(pass) {
    if (pass.length < 8)             return "Minimum 8 caractères.";
    if (!/[A-Z]/.test(pass))         return "Au moins une lettre majuscule.";
    if (!/[0-9]/.test(pass))         return "Au moins un chiffre.";
    if (!/[^a-zA-Z0-9]/.test(pass))  return "Au moins un caractère spécial (!@#$%…).";
    return null;
}

// ── Update password ──
async function updatePassword() {
    const passVal    = document.getElementById('pPass').value;
    const confirmVal = document.getElementById('pConfirm').value;
    const msg        = document.getElementById('passMsg');

    if (!passVal || !confirmVal) {
        msg.innerHTML = '<div class="alert alert-error">❌ Remplissez les deux champs.</div>';
        return;
    }
    const pwErr = strongPassword(passVal);
    if (pwErr) {
        msg.innerHTML = `<div class="alert alert-error">❌ ${pwErr}</div>`;
        return;
    }
    if (passVal !== confirmVal) {
        msg.innerHTML = '<div class="alert alert-error">❌ Les mots de passe ne correspondent pas.</div>';
        return;
    }

    try {
        const res  = await fetch(`${API}?path=users/${UID}`, {
            method : 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${TOKEN}` },
            body   : JSON.stringify({ password: passVal, confirm: confirmVal }),
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(pe) {
            console.error('updatePassword server response:', text.substring(0, 400));
            msg.innerHTML = '<div class="alert alert-error">❌ Erreur serveur. Voir console F12.</div>';
            return;
        }
        if (data.error) {
            msg.innerHTML = `<div class="alert alert-error">❌ ${esc(data.error)}</div>`;
            return;
        }
        msg.innerHTML = '<div class="alert alert-success">✅ Mot de passe changé avec succès !</div>';
        document.getElementById('pPass').value    = '';
        document.getElementById('pConfirm').value = '';
        checkRules('');
        setTimeout(() => msg.innerHTML = '', 4000);
    } catch(e) {
        console.error('updatePassword error:', e);
        msg.innerHTML = `<div class="alert alert-error">❌ ${e.message}</div>`;
    }
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function updateCartCount() {
    const total = JSON.parse(localStorage.getItem('cart') ?? '[]').reduce((s, i) => s + i.qty, 0);
    const badge = document.getElementById('cartCount');
    if (badge) badge.textContent = total;
}

updateCartCount();
loadOrders();
loadProfileData();
</script>

<?php require_once '../includes/footer.php'; ?>