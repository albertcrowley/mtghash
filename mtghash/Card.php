<?php
/**
 * Created by PhpStorm.
 * User: crowley
 * Date: 8/6/2017
 * Time: 10:32 AM
 */

namespace mtghash;


class Card {
  static $ART_COORDS = [23,28,237,232];
  static $X1 = 0;
  static $Y1 = 1;
  static $X2 = 2;
  static $Y2 = 3;
  public $name = "";
  public $set_code = "";
  public $id = "";
  public $in_set_number= "";

  public function __construct($name, $set_code, $id_num, $in_set_number) {
    $this->name = $name;
    $this->set_code = $set_code;
    $this->id = $id_num;
    $this->in_set_number = $in_set_number;
  }

  public function hash() {
    $img_file = $this->getImageFile();
    if ($img_file != null){
      return self::hashImage($img_file);
    }
    return null;

  }

  public function getImageFile($force = false) {
    $root = __DIR__ . DIRECTORY_SEPARATOR .
      ".." . DIRECTORY_SEPARATOR .
      "cards" . DIRECTORY_SEPARATOR .
      $this->set_code . DIRECTORY_SEPARATOR;
    $file = $root . $this->id . " - " . $this->name . " - " . $this->getExtendedInSetNumber() . ".full.jpg";

    if (!file_exists($file)) {
      // try finding just by card number
      $files = glob($root . $this->id . "*");
      if (count($files) > 0) {
        $file = $files[0];
      } else {
        //try just by name
        $files = glob($root . "*" . $this->name . "*");
        if (count($files) > 0) {
          $file = $files[0];
        }
      }
    }

    if ($force || file_exists($file)) {
      return $file;
    }
    return null;
  }

  public function getExtendedInSetNumber() {
    $n = sprintf("%'.03d", $this->in_set_number);
    return $n;
  }

  /***
   * Hashes the image of a card
   *
   * @param $file image file to be hashed
   *
   * @return array ("all_hash"=>XXX, "art_hash"=>YYY)
   */
  public static function hashImage($file) {


    $out = tempnam(sys_get_temp_dir(), "mtghash-card-");
    $artfile = tempnam(sys_get_temp_dir(), "mtghash-card-art-");
    $image = new \claviska\SimpleImage($file);
    $image->fromFile($file)
      ->toFile($out);

    $image->crop(self::$ART_COORDS [self::$X1], self::$ART_COORDS[self::$Y1]
      , self::$ART_COORDS[self::$X2], self::$ART_COORDS[self::$Y2]
    )->toFile($artfile);

    $algs = [
      new \Jenssegers\ImageHash\Implementations\AverageHash(),
      new \Jenssegers\ImageHash\Implementations\DifferenceHash(),
      new \Jenssegers\ImageHash\Implementations\PerceptualHash()
    ];

    $hasher = new \Jenssegers\ImageHash\ImageHash($algs[1]);
    $all_hash = $hasher->hash($out);
    print ("got $all_hash" . PHP_EOL);
    $art_hash = $hasher->hash($artfile);

//    unlink($artfile);
//    unlink($out);

    return array("all_hash" => $all_hash, "art_hash" => $art_hash);
  }

}