# ValidatorGRIDMI 2.0.0

 Simple JSON validator for PHP.

 This class will allow you to check the structure of the incoming JSON data.

 No dependencies, only one static class!

## What types does the class work with?

  |TYPE|DESCRIPTION|
  |:-:|:-|
  |Array|An array can be both an object and a primitive. If you want to check the structure of the array on the object template, then in `properties` you need to describe the object template. If you want to check each element of the array as a primitive, then you must specify the type of the primitive in the `properties`. You can limit the length of the array by adding an object with the name `length` and the properties `min` and `max`.|
  |Object|In `properties` you describe the structure of the object. You can limit the length of the array by adding an object with the name `length` and the properties `min` and `max`. You can describe the mandatory presence of an object property. More details below.|
  |String|Check string function - `is_string(data)`. If you want to check the string for compliance with a regular expression, then you need to add an object `pattern` with the `value` and `description` properties. Value is a regular expression pattern and Description is a description of the error in case of inconsistency.|
  |Numeric|Numeric check function - `is_numeric(data)`. If you want to check the numeric for compliance with a regular expression, then you need to add an object `pattern` with the `value` and `description` properties. Value is a regular expression pattern and Description is a description of the error in case of inconsistency.|
  |Double|Double check function - `is_double(data)`. If you want to check the double for compliance with a regular expression, then you need to add an object `pattern` with the `value` and `description` properties. Value is a regular expression pattern and Description is a description of the error in case of inconsistency.|
  |Float|Float check function - `is_float(data)`. If you want to check the float for compliance with a regular expression, then you need to add an object `pattern` with the `value` and `description` properties. Value is a regular expression pattern and Description is a description of the error in case of inconsistency.|
  |Integer|Integer check function - `is_int(data)`. If you want to check the integer for compliance with a regular expression, then you need to add an object `pattern` with the `value` and `description` properties. Value is a regular expression pattern and Description is a description of the error in case of inconsistency.|

## Features

  Each validation template must contain a property `type` for recursive validation.

## Example schema

  ~~~~
  {
    "type": "object",
    "length": {
      "min": 0,
      "max": 10
    },
    "properties": {
      "name": {
        "type": "string",
        "pattern": {
          "value": "/^GRIDMI$/",
          "description": "Wrong field `name`!"
        }
      },
      "age": {
        "type": "integer",
        "required": true
      },
      "telephones": {
        "type": "array",
        "properties": "integer"
      },
      "friends": {
        "type": "array",
        "required": true,
        "properties": {
          "name": {
            "type": "string"
          },
          "email": {
            "type": "string"
          }
        }
      }
    }
  }
  ~~~~

## Required fields

  If the property in the `object` is not found, then the class can refer to the structure to check whether this field is appropriate. If the property `required` is not found, an error will be caused that the presence of the property is not described!

## Result of checking

  The class will return an array of errors. The levels of the array depend on whether you specified the removal of the hierarchy when calling a static method. The number of errors depends on which validation mode you have selected.

## Class use

  ~~~~
  required 'ValidatorGRIDMI.php';
  $errors = ValidatorGRIDMI()::onValidate(JSON_SCHEMA, JSON_DATA, BOOL:remove_hierarchy?, BOOL:strict_mode?);
  echo empty($errors) ? 'No errors found!' : implode(', ', $errors);
  ~~~~

## License
  Free use with modification rights.

  `Without` the right to change the name of the class!
