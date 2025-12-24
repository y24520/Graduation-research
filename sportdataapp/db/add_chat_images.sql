-- チャットテーブルに画像添付カラムを追加
ALTER TABLE chat_tbl 
ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER message,
ADD COLUMN image_name VARCHAR(255) DEFAULT NULL AFTER image_path;
