<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://github.com/diegoacuna/
 * @since      1.0.0
 *
 * @package    Wp_Fetcher
 * @subpackage Wp_Fetcher/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Fetcher
 * @subpackage Wp_Fetcher/public
 * @author     Diego Acuña <diego.acuna@mailbox.org>
 */
class Wp_Fetcher_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->url = esc_attr(get_option('url'));
		$this->post_count = esc_attr(get_option('post_count')); // number of post to fetch
		$this->post_date_format = esc_attr(get_option('post_date_format')); // format of the date on each post
		if (!isset($this->post_date_format))
			$this->post_date_format = 'd-m-Y';
		$author = esc_attr(get_option('show_author')); // do we show the author on each post?
		$this->show_author = (isset($author) && $author != 0) ? true : false;
		$fetch_more = esc_attr(get_option('fetch_more'));
		// if the first page doesnt have $post_count posts, do we fetch more pages?
		$this->fetch_more = (isset($fetch_more) && $fetch_more != 0) ? true : false;
		$this->max_tries = 5; // max number of pages to fetch

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-fetcher-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-fetcher-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'WPFetcher', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * If the provided text is longer than $size, then it adds '...' to the end of
	 * the substr of max length $size.
	 * @param  $text text to ellipsis
	 * @return ellipsized text if necessary.
	 */
	private function ellipsisText($text) {
		return strlen($text) > 50 ? substr($text, 0, 50)."..." : $text;
	}

	/**
	 * Ajax call that fetch the post from the given url.
	 * @return json object with the post information and the http status.
	 */
	public function fetch_posts() {
		if (!empty($this->url) && !empty($this->post_count)) {
			$categories = (!empty($_POST['categories'])) ? explode(',', $_POST['categories']) : array();
			$total_fetched = 0;
			$page = 1;
			$elements = array();
			$tries = 0;
			while ($total_fetched < $this->post_count) {
				$data = $this->curl_data($this->url, $page);
		    if ($data) {
		    	$content = $this->isValidXml($data);
		    	if ($content['valid'] && count($content['content']->channel->item) > 0) {
		    		foreach ($content['content']->channel->item as $value) {
		    			preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', (string)$value->description, $image);
		    			$post_categories = $value->category;
		    			if (!empty($post_categories)) {
		    				$filter_categories = array();
		    				foreach($post_categories as $category) {
		    					$filter_categories[] = (string)$category;
		    				}
		    			}
		    			// if we have a filter by category, this is the time to do the magic
		    			if (!empty($categories) && isset($filter_categories)) {
		    				foreach($filter_categories as $cat) {
		    					if (in_array($cat, $categories)) {
		    						$elements[] = array(
					    				'title' => $this->ellipsisText((string)$value->title, 0, 55, "..."),
					    				'link' => (string)$value->link,
					    				'date' => date($this->post_date_format, strtotime((string)$value->pubDate)),
					    				'avatar' => $image['src'],
					    				'description' => $this->ellipsisText(strip_tags((string)$value->description), 0, 120, "...")
				    				);
			    					$total_fetched++;
			    					break;
		    					}
		    				}
		    			} else {
			    			$elements[] = array(
			    				'title' => $this->ellipsisText((string)$value->title, 0, 55, "..."),
			    				'link' => (string)$value->link,
			    				'date' => date($this->post_date_format, strtotime((string)$value->pubDate)),
			    				'avatar' => $image['src'],
			    				'description' => $this->ellipsisText(strip_tags((string)$value->description), 0, 120, "...")
		    				);
	    					$total_fetched++;
	    				}
		    		}
		    		if (!$this->fetch_more)
		    			break;
		    		$page++;
		    		if ($total_fetched >= $this->post_count) {
		    			$elements = array_slice($elements, 0, $this->post_count);
		    			break;
		    		}
		    	}
		    } else {
		    	break;
		    }
		    $tries++;
		    // if the number of tries is bigger than the maximum number of pages to
		    // fetch, then we leave out the fetching process. This is a safe guard
		    // to don't get trapped in a expensive while.
		    if ($tries >= $this->max_tries)
		    	break;
	    }
	    if (count($elements) > 0) {
		    header( "Content-Type: application/json" );
	  		echo json_encode(array('status' => '200', 'content' => $elements));
	  	} else {
  			header( "Content-Type: application/json" );
	    	echo json_encode(array('status' => '404'));
  		}
		} else {
			header( "Content-Type: application/json" );
    	echo json_encode(array('status' => '204'));
		}
		wp_die();
	}

	private function curl_data($url, $page) {
		$added = ($page != 1) ? '?paged='.$page : '';
		$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.$added);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    return curl_exec($ch);
	}

	public function print_posts_shortcode($atts) {
		$extra_data = "  "; // in here we'll store extra data attributes
		if (!empty($atts['categories'])) {
			// the user wants to filter post by categories
			$categories = array_map('trim', explode(',', $atts['categories']));
			$data_categories = ' data-categories="'.implode(",", $categories).'" ';
			$extra_data .= $data_categories;
		}
		return "<div class=\"fetched-posts-container\" data-load=\"posts\" ".$extra_data."></div>";
	}

	private function isValidXml($content) {
    $content = trim($content);
    if (empty($content)) {
        return false;
    }
    //html go to hell!
    if (stripos($content, '<!DOCTYPE html>') !== false) {
        return false;
    }

    libxml_use_internal_errors(true);
    $parsed = simplexml_load_string($content);
    $errors = libxml_get_errors();          
    libxml_clear_errors();  

    return array('content' => $parsed, 'valid' => empty($errors));
	}

}
