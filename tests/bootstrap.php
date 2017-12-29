<?php
include __DIR__.DIRECTORY_SEPARATOR.'../src/Iptool.php';
include __DIR__.DIRECTORY_SEPARATOR.'../src/Converter.php';

/* fix for PHP 7 */
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}