<div style="text-align: <?php echo $align ?>;">

  <!-- Mobile Version -->
  <div class="wp-video-capture-mobile">
    <form class="wp-video-capture-mobile-form" method="post" action="http://upload.vidrack.com/video">
      <div class="wp-video-capture-ajax-success-store" style="display: none;"></div>
      <div class="wp-video-capture-ajax-success-upload" style="display: none;"></div>
      <div class="wp-video-capture-ajax-error-store" style="display: none;"></div>
      <div class="wp-video-capture-ajax-error-upload" style="display: none;"></div>
      <div class="wp-video-capture-progress-container" style="display: none;">
        <p>Uploading...</p>
        <progress class="wp-video-capture-progress" value="0" max="100"></progress>
      </div>
      <a href="#" class="wp-video-capture-record-button-mobile"><img src="<?php echo plugin_dir_url(__FILE__) ?>../images/button_record.jpg"></a>
      <input class="wp-video-capture-file-selector" type="file" accept="video/*" capture="camera" style="display: none;" />

      <div class="wp-video-capture-terms-and-conditions" style="display: none;">
        <label>
          <input class="wp-video-capture-tnc-checkbox" type="checkbox" />
          <a class="wp-video-capture-tnc-link" href="#">Terms and Conditions</a>
        </label>
      </div>

      <a href="#" class="wp-video-capture-upload-button" style="display: none;"><img src="<?php echo plugin_dir_url(__FILE__) ?>../images/button_upload.png"></a>
    </form>
  </div>

  <!-- Desktop Version -->
  <div class="wp-video-capture-desktop" style="display: none;">
    <div class="wp-video-capture-flash-container" style="display: none;">
      <div id="wp-video-capture-flash">
        <p>Your browser doesn't support Adobe Flash, sorry...</p>
      </div>
    </div>

    <a href="#" class="wp-video-capture-record-button-desktop"><img src="<?php echo plugin_dir_url(__FILE__) ?>../images/button_record.jpg"></a>
  </div>

</div>
