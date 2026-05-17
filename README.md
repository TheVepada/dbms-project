# VepMune 🎵

A full-stack Spotify-like music streaming app. Available in two backend flavors:
- **Node.js/Express** (original) — `backend/`
- **PHP** (lightweight) — `backend-php/`

---

## Quick Start

### 1. Database
```sql
CREATE DATABASE vepmune;
mysql -u root -p vepmune < vepmune.sql
```

### 2. Backend (Choose One)

#### Option A: Node.js Backend (port 4000)
```bash
cd backend
npm install
npm run dev
```

#### Option B: PHP Backend (port 8000)
```bash
cd backend-php
php -S localhost:8000
```

### 3. Frontend (port 5173)
Before starting, update `frontend/.env` to match your backend:
```env
# For Node.js backend:
VITE_API_URL=http://localhost:4000/api

# For PHP backend:
VITE_API_URL=http://localhost:8000/api
```

Then start:
```bash
cd frontend
npm install
npm run dev
```

Open http://localhost:5173.

---

## Backends Comparison

| Feature | Node.js | PHP |
|---------|---------|-----|
| Runtime | Node.js 16+ | PHP 7.4+ |
| Framework | Express.js | Vanilla PHP |
| Port | 4000 | 8000 |
| Setup | `npm install` | Zero setup (built-in server) |
| Features | Full feature parity | Full feature parity |
| Production | Docker-ready | Apache/Nginx |

Both backends use the same database (`vepmune.sql`) and expose identical API routes.

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/users/signup` | ❌ | Create account |
| POST | `/api/users/login` | ❌ | Get JWT token |
| GET | `/api/users/profile` | ✅ | User + subscription |
| GET | `/api/users/saved-songs` | ✅ | Liked tracks |
| GET | `/api/artists` | ❌ | All artists |
| GET | `/api/artists/search?q=` | ❌ | Search by name |
| GET | `/api/artists/:id` | ❌ | Artist + albums + songs |
| GET | `/api/albums` | ❌ | All albums |
| GET | `/api/albums/:id` | ❌ | Album + tracklist |
| GET | `/api/songs?genre=&artist_id=` | ❌ | Filtered songs |
| GET | `/api/playlists` | ✅ | User playlists |
| POST | `/api/playlists` | ✅ | Create playlist |
| POST | `/api/playlists/:id/songs` | ✅ | Add song |
| DELETE | `/api/playlists/:id/songs/:sid` | ✅ | Remove song |
| POST | `/api/logs/play` | ✅ | Log play event |
| GET | `/api/subscriptions/status` | ✅ | Current plan |
| POST | `/api/subscriptions/subscribe` | ✅ | Subscribe (mock) |

---

## Azure Blob Storage

Store audio files and cover art in Azure Blob Storage.
Save the public/SAS URLs directly into `songs.audio_url` and `songs.cover_url` / `albums.cover_url` columns.
The frontend `Player.jsx` passes `song.audio_url` directly to the HTML `<audio>` element.

### Image uploads

Use the authenticated upload endpoint to send an image and receive a blob URL back:

```bash
curl -X POST http://localhost:4000/api/uploads \
	-H "Authorization: Bearer <TOKEN>" \
	-F "file=@cover.jpg" \
	-F "type=artists"
```

Profile images are saved through `POST /api/users/profile-picture` with JSON:

```json
{ "url": "https://<account>.blob.core.windows.net/<container>/..." }
```

## Deployment (run 24/7)

Quick Docker-based setup to publish the app on a VPS or cloud VM. This repository includes `docker-compose.yml`, backend and frontend `Dockerfile`s and an `nginx` config to serve the built frontend and proxy `/api` to the backend.

1. Create an `.env` with production secrets (example):

```env
MYSQL_ROOT_PASSWORD=change_me_root
MYSQL_DATABASE=vepmune
MYSQL_USER=vepmune
MYSQL_PASSWORD=change_me_db
AZURE_STORAGE_CONNECTION_STRING=DefaultEndpointsProtocol=... # if using Azure blob uploads
CLIENT_URL=http://your-domain.com
PORT=4000
JWT_SECRET=change_this_secret
```

2. Build and start services (on a server with Docker and Docker Compose):

```bash
docker compose up -d --build
```

3. App URLs:
- Frontend: http://<server-ip>/ (nginx on port 80)
- Backend API: http://<server-ip>:4000/api/

Notes:
- Set `CLIENT_URL` to the frontend origin or the domain you will use. 
- For production MySQL, consider using a managed DB and update `DB_HOST` accordingly rather than running the `db` container.
- To expose the services publicly, open port 80 (HTTP) and 443 (HTTPS) and configure a reverse proxy or SSL (Certbot / nginx) on the host.

CI / automatic deploy (recommended)

The repository includes a GitHub Actions workflow that builds and publishes images to GitHub Container Registry and can SSH‑deploy to your VPS.

Required repository secrets (Settings → Secrets):
- `DEPLOY_HOST` — your server IP or hostname
- `DEPLOY_USER` — SSH username
- `DEPLOY_SSH_KEY` — private SSH key (PEM) with access to the server
- `DEPLOY_SSH_PORT` — optional (defaults to 22)
- `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_SSL`, `CLIENT_URL`, `JWT_SECRET`, `AZURE_STORAGE_CONNECTION_STRING`

On push to `main`, Actions will:
1. Build backend/frontend Docker images and publish to `ghcr.io/<your-org>/...`.
2. SSH to your VPS, write a `docker-compose.prod.yml` and `.env` using the above secrets, then run `docker compose up -d`.

Server prerequisites
- Docker and `docker compose` must be installed on the VPS (and the SSH user must be able to run Docker commands).
- Add the VPS public IP to your Azure MySQL firewall or ensure network access.



Artist images are resolved by convention from blob storage as `artists/<username>.jpg`.

---

## Tech Stack

- **Backend**: Node.js, Express, MySQL2, JWT, bcryptjs
- **Frontend**: React 18, Vite, React Router v6, Axios
- **Database**: Azure MySQL (SSL)
- **Storage**: Azure Blob Storage (URLs in DB)
