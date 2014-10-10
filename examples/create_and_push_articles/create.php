#!/usr/bin/php
<?php
  include( '../../phpapilib.php' );
  
  login();
  
  $f1 = fopen( 'database.csv', 'r' );
  $i = 0;
  while( !feof( $f1 ) ) {
    $line = fgets( $f1 );
    $data = explode( '|', $line );
    foreach( $data as $id => $content ) {
      $data[$id] = trim( $content );
    }
    
    $i++;
    $cats = '';
    
    # Namen
    $title_ube = '';
    if( $data[3] ) $title_ube = $data[3];
    if( $data[3] && $data[4] ) $title_ube .= ', ';
    if( $data[4] ) $title_ube .= $data[4];
                       
    $title_wp = $data[1];
    # Einleitung
    $text = "$data[2] ";
    # Geburts- und Todesdaten
    $geb = ''; $tod = '';
    if( $data[13] ) {
      if( $data[15] && $data[14] ) $geb = '* '.$data[15].'.'.$data[14].'.'.$data[13];
      else $geb = '* '.$data[13];
      $cats .= '[[Category:Born '.$data[13].']]'."\n";
    }
    if( $data[16] ) {
      if( $data[18] && $data[17] ) $tod = '† '.$data[18].'.'.$data[17].'.'.$data[16];
      else $tod = '† '.$data[16];
      $cats .= '[[Category:Died '.$data[16].']]'."\n";
    }
    if( $geb || $tod ) {
      $text .= '(';
      if( $geb ) $text .= $geb;
      if( $geb && $tod ) $text .= '; ';
      if( $tod ) $text .= $tod;
      $text .= ')';
    }
    $text .= "\n\n";
    # Beruf
    if( $data[21] ) {
      $text .= 'Profession: '.$data[21];
      $text .= "\n\n";
    }
    # versteckt: Adresse, Heimatort
    if( $data[19] || $data[20] || $data[21] ) {
      $text .= '{{hide|';
      if( $data[19] ) $text .= 'Adress: '.$data[19]."\n";
      if( $data[20] ) $text .= 'Contact Adress: '.$data[20]."\n";
      if( $data[21] ) $text .= 'Home Town: '.$data[21]."\n";
      $text .= '}}';
      $text .= "\n\n";
    }
    # Mitglied im BSV
    if( $data[23] ) {
      $text .= 'Mitglied im Berner Schriftstellerinnen und Schriftsteller Verein (BSV).';
      $text .= "\n\n";
      $cats .= '[[Kategorie:Mitglied des Berner Schriftstellerinnen und Schriftsteller Vereins]]'."\n";
    }
    # Persönlicher Beitrag
    if( $data[24] ) {
      $text .= '== Persönlicher Beitrag =='."\n";
      $text .= str_replace( '\n', "\n", $data[24] );
      $text .= "\n\n";
    }
    # Weblinks UB Bern
    if( $data[3] && $data[4] ) {
      $text .= '== Weblinks =='."\n";
      $text .= '{{AlephLink|'.$data[3].'|'.$data[4].'}}';
      $text .= "\n\n";
    }
    # Quellen
    $text .= '== Sources =='."\n";
    $text .= '<references/>'."\n";
    $text .= '{{Quelle|QUELLE=BBSS|DATUM='.$data[27].'}}';
    $text .= "\n\n";
    # versteckt: interne Notizen
    if( $data[25] ) {
      $text .= '{{hide|'.$data[25].'}}';
      $text .= "\n\n";
    }
    # Kategorien
    if( $cats ) $text .= $cats;
    $text .= '[[Category:A bis Z]]';
    # Normdaten
    if( $data[7] ) {
      $text .= '{{Normdaten|PND='.$data[7].'}}'."\n";
    }
    
    $title_url = str_replace( ' ', '_', $title_ube );
    $f2 = fopen( 'articles/'.$title_url, 'a' );
    fputs( $f2, $text );
    fclose( $f2 );
    echo $title_ube.' -> '.$title_url."\n";
    echo $text."\n\n";
  }
  
  fclose( $f1 );
?>