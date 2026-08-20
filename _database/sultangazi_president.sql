-- Sultangazi Belediyesi ana sitesinin /api/mobile servisinden senkron edilen
-- baskan icerikleri (ozgecmis, mesaj) ve genel bilgiler.
-- Icerik cron ile gunde iki kez tazelenir (bkz. _tools/sync_president.php).

CREATE TABLE IF NOT EXISTS `sultangazi_president_contents` (
  `content_id`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(255) NOT NULL,
  `name`        VARCHAR(255) NOT NULL,
  `description` MEDIUMTEXT       NULL,
  `image_url`   VARCHAR(500)     NULL,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `synced_at`   DATETIME     NOT NULL,
  PRIMARY KEY (`content_id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Baskan genel bilgileri (tek satir)
CREATE TABLE IF NOT EXISTS `sultangazi_president_info` (
  `info_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_surname` VARCHAR(255)     NULL,
  `sub_title`    VARCHAR(255)     NULL,
  `image_url`    VARCHAR(500)     NULL,
  `banner_url`   VARCHAR(500)     NULL,
  `social_media` TEXT             NULL COMMENT 'JSON',
  `synced_at`    DATETIME     NOT NULL,
  PRIMARY KEY (`info_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
