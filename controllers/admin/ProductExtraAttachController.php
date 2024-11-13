<?php
/*
* Konwencja nazewnictwa AJAX process
*/


if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_MKD_NAME_')) {
    define('_MKD_NAME_', 'mkd_product_attachments');
}
// Załaduj Model
require_once(_PS_MODULE_DIR_. _MKD_NAME_ .'/models/MKDProductAttachmentsModel.php');


class ProductExtraAttachController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }


    public function initForm($product_ID)
    {
    
        if ((int)$product_ID <= 0) {
            return;
        }
        
        
        $model = new MKDProductAttachmentsModel();
        
        // Pobierz dostępne grupy załączników
        $shopId = Context::getContext()->shop->id;
        $languageId = Context::getContext()->language->id;

        // Pobierz wszystkie języki
        $languages = Language::getLanguages();
        // Stworzenie tablicy z kodami ISO języka na podstawie ID języka do wyświetlania w tabeli załączników
        $langISO = array();
        foreach ($languages as $language) {
            $langISO[$language['id_lang']] = strtoupper($language['iso_code']);
        }


        $availableGroups = $model->getAvailableAttachmentGroups($shopId, $languageId);

        // Pobierz załączniki dla produktu
        $productAttachments = $model->getProductAttachmentsByProductId($product_ID, $languageId);

        // Link do konfiguracji modułu
        $linkToAddNewGroup   = $this->context->link->getAdminLink('AdminModules', true);
        $linkToAddNewGroup  .= '&configure=mkd_product_attachments';

        // Ścieżki dla plików JS i CSS
        $modulePath = _MODULE_DIR_ . _MKD_NAME_ ;
        // $uploadPath = __PS_BASE_URI__ . _MKD_UPLOAD_DIR_ . $product_ID;
        $uploadPath = Tools::getShopDomainSsl(true) . _MKD_UPLOAD_DIR_ . $product_ID;
        
        
        // Przekaż dane do szablonu
        $smarty = $this->context->smarty;
        $smarty->assign([
            'modulePath'            => $modulePath,
            'uploadPath'            => $uploadPath,
            'linkToAddNewGroup'     => $linkToAddNewGroup,
            'shopId'                => $shopId,
            'languages'             => $languages,
            'langISO'               => $langISO,
            'productId'             => $product_ID,
            'availableGroups'       => $availableGroups,
            'productAttachments'    => $productAttachments
        ]);
        
    }

    

    // Obsługa żądania AJAX || Aktualizacja TimeMarker dla danego produktu [EXPORT]

    // =================================
    // Dodawanie załącznika ============
    // =================================
    public function ajaxProcessAddAttachment()
    {

        // Utwórz instancję modelu
        $model = new MKDProductAttachmentsModel();

        // Pobierz aktualną datę i godzinę
        $currentDateTime = date('Y-m-d H:i:s');

        // Pobierz dane z AJAX
        $id_shop        = (int)Tools::getValue('shopId', Context::getContext()->shop->id);
        $id_lang        = (int)Tools::getValue('langId', Context::getContext()->language->id);
        $product_id     = (int)Tools::getValue('productId');
        $type_id        = (int)Tools::getValue('groupId');
        $file_name      = Tools::getValue('attachmentName');

        $attachmentFile = $_FILES['attachmentFile'] ?? null;


        // if (isset($_FILES['attachmentFile'])) {
        //     $attachmentFile = $_FILES['attachmentFile'];
        // } else {
        //     $attachmentFile = null;
        // }
        $attachmentURL = Tools::getValue('attachmentURL') ? filter_var(Tools::getValue('attachmentURL'), FILTER_VALIDATE_URL) : null;
        
        $comment        = Tools::getValue('attachmentComment');
        $data_add       = $currentDateTime;
        $data_upd       = $currentDateTime;
        $active         = (int)Tools::getValue('active');


        // Pobierz Format pliku (value) dla danej grupy załączników w ścieżce 'upload_dir'
        $language_ID = Context::getContext()->language->id;
        $attachFormat = $model->getAttachmentFormatByTypeId($type_id, $language_ID);

        // Katalog docelowy, gdzie będą przechowywane załączniki
        // $uploadDirectory = _PS_UPLOAD_DIR_ . 'attachments/product_' . $product_id . '/' . $attachFormat . '/';
        $uploadDirectory = _PS_IMG_DIR_ . 'attachments/product_' . $product_id . '/' . $attachFormat . '/';

        // Pobierz najwyższą position dla danej Grupy i dodaj następną
        $maxPosition = (int)Db::getInstance()->getValue('SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . MKDProductAttachmentsModel::$definition['table'] . '` WHERE `type_id` = ' . (int)$type_id);

        // Ustaw dane w Modelu
        $model->id_shop     = $id_shop;
        $model->id_lang     = $id_lang;
        $model->product_id  = $product_id;
        $model->type_id     = $type_id;
        $model->file_name   = $file_name;
        $model->comment     = $comment;
        $model->position    = $maxPosition + 1;
        $model->data_add    = $data_add;
        $model->data_upd    = $data_upd;
        $model->active      = $active;

        // Zapisz plik
        if (Tools::getValue('action') === 'addAttachment') {

            if (!file_exists($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            PrestaShopLogger::addLog(
                'MKD · Attachments => DIR: ' . $uploadDirectory . 
                ' => ' . $attachmentFile['name'] . ' ' . round($attachmentFile['size'] / 1024, 2 ). ' [KB]',
                1
                
            );

            // Walidacja pliku/URL
            if ($attachmentFile && $attachmentFile['error'] === UPLOAD_ERR_OK) {

                // Data pliku
                $currentTimestamp = time();
                $currentDate = date('d-m-Y');
                $fileName = $currentDate . '_' . $currentTimestamp . '_' . $attachmentFile['name'];

                // Ścieżka docelowa
                $targetPath = $uploadDirectory . $fileName;

                // Przesuń plik z folderu tymczasowego do docelowego folderu
                if (move_uploaded_file($attachmentFile['tmp_name'], $targetPath)) {
                    
                    // Plik został zapisany pomyślnie
                    $model->file_url = $fileName;

                } else {
                    // Wystąpił błąd podczas zapisywania pliku
                    $response = [
                        'success' => false,
                        'message' => $this->l('There was an error saving')
                    ];
                }

            } elseif ($attachmentURL) {
                // Przesłano adres URL
                
                $model->file_url = $attachmentURL;
                
            }    

            // Zapisz załącznik
            $result = $model->add();

            if ($result) {

                // Pobierz liczbę plików dla danej Grupy załączników w tabeli ..types_shop
                $currentFilesCount = (int) Db::getInstance()->getValue('
                SELECT `files` 
                FROM `' . _DB_PREFIX_ . pSQL(_MKD_NAME_) .'_types_shop` 
                WHERE `type_id` = ' . $model->type_id . ' 
                AND `id_shop` = ' . $model->id_shop
                );

                // Zwiększ liczbę plików o 1
                $newFilesCount = $currentFilesCount + 1;

                // Zaktualizuj Counter plików w danej Grupie
                Db::getInstance()->update(
                    pSQL(_MKD_NAME_) . '_types_shop',
                    ['files' => $newFilesCount],
                    'type_id = ' . $model->type_id . ' AND `id_shop` = ' . $model->id_shop
                );

                // Wyświetlić powiadomienie w Ajax
                $response = [
                    'success' => true,
                    'message' => $this->l('The new attachment has been saved to the Group:') .' ' . $type_id
                ];

            } else {
                // Wystąpił błąd podczas zapisywania załącznika
                $response = [
                    'success' => false,
                    'message' => $this->l('There was an error saving')
                ];
            }

            // Zwróć odpowiedź JSON
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // =================================
    // Edycja załącznika ===============
    // =================================
    public function ajaxProcessUpdateAttachment() 
    {
            
        // Utwórz instancję modelu
        $model = new MKDProductAttachmentsModel();

        // Pobierz aktualną datę i godzinę
        $currentDateTime = date('Y-m-d H:i:s');

        $edit_ID = (int)Tools::getValue('attachmentId');

        $id_shop        = (int)Tools::getValue('shopId', Context::getContext()->shop->id);
        $id_lang        = (int)Tools::getValue('langId', Context::getContext()->language->id);
        $product_id     = (int)Tools::getValue('productId');
        $type_id        = (int)Tools::getValue('groupId');
        $file_name      = Tools::getValue('attachmentName');

        $attachmentFile = $_FILES['attachmentFile'] ?? null;
        $attachmentURL  = Tools::getValue('attachmentURL') ? filter_var(Tools::getValue('attachmentURL'), FILTER_VALIDATE_URL) : null;

        $comment        = Tools::getValue('attachmentComment');
        $data_upd       = $currentDateTime;
        $active         = (int)Tools::getValue('active');


        // Pobierz Format pliku (value) dla danej grupy załączników w ścieżce 'upload_dir'
        $language_ID = Context::getContext()->language->id;
        $attachFormat = $model->getAttachmentFormatByTypeId($type_id, $language_ID);

        // Katalog docelowy, gdzie będą przechowywane załączniki
        // $uploadDirectory = _PS_UPLOAD_DIR_ . 'attachments/product_' . $product_id . '/' . $attachFormat . '/';
        $uploadDirectory = _PS_IMG_DIR_ . 'attachments/product_' . $product_id . '/' . $attachFormat . '/';


        // Pobierz nazwę poprzedniego załącznika, jeśli istnieje
        $attachmentPrevName = Db::getInstance()->getRow('
        SELECT id_lang, file_name, file_url, comment, data_upd, active
        FROM `' . _DB_PREFIX_ . MKDProductAttachmentsModel::$definition['table'] . '`
        WHERE `id` = ' . (int) $edit_ID . '
            AND `id_shop` = ' . (int) $id_shop . '
            AND `product_id` = ' . (int) $product_id . '
            AND `type_id` = ' . (int) $type_id      
        );

        // Jeśli załącznik do aktualizacji istnieje, usuń jego poprzedni plik, jeśli jest przesyłany nowy plik
        if ($attachmentPrevName && $attachmentFile && $attachmentFile['error'] === UPLOAD_ERR_OK) {
            // Usuń poprzedni plik
            $previousFilePath = $uploadDirectory . $attachmentPrevName['file_url'];
            if (file_exists($previousFilePath)) {
                unlink($previousFilePath);
            }

            PrestaShopLogger::addLog(
                'MKD · Attachments => REPLACE FOR PRODUCT: ' . $product_id . ' in Group: ' . $type_id .
                ' OLD FILE: ' . $attachmentPrevName['file_url'] .
                ' NEW FILE: ' . $attachmentFile['name'] . ' ' . round($attachmentFile['size'] / 1024, 2 ). ' [KB]',
                1
                
            );
        }

        // Walidacja pliku/URL
        if ($attachmentFile && $attachmentFile['error'] === UPLOAD_ERR_OK) {

            if (!file_exists($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }    

            // Data pliku
            $currentTimestamp = time();
            $currentDate = date('d-m-Y');
            $fileNameNew = $currentDate . '_' . $currentTimestamp . '_' . $attachmentFile['name'];

            // Ścieżka docelowa
            $targetPath = $uploadDirectory . $fileNameNew;

            // Przesuń plik z folderu tymczasowego do docelowego folderu
            if (move_uploaded_file($attachmentFile['tmp_name'], $targetPath)) {
                
                // Plik został zapisany pomyślnie

                $file_url = $fileNameNew;

            } else {
                // Wystąpił błąd podczas zapisywania pliku
                $response = [
                    'success' => false,
                    'message' => $this->l('There was an error saving')
                ];
            }

           
        }

        // Update załącznika
        if (Tools::getValue('action') === 'updateAttachment') {

            if (!file_exists($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            // 1. Pobierz dane z rekordu, który ma być update
            $attachmentToUpdate = Db::getInstance()->getRow('
            SELECT id_lang, file_name, file_url, comment, data_upd, active
            FROM `' . _DB_PREFIX_ . MKDProductAttachmentsModel::$definition['table'] . '`
            WHERE `id` = ' . (int) $edit_ID . '
                AND `id_shop` = ' . (int) $id_shop . '
                AND `product_id` = ' . (int) $product_id .'
                AND `type_id` = ' . (int) $type_id      
            );


            if ($attachmentToUpdate) {
                // Sprawdź, które pole zostało zmienione
                $dataToUpdate = array();
                
                if ($id_lang != $attachmentToUpdate['id_lang']) {
                    $dataToUpdate['id_lang'] = pSQL($id_lang);
                }

                if ($file_name != $attachmentToUpdate['file_name']) {
                    $dataToUpdate['file_name'] = pSQL($file_name);
                }
                
                if ($comment != $attachmentToUpdate['comment']) {
                    $dataToUpdate['comment'] = pSQL($comment);
                }
                
                if ($attachmentFile) {
                    $dataToUpdate['file_url'] = $file_url;
                }
                
                if ($attachmentURL && $attachmentURL != $attachmentToUpdate['file_url']) {
                    $dataToUpdate['file_url'] = $attachmentURL;
                }
                
                if ($active != $attachmentToUpdate['active']) {
                    $dataToUpdate['active'] = (int)$active;
                }
                
                // Jeśli jakieś pole zostało zmienione, zaktualizuj rekord w bazie danych
                if (!empty($dataToUpdate)) {

                    $dataToUpdate['data_upd'] = $currentDateTime;
            
                    $result = Db::getInstance()->update(MKDProductAttachmentsModel::$definition['table'], $dataToUpdate, 'id = ' . (int)$edit_ID);

                    // Wyczyszczenie cache
                    $model->clearCache();

                    if ($result) {
                        $response = [
                            'success' => true,
                            'message' => $this->l('The attachment was updated in the Group')
                        ];

                    }
            
                }
            }
            
            // Zwróć odpowiedź JSON
            header('Content-Type: application/json');
            echo json_encode($response);

        }

    }


    // =================================
    // Usuwanie załącznika =============
    // =================================
    public function ajaxProcessDeleteAttachment()
    {
           
        // Utwórz instancję modelu
        $model = new MKDProductAttachmentsModel();

        $delete_ID = (int)Tools::getValue('attachmentId');

        $id_shop    = (int)Tools::getValue('shopId', Context::getContext()->shop->id);
        $product_id = (int)Tools::getValue('productId');
        $type_id    = (int)Tools::getValue('groupId');

        // Pobierz dane z rekordu (position), który zostanie usunięty
        $positionToDelete = Db::getInstance()->getRow('
            SELECT `position`, `type_id`, `id_shop`, `product_id`, `file_url`
            FROM `' . _DB_PREFIX_ . MKDProductAttachmentsModel::$definition['table'] . '`
            WHERE `id` = ' . $delete_ID);


        $position   = (int) $positionToDelete['position'];
        $id_shop    = (int) $positionToDelete['id_shop'];
        $type_id    = (int) $positionToDelete['type_id'];
        $product_id = (int) $positionToDelete['product_id'];
        // Nazwa pliku do usunięcia
        $file_url   = $positionToDelete['file_url'];
        

        // Usuń załącznik
        if (Tools::getValue('action') === 'deleteAttachment') {
            
            // Ustaw dane w Modelu
            $model->id          = $delete_ID;
            $model->id_shop     = $id_shop;
            $model->product_id  = $product_id;
            $model->type_id     = $type_id;
            $model->file_url    = $file_url;
            

            // Usuń rekord
            $result = $model->delete($delete_ID);

            // Wyczyszczenie cache'u
            $model->clearCache();

            if ($result) {

                // 1. Zaktualizuj Pozycje rekordów w danej Grupie, Sklepie i produkcie
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . MKDProductAttachmentsModel::$definition['table'] . '`
                        SET `position` = `position` - 1
                        WHERE `id_shop` = ' . $model->id_shop . '
                                AND `type_id` = ' . $model->type_id . '
                                AND `product_id` = ' . $model->product_id . '
                                AND `position` > ' . $position);

                // 2. Pobierz Counter plików dla danej Grupy załączników w tabeli ..TYPES_SHOP
                $currentFilesCount = (int) Db::getInstance()->getValue('
                    SELECT `files` 
                    FROM `' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_types_shop` 
                    WHERE `type_id` = ' . $model->type_id . ' 
                        AND `id_shop` = ' . $model->id_shop
                );

                // Zmniejsz liczbę plików o 1
                $deleteFilesCount = $currentFilesCount - 1;

                // Zaktualizuj Counter plików w danej Grupie
                Db::getInstance()->update(
                    pSQL(_MKD_NAME_) . '_types_shop',
                    ['files' => $deleteFilesCount],
                    'type_id = ' . $model->type_id . ' AND `id_shop` = ' . $model->id_shop
                );

                // 3. Usuwanie pliku
                // Pobierz Format pliku (value) dla danej grupy załączników w ścieżce 'upload_dir'
                $language_ID = Context::getContext()->language->id;
                $attachFormat = $model->getAttachmentFormatByTypeId($model->type_id, $language_ID);

                // Katalog docelowy, gdzie będą przechowywane załączniki
                // $uploadDirectory = _PS_UPLOAD_DIR_ . 'attachments/product_' . $model->product_id . '/' . $attachFormat . '/';
                $uploadDirectory = _PS_IMG_DIR_ . 'attachments/product_' . $product_id . '/' . $attachFormat . '/';


                // usuń plik
                $filePath = $uploadDirectory . $model->file_url;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Zaktualizuj timeMarker dla kontrolera Exportu danych produktu => wygenerować nowy plik
                $exportTimeMarkerProduct = (int) time();
                Configuration::updateValue('CONF_' . strtoupper(_MKD_NAME_) . '_EXPORT_TIMEMARKER_PRODUCT_ATTACHMENTS', $exportTimeMarkerProduct);

                PrestaShopLogger::addLog(
                    'MKD · Attachments => DELETE FOR PRODUCT: ' . $product_id . ' in Group: ' . $model->type_id .
                    ' DIR: ' . $uploadDirectory .
                    ' ' . $model->file_url,
                    1
                    
                );
                
                // 4. Wyświetlić powiadomienie w Ajax
                $response = [
                    'success' => true,
                    'message' => $this->l('The file was deleted from the Group')
                ];

            } else {
                $response = [
                    'success' => false,
                    'message' => $this->l('Failed to delete file from the Group') . ' [' .$type_id .']'
                ];
            }

            // Zwróć odpowiedź JSON
            header('Content-Type: application/json');
            echo json_encode($response);

        }
    }

}

