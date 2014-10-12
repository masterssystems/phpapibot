<?php
  include( '../../phpapilib.php' );
  $error = '';
  
  # check parameters
  if( $argc == 3 ) {
    # read parameters
    $filepath = $argv[1];
    $text = $argv[2];
    
    # check local file
    if( !is_readable( $filepath ) ) $error .= 'file "'.$filepath.'" is not readable'."\n";
    # check text - if it is a file read contents
    if( is_readable( $text ) ) $text = file_get_contents( $text );
  } else {
    # parameter count doesn't match
    $error .= 'script expects two paramters'."\n";
  }
  
  if( $error == '' ) {
    # login
    login();
    $filename = basename( $argv[1] );
    # upload file - 2nd parameter could be $filename, when NULL the basename will be taken
    upload_file( $filename, NULL, $text );
  } else {
    # deal with errors
    
    echo $error;
    echo "\n";
    echo 'usage: '.$argv[0].' filename text'."\n";
    echo '* filename: local file you want to upload'."\n";
    echo '* text: text to be put on the file description page.'."\n";
    echo '** either: the text itself (string - do not forget to "quote" or escape)'."\n";
    echo '** or    : the filename where the text can be read from'."\n";
  }
?>
