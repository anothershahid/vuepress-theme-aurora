<?php use function App\Lib\e; ?>
<section class="card"><h2>সব ক্যাটাগরি</h2>
<?php foreach (($data['categories'] ?? []) as $name => $count): ?>
<p><a href="/category/<?= rawurlencode((string)$name) ?>"><?= e((string)$name) ?></a> (<?= (int)$count ?>)</p>
<?php endforeach; ?></section>
