<?php
include './class_lib.php';

_class('MyClass', array(
	_private('my_prop', 1),
	_protected('storage', array()),
	_public('__construct', function() {
		var_dump(_this('my_prop', 1));
	}),
	_public('__destruct', function() {
		var_dump(_this('my_prop', 0));
	}),
	_private('my_function', function() {
		return 'Hello from private';
	}),
	_private_static('my_static_function', function() {
		return 'Hello from static private';
	}),
	_public('__call', function($method, $arguments) {
		var_dump($method);
		var_dump($arguments);
	}),
	_public_static('__callStatic', function($method, $arguments) {
		var_dump($method);
		var_dump($arguments);
	}),
	_public('__set', function($key, $value) {
		$data = _this('storage');
		$data[(string) $key] = $value;
		_this('storage', $data);
		return $value;
	}),
	_public('__get', function($key) {
		$data = _this('storage');
		return isset($data[$key]) ? $data[$key] : null;
	}),
	_public('__clone', function() {
		_this('storage', array());
	}),
));

_call('MyClass::my_static_function()', 'Not "Hello from static private"');
// output: string(18) "my_static_function" array(1) { [0]=> string(31) "Not "Hello from static private"" }
$obj = _new('MyClass');
// output: int(1)
_call($obj, 'my_function()', 'Not "Hello from private"');
// output: string(13) "my_function()" array(1) { [0]=> string(24) "Not "Hello from private"" }
var_dump(_prop($obj, 'my_prop'));
// output: NULL
var_dump(_prop($obj, 'my_prop', 100));
// output: 100
var_dump(_prop($obj, 'my_prop'));
// output: 100
$clone = _clone($obj);
var_dump(_prop($clone, 'my_prop'));
// output: NULL
// Execute destructors
// output: int(0)
// output: int(0)