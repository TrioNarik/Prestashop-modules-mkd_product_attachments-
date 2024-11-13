<?php
/*
* Renderowanie Formularza z ustawieniami dla CMS Page i Export File
* 
* Rozszerzenie Installer() => podstawowe dane do formularza 
*/


require_once _PS_MODULE_DIR_ . _MKD_NAME_ . '/classes/Installer/MKDInstallerDefaultData.php';


class AdminCMSPageExportController extends MKDInstallerCustomExportFormData
{

    public function __construct() {
        
        // Ustawienia pomocnicze dla HelperForm z modułu
        $this->name = _MKD_NAME_;
        $this->displayName = _MKD_MODULE_NAME_;
        $this->identifier = _MKD_CONTROLLER_IDENTIFIER_;
        // Ustawić unikalny component w URL konfiguracji
        $this->component = _MKD_MODULE_COMPONENT_[2];
        

    }

    private function getDefaultSortByOption()
    {
        // Pobierz wszystkie opcje sortowania produktów na CMS Page
        $sortByOptions = $this->installCustomCMSPageSortByOption();

        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

            return $sortByOptions;

        // } else {
        //     // Tylko podstawowe opcje
        //     $defaultSortByOptions = [];
        //     foreach ($sortByOptions as $option) {
        //         if ($option['default'] === true) {
        //             $defaultSortByOptions[] = $option;
        //         }
        //     }
        //     return $defaultSortByOptions;
        // }

    }

    private function getDefaultSortWay()
    {
        $sortWay = array(
            array(
                'value' => 'ASC',
                'name_option' => '&#129045; ' . Context::getContext()->getTranslator()->trans('Rosnąco'),
            ),
            array(
                'value' => 'DESC',
                'name_option' => '&#129047; ' . Context::getContext()->getTranslator()->trans('Malejąco'),
            )
        );
        return $sortWay;
    }

    private function getDefaultProductsPerPage()
    {
        $perPage = array(
            array(
                'value' => 6,
                'name_option' => 6,
            ),
            array(
                'value' => 10,
                'name_option' => 10,
            ),
            array(
                'value' => 12,
                'name_option' => 12,
            ),
            array(
                'value' => 15,
                'name_option' => 15,
            ),
            array(
                'value' => 18,
                'name_option' => 18,
            ),
            array(
                'value' => 20,
                'name_option' => 20,
            ),
            array(
                'value' => 25,
                'name_option' => 25,
            ),
            array(
                'value' => 0,
                'name_option' => Context::getContext()->getTranslator()->trans('Wszystkie produkty'),
            )
        );

        return $perPage;
    }

    private function getDefaultProductsViewMode()
    {
        $sortWay = array(
            array(
                'value' => 'grid',
                'name_option' => '&#119638; ' . Context::getContext()->getTranslator()->trans('Siatka'),
            ),
            array(
                'value' => 'catalog',
                'name_option' => '&#8788; ' . Context::getContext()->getTranslator()->trans('Katalog'),
            )
        );
        return $sortWay;
    }
    
    private function getDefaultExportFormat()
    {
        $format = array(
            array(
                'value' => 'csv',
                'name_option' => Context::getContext()->getTranslator()->trans('CSV plik'),
            ),
            array(
                'value' => 'json',
                'name_option' => Context::getContext()->getTranslator()->trans('JSON plik'),
            ),
            array(
                'value' => 'xml',
                'name_option' => Context::getContext()->getTranslator()->trans('XML plik'),
            )
        );

        // Sprawdź, czy klasa PhpOffice\PhpSpreadsheet\Spreadsheet istnieje (XML export)
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
           
            $formatXLS = array(
                array(
                    'value' => 'xls',
                    'name_option' => Context::getContext()->getTranslator()->trans('XLS - arkusz kalkulacyjny'),
                )
            );

            // Połącz tablice
            $format = array_merge($format, $formatXLS);
        }
        
        return $format;
    }
    
    private function getDefaultExportColumns()
    {
        // Pobierz wszystkie Kolumny exportu produktu
        $exportColumnOptions = $this->installCustomExportProductColumn();

        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

            return $exportColumnOptions;

        // } else {
        //     // Tylko podstawowe Kolumny
        //     $defaultexportColumnOptions = [];
        //     foreach ($exportColumnOptions as $column) {
        //         if ($column['default'] === true) {
        //             $defaultexportColumnOptions[] = $column;
        //         }
        //     }
        //     return $defaultexportColumnOptions;
        // }

    }

    private function getDefaultImageSize()
    {
        return $this->installCustomImageSize();
    }

    private function getDefaultImagesCover()
    {
        $imageType = array(
            array(
                'value' => 1,
                'name_option' => Context::getContext()->getTranslator()->trans('Okładka'),
            ),
            array(
                'value' => 0,
                'name_option' => Context::getContext()->getTranslator()->trans('Wszystkie zdjęcia'),
            )
        );
        return $imageType;

    }

    private function getDefaultHooks()
    {
        $hooks = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . pSQL(_MKD_NAME_ . '_hooks') . '`');

        $hookOptions = array();
        
        foreach ($hooks as $hook) {
            $hookOptions[] = array(
                'id' => $hook['id'],
                'label' => $hook['value'] .' ['. $hook['name'] .']', // Ustaw etykietę opcji
            );
        }
        
        return $hookOptions;
    }


    // ========================================================
    // FORMULARZ USTAWIEŃ CMS PAGE && EXPORT ==================
    // ========================================================
    public function renderCMSPageExportOptionForm()
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
       
        // Ustawienia pomocnicze
        $helper = $this->createHelperForm($defaultLang);

        // Pola formularza
        $fieldsForm = $this->createFieldsForm();

        // Ustaw podstawowe dane w formularzu    
        $formOptions = $this->prepareFormValues();
        $helper->fields_value = $formOptions;
        
        return $helper->generateForm(array($fieldsForm));
    }

    private function createHelperForm($defaultLang)
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&id_shop='.(int)Context::getContext()->shop->id.'&section='.$this->component;
        //Obsługa multi-języków:
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;
        $languages = Context::getContext()->controller->getLanguages();
        $helper->languages = $languages;
        // ====================
        $helper->title = $this->name;
        $helper->identifier = $this->identifier;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit_' . $this->name . '_cmspage_export';
        $helper->show_errors = true;

        return $helper;
    }

    private function createFieldsForm()
    {

        // Pobierz podstawowe dane CMSPage:
        $productsSortOptions    = $this->getDefaultSortByOption();
        $productsSortWay        = $this->getDefaultSortWay();
        $productsPerPage        = $this->getDefaultProductsPerPage();
        $productsViewMode       = $this->getDefaultProductsViewMode();
        // Pobierz podstawowe dane Exportu:
        $exportFormat           = $this->getDefaultExportFormat();
        $exportColumns          = $this->getDefaultExportColumns();
        // Pobierz rodzaj i dostępne rozmiary obrazków dla Produktów
        $exportImgSize          = $this->getDefaultImageSize();
        $exportImgCover         = $this->getDefaultImagesCover();

        // Pobierz czas aktualizacji Modułu
        // $timeMarker             = Configuration::get(MKDInstallOptionCMSPageExportProduct::getConfig('prefix') . 'EXPORT_TIMEMARKER');
        // dump($timeMarker);

        $timeMarker = (Configuration::get('EXPORT_TIMEMARKER',false,
        Context::getContext()->shop->id_shop_group,
        Context::getContext()->shop->id));

        // Pobierz domyślne Hooki
        $hookOptions            = $this->getDefaultHooks();
        // ==========================================================


        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => Context::getContext()->getTranslator()->trans('Produkty na stronie CMS i Eksport plików'),
                    'icon' => 'icon-cogs',
                ),
                'tabs' => array(
                    'cmspage' => Context::getContext()->getTranslator()->trans('Kreator CMS Strony'),
                    'export' => Context::getContext()->getTranslator()->trans('Kreator Eksportu plików'),
                    'info' => Context::getContext()->getTranslator()->trans('Jak korzystać ?'),
                )        
            )
        );

        // PREMIUM  Mode = addintional Tab
        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

            // Dodaj zakładkę Premium
            $fieldsForm['form']['tabs']['premium'] = Context::getContext()->getTranslator()->trans('Dodatkowe ustawienia');
        // }

        // cmspage: 1. Przełącznik Statusu
        $fieldsForm['form']['input'][] = array(
            'type' => 'switch',
            'label' => Context::getContext()->getTranslator()->trans('CMS Kontent') .':',
            'name' => 'CMS_PAGE_VIEW_PRODUCTS_SWITCH',
            'is_bool' => true,
            'lang' => true,
            'required' => true,
            'values' => array(
                array(
                    'id' => 'status_on',
                    'value' => 1,
                    'label' => Context::getContext()->getTranslator()->trans('Dostępny'),
                ),
                array(
                    'id' => 'status_off',
                    'value' => 0,
                    'label' => Context::getContext()->getTranslator()->trans('Wyłączony'),
                ),
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Aktywna lub Nieaktywna lista produktów z załącznikami na stronie CMS'),
            'tab' => 'cmspage',
        );

        // cmspage: 2. Sortuj wg:
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Sortuj produkty wg.') .':',
            'name' => 'CMS_PAGE_VIEW_PRODUCTS_SORTBY',
            'options' => array(
                'query' => $productsSortOptions,
                'id' => 'value',
                'name' => 'name_option',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Dostępne opcje sortowania'),
            'tab' => 'cmspage',
        );

        // cmspage: 3. Kierunek sortowania
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Sposób sortowania') . ':',
            'name' => 'CMS_PAGE_VIEW_PRODUCTS_SORTWAY',
            'options' => array(
                'query' => $productsSortWay,
                'id' => 'value',
                'name' => 'name_option',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Metoda sortowania'),
            'tab' => 'cmspage',
        );

        // cmspage: 4. Pagination
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Paginacja') .':',
            'name' => 'CMS_PAGE_VIEW_PRODUCTS_PERPAGE',
            'options' => array(
                'query' => $productsPerPage,
                'id' => 'value',
                'name' => 'name_option',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wyświetlana ilość produktów na stronie'),
            'tab' => 'cmspage',
        );

        // cmspage: 5. PREMIUM Mode = Display mode
        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

            // cmspage: 5. Widok
            $fieldsForm['form']['input'][] = array(
                'type' => 'select',
                'label' => Context::getContext()->getTranslator()->trans('Tryb wyświetlania') .':',
                'name' => 'CMS_PAGE_VIEW_PRODUCTS_VIEWMODE',
                'options' => array(
                    'query' => $productsViewMode,
                    'id' => 'value',
                    'name' => 'name_option',
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Przegląd listy produktów na stronie CMS'),
                'tab' => 'cmspage',
            );
        // }
                     
        // export: 1. Przełącznik Statusu
        $fieldsForm['form']['input'][] = array(
            'type' => 'switch',
            'label' => Context::getContext()->getTranslator()->trans('Eksport danych produktów') . ':',
            'name' => 'EXPORT_SWITCH',
            'is_bool' => true,
            'lang' => true,
            'required' => true,
            'values' => array(
                array(
                    'id' => 'status_on',
                    'value' => 1,
                    'label' => Context::getContext()->getTranslator()->trans('Włączony'),
                ),
                array(
                    'id' => 'status_off',
                    'value' => 0,
                    'label' => Context::getContext()->getTranslator()->trans('Wyłączony'),
                ),
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Aktywny lub Nieaktywny Eksport danych o produktach'),
            'tab' => 'export',
        );

        // export: 2. Folder docelowy
        $fieldsForm['form']['input'][] = array(
            'type' => 'text',
            'label' => Context::getContext()->getTranslator()->trans('Folder') .':',
            'name' => 'EXPORT_FOLDER',
            'required' => true,
            'class' => 'fixed-width-xxl',
            'placeholder' => Context::getContext()->getTranslator()->trans('_export...'),
            'desc' => Context::getContext()->getTranslator()->trans('Bieżący folder:') . _MKD_UPLOAD_DIR_ .'ID/<span class="alert-info">' . Configuration::get('CONF_' . strtoupper(_MKD_NAME_) . '_EXPORT_FOLDER') . '</span>',
            'tab' => 'export',
        );

        // export: 3. Format plików
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Format plików') .':',
            'name' => 'EXPORT_FORMAT',
            'required' => true,
            'options' => array(
                'query' => $exportFormat,
                'id' => 'value',
                'name' => 'name_option',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Dostępne formaty plików'),
            'tab' => 'export',
        );

        // export: 4. Prefix plików
        $fieldsForm['form']['input'][] = array(
            'type' => 'text',
            'label' => Context::getContext()->getTranslator()->trans('Plik _przedrostek_') .':',
            'name' => 'EXPORT_FILE_PREFIX',
            'class' => 'fixed-width-xxl',
            'placeholder' => Context::getContext()->getTranslator()->trans('full, part, version, info') . '...',
            'desc' => Context::getContext()->getTranslator()->trans('Bieżąca nazwa') . ': ' . Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) . '_<span class="alert-info">' . Configuration::get(MKDInstallOptionCMSPageExportProduct::getConfig('prefix') . 'EXPORT_FILE_PREFIX') . '</span>_product-name_DD_MM_YYYY.' . Configuration::get(MKDInstallOptionCMSPageExportProduct::getConfig('prefix') . 'EXPORT_FORMAT') .'</strong>',
            'tab' => 'export',
        );

        // export: 5. Kolumny z produktu
        $fieldsForm['form']['input'][] = array(
            'type' => 'checkbox',
            'label' => Context::getContext()->getTranslator()->trans('Zakres eksportu danych') .':',
            'name' => 'EXPORT_COLUMNS[]',
            'required' => true,
            'values' => array(
                'query' => $exportColumns,
                'id' => 'id',
                'name' => 'name',
                'value' => 'val'        
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz dane produktu do wyeksportowania'),
            'tab' => 'export',
        );

        // export: 6. Rozmiar obrazków produktu
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Rozmiar obrazów:'),
            'name' => 'EXPORT_IMG_SIZE',
            'required' => true,
            'options' => array(
                'query' => $exportImgSize,
                'id' => 'value',
                'name' => 'name_option',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Dostępny rozmiary obrazów dla produktów'),
            'tab' => 'export',
            'form_group_class' => 'images', // class dla JS
        );

        // export: 7. Rodzaj obrazków produktu
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Typ obrazu') .':',
            'name' => 'EXPORT_IMG_COVER',
            'required' => true,
            'options' => array(
                'query' => $exportImgCover,
                'id' => 'value',
                'name' => 'name_option',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz Okładkę lub Wszystkie zdjęcia produktu'),
            'tab' => 'export',
            'form_group_class' => 'images', // class dla JS
        );

        // export: 8. Info Blok
        $fieldsForm['form']['input'][] = array(
            'type' => 'html',
            'name' => 'help',
            'html_content' => '
                    <div class="alert alert-warning" role="alert">
                        <p><strong>' . Context::getContext()->getTranslator()->trans('Ważne:') . '</strong> ' . Context::getContext()->getTranslator()->trans('Moduł wygeneruje pliki na żądanie') . ' !</p>
                        <p>' . Context::getContext()->getTranslator()->trans('Na żądanie zostanie wygenerowany plik %format%. Takie podejście oszczędza zasoby serwera, unikając niepotrzebnego generowania plików i uwzględnia wszelkie aktualizacje produktu, pliki załączników, wartości dodatkowych pól lub opcje modułu, które ulegną zmianie.',
                            ['%format%' => '<span class="badge badge-warning">' . Configuration::get(MKDInstallOptionCMSPageExportProduct::getConfig('prefix') . 'EXPORT_FORMAT') . '</span>']) . '</p>
                        <hr />
                        <pre>' . Context::getContext()->getTranslator()->trans('Aktualny znacznik czasu') . ': <code><strong>' . $timeMarker . '</strong> [' . date('Y-m-d H:i:s', $timeMarker) . ']</code></pre>
                        
                    </div>
                    ',
            'tab' => 'export',              
        );

        // PREMIUM  Mode = ShortCode
        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

            // used: 1. ShortCode
            $fieldsForm['form']['input'][] = array(
                'type' => 'html',
                'name' => 'shortcode',
                'label' => Context::getContext()->getTranslator()->trans('Użyj Kodu w treści CMS') .':',
                'html_content' => '
                        <div class="alert alert-success" role="alert">
                        {{hookDisplayCMSProductAttachments}}
                        </div>
                        ',
                'desc' => Context::getContext()->getTranslator()->trans('Skopiuj i wstaw krótki Kod w treści strony CMS'),
                'tab' => 'info',              
            );
        // }

        // used: 2. Hook TPL
        $fieldsForm['form']['input'][] = array(
            'type' => 'html',
            'name' => 'tpl',
            'label' => Context::getContext()->getTranslator()->trans('Użyj Hook') .':',
            'html_content' => '
                    <div class="alert alert-success" role="alert">
                    {hook h=\'displayCMSProductAttachments\'}
                    </div>
                    ',
            'desc' => Context::getContext()->getTranslator()->trans('Możesz dodać kod do pliku *.tpl [page.tpl], aby wyświetlić szablon'),
            'tab' => 'info',              
        );

        // PREMIUM  Mode = Additional Settings
        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {
           
            
            // Nagłówek CMS Page
            $fieldsForm['form']['input'][] = array(
                'type' => 'html',
                'label' => '<svg fill="#828282" height="64px" width="64px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-179.2 -179.2 870.40 870.40" xml:space="preserve" stroke="#828282"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-179.2" y="-179.2" width="870.40" height="870.40" rx="435.2" fill="#f7f7f7" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M512,405.854V31.219H0v374.634h187.317v37.463H143.61v37.463H368.39v-37.463h-43.707v-37.463H512z M287.219,443.317 h-62.439v-37.463h62.439V443.317z M37.463,368.39V68.683h437.073V368.39H37.463z"></path> </g> </g> <g> <g> <path d="M253.793,199.805c-8.482-35.754-40.654-62.439-78.964-62.439c-44.758,0-81.171,36.413-81.171,81.171 c0,44.758,36.413,81.171,81.171,81.171c38.31,0,70.482-26.686,78.964-62.439h52.158v31.219h37.463v-31.219h12.488v43.707h37.463 v-43.707h18.732v-37.463H253.793z M174.829,262.244c-24.1,0-43.707-19.607-43.707-43.707c0-24.1,19.607-43.707,43.707-43.707 s43.707,19.607,43.707,43.707C218.537,242.637,198.93,262.244,174.829,262.244z"></path> </g> </g> </g></svg>',
                'name' => 'cms-icon',
                'html_content' => '<h2> '. Context::getContext()->getTranslator()->trans('Kontrola zawartości strony CMS').'</h2><hr />',
                'desc' => Context::getContext()->getTranslator()->trans('Możesz zapobiec nieautoryzowanemu dostępowi do treści strony CMS i chronić poufne informacje'),
                'tab' => 'premium',
            );

            // premium: 1. Przełącznik dostępu do strony CMS
            $fieldsForm['form']['input'][] = array(
                'type' => 'switch',
                'label' => Context::getContext()->getTranslator()->trans('Dostęp') .':',
                'name' => 'CMS_PAGE_ACCESS_LOCK',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'status_on',
                        'value' => 1,
                        'label' => Context::getContext()->getTranslator()->trans('Wymagane logowanie'),
                    ),
                    array(
                        'id' => 'status_off',
                        'value' => 0,
                        'label' => Context::getContext()->getTranslator()->trans('Dostęp publiczny'),
                    ),
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Treść Strony CMS jest publicznie dostępna lub ograniczona dla autoryzowanych/zalogowanych Użytkowników'),
                'tab' => 'premium',
            );

            // Nagłówek XLS file
            $fieldsForm['form']['input'][] = array(
                'type' => 'html',
                'label' => '<svg fill="#828282" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="-192.78 -192.78 936.36 936.36" xml:space="preserve" stroke="#828282"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-192.78" y="-192.78" width="936.36" height="936.36" rx="468.18" fill="#f7f7f7" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M488.432,197.019h-13.226v-63.816c0-0.398-0.063-0.799-0.111-1.205c-0.021-2.531-0.833-5.021-2.568-6.992L366.325,3.694 c-0.032-0.031-0.063-0.042-0.085-0.076c-0.633-0.707-1.371-1.295-2.151-1.804c-0.231-0.155-0.464-0.285-0.706-0.422 c-0.676-0.366-1.393-0.675-2.131-0.896c-0.2-0.053-0.38-0.135-0.58-0.188C359.87,0.119,359.037,0,358.193,0H97.2 c-11.918,0-21.6,9.693-21.6,21.601v175.413H62.377c-17.049,0-30.873,13.818-30.873,30.873v160.545 c0,17.038,13.824,30.87,30.873,30.87h13.224V529.2c0,11.907,9.682,21.601,21.6,21.601h356.4c11.907,0,21.6-9.693,21.6-21.601 V419.302h13.226c17.044,0,30.871-13.827,30.871-30.87v-160.54C519.297,210.832,505.48,197.019,488.432,197.019z M97.2,21.601 h250.193v110.51c0,5.967,4.841,10.8,10.8,10.8h95.407v54.108H97.2V21.601z M339.562,354.344v31.324H236.509V220.704h37.46v133.64 H339.562z M74.25,385.663l47.73-83.458l-46.019-81.501h42.833l14.439,30.099c4.899,10.03,8.572,18.116,12.49,27.414h0.483 c3.926-10.529,7.101-17.872,11.259-27.414l13.954-30.099h42.588l-46.509,80.525l48.961,84.438h-43.081l-14.929-29.858 c-6.115-11.507-10.036-20.07-14.684-29.615h-0.49c-3.431,9.55-7.583,18.119-12.722,29.615l-13.711,29.858H74.25V385.663z M453.601,523.353H97.2V419.302h356.4V523.353z M401.963,388.125c-18.837,0-37.446-4.904-46.738-10.04l7.578-30.839 c10.04,5.136,25.46,10.283,41.375,10.283c17.139,0,26.188-7.099,26.188-17.867c0-10.283-7.831-16.157-27.659-23.256 c-27.411-9.55-45.282-24.722-45.282-48.718c0-28.15,23.498-49.684,62.427-49.684c18.594,0,32.305,3.927,42.093,8.322l-8.322,30.109 c-6.607-3.186-18.361-7.834-34.509-7.834c-16.152,0-23.983,7.338-23.983,15.913c0,10.525,9.291,15.18,30.591,23.258 c29.125,10.769,42.836,25.936,42.836,49.191C468.545,364.627,447.257,388.125,401.963,388.125z"></path> </g> </g></svg>',
                'name' => 'xls-icon',
                'html_content' => '<h2> '. Context::getContext()->getTranslator()->trans('Kreatora exportu Excela').'</h2><hr />',
                'desc' => Context::getContext()->getTranslator()->trans('Możesz ustawić dodatkowe ustawienia pliku XLS, aby dane prezentowały się w odpowiedni sposób'),
                'tab' => 'premium',
                'form_group_class' => 'xls'
            );

            // premium: 2. Kolor nagłówków dla XLS plików
            $fieldsForm['form']['input'][] = array(
                'type' => 'color',
                'label' => Context::getContext()->getTranslator()->trans('Nagłówki') .':',
                'name' => 'FILE_XLS_COLOR',
                'desc' => Context::getContext()->getTranslator()->trans('Wybierz tło nagłówka dla arkuszy XLS [domyślny Kontrast = 128]') . ' <sup title="" data-toggle="tooltip" class="label-tooltip" data-original-title="'. Context::getContext()->getTranslator()->trans('Próg jasności, poniżej którego kolor czcionki nagłówka jest uważany za ciemny') .'" data-html="true" data-placement="top"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" fill="#25b9d7" /></svg></sup>',
                'tab' => 'premium',
                'form_group_class' => 'xls'
            );

            // premium: 3. Logo w XLS plikach
            $fieldsForm['form']['input'][] = array(
                'type' => 'switch',
                'label' => Context::getContext()->getTranslator()->trans('Logo') .':',
                'name' => 'FILE_XLS_LOGO',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'status_on',
                        'value' => 1,
                        'label' => Context::getContext()->getTranslator()->trans('Tak'),
                    ),
                    array(
                        'id' => 'status_off',
                        'value' => 0,
                        'label' => Context::getContext()->getTranslator()->trans('Nie'),
                    ),
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Umożliwia wstawienie logo sklepu do aktywnego arkusza'). '[' . Context::getContext()->getTranslator()->trans('domyślna Komórka') .' = '. MKDInstallOptionCMSPageExportProduct::getConfig('xlsLogoCell') .']' . ' <sup title="" data-toggle="tooltip" class="label-tooltip" data-original-title="'. Context::getContext()->getTranslator()->trans('Max. szerokość').' ' . MKDInstallOptionCMSPageExportProduct::getConfig('xlsLogoWidth') .'px' .'" data-html="true" data-placement="top"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" fill="#25b9d7" /></svg></sup>',
                'tab' => 'premium',
                'form_group_class' => 'xls'
            );

            // premium: 4. Przełącznik Images w XSL
            $fieldsForm['form']['input'][] = array(
                'type' => 'switch',
                'label' => Context::getContext()->getTranslator()->trans('Wstaw obrazy jako') .':',
                'name' => 'FILE_XLS_IMAGES',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'status_on',
                        'value' => 1,
                        'label' => Context::getContext()->getTranslator()->trans('Plik graficzny'),
                    ),
                    array(
                        'id' => 'status_off',
                        'value' => 0,
                        'label' => Context::getContext()->getTranslator()->trans('URL obrazu'),
                    ),
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Pozwala wybrać sposób dołączania zdjęć produktów do pliku XLS'),
                'tab' => 'premium',
                'form_group_class' => 'xls'
            );
            
            // Nagłówek Wizard Extra Fields of Product
            $fieldsForm['form']['input'][] = array(
                'type' => 'html',
                'label' => '<svg width="64px" height="64px" viewBox="-3.6 -3.6 31.20 31.20" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#828282"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-3.6" y="-3.6" width="31.20" height="31.20" rx="15.6" fill="#f7f7f7" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M17 15V19M19 17H15M16 10H18C19.1046 10 20 9.10457 20 8V6C20 4.89543 19.1046 4 18 4H16C14.8954 4 14 4.89543 14 6V8C14 9.10457 14.8954 10 16 10ZM6 20H8C9.10457 20 10 19.1046 10 18V16C10 14.8954 9.10457 14 8 14H6C4.89543 14 4 14.8954 4 16V18C4 19.1046 4.89543 20 6 20ZM6 10H8C9.10457 10 10 9.10457 10 8V6C10 4.89543 9.10457 4 8 4H6C4.89543 4 4 4.89543 4 6V8C4 9.10457 4.89543 10 6 10Z" stroke="" "="" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>',
                'name' => 'cms-icon',
                'html_content' => '<h2> '. Context::getContext()->getTranslator()->trans('Kreator eksportu dodatkowych Pól produktu').'</h2><hr />',
                'desc' => Context::getContext()->getTranslator()->trans('Do eksportowanych danych możesz dodać Dodatkowe Pola Produktu'),
                'tab' => 'premium',
            );
        // }

        // premium: 5. EXTRA Fields Produktu
        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {
            // generowanie Extra product Fields na bazie ustawień getCofig()
            for ($i = 1; $i <= (int)MKDInstallOptionCMSPageExportProduct::getConfig('numberExtaProductFields'); $i++) {
                $fieldName = 'PRODUCT_EXTRA_FIELD_TITLE_' . $i .'_LANG';
                $placeholder = '';
            
                switch ($i) {
                    case 1:
                        $placeholder = Context::getContext()->getTranslator()->trans('Max. power...');
                        break;
                    case 2:
                        $placeholder = Context::getContext()->getTranslator()->trans('Product Code, CN, COP...');
                        break;
                    case 3:
                        $placeholder = Context::getContext()->getTranslator()->trans('Energy label...');
                        break;
                    default:
                        $placeholder = '...';
                }
            
                $fieldsForm['form']['input'][] = array(
                    'type' => 'text',
                    'label' => '<span class="badge badge-warning">'. Context::getContext()->getTranslator()->trans('Extra pole produktu') . ' ' . $i . '</span><br /><small>' . Context::getContext()->getTranslator()->trans('[max. 50 znaków]').'</small>',
                    'name' => $fieldName,
                    'lang' => true,
                    'placeholder' => $placeholder,
                    'desc' => Context::getContext()->getTranslator()->trans('Wprowadź nazwę pola. A wartość tego pola można dodać na Stronie Edycji Produktu => Moduły'),
                    'maxlength' => 50,
                    'tab' => 'premium',
                );
                
                // Active
                $fieldsForm['form']['input'][] = array(
                    'type' => 'switch',
                    'label' => Context::getContext()->getTranslator()->trans('Status') .':',
                    'name' => 'PRODUCT_EXTRA_FIELD_ACTIVE_' .$i,
                    'is_bool' => true,
                    'lang' => true,
                    'values' => array(
                        array(
                            'id' => 'status_on',
                            'value' => 1,
                            'label' => Context::getContext()->getTranslator()->trans('Włączone'),
                        ),
                        array(
                            'id' => 'status_off',
                            'value' => 0,
                            'label' => Context::getContext()->getTranslator()->trans('Wyłączone'),
                        ),
                    ),
                    'desc' => Context::getContext()->getTranslator()->trans('Aktywny lub Nieaktywny status tego dodatkowego pola'),
                    'tab' => 'premium',
                );

                // HTML Content
                $fieldsForm['form']['input'][] = array(
                    'type' => 'switch',
                    'label' => Context::getContext()->getTranslator()->trans('HTML Treści:') . '<br /><small>' . Context::getContext()->getTranslator()->trans('[max. 3200 znaków]').'</small>',
                    'name' => 'PRODUCT_EXTRA_FIELD_HTMLCONTENT_' .$i,
                    'is_bool' => true,
                    'lang' => true,
                    'values' => array(
                        array(
                            'id' => 'status_on',
                            'value' => 1,
                            'label' => Context::getContext()->getTranslator()->trans('Tak'),
                        ),
                        array(
                            'id' => 'status_off',
                            'value' => 0,
                            'label' => Context::getContext()->getTranslator()->trans('Nie'),
                        ),
                    ),
                    'desc' => Context::getContext()->getTranslator()->trans('Wartość pola będzie zawierać treść HTML [wprowadzanie tekstu wielowierszowego => Edytor obszaru tekstowego]'),
                    'tab' => 'premium',
                );
                
                // Display Front
                $fieldsForm['form']['input'][] = array(
                    'type' => 'switch',
                    'label' => Context::getContext()->getTranslator()->trans('Wyświetlaj także na Stronie Produktu') .':',
                    'name' => 'PRODUCT_EXTRA_FIELD_FRONT_VIEW_' .$i,
                    'is_bool' => true,
                    'lang' => true,
                    'values' => array(
                        array(
                            'id' => 'status_on',
                            'value' => 1,
                            'label' => Context::getContext()->getTranslator()->trans('Tak'),
                        ),
                        array(
                            'id' => 'status_off',
                            'value' => 0,
                            'label' => Context::getContext()->getTranslator()->trans('Nie'),
                        ),
                    ),
                    'desc' => Context::getContext()->getTranslator()->trans('Czy uwzględnić to dodatkowe pole na stronie produktu (PDP)?'),
                    'tab' => 'premium',
                    'disabled' => true,
                );

                // Użyć HOOK zainstalowane w module dla Front
                // $fieldsForm['form']['input'][] = array(
                //     'type' => 'select',
                //     'label' => Context::getContext()->getTranslator()->trans('Show in:'),
                //     'name' => 'PRODUCT_EXTRA_FIELD_FRONT_HOOK_' . $i,
                //     'options' => array(
                //         'query' => $hookOptions,
                //         'id' => 'id',
                //         'name' => 'label',
                //     ),
                //     'desc' => Context::getContext()->getTranslator()->trans('Select the hook to associate the attachment with'),
                //     'tab' => 'premium',
                // );
                
            }
        // }
        

        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

            // Nagłówek Kombinacji Produktu
            $fieldsForm['form']['input'][] = array(
                'type' => 'html',
                'label' => '<svg fill="#828282" height="64px" width="64px" version="1.1" id="Icons" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-11.2 -11.2 54.40 54.40" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-11.2" y="-11.2" width="54.40" height="54.40" rx="27.2" fill="#f7f7f7" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <polygon points="8.3,20 10.7,20 9.5,18 "></polygon> <path d="M27,4H17V2c0-0.6-0.4-1-1-1s-1,0.4-1,1v2H5C3.3,4,2,5.3,2,7v18c0,1.7,1.3,3,3,3h10v2c0,0.6,0.4,1,1,1s1-0.4,1-1v-2h10 c1.7,0,3-1.3,3-3V7C30,5.3,28.7,4,27,4z M9.1,7.6c0.1-0.1,0.1-0.2,0.2-0.3c0.1-0.1,0.2-0.2,0.3-0.2C10,6.9,10.4,7,10.7,7.3 c0.1,0.1,0.2,0.2,0.2,0.3C11,7.7,11,7.9,11,8c0,0.3-0.1,0.5-0.3,0.7C10.5,8.9,10.3,9,10,9C9.7,9,9.5,8.9,9.3,8.7C9.1,8.5,9,8.3,9,8 C9,7.9,9,7.7,9.1,7.6z M6.3,7.3c0,0,0.1-0.1,0.1-0.1c0.1,0,0.1-0.1,0.2-0.1C6.7,7,6.7,7,6.8,7c0.1,0,0.3,0,0.4,0 c0.1,0,0.1,0,0.2,0.1c0.1,0,0.1,0.1,0.2,0.1c0,0,0.1,0.1,0.1,0.1c0.1,0.1,0.2,0.2,0.2,0.3C8,7.7,8,7.9,8,8c0,0.1,0,0.3-0.1,0.4 C7.9,8.5,7.8,8.6,7.7,8.7C7.5,8.9,7.3,9,7,9S6.5,8.9,6.3,8.7C6.1,8.5,6,8.3,6,8C6,7.7,6.1,7.5,6.3,7.3z M13.5,22.9 C13.3,23,13.2,23,13,23c-0.3,0-0.7-0.2-0.9-0.5L11.8,22H7.2l-0.3,0.5C6.6,23,6,23.1,5.5,22.9C5,22.6,4.9,22,5.1,21.5l3.5-6 c0.4-0.6,1.4-0.6,1.7,0l3.5,6C14.1,22,14,22.6,13.5,22.9z M13.9,8.4c-0.1,0.1-0.1,0.2-0.2,0.3C13.5,8.9,13.3,9,13,9 c-0.1,0-0.3,0-0.4-0.1c-0.1-0.1-0.2-0.1-0.3-0.2c-0.1-0.1-0.2-0.2-0.2-0.3C12,8.3,12,8.1,12,8c0-0.1,0-0.3,0.1-0.4 c0.1-0.1,0.1-0.2,0.2-0.3c0.4-0.4,1-0.4,1.4,0c0.1,0.1,0.2,0.2,0.2,0.3C14,7.7,14,7.9,14,8C14,8.1,14,8.3,13.9,8.4z M17,26 c0,0.6-0.4,1-1,1s-1-0.4-1-1V6c0-0.6,0.4-1,1-1s1,0.4,1,1V26z M23.5,23H20c-0.6,0-1-0.4-1-1v-3v-3c0-0.6,0.4-1,1-1h2.5 c1.4,0,2.5,1.1,2.5,2.5c0,0.3-0.1,0.6-0.2,0.9c0.7,0.4,1.2,1.2,1.2,2.1C26,21.9,24.9,23,23.5,23z"></path> <g> <path d="M23.5,20h-1H21v1h2.5c0.3,0,0.5-0.2,0.5-0.5S23.8,20,23.5,20z"></path> <path d="M23,17.5c0-0.3-0.2-0.5-0.5-0.5H21v1h1.5C22.8,18,23,17.8,23,17.5z"></path> </g> </g></svg>',
                'name' => 'cms-icon',
                'html_content' => '<h2> '. Context::getContext()->getTranslator()->trans('Kreator eksportu Kombinacji produktów').'</h2><hr />',
                'desc' => Context::getContext()->getTranslator()->trans('Do danych eksportu możesz dodać także Kombinacje produktów'),
                'tab' => 'premium',
            );

            // premium: 6. Przełącznik exportu Kombinacji produktu
            $fieldsForm['form']['input'][] = array(
                'type' => 'switch',
                'label' => Context::getContext()->getTranslator()->trans('Kombinacje') . ': <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="'. Context::getContext()->getTranslator()->trans('To export Product &#171;Combinations&#187;, you must first create product Attributes [Catalog -> Attributes and Features] and then generete Product -> Combinations') .'" data-html="true" data-placement="top"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM169.8 165.3c7.9-22.3 29.1-37.3 52.8-37.3h58.3c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24V250.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1H222.6c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" fill="#25b9d7" /></svg></span>',
                'name' => 'EXPORT_PRODUCT_COMBINATIONS',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'status_on',
                        'value' => 1,
                        'label' => Context::getContext()->getTranslator()->trans('Tak'),
                    ),
                    array(
                        'id' => 'status_off',
                        'value' => 0,
                        'label' => Context::getContext()->getTranslator()->trans('Nie'),
                    ),
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Umożliwia eksport również wszystkich dostępnych Kombinacji wybranego Produktu'),
                'disabled' => true,
                'tab' => 'premium',
            );

            // Nagłówek Counter
            $fieldsForm['form']['input'][] = array(
                'type' => 'html',
                'label' => '<svg width="64px" height="64px" viewBox="-8.4 -8.4 40.80 40.80" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"><rect x="-8.4" y="-8.4" width="40.80" height="40.80" rx="20.4" fill="#f7f7f7" strokewidth="0"></rect></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 18.5V4H4V20H20V18.5H5.5Z" fill="#828282"></path> <path d="M10.5 17V8.00131H12V17H10.5Z" fill="#828282"></path> <path d="M7 17V12H8.5V17H7Z" fill="#fbbb22"></path> <path d="M17.5 17V10H19V17H17.5Z" fill="#fbbb22"></path> <path d="M14 17V5H15.5V17H14Z" fill="#828282"></path> </g></svg>',
                'name' => 'counter-icon',
                'html_content' => '<h2>'. Context::getContext()->getTranslator()->trans('Statystyki i Liczniki').'</h2><hr />',
                'desc' => Context::getContext()->getTranslator()->trans('Możesz zbierać dodatkowe dane o pliku załączników/eksportu lub sprawdzić współczynnik klikalności (CTR)'),
                'tab' => 'premium',
            );

            // premium: 7. Przełącznik Download Counter Attachments
            $fieldsForm['form']['input'][] = array(
                'type' => 'switch',
                'label' => Context::getContext()->getTranslator()->trans('Licznik pobrań załączików') .':',
                'name' => 'ATTACHMENT_DOWNLOAD_COUNTER',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'status_on',
                        'value' => 1,
                        'label' => Context::getContext()->getTranslator()->trans('Włączony'),
                    ),
                    array(
                        'id' => 'status_off',
                        'value' => 0,
                        'label' => Context::getContext()->getTranslator()->trans('Wyłączony'),
                    ),
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Prosty i wydajny licznik pobierania załączników, obsługuje dowolny typ pliku'),
                'tab' => 'premium',
            );

            // premium: 8. Przełącznik Counter Attachment Views
            $fieldsForm['form']['input'][] = array(
                'type' => 'switch',
                'label' => Context::getContext()->getTranslator()->trans('Licznik wyświetleń załączików') .':',
                'name' => 'ATTACHMENT_VIEWS_COUNTER',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'status_on',
                        'value' => 1,
                        'label' => Context::getContext()->getTranslator()->trans('Włączony'),
                    ),
                    array(
                        'id' => 'status_off',
                        'value' => 0,
                        'label' => Context::getContext()->getTranslator()->trans('Wyłączony'),
                    ),
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Pozwala wyświetlić ile razy plik Załączników dla dowolnego produktu był przeglądany na Stronie CMS'),
                'disabled' => true,
                'tab' => 'premium',
            );

            // premium: 9. Przełącznik Counter Export files
            $fieldsForm['form']['input'][] = array(
                'type' => 'switch',
                'label' => Context::getContext()->getTranslator()->trans('Licznik pobrań plików eksportu') .':',
                'name' => 'EXPORT_DOWNLOAD_COUNTER',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'status_on',
                        'value' => 1,
                        'label' => Context::getContext()->getTranslator()->trans('Włączony'),
                    ),
                    array(
                        'id' => 'status_off',
                        'value' => 0,
                        'label' => Context::getContext()->getTranslator()->trans('Wyłączony'),
                    ),
                ),
                'desc' => Context::getContext()->getTranslator()->trans('Licznik pobierania plików eksportowych umożliwiający szybkie przeglądanie i rejestrowanie danych w celu szybszej analizy'),
                'tab' => 'premium',
            );
        // }


        // =============== Przyciski Akcji ==================
        $fieldsForm['form']['buttons'] = array(
            'save' => array(
                'title' => Context::getContext()->getTranslator()->trans('Aktualizuj'),
                'name' => 'saveCMSPageExport',
                'type' => 'submit',
                'class' => 'pull-right',
            ),
            'default' => array(
                'title' => '&#8634; ' . Context::getContext()->getTranslator()->trans('Przywróć ustawienia'),
                'name' => 'restoreCMSPageExport',
                'type' => 'button',
                'href' => Context::getContext()->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&cms_export_restore=1&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
        );
        
        return $fieldsForm;
        
    }

    private function prepareFormValues()
    {

        $cmsPageExportOptions = [];

        // 1. Pobierz dane CMS Page Form
        $cmsPageFormOptions = MKDInstallOptionCMSPageExportProduct::setDefaultFormCMSPageConfig('get');
 
        foreach ($cmsPageFormOptions as $formFieldName => $value) {

            // Klucze bez prefixu => nazwy pól formularza
            $keyWithoutPrefix = str_replace(MKDInstallOptionCMSPageExportProduct::getConfig('prefix'), '', $formFieldName);

            $cmsPageExportOptions[$keyWithoutPrefix] = $value;
            
        }

        // 2. Pobierz dane Export Form
        $cmsPageFormOptions = MKDInstallOptionCMSPageExportProduct::setDefaultFormExportConfig('get');
 
        foreach ($cmsPageFormOptions as $formFieldName => $value) {
            
            // Klucze bez prefixu => nazwy pól formularza
            $keyWithoutPrefix = str_replace(MKDInstallOptionCMSPageExportProduct::getConfig('prefix'), '', $formFieldName);

            if ($keyWithoutPrefix === 'EXPORT_COLUMNS') {
        
                $exportColumnsSelected = unserialize($value);

                // Sprawdź, czy dane są tablicą
                if (is_array($exportColumnsSelected)) {
                    // Pobierz wszystkie kolumny
                    $exportColumnsAll = $this->installCustomExportProductColumn();
        
                    // Iteruj po wszystkich dostępnych kolumnach
                    foreach ($exportColumnsAll as $columnsOption) {

                        $column_ID = $columnsOption['id'];
                        $columnName = $columnsOption['val'];

                        // Sprawdź, czy obecna kolumna jest zaznaczona w konfiguracji
                        $isChecked = in_array($columnName, $exportColumnsSelected);

                        // Ustaw wartość w tablicy $cmsPageExportOptions dla danego checkboxa
                        $cmsPageExportOptions['EXPORT_COLUMNS[]_' . $column_ID] = $isChecked;
                    }

                }

            } else {

                $cmsPageExportOptions[$keyWithoutPrefix] = $value;
            }
        }

        // 3. Pobierz dane Premium Form
        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {
            $cmsPageFormOptions = MKDInstallOptionCMSPageExportProduct::setPremiumCMSPageExportFormConfig(false, 'get');

            foreach ($cmsPageFormOptions as $formFieldName => $value) {

                // Klucze bez prefixu => nazwy pól formularza
                $keyWithoutPrefix = str_replace(MKDInstallOptionCMSPageExportProduct::getConfig('prefix'), '', $formFieldName);

                $cmsPageExportOptions[$keyWithoutPrefix] = $value;
            }
        // }

        // =======================================
        // Extra Fields TABELE BD ================
        // =======================================
        // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

            // Załaduj Modele Extra Fields Product
            require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

            $shop_id = (int)Tools::getValue('id_shop', Configuration::get('PS_SHOP_DEFAULT'));

            $languages = Language::getLanguages();

            // Iteruj po numerach Extra Fields => getConfig()
            for ($i = 1; $i <= (int)MKDInstallOptionCMSPageExportProduct::getConfig('numberExtaProductFields'); $i++) {

                // Przypisz nazwy pól formularz do odpowiednich zmiennych
                $activeFieldStatus      = 'PRODUCT_EXTRA_FIELD_ACTIVE_' . $i;
                $htmlContentField       = 'PRODUCT_EXTRA_FIELD_HTMLCONTENT_' . $i;
                $frontViewFieldStatus   = 'PRODUCT_EXTRA_FIELD_FRONT_VIEW_' . $i;
                // Hook ID => t jeszcze                
                $frontViewHookID        = 'PRODUCT_EXTRA_FIELD_FRONT_HOOK_' . $i;
                // Tytuł => multi-lang
                $fieldLangTitle         = 'PRODUCT_EXTRA_FIELD_TITLE_' . $i .'_LANG';


                // Pobierz ID pola
                $field_ID = MKDExtraProductFieldsModel::getExtraFieldId($i);

                if ($field_ID) {
                    // Pobierz wszytskie dane ID fields i sklepu
                    $extraFieldData = MKDExtraProductFieldsModel::getSummaryExtraFieldDataByShopId($field_ID, $shop_id);
                
                    if (is_array($extraFieldData)) {
                        
                        // Dodaj wartości do tablicy $cmsPageExportOptions => domyślnie 0/1
                        $cmsPageExportOptions[$activeFieldStatus] = isset($extraFieldData['active'])
                            ? $extraFieldData['active']
                            : 0;
                        $cmsPageExportOptions[$htmlContentField] = isset($extraFieldData['html_content'])
                            ? $extraFieldData['html_content']
                            : 0;
                        $cmsPageExportOptions[$frontViewFieldStatus] = isset($extraFieldData['front_view'])
                            ? $extraFieldData['front_view'] 
                            : 0;
                        $cmsPageExportOptions[$frontViewHookID] = isset($extraFieldData['hook_id']) 
                            ? $extraFieldData['hook_id'] 
                            : 1;
                
                               
                        foreach ($languages as $language) {
                            $lang_ID = $language['id_lang'];
                            // Pobierz Title Lang dla danego extra field
                            $extraFieldTitle = MKDExtraProductFieldsLangModel::getExtraFieldTitleLangByFieldId($field_ID, $lang_ID);

                            // Dodaj wartości do tablicy $cmsPageExportOptions
                            $cmsPageExportOptions[$fieldLangTitle][$lang_ID] = isset($extraFieldTitle['title']) 
                                ? $extraFieldTitle['title']
                                : '';
                        }
                    } else {
                        Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('Product Extra Fields settings: no data available');
                    }
                } else {

                    // Wstaw domyślne/początkowe dane dla extra fields
                    // Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('Extra Product Fields no available');

                    $cmsPageExportOptions[$activeFieldStatus] = 0;
                    $cmsPageExportOptions[$htmlContentField] = 0;
                    $cmsPageExportOptions[$frontViewFieldStatus] = 0;
                    $cmsPageExportOptions[$frontViewHookID] = 1;

                }
                
                
            }
            
        // }
        
        return  $cmsPageExportOptions;
    }

    
    // ==================================================================
    // Aktualizacja opcji CMS Page & Exprot =============================
    // ==================================================================
    public function postProcess()
    {
        $shop_id = (int) Tools::getValue('id_shop', Context::getContext()->shop->id);
        $lang_id = Configuration::get('PS_LANG_DEFAULT');
        
        // =================
        // Zapisz ustawienia
        // =================
        if (Tools::isSubmit('saveCMSPageExport')) {

            // 1. Pobierz dane CMS Page Form
            $cmsPageEnable    = (int) Tools::getValue('CMS_PAGE_VIEW_PRODUCTS_SWITCH');
            $cmsPageSortBy    = Tools::getValue('CMS_PAGE_VIEW_PRODUCTS_SORTBY');
            $cmsPageSortWay   = Tools::getValue('CMS_PAGE_VIEW_PRODUCTS_SORTWAY');
            $cmsPagePerPage   = (int) Tools::getValue('CMS_PAGE_VIEW_PRODUCTS_PERPAGE');
            $cmsPageViewMode  = Tools::getValue('CMS_PAGE_VIEW_PRODUCTS_VIEWMODE');
            
            // Pobierz aktualną datę i godzinę
            $currentDateTime = date('Y-m-d H:i:s');

            // Zapisz dane CMS Page Form
            MKDInstallOptionCMSPageExportProduct::setDefaultFormCMSPageConfig('update', $cmsPageEnable, $cmsPageSortBy, $cmsPageSortWay, $cmsPagePerPage, $cmsPageViewMode);

            
            // 2. Pobierz dane EXPORT Form
            $exportTimeMarker   = (int) time();
            $exportEnable       = (int) Tools::getValue('EXPORT_SWITCH');
            $exportFolder       = (!Validate::isGenericName(Tools::getValue('EXPORT_FOLDER')) || empty(trim(Tools::getValue('EXPORT_FOLDER'))))
                ? Configuration::get(MKDInstallOptionCMSPageExportProduct::getConfig('prefix') . 'EXPORT_FOLDER')
                : Tools::getValue('EXPORT_FOLDER');
            $exportFormat = Tools::getValue('EXPORT_FORMAT');
            $exportFilePrefix   = (!Validate::isFileName(Tools::getValue('EXPORT_FILE_PREFIX')))
                ? ''
                : Tools::getValue('EXPORT_FILE_PREFIX');
            $exportImgSize      = (int) Tools::getValue('EXPORT_IMG_SIZE');
            $exportImgCover     = (int) Tools::getValue('EXPORT_IMG_COVER');
            $exportColumns      = serialize(Tools::getValue('EXPORT_COLUMNS'));
            // XLS Plik
            $fileXLSColor       = Tools::getValue('FILE_XLS_COLOR');
            $fileXLSLogo        = (int) Tools::getValue('FILE_XLS_LOGO');
            $fileXLSImages      = (int) Tools::getValue('FILE_XLS_IMAGES');

            
            // Zapisz dane EXPORT Form && XLS File
            MKDInstallOptionCMSPageExportProduct::setDefaultFormExportConfig('update', $exportTimeMarker, $exportColumns, $exportEnable, $exportFolder, $exportFilePrefix, $exportFormat, $exportImgSize, $exportImgCover, $fileXLSColor, $fileXLSLogo, $fileXLSImages);

            Configuration::updateValue('EXPORT_TIMEMARKER', $exportTimeMarker, false, Context::getContext()->shop->id_shop_group,           Context::getContext()->shop->id);

            // 3. Pobierz dane PREMIUM Form
            // if (!empty(MKDInstallOptionCMSPageExportProduct::getModuleMode())) {

                $cmsLock = (int) Tools::getValue('CMS_PAGE_ACCESS_LOCK');
                
                
                $productCombinationsEnable  = (int) Tools::getValue('EXPORT_PRODUCT_COMBINATIONS');
                // Counters
                $attachmentDownloadCounter  = (int) Tools::getValue('ATTACHMENT_DOWNLOAD_COUNTER');
                $attachmentViewsCounter     = (int) Tools::getValue('ATTACHMENT_VIEWS_COUNTER');
                $exportDownloadCounter      = (int) Tools::getValue('EXPORT_DOWNLOAD_COUNTER');

                // Zapisz dane EXPORT Form
                MKDInstallOptionCMSPageExportProduct::setPremiumCMSPageExportFormConfig(true, 'update', $cmsLock, $productCombinationsEnable, $attachmentDownloadCounter, $attachmentViewsCounter, $exportDownloadCounter);


                // ======================================
                // Extra Fields => Tabele => Modele => multi-Lang
                // ======================================
                $languages = Language::getLanguages();

                // Załaduj Modele Extra Fields Poduct
                require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

                $extraFieldsModel     = new MKDExtraProductFieldsModel();
                $extraFieldsShopModel = new MKDExtraProductFieldsShopModel();
                $extraFieldsLangModel = new MKDExtraProductFieldsLangModel();

                // Iteruj przez wszystkie Input's extra field
                for ($i = 1; $i <= (int)MKDInstallOptionCMSPageExportProduct::getConfig('numberExtaProductFields'); $i++) {

                    // 1. Pobierz dane z formularza
                    $fieldFrontView = (int) Tools::getValue('PRODUCT_EXTRA_FIELD_FRONT_VIEW_'. $i, 0); // domyślnie 0
                    $fieldHTML      = (int) Tools::getValue('PRODUCT_EXTRA_FIELD_HTMLCONTENT_'. $i, 0); // domyślnie 0
                    $fieldActive    = (int) Tools::getValue('PRODUCT_EXTRA_FIELD_ACTIVE_'. $i, 0); // domyślnie 0
                    $fieldFrontHook = (int) Tools::getValue('PRODUCT_EXTRA_FIELD_FRONT_HOOK_'. $i, 1); // domyślnie 1

                    // 2. Ustaw dane w podstawowym modelu
                    $extraFieldsModel->front_view   = $fieldFrontView;
                    $extraFieldsModel->html_content = $fieldHTML;
                    $extraFieldsModel->active       = $fieldActive;

                    // // 3.0 Pobierz position dla danego _shop
                    // $maxPosition = (int) Db::getInstance()->getValue('SELECT MAX(`position`)
                    // FROM `' . _DB_PREFIX_ . MKDExtraProductFieldsShopModel::$definition['table'] . '`
                    // WHERE `id_shop` = ' . (int)$shop_id);
                    // // Dodaj dane z ostatnią pozycją + 1
                    // $position = $maxPosition + 1;

                    // 3. Ustaw dane w modelu fieldShop
                    $extraFieldsShopModel->id_shop   = $shop_id;
                    $extraFieldsShopModel->field_id  = $i;
                    $extraFieldsShopModel->hook_id   = $fieldFrontHook;
                    $extraFieldsShopModel->position  = $i;

                    
                    // 3. Sprawdź, czy extra field ID już istnieje na podstawie Input number
                    $existingID = $extraFieldsModel->getExtraFieldId($i);
                    
                    if (!$existingID) {
                        
                        $extraFieldsModel->add();
                        $extraFieldsShopModel->add();

                    } else {

                        $extraFieldsModel->id = $existingID; // Ustaw ID rekordu do aktualizacji
                        $extraFieldsModel->update();

                        $extraFieldsShopModel->id = $existingID;
                        $extraFieldsShopModel->update();
                    }
                    
                    // 4. Iteruj przez języki Title extra field

                    $langTitleNotEmpty = []; // Pierwszy tytuł => zostanie użyty do pozostałych pustych tytułów

                    foreach ($languages as $language) {

                        $title = (!Validate::isGenericName('PRODUCT_EXTRA_FIELD_TITLE_' . $i . '_LANG_' . $language['id_lang']))
                                    ? ''
                                    : Tools::getValue('PRODUCT_EXTRA_FIELD_TITLE_' . $i . '_LANG_' . $language['id_lang']);
                                        

                        // Jeśli znajdziemy pierwszy tytuł, zastąp nim puste wersje
                        if (!empty($title) && empty($langTitleNotEmpty)) {
                            $langTitleNotEmpty = [
                                'title' => $title,
                                'lang' => $language['id_lang']
                            ];
                        }

                        // Jeśli aktualny tytuł jest pusty => zastąp go pierwszym tytułem (PL)
                        if (empty($title) && !empty($langTitleNotEmpty)) {
                            $title = '(' . strtoupper(Language::getIsoById($langTitleNotEmpty['lang'])) .') ' . $langTitleNotEmpty['title'];
                        }
                        
                        // 5. Ustaw dane w modelu LangModel

                        $extraFieldsLangModel->id_lang  = $language['id_lang'];
                        $extraFieldsLangModel->field_id = $i;
                        $extraFieldsLangModel->title    = $title;
                        $extraFieldsLangModel->data_upd = $currentDateTime;

                        // Sprawdź, czy extra field ID już istnieje
                        if (!$existingID) {
                            
                            $extraFieldsLangModel->add();

                        } else {

                            $result = MKDExtraProductFieldsLangModel::updateTitleByFieldId($title, $i, $language['id_lang'], $currentDateTime);

                            if ($result) {
                                // Update sukces
                                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('Dodatkowe pola zostały pomyślnie zaktualizowane.');
                            } else {
                                // Update failed
                                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('Zapisywanie nie powiodło się. Sprawdź formularz dodtakowych pól produktu');
                            }

                        }                         
                           
                    }

                }


            // }
            

        }

        // ============================
        // Przywróć domyślne ustawienia
        // ============================
        if (Tools::getValue('cms_export_restore') && Tools::getValue('cms_export_restore') == 1) {
            
            // PREMIUM =================
            MKDInstallOptionCMSPageExportProduct::setPremiumCMSPageExportFormConfig(false, 'upd$this->getConfigate');
            // =========================

            // Przywróć CMS Page Form => $action = 'update'...
            MKDInstallOptionCMSPageExportProduct::setDefaultFormCMSPageConfig();

            // Przywróć Export Form => $action = 'update'..., false = $this->setDefaultExportProductColumns()
            $exportTimeMarker = (int) time();
            // MKDInstallOptionCMSPageExportProduct::setDefaultFormExportConfig('update', $exportTimeMarker);
            Configuration::updateValue('EXPORT_TIMEMARKER', $exportTimeMarker, false, Context::getContext()->shop->id_shop_group,           Context::getContext()->shop->id);

            // =======================================================================
            // Załaduj Model Extra Fields Poduct => wyłącznie Extra Fields (Active = 0)
            // =======================================================================
            require_once(_PS_MODULE_DIR_ . _MKD_NAME_ . '/models/MKDExtraProductFieldsModel.php');

            $extraFieldsModel = new MKDExtraProductFieldsModel();
            // Iteruj przez wszystkie Input's extra field
            for ($i = 1; $i <= (int)MKDInstallOptionCMSPageExportProduct::getConfig('numberExtaProductFields'); $i++) {

                // Sprawdź, czy extra field ID istnieje na podstawie Input number
                $existingID = $extraFieldsModel->getExtraFieldId($i);

                if ($existingID) {

                    $extraFieldsModel->id = $existingID; // Ustaw ID rekordu do aktualizacji
                    $extraFieldsModel->active = 0; // Ustaw active na 0

                    $extraFieldsModel->update();
                }
            
            }
            // =========================================================================


            // Przekieruj po wykonaniu akcji przywracania
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        }

    }

}