<?php
// Images => w osobnym arkuszu => sprawdzić czy to url czy img z ustawień modułu (domyślnie: URL)
// IMG => Bezpośrednia ścieżkę do obrazka (!!!) => użyc w Kontrolerze => Image::getImgFolderStatic()

if (!defined('_PS_VERSION_')) {
    exit;
}

class exporterProductXLS
{

    private $xlsLogoWidth;
    private $xlsLogoCell;
        

    // Pobierz ustawienia logo
    public function __construct($config)
    {
        $this->xlsLogoWidth = $config['xlsLogoWidth']; 
        $this->xlsLogoCell  = $config['xlsLogoCell'];
        
    }


    // =================== XLS export ==============
    // Ustaw Kontrast kolorów BG <=> Font color 
    // $brightnessThreshold => próg jasności, poniżej którego kolor uznawany jest za ciemny
    // =============================================
    protected function isColorContrast($color, $brightnessThreshold = 128) {
        // Usuń ewentualny znak '#' z początku koloru
        $color = ltrim($color, '#');
    
        // Konwertuj kolor na składowe RGB
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
    
        // Oblicz jasność koloru
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
    
        // Zwróć true, jeśli kolor jest ciemny, false w przeciwnym razie
        return $brightness < $brightnessThreshold;
    }
    
    

    public function export($exportData, $language, $settings, $exportPath, $exportFileName)
    {

         // Sprawdź, czy klasa PhpOffice\PhpSpreadsheet\Spreadsheet istnieje
         if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            // Jeśli nie istnieje, zwróć błąd
            $response = [
                'success' => false,
                'message' => Context::getContext()->getTranslator()->trans('PhpSpreadsheet library is not installed.')
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }
        
        
        // Utwórz nowe Arkusze w pliku XLS za pomocą Biblioteki
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Podstawowy Aktywny arkusz
        $sheet = $spreadsheet->getActiveSheet();
        $sheet = $sheet->setTitle(Context::getContext()->getTranslator()->trans('Produkt'));
        
        

        // Przekształć kolor HTML na obiekt RGB
        if ($settings['product']['FILE_XLS_COLOR'] != false || !empty(trim($settings['product']['FILE_XLS_COLOR']))) {
            $headerBGColor = $settings['product']['FILE_XLS_COLOR'];
        } else {
            $headerBGColor = '#FFFFFF';
        }

        if ($this->isColorContrast($headerBGColor)) {
            // Kolor jest ciemny, więc możesz ustawić jasną czcionkę
            $fontColor = '#FFFFFF';
        } else {
            // Kolor jest jasny, więc możesz ustawić ciemną czcionkę
            $fontColor = '#000000';
        }

        // Ścieżka do logo
        $logoPath = _PS_IMG_DIR_ . Configuration::get('PS_LOGO');
        $alternative_logoPath = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL');

        // Kolorowanie nagłówków
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => ltrim($headerBGColor, '#')]
            ],
            'font'  => [
                // 'bold'  => true,
                'color' => ['rgb' => ltrim($fontColor, '#')],
                // 'size'  => 12,
                // 'name'  => 'Verdana',
            ],
            // wyrównanie w pionie
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

                           
        // Dodaj Logo do aktywnego Arkusza
        if ($settings['product']['FILE_XLS_LOGO']) {
            $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName(Configuration::get('PS_SHOP_NAME', $language, null, Context::getContext()->shop->id));
            $drawing->setDescription(Configuration::get('PS_SHOP_DESCRIPTION', $language, null, Context::getContext()->shop->id));
            $drawing->setPath(file_exists($logoPath) ? $logoPath : $alternative_logoPath); // Ścieżka do Logo/ zastępstwo gdy nie ma
            $drawing->setCoordinates($this->xlsLogoCell); // Komórka docelowa logo
            // $drawing->setWidthAndHeight(100, 100); // Rozmiar obrazka
            $drawing->setWidth($this->xlsLogoWidth); // Rozmiar logo
            $drawing->setWorksheet($sheet);
        }



        $headerIndex = 'A'; // Początkowa litera kolumny nagłówków
        $contentIndex = 'A'; // Początkowa litera kolumny treści

        $headers = [];
        $contents = [];
        
        
        // ID
        if (isset($exportData['id_product'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('ID'),
                'width' => 5
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['id_product'][$language]
            );
        }

        // Kategoria
        if (isset($exportData['category'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Kategoria'),
                'width' => 25
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['category'][$language]
            );
        }

        // Product Name
        if (isset($exportData['name'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Nazwa produktu'),
                'width' => 50
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['name'][$language]
            );
        }

        // Index
        if (isset($exportData['index'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Index'),
                'width' => 10
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['index'][$language]
            );
        }

        // Manufacturer
        if (isset($exportData['manufacturer'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Marka'),
                'width' => 20
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['manufacturer'][$language]
            );
        }

        // Kod EAN13
        if (isset($exportData['ean13'][$language]) && !empty($exportData['ean13'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('EAN13'),
                'width' => 13
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['ean13'][$language]
            );
        }

        // Short Description
        if (isset($exportData['description_short'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Krótki opis'),
                'width' => 50
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => strip_tags($exportData['description_short'][$language]) // Usuwa wszystkie znaczniki HTML
            );
        }

        // Description
        if (isset($exportData['description'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Opis'),
                'width' => 80
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => strip_tags($exportData['description'][$language]) // Usuwa wszystkie znaczniki HTML
            );
        }

        // Price
        if (isset($exportData['price'][$language]) && $exportData['price'][$language] > 0) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Cena'),
                'width' => 10
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['price'][$language]
            );
        }

        // Currency
        if (isset($exportData['currency'][$language]) && $exportData['price'][$language] > 0) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Waluta'),
                'width' => 10
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['currency'][$language]
            );
        }

        // ISBN
        if (isset($exportData['isbn'][$language]) && !empty($exportData['isbn'][$language])) {
            $headers[$headerIndex++] = array(
                'text' => Context::getContext()->getTranslator()->trans('ISBN'),
                'width' => 15
            );
        }

        // UPC
        if (isset($exportData['upc'][$language]) && !empty($exportData['upc'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('UPC'),
                'width' => 15
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['upc'][$language]
            );
        }

        // MPN
        if (isset($exportData['mpn'][$language]) && !empty($exportData['mpn'][$language])) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('MPN'),
                'width' => 15
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['mpn'][$language]
            );
        }

        // Width of product
        if (isset($exportData['width'][$language]) && $exportData['width'][$language] > 0) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Szerokość'),
                'width' => 15
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['width'][$language]
            );
        }

        // Height of product
        if (isset($exportData['height'][$language]) && $exportData['height'][$language] > 0) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Wysokość'),
                'width' => 15
            );
        }

        // Weight of product
        if (isset($exportData['weight'][$language]) && $exportData['weight'][$language] > 0) {
            $headers[$headerIndex++] = array(
                'row' => 1,
                'text' => Context::getContext()->getTranslator()->trans('Waga'),
                'width' => 15
            );
            $contents[$contentIndex++] =  array(
                'row' => 2,
                'value' => $exportData['weight'][$language]
            );
        }

        // ==============================================
        // Dodaj Extra Fields produktu
        if (isset($exportData['extra_fields'][$language])) {

            if (isset($exportData['extra_fields'][$language]['field_title']) && isset($exportData['extra_fields'][$language]['field_values'])) {

                $fieldTitles = $exportData['extra_fields'][$language]['field_title'];
                $fieldValues = $exportData['extra_fields'][$language]['field_values'];

                // Iteruj po tytułach i wartościach dodatkowych pól
                foreach ($fieldTitles as $index => $fieldTitle) {

                    // Sprawdź, czy 'field_values' jest dostępne i nie jest puste
                    if (isset($fieldValues[$index]) && !empty(trim($fieldValues[$index]))) {

                        $headers[$headerIndex++] = array(
                            'row' => 1,
                            'text' => $fieldTitle,
                            'width' => 40
                        );
                        $contents[$contentIndex++] =  array(
                            'row' => 2,
                            'value' => strip_tags($fieldValues[$index]) // Usuwa znaczniki HTML
                        );

                    }
                }

                
            }
        }
        // ==============================================


        // Images => w osobnym arkuszu => sprawdzić czy to url czy img z ustawień modułu (domyślnie: URL)
        if (isset($exportData['image'])) {

            $imageSheet = $spreadsheet->createSheet()->setTitle(Context::getContext()->getTranslator()->trans('Zdjęcia'));

            // Pobierz adresy URL obrazków jako tablicę
            $imageUrls = explode(', ', $exportData['image']);

            // Pętla po adresach URL obrazków
            $rowIndex = 1; // Zaczynamy od pierwszego wiersza
            $columnIndex = 'A'; // Początkowa litera kolumny

            foreach ($imageUrls as $imageUrl) {
                if ($settings['product']['FILE_XLS_IMAGES']) {
                    // Utwórz obiekt Drawing
                    $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setPath($imageUrl);
                    $drawing->setCoordinates($columnIndex . $rowIndex); // Komórka docelowa obrazka
                    $drawing->setWorksheet($imageSheet);

                    // Ustaw szerokość kolumny na szerokość obrazka
                    $imageSheet->getColumnDimension($columnIndex)->setWidth($drawing->getWidth() / 7); //Dostosować dzielnik
                    // Ustaw wysokość wiersza na wysokość obrazka
                    $imageSheet->getRowDimension($rowIndex)->setRowHeight($drawing->getHeight());

                } else {
                    // Ustaw link do obrazka w komórce
                    $imageSheet->setCellValue($columnIndex . $rowIndex, $imageUrl);

                    // Ustaw automatyczną szerokość kolumny i zawijanie tekstu
                    $imageSheet->getColumnDimension($columnIndex)->setAutoSize(true);
                    $imageSheet->getStyle($columnIndex . $rowIndex)->getAlignment()->setWrapText(true);
                }

                // Przesuń się do następnego wiersza
                $rowIndex++;
            }
        }


        // Attachments MKD => w osobnym arkuszu
        if (isset($exportData['attachments'][$language])) {
            $attachmentsSheet = $spreadsheet->createSheet()->setTitle(Context::getContext()->getTranslator()->trans('Załączniki'));
        
            // Nagłówki
            $attachmentsSheet->setCellValue('A1', Context::getContext()->getTranslator()->trans('Tytuł'));
            $attachmentsSheet->setCellValue('B1', Context::getContext()->getTranslator()->trans('Data'));
            $attachmentsSheet->setCellValue('C1', Context::getContext()->getTranslator()->trans('Format'));
            $attachmentsSheet->setCellValue('D1', Context::getContext()->getTranslator()->trans('Plik'));

            // Zastosuj style do nagłówków oraz wyrównanie
            $attachmentsSheet->getStyle('A1:D1')->applyFromArray($styleArray);
            $attachmentsSheet->getStyle('A1:D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            // Ustaw wysokość nagłówków
            $attachmentsSheet->getRowDimension(1)->setRowHeight(30);
            
        
            // Pętla po załącznikach
            $rowIndex = 2; // Zaczynamy od drugiego wiersza
            foreach ($exportData['attachments'][$language] as $attachment) {
                // Ustaw pozostałe kolumny (tytuł, plik, data, format)
                $attachmentsSheet->setCellValue('A' . $rowIndex, $attachment['attachments_file']);
                $attachmentsSheet->setCellValue('B' . $rowIndex, $attachment['attachments_date']);
                $attachmentsSheet->setCellValue('C' . $rowIndex, $attachment['attachments_format']);
                
                // Utwórz link do załącznika w komórce
                $attachmentsSheet->getCell('D' . $rowIndex)->getHyperlink()->setUrl($attachment['attachment_path']);
                $attachmentsSheet->setCellValue('D' . $rowIndex, Context::getContext()->getTranslator()->trans('Pobierz'));
                
                // Wyrównanie poziome od B do D
                $attachmentsSheet->getStyle('B:D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
                // Ustaw automatyczną szerokość kolumny i zawijanie tekstu dla wszystkich kolumn
                $attachmentsSheet->getColumnDimension('A')->setAutoSize(true);
                $attachmentsSheet->getColumnDimension('B')->setAutoSize(true);
                $attachmentsSheet->getColumnDimension('C')->setAutoSize(true);
                $attachmentsSheet->getColumnDimension('D')->setAutoSize(true);
        
                $attachmentsSheet->getStyle('A' . $rowIndex . ':D' . $rowIndex)->getAlignment()->setWrapText(true);
        
                // Przesuń się do następnego wiersza
                $rowIndex++;
            }
        }
        
        
        // Files => osobny arkusz
        if (isset($exportData['files'][$language])) {
            $filesSheet = $spreadsheet->createSheet()->setTitle(Context::getContext()->getTranslator()->trans('Pliki'));

            // Nagłówki
            $filesSheet->setCellValue('A1', Context::getContext()->getTranslator()->trans('Tytuł'));
            $filesSheet->setCellValue('B1', Context::getContext()->getTranslator()->trans('Rozmiar') . ' [KB]');
            $filesSheet->setCellValue('C1', Context::getContext()->getTranslator()->trans('Plik'));

            // Zastosuj style do nagłówków oraz wyrównanie
            $filesSheet->getStyle('A1:C1')->applyFromArray($styleArray);
            $filesSheet->getStyle('A1:C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $filesSheet->getRowDimension(1)->setRowHeight(30);

            // Pętla po załącznikach
            $rowIndex = 2; // Zaczynamy od drugiego wiersza
            foreach ($exportData['files'][$language] as $file) {
                // Ustaw pozostałe kolumny (nazwa pliku, rozmiar, link)
                $filesSheet->setCellValue('A' . $rowIndex, $file['file_name']);
                $filesSheet->setCellValue('B' . $rowIndex, round($file['file_size'] / 1024, 2));

                // Utwórz link do pliku w komórce
                $filesSheet->getCell('C' . $rowIndex)->getHyperlink()->setUrl($file['file_link']);
                $filesSheet->setCellValue('C' . $rowIndex, Context::getContext()->getTranslator()->trans('Pobierz'));

                // Wyrównanie poziome od B do C
                $filesSheet->getStyle('B:C')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Ustaw automatyczną szerokość kolumny i zawijanie tekstu dla wszystkich kolumn
                $filesSheet->getColumnDimension('A')->setAutoSize(true);
                $filesSheet->getColumnDimension('B')->setAutoSize(true);
                $filesSheet->getColumnDimension('C')->setAutoSize(true);

                $filesSheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)->getAlignment()->setWrapText(true);

                // Przesuń się do następnego wiersza
                $rowIndex++;
            }
        }




        // =======================================================
        // Dodaj nagłówki w podstawowym arkuszu ==================
        // =======================================================
        foreach ($headers as $column => $header) {

            $sheet->getStyle($column . $header['row'])->applyFromArray($styleArray);

            // Ustaw szerokość kolumn
            if (isset($header['width'])) {
                $sheet->getColumnDimension($column)->setWidth($header['width']);
            }
            
            // Ustaw wysokość komórki
            $sheet->getRowDimension($header['row'])->setRowHeight(30);
            
            // Wyśrodkować
            $sheet->getStyle('A:' . $headerIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Umieść treść w komórce
            $sheet->setCellValue($column . $header['row'], $header['text']);  // '1' oznacza pierwszy wiersz

        }

        // =======================================================
        // Dodaj treści w podstawowym arkuszu ====================
        // =======================================================
        foreach ($contents as $column => $content) {

            $sheet->getRowDimension($content['row'])->setRowHeight(-1);  // Automatyczna wysokość komórki

            $sheet->getStyle($column . $content['row'])->getAlignment()->setWrapText(true); // "Zawijanie" tekstu w komórce

            $sheet->getStyle($column . $content['row'])->getAlignment()->setVertical(PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP); // Wyrównanie w pionie "do góry"

            // Umieść treść w komórce
            $sheet->setCellValue($column . $content['row'], $content['value']);  // '2' oznacza dugi wiersz

            

        }


        // ==============================
        // Zapisz do pliku XLS ==========
        // ==============================
        $exportFilePath = $exportPath . '/' . $exportFileName . '.xls';
        $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save($exportFilePath);

    }

}
