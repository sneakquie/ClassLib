<?php
include './class_lib.php';

_class('Pizza', array(
    _private('_pastry', ''),
    _private('_sauce', ''),
    _private('_garniture', ''),
    _public('getMyPizza', function() {
        return _this('_pastry') . ' ' . _this('_sauce') . ' ' . _this('_garniture') . ' ' . _self();
    }),
    _public('setPastry', function($pastry) {
        _this('_pastry', $pastry);
    }),
    _public('setSauce', function($sauce) {
        _this('_sauce', $sauce);
    }),
    _public('setGarniture', function($garniture) {
        _this('_garniture', $garniture);
    }),
));

_class('BuilderPizza', array(
    _protected('_pizza'),
    _public('getPizza', function() {
        return _this('_pizza');
    }),
    _public('createNewPizza', function() {
        _this('_pizza', _new('Pizza'));
    }),
    _public('buildPastry', function() {}),
    _public('buildSauce', function() {}),
    _public('buildGarniture', function() {}),
));

_class('BuilderPizzaHawaii', _extends('BuilderPizza'), array(
    _public('buildPastry', function() {
        _call(_this('_pizza'), 'setPastry', 'normal');
    }),
    _public('buildSauce', function() {
        _call(_this('_pizza'), 'setSauce', 'soft');
    }),
    _public('buildGarniture', function() {
        _call(_this('_pizza'), 'setGarniture', 'jambon+ananas');
    }),
));

_class('BuilderPizzaSpicy', _extends('BuilderPizza'), array(
    _public('buildPastry', function() {
        _call(_this('_pizza'), 'setPastry', 'puff');
    }),
    _public('buildSauce', function() {
        _call(_this('_pizza'), 'setSauce', 'hot');
    }),
    _public('buildGarniture', function() {
        _call(_this('_pizza'), 'setGarniture', 'pepperoni+salami');
    }),
));

_class('PizzaBuilder', array(
    _private('_builderPizza'),
    _public('setBuilderPizza', function($mp) {
        _this('_builderPizza', $mp);
    }),
    _public('getPizza', function() {
        return _call(_this('_builderPizza'), 'getPizza');
    }),
    _public('constructPizza', function() {
        _call(_this('_builderPizza'), 'createNewPizza');
        _call(_this('_builderPizza'), 'buildPastry');
        _call(_this('_builderPizza'), 'buildSauce');
        _call(_this('_builderPizza'), 'buildGarniture');
    }),
));

$pizzaBuilder = _new('PizzaBuilder');

$builderPizzaHawaii  = _new('BuilderPizzaHawaii');
$builderPizzaPiquante = _new('BuilderPizzaSpicy');

_call($pizzaBuilder, 'setBuilderPizza', $builderPizzaHawaii);
_call($pizzaBuilder, 'constructPizza');
$pizza = _call($pizzaBuilder, 'getPizza');
ClassLib\var_dump($pizza);