<?php

/**
 * Plugin pages
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
 * Pages Class.
 */
class Dt2Pages extends Dt2Base
{
    /**
     *
     * DT2 Settings option
     *
     * @var $options
     **/
    private $options;

    /**
     * Add pages
     */
    public function dt2_pages()
    {
        $capability = 'manage_options';
        add_menu_page('disciple-tools.2414', 'disciple-tools.2414 ', $capability, 'dt2_settings', array($this, 'dt2_settings_cb'), 'dashicons-rest-api', null);
    }

    /**
     * Option page fields
     */
    public function dt2_options_sections()
    {
        register_setting(
            'dt2_settings_group',
            'dt2_settings_triggers_tokens'
        );

        add_settings_section(
            'setting_section_id',
            '2414 MailChimp Configuration',
            array($this, 'dt2_options_sections_cb'),
            'dt2_settings'
        );

        add_settings_field(
            'dt2_control',
            'Global Plugin Control',
            array($this, 'dt2_control'),
            'dt2_settings',
            'setting_section_id'
        );

        add_settings_field(
            'dt2_mc_api_key',
            'MailChimp API Key',
            array($this, 'dt2_mc_key'),
            'dt2_settings',
            'setting_section_id'
        );

        add_settings_field(
            'dt2_mc_list',
            'MailChimp List',
            array($this, 'dt2_mc_list'),
            'dt2_settings',
            'setting_section_id'
        );

    }

    /**
     * Control plugin
     */
    public function dt2_control()
    {
        $dt2_mc_control_options_select = "";
        $dt2_mc_control_options_select .= "<select name='dt2_settings_triggers_tokens[dt2_control]'>";
        $dt2_mc_control_options_select .= '<option value="">Should this thing work?</option>';
        $dt2_mc_control_options_select .= '<option value="1"' . selected(1, $this->options['dt2_control'], false) . '>YES! Surprise me.</option>';
        $dt2_mc_control_options_select .= '<option value="0"' . selected(0, $this->options['dt2_control'], false) . '>No! but It\'s not you, it\'s me :/.</option>';
        $dt2_mc_control_options_select .= "</select>";

        printf(
            $dt2_mc_control_options_select,
            isset($this->options['dt2_control']) ? esc_attr($this->options['dt2_control']) : ''
        );
    }

    /**
     * MC API Key
     */
    public function dt2_mc_key()
    {
        $dt2_helpers = new Dt2Helpers();
        $dt2_mc_key_input = "";
        if ($dt2_helpers->dt2_get_global_control_status()) {
            $dt2_mc_key_input .= '<input type="password" placeholder="Enter MailChimp API Key here" class="regular-text" id="dt2_mc_key" name="dt2_settings_triggers_tokens[dt2_mc_key]" value="%s" />';
        } else {
            $dt2_mc_key_input .= "<div class='notice notice-error inline'>";
            $dt2_mc_key_input .= "<p>Not too fast, please enable this plugin before adding your MC key.</p>";
            $dt2_mc_key_input .= "</div>";
        }
        printf(
            $dt2_mc_key_input,
            isset($this->options['dt2_mc_key']) ? esc_attr($this->options['dt2_mc_key']) : ''
        );
    }

    /**
     * MC List
     */
    public function dt2_mc_list()
    {
        $dt2_helpers = new Dt2Helpers();
        $dt2_mc = new Dt2Mc();
        $dt2_mc_list_options = $dt2_mc->dt2_mc_get_lists();
        $dt2_mc_list_options_select = "";

        if ($dt2_helpers->dt2_get_global_control_status()) {
            if ($dt2_helpers->dt2_get_mc_key()) {
                $dt2_mc_list_options_select .= "<select name='dt2_settings_triggers_tokens[dt2_mc_list]'>";
                $dt2_mc_list_options_select .= '<option>Select MailChimp List</option>';
                if (!empty($dt2_mc_list_options)) {
                    foreach ($dt2_mc_list_options as $dt2_mc_list_option_list_key => $dt2_mc_list_option_list_value) {
                        $dt2_mc_list_options_select .= '<option value="' . $dt2_mc_list_option_list_key . '" ' . selected($dt2_mc_list_option_list_key, $this->options['dt2_mc_list'], false) . '>' . $dt2_mc_list_option_list_value . '</option>';
                    }
                } else {
                    $dt2_mc_list_options_select .= '<option>Failed to fetch MailChimp Lists</option>';
                }
                $dt2_mc_list_options_select .= "</select>";

            } else {
                $dt2_mc_list_options_select .= "<div class='notice notice-error inline'>";
                $dt2_mc_list_options_select .= "<p>Enter MailChimp API to fetch available lists</p>";
                $dt2_mc_list_options_select .= "</div>";
            }
        } else {
            $dt2_mc_list_options_select .= "<div class='notice notice-error inline'>";
            $dt2_mc_list_options_select .= "<p>Please enable this plugin to fetch available lists</p>";
            $dt2_mc_list_options_select .= "</div>";
        }

        printf(
            $dt2_mc_list_options_select,
            isset($this->options['dt2_mc_list']) ? esc_attr($this->options['dt2_mc_list']) : ''
        );
    }

    /**
     * Options settings callback
     */
    public function dt2_options_sections_cb()
    {
    }

    /**
     * Default page
     */
    public function dt2_settings_cb()
    {
        // Set class property.
        $this->options = get_option('dt2_settings_triggers_tokens');
        $dt2_helpers = new Dt2Helpers();
        $dt2_mc_key = $dt2_helpers->dt2_get_mc_key();
        if ($dt2_helpers->dt2_get_global_control_status()) {
            ?>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <!-- main content -->
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <h2><span><?php _e('MailChimp Webhook URL', 'dt2'); ?></span></h2>
                                <div class="inside">
                                    <p>
                                        <?php
                                        printf(esc_attr__('The below API endpoint should be added within the MailChimp webhook configuration.', 'dt2'));
                                        ?>
                                    </p>
                                    <p><code><?php echo DT2_API_ENDPOINT; ?></code></p>
                                </div>
                                <!-- .inside -->
                            </div>
                            <!-- .postbox -->
                        </div>
                        <!-- .meta-box-sortables .ui-sortable -->
                    </div>
                    <!-- post-body-content -->
                </div>
            </div>
            <?php
        }
        ?>

        <br>
        <div style="clear:both"></div>

        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('dt2_settings_group');
            do_settings_sections('dt2_settings');
            submit_button();
            ?>
        </form>
        <?php
        $dt2_helper = new Dt2Helpers();
        $dt2_mc = new Dt2Mc();

        if ($dt2_helpers->dt2_get_global_control_status()) {

            $dt2_fetch_posts_nonce = wp_create_nonce('dt2_add_update_lists');
            $dt2_fetch_posts_ajax_url = admin_url('admin-ajax.php?action=dt2_add_update_lists');
            $dt2_lists = $dt2_helper->dt2_get_lists();
            $dt2_dt_lists_options = $dt2_helper->dt2_get_dt_fields_options('Mailing List');
            $dt2_mc_lists_options = $dt2_mc->dt2_get_mc_list_options();
            ?>
            <div class="dt2_mc_lists">
                <div id="poststuff" class="dt2_page">
                    <div id="post-body" class="metabox-holder columns-2">
                        <!-- main content -->
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <div class="postbox">
                                    <h2><span><?php _e('DT - MailChimp lists mapping', 'dt2'); ?></span></h2>
                                    <div class="inside">
                                        <p>
                                            <?php
                                            printf(esc_attr__('Add new DT lists and map it to generated MailChimp lists', 'dt2'));
                                            ?>
                                        </p>
                                        <?php
                                        if ($dt2_mc_key && !empty($dt2_dt_lists_options) && !empty($dt2_mc_lists_options)) :
                                            ?>
                                            <input type="hidden" class="dt2_add_update_nonce"
                                                   value="<?php echo esc_attr($dt2_fetch_posts_nonce); ?>">
                                            <input type="hidden" class="dt2_add_update_nonce_ajax_url"
                                                   value="<?php echo esc_attr($dt2_fetch_posts_ajax_url); ?>">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <!-- Repeater Html Start -->
                                                    <div id="dt2_lists_repeater">
                                                        <!-- Repeater Heading -->
                                                        <div class="repeater-heading">
                                                            <h5 class="pull-left">Add or remove lists</h5>
                                                            <button
                                                                    class="btn btn-primary pt-5 pull-right repeater-add-btn">
                                                                Add
                                                            </button>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="repeater-items">
                                                            <?php
                                                            if (empty($dt2_lists)):
                                                                ?>
                                                                <div class="items" data-group="def-group-0">
                                                                    <div class="item-content">
                                                                        <div class="form-group">
                                                                            <label
                                                                                    class="col-lg-2 control-label">DT
                                                                                List</label>
                                                                            <div class="col-lg-10">
                                                                                <?php echo $dt2_helper->dt2_select_list_options('dt'); ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label
                                                                                    class="col-lg-2 control-label">MailChimp
                                                                                List</label>
                                                                            <div class="col-lg-10">
                                                                                <?php echo $dt2_helper->dt2_select_list_options('mc'); ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Repeater Remove Btn -->
                                                                    <div class="pull-right repeater-remove-btn">
                                                                        <button class="btn btn-danger remove-btn">
                                                                            Remove
                                                                        </button>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                </div>
                                                            <?php
                                                            else:
                                                                $dt_lists_count = 0;
                                                                foreach ($dt2_lists as $dt2_list) {
                                                                    $dt_lists_count++;
                                                                    ?>
                                                                    <div class="items"
                                                                         data-index="def-<?php echo $dt_lists_count; ?>"
                                                                         data-group="def-group-<?php echo $dt_lists_count; ?>">
                                                                        <div class="item-content">
                                                                            <div class="form-group">
                                                                                <label
                                                                                        class="col-lg-2 control-label">DT
                                                                                    List</label>
                                                                                <div class="col-lg-10">
                                                                                    <?php echo $dt2_helper->dt2_select_list_options('dt', $dt2_list); ?>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label
                                                                                        class="col-lg-2 control-label">MailChimp
                                                                                    List</label>
                                                                                <div class="col-lg-10">
                                                                                    <?php echo $dt2_helper->dt2_select_list_options('mc', $dt2_list); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- Repeater Remove Btn -->
                                                                        <div class="pull-right repeater-remove-btn">
                                                                            <button class="btn btn-danger remove-btn">
                                                                                Remove
                                                                            </button>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <?php
                                                                }
                                                            endif;
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <!-- Repeater End -->
                                                    <br>
                                                    <div class="pull-right repeater-remove-btn">
                                                        <button id="dt2_mc_lists"
                                                                data-action="dt2_add_update_lists"
                                                                class="dt2_update_fields btn btn-success remove-btn">
                                                            Save Changes
                                                        </button>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <br>
                                                    <div class="dt2_spinner spinner is-active"
                                                         style="display: none;padding: 0;margin: 0;"></div>
                                                    <br>
                                                    <div style="display: none" class="dt2_notice notice inline">
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        else :
                                            ?>
                                            <div class="notice notice-error inline">
                                                <p>
                                                    <?php
                                                    if (!$dt2_mc_key) {
                                                        printf(
                                                            esc_attr__('A MailChimp API key in the "2414 MailChimp Configuration" list is required to fetch lists.', 'dt2')
                                                        );
                                                    } else if (empty($dt2_dt_lists_options)) {
                                                        printf(
                                                            esc_attr__('Mailing List custom field is empty or non existing (should be named exactly "Mailing List").', 'dt2')
                                                        );
                                                    } else if (empty($dt2_mc_lists_options)) {
                                                        printf(
                                                            esc_attr__("MailChimp Mailing List custom field is empty or non existing (should have an ID of " . DT2_MC_INTEREST_MAILING_LIST_ID . ").", 'dt2')
                                                        );
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        <?php
                                        endif;
                                        ?>
                                    </div>
                                </div>
                                <!-- .postbox -->
                            </div>
                            <!-- .meta-box-sortables .ui-sortable -->
                        </div>
                        <!-- post-body-content -->
                    </div>
                </div>
                <br>
                <div style="clear:both"></div>
            </div>


            <?php
            $dt2_fetch_posts_nonce_2 = wp_create_nonce('dt2_add_update_descriptions');
            $dt2_fetch_posts_ajax_url_2 = admin_url('admin-ajax.php?action=dt2_add_update_descriptions');
            $dt2_descriptions = $dt2_helper->dt2_get_descriptions();
            $dt2_dt_descriptions_options = $dt2_helper->dt2_get_dt_fields_options('Description');
            $dt2_mc_descriptions_options = $dt2_mc->dt2_get_mc_descriptions_options();
            ?>
            <div class="dt2_mc_descriptions">
                <div id="poststuff" class="dt2_page">
                    <div id="post-body" class="metabox-holder columns-2">
                        <!-- main content -->
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <div class="postbox">
                                    <h2><span><?php _e('DT - MailChimp descriptions mapping', 'dt2'); ?></span></h2>
                                    <div class="inside">
                                        <p>
                                            <?php
                                            printf(esc_attr__('Add new DT descriptions and map it to generated MailChimp descriptions', 'dt2'));
                                            ?>
                                        </p>
                                        <?php
                                        if ($dt2_mc_key && !empty($dt2_dt_descriptions_options) && !empty($dt2_mc_descriptions_options)) :
                                            ?>
                                            <input type="hidden" class="dt2_add_update_nonce"
                                                   value="<?php echo esc_attr($dt2_fetch_posts_nonce_2); ?>">
                                            <input type="hidden" class="dt2_add_update_nonce_ajax_url"
                                                   value="<?php echo esc_attr($dt2_fetch_posts_ajax_url_2); ?>">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <!-- Repeater Html Start -->
                                                    <div id="dt2_descriptions_repeater">
                                                        <!-- Repeater Heading -->
                                                        <div class="repeater-heading">
                                                            <h5 class="pull-left">Add or remove descriptions</h5>
                                                            <button
                                                                    class="btn btn-primary pt-5 pull-right repeater-add-btn">
                                                                Add
                                                            </button>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="repeater-items">
                                                            <?php
                                                            if (empty($dt2_descriptions)):
                                                                ?>
                                                                <div class="items" data-group="def-group-0">
                                                                    <div class="item-content">
                                                                        <div class="form-group">
                                                                            <label
                                                                                    class="col-lg-2 control-label">DT
                                                                                Desc</label>
                                                                            <div class="col-lg-10">
                                                                                <?php echo $dt2_helper->dt2_select_descriptions_options('dt'); ?>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label
                                                                                    class="col-lg-2 control-label">MailChimp
                                                                                Desc</label>
                                                                            <div class="col-lg-10">
                                                                                <?php echo $dt2_helper->dt2_select_descriptions_options('mc'); ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Repeater Remove Btn -->
                                                                    <div class="pull-right repeater-remove-btn">
                                                                        <button class="btn btn-danger remove-btn">
                                                                            Remove
                                                                        </button>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                </div>
                                                            <?php
                                                            else:
                                                                $dt_descriptions_count = 0;
                                                                foreach ($dt2_descriptions as $dt2_description) {
                                                                    $dt_descriptions_count++;
                                                                    ?>
                                                                    <div class="items"
                                                                         data-index="def-<?php echo $dt_descriptions_count; ?>"
                                                                         data-group="def-group-<?php echo $dt_descriptions_count; ?>">
                                                                        <div class="item-content">
                                                                            <div class="form-group">
                                                                                <label
                                                                                        class="col-lg-2 control-label">DT
                                                                                    Desc</label>
                                                                                <div class="col-lg-10">
                                                                                    <?php echo $dt2_helper->dt2_select_descriptions_options('dt', $dt2_description); ?>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label
                                                                                        class="col-lg-2 control-label">MailChimp
                                                                                    Desc</label>
                                                                                <div class="col-lg-10">
                                                                                    <?php echo $dt2_helper->dt2_select_descriptions_options('mc', $dt2_description); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- Repeater Remove Btn -->
                                                                        <div class="pull-right repeater-remove-btn">
                                                                            <button class="btn btn-danger remove-btn">
                                                                                Remove
                                                                            </button>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <?php
                                                                }
                                                            endif;
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <!-- Repeater End -->
                                                    <br>
                                                    <div class="pull-right repeater-remove-btn">
                                                        <button id="dt2_mc_descriptions"
                                                                data-action="dt2_add_update_descriptions"
                                                                class="dt2_update_fields btn btn-success remove-btn">
                                                            Save Changes
                                                        </button>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <br>
                                                    <div class="dt2_spinner spinner is-active"
                                                         style="display: none;padding: 0;margin: 0;"></div>
                                                    <br>
                                                    <div style="display: none" class="dt2_notice notice inline">
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        else :
                                            ?>
                                            <div class="notice notice-error inline">
                                                <p>
                                                    <?php
                                                    if (!$dt2_mc_key) {
                                                        printf(
                                                            esc_attr__('A MailChimp API key in the "2414 MailChimp Configuration" list is required to fetch lists.', 'dt2')
                                                        );
                                                    } else if (empty($dt2_dt_descriptions_options)) {
                                                        printf(
                                                            esc_attr__('Mailing List custom field is empty or non existing (should be named exactly "Mailing List").', 'dt2')
                                                        );
                                                    } else if (empty($dt2_mc_descriptions_options)) {
                                                        printf(
                                                            esc_attr__("MailChimp Mailing List custom field is empty or non existing (should have an ID of " . DT2_MC_INTEREST_MAILING_LIST_ID . ").", 'dt2')
                                                        );
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        <?php
                                        endif;
                                        ?>
                                    </div>
                                </div>
                                <!-- .postbox -->
                            </div>
                            <!-- .meta-box-sortables .ui-sortable -->
                        </div>
                        <!-- post-body-content -->
                    </div>
                </div>
                <br>
                <div style="clear:both"></div>
            </div>

            <?php
            $dt2_fetch_posts_nonce_3 = wp_create_nonce('dt2_add_update_regions');
            $dt2_fetch_posts_ajax_url_3 = admin_url('admin-ajax.php?action=dt2_add_update_regions');
            $dt2_regions = $dt2_helper->dt2_get_regions();
            $dt2_dt_regions_options = $dt2_helper->dt2_get_dt_fields_options('Region');
            $dt2_mc_regions_options = $dt2_mc->dt2_get_mc_regions_options();
            ?>
            <div class="dt2_mc_regions">
                <div id="poststuff" class="dt2_page">
                    <div id="post-body" class="metabox-holder columns-2">
                        <!-- main content -->
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <div class="postbox">
                                    <h2><span><?php _e('DT - MailChimp regions mapping', 'dt2'); ?></span></h2>
                                    <div class="inside">
                                        <p>
                                            <?php
                                            printf(esc_attr__('Add new DT regions and map it to generated MailChimp regions', 'dt2'));
                                            ?>
                                        </p>
                                        <?php
                                        if ($dt2_mc_key && !empty($dt2_dt_regions_options) && !empty($dt2_mc_regions_options)) :
                                            ?>
                                            <input type="hidden" class="dt2_add_update_nonce"
                                                   value="<?php echo esc_attr($dt2_fetch_posts_nonce_3); ?>">
                                            <input type="hidden" class="dt2_add_update_nonce_ajax_url"
                                                   value="<?php echo esc_attr($dt2_fetch_posts_ajax_url_3); ?>">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <!-- Repeater Html Start -->
                                                    <div id="dt2_regions_repeater">
                                                        <!-- Repeater Heading -->
                                                        <div class="repeater-heading">
                                                            <h5 class="pull-left">Add or remove regions</h5>
                                                            <button
                                                                    class="btn btn-primary pt-5 pull-right repeater-add-btn">
                                                                Add
                                                            </button>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="repeater-items">
                                                            <?php
                                                            if (empty($dt2_regions)):
                                                                ?>
                                                                <div class="items" data-group="def-group-0">
                                                                    <div class="item-content">
                                                                        <div class="form-group">
                                                                            <label
                                                                                    class="col-lg-2 control-label">DT
                                                                                Region</label>
                                                                            <div class="col-lg-10">
                                                                                <?php echo $dt2_helper->dt2_select_regions_options('dt'); ?>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label
                                                                                    class="col-lg-2 control-label">MailChimp
                                                                                Region</label>
                                                                            <div class="col-lg-10">
                                                                                <?php echo $dt2_helper->dt2_select_regions_options('mc'); ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Repeater Remove Btn -->
                                                                    <div class="pull-right repeater-remove-btn">
                                                                        <button class="btn btn-danger remove-btn">
                                                                            Remove
                                                                        </button>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                </div>
                                                            <?php
                                                            else:
                                                                $dt_regions_count = 0;
                                                                foreach ($dt2_regions as $dt2_region) {
                                                                    $dt_regions_count++;
                                                                    ?>
                                                                    <div class="items"
                                                                         data-index="def-<?php echo $dt_regions_count; ?>"
                                                                         data-group="def-group-<?php echo $dt_regions_count; ?>">
                                                                        <div class="item-content">
                                                                            <div class="form-group">
                                                                                <label
                                                                                        class="col-lg-2 control-label">DT
                                                                                    Region</label>
                                                                                <div class="col-lg-10">
                                                                                    <?php echo $dt2_helper->dt2_select_regions_options('dt', $dt2_region); ?>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label
                                                                                        class="col-lg-2 control-label">MailChimp
                                                                                    Region</label>
                                                                                <div class="col-lg-10">
                                                                                    <?php echo $dt2_helper->dt2_select_regions_options('mc', $dt2_region); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- Repeater Remove Btn -->
                                                                        <div class="pull-right repeater-remove-btn">
                                                                            <button class="btn btn-danger remove-btn">
                                                                                Remove
                                                                            </button>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <?php
                                                                }
                                                            endif;
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <!-- Repeater End -->
                                                    <br>
                                                    <div class="pull-right repeater-remove-btn">
                                                        <button id="dt2_mc_regions"
                                                                data-action="dt2_add_update_regions"
                                                                class="dt2_update_fields btn btn-success remove-btn">
                                                            Save Changes
                                                        </button>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <br>
                                                    <div class="dt2_spinner spinner is-active"
                                                         style="display: none;padding: 0;margin: 0;"></div>
                                                    <br>
                                                    <div style="display: none" class="dt2_notice notice inline">
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        else :
                                            ?>
                                            <div class="notice notice-error inline">
                                                <p>
                                                    <?php
                                                    if (!$dt2_mc_key) {
                                                        printf(
                                                            esc_attr__('A MailChimp API key in the "2414 MailChimp Configuration" list is required to fetch lists.', 'dt2')
                                                        );
                                                    } else if (empty($dt2_dt_regions_options)) {
                                                        printf(
                                                            esc_attr__('Mailing List custom field is empty or non existing (should be named exactly "Mailing List").', 'dt2')
                                                        );
                                                    } else if (empty($dt2_mc_regions_options)) {
                                                        printf(
                                                            esc_attr__("MailChimp Mailing List custom field is empty or non existing (should have an ID of " . DT2_MC_INTEREST_MAILING_LIST_ID . ").", 'dt2')
                                                        );
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        <?php
                                        endif;
                                        ?>
                                    </div>
                                </div>
                                <!-- .postbox -->
                            </div>
                            <!-- .meta-box-sortables .ui-sortable -->
                        </div>
                        <!-- post-body-content -->
                    </div>
                </div>
                <br>
                <div style="clear:both"></div>
            </div>
            <?php
        }
    }

    /**
     * Edt Pages class + settings fields.
     */
    public function __construct()
    {
        add_action('admin_menu', array(&$this, 'dt2_pages'));

        // Settings options.
        add_action('admin_init', array(&$this, 'dt2_options_sections'));

        parent::__construct();
    }
}
