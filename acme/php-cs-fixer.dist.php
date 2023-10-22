<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$acme = '/acme';

$finder = Finder::create()
	->in($acme)
	->exclude('vendor')
;

// The ruleset
$rules = [
		'@PSR12' => TRUE,
		'array_syntax' => ['syntax' => 'short'],
		'binary_operator_spaces' => TRUE,
		'blank_line_before_statement' => [
				'statements' => ['if', 'declare', 'return', 'throw', 'try'],
		],
		'braces' => [
				'position_after_functions_and_oop_constructs' => 'next',
				'position_after_control_structures' => 'same',
				'allow_single_line_anonymous_class_with_empty_body' => TRUE,
				'allow_single_line_closure' => TRUE,
		],
		'constant_case' => ['case' => 'upper'],
		'indentation_type' => TRUE,
		'method_argument_space' => [
				'on_multiline' => 'ensure_fully_multiline',
				'keep_multiple_spaces_after_comma' => FALSE,
		],
		'no_unused_imports' => TRUE,
		'ordered_imports' => ['sort_algorithm' => 'alpha'],
		'return_type_declaration' => ['space_before' => 'one'],
		'trailing_comma_in_multiline' => TRUE,
		'unary_operator_spaces' => TRUE,
];

// Config initialization
$config = new Config();
$config->setRules($rules);
$config->setIndent("\t");
$config->setFinder($finder);

return $config;
