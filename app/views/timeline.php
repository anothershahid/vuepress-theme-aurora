<?php use function App\Lib\e; ?>
<section class="card"><h2>টাইমলাইন</h2>
<?php foreach (($data['timeline'] ?? []) as $post): ?>
<p><strong><?= e((string)$post['date']) ?></strong> - <a href="/post/<?= e((string)$post['slug']) ?>"><?= e((string)$post['title']) ?></a></p>
<?php endforeach; ?></section>
