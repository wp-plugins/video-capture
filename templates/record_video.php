<div style="text-align: <?php echo $align ?>;">

  <!-- Mobile Version -->
  <div class="wp-video-capture-mobile">
    <form class="wp-video-capture-mobile-form" method="post" action="http://upload.vidrack.com/video">
      <div class="wp-video-capture-ajax-success-store"></div>
      <div class="wp-video-capture-ajax-success-upload"></div>
      <div class="wp-video-capture-ajax-error-store"></div>
      <div class="wp-video-capture-ajax-error-upload"></div>
      <div class="wp-video-capture-progress-container">
        <p>Uploading...</p>
        <progress class="wp-video-capture-progress" value="0" max="100"></progress>
      </div>
      <a href="#" class="wp-video-capture-record-button-mobile"><img src="<?php echo plugin_dir_url(__FILE__) ?>../images/button_record.jpg"></a>
      <input class="wp-video-capture-file-selector" type="file" accept="video/*" capture="camera" />
  
      <div class="wp-video-capture-terms-and-conditions">
        <label>
          <input class="wp-video-capture-tnc-checkbox" type="checkbox" />
          <a class="wp-video-capture-tnc-link" href="#">Terms and Conditions</a>
        </label>
      </div>
  
      <a href="#" class="wp-video-capture-upload-button"><img src="<?php echo plugin_dir_url(__FILE__) ?>../images/button_upload.png"></a>
    </form>
  </div>

  <!-- Desktop Version -->
  <div class="wp-video-capture-desktop">
    <div class="wp-video-capture-flash-container">
      <div id="wp-video-capture-flash">
        <p>Your browser doesn't support Adobe Flash, sorry...</p>
      </div>
    </div>
  
    <a href="#" class="wp-video-capture-record-button-desktop"><img src="<?php echo plugin_dir_url(__FILE__) ?>../images/button_record.jpg"></a>
  </div>

</div>
