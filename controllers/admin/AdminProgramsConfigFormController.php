<?php
/*
* Renderowanie Tabeli i Formularza dla Programów
*/


class AdminProgramsConfigFormController {

    public function __construct() {
        
        // Ustawienia pomocnicze dla HelperForm z modułu
        $this->name = _MKD_NAME_;
        $this->displayName = _MKD_MODULE_NAME_;
        $this->identifier = _MKD_CONTROLLER_IDENTIFIER_;
        // Dla renderTable() ustawić component w URL
        $this->component = _MKD_MODULE_COMPONENT_[1];

    }
    
    // ========================================================
    // FORMULARZ PROGRAMÓW ====================================
    // ========================================================
    public function renderProgramsForm($program = false)
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        // Ustawienia pomocnicze
        $helper = $this->createHelperForm($defaultLang);

        // Pola formularza
        $fieldsForm = $this->createFieldsForm();

        // Ustaw podstawowe dane w formularzu    
        $programValues = $this->prepareFormValues($program);
        $helper->fields_value = $programValues;
        

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

    private function createFieldsForm()
    {
        // Ustaw początkowe wartości pól:
        $countriesOptions         = $this->getDefaultCountries();    
        // ==========================================================

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => Context::getContext()->getTranslator()->trans('Edytuj program wg Kraju'),
                    'icon' => 'icon-cogs',
                ),
            ),
        );

        // 1. Wybór kraju
        $fieldsForm['form']['input'][] = array(
            'type' => 'select',
            'label' => Context::getContext()->getTranslator()->trans('Kraj') .':',
            'name' => 'country_id',
            'required' => true,
            'class' => 'fixed-width-xxl',
            'options' => array(
                'query' => $countriesOptions,
                'id' => 'id',
                'name' => 'name',
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Wybierz kraj, którego dotyczy program z listy dostępnych krajów w sklepie'),
        );

        // 2. Pole Nazwy programu
        $fieldsForm['form']['input'][] = array(
            'type' => 'text',
            'label' => Context::getContext()->getTranslator()->trans('Nazwa programu') .':',
            'name' => 'name',
            'required' => true,
            'class' => 'fixed-width-xxl',
            'placeholder' => Context::getContext()->getTranslator()->trans('Eco, Fresh air...'),
            'desc' => Context::getContext()->getTranslator()->trans('Wpisz nazwę programu [Dotacja rządowa, dotacja itp.]'),
        );
        
        // 3. Pole Opis/Komentarz
        $fieldsForm['form']['input'][] = array(
            'type' => 'text',
            'label' => Context::getContext()->getTranslator()->trans('Komentarz') .':',
            'name' => 'comment',
            'desc' => Context::getContext()->getTranslator()->trans('Podaj krótki opis programu [max.500]'),
        );

        // 4. Pole Wersja programu
        $fieldsForm['form']['input'][] = array(
            'type' => 'text',
            'label' => Context::getContext()->getTranslator()->trans('Wersja:'),
            'name' => 'version',
            'class' => 'fixed-width-md',
            'placeholder' => '2.0., 3.0...',
            'desc' => Context::getContext()->getTranslator()->trans('W razie potrzeby wprowadź wersję programu'),
        );

         // 5. Wybór Daty
         $fieldsForm['form']['input'][] = array(
            'type' => 'date',
            'label' => Context::getContext()->getTranslator()->trans('Data zamknięcia') .':',
            'name' => 'valid_date',
            'class' => 'datepicker',
            'desc' => Context::getContext()->getTranslator()->trans('W razie potrzeby wpisz datę zakończenia programu'),
        );

        // 6. Przełącznik na Status
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
                    'label' => Context::getContext()->getTranslator()->trans('Włączony'),
                ),
                array(
                    'id' => 'status_off',
                    'value' => 0,
                    'label' => Context::getContext()->getTranslator()->trans('Wyłączony'),
                ),
            ),
            'desc' => Context::getContext()->getTranslator()->trans('Aktywny lub Nieaktywny status programu'),
        );

        // Przyciski Akcji
        $fieldsForm['form']['buttons'] = array(
            'save' => array(
                'title' => Context::getContext()->getTranslator()->trans('Zapisz program'),
                'name' => 'saveProgram',
                'type' => 'submit',
                'class' => 'pull-right',
            ),
            'cancel' => array(
                'title' => Context::getContext()->getTranslator()->trans('Anuluj'),
                'href' => Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
        );
        
        // === Do edycji rekordów (...&update...component...) wymagane ID, Position i Button Update ===
        if ((bool)Tools::getIsset('update' . _MKD_MODULE_COMPONENT_[1]) && (int)Tools::getValue('id') > 0) {            
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
                    'title' => Context::getContext()->getTranslator()->trans('Zaktualizuj program'),
                    'name' => 'updateProgram',
                    'type' => 'submit',
                    'class' => 'pull-right',
                ),
                'cancel' => array(
                    'title' => Context::getContext()->getTranslator()->trans('Anuluj'),
                    'href' => Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            );
        }

        return $fieldsForm;
    }

    private function prepareFormValues($program)
    {    

        $programValues = array();

        // Do edycji wymagany jest ID
        if ($program > 0) {
            $program = MKDProgramsModel::getProgramById($program);
        }
        // =========================

        // 0. Sprawdź czy jest ID do Edycji rekordów
        $programValues['id'] = isset($program['id']) ? $program['id'] : '';

        // 0.0. Ustaw Shop (domyślny ID)
        $programValues['shop_id'] = isset($program['shop_id']) ? $program['shop_id'] : Configuration::get('PS_SHOP_DEFAULT');
        
        // 0.0. Ustaw Lang (domyślny ID)
        $programValues['lang_id'] = isset($program['lang_id']) ? $program['lang_id'] : Configuration::get('PS_LANG_DEFAULT');
   
        // 1. Ustaw Kraj
        $programValues['country_id'] = isset($program['country_id']) ? $program['country_id'] : Configuration::get('PS_COUNTRY_DEFAULT');
        
        // 2. Ustaw Nazwę programu
        $programValues['name'] = isset($program['name']) ? $program['name'] : '';

        // 3. Ustaw Komentarz do programu
        $programValues['comment'] = isset($program['comment']) ? $program['comment'] : '';

        // 4. Ustaw wersję do programu
        $programValues['version'] = isset($program['version']) ? $program['version'] : '';

        // 5. Ustaw Datę
        $programValues['valid_date'] = isset($program['valid_date']) ? $program['valid_date'] : '';

        // 6. Ustaw Pozycję
        $programValues['position'] = isset($program['position']) ? $program['position'] : '';

        // 7. Ustaw Status
        $programValues['active'] = isset($program['active']) ? $program['active'] : '1';

        return $programValues;
    }

    private function getDefaultCountries()
    {
        $defaultCountryId = Configuration::get('PS_COUNTRY_DEFAULT'); // Pobierz ID domyślnego kraju
        $countries = Country::getCountries(Context::getContext()->language->id, true);
        $countryOptions = array();

        foreach ($countries as $country) {
            $isDefault = $country['id_country'] == $defaultCountryId;

            $countryOptions[] = array(
                'id' => $country['id_country'],
                'name' => $country['name'] . ($isDefault ? ' [--'.Context::getContext()->getTranslator()->trans('Default').'--]' : ''),
                // Dla renderTable() wykorzystujemy oryginalną nazwę
                'orygin' => $country['name'],
            );
        }

        return $countryOptions;
    }

    // ========================================================
    // TABELA PROGRAMÓW =======================================
    // ========================================================
    public function renderProgramsTable()
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        // Ustawienia pomocnicze
        $helper = $this->createHelperTable();

        // Header Tabeli
        $fields_list = $this->createTableColumns();

        // Ustaw dane w tabeli    
        $programList = $this->prepareTableValues();

        $helper->listTotal = count($programList);
        $helper->tpl_vars = array(
            'fields_list' => $fields_list,
            'list' => $programList,
            'title' => Context::getContext()->getTranslator()->trans('Programs'),
            'token' => Tools::getAdminTokenLite('AdminModules'),
        );

        $output = $helper->generateList($programList, $fields_list);

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
                .'&configure='.$this->name.'&add_program'.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => Context::getContext()->getTranslator()->trans('Add new Program')
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
            'country_id' => array(
                'title' => Context::getContext()->getTranslator()->trans('Kraj'),
                'type' => 'text',
                'orderby' => false,
                'search' => false,
            ),
            'name' => array(
                'title' => Context::getContext()->getTranslator()->trans('Nazwa'),
                'type' => 'text',
                'orderby' => false,
                'search' => false,
            ),
            'version' => array(
                'title' => Context::getContext()->getTranslator()->trans('Wersja'),
                'type' => 'text',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'orderby' => false,
                'search' => false,
            ),
            'valid_date' => array(
                'title' => Context::getContext()->getTranslator()->trans('Data'),
                'type' => 'date',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'orderby' => false,
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
                'title' => Context::getContext()->getTranslator()->trans('Actywny'),
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
                'class' => 'fixed-width-sx',
                'orderby' => false,
                'search' => false,
            ),
        );

        return $fields_list;
    }

    private function prepareTableValues()
    {

        // Załaduj Model
        require_once(_PS_MODULE_DIR_. _MKD_NAME_. '/models/MKDProgramsModel.php');

        $programList = MKDProgramsModel::getProgramList(
            (int) Context::getContext()->language->id,
            Tools::getValue(MKDProgramsModel::$definition['table'] .'Orderby', 'position'), // Sortwanie według
            Tools::getValue(MKDProgramsModel::$definition['table'] .'Orderway', 'ASC'), // Kierunek sortowania
            (int) Context::getContext()->employee->id,
            (int) Context::getContext()->shop->id
        );

        $counter = 1;
        
        // Lista Krajów
        $countries = $this->getDefaultCountries();

        foreach ($programList as &$program) {
            
            //  Przypisz wartość licznika jako Numer
            $program['lp'] = $counter;

            // Pobierz Kraj
            $countryId = $program['country_id'];
                foreach ($countries as $country) {
                    if ($country['id'] == $countryId) {
                        $program['country_id'] = $country['orygin'];
                        break;
                    }
                }

            // Pobierz Nazwę
            $program['name'] = $program['name'];

            // Pobierz Wersję
            $program['version'] = !empty($program['version']) ? $program['version'] : '---';

            // Pobierz Datę
            $program['valid_date'] = ($program['valid_date'] !== '0000-00-00') ? $program['valid_date'] : '---';

            // Pobierz pozycje
            $program['position'] = $program['position'];

             // Pobierz status
             $program['active'] = $program['active'];

            // Zwiększ licznik
            $counter++;

        }

        return $programList;
    }


    // ==================================================================
    // Obsługa zapisu, aktualizacji, usuwania i toogleStatus ============
    // ==================================================================
    public function postProcess()
    {
        $id         = (int) Tools::getValue('id', '');
        $shop_id    = (int) Tools::getValue('shop_id', Configuration::get('PS_SHOP_DEFAULT')); // Domyślny Shop
        $lang_id    = (int) Tools::getValue('lang_id', Configuration::get('PS_LANG_DEFAULT')); // Domyślny Lang
        $country_id = (int) Tools::getValue('country_id');
        $name       = Tools::getValue('name');
        $comment    = Tools::getValue('comment');
        $version    = Tools::getValue('version');
        $valid_date = Tools::getValue('valid_date');
        $position   = (int) Tools::getValue('position');
        $active     = (int) Tools::getValue('active');

        // Załaduj Model
        require_once(_PS_MODULE_DIR_. _MKD_NAME_. '/models/MKDProgramsModel.php');

        $model = new MKDProgramsModel($id);

            // Ustaw dane w Modelu
            $model->shop_id     = $shop_id;
            $model->lang_id     = $lang_id;
            $model->country_id  = $country_id;
            $model->name        = $name;
            $model->comment     = $comment;
            $model->version     = $version;
            $model->valid_date  = Validate::isDate($valid_date) ? $valid_date : null;
            $model->position    = $position;
            $model->active      = $active;


        if (Tools::isSubmit('saveProgram')) {

            // Sprawdź pole Nazwa
            if (empty(pSQL(Tools::getValue('name')))) {
                // Pole 'Nazwa' jest puste, dodaj odpowiedni komunikat o błędzie
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('The Program Name field is required');

            }


            // Pobierz najwyższą position i dodaj następną
            $maxPosition = (int) Db::getInstance()->getValue('SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . MKDProgramsModel::$definition['table'] . '`');

            $model->position = $maxPosition + 1;

            
            if (!count(Context::getContext()->controller->errors) && $model->add()) {
                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('New Program saved successfully.');
    
            }
        }
        
        if (Tools::isSubmit('updateProgram')) {

            // Sprawdź pole Nazwa
            if (empty(pSQL(Tools::getValue('name')))) {
                // Pole 'Nazwa' jest puste, dodaj odpowiedni komunikat o błędzie
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('The Program Name field is required');

            }

            if (!count(Context::getContext()->controller->errors) && $model->update()) {
                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('Program updated successfully.');
            }
        }

        if (Tools::isSubmit('delete' . _MKD_MODULE_COMPONENT_[1])) {
            $deleteId = (int) Tools::getValue('id');
        
            // Pobierz pozycję rekordu, który zostanie usunięty
            $positionToDelete = (int) Db::getInstance()->getValue('SELECT `position` FROM `' . _DB_PREFIX_ . MKDProgramsModel::$definition['table'] . '`
                WHERE `id` = ' . $deleteId);
        
            // Wywołaj metodę delete
            if ($model->delete($deleteId)) {
                // Zaktualizuj pozycje rekordów o wyższych pozycjach niż usunięty rekord
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . MKDProgramsModel::$definition['table'] . '`
                    SET `position` = `position` - 1
                    WHERE `position` > ' . $positionToDelete);
                    
                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('The program was deleted');
            } else {
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('An error occurred while removing the program');
            }
        }
        

        if (Tools::isSubmit('status' . _MKD_MODULE_COMPONENT_[1])) {
            $toggleId = (int) Tools::getValue('id');
            
            // Pobierz rekord, który chcesz zmienić
            $record = new MKDProgramsModel($toggleId);
            
            // Wywołaj metodę toggleStatus
            if ($record->toggleStatus('active')) {
                Context::getContext()->controller->confirmations[] = Context::getContext()->getTranslator()->trans('Status changed successfully.');
            } else {
                Context::getContext()->controller->errors[] = Context::getContext()->getTranslator()->trans('An error occurred while toggling the status.');
            }
        }
       
    }
}