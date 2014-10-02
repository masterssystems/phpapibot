<?
  include( 'phpapilib.php' );
  login();
  
  # open directory
  $d = opendir( 'articles' );
  # iterate through the files
  while( $title_url = readdir( $d ) ) {
    if( $title_url != '.' && $title_url != '..' ) {
      $title_ube = str_replace( '_', ' ', $title_url );
      echo $title_url.' -> '.$title_ube."\n";
      $text = file_get_contents( 'articles/'.$title_url );
      put_article( $title_ube, $text, 'Import, Vorbereitet und Zusammengestellt aus Wikipedia und BBSS' );
    }
  }
  closedir( $d );
?>
