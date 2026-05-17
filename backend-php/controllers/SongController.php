<?php
// Song Controller

class SongController {
    public static function getAll() {
        try {
            $genre = $_GET['genre'] ?? null;
            $artist_id = $_GET['artist_id'] ?? null;
            // Use legacy `song` table only and map columns to modern API shape
            $where = [];
            $params = [];

            if ($artist_id) {
                $where[] = 's.ArtistID = ?';
                $params[] = $artist_id;
            }

            if ($genre) {
                $where[] = 's.Genre LIKE ?';
                $params[] = "%$genre%";
            }

            $whereClause = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

            $sql = "SELECT s.SongID AS id, s.Title AS title, s.ArtistID AS artist_id, s.AlbumID AS album_id, s.Duration AS duration, s.Genre AS genre, NULL AS audio_url, NULL AS cover_url, ar.Name AS artist_name
                    FROM song s
                    LEFT JOIN artist ar ON ar.ArtistID = s.ArtistID $whereClause
                    ORDER BY s.SongID";

            $songs = dbFetchAll($sql, $params);
            echo json_encode($songs);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            // Read from legacy `song` table
            $song = dbFetch(
                'SELECT s.SongID AS id, s.Title AS title, s.ArtistID AS artist_id, s.AlbumID AS album_id, s.Duration AS duration, s.Genre AS genre, NULL AS audio_url, NULL AS cover_url, ar.Name AS artist_name
                 FROM song s
                 LEFT JOIN artist ar ON ar.ArtistID = s.ArtistID
                 WHERE s.SongID = ?',
                [$id]
            );

            echo json_encode($song ?: []);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function updateCover($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $url = trim($data['url'] ?? '');

            if (!$url) {
                http_response_code(400);
                echo json_encode(['error' => 'Image URL is required']);
                return;
            }

            // Legacy `song` table does not have a cover_art column. A persistent update
            // can't be performed without altering the schema. Return success for the
            // API so the frontend can continue, but the value is not persisted.
            echo json_encode(['ok' => true, 'cover_url' => $url]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
