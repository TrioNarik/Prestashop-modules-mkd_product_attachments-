<?php

// Zapisz XML format => zastąpić '&' '&amp;' w linkach (!!!)

if (!defined('_PS_VERSION_')) {
    exit;
}

class exporterProductXML
{
    /**
     * Usuwa niedozwolone znaki, zamienia na małe litery i kodująca do HTML
     *
     * @param string $text
     * 
     * @return sting
     */
    private function cleanAndEncode($text) {

        $cleanedText = mb_strtolower($text, 'UTF-8');
        $cleanedText = preg_replace('/[^a-z0-9_\-ęąłóśźć]/u', '_', $cleanedText);
        return htmlspecialchars($cleanedText);

    }


    public function export($exportData, $language, $settings, $exportPath, $exportFileName)
    {

        $languageObject = new Language($language);
        if (Validate::isLoadedObject($languageObject)) {
            // Pobierz iso_code dla danego języka
            $isoCode = $languageObject->iso_code;
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><product></product>');

        if (isset($exportData['id_product'][$language])) {
            $xml->addChild('id', $exportData['id_product'][$language]);
        }
        if (isset($isoCode)) {
            $xml->addChild('lang', $isoCode);
        }
        if (isset($exportData['category'][$language])) {
            $xml->addChild('category', $exportData['category'][$language]);
        }
        if (isset($exportData['name'][$language])) {
            $xml->addChild('name', $exportData['name'][$language]);
        }
        if (isset($exportData['index'][$language])) {
            $xml->addChild('index', $exportData['index'][$language]);
        }
        if (isset($exportData['manufacturer'][$language])) {
            $xml->addChild('manufacturer', $exportData['manufacturer'][$language]);
        }
        if (isset($exportData['ean13'][$language]) && !empty($exportData['ean13'][$language])) {
            $xml->addChild('ean13', $exportData['ean13'][$language]);
        }
        if (isset($exportData['description_short'][$language])) {
            $xml->addChild('short', $exportData['description_short'][$language]);
        }
        if (isset($exportData['description'][$language])) {
            $xml->addChild('description', $exportData['description'][$language]);
        }
        if (isset($exportData['price'][$language]) && $exportData['price'][$language] > 0) {
            $xml->addChild('price', $exportData['price'][$language]);
        }
        if (isset($exportData['currency'][$language]) && $exportData['price'][$language] > 0) {
            $xml->addChild('currency', $exportData['currency'][$language]);
        }
        if (isset($exportData['isbn'][$language]) && !empty($exportData['isbn'][$language])) {
            $xml->addChild('isbn', $exportData['isbn'][$language]);
        }
        if (isset($exportData['upc'][$language]) && !empty($exportData['upc'][$language])) {
            $xml->addChild('upc', $exportData['upc'][$language]);
        }
        if (isset($exportData['mpn'][$language]) && !empty($exportData['mpn'][$language])) {
            $xml->addChild('mpn', $exportData['mpn'][$language]);
        }
        if (isset($exportData['width'][$language]) && $exportData['width'][$language] > 0) {
            $xml->addChild('width', $exportData['width'][$language]);
        }
        if (isset($exportData['height'][$language]) && $exportData['height'][$language] > 0) {
            $xml->addChild('height', $exportData['height'][$language]);
        }
        if (isset($exportData['weight'][$language]) && $exportData['weight'][$language] > 0) {
            $xml->addChild('weight', $exportData['weight'][$language]);
        }

        // ==============================================
        // Dodaj Extra Fields produktu
        if (isset($exportData['extra_fields'][$language])) {

            // Sprawdź, czy pola dodatkowe są dostępne
            if (isset($exportData['extra_fields'][$language]['field_title']) && isset($exportData['extra_fields'][$language]['field_values'])) {

                $fieldTitles = $exportData['extra_fields'][$language]['field_title'];
                $fieldValues = $exportData['extra_fields'][$language]['field_values'];
        
                // Iteruj po tytułach i wartościach dodatkowych pól
                foreach ($fieldTitles as $index => $fieldTitle) {

                    // Sprawdź, czy 'field_values' jest dostępne i nie jest puste
                    if (isset($fieldValues[$index]) && !empty(trim($fieldValues[$index]))) {

                        // Dodaj dane pola dodatkowego => usunąć znaki specjalne i spacje w nazwie (!!!)
                        $xml->addChild($this->cleanAndEncode($fieldTitle), htmlspecialchars($fieldValues[$index]));
                    }
                }
            }
        }
        // ================================================


        // Dodaj zdjęcia
        if (isset($exportData['image'])) {
            $imagesXml = $xml->addChild('images');
            $images = explode(', ', $exportData['image']);
            foreach ($images as $image) {
                $imagesXml->addChild('image', $image);
            }
        }

        // Dodaj MKD załączniki
        if (isset($exportData['attachments'][$language])) {
            $attachmentsXml = $xml->addChild('attachments');

            foreach ($exportData['attachments'][$language] as $fileData) {
                $attachmentXml = $attachmentsXml->addChild($fileData['attachments_format']);
                $attachmentXml->addChild('name', $fileData['attachments_title']);
                $attachmentXml->addChild('file', $fileData['attachments_file']);
                $attachmentXml->addChild('date', $fileData['attachments_date']);
                $attachmentXml->addChild('link', str_replace('&', '&amp;', $fileData['attachment_path']));
            }
        }

        // Dodaj załączniki produktu
        if (isset($exportData['files'][$language])) {
            $filesXml = $xml->addChild('files');

            foreach ($exportData['files'][$language] as $fileData) {
                $fileXml = $filesXml->addChild('file');
                $fileXml->addChild('name', $fileData['file_name']);
                $fileXml->addChild('size', $fileData['file_size']);
                $fileXml->addChild('link', $fileData['file_link']);
            }
        }
        

        // Zapisz dane do pliku XML
        $xmlString = $xml->asXML();

        $exportFilePath = $exportPath . '/' . $exportFileName . '.' . $settings['product']['EXPORT_FORMAT'];

        if (file_put_contents($exportFilePath, $xmlString) === false) {
            // Zwróć błąd, jeśli zapis do pliku XML nie powiódł się
            $response = [
                'success' => false,
                'message' => Context::getContext()->getTranslator()->trans('Error saving data to the export XML file.')
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }
    }

}