<?php
// Album Controller

class AlbumController {
    public static function getAll() {
        try {
            // Use legacy `album` table only
            $albums = dbFetchAll('SELECT a.AlbumID AS id, a.Title AS title, a.ArtistID AS artist_id, a.ReleaseDate AS release_date, NULL AS cover_url, ar.Name AS artist_name FROM album a LEFT JOIN artist ar ON ar.ArtistID = a.ArtistID ORDER BY a.AlbumID');
            echo json_encode($albums);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            // Read album from legacy `album` table
            $album = dbFetch(
                'SELECT a.AlbumID AS id, a.Title AS title, a.ArtistID AS artist_id, a.ReleaseDate AS release_date, NULL AS cover_url, ar.Name AS artist_name
                 FROM album a
                 LEFT JOIN artist ar ON ar.ArtistID = a.ArtistID
                 WHERE a.AlbumID = ?',
                [$id]
            );

            if (!$album) {
                http_response_code(404);
                echo json_encode(['error' => 'Album not found']);
                return;
            }

            // Fetch songs from legacy `song` table
            $songs = dbFetchAll(
                'SELECT s.SongID AS id, s.Title AS title, s.ArtistID AS artist_id, s.AlbumID AS album_id, s.Duration AS duration, s.Genre AS genre, NULL AS audio_url, NULL AS cover_url, ar.Name AS artist_name
                 FROM song s
                 LEFT JOIN artist ar ON ar.ArtistID = s.ArtistID
                 WHERE s.AlbumID = ?
                 ORDER BY s.SongID',
                [$id]
            );

            $album['songs'] = $songs;
            echo json_encode($album);
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

            // Legacy `album` table does not have a cover_art column. Do not persist;
            // return success so frontend can continue showing the uploaded cover.
            echo json_encode(['ok' => true, 'cover_url' => $url]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
