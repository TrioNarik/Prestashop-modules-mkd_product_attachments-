<?php
/*
* Model Danych dla Programów
* 
* Walidacja
*/

class MKDProgramsModel extends ObjectModel
{
    public $id;
    public $shop_id;
    public $lang_id;
    public $country_id;
    public $name;
    public $comment;
    public $version;
    public $valid_date;
    public $position;
    public $active;
    

    public static $definition = array(
        'table' => _MKD_NAME_ . '_programs',
        'primary' => 'id',
        'multilang' => false, // Dla obsługi multi-lang
        'multishop' => false, // Dla obsługi multi-shop
        'fields' => array(
            'shop_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'lang_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'country_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'required' => true,
            ],
            'comment' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 500,
            ],
            'version' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 20,
            ],
            'valid_date' => [
                'type' => self::TYPE_DATE,
                // 'validate' => 'isDate',
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
        ),
    );

    // Pobierz listę dla renderTable()
    public static function getProgramList($langId, $orderBy, $orderWay, $employeeId, $shopId)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                ORDER BY `' . pSQL($orderBy) . '` ' . pSQL($orderWay);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        
    }

    // Pobierz ID do Edycji dla renderForm()
    public static function getProgramById($programId)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                WHERE `id` = ' . (int)$programId;

        return $result = Db::getInstance()->getRow($sql);

    }

}


