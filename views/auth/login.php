<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AbasPOS — Iniciar Sesión</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo str_replace(' ','%20',APP_URL ?? ''); ?>/public/logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;}
        body {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 16px;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        /* En pantallas muy pequeñas: tarjeta sin bordes redondeados al fondo */
        @media (max-width: 400px) {
            body { padding: 0; align-items: flex-end; }
            .login-card { border-radius: 20px 20px 0 0; max-width: 100%; }
        }
        .login-header {
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            padding: 28px 24px 24px;
            text-align: center;
            color: #fff;
        }
        .login-header .brand-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,.2);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 10px;
        }
        .login-header h1 { font-size: 1.4rem; font-weight: 800; margin: 0 0 3px; }
        .login-header p  { font-size: .82rem; opacity: .8; margin: 0; }
        .login-body { padding: 22px 22px 20px; }
        .form-label { font-size: .82rem; font-weight: 600; color: #374151; }
        .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            /* 16px evita zoom automático en iOS */
            font-size: 16px;
            color: #1e293b;
            padding: .55rem .9rem;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
            color: #1e293b;
        }
            font-size: .9rem;
            color: #1e293b;
            padding: .55rem .9rem;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
            color: #1e293b;
        }
        .btn-login {
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            padding: .65rem;
            width: 100%;
            margin-top: 4px;
            transition: opacity .2s;
        }
        .btn-login:disabled { opacity: .65; cursor: not-allowed; }
        .btn-login:hover:not(:disabled) { opacity: .9; }
        .alert-login {
            border-radius: 8px;
            border: none;
            padding: 10px 14px;
            font-size: .85rem;
            margin-bottom: 16px;
        }
        .alert-danger-login  { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-success-login { background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e; }
        .demo-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-top: 20px;
            font-size: .8rem;
            color: #64748b;
        }
        .demo-box strong { color: #1e293b; }
        code { background: #f1f5f9; padding: 1px 5px; border-radius: 4px; font-size: .78rem; color: #7c3aed; }
        .eye-btn {
            cursor: pointer;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            background: none;
            border: none;
            padding: 2px;
        }
        .eye-btn:hover { color: #6366f1; }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Header -->
    <div class="login-header">
        <div class="brand-icon"><i class="fas fa-store"></i></div>
        <h1>AbasPOS</h1>
        <p>Sistema de Punto de Venta</p>
    </div>

    <!-- Body -->
    <div class="login-body">
        <div id="alertBox"></div>

        <form id="loginForm" autocomplete="on">
            <!-- Usuario -->
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="username" name="username"
                       placeholder="Ingresa tu usuario"
                       autocomplete="username" required>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <div class="position-relative">
                    <input type="password" class="form-control pe-5" id="password" name="password"
                           placeholder="Ingresa tu contraseña"
                           autocomplete="current-password" required>
                    <button type="button" class="eye-btn" onclick="togglePass()" id="eyeBtn">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Recordar -->
            <div class="mb-3 d-flex align-items-center gap-2">
                <input type="checkbox" class="form-check-input m-0" id="recuerdame" style="width:16px;height:16px;">
                <label for="recuerdame" class="form-label m-0" style="font-size:.8rem;cursor:pointer;">
                    Recordar mis datos
                </label>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <i class="fas fa-sign-in-alt me-2"></i>
                <span id="btnText">Iniciar Sesión</span>
            </button>
        </form>

        <!-- Credenciales de ejemplo -->
        <div class="demo-box">
            <div class="mb-1"><i class="fas fa-info-circle me-1"></i><strong>Credenciales de acceso:</strong></div>
            <div>Admin: <code>admin</code> / <code>password</code></div>
            <div>Cajero: <code>cajero1</code> / <code>password</code></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php
if (!defined('APP_URL')) {
    require_once __DIR__ . '/../../config/config.php';
}
// URL con espacios codificados — fetch() necesita URL válida
$APP_URL_JS = str_replace(' ', '%20', APP_URL);
?>
<script>
// URL con espacios codificados — fetch() necesita URL válida
const APP_URL    = '<?php echo APP_URL; ?>';
const API_BASE   = '<?php echo $APP_URL_JS; ?>';

// ── Helpers ─────────────────────────────────────────────────────────
function showAlert(msg, tipo) {
    const box = document.getElementById('alertBox');
    box.innerHTML = `<div class="alert-login alert-${tipo}-login">${msg}</div>`;
    box.scrollIntoView({behavior:'smooth', block:'nearest'});
}

function clearAlert() {
    document.getElementById('alertBox').innerHTML = '';
}

function togglePass() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}

// ── Recordar credenciales ─────────────────────────────────────────
function cargarGuardadas() {
    const saved = localStorage.getItem('abaspos_creds');
    if (saved) {
        try {
            const {u,p} = JSON.parse(saved);
            document.getElementById('username').value    = u || '';
            document.getElementById('password').value    = p || '';
            document.getElementById('recuerdame').checked = true;
        } catch(e) {}
    }
}

function guardarCreds(u, p) {
    if (document.getElementById('recuerdame').checked) {
        localStorage.setItem('abaspos_creds', JSON.stringify({u, p}));
    } else {
        localStorage.removeItem('abaspos_creds');
    }
}

// ── Submit ────────────────────────────────────────────────────────
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearAlert();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    if (!username || !password) {
        showAlert('<i class="fas fa-exclamation-circle me-1"></i>Ingresa usuario y contraseña', 'danger');
        return;
    }

    // Bloquear botón
    const btn     = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    btn.disabled  = true;
    btnText.textContent = 'Conectando...';

    try {
        // Usar API_BASE con %20 — URL válida para fetch
        const url      = API_BASE + '/api-login.php';
        const response = await fetch(url, {
            method:      'POST',
            headers:     {'Content-Type': 'application/json'},
            credentials: 'same-origin',
            body: JSON.stringify({username, password})
        });

        // Leer como texto primero para diagnóstico
        const text = await response.text();

        // Intentar parsear JSON
        let data;
        try {
            data = JSON.parse(text);
        } catch(parseErr) {
            console.error('Respuesta no es JSON:', text.substring(0, 500));
            showAlert('<i class="fas fa-exclamation-circle me-1"></i>Error del servidor. Revisa que Laragon esté activo.', 'danger');
            btn.disabled  = false;
            btnText.textContent = 'Iniciar Sesión';
            return;
        }

        if (response.ok && data.success) {
            guardarCreds(username, password);
            showAlert(`<i class="fas fa-check-circle me-1"></i>¡Bienvenido ${data.usuario.nombre_completo}!`, 'success');
            setTimeout(() => {
                window.location.href = APP_URL + '/dashboard';
            }, 900);
        } else if (response.status === 503) {
            showAlert('<i class="fas fa-database me-1"></i>La base de datos no está disponible aún. Espera unos segundos y reintenta.', 'danger');
            btn.disabled  = false;
            btnText.textContent = 'Iniciar Sesión';
        } else {
            showAlert(`<i class="fas fa-lock me-1"></i>${data.error || 'Usuario o contraseña incorrectos'}`, 'danger');
            btn.disabled  = false;
            btnText.textContent = 'Iniciar Sesión';
        }

    } catch(networkErr) {
        console.error('Error de red:', networkErr);
        showAlert('<i class="fas fa-wifi me-1"></i>Error de conexión. Verifica que el servidor esté activo.', 'danger');
        btn.disabled  = false;
        btnText.textContent = 'Iniciar Sesión';
    }
});

// Cargar credenciales guardadas al iniciar
cargarGuardadas();
</script>
</body>
</html>
