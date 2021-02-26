    <?php

/**
 * Init
 *
 * @package disciple-tools.2414
 * @developer Maroun Melhem <https://maroun.me>
 * @version   1.0
 */

/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Init Class.
 */
class Dt2Init extends DT2Base
{
    /**
     * Enqueue Assets
     */
    public function dt2_uninstall()
    {
        delete_option('dt2_settings_triggers_tokens');
    }

    public function dt2_db()
    {
        global $wpdb;
        $dt2_charset_collate = $wpdb->get_charset_collate();

        $dt2_lists_table = $wpdb->prefix . DT2_DB_LISTS_TABLE_NAME;
        // DT lists table
        if ($wpdb->get_var("show tables like '$dt2_lists_table'") != $dt2_lists_table) {
            $dt2_sql = "CREATE TABLE $dt2_lists_table (
                id int(11) NOT NULL auto_increment,
                dt_list_key varchar(255) NOT NULL,
                dt_list_value varchar(255) NOT NULL,
                mc_list_key varchar(255) NOT NULL,
                mc_list_value varchar(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
                UNIQUE KEY id (id)
        ) $dt2_charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($dt2_sql);
        }

        // DT descriptions table
        $dt2_descriptions_table = $wpdb->prefix . DT2_DB_DESCRIPTIONS_TABLE_NAME;
        if ($wpdb->get_var("show tables like '$dt2_descriptions_table'") != $dt2_descriptions_table) {
            $dt2_sql = "CREATE TABLE $dt2_descriptions_table (
                id int(11) NOT NULL auto_increment,
                dt_description_key varchar(255) NOT NULL,
                dt_description_value varchar(255) NOT NULL,
                mc_description_key varchar(255) NOT NULL,
                mc_description_value varchar(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
                UNIQUE KEY id (id)
        ) $dt2_charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($dt2_sql);
        }

        // DT regions table
        $dt2_regions_table = $wpdb->prefix . DT2_DB_REGIONS_TABLE_NAME;
        if ($wpdb->get_var("show tables like '$dt2_regions_table'") != $dt2_regions_table) {
            $dt2_sql = "CREATE TABLE $dt2_regions_table (
                id int(11) NOT NULL auto_increment,
                dt_region_key varchar(255) NOT NULL,
                dt_region_value varchar(255) NOT NULL,
                mc_region_key varchar(255) NOT NULL,
                mc_region_value varchar(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
                UNIQUE KEY id (id)
        ) $dt2_charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($dt2_sql);
        }
    }

    public function dt2_sync_c_in_crud($post_type, $post_id, $initial_fields)
    {
        $dt2Helper = new Dt2Helpers();
        if ($post_type === 'contacts') {
            $name = $initial_fields['title'];
            $email = $initial_fields['contact_email'][0]['value'];
            $phone = $initial_fields['contact_phone'][0]['value'];

            $mc_mailing_list = $dt2Helper->dt2_get_selected_list();
            // Remaining fields defaulting empty: Region, Tags, MC Unsubscribe

            $emailToMc = strtolower($email);

            try {
                // Creating on MailChimp
                $mc_client = dt2_setup_mc();

                // Add/Update MC contact to list
                $createUpdateMcResponse = $mc_client->lists->setListMember($mc_mailing_list, md5($emailToMc), [
                    "email_address" => $emailToMc,
                    "status_if_new" => "subscribed",
                    "merge_fields" => [
                        'FNAME' => $name,
                        'PHONE' => $phone
                    ]
                ]);
                $dt2Helper->dt2_log_to_file('mc_requests', $createUpdateMcResponse);
            } catch (Exception $e) {
                $dt2Helper->dt2_log_to_file('mc_errors', $e);
            }

            // Update optional fields.
            // Defaults.
            $allListsOptions = $dt2Helper->dt2_get_lists();
            $allDescriptionsOptions = $dt2Helper->dt2_get_descriptions();
            $allRegionsOptions = $dt2Helper->dt2_get_regions();

            $dtc = DT_Posts::get_post( "contacts", $post_id, true, false );
            if ( is_wp_error( $dtc ) ) {
                $dt2Helper->dt2_log_to_file('mc_errors', $dtc);
                return;
            } else {
                $currentPermissionToContact = $dtc['permission_to_contact']['key'];
                $currentLists = $dtc['lists'];
                $currentDescriptions = $dtc['description'];
                $currentRegions = $dtc['region'];
                $currentTags = $dtc['tags'];
                $currentSources = $dtc['sources'];
            }



            // Subscription status.
            if ($currentPermissionToContact === 'no') {
                $subscriptionStatus = 'unsubscribed';
            } else {
                $subscriptionStatus = 'subscribed';
            }

            // MergeFields Args.
            $mergeFields = [];
            if (!empty($currentSources)) {
                $sourcesStr = implode(', ', $currentSources);
                $mergeFields['MMERGE3'] = $sourcesStr ? $sourcesStr : '';
            }

            // Interests Args.
            $interestsFields = [];
            if (!empty($currentLists)) {
                foreach ($allListsOptions as $allListsOption) {
                    $interestsFields[$allListsOption->mc_list_key] = in_array($allListsOption->dt_list_key, $currentLists) ? true : false;
                }
            }
            if (!empty($currentDescriptions)) {
                foreach ($allDescriptionsOptions as $allDescriptionsOption) {
                    $interestsFields[$allDescriptionsOption->mc_description_key] = in_array($allDescriptionsOption->dt_description_key, $currentDescriptions) ? true : false;
                }
            }
            if (!empty($currentRegions)) {
                foreach ($allRegionsOptions as $allRegionsOption) {
                    $interestsFields[$allRegionsOption->mc_region_key] = in_array($allRegionsOption->dt_region_key, $currentRegions) ? true : false;
                }
            }

            // Tags.
            $tags = [];
            if (!empty($currentTags)) {
                // Get current tags.
                $currentMcTagsFormatted = [];
                $currentMcTags = $mc_client->lists->getListMemberTags($mc_mailing_list, md5($emailToMc));
                foreach ($currentMcTags->tags as $currentMcTag) {
                    $currentMcTagsFormatted[] = $currentMcTag->name;
                }

                // Combine tags.
                $allTags = array_merge((array)$currentTags, (array)$currentMcTagsFormatted);

                // Add only selected tags.
                foreach ($allTags as $allTag) {
                    $subTagArr = [];
                    $subTagArr['name'] = $allTag;
                    $subTagArr['status'] = in_array($allTag, $currentTags) ? 'active' : 'inactive';

                    $tags[] = $subTagArr;
                }

                try {
                    // Update MC tags.
                    $mc_client->lists->updateListMemberTags($mc_mailing_list, md5($emailToMc), [
                        "tags" => $tags
                    ]);
                } catch (Exception $e) {
                    $dt2Helper->dt2_log_to_file('mc_errors', $e);
                }
            }

            // Update fields.
            $updateFields = [
                "status" => $subscriptionStatus,
                "email_address" => $emailToMc,
                "interests" => $interestsFields
            ];
            if (!empty($mergeFields)) {
                $updateFields['merge_fields'] = $mergeFields;
            }

            try {
                // Add/Update MC contact to list.
                $updateMcResp = $mc_client->lists->setListMember($mc_mailing_list, md5($emailToMc), $updateFields);
                $dt2Helper->dt2_log_to_file('mc_requests', $updateMcResp);
            } catch (Exception $e) {
                $dt2Helper->dt2_log_to_file('mc_errors', $e);
            }

        }
    }

    public function dt2_sync_u_in_crud($post_type, $post_id, $initial_fields)
    {
        $dt2Helper = new Dt2Helpers();
        if ($post_type === 'contacts') {
            // Defaults.
            $allListsOptions = $dt2Helper->dt2_get_lists();
            $allDescriptionsOptions = $dt2Helper->dt2_get_descriptions();
            $allRegionsOptions = $dt2Helper->dt2_get_regions();

            $dtc = DT_Posts::get_post( "contacts",$post_id, false, false);
            if ( is_wp_error( $dtc ) ) {
                $dt2Helper->dt2_log_to_file('mc_errors', $dtc);
                return;
            }

            $currentUserEmail = $dtc['contact_email'][0]['value'];
            $emailToMc = strtolower($currentUserEmail);
            $currentPermissionToContact = $dtc['permission_to_contact']['key'];
            $currentLists = $dtc['lists'];
            $currentDescriptions = $dtc['description'];
            $currentRegions = $dtc['region'];
            $currentTags = $dtc['tags'];
            $currentSources = $dtc['sources'];

            $name = $initial_fields['title'];
            $phone = $initial_fields['contact_phone'][0]['value'];
            $sourceUpdated = $initial_fields['sources'];
            $listUpdated = $initial_fields['lists'];
            $descriptionUpdated = $initial_fields['description'];
            $regionUpdated = $initial_fields['region'];
            $tagsUpdated = $initial_fields['tags'];

            $mc_client = dt2_setup_mc();
            $mc_mailing_list = $dt2Helper->dt2_get_selected_list();


            // Subscription status.
            if ($currentPermissionToContact === 'no') {
                $subscriptionStatus = 'unsubscribed';
            } else {
                $subscriptionStatus = 'subscribed';
            }

            // MergeFields Args.
            $mergeFields = [];
            if ($name) {
                $mergeFields['FNAME'] = $name;
            }
            if ($phone) {
                $mergeFields['PHONE'] = $phone;
            }
            if (!empty($sourceUpdated)) {
                $sourcesStr = implode(', ', $currentSources);
                $mergeFields['MMERGE3'] = $sourcesStr ? $sourcesStr : '';
            }

            // Interests Args.
            $interestsFields = [];
            if ($listUpdated) {
                foreach ($allListsOptions as $allListsOption) {
                    $interestsFields[$allListsOption->mc_list_key] = in_array($allListsOption->dt_list_key, $currentLists) ? true : false;
                }
            }
            if ($descriptionUpdated) {
                foreach ($allDescriptionsOptions as $allDescriptionsOption) {
                    $interestsFields[$allDescriptionsOption->mc_description_key] = in_array($allDescriptionsOption->dt_description_key, $currentDescriptions) ? true : false;
                }
            }
            if ($regionUpdated) {
                foreach ($allRegionsOptions as $allRegionsOption) {
                    $interestsFields[$allRegionsOption->mc_region_key] = in_array($allRegionsOption->dt_region_key, $currentRegions) ? true : false;
                }
            }

            // Tags.
            $tags = [];
            if (!empty($tagsUpdated)) {
                // Get current tags.
                $currentMcTagsFormatted = [];
                $currentMcTags = $mc_client->lists->getListMemberTags($mc_mailing_list, md5($emailToMc));
                foreach ($currentMcTags->tags as $currentMcTag) {
                    $currentMcTagsFormatted[] = $currentMcTag->name;
                }

                // Combine tags.
                $allTags = array_merge((array)$currentTags, (array)$currentMcTagsFormatted);

                // Add only selected tags.
                foreach ($allTags as $allTag) {
                    $subTagArr = [];
                    $subTagArr['name'] = $allTag;
                    $subTagArr['status'] = in_array($allTag, $currentTags) ? 'active' : 'inactive';

                    $tags[] = $subTagArr;
                }

                try {
                    // Update MC tags.
                    $mc_client->lists->updateListMemberTags($mc_mailing_list, md5($emailToMc), [
                        "tags" => $tags
                    ]);
                } catch (Exception $e) {
                    $dt2Helper->dt2_log_to_file('mc_errors', $e);
                }
            }

            // Update fields.
            $updateFields = [
                "status" => $subscriptionStatus,
                "email_address" => $emailToMc,
                "interests" => $interestsFields
            ];
            if (!empty($mergeFields)) {
                $updateFields['merge_fields'] = $mergeFields;
            }

            try {
                // Add/Update MC contact to list.
                $updateMcResp = $mc_client->lists->setListMember($mc_mailing_list, md5($emailToMc), $updateFields);
                $dt2Helper->dt2_log_to_file('mc_requests', $updateMcResp);
            } catch (Exception $e) {
                $dt2Helper->dt2_log_to_file('mc_errors', $e);
            }
        }
    }

    /**
     *
     * DT2 Assets Enqueues
     **/
    public function __construct()
    {
        add_action('admin_init', array(&$this, 'dt2_db'));

        // DT2 Sync Hooks.
        // ONLY SYNC MC IF SYNC IS ALLOWED (sync will be disabled if MC webhooks are performing updates)
        $dtHelpers = new Dt2Helpers();
        if ($dtHelpers->dt2_get_global_control_status()) {
            add_action('dt_post_created', [$this, "dt2_sync_c_in_crud"], 10, 3);
            add_action('dt_post_updated', [$this, "dt2_sync_u_in_crud"], 10, 3);
        }

        // Uninstall Hooks.
        register_uninstall_hook(__FILE__, 'dt2_uninstall');
        parent::__construct();
    }
}
