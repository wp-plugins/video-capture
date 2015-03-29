<?php

class Video_Capture_Email {


	public function __construct( $hostname ) {

    $this->hostname = $hostname;

    // Email headers
    $this->headers[] = 'MIME-Version: 1.0';
    $this->headers[] = 'Content-type: text/html; charset=utf-8';
    $this->headers[] = 'From: Video Recorder Plugin <vidrack@' . preg_replace('/^www\./', '', $hostname) . '>';
    $this->headers[] = 'Reply-to: Vidrack <info@vidrack.com>';

  }

  public function send_registration_email( $registration_email ) {
    wp_mail(
      'info@vidrack.com',
      'Video Recorder plugin registered at ' . $this->hostname,
      '
      <p>Hello,<br/>
      <br/>
      We have a new registration at <strong>' . $this->hostname . '</strong>!<br/>
      Registration email is <strong>' . $registration_email . '</strong><br/>
      ',
      $this->headers
    );

  }

  public function send_new_video_email( $to, $filename ) {
    wp_mail(
      $to,
      'New video recorded at ' . $this->hostname . ' website',
      '
      <p>Hello,<br/>
      <br/>
      You have a new video at ' . $this->hostname . '!<br/>
      <a href="http://vidrack-media.s3.amazonaws.com/' . $filename . '" download>Click here to download</a><br/>
      <br/>
      Kind regards,<br/>
      Vidrack Team<br/>
      <br/>
      <a href="http://vidrack.com" target="_blank">vidrack.com</a>
      ',
      $this->headers
    );
  }

}

?>
