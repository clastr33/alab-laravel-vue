-- Create a separate database for automated tests.
CREATE DATABASE IF NOT EXISTS `alab_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant the app user access to the test database.
GRANT ALL PRIVILEGES ON `alab_test`.* TO 'alab'@'%';

FLUSH PRIVILEGES;

