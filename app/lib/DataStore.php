<?php

declare(strict_types=1);

namespace App\Lib;

use RuntimeException;

final class DataStore
{
    private array $map = [
        'settings' => 'settings.json',
        'posts' => 'posts.json',
        'moments' => 'moments.json',
        'friends' => 'friends.json',
        'albums' => 'albums.json',
        'music' => 'music.json',
        'comments' => 'comments.json',
    ];

    public function __construct(private readonly string $dataDir)
    {
    }

    public function read(string $key): array
    {
        $path = $this->path($key);
        if (!is_file($path)) {
            return [];
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException('ডাটা পড়া যায়নি');
        }
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function write(string $key, array $data): void
    {
        $path = $this->path($key);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $lockPath = $path . '.lock';
        $lock = fopen($lockPath, 'c');
        if ($lock === false) {
            throw new RuntimeException('লক ফাইল খোলা যায়নি');
        }

        flock($lock, LOCK_EX);
        try {
            $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
            $bytes = file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            if ($bytes === false) {
                throw new RuntimeException('ডাটা লেখা যায়নি');
            }
            chmod($tmp, 0640);
            rename($tmp, $path);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function path(string $key): string
    {
        if (!isset($this->map[$key])) {
            throw new RuntimeException('অবৈধ ডাটা কী');
        }
        return rtrim($this->dataDir, '/') . '/' . $this->map[$key];
    }
}
