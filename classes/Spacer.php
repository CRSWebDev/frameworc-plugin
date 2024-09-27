<?php
namespace CRSCompany\FrameworC\Classes;

class Spacer
{
    static protected $wordsToReplace = ["A", "Bc.", "Dle", "Do", "I", "Ing.", "Je", "K", "Na", "Nebo", "O", "Pod", "Po", "Pro", "Před", "S", "Se", "Si", "U", "Už", "V", "Z", "Za", "Že", "a", "dle", "do", "i", "je", "k", "ke", "na", "nebo", "o", "po", "pod", "pro", "před", "s", "se", "si", "u", "už", "v", "za", "z", "že"];

    static function addNbsp($inputText)
    {
        if($inputText === "")
        {
            return "Pro správné fungování Nbsp generátoru je nutné vložit text.";
        }

        foreach(self::$wordsToReplace as $word) {
            $regex = '/\b' . $word . '\s/';
            $inputText = preg_replace($regex, $word . "&nbsp;", $inputText);
        }

        $inputText = preg_replace('/(\d+)\s(?=[\dA-Za-zÀ-ž])/', "$1&nbsp;", $inputText);

        $currencies = ["Kč", "CZK", "USD", "EUR", "GBP", "JPY", "AUD", "CAD", "CHF", "CNY", "SEK", "NZD"];

        foreach($currencies as $currency) {
            $inputText = preg_replace('/(\d+)\s(' . $currency . ')/', "$1&nbsp;$2", $inputText);
        }

        $inputText = str_replace('[[space]]', ' ', $inputText);

        return $inputText;
    }
}
