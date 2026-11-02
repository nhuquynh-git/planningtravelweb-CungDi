-- Isolated PHPUnit database. Safe to re-run. Does not modify travel_planner_db.
CREATE DATABASE IF NOT EXISTS travel_planner_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON travel_planner_test.* TO 'travel_user'@'%';
FLUSH PRIVILEGES;
