<?php
require_once 'vendor/autoload.php';
use Screen\Capture;
use Jenssegers\ImageHash\ImageHash;
use claviska\SimpleImage;
use keboola\csv;

require_once "mtghash/Card.php";

$file_db = new PDO('sqlite:cards.sqlite3');
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE,
  PDO::ERRMODE_EXCEPTION);

function setupDB() {
    global $file_db;
  // Create table messages
  $file_db->exec("CREATE TABLE IF NOT EXISTS cards (
                    id INTEGER PRIMARY KEY AUTOINCREMENT, 
                    title TEXT, 
                    all_hash TEXT,
                    art_hash TEXT)");
}

function addToDB($title, $all_hash, $art_hash) {
  global $file_db;
    $sql = "insert into cards (title, all_hash, art_hash) values (:title, :all, :art)";
    $sth = $file_db->prepare($sql);
    $sth->bindParam(':title', $title);
    $sth->bindParam(':all', $all_hash);
    $sth->bindParam(':art', $art_hash);
    $sth->execute();
}


echo "<pre>";

setupDB();

$card_array = array();
$csv_file = new Keboola\Csv\CsvFile("./cards.csv");
foreach ($csv_file as $row) {
  if ($row[0] == null || trim($row[0]) == "" || $row[0] == "name") {
    continue;
  }
  $a = array();
  $a['name'] = $row[0];
  $a['set'] = $row[2];
  $a['set_code'] = $row[4];
  $a['id'] = $row[6];
  $a['type'] = $row[8];
  $a['power'] = $row[10];
  $a['toughness'] = $row[12];
  $a['manacost'] = $row[16];
  $a['converted_manacost'] = $row[18];
  $a['artist'] = $row[20];
  $a['flavor'] = $row[22];
  $a['color'] = $row[24];
  $a['number'] = $row[28];
  $a['rarity'] = $row[30];
  $a['number_int'] = $row[54];
  $card_array[] = $a;
}


echo "Read " . count($card_array) . " cards.";
foreach ($card_array as $a) {

  $card = new \mtghash\Card($a['name'], $a['set_code'], $a['id'],$a['number_int'] );
    $hashes = $card->hash();
    if ($hashes != null) {
      echo "${a['name']} (${a['id']}) hashes to: all-${hashes['all_hash']} art-${hashes['art_hash']}" . PHP_EOL;
    } else {
      echo "${a['set_code']} ${a['name']} (${a['id']}) - IMAGE/HASH NOT FOUND " . PHP_EOL;
    }
  echo $card->getImageFile(true) . PHP_EOL;
    //addToDB($file, $hashes['all_hash'], $hashes['art_hash']);
    flush();
}

//print_r($card_array);

$sth = $file_db->prepare ("select * from cards");
$sth->execute();
$result = $sth->fetchAll();
print_r($result);
//        $distance = $hasher->distance($goodhash, $hash);
?>
