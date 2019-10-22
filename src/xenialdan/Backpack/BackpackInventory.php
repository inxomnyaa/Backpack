<?php

declare(strict_types=1);

namespace xenialdan\Backpack;

use muqsit\invmenu\inventories\ChestInventory;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class BackpackInventory extends ChestInventory
{
	/** @var Backpack */
	private $vault_data;

	public function setVaultData(Backpack $vault): void
	{
		$this->vault_data = $vault;
	}

	public function getVaultData(): Backpack
	{
		return $this->vault_data;
	}

	public function getName(): string
	{
		return "Backpack";
	}

	protected function getOpenSound(): int
	{
		return LevelSoundEventPacket::SOUND_LEASHKNOT_PLACE;
	}

	protected function getCloseSound(): int
	{
		return LevelSoundEventPacket::SOUND_LEASHKNOT_BREAK;
	}
}