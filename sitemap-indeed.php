<?php
$SITE_ROOT = "https://api-production.waiter.fr/sitemap";

$jsonData = getData($SITE_ROOT);
makePage($jsonData, $SITE_ROOT);
function getData($siteRoot) {
  $rawData = file_get_contents($siteRoot);
  return json_decode($rawData);
}

function makePage($data, $siteRoot) {

  $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><source><publisher>Waiter</publisher><publisherurl>https://waiter.fr</publisherurl></source>");

  for ($nb_lignes = 0; $nb_lignes < count($data); $nb_lignes++) {

    $date = date_format(date_create($data[$nb_lignes]->created_at), 'D, d M Y H:i:s');

    $datetime1 = date_create($data[$nb_lignes]->created_at);
    $datetime2 = date_create();
    $interval = date_diff($datetime1, $datetime2);
    $diff     = $interval->format('%a');

    if($diff > 30) {
      return;
    } else {
      $contrats_time = 'fulltime';
      for ($nb = 0; $nb < count($data[$nb_lignes]->working_times); $nb++) {
        if($data[$nb_lignes]->working_times[$nb]->id == 1) {
          $contrats_time = 'parttime';
        }
      };

      $track = $xml->addChild('job');
      $track->addChild('referencenumber', '<![CDATA[waiter'.$data[$nb_lignes]->id.']]');
      $track->addChild('title', '<![CDATA['.$data[$nb_lignes]->title.']]');
      $track->addChild('description', '<![CDATA[Description du poste : &#09;
      '.htmlspecialchars($data[$nb_lignes]->description).' &#09; Profil recherché : &#09;
      '.htmlspecialchars($data[$nb_lignes]->required_profile).']]');
      $track->addChild('url', '<![CDATA[https://waiter.fr/offer/'.$data[$nb_lignes]->id.']]' );
      $track->addChild('city', '<![CDATA['.htmlspecialchars($data[$nb_lignes]->restaurant->city).']]');
      $track->addChild('company', '<![CDATA['.htmlspecialchars($data[$nb_lignes]->restaurant->name).']]');
      $track->addChild('country', '<![CDATA[FR]]');
      $track->addChild('jobtype', '<![CDATA['.$contrats_time.']]');
      $track->addChild('date', '<![CDATA['.$date.']]');
    }
  }

  Header('Content-type: text/xml');
  print($xml->asXML());
}
