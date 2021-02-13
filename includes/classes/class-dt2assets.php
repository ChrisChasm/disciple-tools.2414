<?php

/**
 * Assets
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
 * Assets Class.
 */
class Dt2Assets extends DT2Base
{
	/**
	 * Enqueue Assets
	 */
	public function dt2_assets()
	{
		// Only add css to dt2 pages.
		$current_page     = get_current_screen()->base;
		$dt2_plugin_pages = array(
			'toplevel_page_dt2_index',
			'toplevel_page_dt2_settings',
		);
		if (in_array($current_page, $dt2_plugin_pages, true)) {
			// Styles.
			wp_enqueue_style('dt2_src_css', DT2_PLUGIN_URL . 'includes/assets/css/styles.css', array(), '1.0');
			wp_enqueue_style('dt2_src_bs_css', DT2_PLUGIN_URL . 'includes/assets/css/bs.css', array(), '1.0');

			// Scripts.
			wp_enqueue_script('dt2_js_helpers', DT2_PLUGIN_URL . 'includes/assets/js/helpers.js', array('jquery'), '1.0', true);
			wp_enqueue_script('dt2_js_repeater', DT2_PLUGIN_URL . 'includes/assets/js/repeater.js', array('jquery'), '1.0', true);
			wp_enqueue_script('dt2_js_scripts', DT2_PLUGIN_URL . 'includes/assets/js/scripts.js', array('jquery'), '1.0', true);
		}
	}

	/**
	 *
	 * DT2 Assets Enqueues
	 **/
	public function __construct()
	{
		add_action('admin_enqueue_scripts', array(&$this, 'dt2_assets'));

		parent::__construct();
	}
}
