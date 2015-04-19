<div style="text-align: <?php echo $align ?>;">

  <!-- Mobile Version -->
  <div class="wp-video-capture-mobile">
    <form class="wp-video-capture-mobile-form" method="post" action="http://storage.vidrack.com/video">
      <div class="wp-video-capture-ajax-success-store" style="display: none;"></div>
      <div class="wp-video-capture-ajax-success-upload" style="display: none;"></div>
      <div class="wp-video-capture-ajax-error-store" style="display: none;"></div>
      <div class="wp-video-capture-ajax-error-upload" style="display: none;"></div>
      <div class="wp-video-capture-progress-container" style="display: none;">
        <p>Uploading...</p>
        <progress class="wp-video-capture-progress" value="0" max="100"></progress>
      </div>
      <a href="#" class="wp-video-capture-record-button-mobile"></a>
      <input class="wp-video-capture-file-selector" type="file" accept="video/*" capture="camera" style="display: none;" />

      <div class="wp-video-capture-terms-and-conditions" style="display: none;">
        <label>
          <input class="wp-video-capture-tnc-checkbox" type="checkbox" />
          <a href="#" class="wp-video-capture-tnc-link"> Terms and Conditions</a>
        </label>
      </div>

      <a href="#" class="wp-video-capture-upload-button" style="display: none;"></a>
    </form>
  </div>

  <!-- Desktop Version -->
  <div class="wp-video-capture-desktop" style="display: none;">
    <div class="wp-video-capture-flash-container" id="wp-video-capture-flash-block" style="display: none;">
      <div id="wp-video-capture-flash">
        <p>Your browser doesn't support Adobe Flash, sorry...</p>
      </div>
    </div>

    <a href="#" data-mfp-src="#wp-video-capture-flash-block" class="wp-video-capture-record-button-desktop"></a>
  </div>

</div>
