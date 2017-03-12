<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://github.com/diegoacuna/
 * @since      1.0.0
 *
 * @package    Wp_Fetcher
 * @subpackage Wp_Fetcher/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Fetcher
 * @subpackage Wp_Fetcher/admin
 * @author     Diego Acuña <diego.acuna@mailbox.org>
 */
class Wp_Fetcher_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-fetcher-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-fetcher-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
   * Register the administration menu for this plugin into the WordPress Dashboard menu.
   *
   * @since    1.0.0
   */ 
  public function add_options_page() {

  	add_options_page( 'WP Fetcher Options', 'WP Fetcher', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page') );

  }

  public function add_plugin_admin_menu() {

      /*
       * Add a settings page for this plugin to the Settings menu.
       *
       * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
       *
       *        Administration Menus: http://codex.wordpress.org/Administration_Menus
       *
       */
      register_setting( 'wp-fetcher-general-options', 'url');
      register_setting( 'wp-fetcher-general-options', 'post_count');
      register_setting( 'wp-fetcher-general-options', 'post_date_format');
      register_setting( 'wp-fetcher-general-options', 'show_author');
      register_setting( 'wp-fetcher-general-options', 'fetch_more');
  	 	add_settings_section(
  				'wp_fetcher_setting_section',
  				'News fetcher configuration',
  				array($this, 'wp_fetcher_setting_section_callback_function'),
  				$this->plugin_name
  			);
   		add_settings_field('url', 'URL to fetch (can be a XML feed or JSON)', array($this, 'url_callback'), $this->plugin_name, 'wp_fetcher_setting_section');
   		add_settings_field('post_count', 'Max. number of post to retrieve', array($this, 'post_count_callback'), $this->plugin_name, 'wp_fetcher_setting_section');
      // General Options
      add_settings_field('post_date_format', 'Format of date in every post', array($this, 'general_options_callback'), $this->plugin_name, 'wp_fetcher_setting_section', array('type' => 'post_date_format'));
      add_settings_field('show_author', 'Show name of author in every post', array($this, 'general_options_callback'), $this->plugin_name, 'wp_fetcher_setting_section', array('type' => 'show_author'));
      add_settings_field('fetch_more', 'Fetch more pages if max. number of posts is not reached on first page', array($this, 'general_options_callback'), $this->plugin_name, 'wp_fetcher_setting_section', array('type' => 'fetch_more'));
  }

  public function wp_fetcher_setting_section_callback_function() {
  	echo "In this section, you can configure the url and maximum number of news to retrieve from another blog or feed.
  				Valid options are xml sites or JSON responses. Note that the maximum number of post is only a superior limit, if your
  				response has lesser than the maximum it will only show the current amount of posts.";
  }

  public function url_callback() {
      $url = esc_attr(get_option('url'));
      echo "<input type='text' name='url' class='url-to-fetch' value='$url' />";
  }

  public function post_count_callback() {
      $post_count = esc_attr(get_option('post_count'));
      echo "<input type='text' name='post_count' value='$post_count' />";
  }

  public function general_options_callback(array $args) {
    $data = esc_attr(get_option($args['type']));
    if ($args['type'] == 'post_date_format')
      echo "<input type='text' name='post_date_format' value='$data' />";
    else if ($args['type'] == 'show_author') {
      $checked = ($data != null && $data != 0) ? 'checked="checked"' : '';
      echo "<input type='checkbox' name='show_author' value='1' $checked>";
    } else {
      $checked = ($data != null && $data != 0) ? 'checked="checked"' : '';
      echo "<input type='checkbox' name='fetch_more' value='1' $checked>";
    }
  }

  /**
   * Check if a string contains valid XML data.
   * @param  string $content string to parse
   * @return boolean true if the content is valid XML. False otherwise.
   */
  public function isValidXml($content) {
      $content = trim($content);
      if (empty($content)) {
          return false;
      }
      //html go to hell!
      if (stripos($content, '<!DOCTYPE html>') !== false) {
          return false;
      }

      libxml_use_internal_errors(true);
      simplexml_load_string($content);
      $errors = libxml_get_errors();          
      libxml_clear_errors();  

      return empty($errors);
  }

  /**
   * Handler to check if a url is a valid XML to fetch.
   * @return string format of the feed. If the url is not valid, a string with 
   *                an error message.
   */
  public function fetch_and_check_handler() {
     if(strpos($_SERVER["REQUEST_URI"], '/wp-fetcher/fetch/') !== false) {
     	if (empty($_GET['url'])) exit();
     		$url = $_GET['url'];
        $ch = curl_init();
  	    curl_setopt($ch, CURLOPT_URL, $url);
  	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	    $data = curl_exec($ch);
  	    if ($data) {
  	    	// check the format
  	    	if ($this->isValidXml($data)) {
  	    		echo "XML";
  	    	} else {
  	    		// test for json
  	    		$result = json_decode($data);
  					if (json_last_error() === JSON_ERROR_NONE) {
  					   echo "JSON";
  					} else 
  	    			echo "UNK";
  	    	}
  	    } else {
  	    	echo "Hacking Attempt!";
  	    }
  	    curl_close($ch);
        exit();
     }
  }

  /**
   * Render the settings page for this plugin.
   *
   * @since    1.0.0
   */
  public function display_plugin_setup_page() {
      include_once('partials/wp-fetcher-admin-display.php');
  }

  /**
   * Add a link to the plugin's page to access the options page of the plugin.
   * This method is hooked into the filter plugin_action_links.
   * @param  $links Links of the plugin
   * @return array links of the plugin
   */
  public function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=' . $this->plugin_name . '">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
  }

}
