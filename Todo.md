# Kohana PHP 8 — Todo

## 🔴 Krytyczne
- [x] `mysql_*` functions — `Kohana_Database_MySQL_Result` przepisany na rzucanie wyjątku (driver i tak jest dead)

## 🟡 Wysoki priorytet
- [ ] Dodać brakujące typy zwracane w metodach magicznych:
  - `View::__set()` → `: void`
  - `View::__isset()` → `: bool`
  - `View::__unset()` → `: void`
  - `Log_Syslog::__destruct()` → `: void`
  - `ORM::__isset()` → `: bool`
  - `ORM::__unset()` → `: void`
  - `ORM::__serialize()` → `: array`
  - `Database::__destruct()` → `: void`
  - `Database_Result::__destruct()` (abstract) → `: void`
  - `Image_GD::__destruct()` → `: void`
  - `Image_Imagick::__destruct()` → `: void`
- [ ] `Log_Writer::write()` abstract → dodać `: void`

## 🟠 Średni priorytet
- [x] `composer.json` — dodać `"php": ">=8.2"` w root; zaktualizować `system/composer.json`
- [ ] Podnieść poziom static analysis (phpstan level 2+, psalm level 4+)
- [ ] Usunąć martwy kod:
  - `Core.php:50` — `$magic_quotes` property
  - `.travis.yml`, `system/.travis.yml` — stare CI (PHP 5.3–7.0, HHVM)

## 🔵 Niski priorytet
- [ ] Zastąpić `extract()` w `View.php`, `Date.php`, `Exception.php`
- [ ] Zabezpieczyć dostęp do tablic (undefined array key w `Exception.php`, `Header.php`)
- [ ] Zaktualizować `rector.php` → PHP 8.3
