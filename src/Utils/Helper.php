<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 16-4-1
 * Time: 上午10:08
 * Used:
 * file: Color.php
 */

namespace Inhere\Console\Utils;

use Inhere\Console\Traits\RuntimeProfileTrait;

/**
 * Class Helper
 * @package Inhere\Console\Utils
 */
class Helper
{
    use RuntimeProfileTrait;

    /**
     * Returns true if the console is running on windows
     * @return boolean
     */
    public static function isOnWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    /**
     * @return bool
     */
    public static function isMac(): bool
    {
        return stripos(PHP_OS, 'Darwin') !== false;
    }

    /**
     * @return bool
     */
    public static function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') !== false;
    }

    /**
     * @return bool
     */
    public static function isUnix(): bool
    {
        $uNames = ['CYG', 'DAR', 'FRE', 'HP-', 'IRI', 'LIN', 'NET', 'OPE', 'SUN', 'UNI'];

        return \in_array(strtoupper(substr(PHP_OS, 0, 3)), $uNames, true);
    }

    /**
     * @return bool
     */
    public static function isRoot(): bool
    {
        if (\function_exists('posix_getuid')) {
            return posix_getuid() === 0;
        }

        return getmyuid() === 0;
    }

    /**
     * @return bool
     */
    public static function isSupportColor()
    {
        return self::supportColor();
    }

    /**
     * Returns true if STDOUT supports colorization.
     * This code has been copied and adapted from
     * \Symfony\Component\Console\Output\OutputStream.
     * @return boolean
     */
    public static function supportColor()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return
                '10.0.10586' === PHP_WINDOWS_VERSION_MAJOR . '.' . PHP_WINDOWS_VERSION_MINOR . '.' . PHP_WINDOWS_VERSION_BUILD ||
                // 0 == strpos(PHP_WINDOWS_VERSION_MAJOR . '.' . PHP_WINDOWS_VERSION_MINOR . PHP_WINDOWS_VERSION_BUILD, '10.') ||
                false !== getenv('ANSICON') ||
                'ON' === getenv('ConEmuANSI') ||
                'xterm' === getenv('TERM')// || 'cygwin' === getenv('TERM')
                ;
        }

        if (!\defined('STDOUT')) {
            return false;
        }

        return self::isInteractive(STDOUT);
    }

    /**
     * @return bool
     */
    public function isSupport256Color()
    {
        return DIRECTORY_SEPARATOR === '/' && strpos(getenv('TERM'), '256color') !== false;
    }

    /**
     * @return bool
     */
    public static function isAnsiSupport()
    {
        return getenv('ANSICON') === true || getenv('ConEmuANSI') === 'ON';
    }

    /**
     * Returns if the file descriptor is an interactive terminal or not.
     * @param  int|resource $fileDescriptor
     * @return boolean
     */
    public static function isInteractive($fileDescriptor)
    {
        return \function_exists('posix_isatty') && @posix_isatty($fileDescriptor);
    }

    /**
     * 给对象设置属性值
     * @param $object
     * @param array $options
     */
    public static function init($object, array $options)
    {
        foreach ($options as $property => $value) {
            $object->$property = $value;
        }
    }

    /**
     * @param string $srcDir
     * @param callable $filter
     * @return \RecursiveIteratorIterator
     * @throws \InvalidArgumentException
     */
    public static function recursiveDirectoryIterator(string $srcDir, callable $filter)
    {
        if (!$srcDir || !file_exists($srcDir)) {
            throw new \InvalidArgumentException('Please provide a exists source directory.');
        }

        $directory = new \RecursiveDirectoryIterator($srcDir);
        $filterIterator = new \RecursiveCallbackFilterIterator($directory, $filter);

        return new \RecursiveIteratorIterator($filterIterator);
    }

    /**
     * @param string $command
     * @param array $map
     */
    public static function commandSearch(string $command, array $map)
    {

    }

    /**
     * wrap a style tag
     * @param string $string
     * @param string $tag
     * @return string
     */
    public static function wrapTag($string, $tag)
    {
        if (!$string) {
            return '';
        }

        if (!$tag) {
            return $string;
        }

        return "<$tag>$string</$tag>";
    }

    /**
     * clear Ansi Code
     * @param $string
     * @return mixed
     */
    public static function stripAnsiCode($string)
    {
        return preg_replace('/\033\[[\d;?]*\w/', '', $string);
    }

    /**
     * @param $string
     * @return int
     */
    public static function strUtf8Len($string)
    {
        return mb_strlen($string, 'utf-8');
    }

    /**
     * from Symfony
     * @param $string
     * @return int
     */
    public static function strLen($string)
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return \strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }

    /**
     * findValueByNodes
     * @param  array $data
     * @param  array $nodes
     * @param  mixed $default
     * @return mixed
     */
    public static function findValueByNodes(array $data, array $nodes, $default = null)
    {
        $temp = $data;

        foreach ($nodes as $name) {
            if (isset($temp[$name])) {
                $temp = $temp[$name];
            } else {
                $temp = $default;
                break;
            }
        }

        return $temp;
    }

    /**
     * find similar text from an array|Iterator
     * @param string $need
     * @param \Iterator|array $iterator
     * @param int $similarPercent
     * @return array
     */
    public static function findSimilar($need, $iterator, $similarPercent = 45)
    {
        // find similar command names by similar_text()
        $similar = [];

        if (!$need) {
            return $similar;
        }

        foreach ($iterator as $name) {
            similar_text($need, $name, $percent);

            if ($similarPercent <= (int)$percent) {
                $similar[] = $name;
            }
        }

        return $similar;
    }

    /**
     * get key Max Width
     *
     * @param  array $data
     * [
     *     'key1'      => 'value1',
     *     'key2-test' => 'value2',
     * ]
     * @param bool $expectInt
     * @return int
     */
    public static function getKeyMaxWidth(array $data, $expectInt = false)
    {
        $keyMaxWidth = 0;

        foreach ($data as $key => $value) {
            // key is not a integer
            if (!$expectInt || !is_numeric($key)) {
                $width = mb_strlen($key, 'UTF-8');
                $keyMaxWidth = $width > $keyMaxWidth ? $width : $keyMaxWidth;
            }
        }

        return $keyMaxWidth;
    }

    /**
     * dump vars
     * @param array ...$args
     * @return string
     */
    public static function dumpVars(...$args)
    {
        ob_start();
        var_dump(...$args);
        $string = ob_get_clean();

        return preg_replace("/=>\n\s+/", '=> ', $string);
    }

    /**
     * print vars
     * @param array ...$args
     * @return string
     */
    public static function printVars(...$args)
    {
        $string = '';

        foreach ($args as $arg) {
            $string .= print_r($arg, 1) . PHP_EOL;
        }

        return preg_replace("/Array\n\s+\(/", 'Array (', $string);
    }
}
