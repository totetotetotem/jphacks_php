<?php

define('PROJECT_DIR', __DIR__ . '/..');
define('SPEC_DIR', PROJECT_DIR . '/api-spec');
define('DEST_REL_DIR', '../generated-api-schema');


function convert_spec_to_schema($spec)
{
	$schema = [
		'$schema' => 'http://json-schema.org/draft-04/schema#',
		'type' => 'object',
		'additionalProperties' => false];

	$sub_schema = [];
	$sub_required = [];
	foreach ($spec as $sub_name => $sub_spec) {
		$sub_schema[$sub_name] = generate_schema($sub_name, $sub_spec, $sub_required);
	}
	$schema['required'] = $sub_required;
	$schema['properties'] = $sub_schema;
	return $schema;
}

function generate_schema($name, $spec, &$required)
{
	if ($spec['required'] ?? true) {
		$required[] = $name;
	}

	if (isset($spec['+fields']) || isset($spec['+include'])) {
		$sub_required = [];
		$sub_properties = [];

		if (isset($spec['+include'])) {
			$load_file = search_include_file($spec['+include']);
			$yaml = yaml_parse_file($load_file);

			if (!isset($yaml[$name])) {
				throw new Exception("element '$name' is not found in $load_file");
			}

			$spec = array_merge_recursive($yaml[$name], $spec);
		}

		if (isset($spec['+fields'])) {
			foreach ($spec['+fields'] as $field_name => $field_spec) {
				$sub_properties[$field_name] = generate_schema($field_name, $field_spec, $sub_required);
			}
		}

		$schema['type'] = 'object';
		$schema['properties'] = $sub_properties;
		$schema['required'] = $sub_required;
		$schema['additionalProperties'] = false;
	} else if (isset($spec['type'])) {
		$schema = generate_field_schema($spec['type']);
	} else {
		throw new Exception('parse failed');
	}

	if ($spec['array']??false) {
		$schema = make_array_type($schema);
	}
	if (!($spec['required'] ?? true)) {
		$schema = make_nullable_type($schema);
	}

	return $schema;
}

function search_include_file($include_file)
{
	$include_file = SPEC_DIR . '/_includes/' . $include_file;
	if (file_exists($include_file)) {
		return $include_file;
	} else {
		throw new Exception("$include_file is not found");
	}
}

function generate_field_schema($type)
{
	switch ($type) {
		case 'string':
			return ['type' => 'string'];
		case 'int':
		case 'integer':
			return ['type' => 'integer'];
		case 'number':
			return ['type' => 'number'];
		case 'date':
			return ['type' => 'string', 'format' => 'date'];
		default:
			throw new Exception("Unknown spec type: $type");
	}
}

function make_array_type($schema)
{
	return [
		'type' => 'array',
		'items' => $schema];
}

function make_nullable_type($schema)
{
	return [
		'anyOf' => [
			$schema,
			['type' => 'null']]];
}

function convert_file($infile, $outfile)
{
	$yaml = yaml_parse_file($infile);
	if ($yaml === false) {
		throw new Exception("$infile is not readable as yaml");
	}
	if (!(isset($yaml['input']) && isset($yaml['output']))) {
		throw new Exception("$infile: required root object is not found");
	}

	$schema = [
		'input' => convert_spec_to_schema($yaml['input']),
		'output' => convert_spec_to_schema($yaml['output'])];
	file_put_contents($outfile, json_encode($schema));
}

function main()
{
	chdir(SPEC_DIR);
	$yaml_files = glob('**/*.yaml');
	foreach ($yaml_files as $yaml_file) {
		if ($yaml_file[0] == '_') { // _includes
			continue;
		}
		$outfile = DEST_REL_DIR . '/' . str_replace('.yaml', '.json', $yaml_file);
		$outdir = dirname($outfile);
		if (!is_dir($outdir)) {
			mkdir($outdir, 0755, true);
		}
		convert_file($yaml_file, $outfile);
	}

}

main();
