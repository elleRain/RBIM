<?php
require_once __DIR__ . '/login.php';
rbim_start_session();

try {
    rbim_seed_municipality_admins(db());
} catch (Throwable $e) {
    // Login page should still load if seeding fails.
}

if (is_logged_in()) {
    redirect(dashboard_path_for_role(current_user()['role']));
}

$loginError = $_SESSION['login_error'] ?? null;
$loginUsername = $_SESSION['login_username'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RBIM — Registry of Barangay Inhabitants and Migrants</title>
  <meta name="description" content="Sign in to the Registry of Barangay Inhabitants and Migrants (RBIM) — Commission on Population and Development – Negros Island Region.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy: #003399;
      --navy-deep: #002266;
      --navy-mid: #1a4db3;
      --navy-soft: #4d7acc;
      --ink: #0f172a;
      --muted: #64748b;
      --line: #e2e8f0;
      --panel: #f1f5f9;
      --white: #ffffff;
      --danger: #ef4136;
      --accent: #fbb040;
      --font: "Plus Jakarta Sans", sans-serif;
      --radius: 12px;
      --shadow: 0 10px 40px rgba(15, 23, 42, 0.1);
      --ease: cubic-bezier(0.22, 1, 0.36, 1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body { height: 100%; }

    body {
      font-family: var(--font);
      color: var(--ink);
      background: var(--panel);
      -webkit-font-smoothing: antialiased;
      line-height: 1.5;
    }

    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    .auth-layout {
      display: grid;
      grid-template-columns: minmax(0, 1.65fr) minmax(320px, 0.9fr);
      min-height: 100vh;
      min-height: 100dvh;
    }

    /* —— Left panel —— */
    .brand-panel {
      position: relative;
      background: var(--navy);
      color: var(--white);
      padding: 2.25rem 3rem 2.75rem;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .brand-panel::before {
      content: "";
      position: absolute;
      width: min(70vw, 560px);
      height: min(70vw, 560px);
      border-radius: 50%;
      background: radial-gradient(circle, rgba(77, 122, 204, 0.45) 0%, rgba(0, 51, 153, 0) 70%);
      top: -12%;
      right: -8%;
      pointer-events: none;
    }

    .brand-panel::after {
      content: "";
      position: absolute;
      width: 320px;
      height: 320px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.04);
      bottom: -80px;
      left: -60px;
      pointer-events: none;
    }

    .brand-mark {
      position: relative;
      z-index: 1;
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: auto;
      opacity: 0;
      animation: fade-up 0.7s var(--ease) 0.1s forwards;
    }

    .brand-mark img {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      object-fit: cover;
      background: var(--white);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .brand-mark span {
      font-size: 1.05rem;
      font-weight: 700;
      letter-spacing: -0.01em;
    }

    .brand-copy {
      position: relative;
      z-index: 1;
      max-width: 36rem;
      margin-top: 3.5rem;
      opacity: 0;
      animation: fade-up 0.75s var(--ease) 0.25s forwards;
    }

    .brand-copy h1 {
      font-size: clamp(1.85rem, 3.2vw, 2.65rem);
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -0.03em;
      margin-bottom: 1rem;
    }

    .brand-copy .agency {
      font-size: clamp(1rem, 1.6vw, 1.2rem);
      font-weight: 600;
      color: rgba(255, 255, 255, 0.92);
      margin-bottom: 1.15rem;
    }

    .brand-copy .lead {
      font-size: 0.975rem;
      font-weight: 400;
      color: rgba(255, 255, 255, 0.72);
      max-width: 32rem;
      margin-bottom: 2.5rem;
    }

    .feature-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 0.9rem;
      margin-top: auto;
      padding-top: 2rem;
      position: relative;
      z-index: 1;
      opacity: 0;
      animation: fade-up 0.75s var(--ease) 0.4s forwards;
    }

    .feature-list li {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.95rem;
      font-weight: 500;
      color: rgba(255, 255, 255, 0.92);
    }

    .feature-icon {
      flex-shrink: 0;
      width: 28px;
      height: 28px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.14);
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .feature-icon svg {
      width: 14px;
      height: 14px;
      stroke: #fff;
      fill: none;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* —— Right panel —— */
    .login-panel {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1.5rem;
      background: var(--panel);
    }

    .login-card {
      width: 100%;
      max-width: 400px;
      background: var(--white);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 2.25rem 2rem 2rem;
      opacity: 0;
      transform: translateY(16px);
      animation: fade-up 0.7s var(--ease) 0.2s forwards;
    }

    .login-logo {
      width: 72px;
      height: 72px;
      margin: 0 auto 1.25rem;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 4px 14px rgba(0, 51, 153, 0.12);
      background: var(--white);
    }

    .login-logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .login-card h2 {
      text-align: center;
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--navy);
      letter-spacing: -0.02em;
      margin-bottom: 0.4rem;
    }

    .login-card .subtitle {
      text-align: center;
      font-size: 0.875rem;
      color: var(--muted);
      margin-bottom: 1.75rem;
      line-height: 1.45;
    }

    .form-group {
      margin-bottom: 1.1rem;
    }

    .form-group label {
      display: block;
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--ink);
      margin-bottom: 0.4rem;
    }

    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-wrap .leading-icon,
    .input-wrap .trailing-btn {
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--muted);
      pointer-events: none;
    }

    .input-wrap .leading-icon {
      left: 0.85rem;
      width: 18px;
      height: 18px;
    }

    .input-wrap .leading-icon svg,
    .input-wrap .trailing-btn svg {
      width: 18px;
      height: 18px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .input-wrap .trailing-btn {
      right: 0.55rem;
      width: 34px;
      height: 34px;
      border: none;
      background: transparent;
      border-radius: 8px;
      cursor: pointer;
      pointer-events: auto;
      transition: background 0.2s, color 0.2s;
    }

    .input-wrap .trailing-btn:hover {
      background: var(--panel);
      color: var(--navy);
    }

    .input-wrap input {
      width: 100%;
      height: 46px;
      padding: 0 2.75rem 0 2.65rem;
      border: 1px solid var(--line);
      border-radius: 10px;
      font-family: var(--font);
      font-size: 0.9rem;
      color: var(--ink);
      background: var(--white);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-wrap input::placeholder { color: #94a3b8; }

    .input-wrap input:focus {
      border-color: var(--navy-soft);
      box-shadow: 0 0 0 3px rgba(0, 51, 153, 0.12);
    }

    .form-row {
      display: flex;
      justify-content: flex-end;
      margin: -0.25rem 0 1.25rem;
    }

    .forgot-link {
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--navy);
      transition: color 0.2s;
    }

    .forgot-link:hover { color: var(--navy-mid); text-decoration: underline; }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.55rem;
      width: 100%;
      height: 46px;
      border-radius: 10px;
      font-family: var(--font);
      font-size: 0.925rem;
      font-weight: 700;
      cursor: pointer;
      border: none;
      transition: transform 0.2s var(--ease), background 0.2s, box-shadow 0.2s, border-color 0.2s;
    }

    .btn:hover { transform: translateY(-1px); }
    .btn:active { transform: translateY(0); }

    .btn-primary {
      background: var(--navy);
      color: var(--white);
      box-shadow: 0 6px 16px rgba(0, 51, 153, 0.28);
    }

    .btn-primary:hover {
      background: var(--navy-deep);
      box-shadow: 0 8px 20px rgba(0, 51, 153, 0.34);
    }

    .divider {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      margin: 1.25rem 0;
      color: var(--muted);
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.06em;
    }

    .divider::before,
    .divider::after {
      content: "";
      flex: 1;
      height: 1px;
      background: var(--line);
    }

    .btn-google {
      background: var(--white);
      color: var(--ink);
      border: 1px solid var(--line);
      font-weight: 600;
    }

    .btn-google:hover {
      background: #f8fafc;
      border-color: #cbd5e1;
    }

    .btn-google svg { flex-shrink: 0; }

    .login-footer {
      margin-top: 1.5rem;
      text-align: center;
      font-size: 0.75rem;
      color: var(--muted);
    }

    .alert-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
      border-radius: 10px;
      padding: 0.75rem 0.9rem;
      font-size: 0.84rem;
      font-weight: 500;
      margin-bottom: 1.15rem;
      line-height: 1.4;
    }

    @keyframes fade-up {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 960px) {
      .auth-layout {
        grid-template-columns: 1fr;
      }

      .brand-panel {
        padding: 1.75rem 1.5rem 2rem;
        min-height: auto;
      }

      .brand-mark { margin-bottom: 2rem; }

      .brand-copy {
        margin-top: 0;
      }

      .brand-copy h1 {
        font-size: 1.65rem;
      }

      .feature-list {
        padding-top: 1.5rem;
      }

      .login-panel {
        padding: 1.5rem 1.25rem 2.5rem;
        align-items: flex-start;
      }

      .login-card {
        max-width: 440px;
      }
    }

    @media (max-width: 480px) {
      .login-card {
        padding: 1.75rem 1.25rem;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.08);
      }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
      }
      .brand-mark, .brand-copy, .feature-list, .login-card {
        opacity: 1;
        transform: none;
      }
    }
  </style>
</head>
<body>
  <div class="auth-layout">
    <aside class="brand-panel" aria-label="About RBIM">
      <div class="brand-mark">
        <img src="assets/img/system-logo.jpg" alt="" width="42" height="42">
        <span>RBIM System</span>
      </div>

      <div class="brand-copy">
        <h1>Registry of Barangay Inhabitants and Migrants (RBIM)</h1>
        <p class="agency">Commission on Population and Development – Negros Island Region</p>
        <p class="lead">
          A secure digital registry for accurate barangay population and migrant records—
          supporting local planning, service delivery, and evidence-based governance across CPD-NIR.
        </p>
      </div>

      <ul class="feature-list">
        <li>
          <span class="feature-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
          </span>
          Real-time Inhabitant &amp; Migrant Registry
        </li>
        <li>
          <span class="feature-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          Secure Access Control
        </li>
        <li>
          <span class="feature-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 3 5-6"/></svg>
          </span>
          Data-Driven Local Planning
        </li>
      </ul>
    </aside>

    <main class="login-panel">
      <div class="login-card">
        <div class="login-logo">
          <img src="assets/img/system-logo.jpg" alt="Commission on Population and Development logo" width="72" height="72">
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to access the CPD-NIR Registry of Barangay Inhabitants and Migrants.</p>

        <?php if ($loginError): ?>
          <div class="alert-error" role="alert"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <form action="login.php" method="post" autocomplete="on">
          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrap">
              <span class="leading-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </span>
              <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username" value="<?php echo htmlspecialchars($loginUsername); ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
              <span class="leading-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
              <button type="button" class="trailing-btn" id="toggle-password" aria-label="Show password">
                <svg id="eye-icon" viewBox="0 0 24 24">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="form-row">
            <a class="forgot-link" href="#">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <p class="login-footer">&copy; <?php echo date('Y'); ?> CPD – Negros Island Region</p>
      </div>
    </main>
  </div>

  <script>
    (function () {
      var toggle = document.getElementById('toggle-password');
      var input = document.getElementById('password');
      var eye = document.getElementById('eye-icon');
      if (!toggle || !input || !eye) return;

      toggle.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        eye.innerHTML = show
          ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
          : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
      });
    })();
  </script>
</body>
</html>
