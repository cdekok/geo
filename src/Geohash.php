<?php
declare (strict_types = 1);

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

    const DIRECTION_NORTH = [1,0];
    const DIRECTION_NORTHEAST = [1,1];
    const DIRECTION_EAST = [0,1];
    const DIRECTION_SOUTHEAST = [-1, 1];
    const DIRECTION_SOUTH = [-1, 0];
    const DIRECTION_SOUTHWEST = [-1,-1];
    const DIRECTION_WEST = [0,-1];
    const DIRECTION_NORTHWEST = [1,-1];

    /**
     * Encode lat / long to geohash
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $numberOfChars
     * @return string
     */
    public function encode(float $latitude, float $longitude, int $numberOfChars = 9) : string
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
    public function decode(string $hashString) : array
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
    public function decodeBbox(string $hashString) : array
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

    /**
     * Retrieve all geohashes from a bounding box
     *
     * @param float $minLat
     * @param float $minLon
     * @param float $maxLat
     * @param float $maxLon
     * @param int $numberOfChars
     * @return array
     */
    public function bboxes(float $minLat, float $minLon, float $maxLat, float $maxLon, int $numberOfChars = 9) : array
    {
        $hashSouthWest = $this->encode($minLat, $minLon, $numberOfChars);
        $hashNorthEast = $this->encode($maxLat, $maxLon, $numberOfChars);

        $latLon = $this->decode($hashSouthWest);

        $perLat = $latLon['error']['latitude'] * 2;
        $perLon = $latLon['error']['longitude'] * 2;

        $boxSouthWest = $this->decodeBbox($hashSouthWest);
        $boxNorthEast = $this->decodeBbox($hashNorthEast);

        $latStep = round(($boxNorthEast[0] - $boxSouthWest[0]) / $perLat);
        $lonStep = round(($boxNorthEast[1] - $boxSouthWest[1]) / $perLon);

        $hashList = [];

        for ($lat = 0; $lat <= $latStep; $lat++) {
            for ($lon = 0; $lon <= $lonStep; $lon++) {
                $hashList[] = $this->neighbour($hashSouthWest, [$lat, $lon]);
            }
        }

        return $hashList;
    }

    /**
     * Find neighbor of a geohash string in certain direction.
     * Direction is a two-element array $direction [lat, lon], i.e.
     *
     * <code>
     * (new Geohash)->neighbour($hash, Geohash::DIRECTION_NORTH);
     * </code>
     *
     * @param string $hashString
     * @param array $direction
     * @return string
     */
    public function neighbour(string $hashString, array $direction):string
    {
        $lonLat = $this->decode($hashString);
        $neighborLat = $lonLat['latitude'] + $direction[0] * $lonLat['error']['latitude'] * 2;
        $neighborLon = $lonLat['longitude'] + $direction[1] * $lonLat['error']['longitude'] * 2;
        return $this->encode($neighborLat, $neighborLon, strlen($hashString));
    }
}
