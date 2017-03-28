<?php
use codemix\yii2confload\Config;

class WebConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testCanGetConfig()
    {
        $config = new Config(__DIR__ . '/app');
        $web = $config->web();
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
            ],
            'key3' => 2,
            'key4' => 'web4',
        ];

        $this->assertEquals($expected, $web);
    }

    public function testCanMergeCustomConfig()
    {
        $config = new Config(__DIR__ . '/app');
        $web = $config->web([
            'key5' => 'test',
        ]);
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
            ],
            'key3' => 2,
            'key4' => 'web4',
            'key5' => 'test',
        ];

        $this->assertEquals($expected, $web);
    }

    public function testCanMergeLocalConfigByEnvVar()
    {
        $config = new Config(__DIR__ . '/app');
        putenv('ENABLE_LOCALCONF=1');
        $web = $config->web([
            'key5' => 'test',
        ]);
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
                'local1',
                'local2',
            ],
            'key3' => 'local3',
            'key4' => 'web4',
            'key5' => 'test',
        ];
        $this->assertEquals($expected, $web);
    }

    public function testCanMergeLocalConfigByArgument()
    {
        $config = new Config(__DIR__ . '/app');
        $web = $config->web([
            'key5' => 'test',
        ], true);
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
                'local1',
                'local2',
            ],
            'key3' => 'local3',
            'key4' => 'web4',
            'key5' => 'test',
        ];
        $this->assertEquals($expected, $web);
    }
}
