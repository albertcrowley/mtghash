<?php
require_once 'vendor/autoload.php';
use Screen\Capture;
use Jenssegers\ImageHash\ImageHash;
use claviska\SimpleImage;
use mtghash\Card;

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


setupDB();
$allfiles = glob("cards/old_core_v1/*.jp*g");
foreach ($allfiles as $file) {
    $hashes = Card::hashCard($file);
    echo "$file ${hashes['all_hash']} ${hashes['art_hash']} <br>";
    addToDB($file, $hashes['all_hash'], $hashes['art_hash']);
    flush();
}
echo "<pre>";

$sth = $file_db->prepare ("select * from cards");
$sth->execute();
$result = $sth->fetchAll();
print_r($result);
//        $distance = $hasher->distance($goodhash, $hash);
?>
