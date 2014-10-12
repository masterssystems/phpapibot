<?php
  include( '../../phpapilib.php' );
  
  if( !isset( $argv[1] ) ) {
    echo 'please specify local file'."\n";
    exit( 1 );
  }

  # check if 1st argument is a file
  if( !file_exists( $argv[1] ) ) {
    echo 'file is not readable'."\n";
    exit( 127 );
  }
  
  # login
  login();
  $filename = basename( $argv[1] );
  echo $filename;
  # upload file - 2nd parameter could be $filename, when NULL the basename will be taken
  upload_file( $argv[1], NULL, 'upload-text' );
?>
