<?php

declare(strict_types=1);

namespace xenialdan\Backpack;

use muqsit\invmenu\inventory\InvMenuInventory;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class BackpackInventory extends InvMenuInventory
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

	public function onOpen(Player $who) : void
	{
		parent::onOpen($who);
		if (count($this->getViewers()) === 1 and $this->getHolder()->isValid()) {
			$this->getHolder()->getLevelNonNull()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), $this->getOpenSound());
		}
	}

	public function onClose(Player $who) : void
	{
		if (count($this->getViewers()) === 1 and $this->getHolder()->isValid()) {
			$this->getHolder()->getLevelNonNull()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), $this->getCloseSound());
		}
		parent::onClose($who);
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