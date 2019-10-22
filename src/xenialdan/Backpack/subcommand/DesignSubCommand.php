<?php

namespace xenialdan\Backpack\subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use xenialdan\Backpack\arguments\DesignEnumArgument;
use xenialdan\Backpack\Backpack;
use xenialdan\Backpack\Loader;

class DesignSubCommand extends BaseSubCommand
{

	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	protected function prepare(): void
	{
		$this->setPermission("backpack.command");
		$this->registerArgument(0, new DesignEnumArgument("design"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if (empty(trim(($design = $args["design"] ?? "")))) {
			$sender->sendMessage(TextFormat::RED . "Type can not be empty");
			return;
		}
		if (!array_key_exists($design, Loader::$skins)) {
			$sender->sendMessage(TextFormat::RED . "Design $design does not exist");
			return;
		}
		if (!($backpack = Loader::getBackpack($sender)) instanceof Backpack) {
			$sender->sendMessage("You have no backpack");
			return;
		}
		$sender->sendMessage(TextFormat::GREEN . "Setting backpack to design $design");
		Loader::setType($sender, $design);
	}
}
