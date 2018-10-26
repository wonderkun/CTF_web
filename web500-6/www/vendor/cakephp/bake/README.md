# Bake plugin for CakePHP

[![Build Status](https://img.shields.io/travis/cakephp/bake/master.svg?style=flat-square)](https://travis-ci.org/cakephp/bake)
[![Coverage Status](https://img.shields.io/codecov/c/github/cakephp/bake.svg?style=flat-square)](https://codecov.io/github/cakephp/bake)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)

This project provides the code generation functionality for CakePHP. Through a
suite of CLI tools, you can quickly and easily generate code for your application.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require --dev cakephp/bake
```

## Documentation

You can find the documentation for bake [on the cookbook](http://book.cakephp.org/3.0/en/bake.html).

## Testing

After installing dependencies with composer you can run tests with `phpunit`:

```bash
vendor/bin/phpunit
```

If your changes require changing the templates that bake uses, you can save time updating tests, by
enabling bake's 'overwrite fixture feature'. This will let you re-generate the expected output files
without having to manually edit each one:

```bash
UPDATE_TEST_COMPARISON_FILES=1 vendor/bin/phpunit
```
