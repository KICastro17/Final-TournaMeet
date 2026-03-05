-- setup.sql
-- Run this file once to create your database and tables

CREATE DATABASE IF NOT EXISTS ballsports;
USE ballsports;

-- Profiles table
CREATE TABLE IF NOT EXISTS profiles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    role        VARCHAR(100)    DEFAULT '',
    location    VARCHAR(100)    DEFAULT '',
    sport       VARCHAR(100)    DEFAULT '',
    position    VARCHAR(100)    DEFAULT '',
    team        VARCHAR(100)    DEFAULT '',
    region      VARCHAR(100)    DEFAULT '',
    level       VARCHAR(50)     DEFAULT '',
    member_since YEAR           DEFAULT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tournament history table
CREATE TABLE IF NOT EXISTS tournaments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    profile_id   INT             NOT NULL,
    name         VARCHAR(150)    NOT NULL,
    venue        VARCHAR(150)    DEFAULT '',
    date         DATE            DEFAULT NULL,
    result       ENUM('won', 'runner_up', 'active', 'upcoming') DEFAULT 'upcoming',
    type         ENUM('joined', 'organized') DEFAULT 'joined',
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);

-- Sports played table
CREATE TABLE IF NOT EXISTS sports_played (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    profile_id  INT             NOT NULL,
    sport_name  VARCHAR(100)    NOT NULL,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);

-- Achievements table
CREATE TABLE IF NOT EXISTS achievements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    profile_id  INT             NOT NULL,
    icon        VARCHAR(10)     DEFAULT '',
    title       VARCHAR(100)    NOT NULL,
    description VARCHAR(200)    DEFAULT '',
    earned      TINYINT(1)      DEFAULT 0,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);

-- Stats table (tournaments joined, won, organized, prize earnings)
CREATE TABLE IF NOT EXISTS profile_stats (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    profile_id          INT             NOT NULL UNIQUE,
    tournaments_joined  INT             DEFAULT 0,
    tournaments_won     INT             DEFAULT 0,
    organized           INT             DEFAULT 0,
    prize_earnings      DECIMAL(10,2)   DEFAULT 0.00,
    win_rate            INT             DEFAULT 0,
    attendance_rate     INT             DEFAULT 0,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
);

-- Add user_id column to link profiles to users table
ALTER TABLE profiles ADD COLUMN user_id INT UNIQUE AFTER id;
ALTER TABLE profiles ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
