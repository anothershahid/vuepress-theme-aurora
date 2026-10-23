<?php use function App\Lib\e; ?>
<?php foreach (($data['items'] ?? []) as $post): ?>
<article class="card">
    <h3><a href="/post/<?= e((string)$post['slug']) ?>"><?= e((string)$post['title']) ?></a></h3>
    <p class="meta"><?= e((string)$post['date']) ?></p>
</article>
<?php endforeach; ?>
<?php require __DIR__ . '/pagination.php'; ?>
