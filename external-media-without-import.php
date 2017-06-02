<?php
/*
Plugin Name: External Media without Import
 */
namespace WP_ExternalMedia_WithoutImport;

$style = 'WP_ExternalMedia_WithoutImport_css';
$css_file = plugins_url( '/external-media-without-import.css', __FILE__ );
wp_register_style( $style, $css_file );
wp_enqueue_style( $style );

add_action( 'post-plupload-upload-ui', 'WP_ExternalMedia_WithoutImport\post_plupload_upload_ui' );

function post_plupload_upload_ui() {
    $media_library_mode = get_user_option( 'media_library_mode', get_current_user_id() );
?>
    <div class="external-media-without-import-button-wrapper" style="margin-top: 20px">
      <?php echo __('or'); ?>
    </div>
    <div class="external-media-without-import-button-wrapper">
      <?php if ( 'grid' === $media_library_mode ) : ?>
        <button class="button button-large">
          <?php echo __('Add External Media without Import'); ?>
        </button>
      <?php else : ?>
        <a class="button button-large" href="<?php echo plugins_url( '/media-add.php', __FILE__ ); ?>">
          <?php echo __('Add External Media without Import'); ?>
        </a>
      <?php endif; ?>
    </div>
<?php
}
?>
