#!/usr/bin/php
<?
  include( 'phpapilib.php' );
  
  login();
  
  $f = fopen( 'datenbank_bbss_hab.csv', 'r' );
  $i = 0;
  while( !feof( $f ) ) {
    $line = fgets( $f );
    $data = explode( '|', $line );
    foreach( $data as $id => $content ) {
      $data[$id] = trim( $content );
    }
    
    $i++;
    $titel = $data[1];
    
    echo $titel."\n";
    rem_article( $titel, 'Entfernen aller Artikel aus BBSS, vom 2011-02-06' );
  }
  
  fclose( $f );
?>