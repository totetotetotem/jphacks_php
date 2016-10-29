<?php

namespace ORM;

use ORM\Base\ItemMaster as BaseItemMaster;

/**
 * Skeleton subclass for representing a row from the 'item_master' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class ItemMaster extends BaseItemMaster
{
	public function format_as_response()
	{
		return [
			'item_id' => $this->getItemId(),
			'item_name' => $this->getItemName(),
			'default_expire_days' => $this->getDefaultExpireDays()];
	}
}
