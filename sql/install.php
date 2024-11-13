<?php
$sql = [];

// Tabela do programów dofinansowania: (Mój Prąd, Czyste powietrze...)
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_programs` (
    `id` INT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `shop_id` INT(3) UNSIGNED NOT NULL,
    `lang_id` INT(3) UNSIGNED NOT NULL,
    `country_id` INT(5) UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `comment` VARCHAR(500) NOT NULL,
    `version` VARCHAR(20) NOT NULL,
    `valid_date` DATE,
    `position` INT(3) NOT NULL,
    `active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

// Tabela dostępnych hook'ów dla typów załączników
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_hooks` (
    `id` INT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
    `shop_id` INT(3) UNSIGNED NOT NULL,
    `lang_iso` VARCHAR(3) NOT NULL,
    `value` VARCHAR(35) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

// Tabela typów załączników (img, pdf, zip...)
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_formats` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `shop_id` INT(3) UNSIGNED NOT NULL,
    `lang_iso` VARCHAR(3) NOT NULL,
    `value` VARCHAR(10) NOT NULL,
    `name` VARCHAR(50) NOT NULL,
    `active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

// Tabele Grup dla załączników (3) => multi-lang i multi-shop => FOREIGN KEY
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_types` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_types_lang` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_lang` INT(3) UNSIGNED NOT NULL,
    `type_id` INT(5) UNSIGNED NOT NULL,
    `format` INT(2) UNSIGNED NOT NULL,
    `hook` INT(2) NOT NULL,
    `user_groups` TEXT NOT NULL,
    `product_brands` TEXT NOT NULL,
    `product_categories` TEXT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `extra_url` TINYINT(1) DEFAULT 0,
    `program` INT(2) DEFAULT 0,
    PRIMARY KEY (`id`,`id_lang`),
    INDEX `idx_type_id` (`type_id`),
    FOREIGN KEY (`type_id`) REFERENCES `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_types_shop` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` INT(3) UNSIGNED NOT NULL,
    `type_id` INT(5) UNSIGNED NOT NULL,
    `position` INT(3) NOT NULL,
    `files` INT(6) DEFAULT 0,
    PRIMARY KEY (`id`, `id_shop`),
    INDEX `idx_type_id` (`type_id`),
    FOREIGN KEY (`type_id`) REFERENCES `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

// Tabela załączników w Produktach (== MAIN TABLE ==)
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) ."` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` INT(3) UNSIGNED NOT NULL,
    `id_lang` INT(3) UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `type_id` INT(5) UNSIGNED NOT NULL,
    `file_name` VARCHAR(150),
    `file_url` VARCHAR(255),
    `comment` VARCHAR(500),
    `position` INT(3),
    `data_add` DATETIME,
    `data_upd` DATETIME,
    `view_count` INT(10) UNSIGNED DEFAULT 0,
    `download_count` INT(10) UNSIGNED DEFAULT 0,
    `export_count` INT(10) UNSIGNED DEFAULT 0,
    `active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

// ============================================================================================
// Tabele EXTRA pól Produktu (== Export ==)
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_)  . "_extra_product_fields` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `front_view` TINYINT(1) DEFAULT 0,
    `html_content` TINYINT(1) DEFAULT 0,
    `active` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_)  . "_extra_product_fields_shop` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` INT(3) UNSIGNED NOT NULL,
    `field_id` INT(5) UNSIGNED NOT NULL,
    `hook_id` INT(1) UNSIGNED DEFAULT 1,
    `position` INT(3) NOT NULL,
    PRIMARY KEY (`id`, `id_shop`),
    INDEX `idx_field_id` (`field_id`),
    FOREIGN KEY (`field_id`) REFERENCES `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_extra_product_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_extra_product_fields_lang` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_lang` INT(3) UNSIGNED NOT NULL,
    `field_id` INT(5) UNSIGNED NOT NULL,
    `title` VARCHAR(50) NOT NULL,
    `data_upd` DATETIME,
    PRIMARY KEY (`id`,`id_lang`),
    INDEX `idx_field_id` (`field_id`),
    FOREIGN KEY (`field_id`) REFERENCES `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_extra_product_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

// ==============================================
// Tabela wartości Extra pól dla każdego Produktu
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_extra_product_fields_value` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` INT(3) UNSIGNED NOT NULL,
    `field_id` INT(5) UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `id_lang` INT(3) UNSIGNED NOT NULL,
    `value` VARCHAR(3250) NOT NULL,
    `data_upd` DATETIME,
    `active` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`, `id_lang`),
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_field_id` (`field_id`),
    FOREIGN KEY (`field_id`) REFERENCES `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_extra_product_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

// ==============================================
// Tabela liczników export file
$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_ . pSQL(_MKD_NAME_) . "_export_files_product` (
    `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` INT(3) UNSIGNED NOT NULL,
    `id_lang` INT(3) UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `format` VARCHAR(6) NOT NULL,
    `view_count` INT(10) UNSIGNED DEFAULT 0,
    `download_count` INT(10) UNSIGNED DEFAULT 0,
    PRIMARY KEY (`id`, `product_id`, `id_lang`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}