<?php
  require_once( 'config.php' );
  if( !defined( 'BOTNAME' ) ) define( 'BOTNAME', 'PHPAPIlib, by Manuel Schneider' );
  
  function sendcmd( $get, $post = false ) {
    if( is_array( $get ) ) {
      $action = '?';
      foreach( $get as $name => $value ) {
        $action .= $name.'='.$value.'&';
      }
      $action .= 'format=php';
    } elseif( $get ) {
      $action = '?action='.$get.'&format=php';
    } else {
      $action = '?format=php';
    }

    if( defined( 'CONVERTPOST' ) ) {
      # POST-Variable in GET-String umbauen
      if( is_array( $post ) ) {
        $poststr = false;
        foreach( $post as $name => $value ) {
          if( $poststr ) $poststr .= '&';
          $poststr .= $name.'='.$value;
        }
      }
    }
    $poststr = $post;
    
    # bereite Cookie-Datei vor
    touch( 'cookies.txt' );
    $file = realpath( 'cookies.txt' );
    # Setze Parameter zum HTTP-Aufruf
    $c = curl_init( URL.$action );
    curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $c, CURLOPT_ENCODING, 'UTF-8' );
    curl_setopt( $c, CURLOPT_USERAGENT, BOTNAME );
    curl_setopt( $c, CURLOPT_POST, true );
    curl_setopt( $c, CURLOPT_POSTFIELDS, $poststr );
    curl_setopt( $c, CURLOPT_CONNECTTIMEOUT, 10 );
    curl_setopt( $c, CURLOPT_COOKIEJAR, $file );
    curl_setopt( $c, CURLOPT_COOKIEFILE, $file );
    curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
    if( defined( 'DEBUG' ) ) {
      curl_setopt( $c, CURLOPT_VERBOSE, true );
      #curl_setopt( $c, CURLOPT_HEADER, true ); # Achtung, fügt Header-Daten in den Rückgabewert ein - bricht die Verarbeitung der Daten
      curl_setopt( $c, CURLINFO_HEADER_OUT, true );
      echo URL.$action.' ('.$poststr.')'."\n";
      var_dump( $poststr );
    }
    # Führe Aufruf durch
    $r = curl_exec( $c );
    curl_close( $c );
    if( defined( 'DEBUG' ) ) {
      echo 'DEBUG: result string = '.$r."\n";
    }
    $r = unserialize( $r );
    if( defined( 'DEBUG' ) ) {
      echo 'DEBUG: unserialized object = ';
      var_dump( $r );
    }
    return $r;
  }
  
  # Standard ist leer, dh. false. Damit startet der Login-Vorgang.
  function login( $r = false ) {
    if( !$r ) {
      echo 'LOGIN: fetch token'."\n";
      $r = sendcmd( 'login', array( 'lgname' => USERNAME, 'lgpassword' => PASSWORD ) );
      if( $r ) return login( $r );
      else echo 'LOGIN: low-level error while fetching token - check if API works at all'."\n";
    } else {
      switch( $r['login']['result'] ) {
        case 'NeedToken':
          echo 'LOGIN: NeedToken - confirming with token'."\n";
          $r = sendcmd( 'login', array( 'lgname' => USERNAME, 'lgpassword' => PASSWORD, 'lgtoken' => $r['login']['token'] ) );
          if( $r ) return login( $r );
          else echo 'LOGIN: low-level error confirming token - did you create cookies.txt?'."\n";
          break;
        case 'Success':
          echo 'LOGIN: Success'."\n";
          return $r;
          break;
        default:
          echo 'LOGIN: Error '.$r['login']['result']."\n";
          return false;
      }
    }
  }
  
  function logout() {
    echo 'LOGOUT:'."\n";
    return sendcmd( 'logout' );
  }
  
  function get_article( $article = 'Main Page' ) {
    $r = sendcmd( '', array( 'action' => 'query', 'titles' => $article, 'prop' => 'revisions', 'rvprop' => 'content' ) );
    if( isset( $r['query']['pages'][-1] ) ) {
      echo 'READ: Error - article does not exist'."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'READ: Success'."\n";
      return $r['query']['pages'][$page[0]]['revisions'][0]['*'];
    }
    return $r;
  }
  
  function get_info( $article = 'Main Page' ) {
    $r = sendcmd( '', array( 'action' => 'query', 'titles' => $article, 'prop' => 'info' ) );
    if( isset( $r['query']['pages'][-1] ) ) {
      echo 'INFO: Error - article does not exist'."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'INFO: Success'."\n";
      return $r['query']['pages'][$page[0]];
    }
    return $r;
  }
  
  function get_langlinks( $article = 'Main Page' ) {
    $r = sendcmd( '', array( 'action' => 'query', 'titles' => $article, 'prop' => 'langlinks', 'lllimit' => 100 ) );
    if( isset( $r['query']['pages'][-1] ) ) {
      echo 'INFO: Error - article does not exist'."\n";
      return false;
    } elseif( isset( $r['query']['pages'] ) ) {
      $page = array_keys( $r['query']['pages'] );
      $lang = array();
      echo 'LANGLINKS: Success'."\n";
      if( isset( $r['query']['pages'][$page[0]]['langlinks'] ) ) {
        echo 'LANGLINKS: links exist'."\n";
        foreach( $r['query']['pages'][$page[0]]['langlinks'] as $entry ) {
          $lang[$entry['lang']] = $entry['*'];
        }
        return $lang;
      } else {
        echo 'LANGLINKS: no results'."\n";
        return '';
      }
    }
    return $r;
  }
  
  function wbget_sitelinks( $id = '' ) {
    $r = sendcmd( '', array( 'action' => 'wbgetentities', 'ids' => $id, 'props' => 'sitelinks' ) );
    if( isset( $r['error'] ) ) {
      echo 'INFO: Error - entity does not exist'."\n";
      return false;
    } elseif( isset( $r['entities'][$id] ) ) {
      echo 'SITELINKS: Success'."\n";
      if( isset( $r['entities'][$id]['sitelinks'] ) ) {
        echo 'SITELINKS: links exist'."\n";
        foreach( $r['entities'][$id]['sitelinks'] as $entry ) {
          $lang[$entry['site']] = $entry['title'];
        }
        return $lang;
      } else {
        echo 'SITELINKS: no results'."\n";
        return '';
      }
    }
  }

  function wbget_label( $id = '', $lang = 'en' ) {
    $r = sendcmd( '', array( 'action' => 'wbgetentities', 'ids' => $id, 'props' => 'labels', 'langages' => $lang ) );
    if( isset( $r['error'] ) ) {
      echo 'INFO: Error - entity does not exist'."\n";
      return false;
    } elseif( isset( $r['entities'][$id] ) ) {
      echo 'LABEL: Success'."\n";
      if( isset( $r['entities'][$id]['labels'][$lang] ) ) {
        echo 'LABEL: labels exist'."\n";
        return $r['entities'][$id]['labels'][$lang]['value'];
      } else {
        echo 'LABEL: no results'."\n";
        return '';
      }
    }
  }

  function wbget_claims( $id = '', $lang = 'en' ) {
    $r = sendcmd( '', array( 'action' => 'wbgetentities', 'ids' => $id, 'props' => 'claims', 'languages' => $lang ) );
    if( isset( $r['error'] ) ) {
      # return array is error message
      echo 'INFO: Error - entity does not exist'."\n";
      return false;
    } elseif( isset( $r['entities'][$id] ) ) {
      # return array holds requested ID
      echo 'CLAIMS: Success'."\n";
      if( isset( $r['entities'][$id]['claims'] ) ) {
        # walk through claims
        foreach( $r['entities'][$id]['claims'] as $pid => $data ) {
          # resolve property ID into label
          $label = wbget_label( $pid );
          # extract value array
          $data = $data[0]['mainsnak']['datavalue']['value'];
          # investigate value array and find actual values based on format
          if( isset( $data['entity-type'] ) ) {
            switch( $data['entity-type'] ) {
              case 'item':
                # value is a reference to another ID - resolve
                $value = wbget_label( 'Q'.$data['numeric-id'] );
                break;
            }
          } elseif( !is_array( $data ) ) {
            # is just simple value
            $value = $data;
          } elseif( $data['time'] ) {
            # value is time
            $value = $data['time'];
          }
          # build return array
          $properties[$pid]['value'] = $value;
          $properties[$pid]['label'] = $label;
        }
        # return all values
        return $properties;
      } else {
        echo 'CLAIMS: no results'."\n";
        return '';
      }
    }
  }

  function wbset_claim( $id, $pid, $value, $summary = '', $r = false ) {
    if( !$r ) {
      echo 'SET CLAIM: fetch token'."\n";
      return  wbset_claim( $id, $pid, $value, $summary, sendcmd( 'query', array( 'titles' => $id, 'prop' => 'info|revisions', 'intoken' => 'edit' ) ) );
    } elseif( array_key_exists( 'warnings', $r ) ) {
      echo 'SET CLAIM: warnings '.$r['warnings']['info']['*']."\n";
      return false;
    } elseif( array_key_exists( 'error', $r ) ) {
      echo 'SET CLAIM: error '.$r['error']['info']."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      echo 'SET CLAIM: '.$r['query']['pages'][$page[0]]['edittoken']."\n";
      $r = sendcmd( '', array( 'action' => 'wbcreateclaim', 'entity' => $id, 'snaktype' => 'value', 'property' => $pid, 'value' => $value, 'token' => $r['query']['pages'][$page[0]]['edittoken'] ) );
    } elseif( $r['edit']['result'] == 'Failure' ) {
      echo 'SET CLAIM: Error '.$r['edit']['result']."\n";
      return false;
    } elseif( $r['edit']['result'] == 'Success' ) {
      echo 'SET CLAIM: Success'."\n";
      return $r;
    }
  }

  function wbsearch_ids( $name, $lang = 'en' ) {
    $r = sendcmd( '', array( 'action' => 'wbsearchentities', 'search' => $name, 'language' => $lang ) );
    if( isset( $r['error'] ) ) {
      # return array is error message
      echo 'INFO: Error - no search result'."\n";
      return false;
    } elseif( isset( $r['search'] ) ) {
      # return array holds requested ID
      foreach( $r['search'] as $item ) {
        if( isset( $item['id'] ) ) {
          if( isset( $item['label'] ) ) $list[$item['id']]['label'] = $item['label'];
          else $list[$item['id']]['label'] = false;
          if( isset( $item['description'] ) ) $list[$item['id']]['description'] = $item['description'];
          else $list[$item['id']]['description'] = false;
        }
      }
      return $list;
    }
  }
  
  function put_article( $article = 'Main Page', $text = '', $summary = '', $r = false ) {
    if( !$r ) {
      echo 'EDIT: fetch token'."\n";
      return put_article( $article, $text, $summary, sendcmd( 'query', array( 'titles' => $article, 'prop' => 'info|revisions', 'intoken' => 'edit' ) ) );
    } elseif( array_key_exists( 'warnings', $r ) ) {
      echo 'EDIT: warnings '.$r['warnings']['info']['*']."\n";
      return false;
    } elseif( array_key_exists( 'error', $r ) ) {
      echo 'EDIT: error '.$r['error']['info']."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'EDIT: '.$r['query']['pages'][$page[0]]['edittoken']."\n";
      return put_article( $article, $text, $summary, sendcmd( 'edit', array( 'title' => $article, 'text' => $text, 'summary' => $summary, 'token' => $r['query']['pages'][$page[0]]['edittoken'] ) ) );
    } elseif( $r['edit']['result'] == 'Failure' ) {
      echo 'EDIT: Error '.$r['edit']['result']."\n";
      return false;
    } elseif( $r['edit']['result'] == 'Success' ) {
      echo 'EDIT: Success'."\n";
      return $r;
    }
  }

  function rem_article( $article = 'Main Page', $reason = '', $r = false ) {
    if( !$r ) {
      echo 'DELETE: fetch token'."\n";
      return rem_article( $article, $reason, sendcmd( 'query', array( 'titles' => $article, 'prop' => 'info', 'intoken' => 'delete' ) ) );
    } elseif( $r['warnings'] ) {
      echo 'DELETE: warnings '.$r['warnings']['info']['*']."\n";
      return false;
    } elseif( $r['error'] ) {
      echo 'DELETE: error '.$r['error']['info']."\n";
      return false;
    } elseif( $r['query']['pages'] ) {
      $page = array_keys( $r['query']['pages'] );
      echo 'DELETE: '.$r['query']['pages'][$page[0]]['deletetoken']."\n";
      return rem_article( $article, $reason, sendcmd( 'delete', array( 'title' => $article, 'reason' => $reason, 'token' => $r['query']['pages'][$page[0]]['deletetoken'] ) ) );
    } elseif( $r['delete']['result'] == 'Failure' ) {
      echo 'DELETE: Error '.$r['edit']['result']."\n";
      return false;
    } elseif( $r['delete']['result'] == 'Success' ) {
      echo 'DELETE: Success'."\n";
      return $r;
    }
  }
  
  function check_api() {
    sendcmd( false, false );
  }
?>