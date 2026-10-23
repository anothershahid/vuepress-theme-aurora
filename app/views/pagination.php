<?php use function App\Lib\e;
$total = (int)($data['totalPages'] ?? 1);
$current = max(1, (int)($data['page'] ?? 1));
if ($total > 1):
$prefix = $data['prefix'] ?? '/posts';
$query = trim((string)($data['query'] ?? ''));
?>
<div class="card pagination">
<?php for ($i=1; $i<=$total; $i++):
    $url = $prefix . '?page=' . $i;
    if ($query !== '') $url .= '&q=' . rawurlencode($query);
?>
<a href="<?= e($url) ?>"><?= $i === $current ? '• '.$i : (string)$i ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>
