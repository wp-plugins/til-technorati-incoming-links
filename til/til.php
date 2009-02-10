<?php

/*
Plugin Name: TIL Technorati Incoming Links
Plugin URI: http://www.michelem.org/wordpress-plugin-til-technorati-incoming-links/
Description: In Wordpress 2.3 the Technorati Incoming Links has been replaced with Google Blog Search, now you can add it again with this plugin.
Version: 1.1
Author: Michele Marcucci
Author URI: http://www.michelem.org/

Copyright (c) 2007 Michele Marcucci
Released under the GNU General Public License (GPL)
http://www.gnu.org/licenses/gpl.txt
*/

$defaultdata = array(
	'til_links' => "10",
	);

if (version_compare($wp_version, '2.7', '<')) {
	add_action('activity_box_end', 'til');
} else {
	function til_dashboard_widget_function() {
		til();
	} 
	function til_add_dashboard_widgets() {
		wp_add_dashboard_widget('til_dashboard_widget', 'Technorati Incoming Links', 'til_dashboard_widget_function');	
	} 
	add_action('wp_dashboard_setup', 'til_add_dashboard_widgets' );
}

function til() {
	global $defaultdata, $til_settings, $wp_version;
	if (!function_exists('MagpieRSS')) { // Check if another plugin is using RSS, may not work
		include_once (ABSPATH . WPINC . '/rss.php');
		error_reporting(E_ERROR);
	}
	$home = get_option("home");
	$rss = @fetch_rss('http://feeds.technorati.com/cosmos/rss/?url='.$home.'/');
	if ( isset($rss->items) && 1 < count($rss->items) ) { // Technorati returns a 1-item feed when it has no results
		if (version_compare($wp_version, '2.7', '<')) {
			print '<h3>Technorati Links <cite><a href="http://www.technorati.com/search/'.$home.'?partner=wordpress">More &raquo;</a></cite></h3><ul>';
		} else {
			print '<p><cite><a href="http://www.technorati.com/search/'.$home.'?partner=wordpress">More &raquo;</a></p></cite><ul>';
		}
		if ($til_settings['til_links'] != '') {
			$til_links = $til_settings['til_links'];
		} else {
			$til_links = 10;
		}
		$rss->items = array_slice($rss->items, 0, $til_links);
		foreach ($rss->items as $item ) {
			$itemL = wp_filter_kses($item["link"]);
			$itemT = wptexturize(wp_specialchars($item["title"]));
	        	print '<li><a href="'.$itemL.'">'.$itemT.'</a></li>';
		}
		print '</ul>';
	}
}

add_option('til_settings', $defaultdata, 'Options for Technorati Incoming Links');

$til_settings = get_option('til_settings');

add_action('admin_menu', 'add_til_options_page');

function add_til_options_page()
{
	if (function_exists('add_options_page'))
	{
		add_options_page('Technorati Incoming Links', 'TIL', 8, basename(__FILE__), 'til_options_subpanel');
	}
}

function til_options_subpanel()
{
	global $til_settings, $_POST;
	
	if (isset($_POST['submit']))
	{
		$til_settings['til_links'] = $_POST['til_links'];
		
		update_option('til_settings', $til_settings);
	}


	?>
	<div class="wrap">
        <h2>TIL Technorati Incoming Links</h2>
        <form action="" method="post">
	<p>Select the options you want for the TIL plugin</p>
        <h3>How many links from Technorati do you want to show in your dashboard?</h3>
        <p><input type="text" name="til_links" size="3" value="<?php echo $til_settings['til_links'] ?>" /> (if you put an high number like 100 you could wait some times before the dashboard is loaded, so putting in a number between 1 and 50 is recommended)</p>
        <p><input type="submit" name="submit" value="Save Settings" /></p>
        </form>
	<hr>
	<p>Check if your <a href="http://www.michelem.org/wordpress-plugin-til-technorati-incoming-links/"><strong>til plugin</strong></a> is updated</p>
        </div>
	<?php
}
?>
