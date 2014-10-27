<?php
  include( '../../phpapilib.php' );
  
  if( !isset( $argv[1] ) ) {
    echo 'please specify URL'."\n";
    exit( 1 );
  }

  # check if 1st argument is a file
  #if( !file_exists( $argv[1] ) ) {
  #  echo 'URL is not readable'."\n";
  #  exit( 127 );
  #}
  
  # login
  login();
  #$filename = basename( $argv[1] );
  #echo $filename;
  # upload file
  upload_file_url( $argv[1], '2014-10-23 Zeitzeugen - 25 Jahre Berliner Mauerfall - Armin Schuster.webm', 'upload-text' );
?>
