<?php use function App\Lib\e; ?>
<section class="card">
    <h2>মডারেশন লগইন</h2>
    <form method="post" action="/admin/comments">
        <input type="hidden" name="csrf" value="<?= e((string)$data['csrf']) ?>">
        <label>অ্যাডমিন পাসওয়ার্ড</label>
        <input type="password" name="password" required>
        <button type="submit">লগইন</button>
    </form>
</section>
