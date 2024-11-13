<?php
/*
* Konwencja nazewnictwa AJAX process => action <=> button
*
*
*/


if (!defined('_PS_VERSION_')) {
    exit;
}

class ExtraFieldsProductController extends ModuleAdminController
{

    /** @var int Texarea size */
    protected $maxLength;
    


    public function __construct()
    {
        // Ustawienia kontrolera
        parent::__construct();
        
        $this->maxLength = 3250;

    }
    public function init()
    {
        parent::init();

    }


 
    // Sekcja Extra Field na stronie edycji produktu
    public function initExtraFieldsBlock($product_ID)
    {
        if ((int)$product_ID <= 0) {
            return;
        }

        // Pobierz Język
        $id_lang  = Context::getContext()->language->id;
        $lang_iso_code = Language::getIsoById($id_lang);

        // Pobierz aktualny sklep
        $shop_ID = Context::getContext()->shop->id;

        // Załaduj Model Extra Fields Poduct
        require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

        $extraFieldsModel      = new MKDExtraProductFieldsModel();
        $extraFieldsShopModel  = new MKDExtraProductFieldsShopModel();
        $extraFieldsLangModel  = new MKDExtraProductFieldsLangModel();

        $extraFieldsValueModel = new MKDExtraProductFieldsValueModel();


        // Sprawdź aktywne Extra Fields dla sklepu w opcjach Modułu
        $activeShopFields = $extraFieldsModel->getActiveExtraFieldsByShopId($shop_ID);


        if (is_array($activeShopFields)) {

            // Wyzerować tablicę Field Title Lang
            $extraFieldTitleLang = [];
        
            // Wyzerować tablicę Produkt Value Lang
            $productFieldValueLang = [];
        
            foreach ($activeShopFields as $active) {
                $languages = Language::getLanguages();
        
                foreach ($languages as $language) {
                    $lang_ID = $language['id_lang'];
        
                    // Pobierz Title Lang dla aktywnego ID extra field
                    $activeExtraFieldTitle = $extraFieldsLangModel->getExtraFieldTitleLangByFieldId($active['id'], $lang_ID);
                    
                    $extraFieldTitleLang[$active['id']][$lang_ID] = $activeExtraFieldTitle['title'];
        
                    $productFieldValue = $extraFieldsValueModel->getExtraFildValuesForProduct($shop_ID, $product_ID, $active['id'], $lang_ID);
        
                    $productFieldValueLang[$active['id']][$lang_ID] = $productFieldValue;
                }
            }
        
            
            // Dane do szablonu
            $smarty = Context::getContext()->smarty;
        
            $smarty->assign([
                'id_lang'       => $id_lang,
                'lang_iso_code' => $lang_iso_code,
                'activeFields'  => $activeShopFields,
                'fieldTitle'    => $extraFieldTitleLang,

                'productValue'  => $productFieldValueLang,
            ]);
        }

        
    }


    // =================================
    // Zapisz Extra Field Value ========
    // =================================
    public function ajaxProcessSaveExtraFieldValue()
    {

        $id_shop    = (int)Tools::getValue('shopId', Context::getContext()->shop->id);
        $product_ID = (int)Tools::getValue('productId');
        $field_ID   = (int)Tools::getValue('fieldId');
        
        if (Tools::getValue('action') === 'saveExtraFieldValue') {

            // Załaduj Model Extra Fields Product
            require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

            // Ustaw czas
            $currentTimestamp = time();

            // Pobierz Datę
            $currentTimer = date('Y-m-d H:i:s', $currentTimestamp);

            // Utwórz instancję modelu
            $extraFieldValueModel = new MKDExtraProductFieldsValueModel();

            $languages = Language::getLanguages();

            // Pozostałe dane z inputu dla Multi-lang
            foreach ($languages as $language) {
                $lang_ID     = (int) $language['id_lang'];

                // Sprawdź, czy istnieje wartość dla bieżącego języka
                $fieldValue = Tools::getValue('fieldValue');
                $value = isset($fieldValue[$lang_ID]) ? substr($fieldValue[$lang_ID], 0, $this->maxLength) : '';

                // Dodaj walidację dla Value
                if (!Validate::isCleanHtml($value)) {
                    $response = [
                        'success' => false,
                        'message' => $this->l('Invalid HTML content'),
                    ];

                    // Zwróć odpowiedź JSON
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    return; // Przerwij, jeśli walidacja nie powiodła się
                }

                // Ustaw wartości w modelu
                $extraFieldValueModel->id_shop = $id_shop;
                $extraFieldValueModel->product_id = $product_ID;
                $extraFieldValueModel->field_id = $field_ID;
                $extraFieldValueModel->id_lang = $lang_ID;
                $extraFieldValueModel->value = $value;
                $extraFieldValueModel->data_upd = $currentTimer;
                
                // Aktywuj od razu pole, które ma lang Value
                $extraFieldValueModel->active = !empty(trim($value)) ? 1 : 0;

                // 2. Zapisz dla LangModel
                if ($extraFieldValueModel->add()) {

                    $extraFieldValueModel->clearCache();

                    $response = [
                        'success' => true,
                        'message' => $this->l('The extra field value has been successfully saved'),
                        'value' => $value
                    ];
                } else {

                    $response = [
                        'success' => false,
                        'message' => $this->l('Save failed. Check the form')
                    ];
                }
                
            }
        } else {
            // Obsługa innych przypadków
            $response = [
                'success' => false,
                'message' => $this->l('Invalid action')
            ];
        }

        // Zwróć odpowiedź JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }



    // =================================
    // Aktualizuj Extra Field Value ====
    // =================================
    public function ajaxProcessUpdateExtraFieldValue()
    {

        $id_shop    = (int)Tools::getValue('shopId', Context::getContext()->shop->id);
        $product_ID = (int)Tools::getValue('productId');
        $field_ID   = (int)Tools::getValue('fieldId');
        
        if (Tools::getValue('action') === 'updateExtraFieldValue') {

            // Załaduj Model Extra Fields Product
            require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

            // Ustaw czas
            $currentTimestamp = time();
            // Pobierz Datę
            $currentTimer = date('Y-m-d H:i:s', $currentTimestamp);

            // Utwórz instancję modelu
            $extraFieldValueModel = new MKDExtraProductFieldsValueModel();

            $languages = Language::getLanguages();

            // Pozostałe dane z inputu dla Multi-lang
            foreach ($languages as $language) {
                $lang_ID     = (int) $language['id_lang'];

                // Sprawdź, czy istnieje wartość dla bieżącego języka
                $fieldValue = Tools::getValue('fieldValue');
                $value = isset($fieldValue[$lang_ID]) ? substr($fieldValue[$lang_ID], 0, $this->maxLength) : '';

                // Dodaj walidację dla Value
                if (!Validate::isCleanHtml($value)) {
                    $response = [
                        'success' => false,
                        'message' => $this->l('Invalid HTML content'),
                    ];

                    // Zwróć odpowiedź JSON
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    return; // Przerwij, jeśli walidacja nie powiodła się
                }

                // Pobierz istniejący rekord na podstawie type_id i id_lang
                $existingRecord = Db::getInstance()->getRow(
                    'SELECT id 
                        FROM `' . _DB_PREFIX_ . MKDExtraProductFieldsValueModel::$definition['table'] . '`
                        WHERE id_shop = ' . (int) $id_shop . ' AND product_id = ' . (int) $product_ID . ' AND field_id = ' . (int) $field_ID . ' AND id_lang = ' . (int) $lang_ID
                );

                
                // Jeśli rekord istnieje oraz nowa wartość jest inna niż poprzenio => zaktualizuj go
                if ($existingRecord) {
                    
                    $extraFieldValueModel->id = $existingRecord['id'];

                    // Ustaw wartości w modelu
                    $extraFieldValueModel->id_shop = $id_shop;
                    $extraFieldValueModel->product_id = $product_ID;
                    $extraFieldValueModel->field_id = $field_ID;
                    $extraFieldValueModel->id_lang = $lang_ID;
                    $extraFieldValueModel->value = $value;
                    $extraFieldValueModel->data_upd = $currentTimer;
                    
                    // Aktywuj też pole, które ma lang Value
                    $extraFieldValueModel->active = !empty(trim($value)) ? 1 : 0;

                    // 2. Zapisz dla LangModel
                    if ($extraFieldValueModel->update($extraFieldValueModel->id)) {

                        $extraFieldValueModel->clearCache();

                        $response = [
                            'success' => true,
                            'message' => $this->l('The extra field value has been successfully updated'),
                            'value' => $value
                        ];
                    } else {

                        $response = [
                            'success' => false,
                            'message' => $this->l('Update failed. Check the form')
                        ];
                    }
                    
                }  else {
                    $response = [
                        'success' => false,
                        'message' => $this->l('Update failed. The record does not exist')
                    ];
                }
                
            }
        } else {
            // Obsługa innych przypadków
            $response = [
                'success' => false,
                'message' => $this->l('Invalid action')
            ];
        }

        // Zwróć odpowiedź JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }



    // =================================
    // Status zmień Extra Field ========
    // =================================
    public function ajaxProcessChangeStatusField()
    {

        // Załaduj Model Extra Fields Poduct
        require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');
            

        $productField_ID  = (int)Tools::getValue('productFieldId');

        if($productField_ID) {
                
            // Zmień status Extra Field
            if (Tools::getValue('action') === 'changeStatusField') {
                // Utwórz instancję modelu
                $extraFieldValueModel = new MKDExtraProductFieldsValueModel($productField_ID);
            
                if ($extraFieldValueModel->toggleStatus('active')) {

                    // Ustaw czas
                    $currentTimestamp = time();
                    // Pobierz Datę
                    $currentTimer = date('Y-m-d H:i:s', $currentTimestamp);

                    // Zaktualizuj kolumnę data_upd
                    Db::getInstance()->update(
                        MKDExtraProductFieldsValueModel::$definition['table'],
                        ['data_upd' => pSQL($currentTimer)],
                        'id = ' . (int)$productField_ID
                    );
            
                    $response = [
                        'success' => true,
                        'message' => $this->l('Status field changed successfully')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => $this->l('Failed to change status')
                    ];
                }
            
            }
        } else {
            // Brak jeszcze wpisu dla tego pola (nie ma ID)
            $response = [
                'success' => false,
                'message' => $this->l('Important: It is not possible to change the status of a field that does not exist yet')
            ];
        }
        
        // Zwróć odpowiedź JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}