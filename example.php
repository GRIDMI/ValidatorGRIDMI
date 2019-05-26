<?php

// Connect ValidatorGRIDMI
require 'ValidatorGRIDMI.php';

// This is JSON schema
$schema = <<<SCHEMA
{
	"type": "object",
	"properties": {
		"name": {
			"type": "string"
		},
		"age": {
			"type": "integer"
		}
	}
}
SCHEMA;

// This is JSON data
$data = <<<DATA
{
	"name": "GRIDMI",
	"age": "23"
}
DATA;

// In JSON Objects
$schema = json_decode($schema);
$data = json_decode($data);

// On start check data by schema
$errors = ValidatorGRIDMI::onValidate($schema, $data, false, false);

// Result of checking
echo '<pre>';
print_r($errors);
echo '</pre>';

// Result of checking in browser
/*

Array
(
    [0] => The integer is invalid!
)

*/
