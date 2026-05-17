<?php
// Log Controller

class LogController {
    public static function play() {
        $user = JWT::required();
        $data = json_decode(file_get_contents('php://input'), true);
        $song_id = $data['song_id'] ?? null;
        $device = $data['device'] ?? 'web';

        try {
            dbQuery(
                'INSERT INTO streaminglog (UserID, SongID, Timestamp, Device) VALUES (?, ?, NOW(), ?)',
                [$user['id'], $song_id, $device]
            );
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
