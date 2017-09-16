<?php
declare(strict_types=1);

namespace Cdekok\Geo\Test;

use Cdekok\Geo\Geohash;
use PHPUnit\Framework\TestCase;

class GeohashTest extends TestCase {

    /**
     * @dataProvider getEncodeData
     * @return void
     */
    public function testEncode(float $lat, float $lon, string $expected, int $length) {
        $result = (new Geohash)->encode($lat, $lon, $length);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getDecodeData
     */
    public function testDecode(string $hash, array $expected) {
        $result = (new Geohash)->decode($hash);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getDecodeBboxData
     */
    public function testDecodeBbox(string $hash, array $expected) {
        $result = (new Geohash)->decodeBbox($hash);
        $this->assertEquals($expected, $result);
    }

    public function getEncodeData() {
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

    public function getDecodeData() {
        return [
            [
                'u173zmswd',
                [
                    'latitude' => 52.370216846466064,
                    'longitude' => 4.895203113555908,
                    'error' => [
                        'latitude' =>  0.000021457672119140625,
                        'longitude' => 0.000021457672119140625,
                    ]
                ]
            ]
        ];
    }

    public function getDecodeBboxData() {
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
}
