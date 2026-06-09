-- CineByte Database Schema
-- Run once: import via phpMyAdmin or run: mysql -u root -p < db/schema.sql

CREATE DATABASE IF NOT EXISTS cinebyte
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE cinebyte;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    theme         VARCHAR(10)  DEFAULT 'dark',
    notifications TINYINT(1)   DEFAULT 1,
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS watchlist (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    tmdb_id     INT          NOT NULL,
    title       VARCHAR(255) NOT NULL,
    poster_path VARCHAR(500) DEFAULT NULL,
    added_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_watchlist (user_id, tmdb_id)
);

CREATE TABLE IF NOT EXISTS user_lists (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    list_name  VARCHAR(100) NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS list_movies (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    list_id     INT          NOT NULL,
    tmdb_id     INT          NOT NULL,
    title       VARCHAR(255) NOT NULL,
    poster_path VARCHAR(500) DEFAULT NULL,
    release_date VARCHAR(20) DEFAULT NULL,
    vote_average DECIMAL(3,1) DEFAULT NULL,
    added_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (list_id) REFERENCES user_lists(id) ON DELETE CASCADE,
    UNIQUE KEY unique_list_movie (list_id, tmdb_id)
);

CREATE TABLE IF NOT EXISTS journals (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT       NOT NULL,
    tmdb_id    INT       DEFAULT NULL,
    youtube_id VARCHAR(50) DEFAULT NULL,
    title      VARCHAR(255) NOT NULL,
    rating     TINYINT   NOT NULL DEFAULT 3,
    review     TEXT      NOT NULL,
    created_at DATETIME  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

