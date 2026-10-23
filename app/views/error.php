<?php use function App\Lib\e; ?>
<section class="card"><h2><?= e((string)($data['title'] ?? 'ত্রুটি')) ?></h2>
<?php foreach (($data['errors'] ?? []) as $error): ?><p><?= e((string)$error) ?></p><?php endforeach; ?></section>
