<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{block name=title}DevNotes{/block}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<header class="header">
    <div class="container header__inner">
        <a class="logo" href="/">
            <img class="logo__icon" src="/favicon.svg" alt="">
            DevNotes
        </a>
    </div>
</header>

<main class="main">
    <div class="container">
        {block name=content}{/block}
    </div>
</main>

<footer class="footer">
    <div class="container">© {$smarty.now|date_format:'Y'} DevNotes — блог о веб-разработке</div>
</footer>
</body>
</html>
