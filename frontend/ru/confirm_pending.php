<?php
// /frontend/confirm_pending.php

// Генерация CSRF
function new_csrf_token(): string {
    return bin2hex(random_bytes(32));
}
$csrf = new_csrf_token();
setcookie('csrf_token', $csrf, [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => false, // double-submit cookie
    'samesite' => 'Lax',
]);

// Заголовки безопасности
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; base-uri 'self'; form-action 'self';");
?>
<!DOCTYPE html>
<html lang="ru" data-theme="">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Подтверждение почты — Онлайн Академия</title>
<style>
    :root { --bg-color:#ffffff; --text-color:#000000; --card-bg:#f0f0f0; --danger:#cc1526; --muted:#6b7280; --primary:#007BFF; }
    [data-theme="dark"] { --bg-color:#121212; --text-color:#ffffff; --card-bg:#1f1f1f; }
    body { margin:0; font-family:Arial, sans-serif; background:var(--bg-color); color:var(--text-color); transition:background-color .3s,color .3s; }
    header { display:flex; justify-content:space-between; align-items:center; padding:1rem; background:var(--card-bg); }
    .container { max-width: 560px; margin: 2rem auto; padding: 0 1rem; }
    .card { background:var(--card-bg); padding:1.25rem; border-radius:8px; text-align:center; }
    h2 { margin:0 0 1rem 0; }
    .btn { margin-top:1rem; padding:.8rem 1rem; border:none; border-radius:6px; cursor:pointer; background:var(--primary); color:white; font-size:15px; }
    .btn:disabled { background:gray; cursor:not-allowed; }
    .msg { margin-top:1rem; font-size:.95rem; }
    .error { margin-top:.5rem; font-size:.9rem; color:var(--danger); display:none; }
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
    <h2>Подтверждение почты</h2>
    <p>Мы отправили письмо для подтверждения почты.<br>Пожалуйста, проверьте свою почту.</p>
    <input type="hidden" id="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>" />

    <button id="resendBtn" class="btn">Отправить письмо снова</button>
    <button id="checkBtn" class="btn">Проверить подтверждение</button>

    <div id="statusMsg" class="msg"></div>
    <div id="errorMsg" class="error"></div>
  </div>
</div>

<footer style="background:var(--card-bg); padding:1rem; text-align:center; margin-top:3rem; font-size:.9rem;">
  2025 Онлайн Академия.
</footer>

<script>
// ===== Тема (как в регистрации) =====
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
}
themeSwitch.addEventListener('change', function() {
  const theme = this.checked ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', theme);
  setCookie("theme", theme, 365);
});

// ===== Логика подтверждения =====
const csrf = document.getElementById('csrf').value;
const resendBtn = document.getElementById('resendBtn');
const checkBtn  = document.getElementById('checkBtn');
const statusMsg = document.getElementById('statusMsg');
const errorMsg  = document.getElementById('errorMsg');

async function sendConfirmEmail() {
  try {
    const res = await fetch('../backend/send_confirm_email.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf }),
      credentials: 'include'
    });
    const data = await res.json();
    if (data.ok) {
      statusMsg.textContent = "Письмо отправлено! Проверьте почту.";
      errorMsg.style.display = "none";
      startResendCooldown();
    } else {
      errorMsg.textContent = data.error || "Ошибка при отправке письма.";
      errorMsg.style.display = "block";
    }
  } catch (err) {
    errorMsg.textContent = "Сеть недоступна. Попробуйте позже.";
    errorMsg.style.display = "block";
  }
}

// Блокировка кнопки на 3 минуты
function startResendCooldown() {
  let secs = 180;
  resendBtn.disabled = true;
  const timer = setInterval(() => {
    secs--;
    resendBtn.textContent = `Отправить снова (${secs})`;
    if (secs <= 0) {
      clearInterval(timer);
      resendBtn.disabled = false;
      resendBtn.textContent = "Отправить письмо снова";
    }
  }, 1000);
}

async function checkStatus() {
  try {
    const res = await fetch('../backend/check_confirm_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf }),
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (data.confirmed) {
      window.location.href = "map.php";
    } else {
      statusMsg.textContent = "Почта ещё не подтверждена.";
    }
  } catch (err) {
    errorMsg.textContent = "Ошибка сети при проверке.";
    errorMsg.style.display = "block";
  }
}

// События
resendBtn.addEventListener('click', sendConfirmEmail);
checkBtn.addEventListener('click', checkStatus);

// Автоматическая проверка каждые 15 сек
setInterval(checkStatus, 15000);

// При загрузке сразу шлём письмо
sendConfirmEmail();
</script>
</body>
</html>
