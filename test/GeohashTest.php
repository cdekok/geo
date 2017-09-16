<?php
declare (strict_types = 1);

namespace Cdekok\Geo\Test;

use Cdekok\Geo\Geohash;
use PHPUnit\Framework\TestCase;

class GeohashTest extends TestCase
{

    /**
     * @dataProvider getEncodeData
     * @return void
     */
    public function testEncode(float $lat, float $lon, string $expected, int $length)
    {
        $result = (new Geohash)->encode($lat, $lon, $length);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getDecodeData
     */
    public function testDecode(string $hash, array $expected)
    {
        $result = (new Geohash)->decode($hash);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getDecodeBboxData
     */
    public function testDecodeBbox(string $hash, array $expected)
    {
        $result = (new Geohash)->decodeBbox($hash);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getNeighbourData
     */
    public function testNeighbour(string $hash, array $direction, string $expected)
    {
        $result = (new Geohash)->neighbour($hash, $direction);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getBboxesData
     */
    public function testBboxes(float $minLat, float $minLon, float $maxLat, float $maxLon, int $length, array $expected)
    {
        $result = (new Geohash)->bboxes($minLat, $minLon, $maxLat, $maxLon, $length);
        $this->assertEquals($expected, $result);
    }

    public function getEncodeData() : array
    {
        return [
            [
                52.3702,
                4.8952,
                'u173zmswd',
                9
            ],
            [
                52.1326,
                5.2913,
                'u17b86mn2',
                9
            ],
            [
                52.1326,
                5.2913,
                'u1',
                2
            ]
        ];
    }

    public function getDecodeData() : array
    {
        return [
            [
                'u173zmswd',
                [
                    'latitude' => 52.370216846466064,
                    'longitude' => 4.895203113555908,
                    'error' => [
                        'latitude' => 0.000021457672119140625,
                        'longitude' => 0.000021457672119140625,
                    ]
                ]
            ]
        ];
    }

    public function getDecodeBboxData() : array
    {
        return [
            [
                'u173zmswd',
                [
                    52.370195388793945,
                    4.895181655883789,
                    52.370238304138184,
                    4.895224571228027,
                ]
            ]
        ];
    }

    public function getNeighbourData() : array
    {
        return [
            ['u173zmswd', Geohash::DIRECTION_NORTH, 'u173zmswf'],
            ['u173zmswd', Geohash::DIRECTION_NORTHEAST, 'u173zmswg'],
            ['u173zmswd', Geohash::DIRECTION_EAST, 'u173zmswe'],
            ['u173zmswd', Geohash::DIRECTION_SOUTHEAST, 'u173zmsw7'],
            ['u173zmswd', Geohash::DIRECTION_SOUTH, 'u173zmsw6'],
            ['u173zmswd', Geohash::DIRECTION_SOUTHWEST, 'u173zmsw3'],
            ['u173zmswd', Geohash::DIRECTION_WEST, 'u173zmsw9'],
            ['u173zmswd', Geohash::DIRECTION_NORTHWEST, 'u173zmswc'],
        ];
    }

    public function getBboxesData() : array
    {
        return [
            [
                -90,
                -180,
                90,
                180,
                1,
                [
                    '0',
                    '1',
                    '4',
                    '5',
                    'h',
                    'j',
                    'n',
                    'p',
                    '2',
                    '3',
                    '6',
                    '7',
                    'k',
                    'm',
                    'q',
                    'r',
                    '8',
                    '9',
                    'd',
                    'e',
                    's',
                    't',
                    'w',
                    'x',
                    'b',
                    'c',
                    'f',
                    'g',
                    'u',
                    'v',
                    'y',
                    'z'
                ]
            ]
        ];
    }
}
