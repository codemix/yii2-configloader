<?php
use codemix\yii2confload\Config;

class EnvTest extends \PHPUnit\Framework\TestCase
{
    public function testCanInitYiiEnvFromEnvVars()
    {
        putenv('YII_DEBUG=1');
        putenv('YII_ENV=prod');
        Config::initEnv();

        $this->assertTrue(defined('YII_DEBUG'));
        $this->assertTrue(defined('YII_ENV'));
        $this->assertTrue(YII_DEBUG);
        $this->assertEquals('prod', YII_ENV);
    }

    public function testCanInitYiiEnvFromEnvFile()
    {
        Config::initEnv(__DIR__ . '/app');

        $this->assertTrue(defined('YII_DEBUG'));
        $this->assertTrue(defined('YII_ENV'));
        $this->assertEquals(0, YII_DEBUG);
        $this->assertEquals('dev', YII_ENV);
    }

    public function testCanGetEnvVars()
    {
        putenv('TEST1=987');
        putenv('TEST2=demo');

        $this->assertEquals(987, Config::env('TEST1'));
        $this->assertEquals('demo', Config::env('TEST2'));
        $this->assertEquals('default', Config::env('TEST3', 'default'));
    }

    public function testCanRequireEnvVar()
    {
        try {
            $x = Config::env('DOESNOTEXIST', null, true);
            $this->fail('No exception thrown for missing env variable');
        } catch (\Exception $e) {
            $this->assertEquals("Environment variable 'DOESNOTEXIST' is not set", $e->getMessage());
        }
    }

    public function testCanGetEnvVarsFromEnvFile()
    {
        Config::initEnv(__DIR__ . '/app');

        $this->assertEquals(0, Config::env('YII_DEBUG'));
        $this->assertEquals('dev', Config::env('YII_ENV'));
        $this->assertEquals('dotenv1', Config::env('VAR1'));
        $this->assertEquals(2, Config::env('VAR2'));
        $this->assertEquals('default', Config::env('TEST3', 'default'));
    }

    public function testEnvFileDoesNotClearEnvVars()
    {
        putenv('TEST1=654');
        putenv('TEST2=xyz');
        Config::initEnv(__DIR__ . '/app');

        $this->assertEquals(654, Config::env('TEST1'));
        $this->assertEquals('xyz', Config::env('TEST2'));
        $this->assertEquals('dotenv1', Config::env('VAR1'));
        $this->assertEquals(2, Config::env('VAR2'));
        $this->assertEquals('default', Config::env('TEST3', 'default'));
    }

    public function testEnvFileDoesNotOverrideEnvVars()
    {
        putenv('VAR1=654');
        putenv('VAR2=xyz');
        Config::initEnv(__DIR__ . '/app');

        $this->assertEquals(0, Config::env('YII_DEBUG'));
        $this->assertEquals('dev', Config::env('YII_ENV'));
        $this->assertEquals(654, Config::env('VAR1'));
        $this->assertEquals('xyz', Config::env('VAR2'));
    }

    public function testInitsYiiEnvByDefault()
    {
        $config = new Config(__DIR__ . '/app');

        $this->assertTrue(defined('YII_DEBUG'));
        $this->assertTrue(defined('YII_ENV'));
        $this->assertEquals(0, YII_DEBUG);
        $this->assertEquals('dev', YII_ENV);
    }

    public function testCanSuppressYiiEnvInit()
    {
        $config = new Config(__DIR__ . '/app', false);

        $this->assertFalse(defined('YII_DEBUG'));
        $this->assertFalse(defined('YII_ENV'));
    }
}
