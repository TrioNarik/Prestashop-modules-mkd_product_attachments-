<?php
/*
* Konwencja nazewnictwa AJAX process => action <=> button
*
*
*/


if (!defined('_PS_VERSION_')) {
    exit;
}


class PositionProductController extends ModuleAdminController
{

    public function __construct()
    {
        // Ustawienia kontrolera
        parent::__construct();

    }
    public function init()
    {
        parent::init();

    }


    // Sekcja Position Product in Category na stronie edycji produktu
    public function initPositionProductInCategory($product_ID, $category_ID)
    {
        if ((int)$product_ID <= 0 || (int)$category_ID <= 0) {
            return;
        }
    
        // Pobierz język i sklep
        $id_lang  = Context::getContext()->language->id;
        $shop_ID = Context::getContext()->shop->id;
    
    
        // Pobierz aktualną pozycję produktu
        $sql = 'SELECT position, active FROM '._DB_PREFIX_ . pSQL(_MKD_NAME_) . '_product_position 
                WHERE id_product = '.(int)$product_ID.' 
                AND id_category = '.(int)$category_ID.' 
                AND id_shop = '.(int)$shop_ID;
        $result = Db::getInstance()->getRow($sql);
    
        if ($result) {
            $current_position = $result['position'];
            $product_active = $result['active'];
        } else {
            $current_position = 0; // Domyślna pozycja
            $product_active = 0; // Domyślnie nieaktywny
        }
    
        // Pobierz nazwy i pozycje wszystkich produktów w kategorii
        $sql = 'SELECT p.id_product, pl.name, pp.position FROM '._DB_PREFIX_.'category_product cp
                JOIN '._DB_PREFIX_.'product p ON cp.id_product = p.id_product
                JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product AND pl.id_lang = '.(int)$id_lang.'
                LEFT JOIN '._DB_PREFIX_ . pSQL(_MKD_NAME_) . '_product_position pp ON pp.id_product = p.id_product AND pp.id_category = '.(int)$category_ID.' AND pp.id_shop = '.(int)$shop_ID.'
                WHERE cp.id_category = '.(int)$category_ID.' AND p.active = 1 ORDER BY pp.position';
        
        $products = Db::getInstance()->executeS($sql); // Pobierz wszystkie produkty

        $total_product = count($products);
    
        // Pobierz nazwę kategorii
        $category = new Category($category_ID, $id_lang);
        $category_name = $category->name;

        // Pobierz nazwę sklepu
        $shop = new Shop($shop_ID);
        $shop_name = $shop->name;
    
        // Dane do szablonu
        $smarty = Context::getContext()->smarty;
    
        $smarty->assign([
            'total_product'    => $total_product,
            'category_name'    => $category_name,
            'category_id'      => $category_ID,
            'current_position' => $current_position,
            'products'         => $products, // produkty
            'product_id'       => $product_ID,
            'product_active'   => $product_active, // Informacja o aktywności produktu
            'shop_name'        => $shop_name, // Nazwa sklepu
            'lang_id'          => $id_lang,
            'shop_id'          => $shop_ID
        ]);
    }
    
    


    // =================================
    // Zapisz Extra Field Value ========
    // =================================
    public function ajaxProcessSaveProductPosition()
    {
        $shop_ID        = (int) Tools::getValue('shopId', Context::getContext()->shop->id);
        $lang_ID        = (int) Tools::getValue('langId', Context::getContext()->language->id);
        $product_ID     = (int) Tools::getValue('productId');
        $category_ID    = (int) Tools::getValue('categoryId');
        $position       = (int) Tools::getValue('position');
        $active         = (int) Tools::getValue('active');
    
        if (Tools::getValue('action') === 'saveProductPosition') {
            if (!Validate::isInt($product_ID) || !Validate::isInt($category_ID) || !Validate::isInt($shop_ID) || !Validate::isInt($position)) {
                $response = [
                    'success' => false,
                    'message' => $this->l('Invalid parameters'),
                ];
                header('Content-Type: application/json');
                echo json_encode($response);
                return;
            }
    
            // Wstaw nową pozycję lub zaktualizuj istniejącą
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_product_position`
                    (`id_shop`, `id_lang`, `id_product`, `id_category`, `position`, `active`)
                    VALUES (' . (int)$shop_ID . ', ' . (int)$lang_ID . ', ' . (int)$product_ID . ', ' . (int)$category_ID . ', ' . (int)$position . ', ' . (int)$active . ')
                    ON DUPLICATE KEY UPDATE `position` = ' . (int)$position . ', `active` = ' . (int)$active;
    
            // Wykonaj zapytanie SQL
            if (Db::getInstance()->execute($sql)) {
                $response = [
                    'success' => true,
                    'message' => $this->l('Position saved successfully'),
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => $this->l('Failed to save position'),
                ];
            }
    
            // Zwróć odpowiedź JSON
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }
    
}