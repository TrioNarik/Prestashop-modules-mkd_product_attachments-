<?php
/*
* ModuleName_ControlerName[fileName]_ModuleFrontController extends ModuleFrontController
*
* /index.php?fc=module&module=mkd_product_attachments&controller=Downloader?get=ID
*
* Pobierz ustawienia Modułu
*/


if (!defined('_PS_VERSION_')) {
    exit;
}

class MKD_product_attachmentsDownloaderModuleFrontController extends ModuleFrontController
{
    /** @var Config module **/
    private $prefix;
    private $counter;


    public function __construct()
    {
        parent::__construct();

        $this->prefix = 'CONF_' . strtoupper(_MKD_NAME_) . '_';
        $this->counter = 'ATTACHMENT_DOWNLOAD_COUNTER';
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
        

        // Pobierz ID załącznika
        $attachment_ID = (int) Tools::getValue('get');

        // Załaduj Model
        require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDProductAttachmentsModel.php');

        // Sprawdź, czy istnieje załącznik o podanym ID
        $attachment = new MKDProductAttachmentsModel($attachment_ID);


        if (Validate::isLoadedObject($attachment)) {

            $counterSwith = Configuration::get(
                $this->prefix . $this->counter,
                false,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            );

            // Licznik kliknięć dla download_counter = 1
            if ($counterSwith) {
                $attachment->download_count += 1;
                $attachment->update();
            }

            // Pobierz 'type_id' załącznika
            $type_ID = $attachment->type_id;

            // Pobierz format załączników w Grupie
            $sql = 'SELECT f.value
                    FROM ' . _DB_PREFIX_ . _MKD_NAME_ . '_types_lang l
                    JOIN ' . _DB_PREFIX_ . _MKD_NAME_ . '_formats f ON l.format = f.id
                    WHERE l.type_id = ' . (int)$type_ID;


            $format = Db::getInstance()->getValue($sql);


            if (filter_var($attachment->file_url, FILTER_VALIDATE_URL)) {

                // Jeśli `file_url` to adres URL, przekieruj przeglądarkę do tego linku
                header("Location: " . $attachment->file_url);
                exit;

            } else {

                // Jeśli to plik do pobrania => Wyślij plik do przeglądarki
                $file_path = _PS_IMG_DIR_ . 'attachments/product_' . $attachment->product_id . '/' . $format . '/' . $attachment->file_url;

                if (file_exists($file_path)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file_path));
                    readfile($file_path);
                    exit;

                } else {
                    // Jeśli plik nie istnieje
                    Tools::redirect('index.php?controller=404');
                }
            }
        } else {
            // Jeśli załącznik nie istnieje
            Tools::redirect('index.php?controller=404');
        }
    }

}