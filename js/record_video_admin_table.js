jQuery(function() {

  // Ask if we really want to delete a video
  jQuery('.wp-video-capture-delete-video').on('click', function() {
    return confirm('Do you really want to delete this video? This cannot be undone.');
  });
});
