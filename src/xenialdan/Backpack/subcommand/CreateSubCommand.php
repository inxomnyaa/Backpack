<?php

namespace xenialdan\Backpack\subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\Backpack\arguments\DesignEnumArgument;
use xenialdan\Backpack\Backpack;
use xenialdan\Backpack\Loader;

class CreateSubCommand extends BaseSubCommand
{

	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	protected function prepare(): void
	{
		$this->setPermission("backpack.command");
		$this->registerArgument(0, new DesignEnumArgument("design", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if (!$sender instanceof Player) {
			$sender->sendMessage("Please run ingame");
			return;
		}
		if (Loader::getBackpack($sender) instanceof Backpack) {
			$sender->sendMessage(TextFormat::RED . "You already have a backpack");
			return;
		}
		$design = $args["design"] ?? "default";
		$sender->sendMessage(TextFormat::GREEN . "Creating new backpack");
		Loader::setType($sender, $design);
		$backpack = new Backpack($sender->getName(), $design);
		if ($backpack instanceof Backpack) {
			@mkdir(pathinfo(Loader::getSavePath($sender), PATHINFO_DIRNAME), 0777, true);
			file_put_contents(Loader::getSavePath($sender), $backpack->write());
			unset($backpack);
		} else {
			$sender->sendMessage(TextFormat::RED . "Creating failed, check console");
			return;
		}
		Loader::loadBackpacks($sender);
		$sender->sendMessage(TextFormat::GREEN . "Giving backpack");
		Loader::giveBackpackItem($sender);
	}
}
