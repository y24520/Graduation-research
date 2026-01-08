-- tennis_db 初期セットアップSQL（XAMPP / MySQL(MariaDB)想定）
-- 使い方: phpMyAdmin でこのファイルをインポート

-- 既存データを消して作り直す場合は DROP を有効にしてください。
-- DROP DATABASE IF EXISTS tennis_db;

CREATE DATABASE IF NOT EXISTS tennis_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE tennis_db;

-- 既存テーブルを作り直す場合（必要なら）
DROP TABLE IF EXISTS actions;
DROP TABLE IF EXISTS games;

-- 試合テーブル
CREATE TABLE games (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_a VARCHAR(255) NOT NULL,
  team_b VARCHAR(255) NOT NULL,
  games_a INT NOT NULL DEFAULT 0,
  games_b INT NOT NULL DEFAULT 0,

  -- 選手名（シングルなら a2/b2 は空でもOK）
  player_a1 VARCHAR(255) NULL,
  player_a2 VARCHAR(255) NULL,
  player_b1 VARCHAR(255) NULL,
  player_b2 VARCHAR(255) NULL,

  -- AIコーチのコメント（機能で UPDATE される）
  ai_comment TEXT NULL,

  match_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- アクション（ポイント経過）テーブル
CREATE TABLE actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_id INT NOT NULL,
  player_name VARCHAR(255) NOT NULL,
  action_type VARCHAR(255) NOT NULL,
  score_a INT NOT NULL,
  score_b INT NOT NULL,

  -- 任意（将来便利）
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_actions_game_id (game_id),
  CONSTRAINT fk_actions_game
    FOREIGN KEY (game_id) REFERENCES games(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
