<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class exporterProductCSV
{
    public function export($exportData, $language, $settings, $exportPath, $exportFileName)
    {
        // Definiuj separator w pliku CSV, np. średnik
        $separator = ';';

        if (isset($exportData['id_product'][$language])) {
            $headers['id'] = Context::getContext()->getTranslator()->trans('ID');
        }
        if (isset($exportData['category'][$language])) {
            $headers['category'] = Context::getContext()->getTranslator()->trans('Kategoria');
        }
        if (isset($exportData['name'][$language])) {
            $headers['name'] = Context::getContext()->getTranslator()->trans('Nazwa produktu');
        }
        if (isset($exportData['index'][$language])) {
            $headers['index'] = Context::getContext()->getTranslator()->trans('Index');
        }
        if (isset($exportData['manufacturer'][$language])) {
            $headers['manufacturer'] = Context::getContext()->getTranslator()->trans('Marka');
        }
        if (isset($exportData['ean13'][$language]) && !empty($exportData['ean13'][$language])) {
            $headers['ean13'] = Context::getContext()->getTranslator()->trans('EAN');
        }
        if (isset($exportData['description_short'][$language])) {
            $headers['description_short'] = Context::getContext()->getTranslator()->trans('Krótki opis');
        }
        if (isset($exportData['description'][$language])) {
            $headers['description'] = Context::getContext()->getTranslator()->trans('Opis');
        }
        if (isset($exportData['price'][$language]) && $exportData['price'][$language] > 0) {
            $headers['price'] = Context::getContext()->getTranslator()->trans('Cena');
        }
        if (isset($exportData['currency'][$language]) && $exportData['price'][$language] > 0) {
            $headers['currency'] = Context::getContext()->getTranslator()->trans('Waluta');
        }
        if (isset($exportData['isbn'][$language]) && !empty($exportData['isbn'][$language])) {
            $headers['isbn'] = Context::getContext()->getTranslator()->trans('ISBN');
        }
        if (isset($exportData['upc'][$language]) && !empty($exportData['upc'][$language])) {
            $headers['upc'] = Context::getContext()->getTranslator()->trans('UPC');
        }
        if (isset($exportData['mpn'][$language]) && !empty($exportData['mpn'][$language])) {
            $headers['mpn'] = Context::getContext()->getTranslator()->trans('MPN');
        }
        if (isset($exportData['width'][$language]) && $exportData['width'][$language] > 0) {
            $headers['width'] = Context::getContext()->getTranslator()->trans('Szerokość');
        }
        if (isset($exportData['height'][$language]) && $exportData['height'][$language] > 0) {
            $headers['height'] = Context::getContext()->getTranslator()->trans('Wysokość');
        }
        if (isset($exportData['weight'][$language]) && $exportData['weight'][$language] > 0) {
            $headers['weight'] = Context::getContext()->getTranslator()->trans('Waga');
        }

        // ==============================================
        // Dodaj Nagłówki Extra Fields produktu
        if (isset($exportData['extra_fields'][$language])) {

            if (isset($exportData['extra_fields'][$language]['field_title']) && isset($exportData['extra_fields'][$language]['field_values'])) {

                $fieldTitles = $exportData['extra_fields'][$language]['field_title'];
                $fieldValues = $exportData['extra_fields'][$language]['field_values'];

                 // Iteruj po tytułach i wartościach dodatkowych pól
                 foreach ($fieldTitles as $index => $fieldTitle) {

                    // Sprawdź, czy 'field_values' jest dostępne i nie jest puste
                    if (isset($fieldValues[$index]) && !empty(trim($fieldValues[$index]))) {
                    
                        $headers[$fieldTitle] = $fieldTitle;
                    }
                }
            }
        }
        // ================================================

        if (isset($exportData['image'])) {
            $headers['images'] = Context::getContext()->getTranslator()->trans('Zdjęcia');
        }
        if (isset($exportData['attachments'][$language])) {
            $headers['attachments'] = Context::getContext()->getTranslator()->trans('Załączniki: format, nazwa, nazwa pliku, data, link');
        }
        if (isset($exportData['files'][$language])) {
            $headers['files'] = Context::getContext()->getTranslator()->trans('Pliki: nazwa, rozmiar, link');
        }

        
        // Otwórz plik CSV do zapisu
        $csvFilePath = $exportPath . '/' . $exportFileName . '.' . $settings['product']['EXPORT_FORMAT'];
        $csvFile = fopen($csvFilePath, 'w');

        // Zapisz nagłówki do pliku CSV
        fputcsv($csvFile, $headers, $separator);

        // Pobierz dane z $exportData i zapisz każdy rekord do pliku CSV
        
        $record = [];
    
        if (isset($exportData['id_product'][$language])) {
            $record['id'] = $exportData['id_product'][$language];
        }
        if (isset($exportData['category'][$language])) {
            $record['category'] = $exportData['category'][$language];
        }
        if (isset($exportData['name'][$language])) {
            $record['name'] = $exportData['name'][$language];
        }
        if (isset($exportData['index'][$language])) {
            $record['index'] = $exportData['index'][$language];
        }
        if (isset($exportData['manufacturer'][$language])) {
            $record['manufacturer'] = $exportData['manufacturer'][$language];
        }
        if (isset($exportData['ean13'][$language]) && !empty($exportData['ean13'][$language])) {
            $record['ean13'] = $exportData['ean13'][$language];
        }
        if (isset($exportData['description_short'][$language])) {
            $record['description_short'] = strip_tags($exportData['description_short'][$language]);
        }
        if (isset($exportData['description'][$language])) {
            $record['description'] = strip_tags($exportData['description'][$language]);
        }
        if (isset($exportData['price'][$language]) && $exportData['price'][$language] > 0) {
            $record['price'] = $exportData['price'][$language];
        }
        if (isset($exportData['currency'][$language]) && $exportData['price'][$language] > 0) {
            $record['currency'] = $exportData['currency'][$language];
        }
        if (isset($exportData['isbn'][$language]) && $exportData['isbn'][$language] > 0) {
            $record['isbn'] = $exportData['isbn'][$language];
        }
        if (isset($exportData['upc'][$language]) && $exportData['upc'][$language] > 0) {
            $record['upc'] = $exportData['upc'][$language];
        }
        if (isset($exportData['mpn'][$language]) && $exportData['mpn'][$language] > 0) {
            $record['mpn'] = $exportData['mpn'][$language];
        }
        if (isset($exportData['width'][$language]) && $exportData['width'][$language] > 0) {
            $record['width'] = $exportData['width'][$language];
        }
        if (isset($exportData['height'][$language]) && $exportData['height'][$language] > 0) {
            $record['height'] = $exportData['height'][$language];
        }
        if (isset($exportData['weight'][$language]) && $exportData['weight'][$language] > 0) {
            $record['weight'] = $exportData['weight'][$language];
        }

        // ==============================================
        // Dodaj wartości Extra Fields produktu
        if (isset($exportData['extra_fields'][$language])) {

            if (isset($exportData['extra_fields'][$language]['field_title']) && isset($exportData['extra_fields'][$language]['field_values'])) {

                $fieldTitles = $exportData['extra_fields'][$language]['field_title'];
                $fieldValues = $exportData['extra_fields'][$language]['field_values'];

                 // Iteruj po tytułach i wartościach dodatkowych pól
                 foreach ($fieldTitles as $index => $fieldTitle) {

                    // Sprawdź, czy 'field_values' jest dostępne i nie jest puste
                    if (isset($fieldValues[$index]) && !empty(trim($fieldValues[$index]))) {
                    
                        $record[$fieldTitle] = strip_tags($fieldValues[$index]);
                    }
                }
            }
        }
        // ================================================

        // Dodaj zdjęcia
        if (isset($exportData['image'])) {
            $images = explode(', ', $exportData['image']);
            $images = implode("\n", $images);
            $record['image'] = $images;
        }
        // Dodaj MKD załączniki
        $attachmentsText = ''; // Przechowywania ciągu załączników
        if (isset($exportData['attachments'][$language])) {
            foreach ($exportData['attachments'][$language] as $fileData) {
                if (!empty($fileData['attachments_title'])) {
                    $fileData['attachments_title'] = '[' . $fileData['attachments_title'] . ']';
                }
                $attachmentsText .= '['. strtoupper($fileData['attachments_format']) .'] '. $fileData['attachments_title'] .', '.  $fileData['attachments_file'] . ', (' . $fileData['attachments_date'] . ')';
                if (!empty($fileData['attachment_path'])) {
                    $attachmentsText .= ', ' . $fileData['attachment_path'];
                }
                $attachmentsText .= "\n";
            }
            $record['attachments'] = $attachmentsText;
        }
        // Dodaj załączniki produktu
        $filesText = ''; // Przechowywanie ciągu plików
        if (isset($exportData['files'][$language])) {                        
            foreach ($exportData['files'][$language] as $fileData) {
                if (!empty($fileData['file_name'])) {
                    $fileData['file_name'] = '"' . $fileData['file_name'] . '"';
                }
                $filesText .= $fileData['file_name'] . ' [' . number_format($fileData['file_size'] / 1024, 2) . ' KB], ' . $fileData['file_link'];
                $filesText .= "\n";
            }
            $record['files'] = $filesText;
        }

        fputcsv($csvFile, $record, $separator);
        
        // Zamknij plik CSV
        fclose($csvFile);
    }
}