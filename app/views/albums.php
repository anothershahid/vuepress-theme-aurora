<?php use function App\Lib\e; ?>
<?php foreach (($data['albums'] ?? []) as $album): ?>
<section class="card"><h2><?= e((string)$album['title']) ?></h2>
<div class="inline">
<?php foreach (($album['photos'] ?? []) as $photo): ?>
    <div><img src="<?= e((string)$photo['src']) ?>" alt="<?= e((string)$photo['caption']) ?>" style="max-width:100%"><p><?= e((string)$photo['caption']) ?></p></div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
