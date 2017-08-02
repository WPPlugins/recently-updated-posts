<?php
/*
Plugin Name: Recently Updated Posts
Plugin URI: http://f00f.de/blog/2007/10/23/recently-updated-posts-plugin.html
Description: Returns a list of the most recently updated posts.
Version: 0.3
Author: Hannes Hofmann
Author URI: http://uwr1.de/

Based upon the plugin Recent Posts v1.1 from Nick Momrik
*/

function hh_recently_updated_posts($num = 5, $skip = 0, $skipUnmodifiedPosts = true, $includePages = false, $hideProtectedPosts = true) {
//	global $post;
//	// save original post
//	$originalPost =& $post;

	$posts = hh_rup_get($num, $skip, $skipUnmodifiedPosts, $includePages, $hideProtectedPosts);
	print '<ul>';
	foreach($posts as $post) {
//		$title_ = str_replace('-', '- ', $post->post_title);
//		print '<li>'.'<a href="'.get_permalink($post->ID).'" title="'.htmlentities($post->post_title).'">'.$title_.'</a></li>';
		$title_ = wp_specialchars(strip_tags(str_replace('-', '- ', $post->post_title)));
		print '<li>'.'<a href="'.get_permalink($post->ID).'" title="'.wp_specialchars(strip_tags($post->post_title)).'">'.$title_.'</a></li>';
	}
	print '</ul>';

//	// restore original post
//	$post =& $originalPost;
}

function hh_rup_get($no_posts = 5, $skip = 0, $skipUnmodifiedPosts = true, $includePages = false, $hideProtectedPosts = true) {
	global $wpdb;
	$now = gmdate('Y-m-d H:i:s', time());
	$sql = "SELECT `ID`, `post_title`, `comment_count` FROM `{$wpdb->posts}`"
		. " WHERE `post_status` = 'publish'"
		. " AND `post_modified_gmt` != '0000-00-00 00:00:00'"
		. ($skipUnmodifiedPosts
			?  " AND `post_modified_gmt` != `post_date_gmt`" : '')
		. ($includePages
			? " AND (`post_type` = 'post' OR `post_type` = 'page')"
			: " AND `post_type` = 'post'")
		. ($hideProtectedPosts
			? " AND `post_password` = ''" : '')
		. " AND `post_modified_gmt` < '{$now}'"
		. " ORDER BY `post_modified_gmt` DESC"
		. " LIMIT {$skip}, {$no_posts}";
	return $wpdb->get_results($sql);
}

function hh_rup_widget_show($args) {
	extract($args);
	$options = get_option('hh_rup_widget');
	$title = empty($options['title']) ? __('Recently Updated Posts') : $options['title'];
	$num   = empty($options['num']) ? 5 : $options['num'];
	echo $before_widget;
	echo $before_title . $title . $after_title;
	hh_recently_updated_posts($num);
	echo $after_widget;
}

function hh_rup_widget_control() {
	$options = $newoptions = get_option('hh_rup_widget');
	if ( @$_POST['hh-rup-submit'] ) {
		$newoptions['num'] = strip_tags(stripslashes((int)$_POST['hh-rup-num']));
		$newoptions['title'] = strip_tags(stripslashes($_POST['hh-rup-title']));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('hh_rup_widget', $options);
	}
	$title = attribute_escape($options['title']);
	$num = attribute_escape((int)$options['num']);
?>
	<p><label for="hh-rup-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="hh-rup-title" name="hh-rup-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p><label for="hh-rup-num"><?php _e('Number of posts:'); ?> <input style="width: 250px;" id="hh-rup-num" name="hh-rup-num" type="text" value="<?php echo $num; ?>" /></label></p>
	<input type="hidden" id="hh-rup-submit" name="hh-rup-submit" value="1" />
<?php
}

function hh_rup_widget_init() {
	if (!function_exists('register_sidebar_widget')) {
		return;
	}
	register_sidebar_widget(__('Recently Updated Posts'), 'hh_rup_widget_show', 'widget-rup');
	register_widget_control(__('Recently Updated Posts'), 'hh_rup_widget_control', 300, 90);
}

function hh_rup_date_short($date) {
	return date(__('d.m.'), strtotime($date));
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'hh_rup_widget_init');
?>