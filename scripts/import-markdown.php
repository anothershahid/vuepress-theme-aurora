<?php

declare(strict_types=1);

$repo = realpath(__DIR__ . '/..');
if ($repo === false) {
    fwrite(STDERR, "রিপো পাথ পাওয়া যায়নি\n");
    exit(1);
}
$docs = $repo . '/docs';
$out = $repo . '/storage/data/posts.json';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docs));
$posts = [];

foreach ($rii as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'md') {
        continue;
    }
    $path = $file->getPathname();
    if (str_contains($path, '/.vuepress/')) {
        continue;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        continue;
    }

    $frontmatter = [];
    $content = $raw;
    if (preg_match('/^---\R(.*?)\R---\R/s', $raw, $m) === 1) {
        $frontmatter = parse_frontmatter($m[1]);
        $content = substr($raw, strlen($m[0]));
    }

    $title = $frontmatter['title'] ?? first_heading($content) ?? basename($path, '.md');
    $date = (string)($frontmatter['date'] ?? date('Y-m-d', (int)$file->getMTime()));
    $categories = normalize_array($frontmatter['category'] ?? $frontmatter['categories'] ?? []);
    $tags = normalize_array($frontmatter['tag'] ?? $frontmatter['tags'] ?? []);
    $cover = (string)($frontmatter['cover'] ?? '');

    $relative = str_replace($docs . '/', '', $path);
    $slug = trim(strtolower(preg_replace('/[^a-z0-9\-\/]+/i', '-', str_replace('.md', '', $relative)) ?? ''), '-');

    $excerpt = mb_substr(trim(strip_tags($content)), 0, 110);

    $posts[] = [
        'slug' => str_replace('/', '-', $slug),
        'title' => trim((string)$title),
        'date' => $date,
        'cover' => $cover,
        'excerpt' => $excerpt,
        'categories' => $categories,
        'tags' => $tags,
        'content' => trim($content),
    ];
}

usort($posts, static fn($a, $b) => strcmp((string)$b['date'], (string)$a['date']));
file_put_contents($out, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
printf("ইমপোর্ট সম্পন্ন: %dটি পোস্ট\n", count($posts));

function parse_frontmatter(string $fm): array
{
    $result = [];
    $lines = preg_split('/\R/', $fm) ?: [];
    $currentKey = null;

    foreach ($lines as $line) {
        $line = rtrim($line);
        if ($line === '') {
            continue;
        }

        if (preg_match('/^([A-Za-z0-9_\-]+):\s*(.*)$/', $line, $m) === 1) {
            $currentKey = strtolower($m[1]);
            $value = trim($m[2]);
            if ($value === '') {
                $result[$currentKey] = [];
            } elseif (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                $parts = array_filter(array_map('trim', explode(',', trim($value, '[]'))));
                $result[$currentKey] = array_values(array_map(static fn($v) => trim((string)$v, "'\""), $parts));
            } else {
                $result[$currentKey] = trim($value, "'\"");
            }
            continue;
        }

        if ($currentKey !== null && preg_match('/^\-\s*(.+)$/', trim($line), $m) === 1) {
            if (!isset($result[$currentKey]) || !is_array($result[$currentKey])) {
                $result[$currentKey] = [];
            }
            $result[$currentKey][] = trim($m[1], "'\"");
        }
    }

    return $result;
}

function normalize_array(mixed $value): array
{
    if (is_string($value)) {
        return $value === '' ? [] : [$value];
    }
    if (is_array($value)) {
        return array_values(array_filter(array_map(static fn($v) => trim((string)$v), $value), static fn($v) => $v !== ''));
    }
    return [];
}

function first_heading(string $content): ?string
{
    if (preg_match('/^#\s+(.+)$/m', $content, $m) === 1) {
        return trim($m[1]);
    }
    return null;
}
