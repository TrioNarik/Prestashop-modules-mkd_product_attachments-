<?php
/*
* Model Danych dla Typów załączników => multishop/multilang => PRIMARY KEY()
* 
* Walidacja
*/

class MKDTypesModel extends ObjectModel
{
    public $id;
    public $active;

    public static $definition = array(
        'table' => _MKD_NAME_ . '_types',
        'primary' => 'id',
        // 'multilang' => true,
        'multishop' => true,
        'fields' => array(
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
        ),
    );

    // Pobierz ID do Edycji dla renderForm()
    public static function getTypesById($editId)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                WHERE `id` = ' . (int)$editId;

        return $result = Db::getInstance()->getRow($sql);

    }
               
}

class MKDTypesLangModel extends ObjectModel
{
    public $id;
    public $id_lang;
    public $type_id;
    public $format;
    public $hook;
    public $user_groups;
    public $product_brands;
    public $product_categories;
    public $title;
    public $description;
    public $extra_url;
    public $program;

    public static $definition = array(
        'table' => _MKD_NAME_ . '_types_lang',
        'primary' => 'id',
        // 'multishop' => false,
        'fields' => array(
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'type_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                // 'shop' => true,
            ],
            'format' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'hook' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'user_groups' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
            ],
            'product_brands' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'product_categories' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'title' => [
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isGenericName',
                'size' => 255,
            ],
            'description' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
            ],
            'extra_url' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'program' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
        ),
    );

    // Pobierz wszystkie ID's z multi-lang do Edycji dla renderForm() na bazie type_id z getTypesById()
    public static function getTypesLangByTypeId($editType = null)
    {

        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                WHERE `type_id` = ' . (int)$editType;

        return $result = Db::getInstance()->executeS($sql);

    }

}

class MKDTypesShopModel extends ObjectModel
{
    public $id;
    public $id_shop;
    public $type_id;
    public $position;
    public $files;

    public static $definition = array(
        'table' => _MKD_NAME_ . '_types_shop',
        'primary' => 'id',
        'fields' => array(
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'type_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'files' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
        ),
    );

    // Pobierz ID do Edycji dla renderForm() na bazie type_id z getTypesById()
    public static function getTypesShopByTypeId($editType)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                WHERE `type_id` = ' . (int)$editType;

        return $result = Db::getInstance()->getRow($sql);

    }

}
