# flamesphp/collection — AI Developer Instructions

Reference for AI-assisted coding (vibecode) against the `flamesphp/collection` package.

- **Namespace:** `Flames\Collection`
- **PHP:** `>= 8.5`
- **Extensions:** `json`, `mbstring`

---

## Core conventions

### Prefer `Arr` over native `array`

When a method can return a collection wrapper, use **`toArr()`** for in-framework code and **`toArray()`** only when you need a plain PHP array (JSON export, third-party APIs, spread into `array_*` outside this library).

```php
$vec->toArr();   // Arr { x, y }
$vec->toArray(); // ['x' => 1.0, 'y' => 2.0]
```

`Arrays::toArr(array $value): Arr` wraps any native array.

### Two class patterns

| Pattern | Examples | Usage |
|--------|----------|-------|
| **Static utilities** | `Ints`, `Strings`, `Bytes` | `Ints::clamp($n, 0, 100)` — stateless, first arg is the value |
| **Object wrappers** | `Arr`, `Vector2`, `Uuid` | `$arr->map(fn ($v) => …)` — holds state, fluent chain |

All static utilities accept **`mixed`** on input where noted and coerce internally via `parse()` or cast.

### `parse(mixed)` factories

Most utilities expose `parse()` to normalize input:

```php
Ints::parse($request['count']);     // int
Strings::parse($value);             // string
Uuid::parse('550e8400-e29b-…');     // Uuid instance
Vector2::parse([10, 20]);           // Vector2
Vector2::parse(10, 20);             // Vector2
```

### Mutable vs immutable

| Mutable | Immutable | Convert |
|---------|-----------|---------|
| `Arr` | `ArrImmutable` | `$arr->toImmutable()` / `$immutable->toMutable()` |
| `Vector2` | `Vector2Immutable` | `$v->toImmutable()` / `$v->toMutable()` |
| `Vector3` | `Vector3Immutable` | same |

Immutable types throw on in-place mutation. Immutable vectors use **`withX()` / `withY()` / `withZ()`** instead of mutating properties.

### Static vs instance method names

PHP forbids a static and instance method with the **same name** on one class. Vector classes use:

- **Static:** `Vector2::distance($a, $b)`, `Vector2::dot($a, $b)`, `Vector2::lerp($a, $b, $t)`
- **Instance:** `$a->distanceTo($b)`, `$a->dotWith($b)`, `$a->lerpTo($b, $t)`

### Prototype system (subset / vibecode)

Classes using `Prototype` trait can register custom methods for the **flamesphp/subset** syntax (`$value->customMethod()`).

- **STATIC** prototypes: `Strings::toLower($str)` — no `$this`
- **DYNAMIC** prototypes: `$arr->custom()` — host instance injected as first arg (wrapped in `Delegate`)

Registration:

```php
Strings::prototype('slug', fn (Delegate $s) => Strings::toSlug($s()), Type::DYNAMIC);
```

**STATIC by default on:** `Arrays`, `Bits`, `Bools`, `Bytes`, `Chars`, `Enums`, `Floats`, `Functions`, `Ints`, `Objects`, `Strings`.

**DYNAMIC by default on:** `Arr`, `Vector2`, `Uuid`, etc.

Internal types: `Flames\Collection\Prototype\Delegate`, `Flames\Collection\Prototype\Store`, `Flames\Collection\Enum\Prototype\Type`.

---

## Static scalar utilities

### `Ints`

Integer math and inspection. All methods take `int` (after `parse`).

| Method | Description |
|--------|-------------|
| `parse(mixed): int` | Cast to int |
| `clamp(int, min, max): int` | Bound value inclusively |
| `between(int, min, max): bool` | In inclusive range |
| `isEven`, `isOdd`, `isZero`, `isPositive`, `isNegative` | Predicates |
| `add`, `subtract`, `multiply`, `divide`, `mod`, `power` | Arithmetic |
| `abs`, `sign`, `increment`, `decrement` | Unary / step |
| `min`, `max` | Variadic min/max |
| `getRandom(min, max): int` | `random_int` wrapper |
| `toBinary`, `toHex`, `toOctal` | String representations |
| `fromBinary`, `fromHex`, `fromOctal` | Parse from string |
| `gcd`, `lcm` | Greatest common / least common multiple |

### `Floats`

Same shape as `Ints` for floats.

| Method | Description |
|--------|-------------|
| `parse(mixed): float` | Cast to float |
| `clamp`, `between`, `isZero`, `isPositive`, `isNegative` | Bounds / predicates |
| `add`, `subtract`, `multiply`, `divide`, `mod`, `power` | Arithmetic (`divide` throws on 0) |
| `abs`, `round`, `floor`, `ceil`, `format` | Rounding / display |
| `sqrt`, `log`, `exp` | Transcendentals |
| `min`, `max`, `sign` | Aggregates |
| `isFinite`, `isNan`, `isInfinite` | IEEE checks |
| `toInt(float): int` | Truncate to int |

### `Bools`

| Method | Description |
|--------|-------------|
| `parse(mixed): bool\|null` | Coerce; `true`/`yes`/`1`, `false`/`no`/`0`; unknown → `null` |
| `isTrue`, `isFalse`, `isNull` | Parse and compare |
| `negate(bool\|null): bool\|null` | Logical not |
| `and`, `or`, `xor` | Boolean logic |
| `toInt`, `fromInt`, `toString` | Conversions |

### `Strings`

Full string toolkit. **`$caseSensitive = true`** on compare/search/replace unless noted. Multibyte methods assume **UTF-8**.

**Basics:** `parse`, `length`, `count`, `toLower`, `toUpper`, `capitalize`, `capitalizeFirst`, `lowerFirst`, `isEmpty`, `repeat`, `reverse`, `shuffle`, `rot13`

**Search:** `startsWith`, `endsWith`, `contains`, `containsAny`, `equals`, `equalsAny`, `indexOf`, `lastIndexOf`, `compare`, `compareNatural`, `compareLength`, `compareSub`

**Edit:** `replace` (scalar search), `replaceAny` (array of needles), `replaceRegex`, `replaceSub`, `remove`, `removeAny`, `append`, `prepend`, `pad`, `sub`, `subMultibyte`, `truncate`, `limit`, `translate`, `translateMap`

**Trim / strip:** `trim`, `trimLeft`, `trimRight`, `removeSpaces`, `removeSpecialCharacters`, `removeNumbers`, `stripHtmlTags`, `removeAccents` (uses internal `AccentsRemover`)

**Split → `Arr`:** `split`, `splitLength`, `splitWords`, `splitLines`

**Encoding:** `encode`/`decode` (URL), `toBase64`/`fromBase64`, `toHex`/`fromHex`, `encodeHtml`/`decodeHtml`, `encodeHtmlEntities`/`decodeHtmlEntities`, `addSlashes`, `removeSlashes`, `addCslashes`, `stripCslashes`

**Regex:** `match`, `matchAll`, `quoteMeta`

**Filter chars:** `getOnlyNumbers`, `getOnlyLetters`, `getOnlyLettersAndNumbers`

**Format / data:** `format` (`sprintf`), `scan`, `chunkSplit`, `wordWrap`, `nl2Br`, `parseQuery`, `fromCsv`, `toCsv`, `wordCount`, `countOccurrences`

**Hash / fuzzy:** `crc32`, `md5`, `sha1`, `levenshtein`, `similarText`, `soundex`, `metaphone`

**Slug / random:** `toSlug`, `getRandom`

**Span:** `spanInclude`, `spanExclude`

**Increment strings:** `increment`, `decrement` (PHP string increment semantics)

### `Chars`

Single **UTF-8 code point** (string length 1). Do not use for full strings — use `Strings`.

| Method | Description |
|--------|-------------|
| `parse(mixed): string` | One char; int → code point |
| `fromCodePoint(int): string` | Unicode code point → char |
| `toCodePoint(mixed): int` | Char → code point |
| `isSingle(mixed): bool` | Valid single code point |
| `equals`, `equalsIgnoreCase` | Compare |
| `toUpper`, `toLower`, `isUpper`, `isLower` | Case |
| `isAlpha`, `isAlphanumeric`, `isDigit`, `isHexDigit` | Unicode categories |
| `isWhitespace`, `isPrintable`, `isControl`, `isPunctuation`, `isAscii` | Classification |
| `toInt(mixed): int` | Decimal digit char → int |

### `Bits`

Bitwise ops on **integers**. Bit positions are **0-based from LSB**.

| Method | Description |
|--------|-------------|
| `parse(mixed): int` | Cast to int |
| `and`, `or`, `xor`, `not` | Bitwise logic |
| `single(position)`, `mask(width)` | Build masks |
| `set`, `clear`, `toggle`, `test` | Single-bit ops |
| `setMask`, `clearMask`, `toggleMask`, `hasMask`, `hasAny` | Multi-bit masks |
| `shiftLeft`, `shiftRight`, `rotateLeft`, `rotateRight` | Shifts / rotates |
| `count`, `length`, `lowest`, `highest` | Population / bit index |
| `extract`, `insert`, `truncate` | Bit field slice/merge |
| `toBinary`, `fromBinary`, `toHex`, `fromHex` | Representations |

### `Bytes`

**Binary strings** (byte buffers), not bitwise ints. Uses timing-safe `hash_equals` in `equals`.

| Method | Description |
|--------|-------------|
| `parse(mixed): string` | Cast to string |
| `length`, `isEmpty`, `equals`, `compare` | Inspection |
| `concat`, `slice`, `repeat`, `padLeft`, `padRight` | Composition |
| `at(index): int` | Byte value 0–255 at index |
| `fromByte(int): string`, `fromArray(list<int>): string` | Build buffer |
| `toArray`, `toArr` | Bytes as int list / `Arr` |
| `toHex`, `fromHex`, `toBase64`, `fromBase64`, `toBase64Url`, `fromBase64Url` | Encodings |
| `pack(format, values)`, `unpack(format, value, offset)` | PHP `pack`/`unpack` |
| `startsWith`, `endsWith`, `indexOf` | Binary-safe search |

### `Enums`

PHP 8.1+ enum helpers.

| Method | Description |
|--------|-------------|
| `isEnum`, `isBackedEnum` | Instance checks |
| `isEnumClass`, `isBackedEnumClass` | Class checks |
| `tryFrom(class, value): UnitEnum\|null` | Backed enum lookup |
| `from(class, value): UnitEnum` | Throws if invalid |
| `tryParse(class, value): UnitEnum\|null` | Enum instance, name, or backed value |
| `parse(class, value): UnitEnum` | Throws if invalid |
| `cases(class): list` | All cases |
| `names(class): list<string>` | Case names |
| `values(class): list` | Backed values only |
| `name(UnitEnum): string`, `value(BackedEnum)` | Accessors |
| `equals(UnitEnum, UnitEnum): bool` | Identity `===` |

### `Objects`

Runtime object introspection (not enum/string utilities).

| Method | Description |
|--------|-------------|
| `isObject`, `isNull`, `isScalar`, `isArray`, `isCallable`, `isResource` | Type guards |
| `getType(mixed): string` | `get_debug_type` |
| `className`, `shortName`, `namespace` | Class metadata |
| `instanceOf`, `isInstanceOf`, `isA` | Inheritance checks |
| `hasProperty`, `getProperty`, `setProperty` | Dynamic property access |
| `hasMethod`, `isMethodCallable` | Method introspection |
| `clone`, `cloneDeep` | Shallow / serialize clone |
| `equals` (`==`), `identical` (`===`) | Comparison |
| `hash`, `id` | `spl_object_hash`, `spl_object_id` |

### `Functions`

Callable helpers.

| Method | Description |
|--------|-------------|
| `isCallable(mixed): bool` | `is_callable` |
| `call(callable, ...args): mixed` | Invoke |
| `identity`, `noop` | Trivial callables |
| `once(callable): mixed` | Execute once per closure identity; cached via `Flames\Ready\ResetData` |
| `compose(...callables): callable` | Right-to-left pipeline: `f(g(x))` |
| `pipe(...callables): callable` | Alias of `compose` |
| `bind(Closure, newThis, newScope?): Closure` | `Closure::bindTo` |

### `Arrays`

| Method | Description |
|--------|-------------|
| `toArr(array): Arr` | Wrap native array |

---

## `Arr` — array wrapper

Extends `ArrayObject`. Implements fluent collection API. Uses **`Prototype`** (DYNAMIC).

### Read-only virtual properties

`$arr->length`, `$arr->count`, `$arr->first`, `$arr->last` (via `offsetGet`).

### Memory model

- `foreach ($arr as …)` — no copy
- Methods that call PHP `array_*` take **one shallow snapshot** per operation
- `toArray()` — **deep** export (nested `Arr` → array) for JSON/interop

### Factories

| Method | Description |
|--------|-------------|
| `__construct(array\|Arr\|null)` | From array or empty |
| `fromJson(string): self` | Decode JSON array |
| `fromObject(object): self` | Public properties → array |
| `range(start, end, step): self` | `range()` |
| `fill(count, value): self` | `array_fill` |
| `fillKeys(keys, value): self` | `array_fill_keys` |
| `multisortColumns(columns): self` | Sort by multiple columns |

### Inspection

`length()`, `isEmpty()`, `contains(value)`, `containsKey(key)`, `indexOf`, `lastIndexOf`, `find(delegate, isKeyValue?)`, `getKeys()`, `getLastNumberKey()`, `getFirst()`, `getLast()`, `isList()`, `isAssociative()`, `get(key, default)`, `getValues()`

### Mutation (return `self` for chaining)

`add`, `addKey`, `remove`, `removeKey`, `clear`, `limit`, `push`, `pop`, `shift`, `unshift`, `splice`, `merge`, `concat`, `replace`

### Transform

`map`, `filter`, `each`, `eachRecursive`, `reduce`, `every`, `some`, `flatten`, `groupBy`, `keyBy`, `column`/`pluck`, `combine`, `countValues`, `chunk`, `pad`, `flip`, `unique`, `slice`, `reverse`, `shuffle`, `only`, `except`, `changeKeyCase`

### Sort

`sort`, `sortDesc`, `sortIndexed`, `sortIndexedDesc`, `sortByKey`, `sortByKeyDesc`, `sortByDelegate`, `sortByKeyDelegate`, `sortNatural`, `multisort`, plus `ArrayObject` sorts: `asort`, `ksort`, `uasort`, `uksort`, `natsort`, `natcasesort`

### Set operations

`diff`, `diffAssoc`, `diffKey`, `intersect`, `intersectAssoc`, `intersectKey`, plus `*ByDelegate` and `*ByKeyDelegate` variants for custom comparators

### Aggregate

`join(delimiter)`, `sum()`, `product()`, `random(num?)`, `randomValue()`

### Export

`toArray(): array` (deep), `toJson(): string`, `toImmutable(): ArrImmutable`, `clone(): self`, `__toString(): string` (JSON)

### `ArrayObject` overrides

`offsetGet/Set/Unset`, `exchangeArray`, `append` — integrated with auto-increment key tracking.

---

## `ArrImmutable`

Read-only `Arr`. All mutating methods call `guardMutable()` → `LogicException`.

| Method | Description |
|--------|-------------|
| `fromJson(string): self` | Parse JSON to immutable tree |
| `toMutable(): Arr` | Deep copy to mutable `Arr` |

Nested arrays are stored as nested `ArrImmutable` at construction.

---

## Value objects

### `Vector2` / `Vector2Immutable`

2D vector. Mutable: public `$x`, `$y` (in-place ops). Immutable: `readonly`, `withX`/`withY`.

**Factories:** `zero()`, `one()`, `unitX()`, `unitY()`, `parse(mixed, y?)` — accepts `[x,y]`, `{x,y}`, object, or scalars.

**Static math:** `distance`, `distanceSquared`, `dot`, `lerp`, `min`, `max`, `clamp`

**Instance (mutable mutates `$this`):** `length`, `lengthSquared`, `normalize`, `add`, `subtract`, `multiply`, `divide`, `negate`, `reflect`, `distanceTo`, `distanceSquaredTo`, `dotWith`, `lerpTo`, `equals`, `isZero`

**Convert:** `toImmutable`/`toMutable`, `toVector3(z)`, `toArray`, `toArr`

### `Vector3` / `Vector3Immutable`

Same as Vector2 plus **`$z`**, `unitZ()`, `cross` / `crossWith`, `withZ`, `toVector2`.

### `Uuid`

RFC 4122 UUID value object (`readonly`).

| Method | Description |
|--------|-------------|
| `parse(mixed): self` | Validate and normalize lowercase |
| `v4(): self` | Random UUID v4 |
| `toString()`, `__toString()` | Canonical string |
| `equals(self): bool` | String equality |

### `Range`

Bounded interval with inclusive/exclusive flags. Default: lower inclusive, upper exclusive.

| Method | Description |
|--------|-------------|
| `parse(mixed): self` | Array, JSON string, or instance |
| `fromArray(array): self` | `{lower, upper, lowerInclusive?, upperInclusive?}` or `[lower, upper]` |
| `toArray()`, `toJson()`, `__toString()` | Serialize |
| `equals(self): bool` | Loose array compare |

---

## Internal / support (do not use in app code)

| Class | Role |
|-------|------|
| `Flames\Collection\Strings\AccentsRemover` | UTF-8 / Latin accent stripping for `Strings::removeAccents` |
| `Flames\Collection\Prototype\Delegate` | Reflection proxy for prototype handlers |
| `Flames\Collection\Prototype\Store` | Per-class prototype handler registry |
| `Flames\Collection\Enum\Prototype\Type` | `STATIC` / `DYNAMIC` enum for prototypes |
| `Flames\Collection\Trait\Prototype` | `__call` / `__callStatic` dispatch |

---

## Quick decision guide (for AI)

| Need | Use |
|------|-----|
| List / map / filter | `Arr` or `Arrays::toArr()` |
| Read-only config tree | `ArrImmutable` |
| Text | `Strings` |
| Single character | `Chars` |
| Integer math | `Ints` |
| Float math | `Floats` |
| Boolean coercion | `Bools` |
| Bit flags | `Bits` |
| Raw bytes / hex / base64 | `Bytes` |
| PHP enum | `Enums` |
| Object reflection | `Objects` |
| Closure pipeline / once | `Functions` |
| 2D/3D position/direction | `Vector2` / `Vector3` |
| Database UUID | `Uuid` |
| Numeric interval | `Range` |

---

## Related packages (not in this repo)

- **`flamesphp/subset`** — enables `$value->method()` syntax via prototypes
- **`flamesphp/geometry`** — geospatial types (`Geometries`, `Point`, `Polygon`, …)
- **`flamesphp/date`** — date/time types

When generating code, **import from `Flames\Collection`** for this package. Do not duplicate utility logic that already exists on these classes.
