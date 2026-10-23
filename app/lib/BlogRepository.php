<?php

declare(strict_types=1);

namespace App\Lib;

final class BlogRepository
{
    private DataStore $store;

    public function __construct(string $dataDir)
    {
        $this->store = new DataStore($dataDir);
    }

    public function getSettings(): array
    {
        return $this->store->read('settings');
    }

    public function searchPosts(string $query, int $page, int $perPage): array
    {
        $posts = $this->store->read('posts');
        usort($posts, static fn($a, $b) => strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? '')));

        if ($query !== '') {
            $q = mb_strtolower($query);
            $posts = array_values(array_filter($posts, static function (array $post) use ($q): bool {
                $title = mb_strtolower((string)($post['title'] ?? ''));
                $content = mb_strtolower((string)($post['content'] ?? ''));
                return str_contains($title, $q) || str_contains($content, $q);
            }));
        }

        return $this->paginate($posts, $page, $perPage);
    }

    public function postExists(string $slug): bool
    {
        return $this->getPostBySlug($slug) !== null;
    }

    public function getPostBySlug(string $slug): ?array
    {
        foreach ($this->store->read('posts') as $post) {
            if (($post['slug'] ?? '') === $slug) {
                return $post;
            }
        }
        return null;
    }

    public function getCategoriesWithCounts(): array
    {
        $map = [];
        foreach ($this->store->read('posts') as $post) {
            foreach (($post['categories'] ?? []) as $cat) {
                $map[$cat] = ($map[$cat] ?? 0) + 1;
            }
        }
        ksort($map);
        return $map;
    }

    public function getTagsWithCounts(): array
    {
        $map = [];
        foreach ($this->store->read('posts') as $post) {
            foreach (($post['tags'] ?? []) as $tag) {
                $map[$tag] = ($map[$tag] ?? 0) + 1;
            }
        }
        ksort($map);
        return $map;
    }

    public function postsByCategory(string $category, int $page, int $perPage): array
    {
        $items = array_values(array_filter($this->store->read('posts'), static fn(array $p): bool => in_array($category, $p['categories'] ?? [], true)));
        return $this->paginate($items, $page, $perPage);
    }

    public function postsByTag(string $tag, int $page, int $perPage): array
    {
        $items = array_values(array_filter($this->store->read('posts'), static fn(array $p): bool => in_array($tag, $p['tags'] ?? [], true)));
        return $this->paginate($items, $page, $perPage);
    }

    public function getTimeline(): array
    {
        $posts = $this->store->read('posts');
        usort($posts, static fn($a, $b) => strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? '')));
        return $posts;
    }

    public function getMoments(): array
    {
        $moments = $this->store->read('moments');
        usort($moments, static fn($a, $b) => strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? '')));
        return $moments;
    }

    public function getFriends(): array
    {
        return $this->store->read('friends');
    }

    public function getAlbums(): array
    {
        return $this->store->read('albums');
    }

    public function getMusic(): array
    {
        return $this->store->read('music');
    }

    public function getCommentsForPost(string $postSlug, bool $includeUnapproved = false): array
    {
        $items = array_values(array_filter($this->store->read('comments'), static function (array $comment) use ($postSlug, $includeUnapproved): bool {
            if (($comment['post_slug'] ?? '') !== $postSlug) {
                return false;
            }
            if ($includeUnapproved) {
                return true;
            }
            return !empty($comment['approved']);
        }));

        usort($items, static fn($a, $b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
        return $items;
    }

    public function getAllComments(): array
    {
        return $this->store->read('comments');
    }

    public function addComment(array $comment): void
    {
        $comments = $this->store->read('comments');
        $comments[] = $comment;
        $this->store->write('comments', $comments);
    }

    public function moderateComment(string $id, bool $approved): void
    {
        $comments = $this->store->read('comments');
        foreach ($comments as &$comment) {
            if (($comment['id'] ?? '') === $id) {
                $comment['approved'] = $approved;
            }
        }
        unset($comment);
        $this->store->write('comments', $comments);
    }

    public function deleteComment(string $id): void
    {
        $comments = array_values(array_filter($this->store->read('comments'), static fn(array $c): bool => ($c['id'] ?? '') !== $id));
        $this->store->write('comments', $comments);
    }

    private function paginate(array $items, int $page, int $perPage): array
    {
        $total = count($items);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_slice($items, $offset, $perPage),
            'total_pages' => $totalPages,
        ];
    }
}
