<?php

namespace xenialdan\Backpack;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\Backpack\subcommand\CreateSubCommand;
use xenialdan\Backpack\subcommand\DesignSubCommand;
use xenialdan\Backpack\subcommand\GetSubCommand;

class Commands extends BaseCommand
{

	/**
	 * @throws \CortexPE\Commando\exception\ArgumentOrderException
	 * @throws \CortexPE\Commando\exception\SubCommandCollision
	 */
	protected function prepare(): void
	{
		$this->setPermission("backpack.command");
		$this->registerSubCommand(new GetSubCommand("get", "Get your backpack"));
		$this->registerSubCommand(new CreateSubCommand("create", "Create a new backpack"));
		$this->registerSubCommand(new DesignSubCommand("design", "Set the design of your backpack"));
	}

	/**
	 * @param CommandSender $sender
	 * @param string $aliasUsed
	 * @param BaseArgument[] $args
	 * @throws \pocketmine\level\LevelException
	 * @throws \InvalidArgumentException
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if (!$sender instanceof Player) {
			$sender->sendMessage("Please run ingame");
			return;
		}
		if (!Loader::getBackpack($sender) instanceof Backpack) {
			$sender->sendMessage("You have no");
			return;
		}
		Loader::toggleBackpack($sender);
		if (!Loader::wearsBackpack($sender)) {
			//take off
			$current = Loader::spawnBackpack($sender, false);
			if ($current instanceof BackpackEntity) {
				$current->setPositionAndRotation($sender->getLevel()->getSafeSpawn($sender->add($sender->getDirectionVector()->multiply(2))), ($sender->getYaw() + 180) % 360, 0);
				$current->respawnToAll();
			}
		}
	}
}
