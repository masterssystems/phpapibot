<?php
  include( '../../phpapilib.php' );
  login();
  
  # check if 1st argument is a file
  if( !file_exists( $argv[1] ) {
    echo 'URL is not readable'."\n";
    exit 127;
  }
  
  # login
  login();
  # upload file
#  upload_file_url( 
#      echo $title_url.' -> '.$title_ube."\n";
#      $text = file_get_contents( 'articles/'.$title_url );
#      put_article( $title_ube, $text, 'Import, Vorbereitet und Zusammengestellt aus Wikipedia und BBSS' );
?>
