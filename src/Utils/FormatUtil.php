<?php

/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-18
 * Time: 19:34
 */

namespace Inhere\Console\Utils;

/**
 * Class FormatUtil
 * @package Inhere\Console\Utils
 */
final class FormatUtil
{
    /**
     * to camel
     * @param string $name
     * @return mixed|string
     */
    public static function camelCase($name)
    {
        $name = trim($name, '-_');
        // convert 'first-second' to 'firstSecond'
        if (strpos($name, '-')) {
            $name = ucwords(str_replace('-', ' ', $name));
            $name = str_replace(' ', '', lcfirst($name));
        }

        return $name;
    }

    /**
     * @param string $string
     * @param int $indent
     * @param string $indentChar
     * @return string
     */
    public static function applyIndent($string, $indent = 2, $indentChar = ' ')
    {
        if (!$string || $indent <= 0) {
            return $string;
        }
        $new = '';
        $list = explode("\n", $string);
        $indentStr = str_repeat($indentChar ?: ' ', $indent);
        foreach ($list as $value) {
            $new .= $indentStr . trim($value) . "\n";
        }

        return $new;
    }

    /**
     * Word wrap text with indentation to fit the screen size
     * If screen size could not be detected, or the indentation is greater than the screen size, the text will not be wrapped.
     * The first line will **not** be indented, so `wrapText("Lorem ipsum dolor sit amet.", 4)` will result in the
     * following output, given the screen width is 16 characters:
     * ```
     * Lorem ipsum
     *     dolor sit
     *     amet.
     * ```
     * @param string $text the text to be wrapped
     * @param integer $indent number of spaces to use for indentation.
     * @param integer $width
     * @return string the wrapped text.
     * @from yii2
     */
    public static function wrapText($text, $indent = 0, $width = 0)
    {
        if (!$text) {
            return $text;
        }
        if ((int)$width <= 0) {
            $size = CliUtil::getScreenSize();
            if ($size === false || $size[0] <= $indent) {
                return $text;
            }
            $width = $size[0];
        }
        $pad = str_repeat(' ', $indent);
        $lines = explode("\n", wordwrap($text, $width - $indent, "\n", true));
        $first = true;
        foreach ($lines as $i => $line) {
            if ($first) {
                $first = false;
                continue;
            }
            $lines[$i] = $pad . $line;
        }

        return $pad . '  ' . implode("\n", $lines);
    }

    /**
     * @param string $optsStr
     */
    public static function annotationOptions($optsStr)
    {
    }

    /**
     * this is a command's description message
     * the second line text
     * @format
     * @usage usage message
     * @arguments(format=true)
     *  arg1  argument description 1
     *        the second line
     *  a2,arg2  argument description 2
     *        the second line
     * @arguments(
     *  arg1="argument description 1
     *        the second line",
     *  "a2,arg2"="argument description 2
     *        the second line"
     * )
     * @options
     *  -s, --long LONG option description 1
     *  --opt    OPT   option description 2
     * @example example text one
     *  the second line example
     * @param string $argsStr
     */
    public static function annotationArguments($argsStr)
    {
    }

    /**
     * @param array $options
     * @return array
     */
    public static function alignmentOptions(array $options)
    {
        // e.g '-h, --help'
        $hasShort = (bool)strpos(implode(array_keys($options), ''), ',');
        if (!$hasShort) {
            return $options;
        }
        $formatted = [];
        foreach ($options as $name => $des) {
            if (!($name = trim($name, ', '))) {
                continue;
            }
            if (!strpos($name, ',')) {
                // padding length equals to '-h, '
                $name = '    ' . $name;
            } else {
                $name = str_replace([' ', ','], ['', ', '], $name);
            }
            $formatted[$name] = $des;
        }

        return $formatted;
    }

    /**
     * 计算并格式化资源消耗
     * @param int $startTime
     * @param int|float $startMem
     * @param array $info
     * @return array
     */
    public static function runtime($startTime, $startMem, array $info = [])
    {
        $info['startTime'] = $startTime;
        $info['endTime'] = microtime(true);
        $info['endMemory'] = memory_get_usage(true);
        // 计算运行时间
        $info['runtime'] = number_format(($info['endTime'] - $startTime) * 1000, 3) . 'ms';
        if ($startMem) {
            $startMem = array_sum(explode(' ', $startMem));
            $endMem = array_sum(explode(' ', $info['endMemory']));
            $info['memory'] = number_format(($endMem - $startMem) / 1024, 3) . 'kb';
        }
        $peakMem = memory_get_peak_usage() / 1024 / 1024;
        $info['peakMemory'] = number_format($peakMem, 3) . 'Mb';

        return $info;
    }

    /**
     * @param float $memory
     * @return string
     * ```
     * FormatUtil::memoryUsage(memory_get_usage(true));
     * ```
     */
    public static function memoryUsage($memory)
    {
        if ($memory >= 1024 * 1024 * 1024) {
            return sprintf('%.1f GiB', $memory / 1024 / 1024 / 1024);
        }
        if ($memory >= 1024 * 1024) {
            return sprintf('%.1f MiB', $memory / 1024 / 1024);
        }
        if ($memory >= 1024) {
            return sprintf('%d KiB', $memory / 1024);
        }

        return sprintf('%d B', $memory);
    }

    /**
     * format Timestamp
     * @param  int $secs
     * @return string
     */
    public static function timestamp($secs)
    {
        static $timeFormats = [[0, '< 1 sec'], [1, '1 sec'], [2, 'secs', 1], [60, '1 min'], [120, 'mins', 60], [3600, '1 hr'], [7200, 'hrs', 3600], [86400, '1 day'], [172800, 'days', 86400]];
        foreach ($timeFormats as $index => $format) {
            if ($secs >= $format[0]) {
                if (isset($timeFormats[$index + 1]) && ($secs < $timeFormats[$index + 1][0] || $index === \count($timeFormats) - 1)) {
                    if (2 === \count($format)) {
                        return $format[1];
                    }

                    return floor($secs / $format[2]) . ' ' . $format[1];
                }
            }
        }

        return date('Y-m-d H:i:s', $secs);
    }

    /**
     * @param $string
     * @param $width
     * @return array
     */
    public static function splitStringByWidth($string, $width)
    {
        // str_split is not suitable for multi-byte characters, we should use preg_split to get char array properly.
        // additionally, array_slice() is not enough as some character has doubled width.
        // we need a function to split string not by character count but by string width
        if (false === ($encoding = mb_detect_encoding($string, null, true))) {
            return str_split($string, $width);
        }
        $utf8String = mb_convert_encoding($string, 'utf8', $encoding);
        $lines = array();
        $line = '';
        foreach (preg_split('//u', $utf8String) as $char) {
            // test if $char could be appended to current line
            if (mb_strwidth($line . $char, 'utf8') <= $width) {
                $line .= $char;
                continue;
            }
            // if not, push current line to array and make new line
            $lines[] = str_pad($line, $width);
            $line = $char;
        }
        if ('' !== $line) {
            $lines[] = \count($lines) ? str_pad($line, $width) : $line;
        }
        mb_convert_variables($encoding, 'utf8', $lines);

        return $lines;
    }

    /**
     * splice Array
     * @param  array $data
     * e.g [
     *     'system'  => 'Linux',
     *     'version'  => '4.4.5',
     * ]
     * @param  array $opts
     * @return string
     */
    public static function spliceKeyValue(array $data, array $opts = [])
    {
        $text = '';
        $opts = array_merge([
            'leftChar' => '',
            // e.g '  ', ' * '
            'sepChar' => ' ',
            // e.g ' | ' OUT: key | value
            'keyStyle' => '',
            // e.g 'info','comment'
            'valStyle' => '',
            // e.g 'info','comment'
            'keyMinWidth' => 8,
            'keyMaxWidth' => null,
            // if not set, will automatic calculation
            'ucFirst' => true,
        ], $opts);
        if (!is_numeric($opts['keyMaxWidth'])) {
            $opts['keyMaxWidth'] = Helper::getKeyMaxWidth($data);
        }
        // compare
        if ((int)$opts['keyMinWidth'] > $opts['keyMaxWidth']) {
            $opts['keyMaxWidth'] = $opts['keyMinWidth'];
        }
        $keyStyle = trim($opts['keyStyle']);
        foreach ($data as $key => $value) {
            $hasKey = !\is_int($key);
            $text .= $opts['leftChar'];
            if ($hasKey && $opts['keyMaxWidth']) {
                $key = str_pad($key, $opts['keyMaxWidth'], ' ');
                $text .= Helper::wrapTag($key, $keyStyle) . $opts['sepChar'];
            }
            // if value is array, translate array to string
            if (\is_array($value)) {
                $temp = '';
                /** @var array $value */
                foreach ($value as $k => $val) {
                    if (\is_bool($val)) {
                        $val = $val ? 'True' : 'False';
                    } else {
                        $val = is_scalar($val) ? (string)$val : \gettype($val);
                    }
                    $temp .= (!is_numeric($k) ? "{$k}: " : '') . "{$val}, ";
                }
                $value = rtrim($temp, ' ,');
            } else {
                if (\is_bool($value)) {
                    $value = $value ? 'True' : 'False';
                } else {
                    $value = (string)$value;
                }
            }
            $value = $hasKey && $opts['ucFirst'] ? ucfirst($value) : $value;
            $text .= Helper::wrapTag($value, $opts['valStyle']) . "\n";
        }

        return $text;
    }
}