<?php

/**
 * Plugin Name: Disciple Tools - 2414 Mailchimp sync integration
 * Plugin URI: https://maroun.me
 * Description: Custom MailChimp sync integration for 2414 Disciple.Tools WordPress theme
 * Version: 1.0
 * Author: Maroun Melhem
 * Author URI: https://maroun.me
 *
 * @package disciple-tools.2414
 */

/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Definitions.
 */
if (!defined('DT2_PREFIX')) {

    define('DT2_PREFIX', 'dt2_');

    define('DT2_DEFAULT_TIMEZONE', 'America/New York');
    define('DT2_DATE_FORMAT', 'd-m-Y');

    define('DT2_PLUGIN_FOLDER_NAME', 'disciple-tools.2414');
    define('DT2_PLUGIN_URL', WP_PLUGIN_URL . '/' . DT2_PLUGIN_FOLDER_NAME . '/');

    define('DT2_PLUGIN_DIR', dirname(__FILE__) . '/');

    define('DT2_API_ENDPOINT', WP_PLUGIN_URL . '/' . DT2_PLUGIN_FOLDER_NAME . '/public/235MMJM345JMJ43FS/dt2-endpoint.php');

    define('DT2_MC_INTEREST_MAILING_LIST_ID', '9512c69f8d');
    define('DT2_MC_INTEREST_DESCRIPTION_ID', 'e50e54a8a2');
    define('DT2_MC_INTEREST_REGION_ID', 'cf56e5f6ab');

    define('DT2_DB_LISTS_TABLE_NAME', 'dt_mc_dt2414_lists');
    define('DT2_DB_DESCRIPTIONS_TABLE_NAME', 'dt_mc_dt2414_descriptions');
    define('DT2_DB_REGIONS_TABLE_NAME', 'dt_mc_dt2414_regions');
}

/**
 * Load composer.
 */
require DT2_PLUGIN_DIR . 'mailchimp/mailchimp-marketing-php/vendor/autoload.php';


/**
 * Add MailChimp keys.
 */
function dt2_setup_mc()
{
    $dt2_helpers = new Dt2Helpers();
    try {
        $mailchimp = new MailchimpMarketing\ApiClient();

        $mailchimp->setConfig([
            'apiKey' => $dt2_helpers->dt2_get_mc_key(),
            'server' => $dt2_helpers->dt2_get_mc_server()
        ]);

        $response = $mailchimp->ping->get();
        if ($response) {
            return $mailchimp;
        }
    } catch (Error $e) {
        echo 'Error: ', $e->getMessage(), "\n";
    }
}

/**
 * Load plugin Start.
 */
require DT2_PLUGIN_DIR . '/includes/classes/class-dt2base.php';
global $dt2;
$dt2 = new Dt2Base();
$dt2->init();
