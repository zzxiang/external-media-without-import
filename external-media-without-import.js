jQuery(function ($) {
  $('body').on('click', '.emwi-in-upload-ui button', function (e) {
    $('#emwi-media-new-panel').show();

    $('.uploader-inline #emwi-add').click(function (e) {
      var url = $('#emwi-url').val();
      wp.media.post('add_external_media_without_import', { url: url })
        .done(function (response) {
          $('#emwi-error').hide();
          console.error('add done! ' + response);
        }).fail(function (response) {
          $('#emwi-error').show().text(response.responseJSON.data);
        });
    });

    $('.uploader-inline #emwi-cancel').click(function (e) {
      $('#emwi-media-new-panel').hide();
    });

    e.preventDefault();
  });
});
