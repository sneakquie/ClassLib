<?php
include './class_lib.php';


_class('Product', array(
    _protected_static('_data', array()),

    _public_static('set', function($key, $value) {
        $data = _self('_data');
        $data[$key] = $value;
        _self('_data', $data);
    }),

    _public_static('get', function($key) {
        $data = _self('_data');
        return isset($data[$key]) ? $data[$key] : null;
    }),

    _public_static('removeProduct', function($key) {
        $data = _self('_data');
        if(isset($data[$key]) || array_key_exists($key, $data)) {
            unset($data[$key]);
            _self('_data', $data);
        }
    }),
));

_call('Product::set', 'name', 'First product');
print_r(_call('Product::get', 'name'));

/*
 * Standart PHP example
 * @link http://habrahabr.ru/post/214285/
 */
class Product
{
    protected static $data = array();

    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    public static function get($key)
    {
        return isset(self::$data[$key]) ? self::$data[$key] : null;
    }

    final public static function removeProduct($key)
    {
        if (array_key_exists($key, self::$data)) {
            unset(self::$data[$key]);
        }
    }
}

Product::set('name', 'First product');

print_r(Product::get('name'));