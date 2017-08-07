<?php
/**
 * Created by PhpStorm.
 * User: crowley
 * Date: 8/6/2017
 * Time: 10:32 AM
 */

namespace mtghash;


class Card {
  static $ART_COORDS = [40,50,430,370];
  static $X1 = 0;
  static $Y1 = 1;
  static $X2 = 2;
  static $Y2 = 3;

  /***
   * Hashes the image of a card
   *
   * @param $file image file to be hashed
   *
   * @return array ("all_hash"=>XXX, "art_hash"=>YYY)
   */
  public static function hashCard($file) {


    $root = "/var/www/mtghash/";
    //$in = "./cards/Mana Flare.full.jpg";
    $in = $file;
    $out = tempnam(sys_get_temp_dir(), "mtghash-card-");
    $artfile = tempnam(sys_get_temp_dir(), "mtghash-card-art-");
    $image = new claviska\SimpleImage();
    $image->fromFile($root . $in)
      ->toFile($root . $out);

    $image->crop(Card::$ART_COORDS_PCT[Card::$X1], Card::$ART_COORDS_PCT[Card::$Y1]
      , Card::$ART_COORDS_PCT[Card::$X2], Card::$ART_COORDS_PCT[Card::$Y2]
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

}