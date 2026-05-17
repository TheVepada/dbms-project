<?php
// Playlist Controller

class PlaylistController {
    public static function getAll() {
        $user = JWT::required();

        try {
            // Use legacy `playlist` table
            $playlists = dbFetchAll(
                'SELECT PlaylistID AS id, Name AS title, Name AS name, UserID AS user_id, CreationDate AS created_at FROM playlist WHERE UserID = ?',
                [$user['id']]
            );
            echo json_encode($playlists);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function create() {
        $user = JWT::required();
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';

        if (!$name) {
            http_response_code(400);
            echo json_encode(['error' => 'Playlist name is required']);
            return;
        }

        try {
            // Insert into legacy `playlist` table
            dbQuery(
                'INSERT INTO playlist (UserID, Name, CreationDate) VALUES (?, ?, CURDATE())',
                [$user['id'], $name]
            );
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $playlist = dbFetch(
                'SELECT PlaylistID AS id, Name AS title, Name AS name, UserID AS user_id, CreationDate AS created_at FROM playlist WHERE PlaylistID = ?',
                [$id]
            );

            if (!$playlist) {
                http_response_code(404);
                echo json_encode(['error' => 'Playlist not found']);
                return;
            }

            $songs = dbFetchAll(
                'SELECT s.SongID AS id, s.Title AS title, s.ArtistID AS artist_id, s.AlbumID AS album_id, s.Duration AS duration, s.Genre AS genre, NULL AS audio_url, NULL AS cover_url, ar.Name AS artist_name
                 FROM song s
                 JOIN playlistsong ps ON s.SongID = ps.SongID
                 LEFT JOIN artist ar ON ar.ArtistID = s.ArtistID
                 WHERE ps.PlaylistID = ?',
                [$id]
            );

            $playlist['songs'] = $songs;
            echo json_encode($playlist);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function addSong($id) {
        JWT::required();
        $data = json_decode(file_get_contents('php://input'), true);
        $song_id = $data['song_id'] ?? null;

        try {
            dbQuery(
                'INSERT INTO playlistsong (PlaylistID, SongID) VALUES (?, ?)',
                [$id, $song_id]
            );
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function removeSong($playlist_id, $song_id) {
        JWT::required();

        try {
            dbQuery(
                'DELETE FROM playlistsong WHERE PlaylistID = ? AND SongID = ?',
                [$playlist_id, $song_id]
            );
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
