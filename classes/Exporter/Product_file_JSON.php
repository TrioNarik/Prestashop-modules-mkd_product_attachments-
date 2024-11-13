<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class exporterProductJSON
{
    public function export($exportData, $language, $settings, $exportPath, $exportFileName)
    {
        $languageObject = new Language($language);
        if (Validate::isLoadedObject($languageObject)) {
            // Pobierz iso_code dla danego języka
            $isoCode = $languageObject->iso_code;
        }

        $dataToExport = array(); // Przygotuj tablicę danych do eksportu

        if (isset($exportData['id_product'][$language])) {
            $dataToExport['id'] = $exportData['id_product'][$language];
        }
        if (isset($isoCode)) {
            $dataToExport['lang'] = $isoCode;
        }
        if (isset($exportData['category'][$language])) {
            $dataToExport['category'] = $exportData['category'][$language];
        }
        if (isset($exportData['name'][$language])) {
            $dataToExport['name'] = $exportData['name'][$language];
        }
        if (isset($exportData['manufacturer'][$language])) {
            $dataToExport['manufacturer'] = $exportData['manufacturer'][$language];
        }
        if (isset($exportData['ean13'][$language]) && !empty($exportData['ean13'][$language])) {
            $dataToExport['ean13'] = $exportData['ean13'][$language];
        }
        if (isset($exportData['description_short'][$language])) {
            $dataToExport['description_short'] = $exportData['description_short'][$language];
        }
        if (isset($exportData['description'][$language])) {
            $dataToExport['description'] = $exportData['description'][$language];
        }
        if (isset($exportData['price'][$language]) && $exportData['price'][$language] > 0) {
            $dataToExport['price'] = $exportData['price'][$language];
        }
        if (isset($exportData['currency'][$language]) && $exportData['price'][$language] > 0) {
            $dataToExport['currency'] = $exportData['currency'][$language];
        }
        if (isset($exportData['isbn'][$language]) && !empty($exportData['isbn'][$language])) {
            $dataToExport['isbn'] = $exportData['isbn'][$language];
        }
        if (isset($exportData['upc'][$language]) && !empty($exportData['upc'][$language])) {
            $dataToExport['upc'] = $exportData['upc'][$language];
        }
        if (isset($exportData['mpn'][$language]) && !empty($exportData['mpn'][$language])) {
            $dataToExport['mpn'] = $exportData['mpn'][$language];
        }
        if (isset($exportData['width'][$language]) && $exportData['width'][$language] > 0) {
            $dataToExport['width'] = $exportData['width'][$language];
        }
        if (isset($exportData['height'][$language]) && $exportData['height'][$language] > 0) {
            $dataToExport['height'] = $exportData['height'][$language];
        }
        if (isset($exportData['weight'][$language]) && $exportData['weight'][$language] > 0) {
            $dataToExport['weight'] = $exportData['weight'][$language];
        }

        // Dodaj Extra Fields produktu ===================================================
        if (isset($exportData['extra_fields'][$language])) {
            // Wyzeruj tablicę 
            $extraFields = [];

            // Iteruj po polach dodatkowych
            foreach ($exportData['extra_fields'] as $extraFieldData) {
                // Sprawdź, czy istnieje tytuł i wartości dodatkowych pól
                if (isset($extraFieldData['field_title']) && isset($extraFieldData['field_values'])) {
                    $fieldTitles = $extraFieldData['field_title'];
                    $fieldValues = $extraFieldData['field_values'];
                
                    // Iteruj po tytułach i wartościach dodatkowych pól
                    foreach ($fieldTitles as $index => $fieldTitle) {
                        // Sprawdź, czy 'field_values' jest dostępne i nie jest puste
                        if (isset($fieldValues[$index]) && !empty(trim($fieldValues[$index]))) {
                            // Dodaj dane pola dodatkowego do tablicy $extraFields
                            $extraFields[$fieldTitle] = $fieldValues[$index];
                        }
                    }
                }
            }

            // Do tablicy $dataToExport
            $dataToExport = array_merge($dataToExport, $extraFields);
        }
        // =================================================================================
        

        // Dodaj zdjęcia
        if (isset($exportData['image'])) {
            $images = explode(', ', $exportData['image']);
            $dataToExport['images'] = $images;
        }

        // Dodaj MKD załączniki
        if (isset($exportData['attachments'][$language])) {
            $attachments = array();
            foreach ($exportData['attachments'][$language] as $fileData) {
                $attachment = array(
                    'format' => strtoupper($fileData['attachments_format']),
                    'name' => $fileData['attachments_title'],
                    'file' => $fileData['attachments_file'],
                    'date' => $fileData['attachments_date'],
                    'link' => $fileData['attachment_path']
                );
                $attachments[] = $attachment;
            }
            $dataToExport['attachments'] = $attachments;
        }

        // Dodaj załączniki produktu
        if (isset($exportData['files'][$language])) {
            $files = array();
            foreach ($exportData['files'][$language] as $fileData) {
                $file = array(
                    'name' => $fileData['file_name'],
                    'size' => number_format($fileData['file_size'] / 1024, 2) . ' KB',
                    'link' => $fileData['file_link']
                );
                $files[] = $file;
            }
            $dataToExport['files'] = $files;
        }


        

        // Skonwertuj tablicę do formatu JSON
        $jsonContent = json_encode($dataToExport, JSON_PRETTY_PRINT);

        // Utwórz pełną ścieżkę do pliku
        $jsonFilePath = $exportPath . '/' . $exportFileName . '.json';

        // Zapisz dane do pliku
        file_put_contents($jsonFilePath, $jsonContent);

        // Zwróć pełną ścieżkę do zapisanego pliku JSON
        return $jsonFilePath;
    }
}
