jQuery(function ($) {
  $('body').on('click', '.emwi-in-upload-ui button', function (e) {
    $('#emwi-media-new-panel').show();
    $('.uploader-inline #emwi-cancel').click(function (e) {
      $('#emwi-media-new-panel').hide();
    });
    e.preventDefault();
  });
});
