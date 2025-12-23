-- 日記テーブルにtagsカラムを追加

ALTER TABLE diary_tbl 
ADD COLUMN tags TEXT NULL 
AFTER content;

-- 既存レコードのtagsをNULLまたは空文字に設定
UPDATE diary_tbl SET tags = '' WHERE tags IS NULL;
