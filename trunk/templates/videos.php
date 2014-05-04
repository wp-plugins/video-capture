<div class="wrap">

  <h2>WP Video Capture</h2>
  <h4>Brought to you by <a href="http://vidrack.com" target="_blank">vidrack.com</a></h4>
  <h3>WP Video Capture Recorded Videos</h3>

  <form id="videos-filter" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <?php $video_list_table->display() ?>
  </form>

</div>
