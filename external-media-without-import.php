<?php
/*
Plugin Name: External Media without Import
 */
namespace WP_ExternalMedia_WithoutImport;

$style = 'WP_ExternalMedia_WithoutImport_css';
$css_file = plugins_url( '/external-media-without-import.css', __FILE__ );
wp_register_style( $style, $css_file );
wp_enqueue_style( $style );

$script = 'WP_ExternalMedia_WithoutImport_js';
$js_file = plugins_url( '/external-media-without-import.js', __FILE__ );
wp_register_script( $script, $js_file, array( 'jquery' ) );
wp_enqueue_script( $script );

add_action( 'admin_menu', 'WP_ExternalMedia_WithoutImport\add_submenu' );
add_action( 'post-plupload-upload-ui', 'WP_ExternalMedia_WithoutImport\post_upload_ui' );
add_action( 'post-html-upload-ui', 'WP_ExternalMedia_WithoutImport\post_upload_ui' );
add_action( 'wp_ajax_add_external_media_without_import', 'WP_ExternalMedia_WithoutImport\wp_ajax_add_external_media_without_import' );
add_action( 'admin_post_add_external_media_without_import', 'WP_ExternalMedia_WithoutImport\admin_post_add_external_media_without_import' );

function add_submenu() {
    add_submenu_page(
        'upload.php',
        __( 'Add External Media without Import' ),
        __( 'Add External Media without Import' ),
        'manage_options',
        'add-external-media-without-import',
        'WP_ExternalMedia_WithoutImport\print_submenu_page'
    );
}

function post_upload_ui() {
    $media_library_mode = get_user_option( 'media_library_mode', get_current_user_id() );
?>
    <div class="emwi-in-upload-ui">
      <div class="row1">
        <?php echo __('or'); ?>
      </div>
      <div class="row2">
        <?php if ( 'grid' === $media_library_mode ) : ?>
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

function print_media_new_panel( $use_js ) {
?>
    <div id="emwi-media-new-panel" <?php if ( $use_js  ) : ?>style="display: none"<?php endif; ?>>
      <div class="url-row">
        <label><?php echo __('Add a media from URL'); ?></label>
        <span id="emwi-url-input-wrapper">
          <input id="emwi-url" name="url" type="url" required placeholder="<?php echo __('Image URL');?>" value="<?php echo urldecode( $_GET['url'] ); ?>">
        </span>
      </div>
      <div id="emwi-hidden" <?php if ( $use_js || empty( $_GET['error'] ) ) : ?>style="display: none"<?php endif; ?>>
        <div>
          <span id="emwi-error"><?php echo urldecode( $_GET['error'] ); ?></span>
          <?php echo _('Please fill in the following properties manually.'); ?>
        </div>
        <div id="emwi-properties">
          <label><?php echo __('Width'); ?></label>
          <input id="emwi-width" name="width" type="number" value="<?php echo urldecode( $_GET['width'] ); ?>">
          <label><?php echo __('Height'); ?></label>
          <input id="emwi-height" name="height" type="number" value="<?php echo urldecode( $_GET['height'] ); ?>">
          <label><?php echo __('MIME Type'); ?></label>
          <input id="emwi-mime-type" name="mime-type" type="text" value="<?php echo urldecode( $_GET['mime-type'] ); ?>">
        </div>
      </div>
      <div id="emwi-buttons-row">
        <input type="hidden" name="action" value="add_external_media_without_import">
        <span class="spinner"></span>
        <input type="button" id="emwi-clear" class="button" value="<?php echo __('Clear') ?>">
        <input type="submit" id="emwi-add" class="button button-primary" value="<?php echo __('Add') ?>">
        <input type="button" id="emwi-cancel" class="button" value="<?php echo __('Cancel') ?>">
      </div>
    </div>
<?php
}

function wp_ajax_add_external_media_without_import() {
    $info = add_external_media_without_import();
    if ( isset( $info['id'] ) ) {
        if ( $attachment = wp_prepare_attachment_for_js( $info['id'] ) ) {
            wp_send_json_success( $attachment );
        }
        else {
            $info['error'] = _('Failed to prepare attachment for js');
            wp_send_json_error( $info );
        }
    }
    else {
        wp_send_json_error( $info );
    }
}

function admin_post_add_external_media_without_import() {
    $info = add_external_media_without_import();
    $redirect_url = 'upload.php';
    if ( !isset( $info['id'] ) ) {
        $redirect_url = $redirect_url .  '?page=add-external-media-without-import&url=' . urlencode( $_POST['url'] );
        $redirect_url = $redirect_url . '&error=' . urlencode( $info['error'] );
        $redirect_url = $redirect_url . '&width=' . urlencode( $info['width'] );
        $redirect_url = $redirect_url . '&height=' . urlencode( $info['height'] );
        $redirect_url = $redirect_url . '&mime-type=' . urlencode( $info['mime-type'] );
    }
	wp_redirect( admin_url( $redirect_url ) );
    exit;
}

function add_external_media_without_import() {
    $url = $_POST['url'];
    $width = intval( $_POST['width'] );
    $height = intval( $_POST['height'] );
    $mime_type = $_POST['mime-type'];

    $ret = array(
        'width' => $width,
        'height' => $height,
        'mime-type' => $mime_type
    );

    $filename = wp_basename( $url );

    if ( empty( $width ) || empty( $height ) || empty( $mime_type ) ) {
         $image_size = @getimagesize( $url );

        if ( empty( $image_size ) ) {
            if ( empty( $mime_type ) && function_exists( 'curl_init' ) ) {
                // Get MIME type with curl.
                $curl_handle = curl_init( $url );
                curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $curl_handle, CURLOPT_NOBODY, true );
                curl_exec( $curl_handle );
                $mime_type = curl_getinfo( $curl_handle, CURLINFO_CONTENT_TYPE );
                curl_close( $curl_handle );
            }
            $ret['error'] = _('Unable to get the image size.');
            return $ret;
        }

        if ( empty( $width ) ) {
            $width = $image_size[0];
        }

        if ( empty( $height ) ) {
            $height = $image_size[1];
        }

        if ( empty( $mime_type ) ) {
            $mime_type = $image_size['mime'];
        }
    }

    $attachment = array(
        'guid' => $url,
        'post_mime_type' => $mime_type,
        'post_title' => preg_replace( '/\.[^.]+$/', '', $filename ),
    );
    $attachment_metadata = array( 'width' => $width, 'height' => $height, 'file' => $filename );
    $attachment_metadata['sizes'] = array( 'full' => $attachment_metadata );
    $attachment_id = wp_insert_attachment( $attachment );
    wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
    $ret['id'] = $attachment_id;

    return $ret;
}

