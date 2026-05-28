<?php

define('CONTEXT_FILE', __DIR__ . '/../IAContext.txt');

function loadContext(): array {
    if (!file_exists(CONTEXT_FILE)) return [];
    $raw = file_get_contents(CONTEXT_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveContext(array $data): void {
    $ctx = loadContext();
    $ctx = array_merge($ctx, $data);
    $ctx['_updated'] = date('Y-m-d H:i:s');
    file_put_contents(CONTEXT_FILE, json_encode($ctx, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function getContext(string $key, mixed $default = null): mixed {
    $ctx = loadContext();
    return $ctx[$key] ?? $default;
}
