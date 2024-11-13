<?php

$sql = [];
// -- Usuwanie tabel
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_product_position`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_programs`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_hooks`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_formats`';
// Klucze obce z .._types(!)
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_types_lang`'; 
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_types_shop`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_types`';
// Klucze obce z .._extra_product_fields(!)
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_extra_product_fields_lang`'; 
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_extra_product_fields_shop`';
// Extra product fields Value
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_extra_product_fields_value`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_extra_product_fields`';  // Klucz obcy dla pozostałych tabel

// Export Files of Product
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'_export_files_product`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_ . pSQL(_MKD_NAME_) .'`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}