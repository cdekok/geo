<?php
declare(strict_types=1);

namespace Cdekok\Geo;

class Geohash
{

    const BASE32_CODES = '0123456789bcdefghjkmnpqrstuvwxyz';

    const BASE32_CODES_DICT = [
        '0' => 0,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        'b' => 10,
        'c' => 11,
        'd' => 12,
        'e' => 13,
        'f' => 14,
        'g' => 15,
        'h' => 16,
        'j' => 17,
        'k' => 18,
        'm' => 19,
        'n' => 20,
        'p' => 21,
        'q' => 22,
        'r' => 23,
        's' => 24,
        't' => 25,
        'u' => 26,
        'v' => 27,
        'w' => 28,
        'x' => 29,
        'y' => 30,
        'z' => 31,
    ];

    /**
     * Encode lat / long to geohash
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $numberOfChars
     * @return string
     */
    public function encode(float $latitude, float $longitude, int $numberOfChars = 9):string
    {
        $chars = [];
        $bits = 0;
        $bitsTotal = 0;
        $hashValue = 0;
        $maxLat = 90;
        $minLat = -90;
        $maxLon = 180;
        $minLon = -180;
        $mid = null;
        while (count($chars) < $numberOfChars) {
            if ($bitsTotal % 2 === 0) {
                $mid = ($maxLon + $minLon) / 2;
                if ($longitude > $mid) {
                    $hashValue = ($hashValue << 1) + 1;
                    $minLon = $mid;
                } else {
                    $hashValue = ($hashValue << 1) + 0;
                    $maxLon = $mid;
                }
            } else {
                $mid = ($maxLat + $minLat) / 2;
                if ($latitude > $mid) {
                    $hashValue = ($hashValue << 1) + 1;
                    $minLat = $mid;
                } else {
                    $hashValue = ($hashValue << 1) + 0;
                    $maxLat = $mid;
                }
            }

            $bits++;
            $bitsTotal++;
            if ($bits === 5) {
                $code = self::BASE32_CODES[$hashValue];
                $chars[] = $code;
                $bits = 0;
                $hashValue = 0;
            }
        }
        return implode('', $chars);
    }

    /**
     * Decode a hash string into an array of latitude and longitude.
     * An array is returned with keys:
     * [
     *      latitude => x.xxx,
     *      longitude => x.xxx,
     *      error => [
     *          latitude => x.xxx,
     *          longitude => x.xxx,
     *      ]
     * ]
     * @param string $hashString
     * @return array
     */
    public function decode(string $hashString):array
    {
        $bbox = $this->decodeBbox($hashString);
        $lat = ($bbox[0] + $bbox[2]) / 2;
        $lon = ($bbox[1] + $bbox[3]) / 2;
        $latErr = $bbox[2] - $lat;
        $lonErr = $bbox[3] - $lon;

        return [
            'latitude' => $lat,
            'longitude' => $lon,
            'error' => [
                'latitude' => $latErr,
                'longitude' => $lonErr
            ]
        ];
    }

    /**
     * Decode bounding box, returns an array of
     * [$minLat, $minLon, $maxLat, $maxLon]
     *
     * @param string $hashString
     * @return array [$minLat, $minLon, $maxLat, $maxLon]
     */
    public function decodeBbox(string $hashString):array
    {
        $isLon = true;
        $maxLat = 90;
        $minLat = -90;
        $maxLon = 180;
        $minLon = -180;
        $mid = null;

        $hashValue = 0;
        for ($i = 0, $l = strlen($hashString); $i < $l; $i++) {
            $code = strtolower($hashString[$i]);
            $hashValue = self::BASE32_CODES_DICT[$code];

            for ($bits = 4; $bits >= 0; $bits--) {
                $bit = ($hashValue >> $bits) & 1;
                if ($isLon) {
                    $mid = ($maxLon + $minLon) / 2;
                    if ($bit === 1) {
                        $minLon = $mid;
                    } else {
                        $maxLon = $mid;
                    }
                } else {
                    $mid = ($maxLat + $minLat) / 2;
                    if ($bit === 1) {
                        $minLat = $mid;
                    } else {
                        $maxLat = $mid;
                    }
                }
                $isLon = !$isLon;
            }
        }
        return [$minLat, $minLon, $maxLat, $maxLon];
    }
}
