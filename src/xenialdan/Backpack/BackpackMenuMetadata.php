<?php

declare(strict_types=1);

namespace xenialdan\Backpack;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\metadata\SingleBlockMenuMetadata;

class BackpackMenuMetadata extends SingleBlockMenuMetadata
{
	public function createInventory() : InvMenuInventory
	{
		return new BackpackInventory($this);
	}
}