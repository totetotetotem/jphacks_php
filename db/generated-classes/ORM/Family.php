<?php

namespace ORM;

use ORM\Base\Family as BaseFamily;

/**
 * Skeleton subclass for representing a row from the 'family' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Family extends BaseFamily
{
	public function format_as_response()
	{
		return [
			'family_id' => $this->getFamilyId(),
			'token' => $this->getToken()];
	}
}
