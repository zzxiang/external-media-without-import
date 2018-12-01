<?php
/*
Plugin Name: External Media without Import
Description: Add external images to the media library without importing, i.e. uploading them to your WordPress site.
Version: 1.1.2
Author: Zhixiang Zhu
Author URI: http://zxtechart.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0-standalone.html

External Media without Import is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License,
or any later version.
 
External Media without Import is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
Public License for more details.
 
You should have received a copy of the GNU General Public License along
with External Media without Import. If not, see
https://www.gnu.org/licenses/gpl-3.0-standalone.html.
*/
namespace emwi;

function init_emwi() {
	$style = 'emwi-css';
	$css_file = plugins_url( '/external-media-without-import.css', __FILE__ );
	wp_register_style( $style, $css_file );
	wp_enqueue_style( $style );
	$script = 'emwi-js';
	$js_file = plugins_url( '/external-media-without-import.js', __FILE__ );
	wp_register_script( $script, $js_file, array( 'jquery' ) );
	wp_enqueue_script( $script );
}
add_action( 'admin_enqueue_scripts', 'emwi\init_emwi' );

add_action( 'admin_menu', 'emwi\add_submenu' );
add_action( 'post-plupload-upload-ui', 'emwi\post_upload_ui' );
add_action( 'post-html-upload-ui', 'emwi\post_upload_ui' );
add_action( 'wp_ajax_add_external_media_without_import', 'emwi\wp_ajax_add_external_media_without_import' );
add_action( 'admin_post_add_external_media_without_import', 'emwi\admin_post_add_external_media_without_import' );

/**
 * This filter is to make attachments added by this plugin pass the test
 * of wp_attachment_is_image. Otherwise issues with other plugins such
 * as WooCommerce occur:
 *
 * https://github.com/zzxiang/external-media-without-import/issues/10
 * https://wordpress.org/support/topic/product-gallery-image-not-working/
 * http://zxtechart.com/2017/06/05/wordpress/#comment-178
 * http://zxtechart.com/2017/06/05/wordpress/#comment-192
 */
add_filter( 'get_attached_file', function( $file, $attachment_id ) {
	if ( empty( $file ) ) {
		$post = get_post( $attachment_id );
		return $post->guid;
	}
	return $file;
}, 10, 2 );

function add_submenu() {
	add_submenu_page(
		'upload.php',
		__( 'Add External Media without Import' ),
		__( 'Add External Media without Import' ),
		'manage_options',
		'add-external-media-without-import',
		'emwi\print_submenu_page'
	);
}

function post_upload_ui() {
	$media_library_mode = get_user_option( 'media_library_mode', get_current_user_id() );
?>
	<div id="emwi-in-upload-ui">
		<div class="row1">
			<?php echo __('or'); ?>
		</div>
		<div class="row2">
			<?php if ( 'grid' === $media_library_mode ) :  // FIXME: seems that media_library_mode being empty also means grid mode ?>
				<button id="emwi-show" class="button button-large">
					<?php echo __('Add External Media without Import'); ?>
				</button>
				<?php print_media_new_panel( true ); ?>
			<?php else : ?>
				<a class="button button-large" href="<?php echo esc_url( admin_url( '/upload.php?page=add-external-media-without-import', __FILE__ ) ); ?>">
					<?php echo __('Add External Media without Import'); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
<?php
}

function print_submenu_page() {
?>
	<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
	  <?php print_media_new_panel( false ); ?>
	</form>
<?php
}

function print_media_new_panel( $is_in_upload_ui ) {
?>
	<div id="emwi-media-new-panel" <?php if ( $is_in_upload_ui ) : ?>style="display: none"<?php endif; ?>>
		<label id="emwi-urls-label"><?php echo __('Add medias from URLs'); ?></label>
		<textarea id="emwi-urls" rows="<?php echo $is_in_upload_ui ? 3 : 10 ?>" name="urls" required placeholder="<?php echo __("Please fill in the media URLs.\nMultiple URLs are supported with each URL specified in one line.");?>" value="<?php if ( isset( $_GET['urls'] ) ) echo esc_url( $_GET['urls'] ); ?>"></textarea>
		<div id="emwi-hidden" <?php if ( $is_in_upload_ui || empty( $_GET['error'] ) ) : ?>style="display: none"<?php endif; ?>>
		<div>
			<span id="emwi-error"><?php if ( isset( $_GET['error'] ) ) echo esc_html( $_GET['error'] ); ?></span>
			<?php echo _('Please fill in the following properties manually. If you leave the fields blank (or 0 for width/height), the plugin will try to resolve them automatically.'); ?>
		</div>
		<div id="emwi-properties">
			<label><?php echo __('Width'); ?></label>
			<input id="emwi-width" name="width" type="number" value="<?php if ( isset( $_GET['width'] ) ) echo esc_html( $_GET['width'] ); ?>">
			<label><?php echo __('Height'); ?></label>
			<input id="emwi-height" name="height" type="number" value="<?php if ( isset( $_GET['height'] ) ) echo esc_html( $_GET['height'] ); ?>">
			<label><?php echo __('MIME Type'); ?></label>
			<input id="emwi-mime-type" name="mime-type" type="text" value="<?php if ( isset( $_GET['mime-type'] ) ) echo esc_html( $_GET['mime-type'] ); ?>">
		</div>
		</div>
		<div id="emwi-buttons-row">
		<input type="hidden" name="action" value="add_external_media_without_import">
		<span class="spinner"></span>
		<input type="button" id="emwi-clear" class="button" value="<?php echo __('Clear') ?>">
		<input type="submit" id="emwi-add" class="button button-primary" value="<?php echo __('Add') ?>">
		<?php if ( $is_in_upload_ui ) : ?>
			<input type="button" id="emwi-cancel" class="button" value="<?php echo __('Cancel') ?>">
		<?php endif; ?>
		</div>
	</div>
<?php
}

function wp_ajax_add_external_media_without_import() {
	$info = add_external_media_without_import();
	$attachment_ids = $info['attachment_ids'];
	$attachments = array();
	foreach ( $attachment_ids as $attachment_id ) {
		if ( $attachment = wp_prepare_attachment_for_js( $attachment_id ) ) {
			array_push( $attachments, $attachment );
		} else {
			$error = "There's an attachment sucessfully inserted to the media library but failed to be retrieved from the database to be displayed on the page.";
		}
	}
	$info['attachments'] = $attachments;
	if ( isset( $error ) ) {
		$info['error'] = isset( $info['error'] ) ? $info['error'] . "\nAnother error also occurred. " . $error : $error;
	}
	wp_send_json_success( $info );
}

function admin_post_add_external_media_without_import() {
	$info = add_external_media_without_import();
	$redirect_url = 'upload.php';
	$urls = $info['urls'];
	if ( ! empty( $urls ) ) {
		$redirect_url = $redirect_url . '?page=add-external-media-without-import&urls=' . urlencode( $urls );
		$redirect_url = $redirect_url . '&error=' . urlencode( $info['error'] );
		$redirect_url = $redirect_url . '&width=' . urlencode( $info['width'] );
		$redirect_url = $redirect_url . '&height=' . urlencode( $info['height'] );
		$redirect_url = $redirect_url . '&mime-type=' . urlencode( $info['mime-type'] );
	}
	wp_redirect( admin_url( $redirect_url ) );
	exit;
}

function sanitize_and_validate_input() {
	$raw_urls = explode( "\n", $_POST['urls'] );
	$urls = array();
	foreach ( $raw_urls as $i => $raw_url ) {
		// Don't call sanitize_text_field on url because it removes '%20'.
		// Always use esc_url/esc_url_raw when sanitizing URLs. See:
		// https://codex.wordpress.org/Function_Reference/esc_url
		$urls[$i] = esc_url_raw( trim( $raw_url ) );
	}
    unset( $url );  // break the reference with the last element

	$input = array(
		'urls' =>  $urls,
		'width' => sanitize_text_field( $_POST['width'] ),
		'height' => sanitize_text_field( $_POST['height'] ),
		'mime-type' => sanitize_mime_type( $_POST['mime-type'] )
	);

	$width_str = $input['width'];
	$width_int = intval( $width_str );
	if ( ! empty( $width_str ) && $width_int <= 0 ) {
		$input['error'] = _('Width and height must be non-negative integers.');
		return $input;
	}

	$height_str = $input['height'];
	$height_int = intval( $height_str );
	if ( ! empty( $height_str ) && $height_int <= 0 ) {
		$input['error'] = _('Width and height must be non-negative integers.');
		return $input;
	}

	$input['width'] = $width_int;
	$input['height'] = $height_int;

	return $input;
}

function add_external_media_without_import() {
	$info = sanitize_and_validate_input();

	if ( isset( $info['error'] ) ) {
		return $info;
	}

	$urls = $info['urls'];
	$width = $info['width'];
	$height = $info['height'];
	$mime_type = $info['mime-type'];

	$attachment_ids = array();
	$failed_urls = array();

	foreach ( $urls as $url ) {
		if ( empty( $width ) || empty( $height ) ) {
			$image_size = @getimagesize( $url );
			if ( empty( $image_size ) ) {
				array_push( $failed_urls, $url );
				continue;
			}
			$width_of_the_image = empty( $width ) ? $image_size[0] : $width;
			$height_of_the_image = empty( $height ) ? $image_size[1] : $height;
			$mime_type_of_the_image = empty( $mime_type ) ? $image_size['mime'] : $mime_type;
		} elseif ( empty( $mime_type ) ) {
			$response = wp_remote_head( $url );
			if ( is_array( $response ) && isset( $response['headers']['content-type'] ) ) {
				$width_of_the_image = $width;
				$height_of_the_image = $height;
				$mime_type_of_the_image = $response['headers']['content-type'];
			} else {
				continue;
			}
		}
		$filename = wp_basename( $url );
		$attachment = array(
			'guid' => $url,
			'post_mime_type' => $mime_type_of_the_image,
			'post_title' => preg_replace( '/\.[^.]+$/', '', $filename ),
		);
		$attachment_metadata = array(
			'width' => $width_of_the_image,
			'height' => $height_of_the_image,
			'file' => $filename );
		$attachment_metadata['sizes'] = array( 'full' => $attachment_metadata );
		$attachment_id = wp_insert_attachment( $attachment );
		wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
		array_push( $attachment_ids, $attachment_id );
	}

	$info['attachment_ids'] = $attachment_ids;

	$failed_urls_string = implode( "\n", $failed_urls );
	$info['urls'] = $failed_urls_string;

	if ( ! empty( $failed_urls_string ) ) {
		$info['error'] = 'Failed to get info of the image(s).';
	}

	return $info;
}
