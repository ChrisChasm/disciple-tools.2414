<?php
/**
 * Helpers
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
 * Helpers Class.
 */
class Dt2Helpers extends Dt2Base
{
    public function dt2_log_to_file($log_type, $error_obj)
    {
        if ($log_type && $error_obj) {
            $errorObjBt = print_r($error_obj, true);
            switch ($log_type) {
                case "mc_errors":
                    $logFile = DT2_PLUGIN_DIR . '/logs/errors/mc-errors.log';
                    break;
                case "mc_webhooks_errors":
                    $logFile = DT2_PLUGIN_DIR . '/logs/errors/mc-webhooks-errors.log';
                    break;
                case "mc_requests":
                    $logFile = DT2_PLUGIN_DIR . '/logs/requests/mc-requests.log';
                    break;
                case "mc_webhooks":
                    $logFile = DT2_PLUGIN_DIR . '/logs/requests/mc-webhooks.log';
                    break;
                case "formstack_webhooks":
                    $logFile = DT2_PLUGIN_DIR . '/logs/requests/formstack-webhooks.log';
                    break;
                default:
                    $logFile = DT2_PLUGIN_DIR . '/logs/general/mc-general.log';
            }
            file_put_contents($logFile, $errorObjBt, FILE_APPEND);
        }
    }

    public function dt2_truncate_table($table_name)
    {
        if ($table_name) {
            global $wpdb;
            $dt2_table = $wpdb->prefix . $table_name;

            if ($wpdb->get_var("show tables like '$dt2_table'") == $dt2_table) {
                $wpdb->query("TRUNCATE TABLE $dt2_table;");
            }
        }
    }

    public function dt2_get_mc_key()
    {
        return get_option('dt2_settings_triggers_tokens')['dt2_mc_key'];
    }

    public function dt2_get_mc_server()
    {
        $dt2_mc_key = $this->dt2_get_mc_key();
        return substr($dt2_mc_key, strpos($dt2_mc_key, '-') + 1);
    }

    public function dt2_get_dt_fields_options($field_name)
    {
        if ($field_name) {
            $dtApi_fields = DT_Posts::get_post_field_settings( "contacts" );;
            $dtApi_mailing_list_options = [];
            foreach ($dtApi_fields as $dtApi_field) {
                if ($dtApi_field['name'] === $field_name) {
                    $dtApi_field_lists = $dtApi_field['default'];
                    foreach ($dtApi_field_lists as $dtApi_field_list => $dtApi_field_list_values) {
                        $dtApi_mailing_list_options[$dtApi_field_list] = $dtApi_field_list_values['label'];
                    }
                }
            }

            return $dtApi_mailing_list_options;
        }
    }


    // Lists Helpers.
    public function dt2_selected_list_type($type, $added_options, $value)
    {
        if ($type && $value) {
            switch ($type) {
                case "dt":
                    return $added_options->dt_list_key == $value ? true : false;
                    break;

                case "mc":
                    return $added_options->mc_list_key == $value ? true : false;
                    break;

                default:
                    return false;
            }
        }
    }

    public function dt2_get_mc_list_options()
    {
        $dt2_mc = new Dt2Mc();
        $dt2_mc_lists_options = $dt2_mc->dt2_get_mc_list_options();

        $dtApi_mailing_list_options = [];
        foreach ($dt2_mc_lists_options as $dt2_mc_lists_option) {
            $dtApi_mailing_list_options[$dt2_mc_lists_option['id']] = $dt2_mc_lists_option['name'] . ' - [' . $dt2_mc_lists_option['subscriber_count'] . ' subscribers]';
        }

        return $dtApi_mailing_list_options;
    }

    public function dt2_select_list_options($type, $added_lists = [])
    {
        $select_options = [];

        if ($type === 'mc') {
            $select_options = $this->dt2_get_mc_list_options();
        }

        if ($type === 'dt') {
            $select_options = $this->dt2_get_dt_fields_options('Mailing List');
        }

        $select_html = "<select name='" . $type . "_list_src' class='dt2_" . $type . "_field dt2_the_field form-control'>";
        $select_html .= "<option value=''>Select " . strtoupper($type) . " List</option>";
        foreach ($select_options as $select_option_value => $select_option) {
            $selectedOptionText = $this->dt2_selected_list_type($type, $added_lists, $select_option_value) ? ' selected="selected"' : '';
            $select_html .= "<option" . $selectedOptionText . " value=" . $select_option_value . ">$select_option</option>";
        }
        $select_html .= '</select>';

        return $select_html;
    }

    public function dt2_get_mc_list_key($dt_list_key)
    {
        if ($dt_list_key) {
            global $wpdb;
            $dt2_table = $wpdb->prefix . DT2_DB_LISTS_TABLE_NAME;
            $query = $wpdb->get_results("SELECT `mc_list_key` FROM $dt2_table WHERE `dt_list_key`='$dt_list_key'");
            if (isset($query[0])) {
                return $query[0]->mc_list_key;
            }
        }
    }

    public function dt2_insert_list($dt_key, $dt_value, $mc_key, $mc_value)
    {
        if ($dt_key && $dt_value && $mc_key && $mc_value) {
            global $wpdb;
            $dt2_table = $wpdb->prefix . DT2_DB_LISTS_TABLE_NAME;

            if ($wpdb->get_var("show tables like '$dt2_table'") == $dt2_table) {
                return $wpdb->insert($dt2_table, array(
                    'dt_list_key' => $dt_key,
                    'dt_list_value' => $dt_value,
                    'mc_list_key' => $mc_key,
                    'mc_list_value' => $mc_value
                ));
            }
        }
    }

    public function dt2_insert_description($dt_key, $dt_value, $mc_key, $mc_value)
    {
        if ($dt_key && $dt_value && $mc_key && $mc_value) {
            global $wpdb;
            $dt2_table = $wpdb->prefix . DT2_DB_DESCRIPTIONS_TABLE_NAME;

            if ($wpdb->get_var("show tables like '$dt2_table'") == $dt2_table) {
                return $wpdb->insert($dt2_table, array(
                    'dt_description_key' => $dt_key,
                    'dt_description_value' => $dt_value,
                    'mc_description_key' => $mc_key,
                    'mc_description_value' => $mc_value
                ));
            }
        }
    }

    public function dt2_insert_region($dt_key, $dt_value, $mc_key, $mc_value)
    {
        if ($dt_key && $dt_value && $mc_key && $mc_value) {
            global $wpdb;
            $dt2_table = $wpdb->prefix . DT2_DB_REGIONS_TABLE_NAME;

            if ($wpdb->get_var("show tables like '$dt2_table'") == $dt2_table) {
                return $wpdb->insert($dt2_table, array(
                    'dt_region_key' => $dt_key,
                    'dt_region_value' => $dt_value,
                    'mc_region_key' => $mc_key,
                    'mc_region_value' => $mc_value
                ));
            }
        }
    }

    public function dt2_get_lists()
    {
        global $wpdb;
        $dt2_table = $wpdb->prefix . DT2_DB_LISTS_TABLE_NAME;
        return $wpdb->get_results("SELECT * FROM $dt2_table ORDER BY created_at DESC");
    }

    public function dt2_get_selected_list()
    {
        return get_option('dt2_settings_triggers_tokens')['dt2_mc_list'];
    }

    public function dt2_get_global_control_status()
    {
        return get_option('dt2_settings_triggers_tokens')['dt2_control'];
    }


    // Descriptions helpers.
    public function dt2_get_descriptions()
    {
        global $wpdb;
        $dt2_table = $wpdb->prefix . DT2_DB_DESCRIPTIONS_TABLE_NAME;
        return $wpdb->get_results("SELECT * FROM $dt2_table ORDER BY created_at DESC");
    }

    public function dt2_selected_description_type($type, $added_options, $value)
    {
        if ($type && $value) {
            switch ($type) {
                case "dt":
                    return $added_options->dt_description_key == $value ? true : false;
                    break;

                case "mc":
                    return $added_options->mc_description_key == $value ? true : false;
                    break;

                default:
                    return false;
            }
        }
    }

    public function dt2_get_mc_descriptions_options()
    {
        $dt2_mc = new Dt2Mc();
        $dt2_mc_descriptions_options = $dt2_mc->dt2_get_mc_descriptions_options();

        $dtApi_descriptions_options = [];
        foreach ($dt2_mc_descriptions_options as $dt2_mc_descriptions_option) {
            $dtApi_descriptions_options[$dt2_mc_descriptions_option['id']] = $dt2_mc_descriptions_option['name'];
        }

        return $dtApi_descriptions_options;
    }

    public function dt2_select_descriptions_options($type, $added_options = [])
    {
        $select_options = [];

        if ($type === 'mc') {
            $select_options = $this->dt2_get_mc_descriptions_options();
        }

        if ($type === 'dt') {
            $select_options = $this->dt2_get_dt_fields_options('Description');
        }

        $select_html = "<select name='" . $type . "_descriptions_src' class='dt2_" . $type . "_field dt2_the_field form-control'>";
        $select_html .= "<option value=''>Select " . strtoupper($type) . " Description</option>";
        foreach ($select_options as $select_option_value => $select_option) {
            $selectedOptionText = $this->dt2_selected_description_type($type, $added_options, $select_option_value) ? ' selected="selected"' : '';
            $select_html .= "<option" . $selectedOptionText . " value=" . $select_option_value . ">$select_option</option>";
        }
        $select_html .= '</select>';

        return $select_html;
    }

    // Regions helpers.
    public function dt2_get_regions()
    {
        global $wpdb;
        $dt2_table = $wpdb->prefix . DT2_DB_REGIONS_TABLE_NAME;
        return $wpdb->get_results("SELECT * FROM $dt2_table ORDER BY created_at DESC");
    }

    public function dt2_selected_region_type($type, $added_options, $value)
    {
        if ($type && $value) {
            switch ($type) {
                case "dt":
                    return $added_options->dt_region_key == $value ? true : false;
                    break;

                case "mc":
                    return $added_options->mc_region_key == $value ? true : false;
                    break;

                default:
                    return false;
            }
        }
    }

    public function dt2_get_mc_regions_options()
    {
        $dt2_mc = new Dt2Mc();
        $dt2_mc_regions_options = $dt2_mc->dt2_get_mc_regions_options();

        $dtApi_regions_options = [];
        foreach ($dt2_mc_regions_options as $dt2_mc_regions_option) {
            $dtApi_regions_options[$dt2_mc_regions_option['id']] = $dt2_mc_regions_option['name'];
        }

        return $dtApi_regions_options;
    }

    public function dt2_select_regions_options($type, $added_options = [])
    {
        $select_options = [];

        if ($type === 'mc') {
            $select_options = $this->dt2_get_mc_regions_options();
        }

        if ($type === 'dt') {
            $select_options = $this->dt2_get_dt_fields_options('Region');
        }

        $select_html = "<select name='" . $type . "_regions_src' class='dt2_" . $type . "_field dt2_the_field form-control'>";
        $select_html .= "<option value=''>Select " . strtoupper($type) . " Region</option>";
        foreach ($select_options as $select_option_value => $select_option) {
            $selectedOptionText = $this->dt2_selected_region_type($type, $added_options, $select_option_value) ? ' selected="selected"' : '';
            $select_html .= "<option" . $selectedOptionText . " value=" . $select_option_value . ">$select_option</option>";
        }
        $select_html .= '</select>';

        return $select_html;
    }

    // Webhooks.
    public function dt2_webhooks_errors($request, $msg)
    {
        if ($request && $msg) {
            $error_obj['msg'] = $msg;
            $error_obj['request'] = $request;

            $this->dt2_log_to_file('mc_webhooks_errors', $error_obj);
        }
    }

    public function dt2_dt_get_contact_id_by_email($email)
    {
        if ($email) {
            global $wpdb;
            $prep = $wpdb->prepare(
                "SELECT meta_id,post_id FROM $wpdb->postmeta WHERE meta_key LIKE 'contact_email%' AND meta_value = '%s' ORDER BY meta_id desc",
                $email
            );
            $query = $wpdb->get_results($prep);

            if(count($query) > 1){
                foreach($query as $query_elt){
                    $prep_2 = $wpdb->prepare(
                        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'overall_status' AND meta_value = 'active' AND post_id='%d'",
                        $query_elt->post_id
                    );
                    $query_2 = $wpdb->get_results($prep_2);
                    if (isset($query_2[0]->post_id)) {
                        return $query_2[0]->post_id;
                    }
                }
            }

            // Fallback.
            if(isset($query[0]->post_id)){
                return $query[0]->post_id;
            }
        }
    }

    public function dt2_dt_get_last_modified_by_id($post_id)
    {
        if ($post_id) {
            global $wpdb;
            $prep = $wpdb->prepare(
                "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'last_modified' AND post_id = '%d'",
                $post_id
            );
            $query = $wpdb->get_results($prep);

            if(isset($query[0]->meta_value)){
                return $query[0]->meta_value;
            }
        }
    }

    public function dt2_mc_format_dt($cs_string)
    {
        if ($cs_string) {
            $dt2_fetched_arr_org = [];
            $dt2_fetched_arr = explode(', ', $cs_string);
            foreach ($dt2_fetched_arr as $dt2_fetched_elt) {
                $dt2_fetched_arr_org_sub = [];
                $dt2_fetched_arr_org_sub['value'] = $dt2_fetched_elt;
                $dt2_fetched_arr_org[] = $dt2_fetched_arr_org_sub;
            }

            return $dt2_fetched_arr_org;
        }
    }

    public function dt2_get_dt_grouping_slug($type, $mc_name)
    {
        if ($type && $mc_name) {
            global $wpdb;

            switch ($type) {
                case "regions":
                    $dt2_table = $wpdb->prefix . DT2_DB_REGIONS_TABLE_NAME;
                    $query = $wpdb->get_results("SELECT `dt_region_key` FROM $dt2_table WHERE `mc_region_value`='$mc_name'");
                    if (isset($query[0])) {
                        return $query[0]->dt_region_key;
                    }
                    break;
                case "descriptions":
                    $dt2_table = $wpdb->prefix . DT2_DB_DESCRIPTIONS_TABLE_NAME;
                    $query = $wpdb->get_results("SELECT `dt_description_key` FROM $dt2_table WHERE `mc_description_value`='$mc_name'");
                    if (isset($query[0])) {
                        return $query[0]->dt_description_key;
                    }
                    break;
                default:
                    $dt2_table = $wpdb->prefix . DT2_DB_LISTS_TABLE_NAME;
                    $query = $wpdb->get_results("SELECT `dt_list_key` FROM $dt2_table WHERE `mc_list_value` LIKE '$mc_name -%'");
                    if (isset($query[0])) {
                        return $query[0]->dt_list_key;
                    }
            }
        }
    }

    public function dt2_mc_format_groupings($type, $cs_string)
    {
        if ($type && $cs_string) {
            $dt2_fetched_arr_org = [];
            $dt2_fetched_arr = explode(', ', $cs_string);
            foreach ($dt2_fetched_arr as $dt2_fetched_elt) {
                $dt2_fetched_arr_org[] = $this->dt2_get_dt_grouping_slug($type, $dt2_fetched_elt);
            }

            return $dt2_fetched_arr_org;
        }
    }
}
