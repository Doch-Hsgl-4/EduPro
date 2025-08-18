<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Онлайн Академия</title>
<style>
    :root {
        --bg-color: #ffffff;
        --text-color: #000000;
        --card-bg: #f0f0f0;
    }
    [data-theme="dark"] {
        --bg-color: #121212;
        --text-color: #ffffff;
        --card-bg: #1f1f1f;
    }
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: var(--bg-color);
        color: var(--text-color);
        transition: background-color 0.3s, color 0.3s;
    }
    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background-color: var(--card-bg);
    }
    .theme-toggle {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .container {
        max-width: 1000px;
        margin: auto;
        padding: 1rem;
    }
    .btns {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin: 2rem 0;
    }
    .btn {
        padding: 0.7rem 1.2rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background-color: #007BFF;
        color: white;
        font-size: 15px;
    }
    .btn:hover {
        background-color: #0056b3;
    }
    .hero {
        text-align: center;
        padding: 2rem 1rem;
    }
    .hero p {
        max-width: 700px;
        margin: auto;
        line-height: 1.5;
    }
    .benefits, .info-blocks, .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 2rem;
    }
    .card, .info, .stat {
        background: var(--card-bg);
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
    }
    .map-block {
        margin-top: 3rem;
        text-align: center;
    }
    .map-link {
        position: relative;
        display: inline-block;
    }
    .map-link img {
        max-width: 100%;
        border-radius: 8px;
        cursor: pointer;
        display: block;
    }
    .map-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.6);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: bold;
        font-size: 1.1rem;
        text-align: center;
    }
    .stat h3 {
        font-size: 2rem;
        margin: 0.5rem 0;
    }
    footer {
        background-color: var(--card-bg);
        padding: 1rem;
        text-align: center;
        margin-top: 3rem;
        font-size: 0.9rem;
    }
</style>
</head>
<body>

<header>
    <h1>Онлайн Академия</h1>
    <div class="theme-toggle">
        <label>
            <input type="checkbox" id="themeSwitch"> 🌙
        </label>
    </div>
</header>

<div class="container">
    <section class="hero">
        <h2>Добро пожаловать в Академию</h2>
        <p>Наша онлайн академия — это современная платформа для обучения, где каждый студент получает не только знания, но и практические навыки. 
        Мы объединяем лучшие методики преподавания, интерактивные материалы и живое общение с экспертами, чтобы сделать обучение максимально эффективным и увлекательным.</p>
    </section>

    <div class="btns">
        <a href="#"><button class="btn">Войти</button></a>
        <a href="register.php"><button class="btn">Зарегистрироваться</button></a>
    </div>

    <section class="benefits">
        <div class="card">📚 <strong>Глубокое понимание материала</strong> — изучаем реальные кейсы и ситуации из практики.</div>
        <div class="card">🛠 <strong>Практические задания</strong> — закрепляем знания через реальные задачи.</div>
        <div class="card">👨‍🏫 <strong>Доступ к экспертам</strong> — всегда можно задать вопрос и получить ответ.</div>
        <div class="card">⏳ <strong>Гибкий график</strong> — учитесь в удобное время.</div>
        <div class="card">🎓 <strong>Сертификаты</strong> — подтверждение квалификации.</div>
        <div class="card">🔄 <strong>Обновления курсов</strong> — доступ к актуальным материалам.</div>
    </section>

    <section class="info-blocks">
        <div class="info"><h3>О нас</h3><p>Мы работаем с 2020 года и обучили более 10 000 студентов по разным направлениям.</p></div>
        <div class="info"><h3>Методика</h3><p>Авторские курсы, сочетающие теорию и практику.</p></div>
        <div class="info"><h3>Поддержка</h3><p>Наши кураторы на связи 24/7, чтобы помочь в обучении.</p></div>
    </section>

    <section class="stats">
        <div class="stat"><h3>10 000+</h3><p>Студентов</p></div>
        <div class="stat"><h3>50+</h3><p>Курсов</p></div>
        <div class="stat"><h3>95%</h3><p>Довольных студентов</p></div>
    </section>

    <section class="map-block">
        <a href="#" class="map-link">
            <img src="img/image.png" alt="Карта Академии">
            <span class="map-text">Перейти к карте</span>
        </a>
    </section>
</div>

<footer>
    2025 Онлайн Академия.
</footer>

<script>
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i=0;i < ca.length;i++) {
            let c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    const themeSwitch = document.getElementById('themeSwitch');
    const savedTheme = getCookie("theme");

    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        themeSwitch.checked = savedTheme === 'dark';
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
</script>

</body>
</html>
