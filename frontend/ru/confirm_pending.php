<?php
// /frontend/confirm_pending.php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; base-uri 'self'; form-action 'self';");

$welcome = isset($_COOKIE['welcome_name']) ? $_COOKIE['welcome_name'] : 'Пользователь';
?>
<!DOCTYPE html>
<html lang="ru" data-theme="">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Подтверждение почты — Онлайн Академия</title>
<style>
    :root { --bg-color:#ffffff; --text-color:#000000; --card-bg:#f0f0f0; --muted:#6b7280; }
    [data-theme="dark"] { --bg-color:#121212; --text-color:#ffffff; --card-bg:#1f1f1f; }
    body { margin:0; font-family:Arial, sans-serif; background:var(--bg-color); color:var(--text-color); transition:background-color .3s,color .3s; }
    header { display:flex; justify-content:space-between; align-items:center; padding:1rem; background:var(--card-bg); }
    .theme-toggle { display:flex; align-items:center; gap:.3rem; }
    .container { max-width: 680px; margin: 2rem auto; padding: 0 1rem; }
    .card { background:var(--card-bg); padding:1.5rem; border-radius:8px; text-align:center; }
    .btn { display:inline-block; padding:.8rem 1rem; border:none; border-radius:6px; cursor:pointer; background:#007BFF; color:white; font-size:15px; }
    .btn[disabled] { opacity:.6; cursor:not-allowed; }
    .muted { color:var(--muted); margin-top:.5rem; }
    .back { display:inline-block; margin-top:1rem; color:inherit; text-decoration:none; }
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
    <h2>Почти готово, <?php echo htmlspecialchars($welcome, ENT_QUOTES, 'UTF-8'); ?>!</h2>
    <p>Мы отправили письмо с подтверждением на вашу почту. Пожалуйста, перейдите по ссылке из письма, чтобы завершить регистрацию.</p>
    <p class="muted">Если письмо не пришло в течение нескольких минут, проверьте папку «Спам».</p>

    <button class="btn" id="resendBtn" disabled>Отправить письмо повторно</button>
    <div id="resendMsg" class="muted"></div>

    <a class="back" href="/">← На главную</a>
  </div>
</div>

<footer style="background:var(--card-bg); padding:1rem; text-align:center; margin-top:3rem; font-size:.9rem;">
  2025 Онлайн Академия.
</footer>

<script>
// Тема как на главной
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

// Заглушка на будущее (Mailtrap/SMTP)
document.getElementById('resendBtn').addEventListener('click', async () => {
  // В следующем шаге включим реальный запрос
  const msg = document.getElementById('resendMsg');
  msg.textContent = 'Функция будет доступна после настройки почты.';
});
</script>
</body>
</html>
