<?php
/*
* Konwencja nazewnictwa AJAX process
*
* Użyj Biblioteki Spreadsheet do generowania plików XLS
*
*/


if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_MKD_NAME_')) {
    define('_MKD_NAME_', 'mkd_product_attachments');
}


require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Installer/MKDInstallerDefaultData.php';

class ExportProductDataController extends MKDInstallerCustomExportFormData
{

    public function __construct()
    {
        parent::__construct();
    }
      
    // Sekcja exportu na stronie edycji produktu
    public function initExportBlock($product_ID)
    {
        if ((int)$product_ID <= 0) {
            return;
        }
        
        // Dane do szablonu
        $smarty = Context::getContext()->smarty;


        // Pobierz lang i sklep
        $id_lang       = Context::getContext()->language->id;
        $lang_iso_code = Language::getIsoById($id_lang);
        $id_shop       = Context::getContext()->shop->id;


        // ======== EXPORT FILES ==========================================

        // Pobierz ustawienia modułu => export product form
        // $settings['export']['product']['EXPORT_FOLDER'] || $settings['service']['premium']['CMS_PAGE_ACCESS_LOCK']
        // ========================================
        // i przekaż zapisane dane do szablonu
        $settings = [
            'export'  => $this->getFormSettingsValues('product'),
            'service' => $this->getFormSettingsValues('premium')
        ];
        
        $smarty->assign('settings', $settings);

        // Załaduj pomocnicą klasę HelperModule Functionality (BO/FO)
        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Helper/HelperModule.php';
        $helperModule = new HelperModule();

        // Timer aktualizacji dla danego produktu (do porównania z timerem export file)
        $lastUpdateTimeAttachment = strtotime($helperModule->getLastTimeUpdateTable(_MKD_NAME_, 'data_upd', $product_ID) ?: 0);
        $lastUpdateTimeExtraField = strtotime($helperModule->getLastTimeUpdateTable(_MKD_NAME_.'_extra_product_fields_value', 'data_upd', $product_ID) ?: 0);
        $lastUpdateTimeProduct = strtotime((new Product($product_ID, false, $id_lang, $id_shop))->date_upd) ?: 0;

        // Przeszukaj export folder i pobierz dane o pliku
        $exportFileInfo = $helperModule->scanFolderForFile($product_ID, $settings['export']['product']['EXPORT_FOLDER'], $settings['export']['product']['EXPORT_FORMAT'], $lang_iso_code, $settings['export']['product']['EXPORT_FILE_PREFIX']);

        
        // Przekaż do szablonu
        $smarty->assign([
            // czas update załączników MKD, extra fields i produktu
            'attachTime'     => $lastUpdateTimeAttachment,
            'extraFieldTime' => $lastUpdateTimeExtraField,
            'productTime'    => $lastUpdateTimeProduct,
            
            // dane o export pliku
            'downloadPath' => $exportFileInfo['downloadPath'],
            'filePath'     => $exportFileInfo['filePath'],
            'fileName'     => $exportFileInfo['fileName'],
            'fileSize'     => $exportFileInfo['fileSize'],
            'fileTime'     => $exportFileInfo['fileTime']     
        ]);

        
       

        if ($settings['service']['premium']['EXPORT_DOWNLOAD_COUNTER']) {
            // Załaduj Model Liczników
            require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExportFilesModel.php');
            $fileCounterModel = new MKDExportFilesModel();
        
            
            // Sprawdź, czy istnieje już counter ID dla export file produktu (sklep, lang i format)
            $counter_ID = $fileCounterModel->getExportFileCounterForProductByFormat($id_shop, $id_lang, $product_ID, $settings['export']['product']['EXPORT_FORMAT']);
            
            // Pobierz wartość counter dla tego ID i dodaj do tablicy
            if ($counter_ID) {
                $exportFileCounters = $fileCounterModel->getDownloadCounterExportFileForProduct($counter_ID);
            }
            
        
            // Przypisz tablicę do smarty
            $smarty->assign('exportFileCounters', $exportFileCounters);
        }
        
        

        


        // Bieżąca data w nazwie pliku
        $currentDate = date('d-m-Y');

        // Pobierz ustawienia modułu => export product form
        $settings = $this->getFormSettingsValues('product');

        // Pobierz zapisane kolumny exportu
        $exportColumns = unserialize($settings['product']['EXPORT_COLUMNS']);
        

        // ================== Extra Fields Active ======================
        // Załaduj Model Extra Fields Poduct
        require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

        $extraFieldsModel      = new MKDExtraProductFieldsModel();
        $extraFieldsShopModel  = new MKDExtraProductFieldsShopModel();
        $extraFieldsLangModel  = new MKDExtraProductFieldsLangModel();

        $extraFieldsValueModel = new MKDExtraProductFieldsValueModel();


        // Sprawdź aktywne Extra Fields dla sklepu w opcjach Modułu
        $activeShopFields = $extraFieldsModel->getActiveExtraFieldsByShopId($id_shop);
       
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
        
                    $productFieldValue = $extraFieldsValueModel->getExtraFildValuesForProduct($id_shop, $product_ID, $active['id'], $lang_ID);
        
                    $productFieldValueLang[$active['id']][$lang_ID] = $productFieldValue;
                }
            }

            // Dane do szablonu
            $smarty->assign([
                'activeFields'  => $activeShopFields,
                'fieldTitle'    => $extraFieldTitleLang,
                'productValue'  => $productFieldValueLang,
            ]);
        }        
        
        // ============================================================
        
    }
   

    
    // =================================
    // Generowanie pliku Export ========
    // =================================
    public function ajaxProcessExportFile()
    {
        // Bieżąca data w nazwie pliku
        $currentDate = date('d-m-Y');

        // Pobierz dane z AJAX
        $id_shop    = (int) Tools::getValue('shopId', Context::getContext()->shop->id);
        $id_lang    = (int) Tools::getValue('langId', Context::getContext()->language->id);
        $product_ID = (int) Tools::getValue('productId');

        // Pobierz ustawienia modułu => export product form
        $settings = $this->getFormSettingsValues('product');
        

        if (Tools::getValue('action') === 'exportFile') {

            // Załaduj pomocnicą klasę HelperModule Functionality (BO/FO)
            require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Helper/HelperModule.php';
            $helperModule = new HelperModule();

            // Utwórz katalog jeśli nie istnieje (i nadrzędny)
            $exportPath = $helperModule->exportFilesProductDirectory($product_ID, $settings['product']['EXPORT_FOLDER'], 'create');
            

                       

            // 2. Pobierz multi-języki
            $languages = Language::getLanguages();
            
            // Iteruj po każdym języku
            foreach ($languages as $language) {

                // Wyrezować tablice dla każdego następnego języka
                // ===============================
                $exportData = [];   // Tablica danych do exportu
                $image_url  = [];   // Tablica obrazków produktu
                $filesData  = [];   // Tablica załączników produktu
                $moduleData = [];   // Tablica załączników modułu
                // ===============================
                
                // 3. Pobierz obiekt produktu => pełne dane true, język i sklep
                $product = new Product($product_ID, true, $language['id_lang'], $id_shop);
                
                // Sprawdź, czy produkt istnieje
                if (!Validate::isLoadedObject($product)) {
                    $response = [
                        'success' => false,
                        'message' => $this->l('Invalid product ID.', 'mkd_export-data-controller')
                    ];
                    // Zwróć odpowiedź JSON z błędem
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    return;
                }

                // 4. Generuj nazwę pliku na podstawie [iso_lang], link_rewrite[id_lang] (podany w Product), i bieżącej daty
                $productLink = $product->link_rewrite;
                $exportFileName = htmlspecialchars($language['iso_code'] . '_' . $settings['product']['EXPORT_FILE_PREFIX'] . '_' . $productLink . '_' . $currentDate);

                // 5. Pobierz zapisane Kolumny Produktu do exportu
                $exportColumns = unserialize($settings['product']['EXPORT_COLUMNS']);

                // 5.1. Pobierz dane z produktu i przygotuj do eksportu
                if (is_array($exportColumns)) {
                    
                    // Pobierz zaznaczone kolumny z modułu
                    foreach ($exportColumns as $column) {
                        
                        if ($column == 'id_product') {
                            $exportData['id_product'][$language['id_lang']] = $product->id;
                        }

                        if ($column == 'category_default') {
                            $category = new Category($product->id_category_default, $language['id_lang']);
                            $exportData['category'][$language['id_lang']] = $category->name; 
                        }

                        if ($column == 'name') {
                            $exportData['name'][$language['id_lang']] = $product->name;
                        }

                        if ($column == 'index') {
                            $exportData['index'][$language['id_lang']] = $product->reference;
                        }

                        if ($column == 'manufacturer') {
                            $manufacturer = new Manufacturer($product->id_manufacturer, $language['id_lang']);
                            $exportData['manufacturer'][$language['id_lang']] = $manufacturer->name;
                        }

                        if ($column == 'ean13') {
                            $exportData['ean13'][$language['id_lang']] = $product->ean13;
                        }

                        if ($column == 'description_short') {
                            $exportData['description_short'][$language['id_lang']] = $product->description_short;
                        }

                        if ($column == 'description') {
                            $exportData['description'][$language['id_lang']] = $product->description;
                        }

                        if ($column == 'price') {
                            $exportData['price'][$language['id_lang']] = $product->price;
                                // Pobierz kod waluty
                            $currencyId = Context::getContext()->currency->id;
                            $currency = new Currency($currencyId);
                            $exportData['currency'][$language['id_lang']] = $currency->iso_code;
                        }

                        if ($column == 'isbn') {
                            $exportData['isbn'][$language['id_lang']] = $product->isbn;
                        }

                        if ($column == 'upc') {
                            $exportData['upc'][$language['id_lang']] = $product->upc;
                        }

                        if ($column == 'mpn') {
                            $exportData['mpn'][$language['id_lang']] = $product->mpn;
                        }

                        if ($column == 'width') {
                            $exportData['width'][$language['id_lang']] = $product->width;
                        }

                        if ($column == 'height') {
                            $exportData['height'][$language['id_lang']] = $product->height;
                        }

                        if ($column == 'weight') {
                            $exportData['weight'][$language['id_lang']] = $product->weight;
                        }

                        if ($column == 'image') {
                            $db = Db::getInstance();
                        
                            $sql = 'SELECT img.id_image, img.cover, shop.id_shop, shop.id_image, shop.cover, tp.id_image_type, tp.name, tp.products
                                    FROM ' . _DB_PREFIX_ . 'image AS img
                                    JOIN ' . _DB_PREFIX_ . 'image_shop AS shop ON shop.id_shop = ' . $id_shop . ' AND shop.id_image = img.id_image
                                    JOIN ' . _DB_PREFIX_ . 'image_type AS tp ON tp.id_image_type = ' . $settings['product']['EXPORT_IMG_SIZE'] . ' AND tp.products = 1
                                    WHERE img.id_product = ' . $product_ID . '';
                        
                            if ($settings['product']['EXPORT_IMG_COVER'] == 1) {
                                $sql .= ' AND img.cover = 1';
                                $sql .= ' AND shop.cover = 1';
                            }
                        
                            $sql .= ' ORDER BY img.cover DESC, img.id_image ASC';
                        
                            $result = $db->executeS($sql);
                        
                            if ($result) {
                                foreach ($result as $row) {
                                    
                                    $imgSize_ID   = $row['id_image'];   // ID rozmiarów
                                    $imgSize_NAME = $row['name'];       // Nazwa Typów rozmiarów => id-typ-rozmiaru.jpg
                            
                                    // Pobierz zdjęcia gdy format XLS oraz włączone XLS_IMAGES
                                    if ($settings['product']['EXPORT_FORMAT'] == 'xls' && $settings['product']['FILE_XLS_IMAGES']) {

                                        $imgFolder = Image::getImgFolderStatic($row['id_image']);
                                        // Pobierz zdjęcia z folderu na podstawie ID oraz 'NAME IMAGE TYPE' (small, default itd)
                                        $image_url[] = _PS_PROD_IMG_DIR_ . $imgFolder . $row['id_image'] . '-' . $imgSize_NAME . '.jpg';

                                    } else {

                                    // Utwórz adresy URL obrazków
                                    $image_url[] = Context::getContext()->link->getImageLink($product->link_rewrite, $imgSize_ID, $imgSize_NAME);

                                    }               
                                }

                            } else {

                                $image_url[] = '';
                            }

                            $productImg = [];

                            foreach ($image_url as $image) {

                                $productImg[] = $image;
                            }

                            $exportData['image'] = implode(', ', $productImg);
                        }
                        


                        if ($column == 'attachments') {
                            // Załaduj Model MKD załączników
                            require_once(_PS_MODULE_DIR_. _MKD_NAME_ .'/models/MKDProductAttachmentsModel.php');
                            $model = new MKDProductAttachmentsModel();
                            
                            // Pobierz załączniki dla produktu() => product, języki wszystkie oraz języki do exportu(!!!)
                            $productAttachments = $model->getProductAttachmentsByProductId($product_ID, $language['id_lang'],  $language['id_lang']);
                            // Folder produktu
                            $downloadPath = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . $language['iso_code'] .'/index.php';

                            if ($productAttachments) {
                                foreach ($productAttachments as $file) {
                                    $db = Db::getInstance();
                                    // Zapytanie do BD na podstawie type_id załącznika => pobrać format grupy załączników
                                    $sql = 'SELECT format.value
                                            FROM ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_formats AS format
                                            JOIN ' . _DB_PREFIX_ . pSQL(_MKD_NAME_) . '_types_lang AS t ON t.type_id ='.$file['type_id'].'
                                            WHERE format.id = t.format AND shop_id = ' . $id_shop . ' AND active = 1';
                                    // Wykonaj zapytanie
                                    $result = $db->executeS($sql);
                                    // Pobierz pierwszy wiersz wyników
                                    $format = $result ? $result[0]['value'] : '---';
                            
                                    $moduleData[] = [
                                        'attachments_title' => $file['file_name'],
                                        'attachments_file' => preg_replace('/^\d{2}-\d{2}-\d{4}_\d+_/i', '', $file['file_url']),
                                        'attachments_date' => date("d-m-Y", strtotime($file['data_upd'])),
                                        'attachments_link' => $file['file_url'],
                                        'attachments_format' => $format,
                                        'attachment_path' => $downloadPath .'?fc=module&module=mkd_product_attachments&controller=Downloader&get=' . (int)$file['id']
                                    ];
                                }
                            }
                            
                            $exportData['attachments'][$language['id_lang']] = $moduleData;
                        }
                        
                        if ($column == 'files') {
                            $attachments = $product->getAttachments($language['id_lang']);
                            $controlerPath = Context::getContext()->link->getPageLink('attachment', true, $language['id_lang']);

                            if ($attachments) {
                                foreach ($attachments as $attachment) {
                                    $filesData[] = [
                                        'file_name' => $attachment['name'],
                                        'file_size' => $attachment['file_size'],
                                        'file_link' => $controlerPath . '?id_attachment=' . $attachment['id_attachment']
                                    ];
                                }
                            }
                            $exportData['files'][$language['id_lang']] = $filesData;
                        }
                        
                    }
                
                }

                // 6. Pobierz Extra Fields Active
                
                // =======================================
                // Załaduj Model Extra Fields Poduct
                require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

                $extraFieldsModel      = new MKDExtraProductFieldsModel();
                $extraFieldsShopModel  = new MKDExtraProductFieldsShopModel();
                $extraFieldsLangModel  = new MKDExtraProductFieldsLangModel();
                $extraFieldsValueModel = new MKDExtraProductFieldsValueModel();

                // Sprawdź aktywne Extra Fields dla sklepu w opcjach Modułu
                $activeShopFields = $extraFieldsModel->getActiveExtraFieldsByShopId($id_shop);

                if (is_array($activeShopFields)) {
                    foreach ($activeShopFields as $active) {

                        // Pobierz Title dla aktywnego ID extra field
                        $activeExtraFieldTitle = $extraFieldsLangModel->getExtraFieldTitleLangByFieldId($active['id'], $language['id_lang']);

                        // Sprawdź, czy istnieje Tytuł przed dodaniem (pole może być aktywne ale nie ma jeszcze nazwy)
                        if (!empty($activeExtraFieldTitle['title'])) {
                            $exportData['extra_fields'][$language['id_lang']]['field_title'][] = $activeExtraFieldTitle['title'];

                            // Pobierz Value dla aktywnego Title extra field
                            $productFieldValue = $extraFieldsValueModel->getExtraFildValuesForProduct($id_shop, $product_ID, $active['id'], $language['id_lang']);

                            // Ustaw $exportData tylko, jeśli istnieje value dla extra field value (!!!)
                            if (isset($productFieldValue['value']) && !empty(trim($productFieldValue['value']))) {

                                $exportData['extra_fields'][$language['id_lang']]['field_values'][] = $productFieldValue['value'];
                            }
                        }   
                        
                    }
                }

                


                // =================================================
                // Dobierz exporter i zapisz odpowiedni format pliku
                // =================================================

                switch ($settings['product']['EXPORT_FORMAT']) {
                    case 'csv':
                        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Exporter/Product_file_CSV.php';
                        $exporter = new exporterProductCSV();
                        break;
                    case 'xml':
                        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Exporter/Product_file_XML.php';
                        $exporter = new exporterProductXML();
                        break;
                    case 'json':
                        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Exporter/Product_file_JSON.php';
                        $exporter = new exporterProductJSON();
                        break;
                    case 'xls':
                        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Exporter/Product_file_XLS.php';
                        // Przekaż ustawienia Logo z installer (jeśli będzie załączone z ustawień modułu)
                        $exporterConfig = [
                            'xlsLogoWidth' => MKDInstallOptionCMSPageExportProduct::getConfig('xlsLogoWidth'),
                            'xlsLogoCell' => MKDInstallOptionCMSPageExportProduct::getConfig('xlsLogoCell')
                        ];
                        $exporter = new exporterProductXLS($exporterConfig);
                        break;
                        
                }
            
                // Utwórz pliki eksportu (multi- lang)
                $exporter->export($exportData, $language['id_lang'], $settings, $exportPath, $exportFileName);


                // Załaduj Model Liczników
                require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExportFilesModel.php');
                $fileCounterModel = new MKDExportFilesModel();

                // Sprawdź, czy istnieje już counter ID dla produktu (sklep, lang i format)
                $counter_ID = $fileCounterModel->getExportFileCounterForProductByFormat($id_shop, $language['id_lang'], $product_ID, $settings['product']['EXPORT_FORMAT']);


                // Utwórz nowy record(shop, lang, format) dla Licznika export pliku
                if (!$counter_ID) {

                    $fileCounterModel->id_shop = $id_shop;
                    $fileCounterModel->id_lang = $language['id_lang'];
                    $fileCounterModel->product_id = $product_ID;
                    $fileCounterModel->format = $settings['product']['EXPORT_FORMAT'];
                    $fileCounterModel->add();

                }
                
                
            }
            
        // Koniec foreach languages
        }
        
          
        // Wyświetlić powiadomienie w Ajax
        $response = [
            'success' => true,
            'message' => $this->l('The files was successfully generated', 'mkd_export-data-controller')
        ];
        

        // Ustaw odpowiednie nagłówki HTTP
        header('Content-Type: application/json');

        // Zwróć odpowiedź JSON
        echo json_encode($response);
    }



    // =================================
    // Usuwanie plików Export ========
    // =================================
    public function ajaxProcessDeleteExportFileFormat()
    {
        $id_shop        = (int) Tools::getValue('shopId', Context::getContext()->shop->id);
        $product_ID     = (int) Tools::getValue('productId');
        
        // Pobierz ustawienia modułu => export product form
        $settings = $this->getFormSettingsValues('product');

        // Usuń pliki exportu w formacie
        if (Tools::getValue('action') === 'deleteExportFileFormat') {

            
            require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExportFilesModel.php');
            $fileCounterModel = new MKDExportFilesModel();
            
            
            // Usuń też liczniki dla export plików
            $languages = Language::getLanguages();
            foreach ($languages as $language) {
                // Sprawdź, czy istnieje counter ID dla produktu (sklep, lang i format)
                $counter_ID = $fileCounterModel->getExportFileCounterForProductByFormat($id_shop, $language['id_lang'], $product_ID, $settings['product']['EXPORT_FORMAT']);
                
                if ($counter_ID) {
                    
                    $fileCounterModel->delete($counter_ID);
            
                }
            }

            

            // Załaduj pomocnicą klasę HelperModule Functionality (BO/FO)
            require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Helper/HelperModule.php';
            $helperModule = new HelperModule();

            // Sprawdź, czy katalog istnieje
            $exportPath = $helperModule->exportFilesProductDirectory($product_ID, $settings['product']['EXPORT_FOLDER']);

            // Sprawdź, czy katalog istnieje
            if (is_dir($exportPath)) {
                // Pobierz listę plików
                $files = scandir($exportPath);

                // Iteruj przez pliki i usuń tylko te o określonym rozszerzeniu
                foreach ($files as $file) {
                    $fileInfo = pathinfo($file);
                    if (isset($fileInfo['extension']) && $fileInfo['extension'] === $settings['product']['EXPORT_FORMAT']) {
                        unlink($exportPath . '/' . $file);
                    }
                }


                // Sprawdź, czy pliki zostały usunięte poprawnie
                $remainingFiles = scandir($exportPath);
                $filesDeleted = count($files) - count($remainingFiles);

                if ($filesDeleted > 0) {
                    $response = [
                        'success' => true,
                        'message' => $this->l('The files were deleted', 'mkd_export-data-controller')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => $this->l('No files matching the specified format were found', 'mkd_export-data-controller')
                    ];
                }
            } else {
                // Katalog nie istnieje
                $response = [
                    'success' => false,
                    'message' => $this->l('Folder not found', 'mkd_export-data-controller')
                ];
            }

            // Zwróć odpowiedź JSON
            header('Content-Type: application/json');
            echo json_encode($response);
        }

    }

}