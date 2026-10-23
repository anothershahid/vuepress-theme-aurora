<?php
use function App\Lib\{e, render_markdown};
$post = $data['post'];
?>
<article class="card">
    <h2><?= e((string)$post['title']) ?></h2>
    <p class="meta">তারিখ: <?= e((string)$post['date']) ?> | ট্যাগ: <?= e(implode(', ', $post['tags'] ?? [])) ?></p>
    <?php if (!empty($post['cover'])): ?><img src="<?= e((string)$post['cover']) ?>" alt="কভার" style="max-width:100%;border-radius:8px"><?php endif; ?>
    <div><?= render_markdown((string)$post['content']) ?></div>
</article>

<section class="card">
    <h3>মন্তব্য</h3>
    <?php if (($data['message'] ?? '') === 'pending'): ?><p>আপনার মন্তব্য মডারেশনের জন্য অপেক্ষায় আছে।</p><?php endif; ?>
    <?php foreach (($data['comments'] ?? []) as $comment): ?>
        <div class="card">
            <strong><?= e((string)$comment['name']) ?></strong>
            <p class="meta"><?= e((string)$comment['created_at']) ?></p>
            <p><?= nl2br(e((string)$comment['content'])) ?></p>
        </div>
    <?php endforeach; ?>
</section>

<section class="card">
    <h3>নতুন মন্তব্য দিন</h3>
    <form method="post" action="/comment">
        <input type="hidden" name="csrf" value="<?= e((string)$data['csrf']) ?>">
        <input type="hidden" name="post_slug" value="<?= e((string)$post['slug']) ?>">
        <label>নাম</label><input name="name" maxlength="80" required>
        <label>ইমেইল (ঐচ্ছিক)</label><input name="email" type="email" maxlength="120">
        <label>ওয়েবসাইট (ঐচ্ছিক)</label><input name="website" type="url" maxlength="200">
        <input name="nickname" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px" aria-hidden="true">
        <label>মন্তব্য</label><textarea name="content" minlength="5" maxlength="2000" required></textarea>
        <button type="submit">পাঠান</button>
    </form>
</section>
