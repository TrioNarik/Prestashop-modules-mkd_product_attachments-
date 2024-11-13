<?php
/*
* Pobierz Grupy Typów/Kategorii załączników (łączy z tabelami PS BD)
*/

class MKDTypesGroupManager
{
    // renderTable()
    public static function getTypeAttachList($id_lang, $orderBy, $orderWay, $id_employee, $shop_id)
    {
        $query = new DbQuery();

        // Ustawiamy nazwę tabeli głównej
        $query->from(pSQL(_MKD_NAME_) . '_types', 'tat');

        // Pobieramy ID grupy załączników
        $query->select('tat.id AS type_id, tat.active, tat.id AS id');

        // Pobieramy Status grupy załączników
        $query->select('tat.id AS type_id, tat.active');


        // Dołączamy tabelę `mkd_product_attachments_types_shop` z licznikiem dodanych Files
        $query->leftJoin(pSQL(_MKD_NAME_) . '_types_shop', 'mat', 'tat.id = mat.type_id');
        $query->select('mat.id_shop, mat.files');

        // Dołączamy tabelę `mkd_product_attachments_types_lang`
        $query->leftJoin(pSQL(_MKD_NAME_) . '_types_lang', 'mal', 'tat.id = mal.type_id');
        $query->select('mal.id_lang, mal.format, mal.hook, mal.user_groups, mal.product_brands, mal.product_categories, mal.title, mal.description, mal.extra_url, mal.program');

        // Dołączamy tabelę `shop` w celu uzyskania `shop_name`
        $query->leftJoin('shop', 's', 'mat.id_shop = s.id_shop');
        $query->select('s.name AS shop_name');

        // Dołączamy tabelę `mkd_product_attachments_hooks` w celu uzyskania `hook_name`
        $query->leftJoin(pSQL(_MKD_NAME_) . '_hooks', 'hk', 'mal.hook = hk.id');
        $query->select('hk.value AS hook_name');

        // Dołączamy tabelę `mkd_product_attachments_formats` w celu uzyskania `format_name`
        $query->leftJoin(pSQL(_MKD_NAME_) . '_formats', 'tt', 'mal.format = tt.id');
        $query->select('tt.value AS format_name');

        // Dołączamy tabelę `mkd_product_attachments_types_shop` w celu uzyskania `Position` dla danego sklepu
        $query->leftJoin(pSQL(_MKD_NAME_) . '_types_shop', 'ts', 'tat.id = ts.type_id');
        $query->select('ts.position');

        // Dołączamy tabelę `mkd_product_attachments_programs` w celu uzyskania informacji o Programach
        $query->leftJoin(pSQL(_MKD_NAME_) . '_programs', 'mp', 'mal.program = mp.id');
        $query->select('mp.name AS program_name, mp.version');

        // Dołączamy tabelę `country` w celu uzyskania `iso_code` kraju na podstawie `country_id`
        $query->leftJoin('country', 'c', 'mp.country_id = c.id_country');
        $query->select('c.iso_code AS country_iso_code');

        // ...

        // Dodajemy warunek, aby wybrać tylko rekordy z odpowiednim `id_lang`
        $query->where('mal.id_lang = '.(int)$id_lang);

        // Warunki sortowania
        $query->orderBy($orderBy.' '.$orderWay);

        // Wykonujemy zapytanie i zwracamy dane
        $data = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        return $data;
    }

}