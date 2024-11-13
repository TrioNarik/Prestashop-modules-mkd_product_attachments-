<?php
/*
* Model Danych dla Export Files Product
* 
* Walidacja
*/

class MKDExportFilesModel extends ObjectModel
{
    public $id;
    public $id_shop;
    public $id_lang;
    public $product_id;
    public $format;
    public $view_count;
    public $download_count;    

    public static $definition = array(
        'table' => _MKD_NAME_ . '_export_files_product',
        'primary' => 'product_id', // Klucz główny
        'multilang' => false, // Dla obsługi multi-lang
        'multishop' => false, // Dla obsługi multi-shop
        'fields' => array(
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                // 'required' => true,
            ],
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'product_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'format' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
            ],
            'view_count' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                // 'required' => true,
            ],
            'download_count' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                // 'required' => true,
            ],
            
        ),
    );

    /**
     * Get ID export file Counter for Product
     *
     * @param int $shop_ID
     * @param int $lang_ID
     * @param int $product_ID
     * @param string $format
     * 
     * @return int|false
     */
    public static function getExportFileCounterForProductByFormat($shop_ID, $lang_ID, $product_ID, $format)
    {
        $sql = 'SELECT `id`
            FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
            WHERE id_shop = ' . (int)$shop_ID . ' AND id_lang = ' . (int)$lang_ID . ' AND product_id = ' . (int)$product_ID. ' AND format = "' . pSQL($format) . '"';

        // zwraca wartość
        return (int)Db::getInstance()->getValue($sql);

    }


    /**
     * Update Counter for Product
     *
     * @param int $counter_ID
     * 
     * @return true|false
     */
    public static function updateExportFileCounterForProductByFormat($counter_ID)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
            SET `download_count` = `download_count` + 1
            WHERE `id` = ' . (int)$counter_ID;

        return Db::getInstance()->execute($sql);
    }


    /**
     * Pobierz wartość 'download_count' dla danego $counter_ID
     *
     * @param int $counter_ID
     * @return int|false
     */
    public static function getDownloadCounterExportFileForProduct($counter_ID)
    {
        $sql = 'SELECT `download_count`
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
                WHERE `id` = ' . (int)$counter_ID;

        return (int)Db::getInstance()->getValue($sql);
    }


}


