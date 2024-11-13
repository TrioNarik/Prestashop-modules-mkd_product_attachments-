<?php
/*
* Model Danych dla załączników produktu
* 
* Walidacja
*/

class MKDProductAttachmentsModel extends ObjectModel
{
    public $id;
    public $id_shop;
    public $id_lang;
    public $product_id;
    public $type_id;
    public $file_name;
    public $file_url;
    public $comment;
    public $position;
    public $data_add;
    public $data_upd;
    public $view_count;
    public $download_count;
    public $active;

    public static $definition = array(
        'table' => _MKD_NAME_,
        'primary' => 'id',
        
        'fields' => array(
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
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
            'type_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'file_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 50,
            ],
            'file_url' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
            ],
            'comment' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isString',
                'size' => 255,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'data_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'data_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'view_count' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'download_count' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'active' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
        ),
    );

    // Pobierz zapisane Grupy załączników dla danego Shop i Lang
    public static function getAvailableAttachmentGroups($shopId, $languageId)
    {
        $sql = 'SELECT ts.type_id, tl.id_lang, tl.title, tl.description, tl.user_groups, tl.extra_url, f.value, f.name AS format
            FROM ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_types_shop ts
            LEFT JOIN ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_types t ON ts.type_id = t.id
            LEFT JOIN ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_types_lang tl ON t.id = tl.type_id
            LEFT JOIN ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_formats f ON tl.format = f.id
            LEFT JOIN ' . _DB_PREFIX_ . 'lang shopLang ON tl.id_lang = shopLang.id_lang
            WHERE ts.id_shop = ' . (int)$shopId . ' AND tl.id_lang = ' . (int)$languageId . ' AND t.active = 1 AND shopLang.active = 1';
        
        $results = Db::getInstance()->executeS($sql);
    
        // Grupowanie załączników
        $attachmentGroups = [];
        foreach ($results as $row) {
            $groupId = $row['type_id'];
            
            // Pobierz ID grup użytkowników
            $userGroupsSerialized = $row['user_groups'];
            $userGroupIds = unserialize($userGroupsSerialized);

            // Tablica nazw grup użytkowników
            $userGroupNames = [];

            // Przetwórz ID user_group na nazwy
            foreach ($userGroupIds as $usergroupId) {
                $group = new Group($usergroupId, Context::getContext()->language->id);
                $userGroupNames[] = $group->name;
            }


            // Pobierz info o Grupie załączników
            $attachmentGroups[$groupId]['title']            = $row['title'];
            $attachmentGroups[$groupId]['description']      = $row['description'];
            $attachmentGroups[$groupId]['users_name']       = $userGroupNames;
            $attachmentGroups[$groupId]['url']              = $row['extra_url'];
            $attachmentGroups[$groupId]['format']           = $row['format'];
            $attachmentGroups[$groupId]['value']            = $row['value'];
        }
    
        return $attachmentGroups;
    }
    

    // Pobierz załączniki dla danego produktu na podstawie jego ID
    public static function getProductAttachmentsByProductId($product_ID, $languageId, $exportLanguage_ID = false)
    {
        $shopId = Context::getContext()->shop->id; // Pobierz ID aktualnego sklepu

        $sql = 'SELECT a.*, t.title AS group_name
                FROM ' . _DB_PREFIX_ . pSQL(self::$definition['table']) . ' a
                LEFT JOIN ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_types_lang t
                    ON a.type_id = t.type_id AND t.id_lang = ' . (int)$languageId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'lang shopLang
                    ON a.id_lang = shopLang.id_lang

                WHERE a.product_id = ' . (int)$product_ID . ' 
                AND shopLang.active = 1
                AND a.id_shop = ' . (int)$shopId; // Warunek na ID sklepu

        if ($exportLanguage_ID) {
            $sql .= ' AND a.id_lang =  ' . (int)$exportLanguage_ID;
        }

        $result = Db::getInstance()->executeS($sql);

        return is_array($result) ? $result : array();
    }

    

    // Pobierz Format dla danej grupy
    public static function getAttachmentFormatByTypeId($type_id, $languageId)
    {
        $sql = 'SELECT f.value
                FROM ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_formats f
                LEFT JOIN ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_types_lang tl
                ON f.id = tl.format
                WHERE tl.type_id = ' . (int)$type_id . ' AND tl.id_lang = ' . (int)$languageId;

        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

}
