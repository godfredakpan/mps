<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class Env
{
    public static function envPath()
    {
        $evn = app()->environment();
        if (file_exists(base_path('.env.' . $evn))) {
            return base_path('.env.' . $evn);
        }
        return base_path('.env');
    }

    public static function load($string = null)
    {
        return explode("\n", trim($string ?: self::read()));
    }

    public static function read()
    {
        $evn = app()->environment();
        return file_get_contents(self::envPath());
    }

    public static function update($data, $changeable = true)
    {
        if (empty($data) || !is_array($data) || !is_file(self::envPath())) {
            return false;
        }

        return self::updateData($data);
        // return $changeable ? self::updateChangeable($data) : self::updateData($data);
    }

    private static function getChangeable($string = null)
    {
        $str = Str::after($string ?: self::read(), '# changeable start');
        return Str::before($str, '# changeable end');
    }

    private static function putContents($contents)
    {
        file_put_contents(self::envPath(), $contents, LOCK_EX);
        return true;
    }

    private static function updateChangeable($data)
    {
        $change = [];
        foreach ($data as $key => $val) {
            $change[] = $key . '=' . (Str::contains($val, [' ', '$', '#', ',']) ? '"' . $val . '"' : $val);
        }
        $changeable = "\n" . implode("\n", $change) . "\n";
        $contents   = str_replace(self::getChangeable(), $changeable, self::read());
        return self::putContents($contents);
    }

    private static function updateData($data)
    {
        $env = self::load();
        foreach ($data as $dKey => $dValue) {
            foreach ($env as $eKey => $eValue) {
                $entry      = explode('=', $eValue, 2);
                $env[$eKey] = $entry[0] == $dKey ? $dKey . '=' . (Str::contains($dValue, [' ', '$', '#', ',']) ? '"' . $dValue . '"' : $dValue) : $eValue;
            }
        }
        $contents = implode("\n", $env);
        return self::putContents($contents . "\n");
    }
}
