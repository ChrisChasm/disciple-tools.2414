<?php
/**
 * Base
 *
 * @package disciple-tools.2414
 * @developer Maroun Melhem <https://maroun.me>
 * @version   1.0
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Main Class.
 */
class Dt2Base {
    /**
     *
     * DT2 Init Class
     *
     * @var $init
     **/
    public $init;

	/**
	 *
	 * DT2 Helpers Class
	 *
	 * @var $helpers
	 **/
	public $helpers;

	/**
	 *
	 * DT2 Assets Class
	 *
	 * @var $assets
	 **/
	public $assets;

	/**
	 *
	 * DT2 AJAX Class
	 *
	 * @var $ajax
	 **/
	public $ajax;

	/**
	 *
	 * DT2 Pages Class
	 *
	 * @var $pages
	 **/
	public $pages;

    /**
     *
     * DT2 MC Class
     *
     * @var $mc
     **/
    public $mc;

	/**
	 * Required for extended classes to work
	 */
	public function __construct() {
	}

	/**
	 *
	 * Init DT2 Plugin
	 **/
	public function init() {
		/**
		 * Require classes
		 */
        include DT2_PLUGIN_DIR . 'includes/classes/class-dt2init.php';
		include DT2_PLUGIN_DIR . 'includes/classes/class-dt2helpers.php';
		include DT2_PLUGIN_DIR . 'includes/classes/class-dt2assets.php';
		include DT2_PLUGIN_DIR . 'includes/classes/class-dt2pages.php';
        include DT2_PLUGIN_DIR . 'includes/classes/class-dt2ajax.php';
        include DT2_PLUGIN_DIR . 'includes/classes/class-dt2mc.php';

		/**
		 * Init classes
		 */
        $this->init = new Dt2Init();
		$this->helpers = new Dt2Helpers();
		$this->assets  = new Dt2Assets();
		$this->pages   = new Dt2Pages();
        $this->ajax   = new Dt2Ajax();
        $this->mc   = new Dt2Mc();
	}
}
