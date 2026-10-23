<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Lib\BlogRepository;
use function App\Lib\{csrf_token, verify_csrf_or_fail, e, safe_redirect, render_markdown, is_admin, require_admin};

$repo = new BlogRepository(APP_STORAGE_DATA);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$send = static function (string $view, array $data = [], int $code = 200): void {
    http_response_code($code);
    $GLOBALS['view_name'] = $view;
    $GLOBALS['view_data'] = $data;
    require APP_ROOT . '/app/views/layout.php';
    exit;
};

$slugFromPath = static function (string $requestPath, string $prefix): ?string {
    if (!str_starts_with($requestPath, $prefix)) {
        return null;
    }
    $slug = trim(substr($requestPath, strlen($prefix)), '/');
    return $slug === '' ? null : $slug;
};

if ($method === 'POST' && $path === '/comment') {
    verify_csrf_or_fail($_POST['csrf'] ?? '');
    $postSlug = trim((string)($_POST['post_slug'] ?? ''));
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $website = trim((string)($_POST['website'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $trap = trim((string)($_POST['nickname'] ?? ''));

    if ($trap !== '') {
        safe_redirect('/post/' . rawurlencode($postSlug) . '?msg=ok');
    }

    $errors = [];
    if ($postSlug === '' || !$repo->postExists($postSlug)) {
        $errors[] = 'অবৈধ পোস্ট।';
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors[] = 'নাম ২ থেকে ৮০ অক্ষরের মধ্যে হতে হবে।';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'সঠিক ইমেইল দিন।';
    }
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = 'সঠিক ওয়েবসাইট ঠিকানা দিন।';
    }
    if (mb_strlen($content) < 5 || mb_strlen($content) > 2000) {
        $errors[] = 'মন্তব্য ৫ থেকে ২০০০ অক্ষরের মধ্যে হতে হবে।';
    }

    $last = $_SESSION['last_comment_at'] ?? 0;
    if (time() - (int)$last < 30) {
        $errors[] = 'খুব দ্রুত মন্তব্য করা যাচ্ছে না। ৩০ সেকেন্ড পরে আবার চেষ্টা করুন।';
    }

    if ($errors !== []) {
        $send('error', ['title' => 'মন্তব্য পাঠানো যায়নি', 'errors' => $errors], 422);
    }

    $_SESSION['last_comment_at'] = time();
    $repo->addComment([
        'id' => bin2hex(random_bytes(8)),
        'post_slug' => $postSlug,
        'name' => $name,
        'email' => $email,
        'website' => $website,
        'content' => $content,
        'created_at' => gmdate('c'),
        'approved' => false,
        'ip_hash' => hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? '')),
    ]);

    safe_redirect('/post/' . rawurlencode($postSlug) . '?msg=pending');
}

if ($path === '/admin/comments' && $method === 'POST') {
    verify_csrf_or_fail($_POST['csrf'] ?? '');

    if (!is_admin()) {
        $password = (string)($_POST['password'] ?? '');
        if (!password_verify($password, APP_CONFIG['admin_password_hash'])) {
            $send('error', ['title' => 'লগইন ব্যর্থ', 'errors' => ['অ্যাডমিন পাসওয়ার্ড সঠিক নয়।']], 403);
        }
        $_SESSION['is_admin'] = true;
        safe_redirect('/admin/comments');
    }

    require_admin();
    $action = (string)($_POST['action'] ?? '');
    $id = (string)($_POST['id'] ?? '');
    if ($id === '') {
        $send('error', ['title' => 'ভুল অনুরোধ', 'errors' => ['মন্তব্য আইডি পাওয়া যায়নি।']], 422);
    }
    if ($action === 'approve') {
        $repo->moderateComment($id, true);
    } elseif ($action === 'delete') {
        $repo->deleteComment($id);
    }
    safe_redirect('/admin/comments');
}

$query = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 4;

if ($path === '/' || $path === '/posts') {
    $result = $repo->searchPosts($query, $page, $perPage);
    $send('home', [
        'title' => 'হোম',
        'posts' => $result['items'],
        'totalPages' => $result['total_pages'],
        'page' => $page,
        'query' => $query,
        'settings' => $repo->getSettings(),
    ]);
}

if (($slug = $slugFromPath($path, '/post/')) !== null) {
    $post = $repo->getPostBySlug($slug);
    if ($post === null) {
        $send('error', ['title' => 'পোস্ট পাওয়া যায়নি', 'errors' => ['দুঃখিত, এই পোস্টটি নেই।']], 404);
    }

    $comments = $repo->getCommentsForPost($slug, false);
    $send('post', [
        'title' => $post['title'],
        'post' => $post,
        'comments' => $comments,
        'csrf' => csrf_token(),
        'message' => $_GET['msg'] ?? '',
    ]);
}

if ($path === '/categories') {
    $send('categories', ['title' => 'ক্যাটাগরি', 'categories' => $repo->getCategoriesWithCounts()]);
}

if (($cat = $slugFromPath($path, '/category/')) !== null) {
    $result = $repo->postsByCategory($cat, $page, $perPage);
    $send('listing', ['title' => 'ক্যাটাগরি: ' . $cat, 'items' => $result['items'], 'totalPages' => $result['total_pages'], 'page' => $page, 'prefix' => '/category/' . rawurlencode($cat)]);
}

if ($path === '/tags') {
    $send('tags', ['title' => 'ট্যাগ', 'tags' => $repo->getTagsWithCounts()]);
}

if (($tag = $slugFromPath($path, '/tag/')) !== null) {
    $result = $repo->postsByTag($tag, $page, $perPage);
    $send('listing', ['title' => 'ট্যাগ: ' . $tag, 'items' => $result['items'], 'totalPages' => $result['total_pages'], 'page' => $page, 'prefix' => '/tag/' . rawurlencode($tag)]);
}

if ($path === '/timeline') {
    $send('timeline', ['title' => 'টাইমলাইন', 'timeline' => $repo->getTimeline()]);
}

if ($path === '/moments') {
    $send('moments', ['title' => 'বলেছি', 'moments' => $repo->getMoments()]);
}

if ($path === '/friends') {
    $send('friends', ['title' => 'বন্ধু লিংক', 'groups' => $repo->getFriends()]);
}

if ($path === '/albums') {
    $send('albums', ['title' => 'ছবি অ্যালবাম', 'albums' => $repo->getAlbums()]);
}

if ($path === '/music') {
    $send('music', ['title' => 'মিউজিক', 'music' => $repo->getMusic()]);
}

if ($path === '/admin/comments') {
    if (!is_admin()) {
        $send('admin_login', ['title' => 'মডারেশন লগইন', 'csrf' => csrf_token()]);
    }
    $send('admin_comments', ['title' => 'মন্তব্য মডারেশন', 'csrf' => csrf_token(), 'comments' => $repo->getAllComments()]);
}

$send('error', ['title' => 'পৃষ্ঠা পাওয়া যায়নি', 'errors' => ['আপনি যে পাতাটি খুঁজছেন তা নেই।']], 404);
