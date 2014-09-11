<?php
include './class_lib.php';


_class('Factory', array(
    _protected_static('_products', array()),

    _public_static('pushProduct', function($product) {
        if(!_instanceof($product, 'Product')) {
            trigger_error('qwerty', E_USER_ERROR);
        }
        $data = _self('_products');
        $data[_call($product, 'getId')] = $product;
        _self('_products', $data);
    }),

    _public_static('getProduct', function($id) {
        $data = _self('_products');
        return isset($data[$id]) ? $data[$id] : null;
    }),

    _public_static('removeProduct', function($id) {
        $data = _self('_products');
        if(isset($data[$id]) || array_key_exists($id, $data)) {
            unset($data[$id]);
            _self('_products', $data);
        }
    }),
));

_class('Product', array(
    _protected('_id'),

    _public('__construct', function($id) {
        _this('_id', $id);
    }),

    _public('getId', function() {
        return _this('_id');
    }),
));

_call('Factory::pushProduct', _new('Product', 'first'));
_call('Factory::pushProduct', _new('Product', 'second'));

print_r(_call(_call('Factory::getProduct', 'first'), 'getId'));
print_r(_call(_call('Factory::getProduct', 'second'), 'getId'));

/*
 * Standart PHP example
 * @link http://habrahabr.ru/post/214285/
 */
class Factory
{
    protected static $products = array();

    public static function pushProduct(Product $product)
    {
        self::$products[$product->getId()] = $product;
    }

    public static function getProduct($id)
    {
        return isset(self::$products[$id]) ? self::$products[$id] : null;
    }

    public static function removeProduct($id)
    {
        if (array_key_exists($id, self::$products)) {
            unset(self::$products[$id]);
        }
    }
}

class Product
{
    protected $id;


    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

Factory::pushProduct(new Product('first'));
Factory::pushProduct(new Product('second'));

print_r(Factory::getProduct('first')->getId());
print_r(Factory::getProduct('second')->getId());