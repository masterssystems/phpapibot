<?php
  include( '../../phpapilib.php' );

  # configuration
  $vote_catgerories = array( 
    1 => 'Dokumentation und Interview',
    2 => 'Animation',
    3 => 'Experiment und Remix'
  );
  $vote_category_pages = array( 
    1 => 'Commons:Videos für Wikipedia-Artikel/2014/Abstimmung/Dokumentation und Interview', 
    2 => 'Commons:Videos für Wikipedia-Artikel/2014/Abstimmung/Animation', 
    3 => 'Commons:Videos für Wikipedia-Artikel/2014/Abstimmung/Experiment und Remix' 
  );
  $vote_list_prefix = array( 
    1 => 'Commons:Videos für Wikipedia-Artikel/2014/R1/v/',
    2 => 'Commons:Videos für Wikipedia-Artikel/2014/R1/v/',
    3 => 'Commons:Videos für Wikipedia-Artikel/2014/R1/v/'
  );
  $vote_file_indicator = 'votebutton';		# identifies a candidate on the category pages - needed to extract the list of candidates
  $vote_user_indicator = '# [[User:';		# identifies a vote on the vote pages - needed to extract the number of votes
  $result_page = 'Commons:Videos für Wikipedia-Artikel/2014/Ergebnis';

  # prepare
  $files = array();
  $vote_total = 0;
  $vote_category = array( 
    1 => 0,
    2 => 0,
    3 => 0
  );
  login();
  
  # 1st: grep list of candidates
  foreach( $vote_category_pages as $id => $page ) {
    # get full article
    $text = get_article( $page );
    # find the relevant content
    foreach( preg_split( '/((\r?\n)|(\r\n?))/', $text ) as $line ) {
      # find lines with candidates
      if( stripos( $line, $vote_file_indicator ) !== false ) {
        # extract first parameter of gallery
        $line = explode( '|', $line );
        # store filename as index in result array
        $files[$line[0]]['category'] = $id;
        
      }
    }
  }

  # 2nd: count the votes per candidate
  foreach( $files as $filename => $store ) {
    # prepare
    $files[$filename]['votes'] = 0;
    # read the candidates vote page
    $text = get_article( $vote_list_prefix[$store['category']].$filename );
    # find the relevant content
    foreach( preg_split( '/((\r?\n)|(\r\n?))/', $text ) as $line ) {
      # find lines with votes
      if( stripos( $line, $vote_user_indicator ) !== false ) {
        # count
        $files[$filename]['votes']++;
        $vote_total++;
        $vote_category[$store['category']]++;
      }
    }
  }

  # 3rd: create result table
#  sort( $files );
  $text  = '{|class="wikitable sortable"'."\n";
  $text .= '!File !! Category !! Votes !! % Category !! % Total'."\n";
  foreach( $files as $filename => $store ) {
    $text .= '|-'."\n";
    $text .= '| [[:File:'.$filename.'|'.$filename.']]'."\n";
    $text .= '| [['.$vote_category_pages[$store['category']].'|'.$vote_catgerories[$store['category']].']]'."\n";
    $text .= '|style="text-align:right;"| [['.$vote_list_prefix[$store['category']].$filename.'|'.$store['votes'].']]'."\n";
    $text .= '|style="text-align:right;"| '.round( $store['votes'] / $vote_category[$store['category']] * 100, 1 )."\n";
    $text .= '|style="text-align:right;"| '.round( $store['votes'] / $vote_total * 100, 1 )."\n";
  }
  $text .= '|-'."\n";
  $text .= '| '.count( $files )."\n";
  $text .= '| '.count( $vote_catgerories )."\n";
  $text .= '| '.$vote_total."\n";
  $text .= '|colspan="2" style="text-align:right;"| 100'."\n";
  $text .= '|}'."\n";
#  echo $text;
#  var_dump( $files );

  # publish result
  put_article( $result_page, $text, 'updated results from '.date( 'Y-m-d H:i:s') );
?>
