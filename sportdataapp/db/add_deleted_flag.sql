-- チャットメッセージに削除フラグを追加
ALTER TABLE chat_tbl ADD COLUMN is_deleted TINYINT(1) DEFAULT 0 AFTER message;
ALTER TABLE chat_tbl ADD COLUMN deleted_at DATETIME NULL AFTER is_deleted;

-- 既存のレコードは削除されていないとする
UPDATE chat_tbl SET is_deleted = 0 WHERE is_deleted IS NULL;
