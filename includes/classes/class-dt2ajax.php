<?php
/**
 * Ajax Functions
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
 * Ajax Class.
 */
class Dt2Ajax extends Dt2Base
{
    /**
     * Update Lists.
     */
    public function dt2_add_update_lists()
    {

        // Get values.
        $dt2_action = isset($_POST['action']) ? wp_strip_all_tags(wp_unslash($_POST['action'])) : '';
        $dt2_nonce = isset($_POST['nonce']) ? wp_strip_all_tags(wp_unslash($_POST['nonce'])) : '';

        $dt2_options = isset($_POST['dt2_options']) ? wp_unslash($_POST['dt2_options']) : [];

        if (!wp_verify_nonce($dt2_nonce, 'dt2_add_update_lists')) {
            exit('Invalid request');
        }
        if ($dt2_action) {
            if (!empty($dt2_options)) {
                $dt2_inserts = [];

                // Init helper.
                $dt2_helpers = new Dt2Helpers();

                // Truncate table.
                $dt2_helpers->dt2_truncate_table(DT2_DB_LISTS_TABLE_NAME);

                foreach ($dt2_options as $dt2_option) {
                    // Insert list.
                    $insertedId = $dt2_helpers->dt2_insert_list($dt2_option['dt_key'], $dt2_option['dt_value'], $dt2_option['mc_key'], $dt2_option['mc_value']);
                    if ($insertedId) {
                        array_push($dt2_inserts, $insertedId);
                    }
                }

                if (!empty($dt2_inserts)) {
                    $result['status'] = 'success';
                    $result['lists_items'] = $dt2_inserts;
                } else {
                    $result['status'] = 'error';
                    $result['error_type'] = 'empty_lists_items';
                }
            } else {
                $result['status'] = 'error';
                $result['error_type'] = 'missing_lists_items';
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' === strtolower(wp_strip_all_tags(wp_unslash($_SERVER['HTTP_X_REQUESTED_WITH'])))) {
                // Return JSON response.
                if (isset($result['status']) && 'success' === $result['status']) {
                    wp_send_json_success($result);
                } else {
                    wp_send_json_error($result);
                }
            } else {
                $wmp_server_ref = isset($_SERVER['HTTP_REFERER']) ? wp_strip_all_tags(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

                header('Location: ' . $wmp_server_ref);
            }
            wp_die();
        } else {
            exit('Invalid request');
        }
    }

    /**
     * Update Descriptions.
     */
    public function dt2_add_update_descriptions()
    {

        // Get values.
        $dt2_action = isset($_POST['action']) ? wp_strip_all_tags(wp_unslash($_POST['action'])) : '';
        $dt2_nonce = isset($_POST['nonce']) ? wp_strip_all_tags(wp_unslash($_POST['nonce'])) : '';

        $dt2_options = isset($_POST['dt2_options']) ? wp_unslash($_POST['dt2_options']) : [];

        if (!wp_verify_nonce($dt2_nonce, 'dt2_add_update_descriptions')) {
            exit('Invalid request');
        }
        if ($dt2_action) {
            if (!empty($dt2_options)) {
                $dt2_inserts = [];

                // Init helper.
                $dt2_helpers = new Dt2Helpers();

                // Truncate table.
                $dt2_helpers->dt2_truncate_table(DT2_DB_DESCRIPTIONS_TABLE_NAME);

                foreach ($dt2_options as $dt2_option) {
                    // Insert Description.
                    $insertedId = $dt2_helpers->dt2_insert_description($dt2_option['dt_key'], $dt2_option['dt_value'], $dt2_option['mc_key'], $dt2_option['mc_value']);
                    if ($insertedId) {
                        array_push($dt2_inserts, $insertedId);
                    }
                }

                if (!empty($dt2_inserts)) {
                    $result['status'] = 'success';
                    $result['descriptions_items'] = $dt2_inserts;
                } else {
                    $result['status'] = 'error';
                    $result['error_type'] = 'empty_descriptions_items';
                }
            } else {
                $result['status'] = 'error';
                $result['error_type'] = 'missing_descriptions_items';
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' === strtolower(wp_strip_all_tags(wp_unslash($_SERVER['HTTP_X_REQUESTED_WITH'])))) {
                // Return JSON response.
                if (isset($result['status']) && 'success' === $result['status']) {
                    wp_send_json_success($result);
                } else {
                    wp_send_json_error($result);
                }
            } else {
                $wmp_server_ref = isset($_SERVER['HTTP_REFERER']) ? wp_strip_all_tags(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

                header('Location: ' . $wmp_server_ref);
            }
            wp_die();
        } else {
            exit('Invalid request');
        }
    }

    /**
     * Update Regions.
     */
    public function dt2_add_update_regions()
    {

        // Get values.
        $dt2_action = isset($_POST['action']) ? wp_strip_all_tags(wp_unslash($_POST['action'])) : '';
        $dt2_nonce = isset($_POST['nonce']) ? wp_strip_all_tags(wp_unslash($_POST['nonce'])) : '';

        $dt2_options = isset($_POST['dt2_options']) ? wp_unslash($_POST['dt2_options']) : [];

        if (!wp_verify_nonce($dt2_nonce, 'dt2_add_update_regions')) {
            exit('Invalid request');
        }
        if ($dt2_action) {
            if (!empty($dt2_options)) {
                $dt2_inserts = [];

                // Init helper.
                $dt2_helpers = new Dt2Helpers();

                // Truncate table.
                $dt2_helpers->dt2_truncate_table(DT2_DB_REGIONS_TABLE_NAME);

                foreach ($dt2_options as $dt2_option) {
                    // Insert Description.
                    $insertedId = $dt2_helpers->dt2_insert_region($dt2_option['dt_key'], $dt2_option['dt_value'], $dt2_option['mc_key'], $dt2_option['mc_value']);
                    if ($insertedId) {
                        array_push($dt2_inserts, $insertedId);
                    }
                }

                if (!empty($dt2_inserts)) {
                    $result['status'] = 'success';
                    $result['regions_items'] = $dt2_inserts;
                } else {
                    $result['status'] = 'error';
                    $result['error_type'] = 'empty_regions_items';
                }
            } else {
                $result['status'] = 'error';
                $result['error_type'] = 'missing_descriptions_items';
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' === strtolower(wp_strip_all_tags(wp_unslash($_SERVER['HTTP_X_REQUESTED_WITH'])))) {
                // Return JSON response.
                if (isset($result['status']) && 'success' === $result['status']) {
                    wp_send_json_success($result);
                } else {
                    wp_send_json_error($result);
                }
            } else {
                $wmp_server_ref = isset($_SERVER['HTTP_REFERER']) ? wp_strip_all_tags(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

                header('Location: ' . $wmp_server_ref);
            }
            wp_die();
        } else {
            exit('Invalid request');
        }
    }

    /**
     *
     * Dt2 Ajax functions
     **/
    public function __construct()
    {
        add_action('wp_ajax_dt2_add_update_lists', array(&$this, 'dt2_add_update_lists'));
        add_action('wp_ajax_dt2_add_update_descriptions', array(&$this, 'dt2_add_update_descriptions'));
        add_action('wp_ajax_dt2_add_update_regions', array(&$this, 'dt2_add_update_regions'));

        parent::__construct();
    }
}