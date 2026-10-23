<?php use function App\Lib\e; ?>
<section class="card"><h2>বলেছি</h2>
<?php foreach (($data['moments'] ?? []) as $moment): ?>
<div class="card"><p><?= nl2br(e((string)$moment['content'])) ?></p><p class="meta"><?= e((string)$moment['date']) ?></p></div>
<?php endforeach; ?></section>
