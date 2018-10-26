# CHANGELOG

## 3.0.0

- Final release. No changes after first beta.

## 3.0.0-beta1

- Removed `aura/installer-default` from composer.json which was used by aura framework version 1 to install in package folder.
- Fixes [issue 17](https://github.com/auraphp/Aura.Intl/issues/17) by removing Aura.Di wiring trait tests.
- Changed directory structure from PSR-0 to PSR-4.
- Supported PHP version : 5.6+.
- There is no other BC breaks from 1.x version


## 1.1.1

Hygiene release: Composer update.

## 1.1.0

- Merge pull request #11 from lorenzo/feature/format-key. If Translator::translate() cannot translate, it now returns the incoming translation key as the translated message. This makes it easier to use the key as a human readable string, which can contain formatting placeholders. With this feature the developer will be able to see immediate feedback of their translation messages without having to translate to a specific locale beforehand.

## 1.0.1

Hygiene release.

- Merge pull request #10 from harikt/fixdoc; fixes #9, add a test for failure. The expected exception need to be fixed.

- Merge pull request #8 from harikt/v2config, adding v2 config files.

- Merge pull request #7 from harikt/issue6, add a test to show it works. Thank you @samdark.

- Point travis status badge to develop branch

## 1.0.0

- [CHG] When interpolating array values into strings, now converts the array
  to comma-separated values instead of printing 'Array'.

- [DOC] PHP 5.5 appears to be able to format strings with missing token
  replacements; updated tests to account for this.

## 1.0.0-beta2

- [FIX] Multiple typo fixes; thanks, @pborreli.

- [CHG] Use {foo} instead of {:foo} in line with PSR-3 placeholders.

- [NEW] Add a PackageFactory

- [NEW] Add more-detailed exception classes

- [ADD] In config, add 'intl_package_factory' as a service

- [CHG] Throw IcuVersionTooLow Exception if IntlFormatter is instantiated with
  ICU Version lower than 4.8; Skip all IntlFormatter tests if the intl
  extension is not loaded. Thanks, @mapthegod.

- [CHG] In the FormatterLocator, registry entries *must* be wrapped in a
  callable from now on.


## 1.0.0-beta1

Initial release.
