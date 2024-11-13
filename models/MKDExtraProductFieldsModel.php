<?php
/*
* Model Danych dla Extra Fields => multishop/multilang => PRIMARY KEY()
* 
* Model Extra Fields Value dla Produktów
*/

class MKDExtraProductFieldsModel extends ObjectModel
{
    public $id;
    public $front_view;
    public $active;

    public static $definition = array(
        'table' => _MKD_NAME_ . '_extra_product_fields',
        'primary' => 'id',
        // 'multilang' => true,
        'multishop' => true,
        'fields' => array(
            'front_view' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                // 'required' => true,
            ],
            'html_content' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                // 'required' => true,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
        ),
    );

    /**
     * Get field ID by Input number
     *
     * @param int $inputsNumber
     * 
     * @return int|false
     */
    public static function getExtraFieldId($inputsNumber)
    {
        $sql = 'SELECT id
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                WHERE id = ' . pSQL($inputsNumber);

        // zwraca wartość
        return (int)Db::getInstance()->getValue($sql);
    }

    /**
     * Get array Field Data by ID, shop_ID
     *
     * @param int $field_ID
     * @param int $shop_ID
     * 
     * @return array|false
     */
    public static function getSummaryExtraFieldDataByShopId($field_ID, $shop_ID)
    {
        $sql = 'SELECT field.front_view, field.html_content, field.active, shop.hook_id, shop.position
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' field
                LEFT JOIN ' . _DB_PREFIX_ . pSQL(MKDExtraProductFieldsShopModel::$definition['table']) . ' shop ON field.id = shop.field_id 
                                
                WHERE field.id = ' . (int)$field_ID . ' AND shop.id_shop = ' . (int)$shop_ID;

        // zwraca tablicę
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get array Active Fields by shop_ID for Product Edit Page
     *
     * @param int $shop_ID
     * 
     * @return array|false
     */
    public static function getActiveExtraFieldsByShopId($shop_ID)
    {
        $sql = 'SELECT field.id, field.front_view, field.html_content, shop.hook_id
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' field
                LEFT JOIN ' . _DB_PREFIX_ . pSQL(MKDExtraProductFieldsShopModel::$definition['table']) . ' shop ON field.id = shop.field_id
                
                WHERE shop.id_shop = ' . (int)$shop_ID . ' AND field.active = 1';

        return Db::getInstance()->executeS($sql);
    }

               
}

class MKDExtraProductFieldsLangModel extends ObjectModel
{
    public $id;
    public $id_lang;
    public $field_id;
    public $title;

    public static $definition = array(
        'table' => _MKD_NAME_ . '_extra_product_fields_lang',
        'primary' => 'id',
        'multishop' => false,
        'fields' => array(
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'field_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                // 'shop' => true,
            ],
            'title' => [
                'type' => self::TYPE_STRING,
                // 'required' => true,
                'validate' => 'isGenericName',
                'size' => 50,
            ],
            'data_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ),
    );

    /**
     * Update the Title based on field_id and id_lang.
     *
     * @param string $title
     * @param int $field_ID
     * @param int $lang_ID
     * @param date $currentDate
     * 
     * @return bool
     */
    public static function updateTitleByFieldId($title, $field_ID, $lang_ID, $currentDate)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
        SET 
            `title` = "' . pSQL($title) . '",
            `data_upd` = "' . $currentDate . '"
        WHERE `field_id` = ' . (int)$field_ID .' AND `id_lang` = ' . $lang_ID;

        return Db::getInstance()->execute($sql);
    }


    /**
     * Get array Field Title Lang by field_ID, lang_ID
     *
     * @param int $field_ID
     * @param int $lang_ID
     * 
     * @return array|false
     */
    public static function getExtraFieldTitleLangByFieldId($field_ID, $lang_ID)
    {
        $sql = 'SELECT title
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
                WHERE field_id = ' . (int)$field_ID .' AND id_lang = '.$lang_ID;

        // zwraca tablicę
        return Db::getInstance()->getRow($sql);
    }

}

class MKDExtraProductFieldsShopModel extends ObjectModel
{
    public $id;
    public $id_shop;
    public $field_id;
    public $hook_id;
    public $position;

    public static $definition = array(
        'table' => _MKD_NAME_ . '_extra_product_fields_shop',
        'primary' => 'id',
        'fields' => array(
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'field_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'hook_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                // 'required' => true,
                // 'shop' => true,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
        ),
    );

}


// Model przechowania wartości Extra fields każdego Produktu
class MKDExtraProductFieldsValueModel extends ObjectModel
{
    public $id;
    public $id_shop;
    public $field_id;
    public $product_id;
    public $id_lang;
    public $html_content;
    public $value;
    public $data_upd;
    public $active;

    public static $definition = array(
        'table' => _MKD_NAME_ . '_extra_product_fields_value',
        'primary' => 'id',
        'multishop' => false,
        'fields' => array(
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'field_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'product_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'value' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
                'size' => 3250,
            ],
            'data_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                // 'required' => true,
            ],
        ),
    );


    /**
     * Get array ALL Data Field by shop_ID, product_ID
     *
     * @param int $shop_ID
     * @param int $product_ID
     * 
     * @return array|false
     */
    public static function getExtraFildValuesForProduct($shop_ID, $product_ID, $activeField_ID, $lang_ID)
    {

        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '
                WHERE id_shop = '. (int)$shop_ID .'
                    AND product_id = ' . (int)$product_ID . '
                    AND id_lang = ' . (int)$lang_ID . '
                    AND field_id = ' . (int)$activeField_ID;

        $result = Db::getInstance()->getRow($sql);

        return $result;
    }



}