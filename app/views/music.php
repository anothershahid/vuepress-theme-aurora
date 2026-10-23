<?php use function App\Lib\e; ?>
<section class="card"><h2>মিউজিক প্লেয়ার</h2>
<?php foreach (($data['music']['songs'] ?? []) as $song): ?>
<div class="card">
    <strong><?= e((string)$song['title']) ?></strong>
    <audio controls preload="none" src="<?= e((string)$song['src']) ?>"></audio>
</div>
<?php endforeach; ?>
</section>
