<?php

declare(strict_types=1);

namespace xenialdan\Backpack;

use Closure;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

/** @see https://github.com/Muqsit/PlayerVaults */
class Backpack
{

	private const TAG_INVENTORY = "Inventory";
	/** @var BigEndianNBTStream */
	private static $nbtSerializer;
	/** @var string|null */
	private static $name_format = "{PLAYER}'s Backpack";

	public static function init(): void
	{
		self::$nbtSerializer = new BigEndianNBTStream();
	}

	public static function setNameFormat(?string $format = null): void
	{
		self::$name_format = $format;
	}

	/** @var string */
	private $playername;
	/** @var string */
	private $type;
	/** @var InvMenu */
	private $menu;

	public function __construct(string $playername, string $type = "default")
	{
		$this->playername = $playername;
		$this->type = $type;
		$this->menu = InvMenu::create(Loader::MENU_TYPE_BACKPACK_CHEST);
		$this->menu->getInventory()->setVaultData($this);
		$this->menu->setListener(Closure::fromCallable([$this, "onInventoryTransaction"]));
		$this->menu->setInventoryCloseListener(Closure::fromCallable([$this, "onInventoryClose"]));
		$this->menu->setName(strtr(self::$name_format, [
			"{PLAYER}" => $playername,
		]));
	}

	public function onInventoryTransaction(InvMenuTransaction $transaction): InvMenuTransactionResult
	{
		return strtolower($this->playername) === $transaction->getPlayer()->getLowerCaseName() || $transaction->getPlayer()->hasPermission("playervaults.others.edit") ? $transaction->continue() : $transaction->discard();
	}

	public function onInventoryClose(Player $viewer, BackpackInventory $inventory): void
	{
		@mkdir(pathinfo(Loader::getSavePath($viewer), PATHINFO_DIRNAME), 0777, true);
		file_put_contents(Loader::getSavePath($viewer), $this->write());
	}

	public function getPlayerName(): string
	{
		return $this->playername;
	}

	public function getMenuName(): string
	{
		return $this->menu->getInventory()->getName();
	}

	public function getInventory(): BackpackInventory
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->menu->getInventory();
	}

	public function send(Player $player, ?string $custom_name = null): void
	{
		$this->menu->send($player, $custom_name);
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	public function read(string $data): void
	{
		$contents = [];
		$inventoryTag = self::$nbtSerializer->readCompressed($data)->getListTag(self::TAG_INVENTORY);
		/** @var CompoundTag $tag */
		foreach ($inventoryTag as $tag) {
			$contents[$tag->getByte("Slot")] = Item::nbtDeserialize($tag);
		}
		$this->menu->getInventory()->setContents($contents);
	}

	public function write(): string
	{
		$contents = [];
		foreach ($this->menu->getInventory()->getContents() as $slot => $item) {
			$contents[] = $item->nbtSerialize($slot);
		}
		return self::$nbtSerializer->writeCompressed(
			new CompoundTag("", [
				new ListTag(self::TAG_INVENTORY, $contents)
			])
		);
	}
}