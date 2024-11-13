<?php
/*
* Pobierz zapisane załączniki dla Produktu w dostępnych Grupach załączników i Hook'ach (łączy z tabelami PS BD)
*/

class MKDFrontAttachmentsProductManager
{
    // Pobierz listę załączników w hook'u
    public static function getAttachmentsForProduct($hook_NAME, $product_ID, $shop_ID, $lang_ID, $orderBy, $orderWay)
    {

        $query = new DbQuery();
        $query->select('
            a.*,

            tl.title AS attachment_title,
            tl.format,
            tl.hook,
            tl.user_groups,

            th.value AS hook_name,

            tt.title AS group_name,
            tt.description AS group_description,
            tt.program AS group_program,
            
            tug.user_groups AS group_user_groups,

            p.active AS program_active,
            p.name AS program_name,
            p.comment AS program_comment,
            p.version AS program_version,
            p.valid_date AS program_expiration,

            tf.value AS format
        ');

        $query->from(pSQL(_MKD_NAME_), 'a');

        $query->leftJoin(pSQL(_MKD_NAME_) . '_types', 't', 'a.type_id = t.id');

        $query->leftJoin(pSQL(_MKD_NAME_) . '_types_lang', 'tl', 't.id = tl.type_id AND tl.id_lang = ' . $lang_ID);
        $query->leftJoin(pSQL(_MKD_NAME_) . '_types_lang', 'tt', 't.id = tt.type_id AND tt.id_lang = ' . $lang_ID);

        $query->leftJoin(pSQL(_MKD_NAME_) . '_programs', 'p', 'tt.program = p.id AND p.active = 1 AND p.lang_id = ' . $lang_ID);

        $query->leftJoin(pSQL(_MKD_NAME_) . '_types_lang', 'tug', 't.id = tug.type_id AND tug.id_lang = ' . $lang_ID);

        // Pobierz Nazwy hooks
        $query->leftJoin(pSQL(_MKD_NAME_) . '_hooks', 'th', 'tl.hook = th.id');

        // Pobierz Format ścieżki do plików ../pdf/..
        $query->leftJoin(pSQL(_MKD_NAME_) . '_formats', 'tf', 'tl.format = tf.id');


        $query->where('a.product_id = ' . (int)$product_ID);
        $query->where('a.id_shop = ' . (int)$shop_ID);
        $query->where('a.id_lang = ' . (int)$lang_ID);


        $query->where('t.active = 1');  // Aktywna Grupa załączników z '...types'
        $query->where('a.active = 1');  // Aktywny Załącznik z '...attachments';

        $query->where('th.value = "' . pSQL($hook_NAME) . '"'); // Tylko odpowiedni HOOK


        $query->orderBy($orderBy . ' ' . $orderWay);

        $attachments = Db::getInstance()->executeS($query);

        // Grupowanie załączników według grupy
        $groupedAttachments = array();
        foreach ($attachments as $attachment) {
            $groupId = $attachment['user_groups'];
            $groupIds = unserialize($groupId);

            $groupNames = array();

            // Przetwórz ID user_group na ich nazwy
            foreach ($groupIds as $userGroupId) {
                $group = new Group($userGroupId, Context::getContext()->language->id);
                $groupNames[] = $group->name;
            }


            if (!isset($groupedAttachments[$groupId])) {
                $groupedAttachments[$groupId] = array(
                    'group_info' => array(
                        'hook'                  => $attachment['hook_name'],
                        'group_name'            => $attachment['group_name'],
                        'group_description'     => $attachment['group_description'],
                        'group_program'         => $attachment['group_program'],

                        'user_groups'           => $groupNames,

                        'program_active'        => $attachment['program_active'],
                        'program_name'          => $attachment['program_name'],
                        'program_comment'       => $attachment['program_comment'],
                        'program_version'       => $attachment['program_version'],
                        'program_expiration'    => $attachment['program_expiration'],

                        'format'                => $attachment['format']
                    ),
                    'attachments' => array(),
                );
            }

            $groupedAttachments[$groupId]['attachments'][] = $attachment;
        }

        return $groupedAttachments;
    }


    // Pobierz listę Grup załączników dla zbiórczej strony CMS z produktami
    public static function getActiveAttachmentGroupsForCMSPage($shop_ID, $lang_ID)
    {
        $query = new DbQuery();
        $query->select('tl.type_id AS group_id, tl.title AS group_name');
        $query->from(pSQL(_MKD_NAME_) . '_types_lang', 'tl');
        $query->leftJoin(pSQL(_MKD_NAME_) . '_types_shop', 'ts', 'tl.type_id = ts.type_id AND ts.id_shop = ' . (int)$shop_ID);
        $query->leftJoin(pSQL(_MKD_NAME_) . '_types', 't', 'tl.type_id = t.id AND t.active = 1');
        $query->where('ts.id_shop = ' . (int)$shop_ID);
        $query->where('tl.id_lang = ' . (int)$lang_ID);

        $result = Db::getInstance()->executeS($query);

        $groups = array();
        foreach ($result as $row) {
            $groups[] = array(
                'group_id' => $row['group_id'],
                'group_name' => $row['group_name']
            );
        }

        return $groups;
    }


    // Pobierz listę Załączników na zbiórczej stronie CMS dla danego Produktu i Grupy załączników
    public static function getActiveAttachmentsForProductInGroup($shop_ID, $lang_ID, $orderBy, $orderWay, $product_ID = false, $groupAttach_ID = false)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(pSQL(_MKD_NAME_), 'a');

        if ($product_ID != false) {
            $query->where('a.product_id = ' . (int)$product_ID);
        }
        
        if ($groupAttach_ID != false) {
            $query->where('a.type_id = ' . (int)$groupAttach_ID);
        }

        $query->where('a.id_lang = ' . (int)$lang_ID);
        $query->where('a.id_shop = ' . (int)$shop_ID);
        $query->where('a.active = 1');

        $query->orderBy($orderBy . ' ' . $orderWay); // wg position => rosnąco

        $attachments = Db::getInstance()->executeS($query);

        return $attachments;
    }




}