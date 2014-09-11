<?php
namespace ClassLib
{
    /**
     * Code executes __destruct methods of all objects
     * @return void
     */
    function shutdown_function()
    {
        $storage = storage();
        // Foreach object, which have destructor, execute it
        foreach($storage['objects'] as $key => $value) {
            if(isset($storage['classes'][$value['class_l']]['methods']['__destruct'])) {
                _call($key, '__destruct');
            }
        }
    }

    /**
     * Return object class name
     * @param string|null $object (default null)
     * @return string
     */
    function get_class($object = null)
    {
        // Soon
    }

    /**
     * Dump info about object
     * @param string $object
     * @return void
     */
    function var_dump($object)
    {
        $object = trim((string) $object);
        // Object name isn't valid
        if(empty($object)) {
            return \var_dump(null);
        }
        $storage = storage();
        // Object doesn't exists
        if(!isset($storage['objects'][$object])) {
            return \var_dump(null);
        }
        \var_dump($storage['objects'][$object]);
    }
}