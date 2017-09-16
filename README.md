# Geohash

PHP implementation for encoding / decoding geohashes

[![Build Status](https://travis-ci.org/cdekok/geo.svg?branch=develop)](https://travis-ci.org/cdekok/geo)
[![Coverage Status](https://coveralls.io/repos/github/cdekok/geo/badge.svg?branch=develop)](https://coveralls.io/github/cdekok/geo?branch=develop)

## Usage

```php
use Cdekok\Geo\Geohash;

// Encode
$hash = (new Geohash)->encode($lat, $lon);

// Decode
$location = (new Geohash)->decode($hash);
echo $location['latitude'] . "\n";
echo $location['longitude'] . "\n";

// Decode bounding box
$bb = (new Geohash)->deodeBbox($hash);
echo 'Min latitude: ' . $bb[0] . "\n";
echo 'Min longitude: ' . $bb[1] . "\n";
echo 'Max latitude: ' . $bb[2] . "\n";
echo 'Max longitude: ' . $bb[3] . "\n";

// Find neighbour hash
$north = (new Geohash)->neighbour($hash, Geohash::DIRECTION_NORTH);

// Find all geohashes within a bounding box
$minLat = -90;
$minLon = -180;
$maxLat = 90;
$maxLon = 180;
$length = 1;
$hashes = (new Geohash)->bboxes($minLat, $minLon, $maxLat, $maxLon, $length);
prin_r($hashes);
[
    '0',
    '1',
    '4',
    ...
```

## Credits

PHP port of [ngeohash](https://github.com/sunng87/node-geohash)
