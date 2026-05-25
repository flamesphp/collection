# flamesphp/collection

Typed, object-oriented collection and scalar helpers for PHP 8.5+.

Part of the [FlamesPHP](https://github.com/flamesphp) framework ecosystem — designed as a standalone, dependency-free library.

---

## Requirements

- PHP **8.5** or higher
- Extension `mbstring` (for multibyte string operations)

---

## Installation

### Via Composer

```bash
composer require flamesphp/collection
```

### Standalone (no Composer)

```php
require __DIR__ . '/vendor/flamesphp/collection/autoload.php';
```

### Inside FlamesPHP framework

When using the FlamesPHP framework the custom autoloader handles the `Flames\Collection\*` namespace automatically — no extra step needed.

---

## Classes

### `Flames\Collection\Arr`

Object-oriented wrapper around PHP's `ArrayObject` with a fluent API.

```php
use Flames\Collection\Arr;

$arr = new Arr([1, 2, 3]);

// Fluent helpers
$arr->add(4)
    ->add(5, canDuplicate: false);

// Virtual properties
echo $arr->length; // 4
echo $arr->first;  // 1
echo $arr->last;   // 5

// Functional helpers
$doubled  = $arr->map(fn($v) => $v * 2);
$evens    = $arr->filter(fn($v) => $v % 2 === 0);
$sorted   = $arr->sortByDelegate(fn($a, $b) => $a <=> $b);

// Conversion
$plain = $arr->toArray();
$json  = (string) $arr;           // JSON

// Factory
$fromObj = Arr::fromObject($stdClassInstance);
```

**Key methods**

| Method | Description |
|---|---|
| `add($value, $canDuplicate)` | Append value (optionally prevent duplicates) |
| `addKey($key, $value)` | Set a named key |
| `remove($value)` | Remove all elements equal to value |
| `removeKey($key)` | Unset by key |
| `contains($value)` | Strict existence check |
| `containsKey($key)` | Key existence check |
| `find($fn, $isKeyValue)` | Search by closure |
| `getKeys()` | All keys as Arr |
| `getFirst()` / `getLast()` | Boundary elements |
| `getLastNumberKey()` | Highest numeric key |
| `map($fn)` | Transform → new Arr |
| `filter($fn)` | Filter → new Arr |
| `each($fn)` | Iterate in place |
| `sort()` / `sortByKey()` | Value / key sort |
| `sortByDelegate($fn)` | Custom comparison |
| `merge($array, $replace)` | Merge → new Arr |
| `toArray()` | Recursive plain array |
| `clone()` | Deep clone |

---

### `Flames\Collection\Strings`

Stateless string utility class. All methods accept `mixed` and cast internally.

```php
use Flames\Collection\Strings;

Strings::toLower('HELLO');                        // 'hello'
Strings::startsWith('foobar', 'foo');             // true
Strings::contains('Hello World', 'world', false); // true  ← case-insensitive
Strings::split('a,b,,c', clearEmpty: true);       // Arr['a','b','c']
Strings::removeAccents('Héllo Wörld');            // 'Hello World'
Strings::getRandom(16);                           // 'aB3x...' (16 chars)
```

**Key methods**

| Method | Description |
|---|---|
| `parse($v)` | Cast to string |
| `length($v, $mb)` | Byte / char count |
| `toLower / toUpper` | Case conversion |
| `startsWith / endsWith / contains` | Prefix / suffix / substring checks |
| `containsAny / equalsAny` | Multi-needle checks |
| `equals($v, $needle, $cs)` | Equality (opt. case-insensitive) |
| `isEmpty($v)` | Empty-string check |
| `replace / remove` | String substitution |
| `encode / decode` | URL encoding (raw or form) |
| `split / splitLength / splitWords / splitLines` | Splitting → Arr |
| `sub($v, $start, $len)` | Substring |
| `indexOf / lastIndexOf` | Position lookup |
| `trim($v, $charList, $mb)` | Trimming |
| `addSlashes / removeSlashes` | Backslash escaping |
| `toBase64 / fromBase64` | Base64 |
| `getOnlyNumbers / getOnlyLetters / getOnlyLettersAndNumbers` | Character filtering |
| `limit($v, $n)` | Truncation |
| `getRandom($len)` | Secure random string |
| `removeAccents($v)` | Diacritic stripping |

---

### `Flames\Collection\StringObject`

Fluent, mutable wrapper around a single string. Mutating methods return `$this`.

```php
use Flames\Collection\StringObject;

$str = (new StringObject('  Hello, World!  '))
    ->trim()
    ->toLower()
    ->replace(',', '')
    ->remove('!');

echo $str;              // 'hello world'
echo $str->length();    // 11

$words = $str->splitWords(); // Arr['hello', 'world']
```

---

### `Flames\Collection\Ints`

```php
use Flames\Collection\Ints;

Ints::parse('42px');          // 42
Ints::clamp(150, 0, 100);     // 100
Ints::between(5, 1, 10);      // true
Ints::isEven(4);              // true
Ints::getRandom(1, 100);      // secure random int in [1, 100]
```

---

### `Flames\Collection\Floats`

```php
use Flames\Collection\Floats;

Floats::parse('3.14xyz');         // 3.14
Floats::clamp(1.5, 0.0, 1.0);    // 1.0
Floats::round(2.555, 2);         // 2.56
Floats::format(1234567.8, 2, '.', ','); // '1,234,567.80'
```

---

### `Flames\Collection\Bools`

```php
use Flames\Collection\Bools;

Bools::parse('yes');    // true
Bools::parse('false');  // false
Bools::parse('1');      // true
Bools::parse('-1');     // null  (ambiguous)
Bools::isTrue('yes');   // true
Bools::isFalse('no');   // true
```

---

## Bug fixes vs original implementation

The following bugs present in the original embedded code were fixed during extraction:

| File | Bug | Fix |
|---|---|---|
| `Strings.php` | `startsWith/endsWith/contains/equals` applied `toLower` on `$value` instead of `$needle` when case-insensitive | Now both operands are lowercased correctly |
| `Strings.php` | `containsAny/equalsAny` applied lowercasing when `$caseSensitive === true` (logic inverted) | Condition corrected |
| `StringObject.php` | `isEmpty()` had an unused `$value` parameter | Parameter removed |
| `StringObject.php` | `limit()` passed a non-existent `$returnString` argument to `Strings::limit()` | Argument removed |
| `Arr.php` | `getLastNumberKey()` returned 0 for arrays with only non-numeric keys | Now returns null correctly |
| `Arr.php` | `find()` with `$isKeyValue=true` constructed `Arr([...])` via global function which may not be available | Uses `new Arr([...])` |

---

## License

MIT
