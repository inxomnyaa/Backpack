<?php

declare(strict_types=1);

namespace xenialdan\Backpack\arguments;

use CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;
use xenialdan\Backpack\Loader;

class DesignEnumArgument extends StringEnumArgument
{
    public function getTypeName(): string
    {
        return "string";
    }

    public function parse(string $argument, CommandSender $sender)
    {
        return $argument;
    }

    public function getEnumValues(): array
    {
        return array_keys(Loader::$skins);
    }

    public function getEnumName(): string
    {
        return "backpack design";
    }
}