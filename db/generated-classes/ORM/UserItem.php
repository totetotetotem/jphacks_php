<?php

namespace ORM;

use ORM\Base\UserItem as BaseUserItem;

/**
 * Skeleton subclass for representing a row from the 'user_item' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class UserItem extends BaseUserItem
{
	public function format_as_response()
	{
		return [
			'user_item_id' => $this->getUserItemId(),
			'item_id' => $this->getItemId(),
			'item_name' => $this->getItemName(),
			'expire_date' => $this->getExpireDate('Y-m-d')];
	}
}
