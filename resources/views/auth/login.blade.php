<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Diamond World HRM</title>
<link rel="icon" type="image/png" href="/image/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  display: flex;
  background: #ffffff;
}

/* ── LEFT PANEL ───────────────────────────────────────────────── */
.login-left {
  flex: 1;
  background: #f8f9fa;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 50px;
  position: relative;
  overflow: hidden;
  min-height: 100vh;
  border-right: 1px solid #e9ecef;
}

.login-left::before {
  content: '';
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(0,0,0,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,0,0,0.04) 1px, transparent 1px);
  background-size: 44px 44px;
}

.left-content {
  position: relative; z-index: 1;
  text-align: center;
  max-width: 400px;
  width: 100%;
}

/* Logo box — subtle border on white bg */
.left-logo-box {
  display: inline-block;
  background: #ffffff;
  border-radius: 16px;
  padding: 18px 28px;
  margin-bottom: 32px;
  box-shadow: 0 2px 20px rgba(0,0,0,0.08);
  border: 1px solid #e2e8f0;
}
.left-logo-box img {
  height: 70px;
  object-fit: contain;
  display: block;
}

.left-divider {
  width: 48px; height: 2px;
  background: rgba(0,0,0,0.12);
  margin: 0 auto 24px;
}

.left-title {
  font-size: 26px; font-weight: 800;
  color: #0a0a0a; line-height: 1.3;
  letter-spacing: -0.5px; margin-bottom: 10px;
}
.left-subtitle {
  font-size: 13.5px; color: #64748b;
  line-height: 1.7; margin-bottom: 36px;
}

.company-info {
  margin-top: 28px;
  text-align: left;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px 20px;
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}
.company-name {
  font-size: 17px; font-weight: 800;
  color: #0a0a0a; margin-bottom: 10px;
  letter-spacing: -0.3px;
}
.company-detail {
  display: flex; align-items: flex-start; gap: 8px;
  font-size: 13px; color: #475569;
  margin-bottom: 7px; line-height: 1.5;
}
.company-detail i { margin-top: 2px; flex-shrink: 0; color: #94a3b8; }
.company-detail:last-child { margin-bottom: 0; }

.left-bottom {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  padding: 20px 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #e9ecef;
}
.left-bottom .copy {
  font-size: 11px;
  color: #94a3b8;
}
.dev-credit {
  display: flex; align-items: center; gap: 8px;
  text-decoration: none;
}
.dev-credit img {
  height: 82px; object-fit: contain;
  opacity: 0.7;
  transition: opacity 0.2s;
}
.dev-credit:hover img { opacity: 1; }

/* ── RIGHT PANEL ──────────────────────────────────────────────── */
.login-right {
  width: 480px; flex-shrink: 0;
  background: #ffffff;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 60px 50px;
  position: relative;
  box-shadow: -20px 0 60px rgba(0,0,0,0.06);
}
.right-content { width: 100%; max-width: 360px; }

.right-title {
  font-size: 26px; font-weight: 800;
  color: #0a0a0a; margin-bottom: 6px;
  letter-spacing: -0.5px;
}
.right-subtitle {
  font-size: 13px; color: #94a3b8; margin-bottom: 30px;
}

.form-group { margin-bottom: 18px; }
.form-label {
  display: block; font-size: 11px; font-weight: 700;
  color: #475569; margin-bottom: 7px;
  letter-spacing: 0.8px; text-transform: uppercase;
}
.input-wrap { position: relative; }
.input-icon {
  position: absolute; left: 13px; top: 50%;
  transform: translateY(-50%); color: #94a3b8;
  font-size: 15px; pointer-events: none;
}
.form-control {
  width: 100%; padding: 12px 13px 12px 40px;
  background: #f8fafc; border: 1.5px solid #e2e8f0;
  border-radius: 10px; font-size: 14px;
  font-family: inherit; color: #0f172a;
  transition: all 0.2s; outline: none;
}
.form-control:focus {
  background: #fff; border-color: #0a0a0a;
  box-shadow: 0 0 0 3px rgba(0,0,0,0.06);
}
.form-control::placeholder { color: #cbd5e1; }

.toggle-pass {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%); background: none;
  border: none; color: #94a3b8; cursor: pointer; font-size: 15px;
}
.toggle-pass:hover { color: #0a0a0a; }

.form-row {
  display: flex; align-items: center; margin-bottom: 22px;
}
.form-check { display: flex; align-items: center; gap: 8px; }
.form-check input { width: 15px; height: 15px; accent-color: #0a0a0a; cursor: pointer; }
.form-check label { font-size: 13px; color: #64748b; cursor: pointer; font-weight: 500; }

.btn-login {
  width: 100%; padding: 13px;
  background: #0a0a0a; border: none; border-radius: 10px;
  color: #fff; font-size: 14px; font-weight: 700;
  font-family: inherit; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all 0.2s; letter-spacing: 0.3px;
}
.btn-login:hover {
  background: #1a1a1a; transform: translateY(-1px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
.btn-login:active { transform: scale(0.99); }

.alert-error {
  background: #fef2f2; border: 1px solid #fecaca;
  border-radius: 8px; padding: 11px 14px;
  color: #b91c1c; font-size: 13px;
  margin-bottom: 18px;
  display: flex; align-items: center; gap: 8px;
}

.right-bottom {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 18px 30px;
  border-top: 1px solid #f1f5f9;
  display: flex; align-items: center; justify-content: center;
}
.right-bottom span { font-size: 11px; color: #000000; }

/* ── RESPONSIVE ───────────────────────────────────────────────── */
@media (max-width: 900px) {
  body { flex-direction: column; }
  .login-left {
    min-height: auto; padding: 40px 30px 60px;
    flex: 0 0 auto;
  }
  .left-features { display: none; }
  .left-logo-box { margin-bottom: 16px; }
  .left-logo-box img { height: 52px; }
  .left-title { font-size: 20px; }
  .left-subtitle { font-size: 13px; margin-bottom: 0; }
  .left-bottom { position: relative; border-top: none; justify-content: center; padding: 16px 20px 0; }
  .login-right { width: 100%; box-shadow: none; padding: 40px 28px 60px; }
}
@media (max-width: 480px) {
  .login-left { padding: 32px 20px 40px; }
  .login-right { padding: 32px 20px 50px; }
  .right-title { font-size: 22px; }
  .left-logo-box { padding: 12px 20px; }
  .left-logo-box img { height: 44px; }
}
</style>
</head>
<body>

{{-- ── LEFT PANEL ── --}}
<div class="login-left">
  <div class="left-content">

    <div class="left-logo-box">
      <img src="/image/dw_logo_500px_x_300px.png" alt="Diamond World">
    </div>

    <div class="left-divider"></div>

    <h1 class="left-title">Human Resource<br>Management System</h1>
    <p class="left-subtitle">Streamline your workforce — attendance,<br>payroll, leaves & more, all in one place.</p>

    @php
      $companyName    = \App\Models\Setting::get('company_name', 'Diamond World');
      $companyAddress = \App\Models\Setting::get('company_address', '');
      $companyPhone   = \App\Models\Setting::get('company_phone', '');
      $companyEmail   = \App\Models\Setting::get('company_email', '');
    @endphp
    <div class="company-info">
      <div class="company-name">{{ $companyName }}</div>
      @if($companyAddress)
        <div class="company-detail"><i class="bi bi-geo-alt"></i> {{ $companyAddress }}</div>
      @endif
      @if($companyPhone)
        <div class="company-detail"><i class="bi bi-telephone"></i> {{ $companyPhone }}</div>
      @endif
      @if($companyEmail)
        <div class="company-detail"><i class="bi bi-envelope"></i> {{ $companyEmail }}</div>
      @endif
    </div>

  </div>

  <div class="left-bottom">
    <span class="copy">&copy; {{ date('Y') }} Diamond World. All rights reserved.</span>
    <a href="https://mysoftheaven.com/" target="_blank" class="dev-credit" title="Developed by MySoftHeaven">
      <img src="/image/mysoft-logo.png" alt="MySoftHeaven">
    </a>
  </div>
</div>

{{-- ── RIGHT PANEL ── --}}
<div class="login-right">
  <div class="right-content">

    <h2 class="right-title">Welcome Back!</h2>
    <p class="right-subtitle">Please sign in to continue accessing the panel.</p>

    @if($errors->any())
      <div class="alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
      @csrf

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="input-wrap">
          <i class="bi bi-envelope input-icon"></i>
          <input type="email" name="email" class="form-control"
            placeholder="admin@hrm.com" value="{{ old('email') }}" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="bi bi-lock input-icon"></i>
          <input type="password" name="password" id="passwordInput"
            class="form-control" placeholder="••••••••" required>
          <button type="button" class="toggle-pass" onclick="togglePass()">
            <i class="bi bi-eye" id="passEye"></i>
          </button>
        </div>
      </div>

      <div class="form-row">
        <div class="form-check">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">Remember Me</label>
        </div>
      </div>

      <button type="submit" class="btn-login">
        Sign In <i class="bi bi-arrow-right"></i>
      </button>
    </form>
  </div>

  <div class="right-bottom">
    <span>Developed by <a href="https://mysoftheaven.com/" target="_blank" style="color:#0a0a0a;font-weight:600;text-decoration:none;">MySoftHeaven</a> &nbsp;|&nbsp; &copy; {{ date('Y') }} Diamond World HRM</span>
  </div>
</div>

<script>
function togglePass() {
  const inp = document.getElementById('passwordInput');
  const eye = document.getElementById('passEye');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
