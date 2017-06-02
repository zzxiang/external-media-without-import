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
          <button class="button button-large">
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
          <input id="emwi-url" name="url" type="url" placeholder="<?php echo __('Image URL');?>" value="<?php echo urldecode( $_GET['url'] ); ?>">
        </span>
      </div>
      <div id="emwi-error" <?php if ( $use_js || empty( $_GET['error'] ) ) : ?>style="display: none"<?php endif; ?>>
        <?php echo urldecode( $_GET['error'] ); ?>
      </div>
      <div class="buttons-row">
        <input type="hidden" name="action" value="add_external_media_without_import">
        <input type="submit" id="emwi-add" class="button button-primary" value="<?php echo __('Add') ?>">
        <input type="button" id="emwi-cancel" class="button" value="<?php echo __('Cancel') ?>">
      </div>
    </div>
<?php
}

function wp_ajax_add_external_media_without_import() {
    $error = add_external_media_without_import();
    if ( empty( $error ) ) {
        wp_send_json_success( 'lalala' );
    }
    else {
        wp_send_json_error( $error, 500 );
    }
}

function admin_post_add_external_media_without_import() {
    $error = add_external_media_without_import();
    $redirect_url = 'upload.php';
    if ( !empty( $error ) ) {
        $redirect_url = $redirect_url .  '?page=add-external-media-without-import&url=' . urlencode( $_POST['url'] ) . '&error=' . urlencode( $error );
    }
	wp_redirect( admin_url( $redirect_url ) );
    exit;
}

function add_external_media_without_import() {
    $url = $_POST['url'];
    if ( is_callable( 'curl_init' ) ) {
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_NOBODY, true );
        curl_exec( $ch );
        $file_type = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
        curl_close( $ch );
        error_log("ZZXIANG content: $file_contents");
        error_log("ZZXIANG MIME-TYPE: $file_type");
    }
    else {
        return _( "The php of your server doesn't have cURL support enabled, Please contact the server administrator." );
    }

    /*
    $attach_data = wp_generate_attachment_metadata( 572, 'http://i1266.photobucket.com/albums/jj532/zzxiang/Blog/2017-04-27-uncharted4-01_zpsewsha82u.jpeg' );
    ob_start(); // start buffer capture
    var_dump( $attach_data ); // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean(); // end capture
    error_log( "[ZZXIANG DEBUG] $contents" ); // log contents of the result of var_dump( $object )
     */
    return "";
}

