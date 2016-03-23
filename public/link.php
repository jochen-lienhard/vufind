<?php

// TODO: Logging

// TODO: EDS Bookmark-Links id=eds-<db>-<an>
// TODO: Umstellung IPS auf /link/bookmark/id
// TODO: Umstellung panel_olix, searchplugin

// TDDO: Links auf Suchmasken?

// TODO: Formular zum Erstellen von Links bei Aufruf ohne Parameter?

$languages = array('de',
		   'en');

// id und rn werden in Expertensuche umgesetzt (siehe jumptoQuery)
$fields = array('simple' => 'allfields',
		'expert' => 'ex',
		'neu' => '',
		'au' => 'au',
		'ct' => 'ct',
		'co' => 'co',
		'pu' => 'pu',
		'id' => '',
		'rn' => '',
		'sb' => 'sb',
		'si' => 'si',
		'ss' => 'ss',
		'ta' => 'ta',
		'ti' => 'ti');

if(isset($_GET['debug'])) {
  $debug = true;
  unset($_GET['debug']);
} else {
  $debug = false;
}

if(isset($_GET['location'])) {
  $location = $_GET['location'];
  unset($_GET['location']);
  if(!preg_match('/^[a-z]+$/', $location)) {
    errorMessage("Location '{$location}' ist nicht zulässig");
  }
  $location = "/{$location}";
} else {
  // TODO: lese Pfad aus server - link aus.
  $location = $_SERVER["CONTEXT_PREFIX"];
}

if(isset($_GET['pathinfo']))
  pathInfoLink($location);
else if(count($_GET) > 0)
  queryStringLink($location);
else
  httpRedirect($location);

function pathInfoLink($location) {
  if($GLOBALS['debug'])
    print("pathInfoLink<br/>\n");

  $pathinfo = $_GET['pathinfo'];
  unset($_GET['pathinfo']);
  
  $p = explode('/', $pathinfo);
  if(count($p) > 3)
    errorMessage('zu viele durch / getrennte Parameter');

  if ($pathinfo == 'neu')
    $query = urldecode(http_build_query($_GET));
  else
    $query = key($_GET);
 
  if(query == '')
    errorMessage('Suchanfrage fehlt');

  $field = mb_strtolower(array_pop($p));
  if(!isset($GLOBALS['fields'][$field]))
    errorMessage("Suchfeld '{$field}' ist nicht zulässig");

  $jt = array('protocol' => isset($_SERVER['HTTPS']) ? 'https' : 'http',
              'hostname' => $_SERVER['SERVER_NAME'],
              'location' => $location,
	      'path' => '/RDSIndex/Search',
	      'field' => $field,
	      'query' => $query);
  if(count($p) == 2) {
    $jt['language'] = $p[0];
    $jt['source'] = $p[1];
  } else if(count($p) == 1) {
    if(in_array($p[0], $GLOBALS['languages']))
      $jt['language'] = $p[0];
    else
      $jt['source'] = $p[0];
  }

  jumptoQuery($jt);
}


function queryStringLink($location) {
  if($GLOBALS['debug'])
    print('queryStringLink: ' . $_SERVER['QUERY_STRING'] . "<br/>\n");

  $jt = array('protocol' => isset($_SERVER['HTTPS']) ? 'https' : 'http',
              'hostname' => $_SERVER['SERVER_NAME'],
              'location' => $location,
	      'path' => '/RDSIndex/Search',
      	      'field' => '',
	      'query' => '');

  foreach($_GET as $key => $value) {
    if($value === '') {
      if($key == 'simple') {
        // leere Suchanfragen z.B. von der Homepage akzeptieren
        continue;
      }
      errorMessage("Wert für Parameter '{$key}' fehlt");
    }

    if($GLOBALS['debug'])
      print("'{$key}' => '{$value}'<br/>\n");

    switch($key) {
      case 'searchSelection':
        if($value == 'website') {
          $jt['hostname'] = 'www.ub.uni-freiburg.de';
          $jt['path'] = '/Web/Results';
        }
        break;
      case 'debug':
	break;
      case 'language':
      case 'LANGUAGE':
	$jt['language'] = $value;
	break;
      case 'source':
      case 'SOURCE':
      case 'D_SOURCE':
	$jt['source'] = $value;
	break;
      case 'start':
	if(!preg_match('/^[0-9]+$/', $value))
	  errorMessage("Wert '{$value}' ist für Parameter start nicht zulässig");
	$ivalue = (int)$value;
	if($ivalue < 1)
	  errorMessage("Wert '{$value}' ist für Parameter start nicht zulässig");
	$jt['start'] = $ivalue;
	break;
      case 'count':
      case 'D_COUNT':
	if(!in_array($value, array('10', '20', '50')))
	  errorMessage("Wert '{$value}' ist für Parameter count nicht zulässig");
	$jt['count'] = (int)$value;
	break;
      case 'fbt':
        $jt['fbt'] = $value;
        break;
      case 'zj':
        $jt['zj_facet'] = $value;
	break;
      default:
	if(!isset($GLOBALS['fields'][$key]))
	  errorMessage("Suchfeld '{$key}' ist nicht zulässig");
	if($jt['field'] != '')
	  errorMessage('Angabe mehrerer Suchfelder ist nicht zulässig');
	$jt['field'] = $key;
	$jt['query'] = $value;
	break;
    }
  }

  jumptoQuery($jt);
}


function jumptoQuery($jt) {
  if(!isset($jt['location']))
    errorMessage("Location fehlt");
  if(isset($jt['language']) &&
     !in_array($jt['language'], $GLOBALS['languages']))
    errorMessage("Sprache '{$jt['language']}' ist nicht zulässig");

  if($GLOBALS['debug'])
    foreach($jt as $key => $value)
      print("{$key}: {$value}<br/>\n");
  if(isset($_SERVER['HTTP_REFERER']))
    $referer = $_SERVER['HTTP_REFERER'];
  else
    $referer='';

  // Umsetzung von id, rn und neu in Expertensuche
  if($jt['field'] == 'id' ||
     $jt['field'] == 'rn') {
    $jt['query'] = $jt['field'] . ':' . $jt['query'];
    $jt['field'] = 'expert';
  } else if($jt['field'] == 'neu') {
    $jt['source']='neu';
    if (preg_match('/(dbs\=)(.*)(&weeks\=)(.*)/',$jt['query'],$matches)){
    $dbs = $matches[2];
    $weeks = $matches[4];
    $weekSeconds = 7 * 24 * 60 * 60;
    $now = time();
    $end = substr(date('oW', $now - $weekSeconds),2);
    $start = substr(date('oW', $now - $weeks * $weekSeconds),2);
    $jt['query'] = "dbs:{$dbs} AND dbs_date:[{$start} TO {$end}]";
    $jt['field'] = 'expert';
    if($referer)
      $jt['back'] = $referer;
    } else {
      preg_match('/(zj\=)(.*)(&weeks\=)(.*)/',$jt['query'],$matches);
      $dbs = $matches[2];
      $weeks = $matches[4];
      $weekSeconds = 7 * 24 * 60 * 60;
      $now = time();
      $end = substr(date('oW', $now - $weekSeconds),2);
      $start = substr(date('oW', $now - $weeks * $weekSeconds),2);
      $jt['query'] = "zj:{$dbs} AND dbs_date:[{$start} TO {$end}]";
      $jt['field'] = 'expert';
      if($referer)
        $jt['back'] = $referer;
    }
  }

  if ($jt['field'] == 'simple') {
    $p = array('lookfor=' . rawurlencode($jt['query']));
  } else {
    // Suchfeld ist eines aus der Advanced Liste
    $p = array('join=AND&type0[]=' . $GLOBALS['fields'][$jt['field']] . '&lookfor0[]=' . rawurlencode($jt['query']) . '&bool0[]=AND');
  }
  if(isset($jt['source']))
    $p[] = 'source=' . rawurlencode(mb_strtolower($jt['source']));
 /* WIRD ZUR ZEIT NICHT UNTERSTUETZT
  if(isset($jt['language']))
    $p[] = 'DP_LANGUAGE=' . $jt['language'];
 */

  if(isset($jt['start'])) {
    if(isset($jt['count'])) {
       $p[] = 'page=' . ceil($jt['start']/$jt['count']);
    } else {
         $p[] = 'page=' . ceil($jt['start']/10);
    }
  }
  if(isset($jt['count']))
    $p[] = 'limit='. $jt['count'];
  if(isset($jt['fbt']))
    $p[] = 'fbt=' . rawurlencode($jt['fbt']);

  if(isset($jt['zj_facet']))
    $p[] = 'filter[]=zj_facet:' . rawurlencode(mb_strtolower($jt['zj_facet']));

  if(isset($jt['back']))
    $p[] = 'back='. $jt['back'];

  httpRedirect($jt['protocol'] . '://' . $jt['hostname'] . $jt['location'] . $jt['path'] . '?' . implode('&', $p));
}


function httpRedirect($url) {
  if($GLOBALS['debug'])
    print("<br/>\n<a href=\"" . $url . '" target="_blank">' . $url . '</a>');
  else
    header('Location: ' . $url);
  exit(0);
}


function errorMessage($message) {
  print('Die Suchanfrage konnte leider nicht auf die neue Version des <a href="http://katalog.ub.uni-freiburg.de/opac/">Katalog plus</a> umgesetzt werden.');
  print('<br/><br/>Fehler in der Linksyntax: ' . htmlspecialchars($message));

  $ip = $_SERVER['REMOTE_ADDR'];
  $date = date('Y-m-d H:i:s');
  $uri = $_SERVER['REQUEST_URI'];
  $referer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-');
  error_log("{$date}\t{$ip}\t{$uri}\t{$referer}\n", 3, '/var/log/rdsui/link.log');

  exit(5);
}

?>
