<?php
include './class_lib.php';


_class('Product', array(
    _private_static('_instance'),

    _public('a'),

    _public_static('getInstance', function() {
        if(!_instanceof(_self('_instance'), _self())) {
            _self('_instance', _new(_self()));
        }
        return _self('_instance');
    }),

    _private('__construct', function() {}),
    _private('__clone', function() {}),
));

$firstProduct  = _call('Product::getInstance()');
$secondProduct = _call('Product::getInstance()');

_prop($firstProduct, 'a', 1);
_prop($secondProduct, 'a', 2);

print_r(_prop($firstProduct, 'a'));
print_r(_prop($secondProduct, 'a'));

/*
 * Standart PHP example
 * @link http://habrahabr.ru/post/214285/
 */
final class Product
{
    private static $instance;

    public $a;

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }
}

$firstProduct = Product::getInstance();
$secondProduct = Product::getInstance();

$firstProduct->a = 1;
$secondProduct->a = 2;

print_r($firstProduct->a);
print_r($secondProduct->a);