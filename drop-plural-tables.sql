-- WARNING: Run only after backing up your database.
-- This script drops the duplicate/legacy-plural tables that conflict with
-- the application's legacy singular tables. Data in these plural tables
-- will be permanently removed.

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `albums`;
DROP TABLE IF EXISTS `artists`;
DROP TABLE IF EXISTS `playlists`;
DROP TABLE IF EXISTS `playlist_songs`;
DROP TABLE IF EXISTS `songs`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS=1;

-- After running, verify the application works and that singular tables
-- (`album`, `artist`, `playlist`, `playlistsong`, `song`, `user`) contain
-- the expected data. If you need a data-merge script instead of a straight
-- drop, tell me and I will prepare a careful migration that maps columns.