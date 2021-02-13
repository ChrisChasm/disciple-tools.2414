<?php
/**
 * Main page
 *
 * @package disciple-tools.2414
 * @developer Maroun Melhem <https://maroun.me>
 * @version   1.0
 */

// Load WordPress.
require_once("../../../../../../wp-load.php");

// Include WordPress Plugins.
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Load DT2 Helpers.
$dt2_helpers = new Dt2Helpers();

// Verify source.
$postdata = file_get_contents("php://input");
list ($method, $signature) = explode('=', $_SERVER['HTTP_X_FS_SIGNATURE'], 2);

$expectedSignature = hash_hmac(
    $method,
    $postdata,
    'f122a3deafa9b5e6d16192'
);

if (!hash_equals($expectedSignature, $signature)) {
    $dt2_helpers->dt2_webhooks_errors($_REQUEST, 'ERROR: Formstack - You have no access to this API');
}

// Fetch fields.
$dt2_post_values = filter_input_array(INPUT_POST);

// Verify disciple-tools.2414 plugin installation.
if (!function_exists('is_plugin_active') || !is_plugin_active('disciple-tools.2414/disciple-tools.2414.php')) {
    $dt2_helpers->dt2_webhooks_errors($_REQUEST, 'ERROR: disciple-tools.2414 plugin is required for this API to work.');
}

// Save request logs.
$dt2_helpers->dt2_log_to_file('formstack_webhooks', $_REQUEST);

// Fetch fields.
$dt2_fetched_email = $dt2_post_values['Email'];

// DT contacts to update.
$dt2_contacts_to_update_id = $dt2_helpers->dt2_dt_get_contact_id_by_email($dt2_fetched_email);

// Title.
$dt2_fetched_fname = $dt2_post_values['First_Name:'];
$dt2_fetched_lname = $dt2_post_values['Last_Name:'];
$dt2_fetched_name = $dt2_fetched_fname." ".$dt2_fetched_lname;

// List.
$dt2_fetched_dt_list = $dt2_post_values['I_want_to_receive_EMAIL_UPDATES_for:'];
$dt2_fetched_dt_list_arr = [];

if (strpos($dt2_fetched_dt_list, 'Prayer Announcements') !== false) {
    array_push($dt2_fetched_dt_list_arr,'general_prayer');
}

if (strpos($dt2_fetched_dt_list, 'Summary Prayer (1/wk)') !== false) {
    array_push($dt2_fetched_dt_list_arr,'summary_prayer');
}

if (strpos($dt2_fetched_dt_list, 'Weekly Prayer (3/wk)') !== false) {
    array_push($dt2_fetched_dt_list_arr,'weekly_prayer');
}

$dt2_fetched_dt_lists = $dt2_helpers->dt2_mc_format_dt(implode(', ',$dt2_fetched_dt_list_arr));

// Handle operations.
if ($dt2_post_values) {
    if($dt2_contacts_to_update_id){
        try {
            DT_Posts::update_post( "contacts",
                $dt2_contacts_to_update_id,
                [
                    "title" => $dt2_fetched_name,
                    "lists" => [
                        "values" => !empty($dt2_fetched_dt_lists) ? $dt2_fetched_dt_lists : [],
                        "force_values" => true
                    ]
                ],
                false
            );

        } catch (Exception $e) {
            $dt2_helpers->dt2_webhooks_errors($e, 'DT - FORMSTACK - ERROR: Profile update error.');
        }
    }else{
        try {
            $fields = [
                "title" => $dt2_fetched_name,
                "overall_status" => "active",
                "contact_email" => [
                    [
                        "value" => isset($dt2_fetched_email) ? $dt2_fetched_email : ""
                    ],
                ],
                "lists" => [
                    "values" => !empty($dt2_fetched_dt_lists) ? $dt2_fetched_dt_lists : [],
                ]
            ];
            DT_Posts::create_post( "contacts", $fields, false);
        } catch (Exception $e) {
            $dt2_helpers->dt2_webhooks_errors($e, 'DT - FORMSTACK - ERROR: Profile create error.');
        }
    }
}
