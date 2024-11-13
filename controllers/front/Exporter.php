<?php
/*
* ModuleName_ControlerName[fileName]_ModuleFrontController extends ModuleFrontController
*
* /index.php?fc=module&module=mkd_product_attachments&controller=Exporter?get=ID
*
* Pobierz ustawienia Modułu
*/


if (!defined('_PS_VERSION_')) {
    exit;
}


class MKD_product_attachmentsExporterModuleFrontController extends ModuleFrontController
{
    /** @var Config module **/
    private $prefix;
    private $exportFolder;
    private $fileFormat;
    private $filePrefix;
    private $fileCounter;


    public function __construct()
    {
        parent::__construct();

        $this->prefix       = 'CONF_' . strtoupper(_MKD_NAME_) . '_';
        $this->exportFolder = 'EXPORT_FOLDER';
        $this->fileFormat   = 'EXPORT_FORMAT';
        $this->filePrefix   = 'EXPORT_FILE_PREFIX';
        $this->fileCounter  = 'EXPORT_DOWNLOAD_COUNTER';
    }

    public function init()
    {
        parent::init();

    }

    public function initContent()
    {
        parent::initContent();

        // Pobierz ID produktu
        $product_ID = (int) Tools::getValue('get');

        // Pobierz lang i sklep
        $id_lang       = Context::getContext()->language->id;
        $lang_iso_code = Language::getIsoById($id_lang);
        $id_shop       = Context::getContext()->shop->id;

        $exportFolder = Configuration::get(
            $this->prefix . $this->exportFolder,
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
        $exportFileFormat = Configuration::get(
            $this->prefix . $this->fileFormat,
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );
        $exportFilePrefix = Configuration::get(
            $this->prefix . $this->filePrefix,
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        $exportFileCounterSwitch = Configuration::get(
            $this->prefix . $this->fileCounter,
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        // Załaduj Model Liczników
        require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExportFilesModel.php');
        $fileCounterModel = new MKDExportFilesModel();

        // Sprawdź, czy istnieje counter ID dla produktu (sklep, lang i format)
        $counter_ID = $fileCounterModel->getExportFileCounterForProductByFormat($id_shop, $id_lang, $product_ID, $exportFileFormat);

        // Licznik kliknięć dla download_counter = 1
        if ($exportFileCounterSwitch && $counter_ID) {

            $fileCounterModel->updateExportFileCounterForProductByFormat($counter_ID);

        }
        
        // Załaduj pomocnicą klasę HelperModule Functionality (BO/FO)
        require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Helper/HelperModule.php';

        $helperModule = new HelperModule();

        // Przeszukaj export folder i pobierz dane o pliku
        $exportFileInfo = $helperModule->scanFolderForFile($product_ID, $exportFolder, $exportFileFormat, $lang_iso_code, $exportFilePrefix);

        if (file_exists($exportFileInfo['filePath'])) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($exportFileInfo['filePath']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($exportFileInfo['filePath']));
            readfile($exportFileInfo['filePath']);
            exit;

        } else {
            // Jeśli plik nie istnieje
            Tools::redirect('index.php?controller=404');
        }
       

    }

}

