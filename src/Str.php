<?php namespace CupOfTea\Support;

use Illuminate\Support\Str as Illuminate_Str;

class Str extends Illuminate_Str
{
    /**
     * Get the substring that two strings have in common.
     *
     * @param  string  $str1
     * @param  string  $str2
     * @return string
     */
    public static function intersect($str1, $str2)
    {
        if ($str1 === $str2) {
            return $str1;
        }
        
        $str = '';
        $str1 = str_split($str1);
        $str2 = str_split($str2);
        
        if (count($str1) > count($str2)) {
            swap($str1, $str2);
        }
        
        foreach ($str1 as $i => $char) {
            if ($char !== $str2[$i]) {
                break;
            }
            
            $str .= $char;
        }
        
        return $str;
    }
    
    /**
     * Get the substring from $str1 that is unique.
     *
     * @param  string  $str1
     * @param  string  $str2
     * @return string
     */
    public static function complement($str1, $str2)
    {
        return str_replace(self::intersect($str1, $str2), '', $str1);
    }
    
    /**
     * Get the substrings from $str1 and $str2 that is unique.
     *
     * @param  string  $str1
     * @param  string  $str2
     * @return array
     */
    public static function difference($str1, $str2)
    {
        return [self::complement($str1, $str2), self::complement($str2, $str1)];
    }
}
