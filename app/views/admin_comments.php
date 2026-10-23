<?php use function App\Lib\e; ?>
<section class="card"><h2>মন্তব্য মডারেশন</h2>
<?php foreach (($data['comments'] ?? []) as $comment): ?>
<div class="card">
    <p><strong><?= e((string)$comment['name']) ?></strong> (<?= e((string)$comment['post_slug']) ?>)</p>
    <p><?= nl2br(e((string)$comment['content'])) ?></p>
    <p class="meta">অবস্থা: <?= !empty($comment['approved']) ? 'অনুমোদিত' : 'অপেক্ষমাণ' ?></p>
    <form method="post" action="/admin/comments" class="inline">
        <input type="hidden" name="csrf" value="<?= e((string)$data['csrf']) ?>">
        <input type="hidden" name="id" value="<?= e((string)$comment['id']) ?>">
        <button name="action" value="approve" type="submit">অনুমোদন</button>
        <button name="action" value="delete" type="submit">মুছুন</button>
    </form>
</div>
<?php endforeach; ?></section>
