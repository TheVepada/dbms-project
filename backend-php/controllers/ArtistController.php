<?php
// Artist Controller

class ArtistController {
    public static function getAll() {
        try {
            $q = $_GET['q'] ?? $_GET['search'] ?? null;

            if ($q) {
                $artists = dbFetchAll('SELECT ArtistID AS id, Name AS name, NULL AS bio FROM artist WHERE Name LIKE ?', ["%$q%"]);
            } else {
                $artists = dbFetchAll('SELECT ArtistID AS id, Name AS name, NULL AS bio FROM artist ORDER BY ArtistID');
            }

            echo json_encode($artists);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function search() {
        $q = $_GET['q'] ?? '';
        try {
            // Use legacy `artist` table for search
            $artists = dbFetchAll('SELECT ArtistID AS id, Name AS name, NULL AS bio FROM artist WHERE Name LIKE ?', ["%$q%"]);
            echo json_encode($artists);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $artist = dbFetch('SELECT ArtistID AS id, Name AS name, NULL AS bio FROM artist WHERE ArtistID = ?', [$id]);

            if (!$artist) {
                http_response_code(404);
                echo json_encode(['error' => 'Artist not found']);
                return;
            }

            $albums = dbFetchAll(
                'SELECT a.AlbumID AS id, a.Title AS title, a.ArtistID AS artist_id, a.ReleaseDate AS release_date, NULL AS cover_url, ar.Name AS artist_name
                 FROM album a
                 LEFT JOIN artist ar ON ar.ArtistID = a.ArtistID
                 WHERE a.ArtistID = ?
                 ORDER BY a.AlbumID',
                [$id]
            );

            $songs = dbFetchAll(
                'SELECT s.SongID AS id, s.Title AS title, s.ArtistID AS artist_id, s.AlbumID AS album_id, s.Duration AS duration, s.Genre AS genre, NULL AS audio_url, NULL AS cover_url, ar.Name AS artist_name
                 FROM song s
                 LEFT JOIN artist ar ON ar.ArtistID = s.ArtistID
                 WHERE s.ArtistID = ?
                 ORDER BY s.SongID',
                [$id]
            );

            $artist['albums'] = $albums;
            $artist['songs'] = $songs;
            echo json_encode($artist);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
