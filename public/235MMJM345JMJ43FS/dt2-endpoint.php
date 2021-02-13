<?php
/**
 * Main page
 *
 * @package disciple-tools.2414
 * @developer Maroun Melhem <https://maroun.me>
 * @version   1.0
 */

// Load WordPress.
require_once("../../../../../wp-load.php");

// Include WordPress Plugins.
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Load DT2 Helpers.
$dt2_helpers = new Dt2Helpers();

// Only accept requests from MailChimp.
if ($_SERVER['HTTP_USER_AGENT'] !== 'MailChimp') {
    $dt2_helpers->dt2_webhooks_errors($_REQUEST, 'ERROR: You have no access to this API');
}

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $dt2_helpers->dt2_webhooks_errors($_REQUEST, 'ERROR: Request has to be a POST.');
}

// Fetch fields.
$dt2_post_values = filter_input_array(INPUT_POST);
$dt2_fetched_type = $dt2_post_values['type'];
$dt2_fetched_data = $dt2_post_values['data'];

// Verify the request's MailChimp list_id.
$mc_mailing_list = $dt2_helpers->dt2_get_selected_list();
$dt2_fetched_list_id = $dt2_fetched_data['list_id'];
if ($mc_mailing_list !== $dt2_fetched_list_id) {
    $dt2_helpers->dt2_webhooks_errors($_REQUEST, 'ERROR: Invalid Webhook triggered list.');
}

// Verify disciple-tools.2414 plugin installation.
if (!function_exists('is_plugin_active') || !is_plugin_active('disciple-tools.2414/disciple-tools.2414.php')) {
    $dt2_helpers->dt2_webhooks_errors($_REQUEST, 'ERROR: disciple-tools.2414 plugin is required for this API to work.');
}

// Fetch fields.
$dt2_fetched_email = $dt2_fetched_data['email'];

// DT contacts to update.
$dt2_contacts_to_update_id = $dt2_helpers->dt2_dt_get_contact_id_by_email($dt2_fetched_email);

// Save request logs.
$dt2_helpers->dt2_log_to_file('mc_webhooks', $_REQUEST);

// Handle operations by type
if ($dt2_fetched_type && $dt2_contacts_to_update_id) {
    switch ($dt2_fetched_type) {
        case "unsubscribe":
            try {
                DT_Posts::update_post( "contacts",$dt2_contacts_to_update_id, ['permission_to_contact' => 'no'], false);
            } catch (Exception $e) {
                $dt2_helpers->dt2_webhooks_errors($e, 'DT ERROR: Unsubscribe error.');
            }
            break;
        case "subscribe":
            try {
                DT_Posts::update_post( "contacts",$dt2_contacts_to_update_id, ['permission_to_contact' => 'yes'], false);
            } catch (Exception $e) {
                $dt2_helpers->dt2_webhooks_errors($e, 'DT ERROR: Subscribe error.');
            }
            break;

        case "profile":
            $dt2_fetched_merges = $dt2_fetched_data['merges'];

            // Title.
            $dt2_fetched_name = $dt2_fetched_merges['FNAME'];

            // Sources.
            $dt2_fetched_sources = $dt2_fetched_merges['MMERGE3'];
            $dt2_fetched_sources_formatted = $dt2_helpers->dt2_mc_format_dt($dt2_fetched_sources);

            // Groupings.
            $dt2_groupings = $dt2_fetched_merges['GROUPINGS'];

            $dt2_fetched_regions = [];
            $dt2_fetched_lists = [];
            $dt2_fetched_descriptions = [];

            foreach ($dt2_groupings as $dt2_grouping) {
                $dt2_groupings_groups = $dt2_grouping['groups'];
                if ($dt2_groupings_groups) {
                    if ($dt2_grouping['unique_id'] === DT2_MC_INTEREST_REGION_ID) {
                        // Regions.
                        $dt2_groups_dt_regions = implode(', ', $dt2_helpers->dt2_mc_format_groupings('regions', $dt2_groupings_groups));
                        $dt2_fetched_regions = $dt2_helpers->dt2_mc_format_dt($dt2_groups_dt_regions);

                    } else if ($dt2_grouping['unique_id'] === DT2_MC_INTEREST_DESCRIPTION_ID) {
                        // Descriptions.
                        $dt2_groups_dt_descriptions = implode(', ', $dt2_helpers->dt2_mc_format_groupings('descriptions', $dt2_groupings_groups));
                        $dt2_fetched_descriptions = $dt2_helpers->dt2_mc_format_dt($dt2_groups_dt_descriptions);

                    } else if ($dt2_grouping['unique_id'] === DT2_MC_INTEREST_MAILING_LIST_ID) {
                        // Mailing lists.
                        $dt2_groups_dt_mailing_lists = implode(', ', $dt2_helpers->dt2_mc_format_groupings('mailing_lists', $dt2_groupings_groups));
                        $dt2_fetched_lists = $dt2_helpers->dt2_mc_format_dt($dt2_groups_dt_mailing_lists);
                    }
                }
            }

            try {
                DT_Posts::update_post( "contacts",
                    $dt2_contacts_to_update_id,
                    [
                        "title" => $dt2_fetched_name,
                        "sources" => [
                            "values" => !empty($dt2_fetched_sources_formatted) ? $dt2_fetched_sources_formatted : [],
                            "force_values" => true
                        ],
                        "region" => [
                            "values" => !empty($dt2_fetched_regions) ? $dt2_fetched_regions : [],
                            "force_values" => true
                        ],
                        "description" => [
                            "values" => !empty($dt2_fetched_descriptions) ? $dt2_fetched_descriptions : [],
                            "force_values" => true
                        ],
                        "lists" => [
                            "values" => !empty($dt2_fetched_lists) ? $dt2_fetched_lists : [],
                            "force_values" => true
                        ]
                    ],
                    false
                );
            } catch (Exception $e) {
                $dt2_helpers->dt2_webhooks_errors($e, 'DT ERROR: Profile update error.');
            }
            break;
        default:
            $dt2_helpers->dt2_webhooks_errors($_REQUEST, 'ERROR: Invalid type.');
    }
}
