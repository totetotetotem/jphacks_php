<?php

require_once __DIR__ . '/middleware/JsonRenderer.php';

function transaction($callable)
{
	return \Propel\Runtime\Propel::getConnection()->transaction($callable);
}

$__renderer = new \middleware\JsonRenderer();
function get_renderer()
{
	global $__renderer;
	return $__renderer;
}
