<div class="wrap">
  <h2>Video Recorder</h2>
  <h4>Brought to you by <a href="http://vidrack.com" target="_blank">vidrack.com</a></h4>
  <form method="post" action="options.php">
    <?php settings_errors('registration_email') ?>
    <?php @settings_fields('wp_video_capture-group'); ?>
    <?php @do_settings_fields('wp_video_capture-group'); ?>

    <?php do_settings_sections('wp_video_capture'); ?>

    <?php @submit_button(); ?>
  </form>

  <h2>How to use</h2>
  <p>Add shortcode <strong>[vidrack]</strong> anywhere on the page.</p>
  <p>It accept the following parameters:</p>
  <ul>
    <li>Align to the right: <strong>[vidrack align="right"]</strong></li>
    <li>Align to the center: <strong>[vidrack align="center"]</strong></li>
    <li>Align to the left: <strong>[vidrack align="left"]</strong></li>
  </ul>

</div>
