-- Database for integration tests: they recreate its tables from database/schema.sql on every run
CREATE DATABASE IF NOT EXISTS blog_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON blog_test.* TO 'blog'@'%';
