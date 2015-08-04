jQuery(function() {

  // UUID generator
  function generateUUID() {
    var d = Date.now();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = (d + Math.random()*16)%16 | 0;
      d = Math.floor(d/16);
      return (c=='x' ? r : (r&0x3|0x8)).toString(16);
    });
    return uuid;
  };

  // Detect if we're on desktop or mobile
  if (!VideoCapture.mobile) {
    jQuery('.wp-video-capture-mobile').hide();
    jQuery('.wp-video-capture-desktop').show();

    // Display popup window on desktop
    if (VideoCapture.window_modal) {
      jQuery('.wp-video-capture-flash-container').addClass('wp-video-capture-flash-container-popup');
      jQuery('a.wp-video-capture-record-button-desktop').magnificPopup({
        type: 'inline',
        preloader: false,
        callbacks: {
          beforeOpen: function() { jQuery('#wp-video-capture-flash-block').show(); },
          afterClose: function() { jQuery('#wp-video-capture-flash-block').hide(); }
        }
      });
    }
  }

  // Desktop "Record" button
  jQuery('.wp-video-capture-record-button-desktop').click(function(e) {

    // Pass SWF Video Player params
    var flashvars = {
      ajaxurl: VideoCapture.ajaxurl,
      ip: VideoCapture.ip,
      site_name: VideoCapture.site_name,
      backLink: VideoCapture.display_branding
    };

    // Embed SWFObject
    swfobject.embedSWF(
      VideoCapture.plugin_url + 'lib/swf/recorder.swf',
      'wp-video-capture-flash',
      '600',
      '400',
      '10.0.0',
      '',
      flashvars
    );

    if (!VideoCapture.window_modal) {
      // Show SWF container
      jQuery(this).closest('div').find('.wp-video-capture-flash-container').show();

      // Hide the button
      jQuery(this).hide();

      e.preventDefault();
      e.stopPropagation();
    }
  });

  // Mobile "Record" button
  jQuery('.wp-video-capture-record-button-mobile').click(function(event) {
    jQuery(this).closest('div').find('.wp-video-capture-file-selector').click();
    event.preventDefault();
    event.stopPropagation();
  });

  // Submit video automatically after file has been selected
  jQuery('.wp-video-capture-file-selector').on('change', function() {
    if (jQuery(this).val()) {
      submitVideo(jQuery(this).closest('div'));
    }
  });

  // Bind to upload button click
  function submitVideo(d) {
    d.find('.wp-video-capture-ajax-success-store').hide();
    d.find('.wp-video-capture-ajax-success-upload').hide();
    d.find('.wp-video-capture-ajax-error-store').hide();
    d.find('.wp-video-capture-ajax-error-upload').hide();
    d.find('.wp-video-capture-progress-container').show();
    d.find('.wp-video-capture-progress-text').show();

    var form = d.find('.wp-video-capture-mobile-form');
    var got_file = d.find('.wp-video-capture-file-selector').val().replace(/.*(\/|\\)/, '');

    // Get extension before sanitizing file name
    var ext_re = /(?:\.([^.]+))?$/;
    var ext = ext_re.exec(got_file)[1];

    // Sanitize filename
    var filename =
      VideoCapture.site_name + '_' +
      generateUUID() +
      '.' + ext.toLowerCase();

    var form_data = new FormData();
    form_data.append('filename', filename);
    form_data.append('video', d.find('.wp-video-capture-file-selector')[0].files[0]);

    // Store video on the server
    jQuery.ajax({
      url: form.attr('action'),
      type: 'POST',
      contentType: false,
      data: form_data,
      async: true,
      cache: false,
      processData: false,

      // Progress indicator
      xhr: function() {
        myXhr = jQuery.ajaxSettings.xhr();
        if (myXhr.upload) {
          myXhr.upload.addEventListener(
            'progress',
            function(event) {
              var progress = Math.round(event.loaded / event.total * 100);
              d.find('.wp-video-capture-progress').val(progress);
              d.find('.wp-video-capture-progress-text').find('span').html(progress);
            },
            false
          );
        }
        return myXhr;
      },

      // AJAX error
      error: function(jqXHR, textStatus) {
        d.find('.wp-video-capture-ajax-error-upload').html('Error uploading video (AJAX): ' + textStatus);
        d.find('.wp-video-capture-ajax-error-upload').show();
      },

      success: function(data, textStatus, jqXHR) {
        if (data.status == 'success') {
          d.find('.wp-video-capture-ajax-success-upload').html('Success uploading video: ' + data.message);
          d.find('.wp-video-capture-ajax-success-upload').show();

          // Store video info in Wordpress DB
          jQuery.post(
            VideoCapture.ajaxurl,
            {
              'action': 'store_video_file',
              'filename': filename,
              'ip': VideoCapture.ip
            }
          ).done(function(data) {
            if (data.status === 'success') {
              d.find('.wp-video-capture-ajax-success-store').html('Success storing video: ' + data.message);
              d.find('.wp-video-capture-ajax-success-store').show();
            } else {
              d.find('.wp-video-capture-ajax-error-store').html('Error storing video: ' + data.message);
              d.find('.wp-video-capture-ajax-error-store').show();
            }
          }).fail(function(jqXHR, textStatus) {
            d.find('.wp-video-capture-ajax-error-store').html('Error storing video (AJAX): ' + textStatus);
            d.find('.wp-video-capture-ajax-error-store').show();
          });

        } else {
          d.find('.wp-video-capture-ajax-error-upload').html('Error uploading video: ' + data.message);
          d.find('.wp-video-capture-ajax-error-upload').show();
        }
      },

      complete: function() {
        d.find('.wp-video-capture-file-selector').val('');
        d.find('.wp-video-capture-progress-container').hide();
      }
    });
  };
});
