jQuery(function ($) {
  $('body').on('click', '#emwi-show', function (e) {
    $('#emwi-media-new-panel').show();
    e.preventDefault();
  });

  $('body').on('click', '.uploader-inline #emwi-add', function (e) {
    var url = $('#emwi-url').val();
    wp.media.post('add_external_media_without_import', { url: url })
      .done(function (response) {
        $('#emwi-hidden').hide();
        console.log('add done! ' + response);
      }).fail(function (response) {
        $('#emwi-error').text(response['error']);
        $('#emwi-width').val(response['width']);
        $('#emwi-height').val(response['height']);
        $('#emwi-mime-type').val(response['mime-type']);
        $('#emwi-hidden').show();
      });
    e.preventDefault();
  });

  $('body').on('click', '.uploader-inline #emwi-cancel', function (e) {
    $('#emwi-media-new-panel').hide();
    $('#emwi-url').val('');
    $('#emwi-hidden').hide();
    $('#emwi-error').text('');
    $('#emwi-width').val('');
    $('#emwi-height').val('');
    $('#emwi-mime-type').val('');
    e.preventDefault();
  });
});
