<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_6_6($module)
{
    // Dodaj lub zaktualizuj wartość konfiguracji dla kontrolera pozycji produktu
    if (!Configuration::updateValue('AJAX_POSITION_PRODUCT_CONTROLLER', 'PositionProduct')) {
        return false;
    }

    // Sprawdzenie, czy tabela z pozycjami już istnieje
    $sql = 'SHOW TABLES LIKE "'._DB_PREFIX_ . pSQL(_MKD_NAME_) . '_product_position"';
    $result = Db::getInstance()->executeS($sql);

    if (empty($result)) {
        $sql = 'CREATE TABLE `'._DB_PREFIX_ . pSQL(_MKD_NAME_) . '_product_position` (
            `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT(3) UNSIGNED NOT NULL,
            `id_lang` INT(3) UNSIGNED NOT NULL,
            `id_product` INT(11) UNSIGNED NOT NULL,
            `id_category` INT(11) UNSIGNED NOT NULL,
            `position` INT(11) NOT NULL DEFAULT 0,
            `active` TINYINT(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_product_category` (`id_product`, `id_category`, `id_shop`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

        // Wykonaj zapytanie SQL
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    }

    return true;
}

