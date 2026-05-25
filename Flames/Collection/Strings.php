<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Stateless utility class for common string operations.
 *
 * All methods accept mixed $value and cast it to string internally.
 * Multibyte-aware variants always assume UTF-8 encoding.
 */
final class Strings
{
    public static function parse(mixed $value): string
    {
        return (string) $value;
    }

    public static function length(mixed $value, bool $multibyte = false): int
    {
        $str = (string) $value;
        return $multibyte ? mb_strlen($str, 'UTF-8') : strlen($str);
    }

    public static function count(mixed $value, bool $multibyte = false): int
    {
        return self::length($value, $multibyte);
    }

    public static function toLower(mixed $value, bool $multibyte = false): string
    {
        $str = (string) $value;
        return $multibyte ? mb_strtolower($str, 'UTF-8') : strtolower($str);
    }

    public static function toUpper(mixed $value, bool $multibyte = false): string
    {
        $str = (string) $value;
        return $multibyte ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
    }

    /**
     * Capitalises the first letter of each word.
     */
    public static function capitalize(mixed $value): string
    {
        return mb_convert_case((string) $value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Capitalises only the very first character of the string.
     */
    public static function capitalizeFirst(mixed $value): string
    {
        $str = (string) $value;
        if ($str === '') {
            return $str;
        }
        return mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_substr($str, 1, null, 'UTF-8');
    }

    public static function startsWith(mixed $value, mixed $needle, bool $caseSensitive = true): bool
    {
        $str    = (string) $value;
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return str_starts_with(mb_strtolower($str, 'UTF-8'), mb_strtolower($search, 'UTF-8'));
        }
        return str_starts_with($str, $search);
    }

    public static function endsWith(mixed $value, mixed $needle, bool $caseSensitive = true): bool
    {
        $str    = (string) $value;
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return str_ends_with(mb_strtolower($str, 'UTF-8'), mb_strtolower($search, 'UTF-8'));
        }
        return str_ends_with($str, $search);
    }

    public static function contains(mixed $value, mixed $needle, bool $caseSensitive = true): bool
    {
        $str    = (string) $value;
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return str_contains(mb_strtolower($str, 'UTF-8'), mb_strtolower($search, 'UTF-8'));
        }
        return str_contains($str, $search);
    }

    /**
     * Returns true when $value contains at least one element from $needles.
     */
    public static function containsAny(mixed $value, array|Arr $needles, bool $caseSensitive = true): bool
    {
        $str = (string) $value;
        if ($caseSensitive === false) {
            $lower = mb_strtolower($str, 'UTF-8');
            foreach ($needles as $needle) {
                if (str_contains($lower, mb_strtolower((string) $needle, 'UTF-8'))) {
                    return true;
                }
            }
            return false;
        }
        foreach ($needles as $needle) {
            if (str_contains($str, (string) $needle)) {
                return true;
            }
        }
        return false;
    }

    public static function equals(mixed $value, mixed $needle, bool $caseSensitive = true): bool
    {
        $str    = (string) $value;
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return mb_strtolower($str, 'UTF-8') === mb_strtolower($search, 'UTF-8');
        }
        return $str === $search;
    }

    /**
     * Returns true when $value equals at least one element from $needles.
     */
    public static function equalsAny(mixed $value, array|Arr $needles, bool $caseSensitive = true): bool
    {
        $str = (string) $value;
        if ($caseSensitive === false) {
            $lower = mb_strtolower($str, 'UTF-8');
            foreach ($needles as $needle) {
                if ($lower === mb_strtolower((string) $needle, 'UTF-8')) {
                    return true;
                }
            }
            return false;
        }
        foreach ($needles as $needle) {
            if ($str === (string) $needle) {
                return true;
            }
        }
        return false;
    }

    public static function isEmpty(mixed $value): bool
    {
        return ((string) $value) === '';
    }

    public static function replace(mixed $value, mixed $needle, mixed $replace): string
    {
        return str_replace((string) $needle, (string) $replace, (string) $value);
    }

    public static function remove(mixed $value, mixed $needle): string
    {
        return str_replace((string) $needle, '', (string) $value);
    }

    /**
     * Appends $suffix to $value.
     */
    public static function append(mixed $value, mixed $suffix): string
    {
        return ((string) $value) . ((string) $suffix);
    }

    /**
     * Prepends $prefix to $value.
     */
    public static function prepend(mixed $value, mixed $prefix): string
    {
        return ((string) $prefix) . ((string) $value);
    }

    /**
     * Pads $value to $length using $pad on the left, right, or both sides.
     *
     * Use the STR_PAD_* constants for $type.
     */
    public static function pad(mixed $value, int $length, string $pad = ' ', int $type = STR_PAD_RIGHT): string
    {
        return str_pad((string) $value, $length, $pad, $type);
    }

    /**
     * URL-encodes $value.
     *
     * @param bool $raw When false uses rawurlencode (RFC 3986, default);
     *                  when true uses urlencode (application/x-www-form-urlencoded).
     */
    public static function encode(mixed $value, bool $raw = false): string
    {
        $str = (string) $value;
        return $raw ? urlencode($str) : rawurlencode($str);
    }

    /**
     * URL-decodes $value.
     *
     * @param bool $raw When false uses rawurldecode (default); when true uses urldecode.
     */
    public static function decode(mixed $value, bool $raw = false): string
    {
        $str = (string) $value;
        return $raw ? urldecode($str) : rawurldecode($str);
    }

    /**
     * Splits $value by $delimiter and returns the parts as an Arr.
     */
    public static function split(
        mixed  $value,
        string $delimiter    = ',',
        bool   $clearEmpty   = true,
        bool   $keepDelimiter = false,
    ): Arr {
        $str   = (string) $value;
        $parts = $keepDelimiter
            ? preg_split('@(?=' . preg_quote($delimiter, '@') . ')@', $str)
            : explode($delimiter, $str);

        if ($clearEmpty) {
            $parts = array_values(array_filter($parts, static fn($p) => $p !== ''));
        }
        return new Arr($parts);
    }

    /**
     * Splits $value into chunks of exactly $length bytes.
     *
     * The last chunk may be shorter than $length.
     */
    public static function splitLength(mixed $value, mixed $length): Arr
    {
        $chunks = str_split((string) $value, max(1, (int) $length));
        return new Arr($chunks !== false ? $chunks : []);
    }

    /**
     * Splits $value on ASCII spaces and returns every word as an Arr element.
     */
    public static function splitWords(mixed $value): Arr
    {
        return new Arr(explode(' ', (string) $value));
    }

    /**
     * Splits $value on line breaks (\r\n, \r, \n) and returns lines as Arr.
     */
    public static function splitLines(mixed $value): Arr
    {
        $str = str_replace(["\r\n", "\r"], "\n", (string) $value);
        return new Arr(explode("\n", $str));
    }

    /**
     * Returns the portion of $value starting at byte offset $start.
     */
    public static function sub(mixed $value, mixed $start, mixed $length = null): string
    {
        $str   = (string) $value;
        $start = (int)    $start;
        return $length !== null ? substr($str, $start, (int) $length) : substr($str, $start);
    }

    public static function indexOf(mixed $value, mixed $needle, bool $caseSensitive = true): int|null
    {
        $pos = $caseSensitive
            ? strpos((string) $value, (string) $needle)
            : stripos((string) $value, (string) $needle);
        return $pos !== false ? $pos : null;
    }

    public static function lastIndexOf(mixed $value, mixed $needle, bool $caseSensitive = true): int|null
    {
        $pos = $caseSensitive
            ? strrpos((string) $value, (string) $needle)
            : strripos((string) $value, (string) $needle);
        return $pos !== false ? $pos : null;
    }

    /**
     * Strips characters from the start and end of $value.
     *
     * @param mixed|null $charList Characters to strip (null = PHP default whitespace).
     * @param bool       $multibyte Use a regex-based trim for multibyte character lists.
     */
    public static function trim(mixed $value, mixed $charList = null, bool $multibyte = false): string
    {
        $str = (string) $value;
        if ($charList === null) {
            return trim($str);
        }
        $chars = (string) $charList;
        if ($multibyte) {
            $escaped = str_replace('/', '\/', preg_quote($chars));
            return (string) preg_replace("/(^[$escaped]+)|([$escaped]+$)/us", '', $str);
        }
        return trim($str, $chars);
    }

    public static function addSlashes(mixed $value): string
    {
        return addslashes((string) $value);
    }

    public static function removeSlashes(mixed $value): string
    {
        return stripslashes((string) $value);
    }

    public static function toBase64(mixed $value): string
    {
        return base64_encode((string) $value);
    }

    /**
     * Decodes a Base64 string, returning null when the input is invalid.
     */
    public static function fromBase64(mixed $value): string|null
    {
        $decoded = base64_decode((string) $value, strict: true);
        return $decoded !== false ? $decoded : null;
    }

    /**
     * Encodes $value as a hexadecimal string.
     */
    public static function toHex(mixed $value): string
    {
        return bin2hex((string) $value);
    }

    /**
     * Decodes a hexadecimal string, returning null when the input is invalid.
     */
    public static function fromHex(mixed $value): string|null
    {
        $decoded = hex2bin((string) $value);
        return $decoded !== false ? $decoded : null;
    }

    /**
     * Keeps only ASCII digit characters (0-9) and any characters in $whitelist.
     */
    public static function getOnlyNumbers(mixed $value, mixed $whitelist = ''): string
    {
        $escaped = preg_quote((string) $whitelist, '/');
        return (string) preg_replace("/[^0-9{$escaped}]/", '', (string) $value);
    }

    /**
     * Keeps only ASCII letter characters (a-z, A-Z).
     */
    public static function getOnlyLetters(mixed $value): string
    {
        return (string) preg_replace('/[^a-zA-Z]+/', '', (string) $value);
    }

    /**
     * Keeps only ASCII letter and digit characters (a-z, A-Z, 0-9).
     */
    public static function getOnlyLettersAndNumbers(mixed $value): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9]+/', '', (string) $value);
    }

    /**
     * Removes all whitespace characters (spaces, tabs, line breaks).
     *
     * @param bool $includeLineBreaks Also strip \r and \n when true (default).
     */
    public static function removeSpaces(mixed $value, bool $includeLineBreaks = true): string
    {
        $str     = trim((string) $value);
        $pattern = $includeLineBreaks ? '/[ \t\r\n]+/' : '/[ \t]+/';
        return (string) preg_replace($pattern, '', $str);
    }

    /**
     * Removes common special characters from $value.
     *
     * @param bool $includeUnderline Also remove underscores when true (default).
     */
    public static function removeSpecialCharacters(mixed $value, bool $includeUnderline = true): string
    {
        $ul      = $includeUnderline ? '_' : '';
        $pattern = '/[-`~!@#$%^&*' . $ul . '()+={}\\[\\]\\\\|;:\'",.><?\/]+/';
        return (string) preg_replace($pattern, '', (string) $value);
    }

    /**
     * Removes all digit characters from $value.
     */
    public static function removeNumbers(mixed $value): string
    {
        return (string) preg_replace('/[0-9]+/', '', (string) $value);
    }

    /**
     * Strips all HTML and PHP tags from $value.
     *
     * @param bool $cleanContent Also collapse internal whitespace when true.
     */
    public static function stripHtmlTags(mixed $value, bool $cleanContent = false): string
    {
        $str = (string) $value;
        if ($cleanContent) {
            $str = str_replace(['<', '>'], [' <', '> '], $str);
            $str = strip_tags($str);
            $str = (string) preg_replace('/\s+/', ' ', $str);
            return trim($str);
        }
        return strip_tags($str);
    }

    /**
     * Truncates $value to at most $limit characters.
     *
     * When $wordBoundary is true the string is never cut mid-word;
     * the last complete word fitting within $limit characters is returned.
     */
    public static function truncate(mixed $value, int $limit, bool $wordBoundary = false): string
    {
        $str = (string) $value;
        if (mb_strlen($str, 'UTF-8') <= $limit) {
            return $str;
        }
        if ($wordBoundary === false) {
            return mb_substr($str, 0, $limit, 'UTF-8');
        }

        $words   = explode(' ', $str);
        $result  = '';
        foreach ($words as $word) {
            $candidate = $result === '' ? $word : $result . ' ' . $word;
            if (mb_strlen($candidate, 'UTF-8') > $limit) {
                break;
            }
            $result = $candidate;
        }
        return $result;
    }

    /**
     * Limits $value to $limit bytes, truncating from the right.
     */
    public static function limit(mixed $value, mixed $limit = 10): string
    {
        return substr((string) $value, 0, (int) $limit);
    }

    /**
     * Generates a cryptographically random alphanumeric string of $length characters.
     */
    public static function getRandom(int $length = 32): string
    {
        $chars  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max    = strlen($chars) - 1;
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }
        return $result;
    }

    /**
     * Converts $value to a URL-friendly slug.
     *
     * Removes accents, lowercases, replaces non-alphanumeric sequences with
     * $delimiter, and strips leading/trailing delimiters.
     */
    public static function toSlug(mixed $value, string $delimiter = '-'): string
    {
        $str = self::removeAccents((string) $value);
        $str = mb_strtolower($str, 'UTF-8');
        $d   = preg_quote($delimiter, '/');
        $str = (string) preg_replace('/[^a-z0-9]+/', $delimiter, $str);
        $str = (string) preg_replace('/^' . $d . '+|' . $d . '+$/', '', $str);
        return $str;
    }

    /**
     * Replaces accented / diacritic characters with their plain ASCII equivalents.
     *
     * Supports UTF-8 and ISO-8859-1 encoded strings.
     */
    public static function removeAccents(mixed $value): string
    {
        $str = (string) $value;
        if (!preg_match('/[\x80-\xff]/', $str)) {
            return $str;
        }
        return mb_check_encoding($str, 'UTF-8')
            ? self::removeAccentsUtf8($str)
            : self::removeAccentsLatin($str);
    }

    private static function removeAccentsUtf8(string $str): string
    {
        static $map = null;
        if ($map === null) {
            $map = [
                "\xc2\xaa" => 'a',  "\xc2\xba" => 'o',
                "\xc3\x80" => 'A',  "\xc3\x81" => 'A',  "\xc3\x82" => 'A',
                "\xc3\x83" => 'A',  "\xc3\x84" => 'A',  "\xc3\x85" => 'A',
                "\xc3\x86" => 'AE', "\xc3\x87" => 'C',  "\xc3\x88" => 'E',
                "\xc3\x89" => 'E',  "\xc3\x8a" => 'E',  "\xc3\x8b" => 'E',
                "\xc3\x8c" => 'I',  "\xc3\x8d" => 'I',  "\xc3\x8e" => 'I',
                "\xc3\x8f" => 'I',  "\xc3\x90" => 'D',  "\xc3\x91" => 'N',
                "\xc3\x92" => 'O',  "\xc3\x93" => 'O',  "\xc3\x94" => 'O',
                "\xc3\x95" => 'O',  "\xc3\x96" => 'O',  "\xc3\x98" => 'O',
                "\xc3\x99" => 'U',  "\xc3\x9a" => 'U',  "\xc3\x9b" => 'U',
                "\xc3\x9c" => 'U',  "\xc3\x9d" => 'Y',  "\xc3\x9e" => 'TH',
                "\xc3\x9f" => 's',  "\xc3\xa0" => 'a',  "\xc3\xa1" => 'a',
                "\xc3\xa2" => 'a',  "\xc3\xa3" => 'a',  "\xc3\xa4" => 'a',
                "\xc3\xa5" => 'a',  "\xc3\xa6" => 'ae', "\xc3\xa7" => 'c',
                "\xc3\xa8" => 'e',  "\xc3\xa9" => 'e',  "\xc3\xaa" => 'e',
                "\xc3\xab" => 'e',  "\xc3\xac" => 'i',  "\xc3\xad" => 'i',
                "\xc3\xae" => 'i',  "\xc3\xaf" => 'i',  "\xc3\xb0" => 'd',
                "\xc3\xb1" => 'n',  "\xc3\xb2" => 'o',  "\xc3\xb3" => 'o',
                "\xc3\xb4" => 'o',  "\xc3\xb5" => 'o',  "\xc3\xb6" => 'o',
                "\xc3\xb8" => 'o',  "\xc3\xb9" => 'u',  "\xc3\xba" => 'u',
                "\xc3\xbb" => 'u',  "\xc3\xbc" => 'u',  "\xc3\xbd" => 'y',
                "\xc3\xbe" => 'th', "\xc3\xbf" => 'y',
                "\xc4\x80" => 'A',  "\xc4\x81" => 'a',  "\xc4\x82" => 'A',
                "\xc4\x83" => 'a',  "\xc4\x84" => 'A',  "\xc4\x85" => 'a',
                "\xc4\x86" => 'C',  "\xc4\x87" => 'c',  "\xc4\x88" => 'C',
                "\xc4\x89" => 'c',  "\xc4\x8a" => 'C',  "\xc4\x8b" => 'c',
                "\xc4\x8c" => 'C',  "\xc4\x8d" => 'c',  "\xc4\x8e" => 'D',
                "\xc4\x8f" => 'd',  "\xc4\x90" => 'D',  "\xc4\x91" => 'd',
                "\xc4\x92" => 'E',  "\xc4\x93" => 'e',  "\xc4\x94" => 'E',
                "\xc4\x95" => 'e',  "\xc4\x96" => 'E',  "\xc4\x97" => 'e',
                "\xc4\x98" => 'E',  "\xc4\x99" => 'e',  "\xc4\x9a" => 'E',
                "\xc4\x9b" => 'e',  "\xc4\x9c" => 'G',  "\xc4\x9d" => 'g',
                "\xc4\x9e" => 'G',  "\xc4\x9f" => 'g',  "\xc4\xa0" => 'G',
                "\xc4\xa1" => 'g',  "\xc4\xa2" => 'G',  "\xc4\xa3" => 'g',
                "\xc4\xa4" => 'H',  "\xc4\xa5" => 'h',  "\xc4\xa6" => 'H',
                "\xc4\xa7" => 'h',  "\xc4\xa8" => 'I',  "\xc4\xa9" => 'i',
                "\xc4\xaa" => 'I',  "\xc4\xab" => 'i',  "\xc4\xac" => 'I',
                "\xc4\xad" => 'i',  "\xc4\xae" => 'I',  "\xc4\xaf" => 'i',
                "\xc4\xb0" => 'I',  "\xc4\xb1" => 'i',  "\xc4\xb2" => 'IJ',
                "\xc4\xb3" => 'ij', "\xc4\xb4" => 'J',  "\xc4\xb5" => 'j',
                "\xc4\xb6" => 'K',  "\xc4\xb7" => 'k',  "\xc4\xb8" => 'k',
                "\xc4\xb9" => 'L',  "\xc4\xba" => 'l',  "\xc4\xbb" => 'L',
                "\xc4\xbc" => 'l',  "\xc4\xbd" => 'L',  "\xc4\xbe" => 'l',
                "\xc4\xbf" => 'L',  "\xc5\x80" => 'l',  "\xc5\x81" => 'L',
                "\xc5\x82" => 'l',  "\xc5\x83" => 'N',  "\xc5\x84" => 'n',
                "\xc5\x85" => 'N',  "\xc5\x86" => 'n',  "\xc5\x87" => 'N',
                "\xc5\x88" => 'n',  "\xc5\x89" => 'N',  "\xc5\x8a" => 'n',
                "\xc5\x8b" => 'N',  "\xc5\x8c" => 'O',  "\xc5\x8d" => 'o',
                "\xc5\x8e" => 'O',  "\xc5\x8f" => 'o',  "\xc5\x90" => 'O',
                "\xc5\x91" => 'o',  "\xc5\x92" => 'OE', "\xc5\x93" => 'oe',
                "\xc5\x94" => 'R',  "\xc5\x95" => 'r',  "\xc5\x96" => 'R',
                "\xc5\x97" => 'r',  "\xc5\x98" => 'R',  "\xc5\x99" => 'r',
                "\xc5\x9a" => 'S',  "\xc5\x9b" => 's',  "\xc5\x9c" => 'S',
                "\xc5\x9d" => 's',  "\xc5\x9e" => 'S',  "\xc5\x9f" => 's',
                "\xc5\xa0" => 'S',  "\xc5\xa1" => 's',  "\xc5\xa2" => 'T',
                "\xc5\xa3" => 't',  "\xc5\xa4" => 'T',  "\xc5\xa5" => 't',
                "\xc5\xa6" => 'T',  "\xc5\xa7" => 't',  "\xc5\xa8" => 'U',
                "\xc5\xa9" => 'u',  "\xc5\xaa" => 'U',  "\xc5\xab" => 'u',
                "\xc5\xac" => 'U',  "\xc5\xad" => 'u',  "\xc5\xae" => 'U',
                "\xc5\xaf" => 'u',  "\xc5\xb0" => 'U',  "\xc5\xb1" => 'u',
                "\xc5\xb2" => 'U',  "\xc5\xb3" => 'u',  "\xc5\xb4" => 'W',
                "\xc5\xb5" => 'w',  "\xc5\xb6" => 'Y',  "\xc5\xb7" => 'y',
                "\xc5\xb8" => 'Y',  "\xc5\xb9" => 'Z',  "\xc5\xba" => 'z',
                "\xc5\xbb" => 'Z',  "\xc5\xbc" => 'z',  "\xc5\xbd" => 'Z',
                "\xc5\xbe" => 'z',  "\xc5\xbf" => 's',
                "\xc8\x98" => 'S',  "\xc8\x99" => 's',
                "\xc8\x9a" => 'T',  "\xc8\x9b" => 't',
                "\xe2\x82\xac" => 'E',
                "\xc2\xa3"     => '',
            ];
        }
        return strtr($str, $map);
    }

    private static function removeAccentsLatin(string $str): string
    {
        $in  = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
             . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193)
             . chr(194) . chr(195) . chr(196) . chr(197) . chr(199) . chr(200)
             . chr(201) . chr(202) . chr(203) . chr(204) . chr(205) . chr(206)
             . chr(207) . chr(209) . chr(210) . chr(211) . chr(212) . chr(213)
             . chr(214) . chr(216) . chr(217) . chr(218) . chr(219) . chr(220)
             . chr(221) . chr(224) . chr(225) . chr(226) . chr(227) . chr(228)
             . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
             . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242)
             . chr(243) . chr(244) . chr(245) . chr(246) . chr(248) . chr(249)
             . chr(250) . chr(251) . chr(252) . chr(253) . chr(255);

        $out = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';
        $str = strtr($str, $in, $out);

        $dIn  = [chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254)];
        $dOut = ['OE',     'oe',     'AE',     'DH',     'TH',     'ss',     'ae',     'dh',     'th'];
        return str_replace($dIn, $dOut, $str);
    }
}
