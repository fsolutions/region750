<?php


namespace App\Bundles\Keyboard;


class Switcher
{
    private static $switch = array(
        "а" => "f", "А" => "F",
        "б" => ",", "Б" => "<",
        "в" => "d", "В" => "D",
        "г" => "u", "Г" => "D",
        "д" => "l", "Д" => "L",
        "е" => "t", "Е" => "T",
        "ё" => "`", "Ё" => "~",
        "ж" => ";", "Ж" => ":",
        "з" => "p", "З" => "P",
        "и" => "b", "И" => "B",
        "й" => "q", "Й" => "Q",
        "к" => "r", "К" => "R",
        "л" => "k", "Л" => "K",
        "м" => "v", "М" => "V",
        "н" => "y", "Н" => "Y",
        "о" => "j", "О" => "J",
        "п" => "g", "П" => "G",
        "р" => "h", "Р" => "H",
        "с" => "c", "С" => "C",
        "т" => "n", "Т" => "N",
        "у" => "e", "У" => "E",
        "ф" => "a", "Ф" => "A",
        "х" => "[", "Х" => "{",
        "ц" => "w", "Ц" => "W",
        "ч" => "x", "Ч" => "X",
        "ш" => "i", "Ш" => "I",
        "щ" => "o", "Щ" => "O",
        "ъ" => "]", "Ъ" => "}",
        "ы" => "s", "Ы" => "S",
        "ь" => "m", "Ь" => "M",
        "э" => "'", "Э" => "\"",
        "ю" => ".", "Ю" => ">",
        "я" => "z", "Я" => "Z",
        "," => "?", "." => "/"
    );

    public static function fromCyrillic($string)
    {
        return strtr($string, self::$switch);
    }

    public static function toCyrillic($string)
    {
        return strtr($string, array_flip(self::$switch));
    }

}
