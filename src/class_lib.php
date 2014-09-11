<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL | E_STRICT);

// Check PHP version
if(version_compare(PHP_VERSION, '5.4', '<')) {
    trigger_error("PHP Class Library needs PHP version 5.4 or higher", E_USER_ERROR);
}

/**
 * Return random string
 * @param integer $length
 * @link http://stackoverflow.com/questions/4356289/php-random-string-generator/4356295#4356295
 * @return string
 */
function random_string($length = 5)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/*
 * Allow to overwrite methods and properties by another parent
 */
define('MULTIPLE_OVERWRITE', true);

/*
 * Object name length
 */
define('OBJECT_LENGTH', 8);

/*
 * Prefix for all object names
 */
define('OBJECT_PREFIX', '_u');

/*
 * Access modifier constants
 */
define('ACCESS_MODIFIER_PUBLIC', serialize(array(0, 'public')));
define('ACCESS_MODIFIER_PROTECTED', serialize(array(1, 'protected')));
define('ACCESS_MODIFIER_PRIVATE', serialize(array(2, 'private')));

/**
 * Return public modifier
 * @return array
 */
function get_public()
{
    static $_value;
    // Isn't already set
    if(null === $_value) {
        $_value = unserialize(ACCESS_MODIFIER_PUBLIC);
    }
    return $_value;
}

/**
 * Return protected modifier
 * @return array
 */
function get_protected()
{
    static $_value;
    // Isn't already set
    if(null === $_value) {
        $_value = unserialize(ACCESS_MODIFIER_PROTECTED);
    }
    return $_value;
}

/**
 * Return private modifier
 * @return array
 */
function get_private()
{
    static $_value;
    // Isn't already set
    if(null === $_value) {
        $_value = unserialize(ACCESS_MODIFIER_PRIVATE);
    }
    return $_value;
}

/**
 * Check permissions. eg. function can only be called from some another function
 * @param array $debug_backtrace
 * @param array $perms
 * @return boolean
 */
function check_permission(Array $debug_backtrace, Array $perms)
{
    // Foreach access level check permissons
    foreach($perms as $key => $value) {
        // There is no access level in backtrace or smth else
        if( !isset($debug_backtrace[$key])
         || is_array($value)  && !in_array(strtolower($debug_backtrace[$key]['function']), $value)
         || is_string($value) && strcasecmp($value, $debug_backtrace[$key]['function'])
        ) {
            return false;
        }
    }
    return true;
}

/**
 * Return array of extend classes (strings)
 * @param string
 * @return null|array
 */
function _extends()
{
    return (func_num_args() > 0)
            ? array('extends' => func_get_args())
            : null;
}

/**
 * Return array of implement interfaces (strings)
 * @param string
 * @return null|array
 */
function _implements()
{
    return (func_num_args() > 0)
            ? array('implements' => func_get_args())
            : null;
}

/**
 * Not the best one realisation of stack
 * Push - set param to array of values
 * Pop  - set param to boolean
 * Get last (without pop) - no params
 * @param void|null|array
 * @return void|array
 */
function stack($key = null)
{
    static $_stack = array();
    // Functions, which can call stack()
    $taxpayers = array(
        '_self',
        '_this',
        '_call',
        '_prop',
    );
    // Check permissions
    if(!check_permission(debug_backtrace(3, 2), array(1 => $taxpayers))) {
        trigger_error("Access denied: cannot call stack in this context", E_USER_ERROR);
    } elseif(null === $key) {
        // Return last element
        return (sizeof($_stack) > 0) ? end($_stack) : array();
    } elseif(is_bool($key)) {
        // Delete last element
        sizeof($_stack) > 0 && array_pop($_stack);
    } elseif(is_array($key)) {
        // Push element
        $_stack[] = $key;
    }
}

/**
 * Static _storage contains all class and interfaces defines, objects
 * @param array $value (empty array if want to get storage)
 * @return array
 */
function storage(Array $value = array())
{
    static $_storage = null;
    // Functions, which can call storage()
    $taxpayers = array(
        '_new',
        '_self',
        '_this',
        '_call',
        '_prop',
        '_class',
        '_clone',
        '_parent',
        '_interface',
        '_instanceof',
        'classlib\var_dump',
        'classlib\shutdown_function',
    );
    // Check permissions
    if(!check_permission(debug_backtrace(3, 2), array(1 => $taxpayers))) {
        trigger_error("Access denied: cannot call storage in this context", E_USER_ERROR);
    } elseif(null === $_storage) {
        // Isn't initialised
        $_storage = array(
            'classes'    => array(),
            'objects'    => array(),
            'interfaces' => array(),
        );
    }
    if(sizeof($value) > 0) {
        $_storage = $value;
    }
    return $_storage;
}

/**
 * Return property or method with public access modifier
 * @param string $key
 * @param mixed $value Value of property (NULL by default). If typeof callback, then defines as method
 * @param boolean $is_static Is class element static?
 * @return array
 */
function _public($key, $value = null, $is_static = false)
{
    // Set values
    $result = array(
        'name'   => trim((string) $key),
        'value'  => $value,
        'access' => get_public(),
        'method' => false,
        'static' => (boolean) $is_static,
    );
    // Name isn't valid
    if(empty($result['name'])) {
        trigger_error("Property and method name cannot be empty", E_USER_ERROR);
    } elseif(is_callable($value)) {
        // Is method
        $result['method'] = true;
    }
    return $result;
}

/**
 * Prototype of _public() with defined is_static as true
 * @param string $key
 * @param mixed $value
 * @return array
 */
function _public_static($key, $value = null)
{
    return _public($key, $value, true);
}

/**
 * Return property or method with protected access modifier
 * @param string $key
 * @param mixed $value Value of property (NULL by default). If typeof callback, then defines as method
 * @param boolean $is_static Is class element static?
 * @return array
 */
function _protected($key, $value = null, $is_static = false)
{
    // Set values
    $result = array(
        'name'   => trim((string) $key),
        'value'  => $value,
        'access' => get_protected(),
        'method' => false,
        'static' => (boolean) $is_static,
    );
    // Name isn't valid
    if(empty($result['name'])) {
        trigger_error("Property and method name cannot be empty", E_USER_ERROR);
    } elseif(is_callable($value)) {
        // Is method
        $result['method'] = true;
    }
    return $result;
}

/**
 * Prototype of _protected() with defined is_static as true
 * @param string $key
 * @param mixed $value
 * @return array
 */
function _protected_static($key, $value = null)
{
    return _protected($key, $value, true);
}

/**
 * Return property or method with private access modifier
 * @param string $key
 * @param mixed $value Value of property (NULL by default). If typeof callback, then defines as method
 * @param boolean $is_static Is class element static?
 * @return array
 */
function _private($key, $value = null, $is_static = false)
{
    $result = array(
        'name'   => trim((string) $key),
        'value'  => $value,
        'access' => get_private(),
        'method' => false,
        'static' => (boolean) $is_static,
    );
    // Name isn't valid
    if(empty($result['name'])) {
        trigger_error("Property and method name cannot be empty", E_USER_ERROR);
    } elseif(is_callable($value)) {
        // Is method
        $result['method'] = true;
    }
    return $result;
}

/**
 * Prototype of _private() with defined is_static as true
 * @param string $key
 * @param mixed $value
 * @return array
 */
function _private_static($key, $value = null)
{
    return _private($key, $value, true);
}

/**
 * Return parents of current class
 * @return array|string Return an array if there is more than 1 parent. Else return string
 */
function _parent()
{
    $stack = stack();
    // Wrong context
    if(!isset($stack['class'])) {
        trigger_error("Using 'parent' in wrong context", E_USER_ERROR);
    }
    $storage = storage();
    // There is no class with this key
    if(!isset($storage['classes'][$stack['class']])) {
        trigger_error("Class '{$stack['class']}' not found", E_USER_ERROR);
    } elseif(($size = sizeof($storage['classes'][$stack['class']]['extended'])) > 0) {
        // There is such class and class extended some parents, check number of parents
        return ($size > 1)
                ? $storage['classes'][$stack['class']]['extended']
                : end($storage['classes'][$stack['class']]['extended']);
    }
    return '';
}

/**
 * Create class, write class props and methods to storage
 * @param string $class_name
 * @param array $extended
 * @param array $implements
 * @param array $elements
 * Variations:
 *  - Empty class:
 *     _class('MyClass');
 *  - Class extends some other classes:
 *     _class('MyClassExtended', _extends('MyClass', 'AnotherClass'));
 *  - Class implemets interfaces:
 *     _class('MyClassImplements', _implements('MyInterface', 'AnotherInterface'));
 *  - Class with methods and properties
 *     _class('MyClass', array(
 *         _protected_static('my_static_prop', 'default value'),
 *         _public('__construct', function() {
 *             echo 'New object created';
 *         }),
 *     ));
 */
function _class($class_name)
{
    $class_name = trim((string) $class_name);
    // Class name isn't valid
    if(empty($class_name)) {
        trigger_error("Class name cannot be empty", E_USER_ERROR);
    }
    $class_name_l = strtolower($class_name);
    $storage      = storage();
    // Class already exists
    if(isset($storage['classes'][$class_name_l])) {
        trigger_error("Cannot redeclare class {$class_name}", E_USER_ERROR);
    }
    $class   = array(
        'class'      => $class_name,
        'extends'    => array(),
        'implements' => array(),
        'methods'    => array(),
        'properties' => array(),
    );
    $func_args = func_get_args();
    unset($func_args[0]);
    if(sizeof($func_args) > 0) {
        // Set 'implements' key to last key of array if exists
        foreach($func_args as $key => $value) {
            if(isset($value['implements'])) {
                $func_args[] = $value;
                unset($func_args[$key]);
                break;
            }
        }
        // Foreach function param (extends, implements, class structures)
        foreach($func_args as $arg_value) {
            // Extending some classes
            if( isset($arg_value['extends'])
             && is_array($arg_value['extends'])
             && sizeof($arg_value['extends']) > 0
            ) {
                // Foreach extending class
                foreach($arg_value['extends'] as $value) {
                    $extend_class_name = trim(strtolower((string) $value));
                    // Class name isn't valid
                    if(empty($extend_class_name)) {
                        trigger_error("Cannot extend class with empty name", E_USER_ERROR);
                    } elseif(!isset($storage['classes'][$extend_class_name])) {
                        // Class doesn't exists
                        trigger_error("Class {$value} not found", E_USER_ERROR);
                    } elseif(sizeof($class['extends']) > 0 && in_array($extend_class_name, $class['extends'])) {
                        // This class already extended
                        trigger_error("Class {$class['class']} cannot extend previously extended class {$value}",
                                      E_USER_ERROR);
                    }
                    // Get every prop and method
                    foreach(array('methods', 'properties') as $element) {
                        foreach($storage['classes'][$extend_class_name][$element] as $key => $value) {
                            // Class structure already exists and overwriting isn't available
                            if(isset($class[$element][$key]) && !MULTIPLE_OVERWRITE) {
                                trigger_error("Class {$class['class']} cannot overwrite {$value['name']}"
                                            . " extended from {$class[$element][$key]['extended']}"
                                            . " with {$storage['classes'][$extend_class_name]['class']}::"
                                            . ($value['method'] ? $value['name'] . '()' : $value['name']),
                                            E_USER_ERROR);
                            }
                            $class[$element][$key]             = $value;
                            $class[$element][$key]['extended'] = $storage['classes'][$extend_class_name]['class'];
                        }
                    }
                    // Add extended class to list of extended classes
                    $class['extends'][] = $extend_class_name;
                }
            } elseif(isset($arg_value[0])) {
                // Form class with structures -- props and methods
                foreach($arg_value as $value) {
                    if(!isset($value['name'])) {
                        continue;
                    }
                    $element_name = strtolower($value['name']);

                    if(isset($class['methods'][$element_name]) && $value['method']) {
                        // Current struct is method and same methods already exists
                        $tmp = 'methods';
                    } elseif(isset($class['properties'][$element_name]) && !$value['method']) {
                        // Current struct is prop and same prop exists
                        $tmp = 'properties';
                    }
                    // Current prop already exists, check if it's extended, check access levels
                    if(isset($tmp)) {
                        // Struct extended, check access levels
                        if(isset($class[$tmp][$element_name]['extended'])) {
                            // Wrong access level
                            if($class[$tmp][$element_name]['access'][0] < $value['access'][0]) {
                                $message  = "Access level to {$class['class']}::{$value['name']}";
                                if($value['method']) {
                                    $message .= '()';
                                }
                                $message .= " must be {$class[$tmp][$element_name]['access'][1]} "
                                          . "(as in class {$class[$tmp][$element_name]['extended']})";
                            } elseif($class[$tmp][$element_name]['static'] ^ $value['static']) {
                                // Static value is differents from parent
                                $message = ($value['static'])
                                            ? "Cannot make non static %s::%s static in class %s"
                                            : "Cannot make static %s::%s non static in class %s";
                                $message = sprintf(
                                    $message,
                                    $class[$tmp][$element_name]['extended'],
                                    $value['method'] ? $value['name'] . '()' : $value['name'],
                                    $class['class']
                                );
                            }
                        } else {
                            // Struct not extended, trying to redeclare struct
                            $message = "Cannot redeclare {$class['class']}::{$value['name']}";
                            if($value['method']) {
                                $message .= '()';
                            }
                        }
                        if(isset($message)) {
                            trigger_error($message, E_USER_ERROR);
                        }
                    }
                    // Add struct
                    $class[$value['method'] ? 'methods' : 'properties'][$element_name] = $value;
                    unset($tmp);
                }
                // Construct method can't be static
                if(isset($class['methods']['__construct']) && $class['methods']['__construct']['static']) {
                    trigger_error("Constructor {$class_name}::__construct() cannot be static", E_USER_ERROR);
                } elseif(isset($class['methods']['__destruct']) && $class['methods']['__destruct']['static']) {
                    trigger_error("Destructor {$class_name}::__destruct() cannot be static", E_USER_ERROR);
                } elseif(isset($class['methods']['__clone']) && $class['methods']['__clone']['static']) {
                    // __clone can't be static
                    trigger_error("The magic method {$class_name}::__clone() cannot be static", E_USER_ERROR);
                } elseif(isset($class['methods']['__callstatic'])) {
                    // __callStatic can't be static
                    if(!$class['methods']['__callstatic']['static']) {
                        trigger_error("The magic method __callStatic() must be static", E_USER_ERROR);
                    } elseif($class['methods']['__callstatic']['access'][0] > 0) {
                        // __clone must be public
                        trigger_error("The magic method __callStatic() must be public", E_USER_ERROR);
                    }
                }
                // Foreach magic methods check if is static or not public
                foreach(array('__get', '__set', '__isset', '__unset', '__call') as $magic) {
                    if(isset($class['methods'][$magic])) {
                        if($class['methods'][$magic]['static']) {
                            trigger_error("The magic method {$magic}() cannot be static", E_USER_ERROR);
                        } elseif($class['methods'][$magic]['access'][0] > 0) {
                            trigger_error("The magic method {$magic}() must be public", E_USER_ERROR);
                        }
                    }
                }
            } elseif(isset($arg_value['implements'])
                  && is_array($arg_value['implements'])
                  && sizeof($arg_value['implements']) > 0
            ) {
                // Implement some interfaces
                foreach($arg_value['implements'] as $value) {
                    $interface_name = trim(strtolower((string) $value));
                    // Interface name isn't valid
                    if(empty($interface_name)) {
                        trigger_error("Cannot implement interface with empty name", E_USER_ERROR);
                    } elseif(!isset($storage['interfaces'][$interface_name])) {
                        // Interface doesn't exists
                        trigger_error("Interface {$value} not found", E_USER_ERROR);
                    } elseif(sizeof($class['implements']) > 0 && in_array($interface_name, $class['implements'])) {
                        // Interface already implemented
                        trigger_error("Class {$class['class']} cannot implement previously implemented interface {$value}",
                                      E_USER_ERROR);
                    }
                    // Foreach method of the interface
                    foreach($storage['interfaces'][$interface_name]['methods'] as $key => $method) {
                        // Method doesn't exists in class
                        if(!isset($class['methods'][$key])) {
                            trigger_error("Class {$class['class']} contains abstract method "
                                        . "{$method['name']}() and must therefore implement the remaining method "
                                        . "({$value}::{$method['name']})",
                                        E_USER_ERROR);
                        } elseif(0 !== $class['methods'][$key]['access'][0]) {
                            // Access modifier isn't public
                            trigger_error("Access level to {$class['class']}::{$method['name']}() must be public "
                                        . "(as in class {$value})",
                                        E_USER_ERROR);
                        } elseif($method['static'] ^ $class['methods'][$key]['static']) {
                            // Static value differends from interface
                            $message = ($method['static'])
                                            ? "Cannot make static %s::%s non static in class %s"
                                            : "Cannot make non static %s::%s static in class %s";
                            trigger_error(
                                sprintf(
                                    $message,
                                    $value,
                                    $method['name'],
                                    $class['class']
                                ),
                                E_USER_ERROR
                            );
                        }
                    }
                    // Add interface to implemented list
                    $class['implements'][] = $interface_name;
                }
            }
        }
    }
    // Add class to classes array, save class to storage
    $storage['classes'][$class_name_l] = $class;
    storage($storage);
}

/**
 * Register an interface
 * @param string $interface_name
 * @param array $extended
 * @param array $structures
 */
function _interface($interface_name)
{
    $interface_name = trim((string) $interface_name);
    // Interface name isn't valid
    if(empty($interface_name)) {
        trigger_error("Class name cannot be empty", E_USER_ERROR);
    }
    $interface_name_l = strtolower($interface_name);
    $storage          = storage();
    // Interface already exists
    if(isset($storage['interfaces'][$interface_name_l])) {
        trigger_error("Cannot redeclare class {$interface_name}", E_USER_ERROR);
    }
    $class = array(
        'class'   => $interface_name,
        'extends' => array(),
        'methods' => array(),
    );
    $func_args = func_get_args();
    unset($func_args[0]);
    if(sizeof($func_args) > 0) {
        // Foreach argument (struct or interface to extend)
        foreach($func_args as $arg_value) {
            if( isset($arg_value['extends'])
             && is_array($arg_value['extends'])
             && sizeof($arg_value['extends']) > 0
            ) {
                foreach($arg_value['extends'] as $value) {
                    $extend_class_name = trim(strtolower((string) $value));
                    if(empty($extend_class_name)) {
                        trigger_error("Cannot extend class with empty name", E_USER_ERROR);
                    } elseif(!isset($storage['interfaces'][$extend_class_name])) {
                        trigger_error("Class {$value} not found", E_USER_ERROR);
                    } elseif(sizeof($class['extends']) > 0 && in_array($extend_class_name, $class['extends'])) {
                        trigger_error("Class {$class['class']} cannot extend previously extended class {$value}",
                                      E_USER_ERROR);
                    }
                    foreach($storage['interfaces'][$extend_class_name]['methods'] as $key => $value) {
                        if(isset($class['methods'][$key]) && !MULTIPLE_OVERWRITE) {
                            trigger_error("Class {$class['class']} cannot overwrite {$value['name']}"
                                        . " extended from {$class['methods'][$key]['extended']}"
                                        . " with {$storage['interfaces'][$extend_class_name]['class']}::{$value['name']}()",
                                        E_USER_ERROR);
                        }
                        $class['methods'][$key]             = $value;
                        $class['methods'][$key]['extended'] = $storage['interfaces'][$extend_class_name]['class'];
                    }
                    $class['extends'][] = $extend_class_name;
                }
            } elseif(isset($arg_value[0])) {
                foreach($arg_value as $value) {
                    if(!isset($value['name'])) {
                        continue;
                    }
                    $element_name = strtolower($value['name']);
                    if($value['method']) {
                        trigger_error("Interface function {$class['class']}::{$value['name']}() "
                                    . "cannot contain body",
                                      E_USER_ERROR);
                    } elseif(null !== $value['value']) {
                        trigger_error("Interfaces may not include member variables", E_USER_ERROR);
                    } elseif(0 !== $value['access'][0]) {
                        trigger_error("Access type for interface method "
                                    . "{$class['class']}::{$value['name']}() must be omitted",
                                      E_USER_ERROR);
                    } elseif(isset($class['methods'][$element_name])) {
                        if(isset($class['methods'][$element_name]['extended'])) {
                            if($class['methods'][$element_name]['static'] ^ $value['static']) {
                                $message = ($value['static'])
                                            ? "Cannot make non static %s::%s() static in class %s"
                                            : "Cannot make static %s::%s() non static in class %s";
                                $message = sprintf(
                                    $message,
                                    $class['methods'][$element_name]['extended'],
                                    $value['name'],
                                    $class['class']
                                );
                                trigger_error($message, E_USER_ERROR);
                            }
                        } else {
                            trigger_error("Cannot redeclare {$class['class']}::{$value['name']}()", E_USER_ERROR);
                        }
                    }
                    $class['methods'][$element_name] = $value;
                }
            }
        }
    }
    // Save interface
    $storage['interfaces'][$interface_name_l] = $class;
    storage($storage);
}

/**
 * Create new object
 * @param string $class_name
 * @param mixed Params for __construct()
 * @return string
 */
function _new($class_name)
{
    $class_name = trim((string) $class_name);
    // Class name isn't valid
    if(empty($class_name)) {
        trigger_error("Cannot find class with empty name", E_USER_ERROR);
    }
    $class_name_l = strtolower($class_name);
    $storage      = storage();
    // Class doesn't exists
    if(!isset($storage['classes'][$class_name_l])) {
        trigger_error("Class '{$class_name}' not found", E_USER_ERROR);
    }
    // Check if object name already exists
    do {
        $object_key = OBJECT_PREFIX . random_string(OBJECT_LENGTH);
    } while(isset($storage['objects'][$object_key]));
    $storage['objects'][$object_key] = array(
        'class'      => $class_name,
        'class_l'    => $class_name_l,
        'properties' => array(),
    );
    // Write all non-static class properties to object
    foreach($storage['classes'][$class_name_l]['properties'] as $key => $value) {
        if(!$value['static']) {
            $storage['objects'][$object_key]['properties'][$key] = $value;
        }
    }
    // Write object to storage
    storage($storage);
    // Class constructor exists, execute it
    if(isset($storage['classes'][$class_name_l]['methods']['__construct'])) {
        if(func_num_args() > 1) {
            $func_args = func_get_args();
            unset($func_args[0]);
            call_user_func_array('_call', array_merge(array($object_key, '__construct'), $func_args));
        } else {
            _call($object_key, '__construct');
        }
    }
    return $object_key;
}

/**
 * Call class method
 * @param string $class
 * @param mixed method arguments
 * @return mixed
 */
function _call($class)
{
    $class = trim((string) $class);
    // class name invalid
    if(empty($class)) {
        trigger_error("Cannot get class with empty name", E_USER_ERROR);
    } elseif(sizeof($tmp = explode('::', $class)) > 1) {
        // Call static method
        // Class name is invalid
        if(empty($tmp[0])) {
            trigger_error("Cannot get class with empty name", E_USER_ERROR);
        }
        $function   = rtrim($tmp[1], '()');
        $function_l = strtolower($function);
        // Method name is invalid
        if(empty($function)) {
            trigger_error("Cannot call method with empty name", E_USER_ERROR);
        }
        $class   = $tmp[0];
        $class_l = strtolower($class);
        $storage = storage();
        // Class doesn't exists
        if(!isset($storage['classes'][$class_l])) {
            trigger_error("Class {$class} not found", E_USER_ERROR);
        } elseif(!isset($storage['classes'][$class_l]['methods'][$function_l])) {
            // Method doesn't exists, if magic __callStatic exists, then execute it
            if(isset($storage['classes'][$class_l]['methods']['__callstatic'])) {
                return _call($class . '::__callstatic', $function, array_slice(func_get_args(), 1));
            }
            trigger_error("Call to undefined method {$class}::{$function}()", E_USER_ERROR);
        } elseif($storage['classes'][$class_l]['methods'][$function_l]['access'][0] > 0) {
            // Method access level is higher than public, check permissions
            $stack = stack();
            // Stack value is invalid
            if(!isset($stack['class'])) {
                // If magic __callStatic exists, then execute it
                if(isset($storage['classes'][$class_l]['methods']['__callstatic'])) {
                    return _call($class . '::__callstatic', $function, array_slice(func_get_args(), 1));
                }
                trigger_error("Cannot call {$storage['classes'][$class_l]['methods'][$function_l]['access'][1]}"
                            . " {$class}::{$function}()", E_USER_ERROR);
            } elseif($stack['class'] !== $class_l) {
                // Method called not from parent class, if magic __callStatic exists, then execute it
                if(isset($storage['classes'][$class_l]['methods']['__callstatic'])) {
                    return _call($class . '::__callstatic', $function, array_slice(func_get_args(), 1));
                }
                trigger_error("Call {$storage['classes'][$class_l]['methods'][$function_l]['access'][1]}"
                            . " {$class}::{$function}() from context '{$storage['classes'][$stack['class']]['class']}'",
                              E_USER_ERROR);
            } elseif($storage['classes'][$class_l]['methods'][$function_l]['access'][0] > 1
                  && isset($storage['classes'][$class_l]['methods'][$function_l]['extended'])
                  && (!isset($storage['classes'][$class_l]['methods'][$stack['method']]['extended'])
                  ||  strcasecmp($storage['classes'][$class_l]['methods'][$function_l]['extended'],
                                 $storage['classes'][$class_l]['methods'][$stack['method']]['extended']))
            ) {
                // Access level is private and method extended from some class
                // And (current class not equals parent class or parent class isn't set)
                if(isset($storage['classes'][$class_l]['methods']['__callstatic'])) {
                    return _call($class . '::__callstatic', $function, array_slice(func_get_args(), 1));
                }
                trigger_error("Call to private method {$storage['classes'][$class_l]['methods'][$function_l]['extended']}::{$function}()"
                            . " from context '{$class}'", E_USER_ERROR);
            }
        }
        // Push info about current method to stack
        stack(array(
            'class'  => $class_l,
            'method' => $function_l,
        ));
        $func_args = func_get_args();
        unset($func_args[0]);
    } else {
        // Call non-static method
        $func_args = func_get_args();
        // Method name isn't valid
        if( !isset($func_args[1])
         || !($function = trim((string) $func_args[1]))
        ) {
            trigger_error("Cannot call method with empty name", E_USER_ERROR);
        }
        $storage    = storage();

        $object     = $class;
        $class_l    = $storage['objects'][$object]['class_l'];
        $class      = $storage['classes'][$class_l]['class'];
        $function_l = strtolower($function);
        // Object doesn't exists
        if(!isset($storage['objects'][$object])) {
            trigger_error("Object '{$object}' not found");
        } elseif(!isset($storage['classes'][$class_l]['methods'][$function_l])) {
            // Class method doesn't exists, if __call exists, call it
            if(isset($storage['classes'][$class_l]['methods']['__call'])) {
                return _call($object, '__call', $function, array_slice(func_get_args(), 2));
            }
            trigger_error("Call to undefined method {$class}::{$function}()", E_USER_ERROR);
        } elseif($storage['classes'][$class_l]['methods'][$function_l]['access'][0] > 0) {
            // Check access
            $stack = stack();
            if(!isset($stack['class'])) {
                if(isset($storage['classes'][$class_l]['methods']['__call'])) {
                    return _call($object, '__call', $function, array_slice(func_get_args(), 2));
                }
                trigger_error("Cannot call {$storage['classes'][$class_l]['methods'][$function_l]['access'][1]} {$class}::{$function}()",
                              E_USER_ERROR);
            } elseif($stack['class'] !== $class_l) {
                // Class method isn't visible for current class
                if(isset($storage['classes'][$class_l]['methods']['__call'])) {
                    return _call($object, '__call', $function, array_slice(func_get_args(), 2));
                }
                trigger_error("Call {$storage['classes'][$class_l]['methods'][$function_l]['access'][1]}"
                            . " {$class}::{$function}() from context '{$storage['classes'][$stack['class']]['class']}'",
                              E_USER_ERROR);
            } elseif($storage['classes'][$class_l]['methods'][$function_l]['access'][0] > 1
                  && isset($storage['classes'][$class_l]['methods'][$function_l]['extended'])
                  && (!isset($storage['classes'][$class_l]['methods'][$stack['method']]['extended'])
                  ||  strcasecmp($storage['classes'][$class_l]['methods'][$function_l]['extended'],
                                 $storage['classes'][$class_l]['methods'][$stack['method']]['extended']))
            ) {
                if(isset($storage['classes'][$class_l]['methods']['__call'])) {
                    return _call($object, '__call', $function, array_slice(func_get_args(), 2));
                }
                trigger_error("Call to private method {$storage['classes'][$class_l]['methods'][$function_l]['extended']}"
                            . "::{$function}() from context '{$class}'", E_USER_ERROR);
            }
        }
        stack(array(
            'class'  => $class_l,
            'method' => $function_l,
            'object' => $object,
        ));
        unset($func_args[0], $func_args[1]);
    }
    // Execute user method with params
    $result = call_user_func_array($storage['classes'][$class_l]['methods'][$function_l]['value'], array_values($func_args));
    // Pop last perms-element from stack
    stack(true);
    return $result;
}
function _prop($class)
{
    $class = trim((string) $class);
    if(empty($class)) {
        trigger_error("Cannot get class with empty name", E_USER_ERROR);
    }
    if(sizeof($tmp = explode('::', $class)) > 1) {
        if(empty($tmp[0])) {
            trigger_error("Cannot get class with empty name", E_USER_ERROR);
        }
        $key   = $tmp[1];
        $key_l = strtolower($key);
        if(empty($key)) {
            trigger_error("Cannot get property with empty name", E_USER_ERROR);
        }
        $class   = $tmp[0];
        $class_l = strtolower($class);
        $storage = storage();
        if(!isset($storage['classes'][$class_l])) {
            trigger_error("Class {$class} not found", E_USER_ERROR);
        } elseif(!isset($storage['classes'][$class_l]['properties'][$key_l])) {
            trigger_error("Trying to get undefined property {$class}::\${$key}", E_USER_ERROR);
        } elseif($storage['classes'][$class_l]['properties'][$key_l]['access'][0] > 0) {
            $stack = stack();
            if(!isset($stack['class'])) {
                trigger_error("Cannot get {$storage['classes'][$class_l]['properties'][$key_l]['access'][1]} {$class}::\${$key}",
                              E_USER_ERROR);
            } elseif($stack['class'] !== $class_l) {
                trigger_error("Trying to get {$storage['classes'][$class_l]['properties'][$key_l]['access'][1]} "
                            . "{$class}::\${$key} from context '{$storage['classes'][$stack['class']]['class']}'", E_USER_ERROR);
            } elseif($storage['classes'][$class_l]['properties'][$key_l]['access'][0] > 1
                  && isset($storage['classes'][$class_l]['properties'][$key_l]['extended'])
                  && (!isset($storage['classes'][$class_l]['methods'][$stack['method']]['extended'])
                  ||  strcasecmp($storage['classes'][$class_l]['properties'][$key_l]['extended'],
                                 $storage['classes'][$class_l]['methods'][$stack['method']]['extended']))
            ) {
                trigger_error("Trying to get private property {$storage['classes'][$class_l]['properties'][$key_l]['extended']}::"
                            . "\${$key} from context '{$class}'", E_USER_ERROR);
            }
        }
        if(func_num_args() > 1) {
            $value = func_get_arg(1);
        }
    } else {
        $func_args = func_get_args();
        if( !isset($func_args[1])
         || !($prop = trim((string) $func_args[1]))
        ) {
            trigger_error("Cannot get property with empty name", E_USER_ERROR);
        }
        $storage = storage();

        $object  = $class;
        $class_l = $storage['objects'][$object]['class_l'];
        $class   = $storage['classes'][$class_l]['class'];
        $prop_l  = strtolower($prop);
        if(isset($func_args[2])) {
            $value = $func_args[2];
        }
        if(!isset($storage['objects'][$object])) {
            trigger_error("Object '{$object}' not found");
        } elseif(!isset($storage['objects'][$object]['properties'][$prop_l])) {
            if(isset($storage['classes'][$class_l]['methods'][isset($value) ? '__set' : '__get'])) {
                return _call($object, isset($value) ? '__set' : '__get', $prop, isset($value) ? $value : null);
            }
            trigger_error("Trying to get undefined property {$class}::\${$prop}", E_USER_ERROR);
        } elseif($storage['objects'][$object]['properties'][$prop_l]['access'][0] > 0) {
            $stack = stack();
            if(!isset($stack['class'])) {
                if(isset($storage['classes'][$class_l]['methods'][isset($value) ? '__set' : '__get'])) {
                    return _call($object, isset($value) ? '__set' : '__get', $prop, isset($value) ? $value : null);
                }
                trigger_error("Cannot get {$storage['classes'][$class_l]['properties'][$prop_l]['access'][1]} {$class}::\${$prop}",
                              E_USER_ERROR);
            } elseif($stack['class'] !== $class_l) {
                if(isset($storage['classes'][$class_l]['methods'][isset($value) ? '__set' : '__get'])) {
                    return _call($object, isset($value) ? '__set' : '__get', $prop, isset($value) ? $value : null);
                }
                trigger_error("Trying to get {$storage['classes'][$class_l]['properties'][$prop_l]['access'][1]} {$class}::"
                            . "\${$prop} from context '{$storage['classes'][$stack['class']]['class']}'", E_USER_ERROR);
            } elseif($storage['classes'][$class_l]['properties'][$prop_l]['access'][0] > 1
                  && isset($storage['classes'][$class_l]['properties'][$prop_l]['extended'])
                  && (!isset($storage['classes'][$class_l]['methods'][$stack['method']]['extended'])
                  ||  strcasecmp($storage['classes'][$class_l]['properties'][$prop_l]['extended'],
                                 $storage['classes'][$class_l]['methods'][$stack['method']]['extended']))
            ) {
                if(isset($storage['classes'][$class_l]['methods'][isset($value) ? '__set' : '__get'])) {
                    return _call($object, isset($value) ? '__set' : '__get', $prop, isset($value) ? $value : null);
                }
                trigger_error("Trying to get private property {$storage['classes'][$class_l]['properties'][$prop_l]['extended']}::"
                            . "\${$prop} from context '{$class}'", E_USER_ERROR);
            }
        }
    }
    if(isset($func_args)) {
        if(isset($value)) {
            $storage['objects'][$object]['properties'][$prop_l]['value'] = $value;
            storage($storage);
        }
        return $storage['objects'][$object]['properties'][$prop_l]['value'];
    } else {
        if(isset($value)) {
            $storage['classes'][$class_l]['properties'][$key_l]['value'] = $value;
            storage($storage);
        }
        return $storage['classes'][$class_l]['properties'][$key_l]['value'];
    }
}
function _this()
{
    $stack = stack();
    if(!isset($stack['object'])) {
        trigger_error("Using \$this when not in object context", E_USER_ERROR);
    } elseif(func_num_args() == 0) {
        return $stack['object'];
    }
    $func_args = func_get_args();
    
    $storage   = storage();
    if(sizeof($tmp = explode('()', $func_args[0])) > 1) {
        $key = strtolower(trim((string) $tmp[0]));
        if(!isset($storage['classes'][$stack['class']]['methods'][$key])) {
            if(isset($storage['classes'][$stack['class']]['methods']['__call'])) {
                return _call($stack['object'], '__call', $key, array_slice(func_get_args(), 2));
            }
            trigger_error("Call to undefined method {$storage['classes'][$stack['class']]['class']}::"
                        . "{$storage['classes'][$stack['class']]['methods'][$key]['name']}()", E_USER_ERROR);
        } elseif($storage['classes'][$stack['class']]['methods'][$key]['access'][0] > 1
              && isset($storage['classes'][$stack['class']]['methods'][$key]['extended'])
              && (!isset($storage['classes'][$stack['class']]['methods'][$stack['method']]['extended'])
              ||  strcasecmp($storage['classes'][$stack['class']]['methods'][$key]['extended'],
                             $storage['classes'][$stack['class']]['methods'][$stack['method']]['extended']))
        ) {
            trigger_error("Call to private method {$storage['classes'][$stack['class']]['methods'][$key]['extended']}::"
                        . "{$storage['classes'][$stack['class']]['methods'][$key]['name']}()", E_USER_ERROR);
        }
        unset($func_args[0]);

        stack(array(
            'class'  => $stack['class'],
            'method' => $key,
            'object' => $stack['object'],
        ));
        $result = call_user_func_array($storage['classes'][$stack['class']]['methods'][$key]['value'], array_values($func_args));
        stack(true);
        return $result;
    } else {
        $key = strtolower(trim((string) $func_args[0]));
        sizeof($func_args) > 1 && ($value = $func_args[1]);
        if(!isset($storage['objects'][$stack['object']]['properties'][$key])) {
            if(isset($storage['classes'][$stack['class']]['methods'][isset($value) ? '__set' : '__get'])) {
                return _this(isset($value) ? '__set()' : '__get()', $key, isset($value) ? $value : null);
            }
            trigger_error("Trying to access undefined property {$storage['classes'][$stack['class']]['class']}::{$key}", E_USER_ERROR);
        } elseif(isset($value)) {
            $storage['objects'][$stack['object']]['properties'][$key]['value'] = $value;
            storage($storage);
        }
        return $storage['objects'][$stack['object']]['properties'][$key]['value'];
    }
}
function _self()
{
    $stack = stack();
    if(!isset($stack['class'])) {
        trigger_error("Using self in wrong context", E_USER_ERROR);
    } elseif(func_num_args() == 0) {
        return $stack['class'];
    }
    $func_args = func_get_args();

    $storage   = storage();
    if(sizeof($tmp = explode('()', $func_args[0])) > 1) {
        $function   = trim($tmp[0]);
        $function_l = strtolower($function);
        unset($func_args[0]);

        if(!isset($storage['classes'][$stack['class']]['methods'][$function_l])) {
            if(isset($storage['classes'][$stack['class']]['methods']['__callstatic'])) {
                _self('__callstatic()', $function, array_values($func_args));
            }
            trigger_error("Call to undefined method {$storage['classes'][$stack['class']]['class']}::"
                        . "{$function}()", E_USER_ERROR);
        } elseif($storage['classes'][$stack['class']]['methods'][$function_l]['access'][0] > 1
              && isset($storage['classes'][$stack['class']]['methods'][$function_l]['extended'])
              && (!isset($storage['classes'][$stack['class']]['methods'][$stack['method']]['extended'])
              || strcasecmp($storage['classes'][$stack['class']]['methods'][$stack['method']]['extended'],
                            $storage['classes'][$stack['class']]['methods'][$function_l]['extended']))
        ) {
            trigger_error("Call to private method {$storage['classes'][$stack['class']]['methods'][$function_l]['extended']}::"
                        . "{$function}()", E_USER_ERROR);
        }
        stack(array(
            'class'  => $stack['class'],
            'method' => $function_l,
        ));
        $result = call_user_func_array($storage['classes'][$stack['class']]['methods'][$function_l]['value'], array_values($func_args));
        stack(true);
        return $result;
    } else {
        $key = strtolower(trim((string) $func_args[0]));
        sizeof($func_args) > 1 && ($value = $func_args[1]);
        if( !isset($storage['classes'][$stack['class']]['properties'][$key])
         || !$storage['classes'][$stack['class']]['properties'][$key]['static']) {
            trigger_error("Access to undeclared static property {$storage['classes'][$stack['class']]['class']}::{$key}", E_USER_ERROR);
        } elseif(isset($value)) {
            $storage['classes'][$stack['class']]['properties'][$key]['value'] = $value;
            storage($storage);
        }
        return $storage['classes'][$stack['class']]['properties'][$key]['value'];
    }
}

/**
 * Clone object
 * @param string $object
 * @return string
 */
function _clone($object)
{
    $object = trim((string) $object);
    // Object name isn't valid
    if(empty($object)) {
        trigger_error("Cannot clone non existing object", E_USER_ERROR);
    }
    $storage = storage();
    // Object doesn't exists
    if(!isset($storage['objects'][$object])) {
        trigger_error("Object '{$object}' not found", E_USER_ERROR);
    }
    // Generate name for new object, check if exists
    do {
        $object_clone = OBJECT_PREFIX . random_string(OBJECT_LENGTH);
    } while(isset($storage['objects'][$object_clone]));
    $storage['objects'][$object_clone] = $storage['objects'][$object];
    storage($storage);
    // Magic __clone declared, call it
    if(isset($storage['classes'][$storage['objects'][$object]['class_l']]['methods']['__clone'])) {
        _call($object_clone, '__clone');
    }
    return $object_clone;
}

/**
 * Check is object is an instance of class
 * @param string $object
 * @param string $class
 * @return boolean
 */
function _instanceof($object, $class)
{
    $object = trim((string) $object);
    $class  = trim((string) $class);
    // Object name isn't allowed
    if(empty($object)) {
        return false;
    } elseif(empty($class)) {
        // Class name isn't allowed
        trigger_error("Class name cannot be empty", E_USER_ERROR);
    }
    $class_l  = strtolower($class);
    $storage  = storage();
    // Class doesn't exists
    if(!isset($storage['classes'][$class_l])) {
        trigger_error("Class {$class} not found", E_USER_ERROR);
    } elseif(!isset($storage['objects'][$object])) {
        // Object doesn't exists
        return false;
    } elseif($class_l === $storage['objects'][$object]['class_l']) {
        return true;
    } elseif(isset($storage['classes'][$class_l]['extends'][0])) {
        // Foreach class parents check if is instance
        foreach($storage['classes'][$class_l]['extends'] as $value) {
            if(_instanceof($object, $value)) {
                return true;
            }
        }
    }
    return false;
}

include './helper_functions.php';

register_shutdown_function('ClassLib\shutdown_function');