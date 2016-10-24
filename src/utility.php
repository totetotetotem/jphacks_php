<?php

function transaction($callable){
	return \Propel\Runtime\Propel::getConnection()->transaction($callable);
}
