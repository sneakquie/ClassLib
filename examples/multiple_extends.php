<?php
include './class_lib.php';

_interface('MyInterface', array(
	_public('my_function'),
));
_interface('MySecondInterface', array(
	_public('my_second_function'),
));

_class('ParentClass', _implements('MyInterface'), array(
	_public('my_function', function() {
		return 'Hello, world';
	}),
));
_class('SecondParentClass', _implements('MyInterface', 'MySecondInterface'), array(
	_public('my_function', function() {
		return 'Hello from ' . _self();
	}),
	_public('my_second_function', function() {
		return 'World, hello!';
	}),
));

_class('Child', _extends('SecondParentClass', 'ParentClass'));

print_r(_call(_new('Child'), 'my_function'));
// output: Hello, world
print_r(_call(_new('Child'), 'my_second_function'));
// output: World, hello!