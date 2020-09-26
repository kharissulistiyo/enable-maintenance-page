<?php

/**
 * Enable Maintenance Page
 *
 * @package     Enable Maintenance Page
 * @author      kharisblank
 * @copyright   2020 kharisblank
 * @license     GPL-2.0+
 *
 * @enable-maintenance-page
 * Plugin Name: Enable Maintenance Page
 * Plugin URI:  https://easyfixwp.com/blog/enable-maintenance-mode-plugin/
 * Description: WordPress plugin that helps quickly enable maintenance mode for visitors and display content from a specific page during maintenance mode.
 * Version:     1.0.0
 * Author:      kharisblank
 * Author URI:  https://easyfixwp.com
 * Text Domain: enable-maintenance-page
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */


// Disallow direct access to file
defined( 'ABSPATH' ) or die( __('Not Authorized!', 'enable-maintenance-page') );

define( 'EMP_FILE', __FILE__ );
define( 'EMP_DIRECTORY', dirname(__FILE__) );
define( 'EMP_DIRECTORY_URL', plugins_url( null, EMP_FILE ) );

if ( !class_exists('Enable_Maintenance_Page') ) :

  /**
   * Main plugin Class Enable_Maintenance_Page
   */
  class Enable_Maintenance_Page {

    /**
     * Constructor.
     */
    public function __construct() {

      add_action( 'admin_init', array($this, 'plugin_settings') );
      add_action( 'pre_get_posts', array($this, 'modify_query'));
      add_action( 'template_redirect', array($this, 'display_maintenance_page') );
      add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'), 9999 );
      add_filter( 'amp_container_selector', array($this, 'container_selector') );
      add_action( 'emp_content_before', array($this, 'content_before'), 10 );
      add_action( 'emp_content_after', array($this, 'content_after'), 10 );

      // debug
      // add_action( 'storefront_before_site', array($this, 'debug'));

    }

    function debug() {
      echo '<h1>Debug:</h1>';
      global $wp_query;
      var_dump($wp_query);
    }

    /**
     * Modify main query on front-end when maintenance mode is enabled.
     *
     * @param $query current query
     * @return obj
     */
    function modify_query($query) {

      if( false == $this->is_emp_active() ) {
        return $query;
      }

      if( (true == $this->is_emp_active()) && ('0' != $this->emp_page_id()) ) {

        if ( !is_admin() && $query->is_main_query() ) {
          $query->set('post_type', 'page');
          $query->set('page_id', $this->emp_page_id());
        }

      }

      return $query;

    }

    /**
     * CSS selector for page content container.
     *
     * @return string
     */
    function container_selector() {

      $selector = get_option('emp_container', 'class="entry-content"');

      $attr_sl = '';

      if( '' != $selector ) {
        $attr_sl = $selector;
      }

      return $attr_sl;

    }

    /**
     * Before content wrapper.
     *
     * @return void
     */
    function content_before() {
      echo '<div class="emp-page-container">';
    }

    /**
     * After content wrapper.
     *
     * @return string
     */
    function content_after() {
      echo '</div><!-- /.emp-page-container -->';
    }

    /**
     * Add plugin settings under Settings > Reading.
     */
    function plugin_settings() {

      add_settings_section(
    		'emp_reading_setting_section',
    		__('Enable Maintenance Page', 'enable-maintenance-page'),
    		array($this, 'emp_reading_setting_section_callback_function'),
    		'reading'
    	);

      add_settings_field(
    		'emp_activate',
    		__('Enable?', 'enable-maintenance-page'),
    		array($this, 'emp_activate_callback_function'),
    		'reading',
    		'emp_reading_setting_section'
    	);

      add_settings_field(
    		'emp_page',
    		__('Choose a page', 'enable-maintenance-page'),
    		array($this, 'emp_page_callback_function'),
    		'reading',
    		'emp_reading_setting_section'
    	);

      add_settings_field(
    		'emp_container',
    		__('Content container CSS selector', 'enable-maintenance-page'),
    		array($this, 'emp_container_function'),
    		'reading',
    		'emp_reading_setting_section'
    	);

      register_setting( 'reading', 'emp_activate' );
      register_setting( 'reading', 'emp_page' );
      register_setting( 'reading', 'emp_container' );

    }

    /**
     * Plugin settings section decription.
     */
    function emp_reading_setting_section_callback_function() {
      echo '<p><em>'.__('Display content from a specific page during maintenance mode.', 'enable-maintenance-page').'</em></p>';
    }

    /**
     * Checkbox field to activate maintenance mode.
     */
    function emp_activate_callback_function() {

      echo '<input name="emp_activate" id="emp_activate" type="checkbox" value="1" ' . checked( 1, get_option( 'emp_activate' ), false ) . ' />' . __('Check to enable and choose a page below.', 'enable-maintenance-page');

    }

    /**
     * Dropdown menu to select public page.
     */
    function emp_page_callback_function() {

      $pages = wp_dropdown_pages(array(
        'name'              => 'emp_page',
        'echo'              => 0,
        'show_option_none'  => __( '&mdash; Select &mdash;', 'enable-maintenance-page' ),
        'option_none_value' => '0',
        'selected'          => get_option( 'emp_page' )
      ));

      if( !empty($pages) ) {

        echo $pages;
        echo '<p>'. __('Only your visitors will see this page when maintenance mode is active. <br /> Logged in administrators see normal site.', 'enable-maintenance-page') . '</p>';

      } else {

        echo sprintf( __( 'You don\'t have any public page to select. <a href="%s" target="_blank">Create a new one</a> first.', 'enable-maintenance-page' ), esc_url(admin_url('post-new.php?post_type=page')) );

      }

    }

    /**
     * Input field for CSS selector setting.
     */
    function emp_container_function() {

      $selector = get_option('emp_container', 'class="entry-content"');

      $val = '';

      if( '' != $selector ) {
        $val = $selector;
      }

      echo '<input id="emp_container" name="emp_container" class="medium-text" type="text" value="' . esc_attr($val) . '" />';

      echo '<p>'.__('CSS selector for page content container. It should match with your theme to retain the current theme\'s styles. <br /> For example, enter <em><code>class="entry-content"</code></em>, if you use Twenty Twenty theme. In case you can\'t find the selector to use, enter <em><code>class="emp-container"</code></em>.', 'enable-maintenance-page').'<p>';

    }

    /**
     * Check if maintenance mode is active.
     *
     * @return bol
     */
    function is_emp_active() {

      // Disable maintenance mode for logged in user and is site administrator
      if( current_user_can( 'manage_options' ) ) {
        return false;
      }

      $active = get_option('emp_activate');
      if( 1 == $active ) {
        return true;
      } else {
        return false;
      }

    }

    /**
     * Get page ID from the selected page.
     *
     * @return string
     */
    function emp_page_id() {
      $page = get_option('emp_page');
      return $page;
    }

    /**
     * Display maintenance page.
     *
     * @return void
     */
    function display_maintenance_page() {

      if( (true == $this->is_emp_active()) && ('0' != $this->emp_page_id()) ) {
        require_once( EMP_DIRECTORY . '/include/render-template.php' );
        exit;
      }

    }

    /**
     * Enqueue scripts.
     *
     * @return void
     */
    function enqueue_scripts() {

      if( false == $this->is_emp_active() ) {
        return;
      }

      wp_register_style( 'emp-style', EMP_DIRECTORY_URL . '/css/emp-style.css', array(), null );

      wp_enqueue_style( 'emp-style' );

    }

  }

endif;

new Enable_Maintenance_Page;
