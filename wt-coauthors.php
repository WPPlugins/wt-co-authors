<?php
/*
Plugin Name: WT Co-authors
Plugin URI: http://anime2.kokidokom.net/all-team-blogs-attention-the-ultimate-mega-super-awesome-co-authors-plugin-is-here/
Description: Displays co-authors of a post.
Version: 2.0.1
Author: Eugen Rochko
Author URI: http://anime2.kokidokom.net/
*/

//require ( ABSPATH . WPINC . '/registration.php' );

//Basic functionality: checks for coauthors, gives them the right textual realisation, returns.
//Input: $postid, usually $post->ID; $dolink, true or false, defines whether there should be linking or not.
function wt_return_coauthors($postid, $dolink = false) {
	$getcoauthor = get_post_meta($postid, 'coauthor', false);
	$coauthor = "";
	if(is_array($getcoauthor)):
		$i = 0;
		$ac = count($getcoauthor);
		foreach($getcoauthor as $author):
			//if(username_exists($author)):
				$i++;

				$getauthordata = get_userdatabylogin($author);
				$authorid = $getauthordata->ID;
				
				$postsmade = get_usernumposts($authorid);
				if($postsmade > 0):
					$link = get_author_posts_url($authorid, $author);
				else:
					$link = $getauthordata->user_url;
				endif;
				
				if($dolink):
					$coauthor .= sprintf('<a href="%1$s" title="%2$s">%3$s</a>', $link, sprintf(__('Posts by %s'), $getauthordata->display_name), $getauthordata->display_name);
				else:
					$coauthor .= sprintf('%s', $getauthordata->display_name);
				endif;
				if($i !== $ac):
					$coauthor .= ', ';
				elseif($i == $ac):
					if($dolink):
						$coauthor .= sprintf(' <span class="coauthor-sep">%s</span> ', __('and', 'wt-co-authors'));
					else:
						$coauthor .= sprintf(' %s ', __('and', 'wt-co-authors'));
					endif;
				endif;
			//endif;
		endforeach;
		$coauthor = $coauthor;
	endif;
    return $coauthor;
}

function wt_return_editors($postid, $dolink = false) {
    $geteditors = get_post_meta($postid, 'editor', false);
    $editors = "";
    if(is_array($geteditors) && !empty($geteditors)):
        $i = 0;
        foreach($geteditors as $editor):
			if(username_exists($author)):
				$geteditordata = get_userdatabylogin($editor);
				$editorid = $geteditordata->ID;
				if($i != 0):
					$editors .= ", ";
				endif;
				$editors .= $geteditordata->display_name;
				$i++;
			endif;
        endforeach;
        $editors = sprintf("<span title=\"%s %s\">*</span>", __('Editor: ', 'wt-co-authors'), $editors);
    endif;
    return $editors;
}

//Template tag for manual use. Displays the coauthors together with the original author. To replace the_author, the_author_posts_link etc
function wt_the_coauthors_link() {
    global $post;
    $user_data = get_userdata($post->post_author);
    $content = sprintf('<a href="%1$s" title="%2$s">%3$s</a>', get_author_posts_url($user_data->ID, $user_data->user_nicename), sprintf(__('Posts by %s'), $user_data->display_name), $user_data->display_name);
    $coauthors = wt_return_coauthors($post->ID, true);
	echo $coauthors.$content;
}

//Filter for the_author template tag, display coauthors automatically.
//Input: $display_name, usually $user_data->display_name
function wt_the_coauthors($display_name) {
	global $post;
	$content = $display_name;
    $coauthors = wt_return_coauthors($post->ID, false);
	return $coauthors.$content;
}

function wt_the_coauthors_link_hack($link) {
	global $authordata, $post;
	$content = sprintf(
	        '<a href="%1$s" title="%2$s">%3$s</a>',
	        get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
	        esc_attr( sprintf( __( 'Posts by %s' ), get_the_author() ) ),
	        get_the_author()
	        );
	$coauthors = wt_return_coauthors($post->ID, true);
	return $coauthors.$content;
}

//The filtering, for automatisation.
add_filter('the_author', 'wt_the_coauthors');
add_filter('get_the_author_display_name', 'wt_the_coauthors');

add_filter('the_author_posts_link', 'wt_the_coauthors_link_hack');

$new_meta_boxes = array(
    "coauthors" => array(
        "name" => "coauthors",
		"id" => "coauthor",
        "std" => "",
        "title" => "Co-authors",
        "description" => "Enter a comma separated list of usernames of authors contributing to this post"),
	"editors" => array(
        "name" => "editors",
		"id" => "editor",
        "std" => "",
        "title" => "Editors",
        "description" => "Enter a comma separated list of usernames of authors responsible for this post's edit")
    );

function wt_new_meta_boxes() {
	global $post, $new_meta_boxes;

	foreach($new_meta_boxes as $meta_box) {
		$meta_box_value = get_post_meta($post->ID, $meta_box['id'], false);
		
		$values = "";
		$i = 0;
		foreach($meta_box_value as $value):
			if($i != 0):
				$values .= ", ";
			endif;
			$values .= $value;
			$i++;
		endforeach;

		echo'<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
		echo'<h2>'.$meta_box['title'].'</h2>';
		echo'<input type="text" name="'.$meta_box['name'].'_value" value="'.$values.'" size="55" /><br />';
		echo'<p><label for="'.$meta_box['name'].'_value">'.$meta_box['description'].'</label></p>';
	}
}

function wt_create_meta_box() {
    global $theme_name;
    if ( function_exists('add_meta_box') ) {
        add_meta_box('wt-new-meta-boxes', 'Authorship & Editorship', 'wt_new_meta_boxes', 'post', 'normal', 'high');
    }
}

function wt_save_postdata($post_id) {
	global $post, $new_meta_boxes;

	foreach($new_meta_boxes as $meta_box) {
		// Verify
		if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
			return $post_id;
		}

		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ))
				return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ))
				return $post_id;
		}
		$old = get_post_meta($post_id, $meta_box['id'], false);
		$data = $_POST[$meta_box['name'].'_value'];
		$datas = explode(", ", $data);
		foreach($datas as $update):
			if(!in_array(trim($update), $old) && !empty($update))
				add_post_meta($post_id, $meta_box['id'], trim($update), false);
		endforeach;
		foreach($old as $delete):
			if(!in_array($delete, $datas))
				delete_post_meta($post_id, $meta_box['id'], $delete);
		endforeach;
	}
}

add_action('admin_menu', 'wt_create_meta_box');
add_action('save_post', 'wt_save_postdata');

?>