<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation;

/**
 * Returns the plural rules for a given locale.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PluralizationRules
{
    private static $rules = [];

    /**
     * Returns the plural position to use for the given locale and number.
     *
     * @param int    $number The number
     * @param string $locale The locale
     *
     * @return int The plural position
     */
    public static function get($number, $locale)
    {
        if ('pt_BR' === $locale) {
            // temporary set a locale for brazilian
            $locale = 'xbr';
        }

        if (strlen($locale) > 3) {
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));
        }

        if (isset(self::$rules[$locale])) {
            $return = call_user_func(self::$rules[$locale], $number);

            if (!is_int($return) || $return < 0) {
                return 0;
            }

            return $return;
        }

        /*
         * The plural rules are derived from code of the Zend Framework (2010-09-25),
         * which is subject to the new BSD license (http://framework.zend.com/license/new-bsd).
         * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
         */
        return match ($locale) {
            'az', 'bo', 'dz', 'id', 'ja', 'jv', 'ka', 'km', 'kn', 'ko', 'ms', 'th', 'tr', 'vi', 'zh' => 0,
            'af', 'bn', 'bg', 'ca', 'da', 'de', 'el', 'en', 'eo', 'es', 'et', 'eu', 'fa', 'fi', 'fo', 'fur', 'fy', 'gl', 'gu', 'ha', 'he', 'hu', 'is', 'it', 'ku', 'lb', 'ml', 'mn', 'mr', 'nah', 'nb', 'ne', 'nl', 'nn', 'no', 'oc', 'om', 'or', 'pa', 'pap', 'ps', 'pt', 'so', 'sq', 'sv', 'sw', 'ta', 'te', 'tk', 'ur', 'zu' => (1 == $number) ? 0 : 1,
            'am', 'bh', 'fil', 'fr', 'gun', 'hi', 'hy', 'ln', 'mg', 'nso', 'xbr', 'ti', 'wa' => ((0 == $number) || (1 == $number)) ? 0 : 1,
            'be', 'bs', 'hr', 'ru', 'sr', 'uk' => ((1 == $number % 10) && (11 != $number % 100)) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2),
            'cs', 'sk' => (1 == $number) ? 0 : ((($number >= 2) && ($number <= 4)) ? 1 : 2),
            'ga' => (1 == $number) ? 0 : ((2 == $number) ? 1 : 2),
            'lt' => ((1 == $number % 10) && (11 != $number % 100)) ? 0 : ((($number % 10 >= 2) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2),
            'sl' => (1 == $number % 100) ? 0 : ((2 == $number % 100) ? 1 : (((3 == $number % 100) || (4 == $number % 100)) ? 2 : 3)),
            'mk' => (1 == $number % 10) ? 0 : 1,
            'mt' => (1 == $number) ? 0 : (((0 == $number) || (($number % 100 > 1) && ($number % 100 < 11))) ? 1 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 2 : 3)),
            'lv' => (0 == $number) ? 0 : (((1 == $number % 10) && (11 != $number % 100)) ? 1 : 2),
            'pl' => (1 == $number) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 1 : 2),
            'cy' => (1 == $number) ? 0 : ((2 == $number) ? 1 : (((8 == $number) || (11 == $number)) ? 2 : 3)),
            'ro' => (1 == $number) ? 0 : (((0 == $number) || (($number % 100 > 0) && ($number % 100 < 20))) ? 1 : 2),
            'ar' => (0 == $number) ? 0 : ((1 == $number) ? 1 : ((2 == $number) ? 2 : ((($number % 100 >= 3) && ($number % 100 <= 10)) ? 3 : ((($number % 100 >= 11) && ($number % 100 <= 99)) ? 4 : 5)))),
            default => 0,
        };
    }

    /**
     * Overrides the default plural rule for a given locale.
     *
     * @param callable $rule   A PHP callable
     * @param string   $locale The locale
     */
    public static function set(callable $rule, $locale)
    {
        if ('pt_BR' === $locale) {
            // temporary set a locale for brazilian
            $locale = 'xbr';
        }

        if (strlen($locale) > 3) {
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));
        }

        self::$rules[$locale] = $rule;
    }
}
