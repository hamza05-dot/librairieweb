<?php
$pageTitle = "Mot de passe oublié";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="/LibraireWeb/assets/css/pages/auth.css">

<div class="auth-container">
<div class="auth-card" style="max-width:480px">

    <!-- STEP 1 – Email -->
    <div id="step1">
        <h2>🔑 Mot de passe oublié</h2>
        <p class="auth-subtitle">
            Entrez votre adresse email. Nous vous enverrons un code de vérification à 6 chiffres.
        </p>
        <div id="msg1"></div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" id="s1Email" placeholder="votre@email.com" autofocus>
        </div>
        <button class="btn btn-primary btn-full" onclick="sendCode()">Envoyer le code</button>
        <div class="auth-link"><a href="/LibraireWeb/public/login.php">← Retour à la connexion</a></div>
    </div>

    <!-- STEP 2 – Code -->
    <div id="step2" style="display:none">
        <h2>📨 Vérification</h2>
        <p class="auth-subtitle">
            Un code à 6 chiffres a été envoyé à <strong id="s2EmailDisplay"></strong>.
            Le code expire dans 15 minutes.
        </p>
        <div id="msg2"></div>

        <!-- DEV ONLY: show code returned by API -->
        <div id="devCode" style="display:none;background:#fef3c7;border:1px solid #d97706;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-family:monospace;font-size:1.1rem;text-align:center;color:#92400e">
            🛠️ Dev — Code : <strong id="devCodeVal"></strong>
        </div>

        <div class="form-group">
            <label>Code de vérification</label>
            <input type="text" id="s2Code" placeholder="000000" maxlength="6"
                   inputmode="numeric" pattern="[0-9]{6}"
                   style="letter-spacing:8px;font-size:1.4rem;text-align:center;font-weight:700">
        </div>
        <button class="btn btn-primary btn-full" onclick="verifyCode()">Vérifier le code</button>
        <div class="auth-link">
            Pas reçu ? <a href="#" onclick="goStep(1);return false">Réessayer</a>
        </div>
    </div>

    <!-- STEP 3 – New password -->
    <div id="step3" style="display:none">
        <h2>🔒 Nouveau mot de passe</h2>
        <p class="auth-subtitle">Choisissez un nouveau mot de passe sécurisé.</p>
        <div id="msg3"></div>
        <div class="pass-rules">
            Votre mot de passe doit contenir :
            <ul>
                <li id="r-len">8 caractères minimum</li>
                <li id="r-upper">1 lettre majuscule</li>
                <li id="r-digit">1 chiffre</li>
                <li id="r-special">1 caractère spécial (!@#$%…)</li>
            </ul>
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" id="s3Pass" placeholder="Nouveau mot de passe" oninput="liveCheck(this.value)">
        </div>
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" id="s3Confirm" placeholder="Répétez le mot de passe">
        </div>
        <button class="btn btn-primary btn-full" onclick="resetPassword()">Réinitialiser le mot de passe</button>
    </div>

    <!-- STEP 4 – Success -->
    <div id="step4" style="display:none;text-align:center">
        <div style="font-size:3rem;margin-bottom:16px">✅</div>
        <h2 style="color:var(--color-primary)">Mot de passe réinitialisé !</h2>
        <p style="color:var(--color-text-muted);margin:12px 0 24px">
            Votre mot de passe a été changé. Vous pouvez maintenant vous connecter.
        </p>
        <a href="/LibraireWeb/public/login.php" class="btn btn-primary">Se connecter →</a>
    </div>

</div>
</div>

<style>
.pass-rules { background:var(--color-bg); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:12px 16px; font-size:0.84rem; color:var(--color-text-muted); margin-bottom:16px; }
.pass-rules ul { margin:6px 0 0 18px; }
.pass-rules li { margin-bottom:3px; transition:color 0.2s; }
.pass-rules li.ok { color:#166534; font-weight:600; }
.pass-rules li.ok::marker { content:"✅ "; }
.btn.loading { opacity:0.6; pointer-events:none; }
</style>

<script>
const API = '/LibraireWeb/api/index.php';
let currentEmail = '';
let currentToken = '';

function goStep(n) {
    [1,2,3,4].forEach(i =>
        document.getElementById('step'+i).style.display = i === n ? 'block' : 'none'
    );
}

async function sendCode() {
    const email = document.getElementById('s1Email').value.trim();
    const msg   = document.getElementById('msg1');
    if (!email || !email.includes('@')) {
        msg.innerHTML = '<div class="alert alert-error">❌ Entrez un email valide.</div>';
        return;
    }
    setLoading('step1', true);
    try {
        const res  = await fetch(`${API}?path=auth/forgot-password`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ email })
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) {
            console.error(text);
            msg.innerHTML = '<div class="alert alert-error">❌ Erreur serveur.</div>';
            return;
        }
        if (data.error) { msg.innerHTML = `<div class="alert alert-error">❌ ${esc(data.error)}</div>`; return; }

        currentEmail = email;
        document.getElementById('s2EmailDisplay').textContent = email;

        // Show dev code if present
        if (data.dev_code) {
            document.getElementById('devCode').style.display = 'block';
            document.getElementById('devCodeVal').textContent = data.dev_code;
        }

        goStep(2);
    } catch(e) {
        msg.innerHTML = `<div class="alert alert-error">❌ ${e.message}</div>`;
    } finally { setLoading('step1', false); }
}

async function verifyCode() {
    const code = document.getElementById('s2Code').value.trim();
    const msg  = document.getElementById('msg2');
    if (!/^\d{6}$/.test(code)) {
        msg.innerHTML = '<div class="alert alert-error">❌ Le code doit être composé de 6 chiffres.</div>';
        return;
    }
    setLoading('step2', true);
    try {
        const res  = await fetch(`${API}?path=auth/verify-reset`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ email: currentEmail, code })
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) {
            console.error(text);
            msg.innerHTML = '<div class="alert alert-error">❌ Erreur serveur.</div>';
            return;
        }
        if (data.error) { msg.innerHTML = `<div class="alert alert-error">❌ ${esc(data.error)}</div>`; return; }
        currentToken = data.reset_token;
        goStep(3);
    } catch(e) {
        msg.innerHTML = `<div class="alert alert-error">❌ ${e.message}</div>`;
    } finally { setLoading('step2', false); }
}

async function resetPassword() {
    const pass    = document.getElementById('s3Pass').value;
    const confirm = document.getElementById('s3Confirm').value;
    const msg     = document.getElementById('msg3');
    const err = strongPassword(pass);
    if (err) { msg.innerHTML = `<div class="alert alert-error">❌ ${err}</div>`; return; }
    if (pass !== confirm) { msg.innerHTML = '<div class="alert alert-error">❌ Les mots de passe ne correspondent pas.</div>'; return; }

    setLoading('step3', true);
    try {
        const res  = await fetch(`${API}?path=auth/reset-password`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ reset_token: currentToken, password: pass, password_confirm: confirm })
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) {
            console.error(text);
            msg.innerHTML = '<div class="alert alert-error">❌ Erreur serveur.</div>';
            return;
        }
        if (data.error) { msg.innerHTML = `<div class="alert alert-error">❌ ${esc(data.error)}</div>`; return; }
        goStep(4);
    } catch(e) {
        msg.innerHTML = `<div class="alert alert-error">❌ ${e.message}</div>`;
    } finally { setLoading('step3', false); }
}

function strongPassword(pass) {
    if (pass.length < 8)            return "Au moins 8 caractères.";
    if (!/[A-Z]/.test(pass))        return "Au moins une lettre majuscule.";
    if (!/[0-9]/.test(pass))        return "Au moins un chiffre.";
    if (!/[^a-zA-Z0-9]/.test(pass)) return "Au moins un caractère spécial (!@#$%…).";
    return null;
}

function liveCheck(val) {
    const mark = (id, ok) => document.getElementById(id).classList.toggle('ok', ok);
    mark('r-len',     val.length >= 8);
    mark('r-upper',   /[A-Z]/.test(val));
    mark('r-digit',   /[0-9]/.test(val));
    mark('r-special', /[^a-zA-Z0-9]/.test(val));
}

function setLoading(stepId, loading) {
    const btn = document.querySelector(`#${stepId} .btn`);
    if (btn) btn.classList.toggle('loading', loading);
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}
</script>

<?php require_once '../includes/footer.php'; ?>