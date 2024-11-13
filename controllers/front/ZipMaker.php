<?php
/*
* ModuleName_ControlerName[fileName]_ModuleFrontController extends ModuleFrontController
*
* /index.php??fc=module&module=mkd_product_attachments&controller=ZipMaker&product=productID&get=all
*
* Pobierz ustawienia Modułu
*/


if (!defined('_PS_VERSION_')) {
    exit;
}

class MKD_product_attachmentsZipMakerModuleFrontController extends ModuleFrontController
{
    /** @var Config module **/
    private $prefix;
    private $counter;


    public function __construct()
    {
        parent::__construct();

        $this->prefix = 'CONF_' . strtoupper(_MKD_NAME_) . '_';
        // $this->counter = 'ATTACHMENT_DOWNLOAD_COUNTER';
    }

    public function init()
    {
        parent::init();

        $module_active = Module::isEnabled(_MKD_NAME_);
        if (!$module_active) {
            // Moduł jest wyłączony
            exit;
        }

    }

    public function initContent()
    {
        parent::initContent();
        
        $shop_ID = Context::getContext()->shop->id;
        $lang_ID = Context::getContext()->language->id;


        // Pobierz ID produktu
        $product_ID = (int)Tools::getValue('product');

        // Pobierz Limit => 0 = bez limitu
        $limit = (int)Tools::getValue('limit');

        // Pobierz Grupę/Kategorie załączników => 0 = wszystkie grupy
        $group_ID = (int)Tools::getValue('group');

        // ==================================================
        // W przypadku WSZYSTKICH załączników => (limit = 0 && group = 0)
        if ($product_ID) {
            $this->getLimitAttachmentsByGroup($shop_ID, $lang_ID, $product_ID, $group_ID, $limit);
        } else {
            // Obsługa błędu
            Tools::redirect('index.php?controller=404');
        }
    }


    private function getLimitAttachmentsByGroup($shop_ID, $lang_ID, $product_ID, $group_ID = false, $limit = false)
    {
        // Załaduj Model
        require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDProductAttachmentsModel.php');
        
        // Pobranie nazwy produktu
        $product = new Product($product_ID, false, $lang_ID);
        $productName = $product->name;

        // Pobranie dostępnych grup załączników
        $avaibleAttachmentGroup = MKDProductAttachmentsModel::getAvailableAttachmentGroups($shop_ID, $lang_ID);
        
        // Pobranie załączników dla danego produktu
        $attamentsByProducts = MKDProductAttachmentsModel::getProductAttachmentsByProductId($product_ID, $lang_ID);
        
        // Tablica do przechowywania załączników do pobrania
        $attachmentsToDownload = [];
        
        // Przefiltrowanie załączników według grupy
        foreach ($attamentsByProducts as $attachment) {
            // Sprawdzenie, czy załącznik należy do wskazanej grupy (jeśli grupa została podana)
            if ($group_ID && isset($avaibleAttachmentGroup[$group_ID])) {
                if ($attachment['group_name'] === $avaibleAttachmentGroup[$group_ID]['title']) {
                    $attachmentsToDownload[] = $attachment;
                }
            } else {
                $attachmentsToDownload[] = $attachment;
            }
            
            // Limit plików
            if ($limit && count($attachmentsToDownload) >= $limit) {
                break;
            }
        }
        
        // Jeśli są załączniki, tworzymy ZIP
        if (!empty($attachmentsToDownload)) {
            $this->createZIPFromAttachments($shop_ID, $lang_ID, $attachmentsToDownload, $productName);
        } else {
            Tools::redirect('index.php?controller=404');
        }
    }

    private function createZIPFromAttachments($shop_ID, $lang_ID, $attachments, $productName)
    {
        $zip = new ZipArchive();
        // Usuń niebezpieczne znaki z nazwy produktu (np. spacje, znaki specjalne)
        $safeProductName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $productName);
        $safeProductName = strtolower($safeProductName);
        
        // Ustawienie nazwy ZIP na nazwę produktu
        $zipFileName = $safeProductName . '.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            exit("Nie można otworzyć pliku ZIP");
        }

        foreach ($attachments as $attachment) {
            $format = MKDProductAttachmentsModel::getAttachmentFormatByTypeId($attachment['type_id'], $lang_ID);
            $filePath = _PS_IMG_DIR_ . 'attachments/product_' . $attachment['product_id'] . '/' . $format . '/' . $attachment['file_url'];

            if (file_exists($filePath)) {
                $zip->addFile($filePath, basename($filePath));
            } else {
                if ($this->isExternal($attachment['file_url'])) {
                    $fileContent = file_get_contents($attachment['file_url']);
                    $zip->addFromString($attachment['file_name'], $fileContent);
                }
            }
        }

        $zip->close();

        // Pobranie pliku ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);

        // Usuń tymczasowy plik ZIP
        unlink($zipFilePath);
        exit;
    }

    
    private function isExternal($url)
    {
        // Sprawdzenie, czy URL jest zewnętrzny
        return (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0);
    }
    

}