-- Nexora'dan senkron edilen etkinlikler icin yerel tablo.
-- Icerik cron ile gunde iki kez tazelenir (bkz. _tools/sync_events.php).
-- Kaynak kayit id'si (UUID) benzersiz anahtardir; tekrar calistirmada guncellenir.

CREATE TABLE IF NOT EXISTS `nexora_events` (
  `event_id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `remote_id`         CHAR(36)     NOT NULL COMMENT 'Nexora UUID',
  `slug`              VARCHAR(255) NOT NULL,
  `name`              VARCHAR(255) NOT NULL,
  `code`              VARCHAR(50)      NULL,
  `category_name`     VARCHAR(150)     NULL,
  `category_color`    VARCHAR(20)      NULL,
  `venue_type`        VARCHAR(20)      NULL,
  `facility_name`     VARCHAR(255)     NULL,
  `hall_name`         VARCHAR(255)     NULL,
  `location_name`     VARCHAR(255)     NULL,
  `latitude`          DECIMAL(10,7)    NULL,
  `longitude`         DECIMAL(10,7)    NULL,
  `start_date`        DATE         NOT NULL,
  `end_date`          DATE             NULL,
  `start_time`        TIME             NULL,
  `end_time`          TIME             NULL,
  `is_single_day`     TINYINT(1)   NOT NULL DEFAULT 1,
  `gender`            VARCHAR(20)      NULL,
  `min_age`           INT              NULL,
  `max_age`           INT              NULL,
  `capacity`          INT              NULL,
  `registered_count`  INT              NULL,
  `available_capacity` INT             NULL,
  `registration_open` TINYINT(1)   NOT NULL DEFAULT 0,
  `is_paid`           TINYINT(1)   NOT NULL DEFAULT 0,
  `price_info`        VARCHAR(255)     NULL,
  `resident_only`     TINYINT(1)   NOT NULL DEFAULT 0,
  `description`       MEDIUMTEXT       NULL,
  `image_url`         VARCHAR(500)     NULL,
  `session_count`     INT          NOT NULL DEFAULT 0,
  `synced_at`         DATETIME     NOT NULL,
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `uq_remote_id` (`remote_id`),
  KEY `ix_slug` (`slug`),
  KEY `ix_end_date` (`end_date`),
  KEY `ix_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Senkron calismalarinin kaydi (izleme icin)
CREATE TABLE IF NOT EXISTS `nexora_sync_log` (
  `log_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `started_at`  DATETIME     NOT NULL,
  `finished_at` DATETIME         NULL,
  `status`      VARCHAR(20)  NOT NULL COMMENT 'ok | error',
  `fetched`     INT          NOT NULL DEFAULT 0,
  `inserted`    INT          NOT NULL DEFAULT 0,
  `updated`     INT          NOT NULL DEFAULT 0,
  `deleted`     INT          NOT NULL DEFAULT 0,
  `message`     VARCHAR(500)     NULL,
  PRIMARY KEY (`log_id`),
  KEY `ix_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
