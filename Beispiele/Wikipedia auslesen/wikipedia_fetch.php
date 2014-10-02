#!/usr/bin/php
<?
  include( 'phpapilib.php' );
  
  $f1 = fopen( 'autoren-aleph.txt', 'r' );
  $i = 0;
  while( !feof( $f1 ) ) {
    $line = fgets( $f1 );
    $data = explode( ', ', $line );
    foreach( $data as $id => $content ) {
      $data[$id] = trim( $content );
    }
    
    $titel_wp = $data[1].' '.$data[0];
    $titel_ube = $data[0].', '.$data[1];
    $titel_url = str_replace( ' ', '_', $titel_ube );
    $text = get_article( $titel_wp );
    if( $text ) {
      $info = get_info( $titel_wp );
      echo $titel_wp.' (rev '.$info['lastrevid'].') -> '.$titel_ube."\n";
      $text .= "\n\n";
      $text .= '{{Quelle|QUELLE=Wikipedia|DATUM='.date( 'd.m.Y' ).'|TITLE='.$titel_wp.'|REVID='.$info['lastrevid'].'}}'."\n\n";
      $text .= '== Weblinks UB Bern =='."\n";
      $text .= '{{AlephLink|'.$data[0].'|'.$data[1].'}}';
      $text .= "\n\n";
#      echo $text;
      $f2 = fopen( 'articles/'.$titel_url, 'w' );
      fputs( $f2, $text );
      fclose( $f2 );
    }
  }
  
  fclose( $f1 );
?>