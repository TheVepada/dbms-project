<?php
// Central routing for VepMune PHP backend

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path);

// Simple router
if (preg_match('#^/users/signup$#', $path) && $method === 'POST') {
	require 'controllers/UserController.php';
	UserController::signup();
} elseif (preg_match('#^/users/login$#', $path) && $method === 'POST') {
	require 'controllers/UserController.php';
	UserController::login();
} elseif (preg_match('#^/users/profile$#', $path) && $method === 'GET') {
	require 'controllers/UserController.php';
	UserController::profile();
} elseif (preg_match('#^/users/profile-picture$#', $path) && $method === 'POST') {
	require 'controllers/UserController.php';
	UserController::profilePicture();
} elseif (preg_match('#^/users/saved-songs$#', $path) && $method === 'GET') {
	require 'controllers/UserController.php';
	UserController::savedSongs();
} elseif (preg_match('#^/songs(?:/(\d+))?$#', $path, $m) && $method === 'GET') {
	require 'controllers/SongController.php';
	isset($m[1]) ? SongController::getById($m[1]) : SongController::getAll();
} elseif (preg_match('#^/songs/(\d+)/cover$#', $path, $m) && $method === 'POST') {
	require 'controllers/SongController.php';
	SongController::updateCover($m[1]);
} elseif (preg_match('#^/artists(?:/(\d+))?$#', $path, $m) && $method === 'GET') {
	require 'controllers/ArtistController.php';
	isset($m[1]) ? ArtistController::getById($m[1]) : ArtistController::getAll();
} elseif (preg_match('#^/artists/search$#', $path) && $method === 'GET') {
	require 'controllers/ArtistController.php';
	ArtistController::search();
} elseif (preg_match('#^/albums(?:/(\d+))?$#', $path, $m) && $method === 'GET') {
	require 'controllers/AlbumController.php';
	isset($m[1]) ? AlbumController::getById($m[1]) : AlbumController::getAll();
} elseif (preg_match('#^/albums/(\d+)/cover$#', $path, $m) && $method === 'POST') {
	require 'controllers/AlbumController.php';
	AlbumController::updateCover($m[1]);
} elseif (preg_match('#^/playlists(?:/(\d+))?$#', $path, $m) && $method === 'GET') {
	require 'controllers/PlaylistController.php';
	isset($m[1]) ? PlaylistController::getById($m[1]) : PlaylistController::getAll();
} elseif (preg_match('#^/playlists$#', $path) && $method === 'POST') {
	require 'controllers/PlaylistController.php';
	PlaylistController::create();
} elseif (preg_match('#^/playlists/(\d+)/songs(?:/(\d+))?$#', $path, $m) && $method === 'POST') {
	require 'controllers/PlaylistController.php';
	PlaylistController::addSong($m[1]);
} elseif (preg_match('#^/playlists/(\d+)/songs/(\d+)$#', $path, $m) && $method === 'DELETE') {
	require 'controllers/PlaylistController.php';
	PlaylistController::removeSong($m[1], $m[2]);
} elseif (preg_match('#^/logs/play$#', $path) && $method === 'POST') {
	require 'controllers/LogController.php';
	LogController::play();
} elseif (preg_match('#^/subscriptions/status$#', $path) && $method === 'GET') {
	require 'controllers/SubscriptionController.php';
	SubscriptionController::status();
} elseif (preg_match('#^/subscriptions/subscribe$#', $path) && $method === 'POST') {
	require 'controllers/SubscriptionController.php';
	SubscriptionController::subscribe();
} else {
	http_response_code(200);
	echo json_encode(['message' => 'VepMune PHP backend running']);
}
