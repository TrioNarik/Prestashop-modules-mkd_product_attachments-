<?php
/*
*
* Tworzenie instancji klasy "ProductExtraContent" dla każdej nowej zakładki w hooku displayProductExtraContent
*/


if (!defined('_PS_VERSION_')) {
    exit;
}


// Dostęp do oryginalnej klasy Zakładek/Tabs produktu: setTitle, setContent => tylko dla hook'a: displayProductExtraContent
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;



if (!defined('_MKD_NAME_')) {
    define('_MKD_NAME_', 'mkd_product_attachments');
}

if (!defined('_MKD_MODULE_VERSION_')) {
    define('_MKD_MODULE_VERSION_', '4.6.6');
}
if (!defined('_MKD_CONTROLLER_IDENTIFIER_')) {
    define('_MKD_CONTROLLER_IDENTIFIER_', _MKD_NAME_);
}
// Osobne renderForm() i renderTable()
// Komponenty dla odróżnienia przycisków formularzy konfiguracyjnych Modułu
if (!defined('_MKD_MODULE_COMPONENT_')) {
    define('_MKD_MODULE_COMPONENT_', ['types', 'programs', 'cmspage']);
}
// Folder docelowy załączników
if (!defined('_MKD_UPLOAD_DIR_')) {
    define('_MKD_UPLOAD_DIR_', _PS_IMG_DIR_ . 'attachments/product_');
}


class MKD_product_attachments extends Module
{

    public function __construct()
    {
        $this->name = _MKD_NAME_;
        $this->tab = 'administration';
        $this->version = _MKD_MODULE_VERSION_;
        $this->author = 'MKD';
        $this->need_instance = 0;
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->id_shop = Context::getContext()->shop->id;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->module_key = '5f37b62f4886a118c4bb7f70c27b54a0';

        parent::__construct();

        if (!defined('_MKD_MODULE_NAME_')) {
            define('_MKD_MODULE_NAME_', 'MKD · '. $this->l('Attachments, Extra Fields and Export product data Management'));
        }

        $this->bootstrap = true;
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module? All data and files will be deleted.');

        $this->displayName = _MKD_MODULE_NAME_;
        $this->description = $this->l('Product attachments Manager on the Product Display Page (PDP) and Export product data Manager with attachments on any CMS Page');
        $this->image = 'logo.png';

    }


    public function install()
    {
        // 1. Wymagane tabele:
        require_once __DIR__ . '/sql/install.php';


        // + Kontroler BO Export Files danych o produktcie
        Configuration::updateValue(
            'AJAX_EXPORT_FILES_CONTROLLER',
            'ExportProductData');

        // + Kontroler BO dodawania Załączników
        Configuration::updateValue(
            'AJAX_ATTACHMENTS_CONTROLLER',
            'ProductExtraAttach');

        // + Kontroler BO Extra Fields produktu
        Configuration::updateValue(
            'AJAX_EXTRA_FIELDS_CONTROLLER',
            'ExtraFieldsProduct',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        // + Kontroler BO Export Files danych o produktcie
        Configuration::updateValue(
            'AJAX_EXPORT_FILES_CONTROLLER',
            'ExportProductData',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        // + Kontroler BO dodawania Załączników
        Configuration::updateValue(
            'AJAX_ATTACHMENTS_CONTROLLER',
            'ProductExtraAttach',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );


        // 2. Wstaw podstawowe dane:
        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Installer/MKDInstallerDefaultData.php';
        
        $installer = new MKDInstallerDefaultData();

        // Zainstaluj podstawowe hooki dla strony produktu
        $installer->installDefaultData('hooksProductPage', _MKD_NAME_ . '_hooks');

        // Zainstaluj podstawowe formaty załączników
        $installer->installDefaultData('attachmentFormats', _MKD_NAME_ . '_formats');


        // 3. CMSPage/Export Form
        $installerCMSPageExportForm = new MKDInstallOptionCMSPageExportProduct();

        // Ustaw PREMIUM CMS Page/Export Form => $moduleKey = false, $action = 'update', $cmsLock = 0, $productCombinationsEnable = 0
        $installerCMSPageExportForm->setPremiumCMSPageExportFormConfig($this->module_key);
        // =============================================================================

        // Ustaw DOMYŚLNE CMS Page Form => $action = 'update', $cmsPageEnable = 1, $cmsPageSortBy = 'name', $cmsPageSortWay = 'ASC', $cmsPagePerPage = 12, $cmsPageViewMode = 'grid'
        $installerCMSPageExportForm->setDefaultFormCMSPageConfig();

        // Ustaw DOMYŚLNE Export Form => $action = 'update', $exportTimeMarker = false, $exportColumns = false, $exportEnable = 1, $exportFolder = '_export', $exportFilePrefix = 'info', $exportFormat = 'csv', $exportImgSize = '', $exportImgCover = 1
        $exportTimeMarker   = (int) time();
        $installerCMSPageExportForm->setDefaultFormExportConfig('update', $exportTimeMarker);
        
       
        // 5. Rejestruj hooki
        return parent::install()
            // Podstawowy hook do zakładek/Tabs na stronie produktu
            && $this->registerHook('displayProductExtraContent')    // PS > 1.7.0

            && $this->registerHook('displayProductActions')         // PS > 1.7.6
            && $this->registerHook('displayAfterProductThumbs')     // PS > 1.7.1
            && $this->registerHook('displayProductAdditionalInfo')  // PS > 1.7.1
            && $this->registerHook('displayFooterProduct')

            // Dodaj CSS/JS Front
            && $this->registerHook('displayHeader')

            // Hooki BO:
            // 1. dla załączników na stronie edycji produktu
            && $this->registerHook('displayAdminProductsExtra')
            // 2. do obsługi zapytań AJAX
            // Dodaj CSS/JS BO
            // && $this->registerHook('backOfficeHeader'); - przestarzały dla 8.0
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionAdminControllerSetMedia')

            // Dodawanie niestandardowych reguł Rewrite .htaccess
            && $this->registerHook('actionHtaccessCreate')

            // 3. do sprawdzenia Export File po aktualizacji produktu
            && $this->registerHook('actionProductSave')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionProductDelete')

            // 4. Hook dla CMSPage => SHORTCODE => wyświetlanie produktów i załączników
            && $this->registerHook('actionOutputHTMLBefore')    // Filtrowanie strony HTML przed jej wyrenderowaniem => only Front

            
            
            // dodatkowy hook dla CMS Page => do TPL zamiast ShortCode
            && $this->registerHook('displayCMSProductAttachments');
            
    }

    public function uninstall()
    {
        // 1. Usuń Tabele
        require_once __DIR__ . '/sql/uninstall.php';

        // 2. Usuń dane ajax Kontrolerów BO
        Configuration::deleteByName('AJAX_EXTRA_FIELDS_CONTROLLER');
        Configuration::deleteByName('AJAX_EXPORT_FILES_CONTROLLER');
        Configuration::deleteByName('AJAX_ATTACHMENTS_CONTROLLER');
        Configuration::deleteByName('AJAX_POSITION_PRODUCT_CONTROLLER');


        // 3. Usuń folder "attachments" i jego zawartość
        $uploadDirectory = _PS_IMG_ . 'attachments/';
        if (file_exists($uploadDirectory) && is_dir($uploadDirectory)) {
            $this->deleteDirectory($uploadDirectory);
        }

        // // 3. Usuń konfiguracje CMS Page/Export/Additional Form
        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Installer/MKDInstallerDefaultData.php';
        $cancelCMSPageExportForm = new MKDInstallOptionCMSPageExportProduct();
        $cancelCMSPageExportForm->setPremiumCMSPageExportFormConfig('delete');
        $cancelCMSPageExportForm->setDefaultFormCMSPageConfig('delete');
        $cancelCMSPageExportForm->setDefaultFormExportConfig('delete');

        // 4. Czyszczenie cache
        Tools::clearCache();

        return parent::uninstall();
    }

    // Usuń folder z załącznikami
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    // =============================================================================
    // ========================== HOOKI ============================================
    // =============================================================================
    // FRONT: addCSS && addJS
    public function hookDisplayHeader($params)
    {
        if ($this->context->controller->php_self == 'cms') {

            $this->context->controller->addCSS($this->_path . 'views/css/front_cms_page_products.css', 'all');
            $this->context->controller->addJS($this->_path . '/views/js/front_cms_page_products.js');

        }
    }


    public function hookDisplayProductExtraContent($params)
    {

        $hook_NAME = 'displayProductExtraContent';
        
        // Dodać do szablonu => inne #tabID
        $this->context->smarty->assign('hookName', $hook_NAME);

        $productExtraContents = array();

        // Tablice z danymi dla dodatkowych zakładek
        $extraTabsData = array(
            array(
                'title'     => $this->l('Download'),
                'content'   => $this->viewAttachmentsHook($hook_NAME, $params)
            ),
        );

        
        // Sprawdź, czy są grupy w danym hooku
        if ($this->viewAttachmentsHook($hook_NAME, $params)) {
            // Przetwarzanie danych zakładek tylko jeśli są grupy
            foreach ($extraTabsData as $tabData) {
                
                // Stwórz instancję klasy ProductExtraContent i ustaw tytuł oraz treść
                $productExtraContent = new ProductExtraContent();

                $productExtraContent->setTitle($tabData['title']);
                $productExtraContent->setContent($tabData['content']);

                $productExtraContents[] = $productExtraContent;
            }
        }

        return $productExtraContents;
    }

    public function hookDisplayProductActions($params)
    {

        // $hook_NAME = 'displayProductActions';

        // // Dodać do szablonu => inne #tabID
        // $this->context->smarty->assign('hookName', $hook_NAME);
        
        // return $this->viewAttachmentsHook($hook_NAME, $params);

    }


    public function hookDisplayAfterProductThumbs($params)
    {
        
        // $hook_NAME = 'displayAfterProductThumbs';

        // // Dodać do szablonu => inne #tabID
        // $this->context->smarty->assign('hookName', $hook_NAME);
        
        // return $this->viewAttachmentsHook($hook_NAME, $params);
      
    }

    public function hookDisplayProductAdditionalInfo($params)
    {

        // $hook_NAME = 'displayProductAdditionalInfo';

        // // Dodać do szablonu => inne #tabID
        // $this->context->smarty->assign('hookName', $hook_NAME);
        
        // return $this->viewAttachmentsHook($hook_NAME, $params);
      
    }

    public function hookDisplayFooterProduct($params)
    {
        // $hook_NAME = 'displayFooterProduct';

        // // Dodać do szablonu => inne #tabID
        // $this->context->smarty->assign('hookName', $hook_NAME);
        
        // return $this->viewAttachmentsHook($hook_NAME, $params);

    }


    //============================================================
    // CMS Page => SHORTCODE => produkty > załączniki i Export ===
    //============================================================
    public function hookActionOutputHTMLBefore($params)
    {
        $html = $params['html'];
        
        // Załączniki produktów
        $attachments = $this->hookDisplayCMSProductAttachments($html);
    
        // Zamiana shortcodu z różnymi możliwymi wariantami
        $patterns = [
            '/\{\{hookDisplayCMSProductAttachments\}\}/',
            '/<div>\{\{hookDisplayCMSProductAttachments\}\}<\/div>/',
            '/<p>\{\{hookDisplayCMSProductAttachments\}\}<\/p>/',
        ];
    
        foreach ($patterns as $pattern) {
            $html = preg_replace($pattern, $attachments, $html);
        }
    
        // Zaktualizuj zawartość w params
        $params['html'] = $html;
    
        // Przetworzony html
        return $html;
    }
    

    public function hookDisplayCMSProductAttachments($params)
    {
        // Dodaj obiekt klasy Link => getImageLink()
        Context::getContext()->link;

        // Pobierz sklep i lang
        $shop_ID = Context::getContext()->shop->id;
        $lang_ID = Context::getContext()->language->id;
        $lang_iso_code = Language::getIsoById($lang_ID);

        // Pobierz opcje CMS Page
        $cmsPageEnable = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_CMS_PAGE_VIEW_PRODUCTS_SWITCH',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id,
            1
        );
        
        $cmsPageAccess = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_CMS_PAGE_ACCESS_LOCK',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id,
            0
        );

        // Pobierz opcje CMP Page View
        $productSortBy = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_CMS_PAGE_VIEW_PRODUCTS_SORTBY',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id,
            'name'
        );
            
        $orderWay = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_CMS_PAGE_VIEW_PRODUCTS_SORTWAY',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id,
            'ASC'
        );

        $productPerPage = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_CMS_PAGE_VIEW_PRODUCTS_PERPAGE',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id,
            12
        );

        $productViewMode = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_CMS_PAGE_VIEW_PRODUCTS_VIEWMODE',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id,
            'grid'
        );

        $attachDownloadCounter = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_ATTACHMENT_DOWNLOAD_COUNTER',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        $this->context->smarty->assign(array(
            'productPerPage' => $productPerPage,
            'productViewMode' => $productViewMode,
            'attachDownloadCounter' => $attachDownloadCounter
        ));


        // Pobierz root Kategorię
        $rootCategory_ID = Category::getRootCategory($lang_ID)->id;

        // ==============================================================================
        // ============= Dostęp do CMS Page =============================================
        // Strona CMS jest wyłączona, przekieruj użytkownika na stronę kategorii (root)
        if (!$cmsPageEnable) {
            Tools::redirect(Context::getContext()->link->getCategoryLink($rootCategory_ID));
        }

        // Strona CMS jest zablokowana, a użytkownik nie jest zalogowany
        if ($cmsPageAccess == 1 && !Context::getContext()->customer->isLogged()) {
            // Dodaj komunikat
            Context::getContext()->controller->errors[] = $this->trans('Access to this page requires authentication. Already have an account?') . ' &#8594; <a href="' . Context::getContext()->link->getPageLink('authentication') . '">'.$this->trans('Sign In').'</a>';

            return;
            // Tools::redirect(Context::getContext()->link->getPageLink('authentication'));
        }
        // ==============================================================================
        

        // Pobierz kategorie (dla języka $lang_ID)
        $categories = Category::getCategories($lang_ID, true, false);
        
        // Pobierz podkategorie kategorii o ID 3
        $subCategories = Category::getChildren(3, $lang_ID);
        
        // Znajdź kategorię o ID 3 w tablicy $categories
        $category3 = null;
        foreach ($categories as $category) {
            if ($category['id_category'] == 3) {
                $category3 = $category;
                break;
            }
        }

        // Jeśli znaleziono kategorię 3, połącz ją z podkategoriami
        if ($category3) {
            $allCategories = array_merge(array($category3), $subCategories);
        } else {
            $allCategories = $subCategories;
        }


        // Pobierz ID kategorii z URL
        $currentCategory_ID = (int)Tools::getValue('category');


        if (!$currentCategory_ID) {
            $currentCategory_ID = 3;
        }

        // Pobierz wszystkie produkty z danej kategorii
        $products = Product::getProducts($lang_ID, 0, 0, $productSortBy, $orderWay, $currentCategory_ID, true);

        $this->context->smarty->assign(array(
            'rootCategory_ID' => $rootCategory_ID,
            'categories' => $categories,
            'currentCategory_ID' => $currentCategory_ID, // ustaw 'class' dla bieżącej kategorii produktów
            'products' => $products,
            'allCategories' => $allCategories
        ));


        // Załaduj pomocnicą klasę HelperModule Functionality (BO/FO)
        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Helper/HelperModule.php';
        $helperModule = new HelperModule();

        // Załaduj Manager załączników dla danego produktu:
        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/MKDFrontAttachmentsProductManager.php';
        $frontViewerManager = new MKDFrontAttachmentsProductManager();

        // Pobierz listę aktywnych Grup załączników
        $activeGroupsAttachment = $frontViewerManager->getActiveAttachmentGroupsForCMSPage($shop_ID, $lang_ID);

        // Pobierz ID grupy załącnzików z URL
        $currentGroup_ID = (int)Tools::getValue('group');

        $this->context->smarty->assign(array(
            'activeGroupsAttachment' => $activeGroupsAttachment,
            'currentGroup_ID' => $currentGroup_ID // ustaw 'class' dla bieżącej kategorii załączników
        ));


        // Pobierz opcje Exportu
        $exportFolder = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_EXPORT_FOLDER',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
        $exportFileFormat = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_EXPORT_FORMAT',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
        $exportFilePrefix = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_EXPORT_FILE_PREFIX',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
      
        $exportFileDownloadCounterSwitch = Configuration::get(
            'CONF_' . strtoupper(_MKD_NAME_) . '_EXPORT_DOWNLOAD_COUNTER',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        // Załaduj Model Liczników Export File
        require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExportFilesModel.php');
        $fileCounterModel = new MKDExportFilesModel();


        // Pobierz listę aktywnych Załączników w dostępnych/current Grupach/-ie posortowaną wg position
        // oraz pobierz Export file i export Counter
        $activeAttachments = [];
        $exportFile = [];
        $exportFileCounter =[];
        

        foreach ($products as $product) {
            $product_ID = (int)$product['id_product'];

            // ================================
            // Pobranie okładki produktu
            // ================================
            $anyProduct = new Product($product_ID, true, $lang_ID, $shop_ID);

            $coverImage = $anyProduct->getCover($product_ID);

            // Ustawienie ID obrazka (cover)
            if ($coverImage) {
                $imageID = $coverImage['id_image'];
            } else {
                $imageID = ''; // Domyślne ID obrazka, gdy nie ma okładki
            }

            // Przekazanie ID obrazka
            $this->context->smarty->assign('coverImageID_'.$product_ID, $imageID);

            // ===========================

            $attachments = $frontViewerManager->getActiveAttachmentsForProductInGroup($shop_ID, $lang_ID, 'position', 'ASC', $product_ID, $currentGroup_ID);
            $activeAttachments[$product_ID] = $attachments;

            // Przeszukaj export folder i pobierz dane o pliku
            $exportFileInfo = $helperModule->scanFolderForFile($product_ID, $exportFolder, $exportFileFormat, $lang_iso_code, $exportFilePrefix);
            $exportFile[$product_ID] = $exportFileInfo['fileName'];
            $exportFormat[$product_ID] = $exportFileInfo['fileName'];

            // Sprawdź, czy istnieje już counter ID dla export file produktu (sklep, lang i format)
            $counter_ID = $fileCounterModel->getExportFileCounterForProductByFormat($shop_ID, $lang_ID, $product_ID, $exportFileFormat);
            // Sprawdź czy isnieje counter dla dego ID
            if ($counter_ID) {
                $exportFileCounter[$product_ID] = $fileCounterModel->getDownloadCounterExportFileForProduct($counter_ID);
            }
            
        }
        


        $this->context->smarty->assign(array(
            'activeAttachments' => $activeAttachments,
            'exportFile' =>  $exportFile,
            'exportFormat' => $exportFileFormat,
            'exportFileCounterSwitch' => $exportFileDownloadCounterSwitch,
            'exportFileCounter' => $exportFileCounter
        ));


        return $this->display(__FILE__, 'views/templates/hook/cms_page_attachments.tpl');
    }


    //========================================
    // BO => Product Page Edit => Moduły   ===
    //========================================
    public function hookDisplayAdminProductsExtra($params)
    {

        // Pobierz ID edytowanego produktu
        $product_ID = (int) $params['id_product'];
        
        // Pobierz obiekt produktu
        $product = new Product($product_ID);


        if (Validate::isLoadedObject($product)) {

            // 1. Kontroler Position product in Category => Form => Select:
            require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/controllers/admin/PositionProductController.php';
            $positionController = new PositionProductController();
            $positionController->initPositionProductInCategory($product_ID,  $product->id_category_default);

            // 2. Kontroler Extra Fields produktu => Form:
            require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/controllers/admin/ExtraFieldsProductController.php';
            $extraController = new ExtraFieldsProductController();
            $extraController->initExtraFieldsBlock($product_ID);

            // 3. Kontroler Exportu danych o produkcie => Buttons:
            require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/controllers/admin/ExportProductDataController.php';
            $exportController = new ExportProductDataController();
            $exportController->initExportBlock($product_ID);

            // 4. Kontroler Załączników i Grup => Modal Form i Table:
            require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/controllers/admin/ProductExtraAttachController.php';
            $controller = new ProductExtraAttachController();
            $controller->initForm($product_ID);

            // Renderuj wspólny szablon
            return $this->display(__FILE__, 'views/templates/admin/product_module_form.tpl');
        }

    }

    //=====================================================
    // BO => AJAX Hook ========== controller....&ajax=1 ===
    //=====================================================
    public function hookDisplayBackOfficeHeader()
    {

        $this->context->controller->addJS($this->_path.'/views/js/admin_product_attachments.js');
        $this->context->controller->addJS($this->_path.'/views/js/admin_product_export_file.js');
        $this->context->controller->addJS($this->_path.'/views/js/admin_product_extra_field.js');
        $this->context->controller->addJS($this->_path.'/views/js/admin_product_position_category.js');


        Media::addJsDef(array(
            'hookBackOfficeHeader_positionProduct' => array(
                // 1. Product Position in Category => do obsługi ajax na stronie edycji produktu
                'ajaxUrl' => $this->context->link->getAdminLink(Configuration::get('AJAX_PRODUCT_POSITION_CONTROLLER'), true) . '&ajax=1',
            ),
            'hookBackOfficeHeader_extraFields' => array(
                // 2. Extra Fields => do obsługi ajax na stronie edycji produktu
                'ajaxUrl' => $this->context->link->getAdminLink(Configuration::get('AJAX_EXTRA_FIELDS_CONTROLLER'), true) . '&ajax=1',
            ),
            'hookBackOfficeHeader_exportFiles' => array(
                // 3. Export Files => do obsługi ajax na stronie edycji produktu
                'ajaxUrl' => $this->context->link->getAdminLink(Configuration::get('AJAX_EXPORT_FILES_CONTROLLER'), true) . '&ajax=1',
            ),
            'hookBackOfficeHeader_attachments' => array(
                // 4. Attachments => do obsługi ajax na stronie edycji produktu
                'ajaxUrl' => $this->context->link->getAdminLink(Configuration::get('AJAX_ATTACHMENTS_CONTROLLER'), true) . '&ajax=1',

                // do walidacji formatów i rozmiarów przesyłanych plików w formularzu - wersja beta (!!!)
                // 'jsonSampleDataFile'    => __PS_BASE_URI__ . 'modules/' . _MKD_NAME_ . '/SampleDataDefault.json'
            )
        ));
    }

    // BO: addCSS && addJS
    public function hookActionAdminControllerSetMedia($params)
    {
        $controller = Tools::getValue('controller');
        $configure = Tools::getValue('configure');

        // Dodanie CSS/JS tylko dla określonego kontrolera i konfiguracji
        if ($controller === 'AdminModules' && $configure === _MKD_NAME_) {
            // $this->context->controller->addCSS($this->_path . 'views/admin/css/mkd_form.css');
            // $this->context->controller->addJS($this->_path . 'views/admin/js/mkd_form.js');
            $this->context->controller->addJS($this->_path.'/views/js/modul_export_image_size.js');

        }
    }

    //=====================================================
    // BO => Update/Delete Product => Export File =========
    //=====================================================
    public function hookActionProductSave($params)
    {
        // $params['id_product'] zawiera identyfikator zapisanego produktu
        // Działania po zapisaniu produktu
    }
    public function hookActionProductUpdate($params)
    {
        // $params['id_product'] zawiera identyfikator zapisanego produktu
        // Działania po aktualizacji produktu
    }
    public function hookActionProductDelete($params)
    {
        // $params['id_product'] zawiera identyfikator zapisanego produktu
        // Działania po usunięciu produktu
    }

    //=====================================================
    // Pomocnicze Hooki ===================================
    //=====================================================
    public function hookActionHtaccessCreate($params)
    {
        // Twój kod dostosowania pliku .htaccess
        // $params zawiera informacje, które mogą być używane do dostosowania działań w zależności od potrzeb
    }
    public function hookActionModuleInstallAfter($params)
    {
        // Nie musi być rejestrowany, automatycznie obsługiwany
        //
        // // Przykładowe zastosowanie: ustawienia domyślne dla nowo zainstalowanego modułu
        // if ($module->name == 'examplemodule') {
        //     Configuration::updateValue('EXAMPLEMODULE_SETTING', 'default_value');
        // }
    }
    
    // =============================================================================
    // ========================== KONFIGURACJA MODUŁU ==============================
    // =============================================================================  
    public function getContent()
    {
        $output = '';

        // Kontroler Groups Załączników Config => Form i Table:
        require_once _PS_MODULE_DIR_ . _MKD_NAME_. '/controllers/admin/AdminModuleConfigFormController.php';

        // Kontroler Programs Załączników Config => Form i Table:
        require_once _PS_MODULE_DIR_ . _MKD_NAME_. '/controllers/admin/AdminProgramsConfigFormController.php';

        // Kontroler CMS Page / Export File / Extra Fields => Form:
        require_once _PS_MODULE_DIR_ . _MKD_NAME_. '/controllers/admin/AdminCMSPageExportController.php';
        
        // 1. Grupa załączników
        $adminController = new AdminModuleConfigFormController();
        $adminController->postProcess();


        if (Tools::isSubmit('add_group_type')) {
            
            // Renderuj Formularz dla Typów załączników
            $output .= $adminController->renderTypesForm();

        } elseif (Tools::isSubmit('update' . _MKD_MODULE_COMPONENT_[0])) {

            $editId = (int) Tools::getValue('id');
            $output .= $adminController->renderTypesForm($editId);

        
        } else {

            $output .= $adminController->renderTypesTable();

            // Przycisk dodawania nowej Grupy załączników (type)
            $output .= '<a href="' . $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&add_group_type" class="btn btn-primary">';
            $output .= $this->l('Add New Group') . '</a>';
            $output .= '<hr />';

        }

        // 2. Programy do załączników
        $programsController = new AdminProgramsConfigFormController();
        $programsController->postProcess();
              

        if (Tools::isSubmit('add_program')) {
            
            $output .= $programsController->renderProgramsForm();
        }

        if (Tools::isSubmit('update' . _MKD_MODULE_COMPONENT_[1])) { {

            $editId = (int) Tools::getValue('id');
            $output .= $programsController->renderProgramsForm($editId);

            }
        }

        $output .= $programsController->renderProgramsTable();

        // Przycisk dodawania nowego Programu Załączników
        $output .= '<a href="' . $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&add_program" class="btn btn-primary">';
        $output .= $this->l('Add New program') . '</a>';
        $output .= '<hr />';
        

        // 3. Ustawienia dla CMS Page i Export produkt
        $CMSpageExportController = new AdminCMSPageExportController();
        $CMSpageExportController->postProcess();

        // Wyświelić tylko, gdy nie ma innych formularzy Modułu (attachment/program)
        if (!Tools::getIsset('add_program') 
            && !Tools::getIsset('update' . _MKD_MODULE_COMPONENT_[0]) 
            && !Tools::getIsset('add_group_type') 
            && !Tools::getIsset('update' . _MKD_MODULE_COMPONENT_[1])) {

            $output .= $CMSpageExportController->renderCMSPageExportOptionForm();
        }

        
        return $output;
    }


    // =============================================================================
    // ====== WYŚWIETLANIA ZAŁĄCZNIKÓW W HOOKACH NA STRONIE PRODUKTU ===============
    // =============================================================================
    private function viewAttachmentsHook($hook_NAME, $params)
    {
        // Załaduj Manager załączników dla danego produktu:
        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/MKDFrontAttachmentsProductManager.php';
        $frontViewerManager = new MKDFrontAttachmentsProductManager();

        // Pobierz ID sklepu
        $shop_ID = (int) Context::getContext()->shop->id;

        // Pobierz Lang sklepu
        $lang_ID = (int) Context::getContext()->language->id;

        // Pobierz wszystkie dostępne grupy użytkowników w PS
        $userGroups = Group::getGroups($lang_ID);
        $userGroupsNames = array();
        foreach ($userGroups as $group) {
            $userGroupsNames[] = $group['name'];
        }

        if (isset($params['product'])) {
            $product_ID = (int) $params['product']->id;

            $downloadPath = __PS_BASE_URI__ . _MKD_UPLOAD_DIR_ . $product_ID;

            // Pobierz listę załączników dla produktu
            $attachments = $frontViewerManager->getAttachmentsForProduct($hook_NAME, $product_ID, $shop_ID, $lang_ID, 'position', 'ASC');

            // Sprawdź, czy istnieją załączniki
            if (!empty($attachments)) {
                // Przekaż dane do szablonu
                $this->context->smarty->assign([
                    'attachments' => $attachments,
                    'downloadPath' => $downloadPath,
                    'allUserGroups' => $userGroupsNames
                ]);

                return $this->display(__FILE__, 'views/templates/hook/product_attachments.tpl');
            }
        }
    }


}
