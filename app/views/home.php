<?php use function App\Lib\e; ?>
<?php foreach (($data['posts'] ?? []) as $post): ?>
<article class="card">
    <h2><a href="/post/<?= e((string)$post['slug']) ?>"><?= e((string)$post['title']) ?></a></h2>
    <p class="meta">তারিখ: <?= e((string)$post['date']) ?> | ক্যাটাগরি: <?= e(implode(', ', $post['categories'] ?? [])) ?></p>
    <p><?= e((string)($post['excerpt'] ?? '')) ?></p>
</article>
<?php endforeach; ?>
<?php require __DIR__ . '/pagination.php'; ?>
