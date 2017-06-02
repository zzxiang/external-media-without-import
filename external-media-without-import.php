<?php
/*
Plugin Name: External Media without Import
 */
namespace WP_ExternalMedia_WithoutImport;

$style = 'WP_ExternalMedia_WithoutImport_css';
$css_file = plugins_url( '/external-media-without-import.css', __FILE__ );
wp_register_style( $style, $css_file );
wp_enqueue_style( $style );

add_action( 'pre-plupload-upload-ui', 'WP_ExternalMedia_WithoutImport\pre_plupload_upload_ui' );

function pre_plupload_upload_ui() {
?>
    <div class="external-media-without-import-button-wrapper">
      <button class="button button-large">
        <?php echo __('Add External Media without Import'); ?>
      </button>
    </div>
<?php
}
?>
