<?php

namespace xenialdan\Backpack\subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\Backpack\Backpack;
use xenialdan\Backpack\Loader;

class GetSubCommand extends BaseSubCommand
{

	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	protected function prepare(): void
	{
		$this->setPermission("backpack.command");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if (!$sender instanceof Player) {
			$sender->sendMessage(TextFormat::RED . "Please run ingame");
			return;
		}
		if (!($backpack = Loader::getBackpack($sender)) instanceof Backpack) {
			$sender->sendMessage("You have no backpack");
			return;
		}
		$sender->sendMessage(TextFormat::GREEN . "Giving backpack");
		Loader::giveBackpackItem($sender);
	}
}
