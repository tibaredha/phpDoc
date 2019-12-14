<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor;

use RuntimeException;
use const DIRECTORY_SEPARATOR;
use function date_default_timezone_set;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function ini_get;
use function ini_set;
use function sys_get_temp_dir;
use function trim;

/**
 * Application class for phpDocumentor.
 *
 * Can be used as bootstrap when the run method is not invoked.
 */
final class Application
{
    private $cacheFolder;

    public static function VERSION() : string
    {
        return trim(file_get_contents(__DIR__ . '/../../VERSION'));
    }

    public static function templateDirectory() : string
    {
        // @codeCoverageIgnoreStart
        $templateDir = __DIR__ . '/../../data/templates';

        // when installed using composer the templates are in a different folder
        $composerTemplatePath = __DIR__ . '/../../../templates';
        if (file_exists($composerTemplatePath)) {
            $templateDir = $composerTemplatePath;
        }

        return $templateDir;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Initializes all components used by phpDocumentor.
     */
    public function __construct()
    {
        $this->defineIniSettings();
    }

    public function cacheFolder() : string
    {
        if ($this->cacheFolder === null) {
            throw new \RuntimeException('Cache folder should be declared before first use');
        }

        return $this->cacheFolder;
        //return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpdocumentor' . DIRECTORY_SEPARATOR . 'pools';
    }

    public function withCacheFolder(string $cacheFolder) : void
    {
        $this->cacheFolder = $cacheFolder;
    }

    /**
     * Adjust php.ini settings.
     *
     * @throws RuntimeException
     */
    private function defineIniSettings() : void
    {
        $this->setTimezone();
        ini_set('memory_limit', '-1');

        // @codeCoverageIgnoreStart
        if (extension_loaded('Zend OPcache')
            && ini_get('opcache.enable')
            && ini_get('opcache.enable_cli')
            && ini_get('opcache.save_comments') === '0'
        ) {
            throw new RuntimeException('Please enable opcache.save_comments in php.ini.');
        }

        if (extension_loaded('Zend Optimizer+') && ini_get('zend_optimizerplus.save_comments') === '0') {
            throw new RuntimeException('Please enable zend_optimizerplus.save_comments in php.ini.');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * If the timezone is not set anywhere, set it to UTC.
     *
     * This is done to prevent any warnings being outputted in relation to using
     * date/time functions.
     *
     * @link http://php.net/manual/en/function.date-default-timezone-get.php for more information how PHP determines the
     *     default timezone.
     */
    private function setTimezone() : void
    {
        // @codeCoverageIgnoreStart
        if (ini_get('date.timezone') !== false) {
            return;
        }

        date_default_timezone_set('UTC');
        // @codeCoverageIgnoreEnd
    }
}
