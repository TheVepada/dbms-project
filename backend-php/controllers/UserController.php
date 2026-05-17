<?php
// User Controller

class UserController {
    private static function ensureProfileColumn($table) {
        try {
            dbQuery("ALTER TABLE `$table` ADD COLUMN profile_url TEXT NULL");
        } catch (Exception $e) {
            // Column already exists, or this fallback table is not present.
        }
    }

    public static function signup() {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$username || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Insert into legacy `user` table only
            dbQuery(
                'INSERT INTO `user` (Name, username, Email, password, JoinDate) VALUES (?, ?, ?, ?, CURDATE())',
                [$name ?: $username, $username, $email, $hash]
            );

            // get last insert id from the DB driver
            try {
                $last = dbFetch('SELECT LAST_INSERT_ID() as id', []);
                $lastId = $last['id'] ?? null;
            } catch (Exception $e) {
                $lastId = null;
            }

            echo json_encode(['id' => $lastId, 'username' => $username, 'email' => $email]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $identifier = $data['identifier'] ?? '';
        $password = $data['password'] ?? '';

        if (!$identifier || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing credentials']);
            return;
        }

        $user = null;
        $source = 'user';

        try {
            $legacyUser = dbFetch(
                'SELECT UserID as id, username, Email as email, password FROM `user` WHERE username = ? OR Email = ?',
                [$identifier, $identifier]
            );

            if ($legacyUser && password_verify($password, $legacyUser['password'] ?? '')) {
                $user = $legacyUser;
            }
        } catch (Exception $e) {
            // ignore
        }

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid username/email or password']);
            return;
        }

        $token = JWT::encode(['id' => $user['id'], 'source' => $source]);
        echo json_encode([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    }

    public static function profile() {
        $user = JWT::required();
        self::ensureProfileColumn('user');

        $profile = null;

        try {
            $profile = dbFetch(
                'SELECT UserID as id, COALESCE(username, Name) as username, Email as email, Name as name,
                        SubscriptionType,
                        JoinDate as created_at, profile_url
                 FROM `user`
                 WHERE UserID = ?',
                [$user['id']]
            );
        } catch (Exception $e) {
            // Fallback below.
        }

        if (!$profile) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        $sub = null;
        try {
            $sub = dbFetch(
                'SELECT Type as plan_type,
                        CASE WHEN PaymentStatus = "Paid" THEN "active" ELSE LOWER(COALESCE(PaymentStatus, "free")) END as sub_status,
                        PaymentStatus as payment_status,
                        StartDate as start_date,
                        EndDate as end_date
                 FROM subscription
                 WHERE UserID = ?
                 ORDER BY SubID DESC
                 LIMIT 1',
                [$user['id']]
            );
        } catch (Exception $e) {
            // Subscription data is optional for profile rendering.
        }

        if ($sub) {
            $profile = array_merge($profile, $sub);
        } else {
            $profile['plan_type'] = $profile['SubscriptionType'] ?? 'Free';
            $profile['sub_status'] = 'free';
        }

        echo json_encode($profile);
    }

    public static function profilePicture() {
        $user = JWT::required();
        self::ensureProfileColumn('user');
        $data = json_decode(file_get_contents('php://input'), true);
        $url = trim($data['url'] ?? '');

        if (!$url) {
            http_response_code(400);
            echo json_encode(['error' => 'Image URL is required']);
            return;
        }

        try {
            // Update legacy `user` table only
            dbQuery('UPDATE `user` SET profile_url = ? WHERE UserID = ?', [$url, $user['id']]);

            echo json_encode(['ok' => true, 'profile_url' => $url]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function savedSongs() {
        JWT::required();

        try {
            $songs = dbFetchAll(
                'SELECT s.SongID AS id, s.Title AS title, s.ArtistID AS artist_id, s.AlbumID AS album_id, s.Duration AS duration, s.Genre AS genre, NULL AS audio_url, NULL AS cover_url, ar.Name AS artist_name
                 FROM song s
                 LEFT JOIN artist ar ON ar.ArtistID = s.ArtistID'
            );
            echo json_encode($songs);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
