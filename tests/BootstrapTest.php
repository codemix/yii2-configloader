<?php
use codemix\yii2confload\Config;

class BootstrapTest extends \PHPUnit\Framework\TestCase
{
    public function testCanBootstrapApplication()
    {
        $config = Config::bootstrap(__DIR__ . '/app', __DIR__ . '/../vendor');
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
        $this->assertTrue(defined('YII_DEBUG'));
        $this->assertTrue(defined('YII_ENV'));
        $this->assertTrue(defined('YII_ENV_PROD'));
        $this->assertEquals(0, YII_DEBUG);
        $this->assertEquals('dev', YII_ENV);
        $this->assertTrue(class_exists('Yii', false));
    }
}
