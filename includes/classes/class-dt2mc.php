<?php
/**
 * MailChimp Functions
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
class Dt2Mc extends Dt2Base
{
    public function dt2_mc_connect($url, $request_type, $api_key, $data = array())
    {

        if ($request_type == 'GET')
            $url .= '?' . http_build_query($data);

        $mch = curl_init();
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode('user:' . $api_key)
        );
        curl_setopt($mch, CURLOPT_URL, $url);
        curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($mch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
        curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type); // according to MailChimp API: POST/GET/PATCH/PUT/DELETE
        curl_setopt($mch, CURLOPT_TIMEOUT, 10);
        curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection

        if ($request_type != 'GET') {
            curl_setopt($mch, CURLOPT_POST, true);
            curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data)); // send data in json
        }

        return curl_exec($mch);
    }

    // Lists MC.
    public function dt2_get_mc_list_options()
    {
        $dt2_get_mc_list_options = [];
        $dt2_helpers = new Dt2Helpers();
        $dt2_key = $dt2_helpers->dt2_get_mc_key();
        if ($dt2_key) {
            $mc_client = dt2_setup_mc();
            $dt2_mc_list_id = $dt2_helpers->dt2_get_selected_list();
            if ($dt2_mc_list_id) {
                try {
                    $responses = $mc_client->lists->listInterestCategoryInterests($dt2_mc_list_id, DT2_MC_INTEREST_MAILING_LIST_ID, null, null, 100);
                    $dt2_helpers->dt2_log_to_file('mc_requests', $responses);
                    foreach ($responses->interests as $response) {
                        $dt2_get_mc_list_option = [];
                        $dt2_get_mc_list_option['id'] = $response->id;
                        $dt2_get_mc_list_option['name'] = $response->name;
                        $dt2_get_mc_list_option['subscriber_count'] = $response->subscriber_count;

                        $dt2_get_mc_list_options[] = $dt2_get_mc_list_option;
                    }
                } catch (Exception $e) {
                    $dt2_helpers->dt2_log_to_file('mc_errors', $e);
                }
            }
        }

        return $dt2_get_mc_list_options;
    }

    public function dt2_mc_get_lists()
    {
        $mc_lists = [];

        $dt2_helpers = new Dt2Helpers();
        $api_key = $dt2_helpers->dt2_get_mc_key();
        if ($api_key) {
            $data = array(
                'fields' => 'lists'
            );

            $url = 'https://' . substr($api_key, strpos($api_key, '-') + 1) . '.api.mailchimp.com/3.0/lists/';
            $result = json_decode($this->dt2_mc_connect($url, 'GET', $api_key, $data));


            if (!empty($result->lists)) {
                foreach ($result->lists as $list) {
                    $mc_lists[$list->id] = $list->name;
                }
            }
        }

        return $mc_lists;
    }

    // Descriptions MC.
    public function dt2_get_mc_descriptions_options()
    {
        $dt2_get_mc_descriptions_options = [];
        $dt2_helpers = new Dt2Helpers();
        $dt2_key = $dt2_helpers->dt2_get_mc_key();
        if ($dt2_key) {
            $mc_client = dt2_setup_mc();
            $dt2_mc_list_id = $dt2_helpers->dt2_get_selected_list();
            if ($dt2_mc_list_id) {
                try {
                    $responses = $mc_client->lists->listInterestCategoryInterests($dt2_mc_list_id, DT2_MC_INTEREST_DESCRIPTION_ID, null, null, 100);
                    $dt2_helpers->dt2_log_to_file('mc_requests', $responses);
                    foreach ($responses->interests as $response) {
                        $dt2_get_mc_descriptions_option = [];
                        $dt2_get_mc_descriptions_option['id'] = $response->id;
                        $dt2_get_mc_descriptions_option['name'] = $response->name;

                        $dt2_get_mc_descriptions_options[] = $dt2_get_mc_descriptions_option;
                    }
                } catch (Exception $e) {
                    $dt2_helpers->dt2_log_to_file('mc_errors', $e);
                }
            }
        }

        return $dt2_get_mc_descriptions_options;
    }

    // Regions MC.
    public function dt2_get_mc_regions_options()
    {
        $dt2_get_mc_regions_options = [];
        $dt2_helpers = new Dt2Helpers();
        $dt2_key = $dt2_helpers->dt2_get_mc_key();
        if ($dt2_key) {
            $mc_client = dt2_setup_mc();
            $dt2_mc_list_id = $dt2_helpers->dt2_get_selected_list();
            if ($dt2_mc_list_id) {
                try {
                    $responses = $mc_client->lists->listInterestCategoryInterests($dt2_mc_list_id, DT2_MC_INTEREST_REGION_ID, null, null, 100);
                    $dt2_helpers->dt2_log_to_file('mc_requests', $responses);
                    foreach ($responses->interests as $response) {
                        $dt2_get_mc_regions_option = [];
                        $dt2_get_mc_regions_option['id'] = $response->id;
                        $dt2_get_mc_regions_option['name'] = $response->name;

                        $dt2_get_mc_regions_options[] = $dt2_get_mc_regions_option;
                    }
                } catch (Exception $e) {
                    $dt2_helpers->dt2_log_to_file('mc_errors', $e);
                }
            }
        }

        return $dt2_get_mc_regions_options;
    }

    public function __construct()
    {
        parent::__construct();
    }
}