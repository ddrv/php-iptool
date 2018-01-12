<?php

const IPTOOL_TEST_CSV_DIR = __DIR__.DIRECTORY_SEPARATOR.'csv';
const IPTOOL_TEST_TMP_DIR = __DIR__.DIRECTORY_SEPARATOR.'tmp';
$srcDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src';
include $srcDir.DIRECTORY_SEPARATOR.'Iptool.php';
include $srcDir.DIRECTORY_SEPARATOR.'Wizard.php';
include $srcDir.DIRECTORY_SEPARATOR.'Wizard'.DIRECTORY_SEPARATOR.'CsvAbstract.php';
include $srcDir.DIRECTORY_SEPARATOR.'Wizard'.DIRECTORY_SEPARATOR.'Register.php';
include $srcDir.DIRECTORY_SEPARATOR.'Wizard'.DIRECTORY_SEPARATOR.'Network.php';
include $srcDir.DIRECTORY_SEPARATOR.'Wizard'.DIRECTORY_SEPARATOR.'FieldAbstract.php';
include $srcDir.DIRECTORY_SEPARATOR.'Wizard'.DIRECTORY_SEPARATOR.'Fields'.DIRECTORY_SEPARATOR.'NumericField.php';
include $srcDir.DIRECTORY_SEPARATOR.'Wizard'.DIRECTORY_SEPARATOR.'Fields'.DIRECTORY_SEPARATOR.'StringField.php';

/*
 * fix for using PHPUnit as composer package and PEAR extension
 */
$composerClassName = '\PHPUnit\Framework\TestCase';
$pearClassName = '\PHPUnit_Framework_TestCase';
if (!class_exists($composerClassName) && class_exists($pearClassName)) {
    class_alias($pearClassName, $composerClassName);
}