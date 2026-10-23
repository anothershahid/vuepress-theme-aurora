<?php
use function App\Lib\e;
$view = $GLOBALS['view_name'] ?? 'error';
$data = $GLOBALS['view_data'] ?? [];
$settings = $data['settings'] ?? [];
$title = $data['title'] ?? 'Aurora বাংলা ব্লগ';
?>
<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e((string)$title) ?> | <?= e((string)($settings['site_title'] ?? 'Aurora')) ?></title>
    <link rel="stylesheet" href="/assets/css/theme.style.css">
    <link rel="stylesheet" href="/assets/css/coverStyle.css">
    <style>
        body{max-width:1100px;margin:0 auto;padding:1rem;font-family:Arial,sans-serif;background:#f6f8fa;color:#222}
        nav{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
        nav a{padding:.4rem .8rem;background:#fff;border-radius:8px;text-decoration:none;color:#333}
        .card{background:#fff;border-radius:12px;padding:1rem;margin:.7rem 0;box-shadow:0 4px 12px rgba(0,0,0,.05)}
        .meta{color:#666;font-size:.9rem}
        .pagination a{margin-right:.4rem}
        input,textarea,button{width:100%;padding:.6rem;margin:.3rem 0;border:1px solid #ddd;border-radius:8px}
        button{cursor:pointer;background:#2f6feb;color:#fff}
        .inline{display:flex;gap:.6rem}
        .inline>*{flex:1}
    </style>
</head>
<body>
<header class="card">
    <h1><?= e((string)($settings['site_title'] ?? 'Aurora বাংলা ব্লগ')) ?></h1>
    <p><?= e((string)($settings['site_desc'] ?? 'VuePress Aurora-এর PHP সংস্করণ')) ?></p>
    <nav>
        <a href="/">হোম</a><a href="/posts">পোস্ট</a><a href="/categories">ক্যাটাগরি</a><a href="/tags">ট্যাগ</a>
        <a href="/timeline">টাইমলাইন</a><a href="/moments">বলেছি</a><a href="/friends">বন্ধু লিংক</a>
        <a href="/albums">অ্যালবাম</a><a href="/music">মিউজিক</a><a href="/admin/comments">মন্তব্য মডারেশন</a>
    </nav>
    <form method="get" action="/posts" class="inline">
        <input name="q" placeholder="পোস্ট খুঁজুন..." value="<?= e((string)($_GET['q'] ?? '')) ?>">
        <button type="submit">খুঁজুন</button>
    </form>
</header>
<main>
<?php require __DIR__ . '/' . $view . '.php'; ?>
</main>
<footer class="card"><small>© <?= date('Y') ?> Aurora বাংলা</small></footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
