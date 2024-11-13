<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_MKD_UPLOAD_DIR_')) {
    define('_MKD_UPLOAD_DIR_', _PS_IMG_DIR_ . 'attachments/product_');
}

class HelperModule
{
    
    // Pobierz ostatnią aktualizację z Tabel
    public function getLastTimeUpdateTable($table_NAME, $column_DATE, $product_ID, $shop_ID = null, $lang_ID = null)
    {
        $db = Db::getInstance();

        // Ustawienia domyślne
        if ($shop_ID === null) {
            $shop_ID = (int)Context::getContext()->shop->id;
        }

        if ($lang_ID === null) {
            $lang_ID = (int)Context::getContext()->language->id;
        }

        $sql = 'SELECT MAX(' . pSQL($column_DATE) . ')
                FROM ' . _DB_PREFIX_ . $table_NAME .'
                WHERE id_shop = '. (int) $shop_ID .' AND id_lang = '. (int) $lang_ID;

        $sql .= ' AND product_id = '. (int) $product_ID;

        return $db->getValue($sql);
    }



    // Sprawdź lub Utwórz folder (i nadrzędny) dla plików eksportu danego Produktu
    public function exportFilesProductDirectory($product_ID, $folder, $action = 'create', $path = 'attachments/product_', $dir = '_PS_IMG_DIR_')
    {
        $exportPath = constant($dir) . $path . $product_ID . '/' . $folder;

        if ($action == 'create' && !is_dir($exportPath)) {
            // Sprawdź, czy katalog nadrzędny istnieje
            $parentPath = constant($dir) . $path . '/' . $product_ID;

            if (!is_dir($parentPath)) {
                // Jeśli katalog nadrzędny nie istnieje, utwórz go
                mkdir($parentPath, 0755, true);
            }

            // Teraz utwórz katalog eksportu
            mkdir($exportPath, 0755, true);
        }

        // Zwróć pełną ścieżkę do katalogu eksportu
        return $exportPath;
    }

  


    // Przeszukaj folder w poszukiwaniu export pliku i jego danych
    public function scanFolderForFile($product_ID, $folder, $file_Format, $file_ISOCode, $file_Prefix)
    {

        // $downloadPath = _PS_BASE_URL_ . __PS_BASE_URI__ . _MKD_UPLOAD_DIR_ . $product_ID . '/' . $folder;
        $downloadPath = _MKD_UPLOAD_DIR_ . $product_ID . '/' . $folder;

        // $dirPath = _PS_ROOT_DIR_ .'/'. _MKD_UPLOAD_DIR_ . $product_ID . '/' . $folder;
        $dirPath = _MKD_UPLOAD_DIR_ . $product_ID . '/' . $folder;
        
        // Wyzerować, gdy nie ma pliku
        $filePath = '';
        $fileName = '';
        $fileSize = '';
        $fileTime = '';

        if (is_dir($dirPath)) {
            $files = scandir($dirPath);
            foreach ($files as $file) {
                if (is_file($dirPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) ===  $file_Format && strpos($file, $file_ISOCode . '_' . $file_Prefix . '_' ) === 0) {
                    // Pobierz nazwę pliku
                    $fileName = pathinfo($file, PATHINFO_BASENAME);
                    
                    // Pobierz czas pliku
                    $fileTime = filemtime($dirPath . '/' . $file);  // czas modyfikacji lub 'filectime()' = czas utworzenia pliku

                    // Pobierz rozmiar pliku w KB
                    $filePath = $dirPath . '/' . $file;
                    $fileSize = number_format(filesize($filePath) / 1024, 2);
                }
            }
        }

        return [
            'downloadPath' => $downloadPath,
            'filePath' => $filePath,
            'fileName' => $fileName,
            'fileSize' => $fileSize,
            'fileTime' => $fileTime,
        ];
    }
    
}