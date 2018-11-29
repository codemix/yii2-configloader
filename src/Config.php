<?php
namespace codemix\yii2confload;

/**
 * Config helps to build application configuration from a set of config files.
 * It can also initialize the Yii environment from environment vars and help
 * to load the latter from a `.env` file.
 *
 * ```php
 * // Init Yii environment from env vars (or `.env` file) and load configuration
 * // from `config/web.php` or `config/console.php` respectively
 * $config = new Config('/path/to/app')->web();
 * $config = new Config('/path/to/app')->console();
 *
 * // Merge a `config/local.php` or `config/console-local.php` with local overrides
 * // This also happens if `ENABLE_LOCALCONF` environment variable is set.
 * $config = new Config('/path/to/app')->web([], true);
 * $config = new Config('/path/to/app')->console([], true);
 * ```
 *
 * The class normally is used with this set of files:
 *
 *  - `.env`: Defines environment variables (usually only used for local development)
 *  - `config/web.php`: Web configuration
 *  - `config/console.php`: Console configuration
 *
 * A typical example for an `.env` file:
 *
 * ```
 * YII_DEBUG=1
 * DB_DSN=mysql:host=db;dbname=web
 * DB_USER=user
 * DB_PASSWORD=secret
 * ```
 *
 * The `web.php` is loaded in the context of this class, so you can easily access
 * these settings there like:
 *
 * ```php
 * return [
 *     'components' => [
 *         'db' => [
 *             'dsn' => self::env('DB_DSN'),
 *             'username' => self::env('DB_USER'),
 *             'password' => self::env('DB_PASSWORD'),
 * ```
 *
 * In your `console.php` you can also reuse parts of your web configuration:
 *
 * ``php
 * $web = $this->web();
 * return [
 *     'components' => [
 *         'db' => $web['components']['db'],
 * ```
 *
 * The initialization of the Yii environment and loading of `.env` file
 * is optional and can also be supressed:
 *
 * ```php
 * $config = new Config('/path/to/app', false)->web();
 * ```
 *
 * Vice versa the class can also be used to only initialize the Yii environment
 * and load a `.env` file:
 *
 * ```php
 * Config::initEnv('/path/to/app');
 *
 * // Get setting from environment variable or .env file
 * $setting = Config::env('MY_SETTING', 'default');
 * ```
 */
class Config
{
    /**
     * @var string the application base directory
     */
    public $directory;

    /**
     * Initialize the app directory path and the Yii environment.
     *
     * If an `.env` file is found in the app directory, it's loaded with `Dotenv`.
     * If a `YII_DEBUG` or `YII_ENV` environment variable is set, the Yii constants
     * are set accordingly. In debug mode the error reporting is also set to `E_ALL`.
     *
     * @param string $directory the application base directory
     * @param bool $initEnv whether to initialize the Yii environment. Default is `true`.
     */
    public function __construct($directory, $initEnv = true)
    {
        $this->directory = $directory;

        if ($initEnv) {
            self::initEnv($directory);
        }

    }

    /**
     * Gets the filename for a config file
     *
     * @param string $name
     * @param bool $required whether the file must exist. Default is `true`.
     * @param string $directory the name of the config directory. Default is `config`.
     * @return string|null the full path to the config file or `null` if $required
     * is set to `false` and the file does not exist
     * @throws \Exception
     */
    public function getConfigFile($name, $required = true, $directory = 'config')
    {
        $sep = DIRECTORY_SEPARATOR;
        $path = rtrim($this->directory, $sep) . $sep . trim($directory, $sep) . $sep . $name;
        if (!file_exists($path)) {
            if ($required) {
                throw new \Exception("Config file '$path' does not exist");
            } else {
                return null;
            }
        }
        return $path;
    }

    /**
     * Builds the web configuration.
     *
     * If $local is set to `true` and a `local.php` config file exists, it
     * is merged into the web configuration.
     *
     * Alternatively an environment variable `ENABLE_LOCALCONF` can be set
     * to 1. Setting $local to `false` completely disables the local config.
     *
     * @param array $config additional configuration to merge into the result
     * @param bool|null $local whether to check for local configuration overrides.
     * The default is `null`, which will check `ENABLE_LOCALCONF` env var.
     * @return array the web configuration array
     * @throws \Exception
     */
    public function web($config = [], $local = null)
    {
        $files = [$this->getConfigFile('web.php')];
        if ($local === null) {
            $local = $this->env('ENABLE_LOCALCONF', false);
        }
        if ($local) {
            $localFile = $this->getConfigFile('local.php', false);
            if ($localFile !==null) {
                $files[] = $localFile;
            }
        }
        return $this->mergeFiles($files, $config);
    }

    /**
     * Builds the console configuration.
     *
     * If $local is set to `true` and a `local-console.php` config file exists,
     * it is merged into the console configuration.
     *
     * Alternatively an environment variable `ENABLE_LOCALCONF` can be set
     * to 1. Setting $local to `false` completely disables the local config.
     *
     * @param array $config additional configuration to merge into the result
     * @param bool|null $local whether to check for local configuration overrides.
     * The default is `null`, which will check `ENABLE_LOCALCONF` env var.
     * @return array the web configuration array
     * @throws \Exception
     */
    public function console($config = [], $local = null)
    {
        $files = [$this->getConfigFile('console.php')];
        if ($local === null) {
            $local = $this->env('ENABLE_LOCALCONF', false);
        }
        if ($local) {
            $localFile = $this->getConfigFile('local-console.php', false);
            if ($localFile !==null) {
                $files[] = $localFile;
            }
        }
        return $this->mergeFiles($files, $config);
    }

    /**
     * Load configuration files and merge them together.
     *
     * The files are loaded in the context of this class. So you can use `$this`
     * and `self` to access instance / class methods.
     *
     * @param array $files list of configuration files to load and merge.
     * Configuration from later files will override earlier values.
     * @param array $config additional configuration to merge into the result
     * @return array the resulting configuration array
     */
    public function mergeFiles($files, $config = [])
    {
        $configs = array_map(function ($f) { return require($f); }, $files);
        $configs[] = $config;
        return call_user_func_array('yii\helpers\ArrayHelper::merge', $configs);
    }

    /**
     * Init the configuration for the given directory and load the Yii bootstrap file.
     *
     * @param string $directory the application directory
     * @param string|null $vendor the composer vendor directory. Default is `null`
     * which means, the vendor directory is autodetected.
     * @param bool $initEnv whether to initialize the Yii environment. Default is `true`.
     * @return static the Config instance for that application directory
     */
    public static function bootstrap($directory, $vendor = null, $initEnv = true)
    {
        $sep = DIRECTORY_SEPARATOR;
        if ($vendor === null) {
            $vendor = realpath(__DIR__ . $sep . '..' . $sep . '..' . $sep . '..');
        }
        $config = new self($directory, $initEnv);
        require(rtrim($vendor, $sep) . $sep . 'yiisoft' . $sep . 'yii2' . $sep . 'Yii.php');
        return $config;
    }

    /**
     * Init the Yii environment from environment variables.
     *
     * If $directory is passed and contains a `.env` file, that file is loaded
     * with `Dotenv` first.
     *
     * @param string|null $directory the directory to check for an `.env` file
     */
    public static function initEnv($directory = null)
    {
        if ($directory !== null && file_exists($directory . DIRECTORY_SEPARATOR . '.env')) {
            $dotenv = new \Dotenv\Dotenv($directory);
            $dotenv->load();
        }

        // Define main Yii environment variables
        $debug = self::env('YII_DEBUG', false);
        if ($debug !== false) {
            define('YII_DEBUG', (bool)$debug);
            if (YII_DEBUG) {
                error_reporting(E_ALL);
            }
        }
        $env = self::env('YII_ENV', false);
        if ($env !== false) {
            define('YII_ENV', $env);
        }
    }

    /**
     * Get either an env var or a default value if the var is not set.
     *
     * @param string $name the name of the variable to get
     * @param mixed $default the default value to return if variable is not set.
     * Default is `null`.
     * @param bool $required whether the var must be set. $default is ignored in
     * this case. Default is `false`.
     * @return mixed the content of the environment variable or $default if not set
     */
    public static function env($name, $default = null, $required = false)
    {
        if (array_key_exists($name, $_ENV)) {
            return $_ENV[$name];
        }
        if (array_key_exists($name, $_SERVER)) {
            return $_SERVER[$name];
        }
        $value = getenv($name);
        if ($value === false && $required) {
            throw new \Exception("Environment variable '$name' is not set");
        }
        return $value === false ? $default : $value;
    }
}
