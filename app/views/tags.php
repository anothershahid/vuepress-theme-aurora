<?php use function App\Lib\e; ?>
<section class="card"><h2>সব ট্যাগ</h2>
<?php foreach (($data['tags'] ?? []) as $name => $count): ?>
<p><a href="/tag/<?= rawurlencode((string)$name) ?>"><?= e((string)$name) ?></a> (<?= (int)$count ?>)</p>
<?php endforeach; ?></section>
