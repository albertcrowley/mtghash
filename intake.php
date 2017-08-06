<?php
require_once 'vendor/autoload.php';
use Screen\Capture;
use Jenssegers\ImageHash\ImageHash;
use claviska\SimpleImage;

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

function hashCard($file) {

  $ART_COORDS_PCT = [40, 50, 430, 370];
  $X1 = 0;
  $Y1 = 1;
  $X2 = 2;
  $Y2 = 3;


  $root = "/var/www/mtghash/";
  //$in = "./cards/Mana Flare.full.jpg";
  $in = $file;
  $out = "./images/out.jpg";
  $artfile = "./images/art.jpg";
  $image = new claviska\SimpleImage();
  $image->fromFile($root . $in)
    ->toFile($root . $out);

  $height = $image->getHeight();
  $width = $image->getWidth();

  $image->crop($ART_COORDS_PCT[$X1], $ART_COORDS_PCT[$Y1]
    , $ART_COORDS_PCT[$X2], $ART_COORDS_PCT[$Y2]
  )->toFile($root . $artfile);

  $algs = [
    new Jenssegers\ImageHash\Implementations\AverageHash(),
    new Jenssegers\ImageHash\Implementations\DifferenceHash(),
    new Jenssegers\ImageHash\Implementations\PerceptualHash()
  ];

  $hasher = new ImageHash($algs[0]);
  $all_hash = $hasher->hash($out);
  $art_hash = $hasher->hash($artfile);

  return array("all_hash" => $all_hash, "art_hash" => $art_hash);
}

setupDB();
$allfiles = glob("cards/old_core_v1/*.jp*g");
foreach ($allfiles as $file) {
    $hashes = hashCard($file);
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
