<?php
use codemix\yii2confload\Config;

class ConsoleConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testCanGetConfig()
    {
        $config = new Config(__DIR__ . '/app');
        $console = $config->console();
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
            ],
            'key3' => 'console3',
            'key4' => [
                'console3',
                'console4',
            ],
        ];
        $this->assertEquals($expected, $console);
    }

    public function testCanMergeCustomConfig()
    {
        $config = new Config(__DIR__ . '/app');
        $console = $config->console([
            'key5' => 'test',
        ]);
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
            ],
            'key3' => 'console3',
            'key4' => [
                'console3',
                'console4',
            ],
            'key5' => 'test',
        ];
        $this->assertEquals($expected, $console);
    }

    public function testCanMergeLocalConfigByEnvVar()
    {
        $config = new Config(__DIR__ . '/app');
        putenv('ENABLE_LOCALCONF=1');
        $console = $config->console([
            'key5' => 'test',
        ]);
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
                'local1',
                'local2',
                'lconsole1',
                'lconsole2',
            ],
            'key3' => 'lconsole3',
            'key4' => [
                'console3',
                'console4',
            ],
            'key5' => 'test',
        ];
        $this->assertEquals($expected, $console);
    }

    public function testCanMergeLocalConfigByArgument()
    {
        $config = new Config(__DIR__ . '/app');
        $console = $config->console([
            'key5' => 'test',
        ], true);
        $expected = [
            'key1' => 'dotenv1',
            'key2' => [
                'web1',
                'web2',
                'lconsole1',
                'lconsole2',
            ],
            'key3' => 'lconsole3',
            'key4' => [
                'console3',
                'console4',
            ],
            'key5' => 'test',
        ];
        $this->assertEquals($expected, $console);
    }
}
