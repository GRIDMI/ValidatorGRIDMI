<?php

/*
 * ValidatorGRIDMI version 2.0.0
 * -----------------------------
 * This class will allow you to check
 * the structure input data JSON format in PHP
 * */

class ValidatorGRIDMI {

    private static $BAD = array(

        // MAIN
        'mainFatalError'                => 'A major fatal error has occurred!',
        'mainTypeInvalid'               => 'The main type of the scheme is not valid!',
        'mainTypeNotFound'              => 'Type `@T` cannot be verified!',

        // ARRAY
        'arrayPropertiesInvalid'        => 'Array properties are invalid!',
        'arrayDataInvalid'              => 'Array data is not valid!',
        'arrayDataLengthNotDeclared'    => 'Array length limiter `min` and `max` must be integers!',
        'arrayDataLengthInvalid'        => 'The array length must be at least @MIN and not more than @MAX! Current length @NOW!',
        'arrayItemInvalid'              => 'Array element is not a @T!',

        // OBJECT
        'objectPropertiesInvalid'       => 'Object properties invalid!',
        'objectInvalid'                 => 'Object invalid!',
        'objectDataLengthNotDeclared'   => 'The length limiter `min` and `max` of the properties of the object must be integers!',
        'objectDataLengthInvalid'       => 'The length of the object properties must be at least @MIN and not more than @MAX! The current length is @NOW!',
        'objectRequiredNotDeclared'     => 'No information about the mandatory availability of properties!',
        'objectPropertyMustBeDeclared'  => '`@P` property must be declared as `@T`!',

        // PRIMITIVE
        'primitiveInvalid'              => 'The @T is invalid!',

        // REGULAR EXPRESSION
        'regExpRequiredFieldsInvalid'   => 'Regular expression delimiter `value` and `description` must be strings!',
        'regExpPatternNotDeclared'      => 'Regular expression pattern must be an object!'

    );

    /*
     * This is method for check pattern primitive value
     * Pattern in schema must contains [value:String, description:String]
     * */

    private static function onCheckPattern($pattern, $data, &$cursor) {

        if (isset($pattern -> value, $pattern -> description)) {

            // Value and description in pattern must be strings
            if (!is_string($pattern -> value) || !is_string($pattern -> description)) {
                return self::onCatchError($cursor, self::$BAD['regExpRequiredFieldsInvalid']);
            }

            // Return result check for regexp check
            return !(@preg_match($pattern -> value, $data)) && self::onCatchError($cursor, $pattern -> description);

        }

        // If properties for pattern is not declared
        return self::onCatchError($cursor, self::$BAD['regExpPatternNotDeclared']);

    }

    /*
     * This is method for recursive check
     * */

    private static function onCheckRecursive($schema, $data, &$cursor) {

        // Each property must be have type in STRING
        if (!isset($schema -> type) || !is_string($schema -> type)) {
            return self::onCatchError($cursor, self::$BAD['mainTypeInvalid']);
        }

        switch (strtolower($schema -> type)) {

            case 'array':

                // Properties must be declared and must be object or string
                if (!isset($schema -> properties) || (!is_object($schema -> properties) && !is_string($schema -> properties))) {
                    return self::onCatchError($cursor, self::$BAD['arrayPropertiesInvalid']);
                }

                // If input data is not array
                if (!is_array($data)) {
                    return self::onCatchError($cursor, self::$BAD['arrayDataInvalid']);
                }

                // If isset length constraint for array in schema
                if (isset($schema -> length -> min, $schema -> length -> max)) {

                    // Array length constrain must be integers
                    if (!is_int($schema -> length -> min) || !is_int($schema -> length -> max)) {
                        return self::onCatchError($cursor, self::$BAD['arrayDataLengthNotDeclared']);
                    }

                    // Count current length array
                    $lengthArray = count($data);

                    // If array bound from range then declare error
                    if ($lengthArray < $schema -> length -> min || $lengthArray > $schema -> length -> max) {

                        // Create information about array data
                        $info = array(
                            'search'    => array('@NOW', '@MIN', '@MAX'),
                            'replace'   => array($lengthArray, $schema -> length -> min, $schema -> length -> max)
                        );

                        // Add new error and exit if strict mode is enabled
                        return self::onCatchError($cursor, str_replace($info['search'], $info['replace'], self::$BAD['arrayDataLengthInvalid']));

                    }

                }

                // Before inner edit schema clone it
                $schema = clone $schema;

                // Mark about type each item
                $object = null;

                // If properties is object then check each item like object otherwise like primitive
                if (!($object = ($schema -> type = is_object($schema -> properties) ? 'object' : $schema -> properties) === 'object')) {
                    $schema -> properties = null;
                }

                foreach ($data as $index => $item) {

                    // Check input item from array
                    if (($object && !is_object($item)) || (!$object && is_object($item))) {

                        // Create description error of current item
                        $error = str_replace('@T', $object ? 'object' : 'primitive', self::$BAD['arrayItemInvalid']);

                        // Add new error and exit if strict mode is enabled
                        if (self::onCatchError($cursor[$index], $error)) {
                            return true;
                        }

                        continue;

                    }

                    // Send in recursive check
                    if (self::onCheckRecursive($schema, $item, $cursor[$index])) {
                        return true;
                    }

                }

                // If success
                return false;

            case 'object':

                // Properties must be object
                if (!isset($schema -> properties) || !is_object($schema -> properties)) {
                    return self::onCatchError($cursor, self::$BAD['objectPropertiesInvalid']);
                }

                // If input data is not object
                if (!is_object($data)) {
                    return self::onCatchError($cursor, self::$BAD['objectInvalid']);
                }

                // If isset length constraint of properties for object in schema
                if (isset($schema -> length -> min, $schema -> length -> max)) {

                    // Object length constrain must be integers
                    if (!is_int($schema -> length -> min) || !is_int($schema -> length -> max)) {
                        return self::onCatchError($cursor, self::$BAD['objectDataLengthNotDeclared']);
                    }

                    // Count current length of properties in object
                    $lengthObject = count(get_object_vars($data));

                    // If properties of object bound from range then declare error
                    if ($lengthObject < $schema -> length -> min || $lengthObject > $schema -> length -> max) {

                        // Create information about object data
                        $info = array(
                            'search'    => array('@NOW', '@MIN', '@MAX'),
                            'replace'   => array($lengthObject, $schema -> length -> min, $schema -> length -> max)
                        );

                        // Add new error and exit if strict mode is enabled
                        return self::onCatchError($cursor, str_replace($info['search'], $info['replace'], self::$BAD['objectDataLengthInvalid']));

                    }

                }

                foreach ($schema -> properties as $property => $schema) {

                    // If property not found in input data
                    if (!isset($data -> $property)) {

                        // For this moment must be declared `required` property
                        if (!isset($schema -> required) || !is_bool($schema -> required)) {

                            // Add new error and exit if strict mode is enabled
                            if (self::onCatchError($cursor[$property], self::$BAD['objectRequiredNotDeclared'])) {
                                return true;
                            }

                            continue;

                        }

                        if ($schema -> required) {

                            // Create information about property data
                            $info = array(
                                'search' => array('@P', '@T'),
                                'replace' => array($property, isset($schema -> type) && is_string($schema -> type) ? $schema -> type : 'NULL')
                            );

                            // Create description about error
                            $error = str_replace($info['search'], $info['replace'], self::$BAD['objectPropertyMustBeDeclared']);

                            // Add new error and exit if strict mode is enabled
                            if (self::onCatchError($cursor[$property], $error)) {
                                return true;
                            }

                        }

                        continue;

                    }

                    // Send in recursive check
                    if (self::onCheckRecursive($schema, $data -> $property, $cursor[$property])) {
                        return true;
                    }

                }

                // If success
                return false;

            case 'string':

                // Data is a string?
                if (!is_string($data)) {
                    return self::onCatchError($cursor, str_replace('@T', 'string', self::$BAD['primitiveInvalid']));
                }

                // If pattern is declared send data for check
                if (isset($schema -> pattern)) {
                    return self::onCheckPattern($schema -> pattern, $data, $cursor);
                }

                // If success
                return false;

            case 'numeric':

                // Data is a numeric?
                if (!is_numeric($data)) {
                    return self::onCatchError($cursor, str_replace('@T', 'numeric', self::$BAD['primitiveInvalid']));
                }

                // If pattern is declared send data for check
                if (isset($schema -> pattern)) {
                    return self::onCheckPattern($schema -> pattern, $data, $cursor);
                }

                // If success
                return false;

            case 'double':

                // Data is a double?
                if (!is_double($data)) {
                    return self::onCatchError($cursor, str_replace('@T', 'double', self::$BAD['primitiveInvalid']));
                }

                // If pattern is declared send data for check
                if (isset($schema -> pattern)) {
                    return self::onCheckPattern($schema -> pattern, $data, $cursor);
                }

                // If success
                return false;

            case 'float':

                // Data is a float?
                if (!is_float($data)) {
                    return self::onCatchError($cursor, str_replace('@T', 'float', self::$BAD['primitiveInvalid']));
                }

                // If pattern is declared send data for check
                if (isset($schema -> pattern)) {
                    return self::onCheckPattern($schema -> pattern, $data, $cursor);
                }

                // If success
                return false;

            case 'integer':

                // Data is a integer?
                if (!is_integer($data)) {
                    return self::onCatchError($cursor, str_replace('@T', 'integer', self::$BAD['primitiveInvalid']));
                }

                // If pattern is declared send data for check
                if (isset($schema -> pattern)) {
                    return self::onCheckPattern($schema -> pattern, $data, $cursor);
                }

                // If success
                return false;

        }

        // If type is not recognized
        return self::onCatchError($cursor, str_replace('@T', $schema -> type, self::$BAD['mainTypeNotFound']));

    }

    /*
     * This is method for intercept error
     * and return about continue checking
     * */

    private static function onCatchError(&$cursor, $description) {

        // Set description in inner cursor
        $cursor = $description;

        // If mode is strict then check will be stop
        return self::$strict;

    }

    /*
     * This is method for clear hierarchy
     * All errors in ONE array
     * */

    private static function onRemoveHierarchy(&$errors) {

        // Array without hierarchy
        $array = array();

        foreach ($errors as &$error) {

            if (is_array($error)) {

                // Merge result without hierarchy and continue
                $array = array_merge($array, self::onRemoveHierarchy($error));
                continue;

            }

            // Otherwise append string
            $array[] = $error;

        }

        // Return stack errors without hierarchy
        return $array;

    }

    /*
     * This is method for clear
     * traces inner of cursor after checking
     * */

    private static function onPrepareResult(&$errors) {

        foreach ($errors as $key => &$value) {

            // If value contains inner array start recursive
            if (is_array($value) && count($value) > 0) {
                self::onPrepareResult($value);
            }

            // Remove all value where is NULL or array is empty
            if (is_null($value) || (is_array($value) && count($value) == 0)) {
                unset($errors[$key]);
            }

        }

        // Return clear result
        return $errors;

    }

    // This is mark about mode checking
    private static $strict;

    /*
     * This method is main in class
     * It allow you check JSON structure
     * */

    public static function onValidate($schema, $data, $hierarchy = true, $strict = true) {

        // Initialize array with errors
        $errors = array();

        // Is the transferred scheme and data an object?
        if (is_object($schema) && is_object($data)) {

            // Set test mode
            self::$strict = $strict;

            // Run recursive validation
            self::onCheckRecursive($schema, $data, $errors['start']);

            // Prepare a bug report in array
            $errors = self::onPrepareResult($errors);

            // Return the final result
            return $hierarchy ? $errors : self::onRemoveHierarchy($errors);

        }

        // Initialize first and last error
        $errors[$hierarchy ? 'start' : 0] = self::$BAD['mainFatalError'];

        // Return the final result
        return $errors;

    }

}

?>
