<?php
/*
* Renderowanie Tabeli i Formularza dla Typów załączników w module
*/


class AdminModuleConfigFormController {

    public function __construct() {
        
        // Ustawienia pomocnicze dla HelperForm z modułu
        $this->name = _MKD_NAME_;
        $this->displayName = _MKD_MODULE_NAME_;
        $this->identifier = _MKD_CONTROLLER_IDENTIFIER_;
        // Dla renderTable() ustawić component w URL
        $this->component = _MKD_MODULE_COMPONENT_[0];

    }
    
    // ========================================================
    // FORMULARZ GRUP TYPÓW ===================================
    // ========================================================
    public function renderTypesForm($typeAttach = false)
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        // Ustawienia pomocnicze
        $helper = $this->createHelperForm($defaultLang);

        // Pola formularza
        $fieldsForm = $this->createFieldsForm($typeAttach);

        // Ustaw dane w formularzu    
        $typeAttachValues = $this->prepareFormValues($typeAttach);
        
        $helper->fields_value = $typeAttachValues;
        

        return $helper->generateForm(array($fieldsForm));
    }

    private function createHelperForm($defaultLang)
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&id_shop='.(int)Context::getContext()->shop->id;
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
        $helper->submit_action = 'submit'.$this->name;
        $helper->show_errors = true;

        return $helper;
    }

    private function createFieldsForm($typeAttach)
    {
        // Ustaw początkowe wartości pól:
        $shopOptions            = $this->getDefaultShops();
        $hookOptions            = $this->getDefaultHooks();
        $formatOptions          = $this->getDefaultFormats();
        $groupOptions           = $this->getDefaultUserGroups();
        $manufacturerOptions    = $this->getDefaultManufacturers();
        $programOptions         = $this->getDefaultPrograms();
        // ==========================================================

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => Context::getContext()->getTranslator()->trans('Edytuj GRUPĘ załączników dla określonych użytkowników według marki i kategorii'),
                    'icon' => 'icon-cogs',
                ),
            ),
        );

        // 1. Wybór Sklepu (ukryty w trybie edycji)
        // $fieldsForm['form']['input'][] = array(
        //     'type' => (Tools::getIsset('update' . _MKD_MODULE_COMPONENT_[0]) && (int)Tools::getValue('id') > 0) ? 'hidden' : 'select',
        //     'label' => Context::getContext()->getTranslator()->trans('Select Store:'),
        //     'name' => 'id_shop',
        //     'required' => true,
        //     'desc' => Context::getContext()->getTranslator()->trans('Select the store for which the group will be assigned <span class="alert-danger">[You can\'t change the selected store in Edit Mode]</span>'),
        //     'options' => array(
        //         'query' => $shopOptions,
        //         'id' => 'id',
        //         'name' => 'name'
        //     )
        // );

        // 2. Pole do nazwy typu załącznika
        $fieldsForm['form']['input'][] = array(
            'type' => 'text',
            'label' => Context::getContext()->getTranslator()->trans('Nazwa grupy/kategorii załączników') . ':',
            'name' => 'title',
            'required' => true,
            'lang' => true,
            'placeholder' => Context::getContext()->getTranslator()->trans('Instrukcja, Delkaracja...'),
            'desc' => Context::getContext()->getTranslator()->trans('Ogólna nazwa grupy załączników [Katalog, Instrukcja, Deklaracja, Reklamacje, Zwroty, Formularz itp.]'),
        );
        
        // 3. Pole tekstowe dla opisu załączników
        $fieldsForm['form']['input'][] = array(
            'type' => 'textarea',
            'label' => Context::getContext()->getTranslator()->trans('Opis [opcjonalnie]'),
            'name' => 'description',
            'autoload_rte' => true,
            'lang' => true,
            'desc' => Context::getContext()->getTranslator()->trans('Wprowadź krótki opis grupy/kategorii załączników'),
        );

        // 4. Wyboru formtatu załącznika
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Format') .':',
            'name' => 'format',
            'required' => true,
            'class' => 'fixed-width-xxl',
            'options' => array(
                'query' => $formatOptions,
                'id' => 'id',
                'name' => 'name',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz typ załączników'),
        );

        // // 5. Wybór dostępnych hook'ów
        // $fieldsForm['form']['input'][] = array(
        //     'type' => 'radio',
        //     'label' => Context::getContext()->getTranslator()->trans('Show in:'),
        //     'name' => 'hook',
        //     'required' => true,
        //     'values' => $hookOptions,
        //     'desc' => Context::getContext()->getTranslator()->trans('Select the hook to associate the attachment with'),
        // );

        // 6. Wybór grupy użytkowników
        $fieldsForm['form']['input'][] = array(
            'type' => 'checkbox',
            'label' => Context::getContext()->getTranslator()->trans('Grupy Użytkowników') .':',
            'name' => 'user_groups[]', // Tablica, aby umożliwić wybór wielu
            'required' => true,
            'class' => 'custom',
            'values' => array(
                'query' => $groupOptions,
                'id' => 'id',
                'name' => 'name',
                'value' => 'val'        
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz grupy użytkowników, dla których będzie to widoczne'),
        );

        // 7. Checkbox'y dla Marek
        $fieldsForm['form']['input'][] = array(
            'type' => 'checkbox',
            'label' => Context::getContext()->getTranslator()->trans('Producenci/Marka') .':',
            'name' => 'product_brands[]', // Tablica, aby umożliwić wybór wielu
            'values' => array(
                'query' => $manufacturerOptions,
                'id' => 'id',
                'name' => 'name',
                'value' => 'val'
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz producentów, z którymi chcesz powiązać załączniki'),
        );

        // 8. Checkbox'y dla Kategorii produktu => do edycji => zaznaczone kategorie
        if ($typeAttach > 0) {
            $editTypeId = MKDTypesModel::getTypesById($typeAttach);
            $selectCat = MKDTypesLangModel::getTypesLangByTypeId($editTypeId['id']);
            
            // Tablica dla zapisanych kategorii
            $savedCategories = [];
        
            // dodanie zapisanych kategorii do tablicy
            foreach ($selectCat as $cat) {
                $productCategories = unserialize($cat['product_categories']);
                if (is_array($productCategories)) {
                    $savedCategories = array_merge($savedCategories, $productCategories);
                }
            }
        
            // Usunięcie duplikatów z zapisanych kategorii
            $savedCategories = array_unique($savedCategories);

        } else {
            $savedCategories = [];
        }
        
        
        // Pobierz podkategorie kategorii o ID 3
        $subCategories = Category::getChildren(3, Context::getContext()->shop->id);
        
        // Zainicjuj tablicę dla wybranych kategorii
        $selectedCategories = $savedCategories; // Ustaw zapisane kategorie jako wybrane
        

        $fieldsForm['form']['input'][] = array(
            'type' => 'categories',
            'label' => Context::getContext()->getTranslator()->trans('Kategorie') . ':',
            'name' => 'product_categories',
            'tree' => array(
                'id' => 'categories-tree',
                'use_search' => false,
                'root_category' => 3, // Ustaw root_category
                'use_checkbox' => true,
                'use_context' => true,
                'expand_all' => true,
                'selected_categories' => $selectedCategories,
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz dostępne kategorie produktów, w jakich będą wyświetlane załączniki'),
        );


        // 9. Wybór Programów [Dotacje]
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Odnoszą się do Programu [opcjonalnie]'),
            'name' => 'program',
            'class' => 'fixed-width-xxl',
            'lang' => true,
            'options' => array(
                'query' => $programOptions,
                'id' => 'id',
                'name' => 'name',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz dowolny program [dotacja rządowa, dotacja itp.] według kraju lub'). ' <a href="' . Context::getContext()->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&add_program">'.Context::getContext()->getTranslator()->trans('dodaj nowy Program').'</a>',
        );

        // 10. Przełącznik na URL
        $fieldsForm['form']['input'][] = array(
            'type' => 'switch',
            'label' => Context::getContext()->getTranslator()->trans('Zewnętrzny adres URL') .':',
            'name' => 'extra_url',
            'is_bool' => true,
            'lang' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => Context::getContext()->getTranslator()->trans('Tak'),
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => Context::getContext()->getTranslator()->trans('Nie'),
                ),
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Będą to linki do innej strony internetowej, np: GoogleDrive'),
        );

        // 11. Przełącznik na Status
        $fieldsForm['form']['input'][] = array(
            'type' => 'switch',
            'label' => Context::getContext()->getTranslator()->trans('Status') .':',
            'name' => 'active',
            'is_bool' => true,
            'lang' => true,
            'values' => array(
                array(
                    'id' => 'status_on',
                    'value' => 1,
                    'label' => Context::getContext()->getTranslator()->trans('Włączona'),
                ),
                array(
                    'id' => 'status_off',
                    'value' => 0,
                    'label' => Context::getContext()->getTranslator()->trans('Wyłączona'),
                ),
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Aktywny lub Nieaktywny status załączników'),
        );

        // Przyciski Akcji
        $fieldsForm['form']['buttons'] = array(
            'save' => array(
                'title' => Context::getContext()->getTranslator()->trans('Zapisz Grupę'),
                'name' => 'saveType',
                'type' => 'submit',
                'class' => 'pull-right',
            ),
            'cancel' => array(
                'title' => Context::getContext()->getTranslator()->trans('Anuluj'),
                'href' => Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
        );
        
        // === Do edycji rekordów (...&update...component...) wymagane ID, Position i Button Update ===
        if ((bool)Tools::getIsset('update' . _MKD_MODULE_COMPONENT_[0]) && (int)Tools::getValue('id') > 0) {
                       
            $fieldsForm['form']['input'][] = array(
                'type' => 'hidden',
                'name' => 'id',
            );
            $fieldsForm['form']['input'][] = array(
                'type' => 'hidden',
                'name' => 'position',
            );
            $fieldsForm['form']['buttons'] = array(
                'update' => array(
                    'title' => Context::getContext()->getTranslator()->trans('Zaktualizuj Grupę'),
                    'name' => 'updateType',
                    'type' => 'submit',
                    'class' => 'pull-right',
                ),
                'cancel' => array(
                    'title' => Context::getContext()->getTranslator()->trans('Cancel'),
                    'href' => Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            );
        }

        return $fieldsForm;
    }

    private function prepareFormValues($typeAttach)
    {      
        // Pobierz dane dla pól:
        $groupOptions           = $this->getDefaultUserGroups();
        $manufacturerOptions    = $this->getDefaultManufacturers();
        // ==========================================================

        $typeAttachValues = array();

        // Do edycji wymagany jest ID
        if ($typeAttach > 0) {

            $typeAttach     = MKDTypesModel::getTypesById($typeAttach);
            
            $typeLangAttach = MKDTypesLangModel::getTypesLangByTypeId($typeAttach['id']);
            
            $typeShopAttach = MKDTypesShopModel::getTypesShopByTypeId($typeAttach['id']);
            
        } else {
            $typeLangAttach = MKDTypesLangModel::getTypesLangByTypeId();
        }
        // =========================

        // 0. Sprawdź czy jest ID do Edycji rekordów
        $typeAttachValues['id'] = isset($typeAttach['id']) ? $typeAttach['id'] : '';

        // 1. Ustaw wartość dla Sklepu
        $typeAttachValues['id_shop'] = isset($typeShopAttach['id_shop']) ? $typeShopAttach['id_shop'] : Configuration::get('PS_SHOP_DEFAULT');
        
        // 2-3. Ustaw Tytuł i Opis => multi-lang
        $languages = Language::getLanguages();
        
        $typeLangAttachByIdLang = array();
        

        foreach ($typeLangAttach as $langData) {
            $typeLangAttach[$langData['id_lang']] = $langData;
        }

        $typeAttachValues['title'] = array();
        $typeAttachValues['description'] = array();

        foreach ($languages as $language) {
            $langId = (int) $language['id_lang'];
            
            // Użyj uprzednio przygotowanej tablicy $typeLangAttachByIdLang
            $typeAttachValues['title'][$langId] = isset($typeLangAttach[$langId]['title']) ? $typeLangAttach[$langId]['title'] : '';
            $typeAttachValues['description'][$langId] = isset($typeLangAttach[$langId]['description']) ? $typeLangAttach[$langId]['description'] : '';
        }

        // 4. Ustaw wartość Typu załącznika
        $typeAttachValues['format'] = isset($typeLangAttach[0]['format']) ? $typeLangAttach[0]['format'] : '1';

        // 5. Ustaw wartość hook'a
        $typeAttachValues['hook'] = isset($typeLangAttach[0]['hook']) ? $typeLangAttach[0]['hook'] : '1';

        // 6. Ustaw wartości dla Grup użytkowników
        if (!empty($typeLangAttach[0]['user_groups'])) {
            $serializedGroups = $typeLangAttach[0]['user_groups'];
            $groupsArray = unserialize($serializedGroups);
        
            if (is_array($groupsArray)) {
                foreach ($groupOptions as $groupOption) {
                    $groupId = $groupOption['id'];
                    $typeAttachValues['user_groups[]_' . $groupId] = in_array($groupId, $groupsArray);
                }
            }
        }
        
        // 7. Ustaw wartości dla Marek/Producentów
        if (!empty($typeLangAttach[0]['product_brands'])) {
            $serializedBrands = $typeLangAttach[0]['product_brands'];
            $brandsArray = unserialize($serializedBrands);
        
            if (is_array($brandsArray)) {
                foreach ($manufacturerOptions as $manufacturerOption) {
                    $manufacturerId = $manufacturerOption['id'];
                    $typeAttachValues['product_brands[]_' . $manufacturerId] = in_array($manufacturerId, $brandsArray);
                }
            }
        }

        // 8. Ustaw wartości dla Kategorii produktów 
        // Sprawdzamy to bezpośrednio w 'categories-tree'...
        
        // 9. Ustaw wartości dla Programów
        $typeAttachValues['program'] = isset($typeLangAttach[0]['program']) ? $typeLangAttach[0]['program'] : '';

        // 10. Ustaw wartość dla extra url
        $typeAttachValues['extra_url'] = isset($typeLangAttach[0]['extra_url']) ? $typeLangAttach[0]['extra_url'] : '0';

        // 11. Ustaw Status
        $typeAttachValues['active'] = isset($typeAttach['active']) ? $typeAttach['active'] : '1';

        // 12. Ustaw Position dla edycji (hidden)
        $typeAttachValues['position'] = isset($typeShopAttach['position']) ? $typeShopAttach['position'] : '';

        return $typeAttachValues;
    }


    private function getDefaultShops() {
        $shops = Shop::getShops();
        $shopOptions = array();

        foreach ($shops as $shop) {
            $shopOptions[] = array(
                'id' => $shop['id_shop'],
                'name' => $shop['name'] . ($shop['id_shop'] == Configuration::get('PS_SHOP_DEFAULT') ? ' [--'.Context::getContext()->getTranslator()->trans('Default').'--]' : ''),
            );
        }
        return $shopOptions;
    }

    private function getDefaultFormats()
    {
        $formatsType = $this->getModuleDefaultData(pSQL(_MKD_NAME_) . '_formats');

        $formatOptions = array();
        foreach ($formatsType as $type) {
            $formatOptions[] = array(
                'id' => $type['id'],
                'name' => $type['name'], // Wartość w 'select'
            );
        }
        return $formatOptions;
    }

    private function getDefaultHooks()
    {
        $hooks = $this->getModuleDefaultData(pSQL(_MKD_NAME_) . '_hooks');

        $hookOptions = array();
        foreach ($hooks as $hook) {
            $hookOptions[] = array(
                'id' => $hook['id'], // Ustaw id
                'value' => $hook['id'], // Wartość w 'radio'
                'label' => '<span>' . $hook['name'] .'</span><legend>' . $hook['value'] . '</legend>'
            );
        }
        
        return $hookOptions;
    }

    private function getDefaultUserGroups()
    {
        $groupOptions = array();
        $groupIds = array(); // Tablica do przechowywania unikalnych id grup użytkowników

        // Pobierz grupy użytkowników dla aktualnego języka
        $groups = Group::getGroups(Context::getContext()->language->id);

        foreach ($groups as $group) {
            // Sprawdź, czy grupa już nie jest dodana do tablicy
            if (!in_array($group['id_group'], $groupIds)) {
                $groupOptions[] = array(
                    'id' => $group['id_group'], // Ustaw id
                    'name' => $group['name'],
                    'val' => $group['id_group'] // Ustaw value ('val' = !!!)
                );
                $groupIds[] = $group['id_group']; // Dodaj id grupy użytkownika do tablicy
            }
        }
        
        return $groupOptions;
    }

    private function getDefaultManufacturers()
    {
        $manufacturerOptions = array();
        $context = Context::getContext();
        $id_shop = $context->shop->id;

        // Pobierz producentów dla aktualnego sklepu
        $manufacturers = Manufacturer::getManufacturers(false, $id_shop);

        $manufacturerIds = array(); // Tablica do przechowywania unikalnych id producentów

        foreach ($manufacturers as $manufacturer) {
            // Sprawdź, czy producent już nie jest dodany do tablicy
            if (!in_array($manufacturer['id_manufacturer'], $manufacturerIds)) {
                $manufacturerOptions[] = array(
                    'id' => $manufacturer['id_manufacturer'], // Ustaw id
                    'name' => $manufacturer['name'],
                    'val' => $manufacturer['id_manufacturer'] // Ustaw value ('val' = !!!)
                );
                $manufacturerIds[] = $manufacturer['id_manufacturer']; // Dodaj id producenta do tablicy
            }
        }
        
        return $manufacturerOptions;
    }

    private function getDefaultPrograms()
    {
        $dataPrograms = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('sp.id, sp.country_id, sp.name, sp.version, c.iso_code')
                ->from(pSQL(_MKD_NAME_) . '_programs', 'sp')
                ->leftJoin('country', 'c', 'c.id_country = sp.country_id')
                ->where('sp.active = 1')
        );
        
        $programOptions = array();

        // Dodaj pierwszy element jako pustą opcję
        $programOptions[0] = array('id' => 0, 'name' => '---------');
        
        foreach ($dataPrograms as $program) {
            $programOptions[] = array(
                'id' => $program['id'],
                'name' => $program['iso_code'] . ' : &#171;	'. $program['name'] . (!empty($program['version']) ? ' &#187; [ver.' . $program['version'] .']' : '"')
            );
        }
        return $programOptions;
    }
    // Pobierz wgrane dane z formats & hooks
    private function getModuleDefaultData($tableName)
    {
        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . pSQL($tableName) . '`');
    }


    // ========================================================
    // TABELA GRUP TYPÓW ======================================
    // ========================================================
    public function renderTypesTable()
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        // Ustawienia pomocnicze
        $helper = $this->createHelperTable();

        // Header Tabeli
        $fields_list = $this->createTableColumns();

        // Ustaw dane w tabeli    
        $typeAttachList = $this->prepareTableValues();

        $helper->listTotal = count($typeAttachList);
        $helper->tpl_vars = array(
            'fields_list' => $fields_list,
            'list' => $typeAttachList,
            'title' => Context::getContext()->getTranslator()->trans('Grupy/Kategorie Załączników'),
            'token' => Tools::getAdminTokenLite('AdminModules'),
        );    

        $output = $helper->generateList($typeAttachList, $fields_list);

        return $output;
    }

    private function createHelperTable()
    {
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id';
        $helper->no_link = true;
        // Dodawane do URL actions (!!!): update..component, delete..component
        $helper->table = $this->component;
        // ==============================
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->title = $this->displayName;
        $helper->toolbar_scroll = true;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex
                .'&configure='.$this->name.'&add_group_type'.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => Context::getContext()->getTranslator()->trans('Add New Group of attachments')
        );
        $helper->currentIndex = AdminController::$currentIndex
                            .'&configure='.$this->name
                            .'&id_shop='.(int)Context::getContext()->shop->id;

        return $helper;
    }

    private function createTableColumns()
    {
        $fields_list = array(
            'lp' => array(
                'title' => Context::getContext()->getTranslator()->trans('LP'),
                'type' => 'text',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'search' => false,
            ),
            'shop' => array(
                'title' => Context::getContext()->getTranslator()->trans('Sklep'),
                'type' => 'text',
                'class' => 'fixed-width-md',
                'orderby' => false,
                'search' => false,
            ),
            'files' => array(
                'title' => Context::getContext()->getTranslator()->trans('Plików'),
                'type' => 'text',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'search' => false,
            ),
            'title' => array(
                'title' => Context::getContext()->getTranslator()->trans('Nazwa'),
                'type' => 'text',
                'class' => 'fixed-width-xxl',
                'orderby' => false,
                'search' => false,
            ),
            'format' => array(
                'title' => Context::getContext()->getTranslator()->trans('Format'),
                'type' => 'text',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'search' => false,
            ),
            // 'hook' => array(
            //     'title' => Context::getContext()->getTranslator()->trans('Block'),
            //     'type' => 'text',
            //     'class' => 'fixed-width-md',
            //     'orderby' => false,
            //     'search' => false,
            // ),
            'user_groups' => array(
                'title' => Context::getContext()->getTranslator()->trans('Użytkownicy'),
                'type' => 'text',
                'class' => 'fixed-width-xxl',
                'orderby' => false,
                'search' => false,
            ),
            // 'product_brands' => array(
            //     'title' => Context::getContext()->getTranslator()->trans('Brands'),
            //     'type' => 'text',
            //     'class' => 'fixed-width-md',
            //     'orderby' => false,
            //     'search' => false,
            // ),
            // 'extra_url' => array(
            //     'title' => Context::getContext()->getTranslator()->trans('URL'),
            //     'type' => 'bool',
            //     'align' => 'text-center',
            //     'class' => 'fixed-width-sm',
            //     'orderby' => false,
            //     'search' => false,
            // ),
            'program' => array(
                'title' => Context::getContext()->getTranslator()->trans('Program'),
                'type' => 'text',
                'orderby' => false,
                'class' => 'fixed-width-md',
                'search' => false,
            ),
            'position' => array(
                'title' => Context::getContext()->getTranslator()->trans('Pozycja'),
                'type' => 'position',
                'align' => 'text-center',
                'class' => 'fixed-width-sx',
                'orderby' => false,
                'search' => false,
            ),
            'active' => array(
                'title' => Context::getContext()->getTranslator()->trans('Actywna'),
                'type' => 'bool',
                'align' => 'text-center',
                'class' => 'fixed-width-sx',
                'active' => 'status', // On/Off Status
                'orderby' => false,
                'search' => false,
            ),
            'id' => array(
                'title' => Context::getContext()->getTranslator()->trans('ID'),
                'type' => 'text',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'search' => false,
            ),
        );

        return $fields_list;
    }

    private function prepareTableValues()
    {
        // Załaduj Manager
        require_once(_PS_MODULE_DIR_. _MKD_NAME_ .'/classes/MKDTypesGroupManager.php');

        $typeAttachList = MKDTypesGroupManager::getTypeAttachList(
            (int) Context::getContext()->language->id,
            Tools::getValue('AdminModuleConfigFormController::table'.'Orderby', 'id_shop'), // Sortwanie według
            Tools::getValue('AdminModuleConfigFormController::table'.'Orderway', 'ASC'),
            (int) Context::getContext()->employee->id,
            (int) Context::getContext()->shop->id
        );

        $counter = 1;

        foreach ($typeAttachList as &$typeAttach) {
            
            //  Przypisz wartość licznika jako Numer
            $typeAttach['lp'] = $counter;

            // Pobierz listę Sklepów
            $typeAttach['shop'] = $typeAttach['shop_name'];

            // Pobierz ilość dodanych Plików w danej Grupie dla Shop
            $typeAttach['files'] = $typeAttach['files'];

            // Pobierz nazwę typu na podstawie 'type_id'
            $typeAttach['format'] = strtoupper($typeAttach['format_name']);

            // Pobierz nazwę hooku na podstawie 'hook_id'
            $typeAttach['hook'] = $typeAttach['hook_name'];

            // Odczyt Grup
            $groupIds = unserialize($typeAttach['user_groups']);
                $groupNames = array();

                // Przetwórz ID grup na ich nazwy
                foreach ($groupIds as $groupId) {
                    $group = new Group($groupId, Context::getContext()->language->id);
                    $groupNames[] = $group->name;
                }
            // Wyświetl nazwy grup
            $typeAttach['user_groups'] = implode(', ', $groupNames);

            // // Odczyt producentów
            // $brandIds = unserialize($typeAttach['product_brands']);
            //     $brandNames = array();

            //     foreach ($brandIds as $brandId) {
            //         $brand = new Manufacturer($brandId, Context::getContext()->language->id);
            //         $brandNames[] = $brand->name;
            //     }
            // // Wyświetl nazwy Marek
            // $typeAttach['product_brands'] = implode(', ', $brandNames);

            
            // Pobierz kraj, nazwę i wersję Programu na podstawie 'id' jeśli > 0
            $typeAttach['program'] = $typeAttach['program'] > 0
                    ? $typeAttach['country_iso_code'] .' | "' . $typeAttach['program_name']
                    . (isset($typeAttach['version']) ? '" - ver.' .$typeAttach['version'] : '')
                    : '------';

            // Zwiększ licznik
            $counter++;

        }

        return $typeAttachList;
    }

    // ==================================================================
    // Obsługa zapisu, aktualizacji, usuwania i toogleStatus ============
    // ==================================================================
    public function postProcess()
    {

        // Pobierz dane z formularza
        $id                     = (int) Tools::getValue('id', '');
        $id_shop                = (int) Context::getContext()->shop->id; // Aktualny sklep [wyłączono pole w form]
        $format                 = (int) Tools::getValue('format');
        $hook                   = (int) Tools::getValue('hook', 1);
        $user_groups            = serialize(Tools::getValue('user_groups'));
        $product_brands         = serialize(Tools::getValue('product_brands'));
        $product_categories     = serialize(Tools::getValue('product_categories'));
        $extra_url              = (int) Tools::getValue('extra_url');
        $program                = (int) Tools::getValue('program');
        $active                 = (int) Tools::getValue('active');
        $position               = (int) Tools::getValue('position');

        
        // Załaduj Modele
        require_once(_PS_MODULE_DIR_. _MKD_NAME_ .'/models/MKDTypesModel.php');
        
        $typesModel      = new MKDTypesModel();
        $typesLangModel  = new MKDTypesLangModel();
        $typesShopModel  = new MKDTypesShopModel();


        if (Tools::isSubmit('saveType')) {

            $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

            // Sprawdź default Title
            if (empty(pSQL(Tools::getValue('title_' . $defaultLang)))) {
                // Pole 'title' jest puste, dodaj odpowiedni komunikat o błędzie
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('The Group Name/Title field is required');

            }
            
            // Sprawdź wymagane UserGroup
            $userGroupsArray = unserialize($user_groups);
            if (empty($userGroupsArray) || !is_array($userGroupsArray)) {
                // Pole 'usergroup' jest puste, dodaj odpowiedni komunikat o błędzie
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('The User Group field is required');
            }

            // Sprawdź Format
            if (empty($format)) {
                // Pole 'format' jest puste, dodaj odpowiedni komunikat o błędzie
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('The Format field is required');
            }


            if (!count(Context::getContext()->controller->errors)) {

                // 1. Dodaj (required) dane do _types
                $typesModel->active = $active;
                
                $typesModel->add();

                // Pobierz zapisane ID dla _lang i _shop
                $lastID = $typesModel->id;
                

                // 2.0 Pobierz position dla danego _shop
                $maxPosition = (int) Db::getInstance()->getValue('SELECT MAX(`position`)
                    FROM `' . _DB_PREFIX_ . MKDTypesShopModel::$definition['table'] . '`
                    WHERE `id_shop` = ' . (int)$id_shop);
                // Dodaj dane z ostatnią pozycją + 1
                $position = $maxPosition + 1;
                
                $typesShopModel->id_shop = $id_shop;
                $typesShopModel->type_id = $typesModel->id; // lub $lastID
                $typesShopModel->position = $position;
                
                $typesShopModel->add();

                // Wyczyszczenie cache'u
                $typesModel->clearCache();
                $typesShopModel->clearCache();


                // 3. Dodaj dane do Lang  
                $languages = Language::getLanguages();
                $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

                // Pozostałe dane z formularza dla Multi-lang
                foreach ($languages as $language) {
                    $langID         = (int) $language['id_lang'];
                    $iso_code       = strtoupper($language['iso_code']);

                    $title[$langID]         = Tools::getValue('title_' . $langID);
                    $description[$langID]   = Tools::getValue('description_' . $langID);


                    // Pole 'title' nie jest puste, sprawdź, czy istnieje wersja dla języka domyślnego
                    if ($langID !== $defaultLang && empty(pSQL(Tools::getValue('title_' . $langID)))) {
                        // Jeśli wersja domyślna nie jest pusta, przypisz wartość domyślną dla danego języka
                        $title[$langID] = $iso_code . ': ' . $title[$defaultLang];

                        Context::getContext()->controller->warning[] = Context::getContext()->getTranslator()->trans('Attention') .': ' . Context::getContext()->getTranslator()->trans('The Group Name/Title field for language') .' [<strong> '. $langID . '</strong> ] ' . Context::getContext()->getTranslator()->trans('is empty') . '. ' . Context::getContext()->getTranslator()->trans('The default lang versions were used.');
                    }
                    
                    // Pole 'title' nie jest puste, sprawdź, czy istnieje wersja dla języka domyślnego
                    if (!empty($description[$defaultLang]) && $langID !== $defaultLang && empty(pSQL(Tools::getValue('description_' . $langID)))) {
                        // Jeśli wersja domyślna nie jest pusta, przypisz wartość domyślną dla danego języka
                        $description[$langID] = $iso_code . ': ' . $description[$defaultLang];

                        Context::getContext()->controller->warning[] = Context::getContext()->getTranslator()->trans('Attention') .': ' . Context::getContext()->getTranslator()->trans('The Description field for language') .' [<strong> '. $langID . '</strong> ] ' . Context::getContext()->getTranslator()->trans('is empty') . '. ' . Context::getContext()->getTranslator()->trans('The default lang versions were used.');
                    }

                    // Ustaw dane dla LangModel
                    $typesLangModel->id_lang             = $langID;
                    $typesLangModel->type_id             = $lastID; // Użyj ID
                    $typesLangModel->format              = $format;
                    $typesLangModel->hook                = $hook;
                    $typesLangModel->user_groups         = $user_groups;
                    $typesLangModel->product_brands      = $product_brands;
                    $typesLangModel->product_categories  = $product_categories;
                    $typesLangModel->title               = $title[$langID];
                    $typesLangModel->description         = $description[$langID];
                    $typesLangModel->extra_url           = $extra_url;
                    $typesLangModel->program             = $program;

                    if (!count(Context::getContext()->controller->errors)) {
                        // 2. Zapisz dla LangModel
                        if ($typesLangModel->add()) {

                            $typesLangModel->clearCache();
                            
                            Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('New Group of attachments has been added.');
                            
                        } else {

                            Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('Save failed. Check the form');
                        }
                    }
                }
            }
        }

        if (Tools::isSubmit('updateType')) {
            // 1. Dodaj (required) dane do _types
            $typesModel->id = $id;
            $typesModel->active = $active;
        
            // Update Status w typesModel
            $typesModel->update();
                        
            // Pobierz dostępne języki
            $languages = Language::getLanguages();
            $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
            
            foreach ($languages as $language) {
                $langID     = (int) $language['id_lang'];
                $iso_code   = strtoupper($language['iso_code']);

                $title[$langID]         = Tools::getValue('title_' . $langID);
                $description[$langID]   = Tools::getValue('description_' . $langID);
                             
                // Pobierz istniejący rekord na podstawie type_id i id_lang
                $existingRecord = Db::getInstance()->getRow(
                    'SELECT * 
                        FROM `' . _DB_PREFIX_ . MKDTypesLangModel::$definition['table'] . '`
                        WHERE `type_id` = ' . (int) $id . ' AND `id_lang` = ' . (int) $langID
                );
                
                if ($existingRecord) {

                    // Sprawdź default Title
                    if (empty(pSQL(Tools::getValue('title_' . $defaultLang)))) {
                        // Pole 'title' jest puste, dodaj odpowiedni komunikat o błędzie
                        Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('The Group Name/Title field is required');

                    }

                    // Pole 'title' nie jest puste, sprawdź, czy istnieje wersja dla języka domyślnego
                    if ($langID !== $defaultLang && empty(pSQL(Tools::getValue('title_' . $langID)))) {
                        // Jeśli wersja domyślna nie jest pusta, przypisz wartość domyślną dla danego języka
                        $title[$langID] = $iso_code . ': ' . $title[$defaultLang];

                        Context::getContext()->controller->warning[] = Context::getContext()->getTranslator()->trans('Attention') .': ' . Context::getContext()->getTranslator()->trans('The Group Name/Title field for language') .' [<strong> '. $langID . '</strong> ] ' . Context::getContext()->getTranslator()->trans('is empty') . '. ' . Context::getContext()->getTranslator()->trans('The default lang versions were used.');
                    }
                    
                    // Pole 'title' nie jest puste, sprawdź, czy istnieje wersja dla języka domyślnego
                    if (!empty($description[$defaultLang]) && $langID !== $defaultLang && empty(pSQL(Tools::getValue('description_' . $langID)))) {
                        // Jeśli wersja domyślna nie jest pusta, przypisz wartość domyślną dla danego języka
                        $description[$langID] = $iso_code . ': ' . $description[$defaultLang];

                        Context::getContext()->controller->warning[] = Context::getContext()->getTranslator()->trans('Attention') .': ' . Context::getContext()->getTranslator()->trans('The Description field for language') .' [<strong> '. $langID . '</strong> ] ' . Context::getContext()->getTranslator()->trans('is empty') . '. ' . Context::getContext()->getTranslator()->trans('The default lang versions were used.');
                    }

                    // Jeśli rekord istnieje, zaktualizuj go
                    $typesLangModel->id = $existingRecord['id'];
                    
                    $typesLangModel->id_lang            = $langID;
                    $typesLangModel->type_id            = $id;
                    $typesLangModel->format             = $format;
                    $typesLangModel->hook               = $hook;
                    $typesLangModel->user_groups        = $user_groups;
                    $typesLangModel->product_brands     = $product_brands;
                    $typesLangModel->product_categories = $product_categories;
                    $typesLangModel->title              = $title[$langID];
                    $typesLangModel->description        = $description[$langID];
                    $typesLangModel->extra_url          = $extra_url;
                    $typesLangModel->program            = $program;

                    // Zaktualizuj dane w LangModelu na podstawie ID języka
                    if (!count(Context::getContext()->controller->errors) && $typesLangModel->update()) {

                        Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('Group was updated successfully.');
                        
                    }
                }
            }
            
        }
        
        if (Tools::isSubmit('delete' . _MKD_MODULE_COMPONENT_[0])) {
            
            $deleteId = (int) Tools::getValue('id');
            
            // Ustaw w modelu kasowane ID
            $typesModel->id = $deleteId;

            // Pobierz position i id_shop z rekordu, który zostanie usunięty
            $positionToDelete = Db::getInstance()->getRow('SELECT `position`, `id_shop`
                        FROM `' . _DB_PREFIX_ . MKDTypesShopModel::$definition['table'] . '`
                        WHERE `type_id` = ' . $deleteId);

            $idShop     = (int) $positionToDelete['id_shop'];
            $position   = (int) $positionToDelete['position'];

            
            
            // Wywołaj metodę delete na modelu
            if ($typesModel->delete($deleteId)) {
                
                // Zaktualizuj pozycje rekordów dla odpowiedniego id_shop
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . MKDTypesShopModel::$definition['table'] . '`
                SET `position` = `position` - 1
                WHERE `id_shop` = ' . $idShop . ' AND `position` > ' . $position);
                
        
                // Wyczyszczenie cache'u
                $typesModel->clearCache();
                $typesLangModel->clearCache();
                $typesShopModel->clearCache();
        
                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('The Group was deleted');
                // Przekierowanie na stronę modułu:
                // Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name);
                
            } else {
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('An error occurred while removing the Group');
            }
        }

        if (Tools::isSubmit('status' . _MKD_MODULE_COMPONENT_[0])) {

            $toggleId = (int) Tools::getValue('id');
            
            // Ustaw obiekt modelu na podstawie ID
            $record = new MKDTypesModel($toggleId);
            
            // Wywołaj metodę toggleStatus
            if ($record->toggleStatus('active')) {
                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('Status changed successfully.');
            } else {
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('An error occurred while toggling the status.');
            }
        }
        
    }

}