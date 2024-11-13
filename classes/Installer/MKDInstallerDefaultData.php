<?php
/*
* 1. Domyślne Ustawienia formularza CMS Page & Export file
*
* 2. Install() => podstawowe dane do BD: hooki i formaty załaczników z JSON
*
*
* Configuration::updateValue(
*    $key,       // Klucz konfiguracji
*    $value,     // Wartość
*    false,      // Czy wartość ma być zapisana jako HTML (true/false)
*    Context::getContext()->shop->id_shop_group, // ID grupy sklepu (dla wielu sklepów)
*    Context::getContext()->shop->id    //ID sklepu (dla wielu sklepów)
* );
*
*/

class MKDInstallOptionCMSPageExportProduct {

    // =========================================
    // Konfiguracja CMS Page/Export/Extra fields
    // =========================================
    public static function getConfig($key)
    {

        $config = [
            'prefix' => 'CONF_' . strtoupper(_MKD_NAME_) . '_',  // ustawienia Configutation::update/get()
            'mode'   => 'MODE_' . strtoupper(_MKD_NAME_) . '_',  // tryb modułu => premium

            'xlsLogoWidth' => 200,      // Rozmiar logo w XLS
            'xlsLogoCell' => 'A20',    // Komórka logo w XLS

            'numberExtaProductFields' => 2  // Ilość Extra pól Produktu (default = 1)
        ];

        return $config[$key];
        
    }

    // Sprawdź tryb Modułu => return 'module_key'
    public static function getModuleMode()
    {

        return Configuration::get(
            self::getConfig('mode') . 'PREMIUM',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
    }

    // PREMIUM opcje konfiguracyjne CMS Page/Export
    public static function setPremiumCMSPageExportFormConfig($moduleKey = false, $action = 'update', $cmsLock = 0, $productCombinationsEnable = 0, $attachmentDownloadCounter = 0, $attachmentViewsCounter = 0, $exportDownloadCounter = 0)
    {
        // Włącz tryb PREMIUM
        if (isset($moduleKey) && !empty($moduleKey) && $action == 'update') {

            Configuration::updateValue(
                self::getConfig('mode') . 'PREMIUM', 
                $moduleKey,
                false,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            );
        }

        $configValues = array(
            self::getConfig('prefix') . 'CMS_PAGE_ACCESS_LOCK'         => (int) $cmsLock,
            // Kombinacje produktu
            self::getConfig('prefix') . 'EXPORT_PRODUCT_COMBINATIONS'  => (int) $productCombinationsEnable,
            // Counters
            self::getConfig('prefix') . 'ATTACHMENT_DOWNLOAD_COUNTER'  => (int) $attachmentDownloadCounter,
            self::getConfig('prefix') . 'ATTACHMENT_VIEWS_COUNTER'     => (int) $attachmentViewsCounter,
            self::getConfig('prefix') . 'EXPORT_DOWNLOAD_COUNTER'      => (int) $exportDownloadCounter,
        );

        // Tylko dla akcji GET
        $additionalFormOptions = [];

        foreach ($configValues as $configKey => $configValue) {
            if ($action === 'update') {

                // Sprawdź tryb PREMIUM
                if (!empty(self::getModuleMode())) {

                    Configuration::updateValue(
                        $configKey,
                        $configValue,
                        false,
                        Context::getContext()->shop->id_shop_group,
                        Context::getContext()->shop->id
                    );

                    Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('Additional Settings was updated successfully.');
                    
                }

            } elseif ($action === 'delete') {

                if (!empty(self::getModuleMode())) {
                    
                    Configuration::deleteByName($configKey);
                }

            } elseif ($action === 'get') {

                if (!empty(self::getModuleMode())) {

                    $additionalFormOptions[$configKey] = Configuration::get(
                        $configKey,
                        false,
                        Context::getContext()->shop->id_shop_group,
                        Context::getContext()->shop->id
                    );
                }
            }
        }

        return $additionalFormOptions;

    }

    // Podstawowe opcje CMS Page Product Form
    public static function setDefaultFormCMSPageConfig($action = 'update', $cmsPageEnable = 1, $cmsPageSortBy = 'name', $cmsPageSortWay = 'ASC', $cmsPagePerPage = 12, $cmsPageViewMode = 'grid')
    {

        $configValues = array(
            self::getConfig('prefix') . 'CMS_PAGE_VIEW_PRODUCTS_SWITCH'   => (int) $cmsPageEnable,
            self::getConfig('prefix') . 'CMS_PAGE_VIEW_PRODUCTS_SORTBY'   => $cmsPageSortBy,
            self::getConfig('prefix') . 'CMS_PAGE_VIEW_PRODUCTS_SORTWAY'  => $cmsPageSortWay,
            self::getConfig('prefix') . 'CMS_PAGE_VIEW_PRODUCTS_PERPAGE'  => (int) $cmsPagePerPage,
            self::getConfig('prefix') . 'CMS_PAGE_VIEW_PRODUCTS_VIEWMODE' => $cmsPageViewMode
        );

        // Tylko dla akcji GET
        $cmsPageFormOptions = [];

        foreach ($configValues as $configKey => $configValue) {
            if ($action === 'update') {
                
                Configuration::updateValue(
                    $configKey,
                    $configValue,
                    false,
                    Context::getContext()->shop->id_shop_group,
                    Context::getContext()->shop->id
                );
                           
                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('CMS Page settings was updated successfully.');

            } elseif ($action === 'delete') {

                Configuration::deleteByName($configKey);

            } elseif ($action === 'get') {

                $cmsPageFormOptions[$configKey] = Configuration::get(
                    $configKey,
                    false,
                    Context::getContext()->shop->id_shop_group,
                    Context::getContext()->shop->id
                );
            }
        }

        return $cmsPageFormOptions;

    }

    // Podstawowe opcje Export Product && XLS File Form
    public static function setDefaultFormExportConfig($action = 'update', $exportTimeMarker = false, $exportColumns = false, $exportEnable = 1, $exportFolder = '_export', $exportFilePrefix = 'info', $exportFormat = 'csv', $exportImgSize = 1, $exportImgCover = 1, $fileXLSColor = '#012d5a', $fileXLSLogo = 0, $fileXLSImages = 0)
    {
        
        if ($exportColumns === false) { // Reset kolumn
            $exportColumns = self::setDefaultExportProductColumns();
        }
        
        $configValues = array(
            self::getConfig('prefix') . 'EXPORT_SWITCH'          => (int) $exportEnable,
            self::getConfig('prefix') . 'EXPORT_FOLDER'          => $exportFolder,
            self::getConfig('prefix') . 'EXPORT_FILE_PREFIX'     => $exportFilePrefix,
            self::getConfig('prefix') . 'EXPORT_FORMAT'          => $exportFormat,
            self::getConfig('prefix') . 'EXPORT_COLUMNS'         => $exportColumns,
            self::getConfig('prefix') . 'EXPORT_TIMEMARKER'      => (int) $exportTimeMarker,
            self::getConfig('prefix') . 'EXPORT_IMG_SIZE'        => (int) $exportImgSize,
            self::getConfig('prefix') . 'EXPORT_IMG_COVER'       => (int) $exportImgCover,
            self::getConfig('prefix') . 'FILE_XLS_COLOR'         => $fileXLSColor,
            self::getConfig('prefix') . 'FILE_XLS_LOGO'          => (int) $fileXLSLogo,
            self::getConfig('prefix') . 'FILE_XLS_IMAGES'        => (int) $fileXLSImages
        );

        // Tylko dla akcji GET
        $exportFormOptions = [];
        
        foreach ($configValues as $configKey => $configValue) {
            if ($action === 'update') {
                
                Configuration::updateValue(
                    $configKey,
                    $configValue,
                    false,
                    Context::getContext()->shop->id_shop_group,
                    Context::getContext()->shop->id
                );

                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('Export Files settings was updated successfully.');
                Context::getContext()->controller->warnings[] = Context::getContext()->getTranslator()->trans('If there have been changes in the Export File settings, the product export files will need to be updated.');

            } elseif ($action === 'delete') {

                Configuration::deleteByName($configKey);

            } elseif ($action === 'get') {
               
                $exportFormOptions[$configKey] = Configuration::get(
                    $configKey,
                    false,
                    Context::getContext()->shop->id_shop_group,
                    Context::getContext()->shop->id
                );

            }
        }

        return $exportFormOptions;

    }

    // Ustaw DOMYŚLNIE zaznaczone kolumny Exportu
    public static function setDefaultExportProductColumns()
    {

        $productColumns = MKDInstallerCustomExportFormData::installCustomExportProductColumn();

        $defaultColumns = [];
        foreach ($productColumns as $column) {
            if ($column['default'] === true) {
                $defaultColumns[] = $column['val'];
            }
        }
        return serialize($defaultColumns);
    }
}

class MKDInstallerCustomExportFormData extends ModuleAdminController
{

    public function __construct()
    {
        parent::__construct();
        
    }

    // Wybrane opcje sortowania produktów CMS Page
    public function installCustomCMSPageSortByOption()
    {
        $sortBy = array(
            array(
                'value' => 'id_product',
                'name_option' => Context::getContext()->getTranslator()->trans('ID Produktu'),
                'default' => true
            ),
            array(
                'value' => 'name',
                'name_option' => Context::getContext()->getTranslator()->trans('Nazwa produktu'),
                'default' => true
            ),
            array(
                'value' => 'date_add',
                'name_option' => Context::getContext()->getTranslator()->trans('Najnowsze'),
                'default' => false
            ),
            array(
                'value' => 'date_upd',
                'name_option' => Context::getContext()->getTranslator()->trans('Zaktualizowane'),
                'default' => false
            ),
            array(
                'value' => 'price',
                'name_option' => Context::getContext()->getTranslator()->trans('Cena'),
                'default' => true
            ),
            array(
                'value' => 'position',
                'name_option' => Context::getContext()->getTranslator()->trans('Pozycja w kategorii'),
                'default' => false
            ),
            array(
                'value' => 'quantity',
                'name_option' => Context::getContext()->getTranslator()->trans('Ilość'),
                'default' => false
            ),
            array(
                'value' => 'manufacturer',
                'name_option' => Context::getContext()->getTranslator()->trans('Producent/Marka'),
                'default' => false
            ),
            array(
                'value' => 'width',
                'name_option' => Context::getContext()->getTranslator()->trans('Szerokość'),
                'default' => false
            ),
            array(
                'value' => 'height',
                'name_option' => Context::getContext()->getTranslator()->trans('Wysokość'),
                'default' => false
            ),
            array(
                'value' => 'weight',
                'name_option' => Context::getContext()->getTranslator()->trans('Waga'),
                'default' => false
            )          
        );
        return $sortBy;
    }

    // Wszystkie kolumny Exportu produktu
    public static function installCustomExportProductColumn()
    {
        $columns = array(
            array(
                'id' => 1,
                'name' => Context::getContext()->getTranslator()->trans('ID Produktu'),
                'val' => 'id_product',
                'default' => true
            ),
            array(
                'id' => 2,
                'name' => Context::getContext()->getTranslator()->trans('Kategoria') . ' <sup title="" data-toggle="tooltip" class="label-tooltip" data-original-title="'. Context::getContext()->getTranslator()->trans('Domyślna kategoria produktu') .'" data-html="true" data-placement="top"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" fill="#25b9d7" /></svg></sup>',
                'val' => 'category_default',
                'default' => true
            ),
            array(
                'id' => 3,
                'name' => Context::getContext()->getTranslator()->trans('Nazwa produktu'),
                'val' => 'name',
                'default' => true
            ),
            array(
                'id' => 4,
                'name' => Context::getContext()->getTranslator()->trans('Index [identyfikator]'),
                'val' => 'index',
                'default' => false
            ),
            array(
                'id' => 5,
                'name' => Context::getContext()->getTranslator()->trans('Producent'),
                'val' => 'manufacturer',
                'default' => false
            ),
            array(
                'id' => 6,
                'name' => Context::getContext()->getTranslator()->trans('EAN - European Article Number'),
                'val' => 'ean13',
                'default' => false
            ),
            array(
                'id' => 7,
                'name' => Context::getContext()->getTranslator()->trans('Krótki opis'),
                'val' => 'description_short',
                'default' => true
            ),
            array(
                'id' => 8,
                'name' => Context::getContext()->getTranslator()->trans('Opis'),
                'val' => 'description',
                'default' => true
            ),
            array(
                'id' => 9,
                'name' => Context::getContext()->getTranslator()->trans('ISBN - International Standard Book Number'),
                'val' => 'isbn',
                'default' => false
            ),
            array(
                'id' => 10,
                'name' => Context::getContext()->getTranslator()->trans('UPC - Universal Product Code'),
                'val' => 'upc',
                'default' => false
            ),
            array(
                'id' => 11,
                'name' => Context::getContext()->getTranslator()->trans('MPN - Manufacturer Part Number'),
                'val' => 'mpn',
                'default' => false
            ),
            array(
                'id' => 12,
                'name' => Context::getContext()->getTranslator()->trans('Cena / [waluta]'),
                'val' => 'price',
                'default' => true
            ),
            array(
                'id' => 13,
                'name' => Context::getContext()->getTranslator()->trans('Zdjęcia') . ' <sup title="" data-toggle="tooltip" class="label-tooltip" data-original-title="'. Context::getContext()->getTranslator()->trans('Proszę także wybrać Typ obrazu i konkretny Rozmiar obrazu dostępny dla PRODUKTÓW w ustawieniach obrazu zainstalowanych w sklepie') .'" data-html="true" data-placement="top"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z" fill="#fab000" /></svg></sup>',
                'val' => 'image',
                'default' => true
            ),
            array(
                'id' => 14,
                'name' => Context::getContext()->getTranslator()->trans('Szerokość produktu'),
                'val' => 'width',
                'default' => false
            ),
            array(
                'id' => 15,
                'name' => Context::getContext()->getTranslator()->trans('Wysokość produktu'),
                'val' => 'height',
                'default' => false
            ),
            array(
                'id' => 16,
                'name' => Context::getContext()->getTranslator()->trans('Waga produktu'),
                'val' => 'weight',
                'default' => false
            ),
            array(
                'id' => 17,
                'name' => Context::getContext()->getTranslator()->trans('Załączniki [Module]') . '  <sup title="" data-toggle="tooltip" class="label-tooltip" data-original-title="'. Context::getContext()->getTranslator()->trans('Files attached by Module') .': &#171;' . _MKD_MODULE_NAME_ . ' [v' . _MKD_MODULE_VERSION_ .'] &#187;" data-html="true" data-placement="top"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" fill="#25b9d7" /></svg></sup>',
                'val' => 'attachments',
                'default' => true
            ),
            array(
                'id' => 18,
                'name' => Context::getContext()->getTranslator()->trans('Pliki [Produktu]'),
                'val' => 'files',
                'default' => false
            ),
        );
       
        return $columns;
    }

    // Ustaw DOMYŚLNIE zaznaczone kolumny Exportu
    public function setDefaultExportProductColumns()
    {

        $productColumns = $this->installCustomExportProductColumn();

        $defaultColumns = [];
        foreach ($productColumns as $column) {
            if ($column['default'] === true) {
                $defaultColumns[] = $column['val'];
            }
        }
        return serialize($defaultColumns);
    }

    // Dostępne w PS rozmiary obrazków
    public function installCustomImageSize()
    {
        // Pobierz rozmiary obrazków tylko dla produktów == 1 (!) z szerokością i wysokością
        $sql = 'SELECT `id_image_type`, `name`, `width`, `height`  FROM ' . _DB_PREFIX_ . 'image_type  WHERE products = 1';

        $results = Db::getInstance()->executeS($sql);

        $exportImgSize = [];

        foreach ($results as $result) {
        $exportImgSize[] = array(
            'value' => $result['id_image_type'],
            'name_option' => $result['name'] .' - ['. $result['width'] .'x'. $result['height'] .'px]',
            );
        }

        
        return $exportImgSize;

    }

    // ==========================================================
    // Pobierz ustawienia CMS Page & Export ==== BO & Front =====
    // ==========================================================
    public function getFormSettingsValues($form = 'product')
    {
        $formSettings = [];

        switch ($form) {
            case 'cms':
                $exportFormOptions = MKDInstallOptionCMSPageExportProduct::setPremiumCMSPageExportFormConfig('get');
                break;

            case 'product':
                $exportFormOptions = MKDInstallOptionCMSPageExportProduct::setDefaultFormExportConfig('get');
                break;

            case 'premium':
                if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {
                    $exportFormOptions = MKDInstallOptionCMSPageExportProduct::setPremiumCMSPageExportFormConfig(false, 'get');
                }
                break;
        }

        if (isset($exportFormOptions)) {
            foreach ($exportFormOptions as $formFieldName => $value) {
                // Klucze bez prefixu => nazwy pól formularza
                $keyWithoutPrefix = str_replace(MKDInstallOptionCMSPageExportProduct::getConfig('prefix'), '', $formFieldName);
                $formSettings[$form][$keyWithoutPrefix] = $value;
            }
        }

        return $formSettings;
    }


}

// Intaluj podstawowe dane do BD
class MKDInstallerDefaultData
{

    public function installDefaultData($dataList, $table, $file = 'SampleDataDefault')
    {
        // Pobierz podstawowe dane z JSON
        $jsonPath = _PS_MODULE_DIR_ . _MKD_NAME_ . '/' . $file . '.json';
        $jsonContent = file_get_contents($jsonPath);
        $jsonData = json_decode($jsonContent, true);

        if (!isset($jsonData[$dataList])) {
            return false; // Nieznana lista danych
        }
        
        $idShop = (int) Context::getContext()->shop->id;
        $defaultLangIso = Context::getContext()->language->iso_code;
        
        foreach ($jsonData[$dataList] as $row) {
            $value = pSQL($row['value']);
            $name = pSQL(isset($row['name'][$defaultLangIso]) ? $row['name'][$defaultLangIso] : $row['name']['en']);
            
            $data = array(
                'shop_id' => $idShop,
                'lang_iso' => $defaultLangIso,
                'value' => $value,
                'name' => $name,
            );
            
            Db::getInstance()->insert($table, $data);
            
        }
        
        return true;
    }
}




