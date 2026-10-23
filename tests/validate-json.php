<?php

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
$schema = json_decode((string)file_get_contents($root . '/storage/data/schema.json'), true);
if (!is_array($schema)) {
    fwrite(STDERR, "schema.json পড়া যায়নি\n");
    exit(1);
}

$targets = [
    'posts' => 'posts.json',
    'moments' => 'moments.json',
    'friends' => 'friends.json',
    'albums' => 'albums.json',
    'comments' => 'comments.json'
];

foreach ($targets as $name => $file) {
    $data = json_decode((string)file_get_contents($root . '/storage/data/' . $file), true);
    if (!is_array($data)) {
        fwrite(STDERR, "$file বৈধ JSON নয়\n");
        exit(1);
    }
    $required = $schema[$name]['required'] ?? [];
    foreach ($data as $index => $row) {
        if (!is_array($row)) {
            fwrite(STDERR, "$file[$index] অবজেক্ট নয়\n");
            exit(1);
        }
        foreach ($required as $field) {
            if (!array_key_exists($field, $row)) {
                fwrite(STDERR, "$file[$index] এ $field নেই\n");
                exit(1);
            }
        }
    }
}

$music = json_decode((string)file_get_contents($root . '/storage/data/music.json'), true);
if (!is_array($music) || !isset($music['songs']) || !is_array($music['songs'])) {
    fwrite(STDERR, "music.json এ songs অ্যারে নেই\n");
    exit(1);
}

echo "JSON validation সফল\n";
