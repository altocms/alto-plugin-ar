-- ----------------------------------------------------------------------------------------------
-- install.sql
-- Файл таблиц баз данных плагина Ar
--
-- @author      Андрей Г. Воронов <andreyv@gladcode.ru>
-- @copyrights  Copyright © 2014, Андрей Г. Воронов
--              Является частью плагина Ar
-- @version     0.0.1 от 30.07.2014 21:09
-- ----------------------------------------------------------------------------------------------

# ХРАНЕНИЕ ТОКЕНОВ ПРОВАЙДЕРОВ
CREATE TABLE IF NOT EXISTS `prefix_user_token` (
  `token_id`               INT(11)          NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `token_data`             VARCHAR(250)     NOT NULL UNIQUE,
  `token_data_secret`      VARCHAR(250)     NOT NULL,
  `token_provider_name`    VARCHAR(50)      NOT NULL,
  `token_provider_user_id` VARCHAR(50)      NOT NULL,
  `token_user_id`          INT(10) UNSIGNED NOT NULL,
  `token_expire`           INT(10) UNSIGNED
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;


# НАСТРОЙКИ РЕПОСТИНГА ПОЛЬЗОВАТЕЛЕЙ
CREATE TABLE IF NOT EXISTS `prefix_repost_setting` (
  `setting_id`       INT(11)                        NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `setting_user_id`  INT(10) UNSIGNED               NOT NULL,
  `setting_token_id` INT(10) UNSIGNED               NOT NULL,
  `setting_type_id`  ENUM('wall', 'post', 'status') NOT NULL,
  `setting_value`    TINYINT(1)                     NOT NULL
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

# ТЕКТОВКИ РЕПОСТИНГА ТОПИКА
CREATE TABLE IF NOT EXISTS `prefix_repost_text` (
  `text_id`      INT(11)          NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `text_user_id` INT(10) UNSIGNED NOT NULL,
  `text_data`    VARCHAR(200)     NOT NULL,
  `text_type_id` ENUM('topic')    NOT NULL
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

# ДРУЗЬЯ ИЗ СОЦИАЛЬНЫХ СЕТЕЙ, НАЙДЕННЫЕ НА САЙТЕ
CREATE TABLE IF NOT EXISTS `prefix_searched_user` (
  `searched_id`       INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `searched_user_id`  INT(10) UNSIGNED,
  `searched_token_id` INT(10) UNSIGNED,
  `searched_time`     INT(10) UNSIGNED
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;

# ШАБЛОНА КОНТАКТА ДЛЯ МОЕГО МИРА
INSERT INTO `prefix_user_field` (`id`, `type`, `name`, `title`, `pattern`)
VALUES (NULL, 'social', 'mm', 'Мой Мир', '<a href="http://my.mail.ru/mail/{*}/" rel="nofollow">{*}</a>');

# ШАБЛОНА КОНТАКТА ДЛЯ ГУГЛА
INSERT INTO `prefix_user_field` (`id`, `type`, `name`, `title`, `pattern`)
VALUES (NULL, 'social', 'google', 'Google Plus', '<a href="https://plus.google.com/{*}" rel="nofollow">{*}</a>');

# ШАБЛОНА КОНТАКТА ДЛЯ ГИТХАБА
INSERT INTO `prefix_user_field` (`id`, `type`, `name`, `title`, `pattern`)
VALUES (NULL, 'social', 'guthub', 'GitHub', '<a href="https://github.com/{*}" rel="nofollow">{*}</a>');

# ШАБЛОНА КОНТАКТА ДЛЯ ЛИНКЕДИНА
INSERT INTO `prefix_user_field` (`id`, `type`, `name`, `title`, `pattern`)
VALUES (NULL, 'social', 'linkedin', 'LinkedIn',
        '<a href="https://www.linkedin.com/profile/view?id={*}" rel="nofollow">{*}</a>');

# ШАБЛОНА КОНТАКТА ДЛЯ ИНСТАГРАММА
INSERT INTO `prefix_user_field` (`id`, `type`, `name`, `title`, `pattern`)
VALUES (NULL, 'social', 'instagram', 'Instagram', '<a href="http://instagram.com/{*}" rel="nofollow">{*}</a>');