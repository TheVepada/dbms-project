<?php
// Simple health check for DB connectivity and main tables
require_once 'config.php';
header('Content-Type: application/json');

$tables = ['album', 'artist', 'playlist', 'playlistsong', 'song', 'user'];
$result = [];
try {
    foreach ($tables as $t) {
        try {
            $count = dbFetch("SELECT COUNT(*) AS cnt FROM `$t`", []);
            $result[$t] = ['ok' => true, 'count' => $count['cnt'] ?? 0];
        } catch (Exception $e) {
            $result[$t] = ['ok' => false, 'error' => $e->getMessage()];
        }
    }
    echo json_encode(['ok' => true, 'tables' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
