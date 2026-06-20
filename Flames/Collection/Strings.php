<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for common string operations.
 *
 * All methods accept mixed $value and cast it to string internally.
 * Multibyte-aware variants always assume UTF-8 encoding.
 */
final class Strings
{
    use PrototypeTrait;

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
        foreach ($needles as $needle) {
            if (self::equals($value, $needle, $caseSensitive)) {
                return true;
            }
        }
        return false;
    }

    public static function isEmpty(mixed $value): bool
    {
        return ((string) $value) === '';
    }

    public static function replace(
        mixed $value,
        mixed $search,
        mixed $replace,
        bool  $caseSensitive = true,
        int   &$count = 0,
    ): string {
        if ($caseSensitive === false) {
            return str_ireplace((string) $search, (string) $replace, (string) $value, $count);
        }
        return str_replace((string) $search, (string) $replace, (string) $value, $count);
    }

    /**
     * Replaces multiple search strings at once, like equalsAny is to equals.
     *
     * When $replace is a string, every match is replaced with that value.
     */
    public static function replaceAny(
        mixed            $value,
        array|Arr        $search,
        array|Arr|string $replace,
        bool             $caseSensitive = true,
        int              &$count = 0,
    ): string {
        $searchArr  = self::toPairArray($search);
        $replaceVal = is_string($replace) ? $replace : self::toPairArray($replace);

        if ($caseSensitive === false) {
            return str_ireplace($searchArr, $replaceVal, (string) $value, $count);
        }
        return str_replace($searchArr, $replaceVal, (string) $value, $count);
    }

    /**
     * Replaces text matching a regex pattern (preg_replace).
     */
    public static function replaceRegex(
        mixed  $value,
        string $pattern,
        mixed  $replacement,
        int    $limit = -1,
        int    &$count = 0,
    ): string {
        $replacementArr = $replacement instanceof Arr ? $replacement->toArray() : $replacement;
        return (string) preg_replace($pattern, $replacementArr, (string) $value, $limit, $count);
    }

    /**
     * Replaces a substring by offset/length (substr_replace).
     */
    public static function replaceSub(
        mixed  $value,
        mixed  $replace,
        mixed  $offset,
        mixed  $length = null,
    ): string {
        return substr_replace((string) $value, (string) $replace, (int) $offset, $length);
    }

    public static function remove(mixed $value, mixed $needle, bool $caseSensitive = true): string
    {
        return self::replace($value, $needle, '', $caseSensitive);
    }

    /**
     * Removes every occurrence of each needle, like removeAny is to remove.
     */
    public static function removeAny(mixed $value, array|Arr $needles, bool $caseSensitive = true): string
    {
        return self::replaceAny($value, $needles, '', $caseSensitive);
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
        // str_split never returns false when length >= 1 (PHP 8+)
        return new Arr(str_split((string) $value, max(1, (int) $length)) ?: []);
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

    public static function trimLeft(mixed $value, mixed $charList = null): string
    {
        $str = (string) $value;
        return $charList === null ? ltrim($str) : ltrim($str, (string) $charList);
    }

    public static function trimRight(mixed $value, mixed $charList = null): string
    {
        $str = (string) $value;
        return $charList === null ? rtrim($str) : rtrim($str, (string) $charList);
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

        // Pre-slice to $limit chars so the loop never inspects more text than needed
        $str    = mb_substr($str, 0, $limit, 'UTF-8');
        $words  = explode(' ', $str);
        $result = '';
        $len    = 0;

        foreach ($words as $word) {
            $wordLen   = mb_strlen($word, 'UTF-8');
            $candidate = $result === '' ? $wordLen : $len + 1 + $wordLen;
            if ($candidate > $limit) {
                break;
            }
            $result = $result === '' ? $word : $result . ' ' . $word;
            $len    = $candidate;
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
     *
     * Uses a single random_bytes() call (one CSPRNG syscall) instead of invoking
     * random_int() once per character. Bytes >= 248 are rejected to ensure a
     * perfectly uniform distribution across the 62-character alphabet
     * (248 = 4 × 62, the largest multiple of 62 that fits in a byte).
     */
    public static function getRandom(int $length = 32): string
    {
        if ($length <= 0) {
            return '';
        }

        $chars  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        $count  = 0;

        while ($count < $length) {
            $needed = $length - $count;
            // ~3.1 % rejection rate; extra headroom avoids a second syscall in practice
            $bytes = random_bytes((int) ceil($needed * 1.07) + 8);
            $bLen  = strlen($bytes);
            for ($i = 0; $i < $bLen && $count < $length; $i++) {
                $b = ord($bytes[$i]);
                if ($b < 248) {
                    $result .= $chars[$b % 62];
                    $count++;
                }
            }
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
        $str = (string) preg_replace('/[^a-z0-9]+/', $delimiter, $str);
        return trim($str, $delimiter);
    }

    public static function repeat(mixed $value, int $times): string
    {
        return str_repeat((string) $value, max(0, $times));
    }

    public static function reverse(mixed $value): string
    {
        return strrev((string) $value);
    }

    public static function shuffle(mixed $value): string
    {
        return str_shuffle((string) $value);
    }

    public static function rot13(mixed $value): string
    {
        return str_rot13((string) $value);
    }

    public static function lowerFirst(mixed $value): string
    {
        $str = (string) $value;
        if ($str === '') {
            return $str;
        }
        return mb_strtolower(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($str, 1, null, 'UTF-8');
    }

    public static function wordCount(mixed $value, int $format = 0, ?string $charList = null): int|array
    {
        return $charList === null
            ? str_word_count((string) $value, $format)
            : str_word_count((string) $value, $format, $charList);
    }

    public static function countOccurrences(
        mixed $value,
        mixed $needle,
        int   $offset = 0,
        int   $length = PHP_INT_MAX,
        bool  $caseSensitive = true,
    ): int {
        $str    = (string) $value;
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return substr_count(mb_strtolower($str, 'UTF-8'), mb_strtolower($search, 'UTF-8'), $offset, $length);
        }
        return substr_count($str, $search, $offset, $length);
    }

    public static function compare(mixed $value, mixed $other, bool $caseSensitive = true): int
    {
        return $caseSensitive
            ? strcmp((string) $value, (string) $other)
            : strcasecmp((string) $value, (string) $other);
    }

    public static function compareNatural(mixed $value, mixed $other, bool $caseSensitive = true): int
    {
        return $caseSensitive
            ? strnatcmp((string) $value, (string) $other)
            : strnatcasecmp((string) $value, (string) $other);
    }

    public static function compareLength(mixed $value, mixed $other, int $length, bool $caseSensitive = true): int
    {
        return $caseSensitive
            ? strncmp((string) $value, (string) $other, $length)
            : strncasecmp((string) $value, (string) $other, $length);
    }

    public static function compareSub(
        mixed $value,
        mixed $other,
        mixed $offset,
        mixed $length = null,
        bool  $caseSensitive = true,
    ): int {
        $str    = (string) $value;
        $other  = (string) $other;
        $offset = (int) $offset;
        $len    = $length !== null ? (int) $length : strlen($other);

        if ($caseSensitive === false) {
            $slice = mb_strtolower(mb_substr($str, $offset, $len, 'UTF-8'), 'UTF-8');
            $other = mb_strtolower(mb_substr($other, 0, $len, 'UTF-8'), 'UTF-8');
            return strcmp($slice, $other);
        }

        return substr_compare($str, $other, $offset, $len);
    }

    /**
     * Translates characters using parallel from/to strings (strtr).
     */
    public static function translate(mixed $value, string $from, string $to): string
    {
        return strtr((string) $value, $from, $to);
    }

    /**
     * Translates substrings using a search/replace map (strtr).
     */
    public static function translateMap(mixed $value, array|Arr $map): string
    {
        return strtr((string) $value, self::toPairArray($map));
    }

    public static function spanInclude(mixed $value, mixed $characters, int $offset = 0): int
    {
        return strspn((string) $value, (string) $characters, $offset);
    }

    public static function spanExclude(mixed $value, mixed $characters, int $offset = 0): int
    {
        return strcspn((string) $value, (string) $characters, $offset);
    }

    public static function chunkSplit(mixed $value, int $length = 76, string $separator = "\r\n"): string
    {
        return chunk_split((string) $value, $length, $separator);
    }

    public static function wordWrap(
        mixed  $value,
        int    $width = 75,
        string $break = "\n",
        bool   $cutLongWords = false,
    ): string {
        return wordwrap((string) $value, $width, $break, $cutLongWords);
    }

    public static function nl2Br(mixed $value, bool $useXhtml = true): string
    {
        return nl2br((string) $value, $useXhtml);
    }

    public static function format(mixed $value, mixed ...$args): string
    {
        return sprintf((string) $value, ...$args);
    }

    public static function scan(mixed $value, string $format, mixed &...$vars): int|array|null
    {
        return sscanf((string) $value, $format, ...$vars);
    }

    public static function match(mixed $value, string $pattern, array &$matches = [], int $flags = 0, int $offset = 0): int|false
    {
        return preg_match($pattern, (string) $value, $matches, $flags, $offset);
    }

    public static function matchAll(mixed $value, string $pattern, array &$matches = [], int $flags = PREG_PATTERN_ORDER, int $offset = 0): int|false
    {
        return preg_match_all($pattern, (string) $value, $matches, $flags, $offset);
    }

    public static function quoteMeta(mixed $value): string
    {
        return quotemeta((string) $value);
    }

    public static function addCslashes(mixed $value, mixed $charList): string
    {
        return addcslashes((string) $value, (string) $charList);
    }

    public static function stripCslashes(mixed $value): string
    {
        return stripcslashes((string) $value);
    }

    public static function encodeHtml(mixed $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, ?string $encoding = 'UTF-8'): string
    {
        return htmlspecialchars((string) $value, $flags, $encoding);
    }

    public static function decodeHtml(mixed $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE): string
    {
        return htmlspecialchars_decode((string) $value, $flags);
    }

    public static function encodeHtmlEntities(
        mixed   $value,
        int     $flags = ENT_QUOTES | ENT_SUBSTITUTE,
        ?string $encoding = 'UTF-8',
        bool    $doubleEncode = true,
    ): string {
        return htmlentities((string) $value, $flags, $encoding, $doubleEncode);
    }

    public static function decodeHtmlEntities(
        mixed   $value,
        int     $flags = ENT_QUOTES | ENT_SUBSTITUTE,
        ?string $encoding = 'UTF-8',
    ): string {
        return html_entity_decode((string) $value, $flags, $encoding);
    }

    public static function parseQuery(mixed $value, array &$result = null): int
    {
        parse_str((string) $value, $result);
        return count($result ?? []);
    }

    public static function fromCsv(
        mixed  $value,
        string $separator = ',',
        string $enclosure = '"',
        string $escape = '\\',
    ): Arr {
        return new Arr(str_getcsv((string) $value, $separator, $enclosure, $escape));
    }

    public static function toCsv(
        array|Arr $fields,
        string    $separator = ',',
        string    $enclosure = '"',
        string    $escape = '\\',
    ): string {
        $fields = $fields instanceof Arr ? $fields->toArray() : $fields;
        return implode($separator, array_map(
            static fn(mixed $field): string => self::escapeCsvField((string) $field, $separator, $enclosure, $escape),
            $fields,
        ));
    }

    public static function increment(mixed $value): string
    {
        return str_increment((string) $value);
    }

    public static function decrement(mixed $value): string
    {
        return str_decrement((string) $value);
    }

    public static function levenshtein(mixed $value, mixed $other): int
    {
        return levenshtein((string) $value, (string) $other);
    }

    public static function similarText(mixed $value, mixed $other, float &$percent = null): int
    {
        return similar_text((string) $value, (string) $other, $percent);
    }

    public static function soundex(mixed $value): string
    {
        return soundex((string) $value);
    }

    public static function metaphone(mixed $value, int $maxLength = 0): string
    {
        return metaphone((string) $value, $maxLength);
    }

    public static function crc32(mixed $value): int
    {
        return crc32((string) $value);
    }

    public static function md5(mixed $value, bool $rawOutput = false): string
    {
        return md5((string) $value, $rawOutput);
    }

    public static function sha1(mixed $value, bool $rawOutput = false): string
    {
        return sha1((string) $value, $rawOutput);
    }

    public static function subMultibyte(mixed $value, mixed $start, mixed $length = null, string $encoding = 'UTF-8'): string
    {
        $str = (string) $value;
        return $length !== null
            ? mb_substr($str, (int) $start, (int) $length, $encoding)
            : mb_substr($str, (int) $start, null, $encoding);
    }

    /**
     * Removes accented / diacritic characters with their plain ASCII equivalents.
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
            ? Strings\AccentsRemover::utf8($str)
            : Strings\AccentsRemover::latin($str);
    }

    /**
     * @return array<string|int, string|int>
     */
    private static function toPairArray(array|Arr $value): array
    {
        return $value instanceof Arr ? $value->toArray() : $value;
    }

    private static function escapeCsvField(string $field, string $separator, string $enclosure, string $escape): string
    {
        if (!str_contains($field, $separator) && !str_contains($field, $enclosure) && !str_contains($field, "\n")) {
            return $field;
        }

        return $enclosure . str_replace($enclosure, $escape . $enclosure, $field) . $enclosure;
    }
}
