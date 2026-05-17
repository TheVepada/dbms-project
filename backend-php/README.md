# VepMune Backend (PHP)

Lightweight PHP backend for VepMune music streaming app, connected to `vepmune` MySQL database.

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Apache with `mod_rewrite` (for URL rewriting) or PHP built-in server

## Setup

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` with your database credentials if needed (defaults work for localhost).

3. Ensure database is imported:
   ```bash
   mysql -u root -p vepmune < ../vepmune.sql
   ```

## Run the Backend

### Option A: PHP Built-in Server (Development)
```bash
cd backend-php
php -S localhost:8000
```
Then point the frontend to `http://localhost:8000/api`.

### Option B: Apache
1. Configure Apache to serve this folder as a virtual host or in `htdocs`.
2. Ensure `.htaccess` is present and `mod_rewrite` is enabled.
3. Access via your Apache URL (e.g., `http://localhost/vepmune/backend-php/index.php/api/songs`).

## Endpoints

Same as Node.js backend. All routes at `/api/...`:

### Users
- `POST /api/users/signup` - Create account
- `POST /api/users/login` - Login (returns JWT)
- `GET /api/users/profile` - Get user profile (auth required)
- `POST /api/users/profile-picture` - Update profile picture (auth required)
- `GET /api/users/saved-songs` - Get saved songs (auth required)

### Songs
- `GET /api/songs` - List songs (filter by `?artist_id=1`)
- `GET /api/songs/:id` - Get song by ID
- `POST /api/songs/:id/cover` - Update song cover

### Artists
- `GET /api/artists` - List artists (search with `?q=name`)
- `GET /api/artists/search?q=name` - Search artists
- `GET /api/artists/:id` - Get artist with albums and songs

### Albums
- `GET /api/albums` - List albums
- `GET /api/albums/:id` - Get album with songs

### Playlists (auth required)
- `GET /api/playlists` - Get user's playlists
- `POST /api/playlists` - Create playlist
- `GET /api/playlists/:id` - Get playlist with songs
- `POST /api/playlists/:id/songs` - Add song to playlist
- `DELETE /api/playlists/:id/songs/:sid` - Remove song from playlist

### Logs (auth required)
- `POST /api/logs/play` - Log a play event

### Subscriptions (auth required)
- `GET /api/subscriptions/status` - Get subscription status
- `POST /api/subscriptions/subscribe` - Subscribe to plan

## Database Tables Used

From `vepmune.sql`:
- `user` - User accounts
- `users` - Alternative user table (fallback)
- `songs` - Song catalog
- `artists` - Artist info
- `albums` - Albums
- `playlists` - User playlists
- `playlist_songs` - Playlist songs mapping
- `streaminglog` - Play history
- `subscription` - Subscription info

## Authentication

JWT tokens are issued on login. Include in requests as:
```
Authorization: Bearer <token>
```

## Troubleshooting

- **404 errors**: Ensure `mod_rewrite` is enabled or use `php -S` for built-in server.
- **Database errors**: Check `.env` credentials and ensure MySQL is running.
- **CORS errors**: Headers are set in `index.php` (if frontend is on different port/domain).
- **Invalid JWT**: Token has expired or signature is wrong. Re-login to get a new token.
