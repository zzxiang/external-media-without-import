jQuery( function ( $ ) {
  function clear() {
    $( '#emwi-url' ).val( '' );
    $( '#emwi-hidden' ).hide();
    $( '#emwi-error' ).text( '' );
    $( '#emwi-width' ).val( '' );
    $( '#emwi-height' ).val( '' );
    $( '#emwi-mime-type' ).val( '' );
  }

  $( 'body' ).on( 'click', '#emwi-clear', function ( e ) {
    clear();
  });

  $( 'body' ).on( 'click', '#emwi-show', function ( e ) {
    $( '#emwi-media-new-panel' ).show();
    e.preventDefault();
  });

  $( 'body' ).on( 'click', '.uploader-inline #emwi-add', function ( e ) {
    var url = $( '#emwi-url' ).val();
    wp.media.post( 'add_external_media_without_import', { url: url } )
      .done( function ( response ) {
        var attachment = wp.media.model.Attachment.create( response );
        attachment.fetch();
        // Update the attachment list in browser.
        var frame = wp.media.frame || wp.media.library;
        frame.content.mode( 'browse' );
        frame.state().get( 'library' ).add( attachment ? [ attachment ] : [] );
        if ( wp.media.frame._state != 'library' ) {
          frame.state().get( 'selection' ).add( attachment );
        }
        // Reset the input.
        clear();
        $( '#emwi-hidden' ).hide();
        $( '#emwi-buttons-row .spinner' ).css( 'visibility', 'hidden' );
        console.log( 'add done! ' );
      }).fail( function (response ) {
        $( '#emwi-error' ).text( response['error'] );
        $( '#emwi-width' ).val( response['width'] );
        $( '#emwi-height' ).val( response['height'] );
        $( '#emwi-mime-type' ).val( response['mime-type'] );
        $( '#emwi-hidden' ).show();
        $( '#emwi-buttons-row .spinner' ).css( 'visibility', 'hidden' );
      });
    e.preventDefault();
    $( '#emwi-buttons-row .spinner' ).css( 'visibility', 'visible' );
  });

  $( 'body' ).on( 'click', '.uploader-inline #emwi-cancel', function (e ) {
    clear();
    $( '#emwi-media-new-panel' ).hide();
    $( '#emwi-buttons-row .spinner' ).css( 'visibility', 'hidden' );
    e.preventDefault();
  });
});
