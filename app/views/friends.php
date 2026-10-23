<?php use function App\Lib\e; ?>
<?php foreach (($data['groups'] ?? []) as $group): ?>
<section class="card"><h2><?= e((string)$group['title']) ?></h2>
<?php foreach (($group['links'] ?? []) as $link): ?>
<p><a href="<?= e((string)$link['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e((string)$link['title']) ?></a> - <?= e((string)$link['describe']) ?></p>
<?php endforeach; ?></section>
<?php endforeach; ?>
