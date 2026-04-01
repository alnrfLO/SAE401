-- =============================================================
--  FAV DATABASE SCHEMA — Sécurisé & Production-Ready
--  Projet SAE401 — BUT 2 MMI
--  Inspiré d'un RS type Twitter / Blog
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Fix #1071 : permet les index > 767 octets avec utf8mb4
-- SET GLOBAL innodb_large_prefix = 1;
-- SET GLOBAL innodb_file_format  = 'Barracuda';

-- ─────────────────────────────────────────────
-- 1. DATABASE
-- ─────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `fav`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `fav`;

-- ─────────────────────────────────────────────
-- 2. TABLE : users
--    Stocke tous les comptes (user + admin)
--    Le rôle détermine les droits d'accès
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username`        VARCHAR(50)  NOT NULL,
    `email`           VARCHAR(191) NOT NULL COMMENT 'max 191 car. utf8mb4 = 764 octets < limite 767',
    `password`        VARCHAR(255) NOT NULL COMMENT 'bcrypt hash — JAMAIS en clair',
    `role`            ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `avatar`          VARCHAR(500) DEFAULT NULL COMMENT 'chemin ou URL avatar',
    `bio`             TEXT         DEFAULT NULL,
    `country`         VARCHAR(100) DEFAULT NULL,
    `language`        VARCHAR(10)  DEFAULT 'fr',
    `is_active`       TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '0 = compte banni',
    `email_verified`  TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_email`    (`email`),
    UNIQUE KEY `uq_username` (`username`),
    INDEX `idx_role`         (`role`),
    INDEX `idx_is_active`    (`is_active`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 3. TABLE : sessions
--    Gestion sécurisée des tokens de session
--    côté serveur (alternative à $_SESSION seul)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sessions` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `token`       VARCHAR(128) NOT NULL UNIQUE COMMENT 'token généré avec random_bytes()',
    `ip_address`  VARCHAR(45)  DEFAULT NULL COMMENT 'IPv4 / IPv6',
    `user_agent`  TEXT         DEFAULT NULL,
    `expires_at`  DATETIME     NOT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_token`   (`token`),
    INDEX `idx_user_id` (`user_id`),
    CONSTRAINT `fk_sessions_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 4. TABLE : password_resets
--    Tokens "mot de passe oublié" à usage unique
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(191) NOT NULL COMMENT 'max 191 car. pour index utf8mb4',
    `token`      VARCHAR(128) NOT NULL UNIQUE,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    `expires_at` DATETIME     NOT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_email` (`email`),
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 5. TABLE : spots
--    La feature principale — les publications FAV
--    Chaque spot = une publication d'un user
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `spots` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED NOT NULL,
    `title`         VARCHAR(191) NOT NULL,
    `description`   TEXT         NOT NULL,
    `location`      VARCHAR(191) DEFAULT NULL COMMENT 'lieu du spot (ville, pays...)',
    `latitude`      DECIMAL(10, 8) DEFAULT NULL COMMENT 'coordonnées GPS',
    `longitude`     DECIMAL(11, 8) DEFAULT NULL,
    `image`         VARCHAR(500) DEFAULT NULL COMMENT 'chemin image principale',
    `category`      ENUM('voyage', 'nature', 'ville', 'gastronomie', 'culture', 'aventure', 'autre')
                    NOT NULL DEFAULT 'autre',
    `status`        ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    `views_count`   INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_user_id`  (`user_id`),
    INDEX `idx_status`   (`status`),
    INDEX `idx_category` (`category`),
    INDEX `idx_created`  (`created_at` DESC),
    CONSTRAINT `fk_spots_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 6. TABLE : spot_images
--    Galerie multi-images par spot (optionnel)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `spot_images` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `spot_id`    INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(500) NOT NULL,
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_spot_id` (`spot_id`),
    CONSTRAINT `fk_spot_images_spot`
        FOREIGN KEY (`spot_id`) REFERENCES `spots`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 7. TABLE : comments
--    Commentaires sous les spots — style Twitter/Blog
--    Supporte les réponses imbriquées (reply)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `comments` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `spot_id`    INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `parent_id`  INT UNSIGNED DEFAULT NULL COMMENT 'NULL = commentaire racine, sinon reply',
    `content`    TEXT         NOT NULL,
    `is_hidden`  TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = masqué par modération',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_spot_id`   (`spot_id`),
    INDEX `idx_user_id`   (`user_id`),
    INDEX `idx_parent_id` (`parent_id`),
    CONSTRAINT `fk_comments_spot`
        FOREIGN KEY (`spot_id`)   REFERENCES `spots`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_comments_user`
        FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_comments_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 8. TABLE : likes
--    Système de likes sur les spots ET les commentaires
--    Clé unique (user+spot|comment) empêche les doublons
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `likes` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `spot_id`    INT UNSIGNED DEFAULT NULL,
    `comment_id` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Un user ne peut liker qu'une fois par spot
    UNIQUE KEY `uq_like_spot`    (`user_id`, `spot_id`),
    -- Un user ne peut liker qu'une fois par commentaire
    UNIQUE KEY `uq_like_comment` (`user_id`, `comment_id`),

    INDEX `idx_spot_id`    (`spot_id`),
    INDEX `idx_comment_id` (`comment_id`),

    CONSTRAINT `fk_likes_user`
        FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_likes_spot`
        FOREIGN KEY (`spot_id`)    REFERENCES `spots`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_likes_comment`
        FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE,

    -- Au moins l'un des deux doit être renseigné
    CONSTRAINT `chk_like_target`
        CHECK (`spot_id` IS NOT NULL OR `comment_id` IS NOT NULL)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 9. TABLE : follows
--    S'abonner/suivre un autre utilisateur
--    Relation asymétrique (comme Twitter)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `follows` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `follower_id` INT UNSIGNED NOT NULL COMMENT 'celui qui suit',
    `followed_id` INT UNSIGNED NOT NULL COMMENT 'celui qui est suivi',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_follow` (`follower_id`, `followed_id`),
    INDEX `idx_followed_id` (`followed_id`),

    CONSTRAINT `fk_follows_follower`
        FOREIGN KEY (`follower_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_follows_followed`
        FOREIGN KEY (`followed_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    CONSTRAINT `chk_no_self_follow`
        CHECK (`follower_id` != `followed_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 10. TABLE : tags
--     Système de hashtags pour les spots
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tags` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(50)  NOT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_tag_name` (`name`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 11. TABLE : spot_tags (pivot)
--     Liaison many-to-many spots <-> tags
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `spot_tags` (
    `spot_id` INT UNSIGNED NOT NULL,
    `tag_id`  INT UNSIGNED NOT NULL,

    PRIMARY KEY (`spot_id`, `tag_id`),
    CONSTRAINT `fk_spot_tags_spot`
        FOREIGN KEY (`spot_id`) REFERENCES `spots`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spot_tags_tag`
        FOREIGN KEY (`tag_id`)  REFERENCES `tags`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 12. TABLE : reports
--     Signalements de contenu (spots ou commentaires)
--     Pour l'admin — modération
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reports` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `reporter_id` INT UNSIGNED NOT NULL,
    `spot_id`     INT UNSIGNED DEFAULT NULL,
    `comment_id`  INT UNSIGNED DEFAULT NULL,
    `reason`      ENUM('spam', 'harcelement', 'contenu inapproprie', 'fausse information', 'autre')
                  NOT NULL DEFAULT 'autre',
    `details`     TEXT         DEFAULT NULL,
    `status`      ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
    `reviewed_by` INT UNSIGNED DEFAULT NULL COMMENT 'admin qui a traite le signalement',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_reporter`   (`reporter_id`),
    INDEX `idx_status`     (`status`),

    CONSTRAINT `fk_reports_reporter`
        FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reports_spot`
        FOREIGN KEY (`spot_id`)     REFERENCES `spots`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reports_comment`
        FOREIGN KEY (`comment_id`)  REFERENCES `comments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reports_admin`
        FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 13. TABLE : notifications
--     Notifications en temps réel pour les users
--     (like, commentaire, follow, mention)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL COMMENT 'destinataire',
    `actor_id`    INT UNSIGNED NOT NULL COMMENT 'qui a declenche l action',
    `type`        ENUM('like_spot', 'like_comment', 'comment', 'reply', 'follow', 'mention')
                  NOT NULL,
    `spot_id`     INT UNSIGNED DEFAULT NULL,
    `comment_id`  INT UNSIGNED DEFAULT NULL,
    `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_user_id`  (`user_id`),
    INDEX `idx_is_read`  (`is_read`),

    CONSTRAINT `fk_notif_user`
        FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_notif_actor`
        FOREIGN KEY (`actor_id`)   REFERENCES `users`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_notif_spot`
        FOREIGN KEY (`spot_id`)    REFERENCES `spots`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_notif_comment`
        FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 14. TABLE : events (conservé de l'original)
--     Événements organisés par les users
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `events` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `title`       VARCHAR(191) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    `location`    VARCHAR(191) DEFAULT NULL,
    `event_date`  DATETIME     NOT NULL,
    `type`        ENUM('private', 'shared', 'public') NOT NULL DEFAULT 'public',
    `status`      ENUM('pending', 'accepted', 'refused') NOT NULL DEFAULT 'pending',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_user_id`    (`user_id`),
    INDEX `idx_event_date` (`event_date`),
    CONSTRAINT `fk_events_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 15. DONNÉES DE TEST — Utilisateurs par défaut
--     Admin + User de démonstration
--     Passwords hashés avec PASSWORD_BCRYPT
--     admin123 → $2y$12$...  |  user123 → $2y$12$...
-- ─────────────────────────────────────────────

-- Pour générer les hashs en PHP :
-- echo password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
-- echo password_hash('user123',  PASSWORD_BCRYPT, ['cost' => 12]);

INSERT INTO `users` (`username`, `email`, `password`, `role`, `bio`, `country`, `language`, `is_active`, `email_verified`)
VALUES
    (
        'admin',
        'admin@fav.fr',
        -- Mot de passe : admin123 (à changer en prod !)
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        'Administrateur FAV',
        'France',
        'fr',
        1, 1
    ),
    (
        'demo_user',
        'user@fav.fr',
        -- Mot de passe : user123
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'user',
        'Voyageur passionné 🌍',
        'France',
        'fr',
        1, 1
    );

-- Tags de base
INSERT INTO `tags` (`name`) VALUES
    ('voyage'), ('nature'), ('citytrip'), ('roadtrip'),
    ('foodie'), ('sunset'), ('montagne'), ('plage'),
    ('architecture'), ('culture'), ('aventure'), ('caché');

SET FOREIGN_KEY_CHECKS = 1;




ALTER TABLE users ADD COLUMN status VARCHAR(100) DEFAULT "Hey, I'm on FAV!";
-- =============================================================
-- FIN DU SCHÉMA FAV DATABASE
-- =============================================================
