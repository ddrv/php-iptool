<?php
include __DIR__.DIRECTORY_SEPARATOR.'../src/Iptool.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Converter.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Wizard.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Wizard/CsvAbstract.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Wizard/Register.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Wizard/Network.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Wizard/TypeAbstract.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Wizard/Types/NumericType.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Wizard/Types/StringType.php';

/* fix for PHP 7 */

if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', str_replace('_','\\','\PHPUnit_Framework_TestCase'));
}
/**/