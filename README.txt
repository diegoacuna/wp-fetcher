=== Plugin Name ===
Contributors: diegoacuna
Donate link: http://github.com/diegoacuna/
Tags: news, post, fetch, integrate, blog
Requires at least: 4.0
Tested up to: 4.7.3
Stable tag: 4.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fetch posts from other Wordpress Blog and allows to display those posts using a shortcode on a page. Supports the standard feed of WordPress (/feed).

== Description ==

WPFetcher is a plugin which allows fetching news/posts from the feed of another WordPress site
into your own site. Using a shortcode, you can embed the posts from the other blog in a page
or section on your site.

== Installation ==

To install the plugin, follow the next instructions (similar as an standard wordpress plugin):

1. Upload `wp-fetcher` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. To configure (set url of feed to fetch, how many posts to fetch, etc.) go to the
plugin's options page by pressing the "Options" link of the plugin. You can access the
same options page in the "Options" menu and then clicking "WP Fetcher".
4. After configure everything, use the shortcode [wp_fetcher_add_posts]. This shortcode allows
the option "categories" which is a comma separated list of categories of post that you want to
retrieve. For example, the shortcode [wp_fetcher_add_posts categories="news,life"] will display
posts from the categories "news" and "life".