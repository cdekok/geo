# Geohash

PHP implementation for encoding / decoding geohashes

## Usage

```php
use Cdekok\Geo\Geohash;

// Encode
$hash = (new Geohash)->encode($lat, $lon);

// Decode
$location = (new Geohash)->deode($hash);
echo $location['latitude'] . "\n";
echo $location['longitude'] . "\n";

// Decode bounding box
$bb = (new Geohash)->deodeBbox($hash);
echo 'Min latitude: ' . $bb[0] . "\n";
echo 'Min longitude: ' . $bb[1] . "\n";
echo 'Max latitude: ' . $bb[2] . "\n";
echo 'Max longitude: ' . $bb[3] . "\n";
```

## Credits

PHP port of [ngeohash](https://github.com/sunng87/node-geohash)
