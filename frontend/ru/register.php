<?php
// /frontend/register.php
// Генерация CSRF (double-submit cookie)
function new_csrf_token(): string {
    return bin2hex(random_bytes(32));
}
$csrf = new_csrf_token();
// HttpOnly для CSRF не ставим, чтобы фронт мог отправить значение в теле — это именно double-submit cookie.
setcookie('csrf_token', $csrf, [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => false,
    'samesite' => 'Lax',
]);
// Базовые security-заголовки для страницы
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
// CSP оставим упрощённой, т.к. используем inline JS в MVP
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; base-uri 'self'; form-action 'self';");
?>
<!DOCTYPE html>
<html lang="ru" data-theme="">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Регистрация — Онлайн Академия</title>
<style>
    :root { --bg-color:#ffffff; --text-color:#000000; --card-bg:#f0f0f0; --danger:#cc1526; --muted:#6b7280; }
    [data-theme="dark"] { --bg-color:#121212; --text-color:#ffffff; --card-bg:#1f1f1f; }
    body { margin:0; font-family:Arial, sans-serif; background:var(--bg-color); color:var(--text-color); transition:background-color .3s,color .3s; }
    header { display:flex; justify-content:space-between; align-items:center; padding:1rem; background:var(--card-bg); }
    .theme-toggle { display:flex; align-items:center; gap:.3rem; }
    .container { max-width: 560px; margin: 2rem auto; padding: 0 1rem; }
    .card { background:var(--card-bg); padding:1.25rem; border-radius:8px; }
    h2 { margin:0 0 1rem 0; }
    .field { margin-bottom:1rem; }
    .label { display:block; margin-bottom:.4rem; font-weight:bold; }
    .input { width:100%; padding:.7rem .8rem; border:1px solid #ccc; border-radius:6px; background:transparent; color:var(--text-color); }
    .hint { margin-top:.35rem; font-size:.9rem; color:var(--muted); }
    .error { margin-top:.35rem; font-size:.9rem; color:var(--danger); display:none; }
    .btn { width:100%; padding:.8rem 1rem; border:none; border-radius:6px; cursor:pointer; background:#007BFF; color:white; font-size:15px; }
    .btn:hover { background:#0056b3; }
    .top-error { color:var(--danger); margin-bottom:1rem; display:none; }
    .success { text-align:center; }
    .back { display:inline-block; margin-top:1rem; color:inherit; text-decoration:none; }
    /* фикс ширины */
    *, *::before, *::after { box-sizing: border-box; }

</style>
</head>
<body>
<header>
    <h1>Онлайн Академия</h1>
    <div class="theme-toggle">
        <label><input type="checkbox" id="themeSwitch"> 🌙</label>
    </div>
</header>

<div class="container">
  <div class="card">
    <h2>Регистрация</h2>
    <div id="topError" class="top-error"></div>

    <form id="regForm" novalidate>
      <input type="hidden" name="csrf" id="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>" />

      <div class="field">
        <label class="label" for="username">Имя пользователя</label>
        <input class="input" id="username" name="username" type="text"
               placeholder="3–30 символов: буквы (лат/кир), цифры, _ . -"
               autocomplete="username" inputmode="text" />
        <div id="usernameError" class="error"></div>
      </div>

      <div class="field">
        <label class="label" for="email">Email</label>
        <input class="input" id="email" name="email" type="email"
               placeholder="name@example.com" autocomplete="email" />
        <div id="emailError" class="error"></div>
      </div>

      <div class="field">
        <label class="label" for="password">Пароль</label>
        <input class="input" id="password" name="password" type="password"
               placeholder="8–64 символа, буквы и цифры, без пробелов"
               autocomplete="new-password" />
        <div id="passwordError" class="error"></div>
      </div>

      <button class="btn" type="submit" id="submitBtn">Зарегистрироваться</button>
    </form>

    <a class="back" href="/">← На главную</a>
  </div>
</div>

<footer style="background:var(--card-bg); padding:1rem; text-align:center; margin-top:3rem; font-size:.9rem;">
  2025 Онлайн Академия.
</footer>

<script>
// ===== Тема (как на главной) =====
function setCookie(name, value, days) {
  let expires = "";
  if (days) { const d = new Date(); d.setTime(d.getTime()+days*24*60*60*1000); expires = "; expires="+d.toUTCString(); }
  document.cookie = name+"="+(value||"")+expires+"; path=/";
}
function getCookie(name) {
  const nameEQ = name+"="; const ca = document.cookie.split(';');
  for (let i=0;i<ca.length;i++){ let c=ca[i]; while(c.charAt(0)==' ') c=c.substring(1); if(c.indexOf(nameEQ)==0) return c.substring(nameEQ.length); }
  return null;
}
const themeSwitch = document.getElementById('themeSwitch');
const savedTheme = getCookie("theme");
if (savedTheme) {
  document.documentElement.setAttribute('data-theme', savedTheme);
  themeSwitch.checked = (savedTheme === 'dark');
} else {
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const defaultTheme = prefersDark ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', defaultTheme);
  setCookie("theme", defaultTheme, 365);
  themeSwitch.checked = prefersDark;
}
themeSwitch.addEventListener('change', function() {
  const theme = this.checked ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', theme);
  setCookie("theme", theme, 365);
});

// ===== Клиентская валидация =====
const reUsername = /^[A-Za-zА-Яа-яЁё0-9._-]{3,30}$/u;
const reUsernameHasAlnum = /[A-Za-zА-Яа-яЁё0-9]/u; // не только спец-символы
const rePasswordLenSpace = /^[^\s]{8,64}$/u;
const rePasswordHasLetter = /[A-Za-zА-Яа-яЁё]/u;
const rePasswordHasDigit  = /[0-9]/;

function showError(id, msg) {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.style.display = msg ? 'block' : 'none';
}
function clearAllErrors() {
  showError('topError',''); showError('usernameError',''); showError('emailError',''); showError('passwordError','');
}

document.getElementById('regForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  clearAllErrors();
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;

  const username = document.getElementById('username').value.trim();
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const csrf     = document.getElementById('csrf').value;

  let hasError = false;

  // Username
  if (!reUsername.test(username) || !reUsernameHasAlnum.test(username)) {
    showError('usernameError', 'Имя может содержать буквы (лат/кир), цифры и символы _ . -, длина 3–30. Должен быть хоть один символ, кроме специальных.');
    hasError = true;
  }

  // Email: базовая проверка + наличие @ и .
  if (!email || !email.includes('@') || !email.includes('.')) {
    showError('emailError', 'Введите корректный email.');
    hasError = true;
  }

  // Password
  if (!rePasswordLenSpace.test(password) || !rePasswordHasLetter.test(password) || !rePasswordHasDigit.test(password)) {
    showError('passwordError', 'Пароль должен быть от 8 до 64 символов, без пробелов, содержать буквы и цифры.');
    hasError = true;
  }

  if (hasError) { btn.disabled = false; return; }

  try {
    const res = await fetch('/backend/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, email, password, csrf }),
      credentials: 'same-origin',
      keepalive: true
    });
    const data = await res.json();

    if (data?.ok) {
      // Редирект на страницу проверки почты
      window.location.href = '/ru/confirm_pending.php';
      return;
    }

    // Ошибки сервера
    if (data?.errors) {
      if (data.errors.username) showError('usernameError', data.errors.username);
      if (data.errors.email)    showError('emailError',    data.errors.email);
      if (data.errors.password) showError('passwordError', data.errors.password);
      if (data.errors.common)   { const t = document.getElementById('topError'); t.textContent = data.errors.common; t.style.display='block'; }
    } else {
      const t = document.getElementById('topError');
      t.textContent = 'Что-то пошло не так. Попробуйте позже.';
      t.style.display = 'block';
    }
  } catch (err) {
    const t = document.getElementById('topError');
    t.textContent = 'Сеть недоступна. Попробуйте позже.';
    t.style.display = 'block';
  } finally {
    btn.disabled = false;
  }
});
</script>
</body>
</html>
