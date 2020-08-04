<?php

namespace xenialdan\Backpack;

use CortexPE\Commando\PacketHooker;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase
{
	const TAG_BACKPACK_OWNER = "owner";
	const TAG_BACKPACK = "backpack";
	const MENU_TYPE_BACKPACK_CHEST = "backpack:chest";
	/** @var self */
	private static $instance;
	/** @var Skin[] */
	public static $skins = [];
	/** @var array */
	public static $backpacks = [];

	/**
	 * @param Player $player
	 * @return string
	 */
	public static function getSavePath(Player $player): string
	{
		return self::getInstance()->getDataFolder() . "players" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".nbt";
	}

	/**
	 * @param Entity|null $player
	 * @return bool
	 */
	public static function wearsBackpack(?Entity $player): bool
	{
		return $player instanceof Player && $player->getGenericFlag(Entity::DATA_FLAG_CHESTED);
	}

	/**
	 * @param Entity|null $player
	 * @return bool
	 */
	public static function wantsToWearBackpack(?Entity $player): bool
	{
		return $player instanceof Player && $player->isOnline() && self::getType($player) !== "none" && !self::wearsBackpack($player);
	}

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		return self::$instance;
	}

	/**
	 * @throws PluginException
	 */
	public function onLoad()
	{
		if (!extension_loaded("gd")) {
			throw new PluginException("GD library is not enabled! Please uncomment gd2 in php.ini!");
		}
		self::$instance = $this;
		$this->saveDefaultConfig();
		$this->saveResource("default.png");
		$this->saveResource("default.json");
		@mkdir($this->getDataFolder() . "players");
		$defaultJson = file_get_contents($this->getDataFolder() . "default.json");
		foreach (glob($this->getDataFolder() . "*.png") as $imagePath) {
			$json = $defaultJson;
			$fileName = pathinfo($imagePath, PATHINFO_FILENAME);
			$jsonPath = pathinfo($imagePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $fileName . ".json";
			if (file_exists($jsonPath)) $json = file_get_contents($jsonPath);
			$skin = new Skin("backpack.$fileName", self::fromImage(imagecreatefrompng($imagePath)), "", "geometry.backpack.$fileName", $json);
			if (!$skin->isValid()) {
				$this->getLogger()->error("Resulting skin of $fileName is not valid");
				continue;
			}
			self::$skins[$fileName] = $skin;
		}

		$this->getLogger()->info($this->getDescription()->getPrefix() . TextFormat::GREEN . count(self::$skins) . " backpacks successfully loaded: " . implode(", ", array_keys(self::$skins)));

		//TODO test
		self::$skins["none"] = null;

		Backpack::init();

		Entity::registerEntity(BackpackEntity::class, true, ['backpack']);
		$this->getServer()->getCommandMap()->register("Backpack", new Commands($this, "backpack", "Manage your backpack"));

	}

	/**
	 * from skinapi
	 * @param resource $img
	 * @return string
	 */
	public static function fromImage($img)
	{
		$bytes = '';
		for ($y = 0; $y < imagesy($img); $y++) {
			for ($x = 0; $x < imagesx($img); $x++) {
				$rgba = @imagecolorat($img, $x, $y);
				$a = ((~((int)($rgba >> 24))) << 1) & 0xff;
				$r = ($rgba >> 16) & 0xff;
				$g = ($rgba >> 8) & 0xff;
				$b = $rgba & 0xff;
				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		@imagedestroy($img);
		return $bytes;
	}

	/**
	 * @param Player $player
	 */
	public static function loadBackpacks(Player $player): void
	{
		self::despawnBackpack($player);
		unset(self::$backpacks[$player->getName()]);
		$type = self::getType($player);
		if ($type === "none") return;
		if (file_exists(($path = self::getSavePath($player)))) {
			$backpack = new Backpack($player->getName(), $type);
			$backpack->read(file_get_contents($path));
			self::$backpacks[$player->getName()] = $backpack;
		}
	}

	/**
	 * @param Player $player
	 * @param string $type
	 * @throws \InvalidArgumentException
	 */
	public static function setType(Player $player, string $type = "none")
	{
		if (!array_key_exists($type, self::$skins)) throw new \InvalidArgumentException("Type $type does not exist");
		if ($type === "none") self::despawnBackpack($player);
		self::getInstance()->getConfig()->setNested("players." . $player->getName(), $type);
		self::getInstance()->getConfig()->save();
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public static function getType(Player $player): string
	{
		return (string)self::getInstance()->getConfig()->getNested("players." . $player->getName(), "none");
	}

	/**
	 * @param Entity|null $player
	 * @return Backpack|null
	 */
	public static function getBackpack(?Entity $player): ?Backpack
	{
		if (!$player instanceof Player) return null;
		return self::$backpacks[$player->getName()] ?? null;
	}

	/**
	 * @param Player $player
	 * @throws \InvalidArgumentException
	 */
	public static function giveBackpackItem(Player $player): void
	{
		if (($backpack = self::getBackpack($player)) === null) return;
		$bpi = Item::get(Item::LEATHER_CHESTPLATE);
		$bpi->setCustomName($backpack->getMenuName());
		$bpi->setNamedTagEntry(new CompoundTag(self::TAG_BACKPACK, [new StringTag(self::TAG_BACKPACK_OWNER, $backpack->getPlayerName())]));
		$player->getInventory()->addItem($bpi);
	}

	/**
	 * @param Level|null $level If given, searches only in that level
	 * @return BackpackEntity[]
	 */
	public static function getAllBackpackEntities(?Level $level = null): array
	{
		$entities = [];
		if ($level instanceof Level)
			$levels = [$level];
		else $levels = self::getInstance()->getServer()->getLevels();
		foreach ($levels as $level) {
			$entities = array_merge($entities, array_filter($level->getEntities(), function (Entity $entity) {
				return $entity instanceof BackpackEntity && $entity->isValid() && !$entity->isFlaggedForDespawn() && !$entity->isClosed();
			}));
		};
		return $entities;
	}

	/**
	 * @param Player $player
	 * @return null|BackpackEntity
	 */
	public static function getBackpackEntity(Player $player): ?BackpackEntity
	{
		$id = $player->getId();
		$array = array_filter(self::getAllBackpackEntities($player->getLevel()), function (?BackpackEntity $backpackEntity) use ($id) {
			return $backpackEntity instanceof BackpackEntity && $backpackEntity->getOwningEntityId() === $id;
		});
		return array_shift($array);
	}

	public function onEnable()
	{
		@mkdir($this->getDataFolder() . "players");
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}
		if (!InvMenuHandler::isRegistered()) {
			InvMenuHandler::register($this);
		}
		if (InvMenuHandler::getMenuType(self::MENU_TYPE_BACKPACK_CHEST) === null) {
			$copy = InvMenuHandler::getMenuType(InvMenu::TYPE_CHEST);
			assert($copy !== null);
			InvMenuHandler::registerMenuType(new BackpackMenuMetadata(self::MENU_TYPE_BACKPACK_CHEST, $copy->getSize(), $copy->getWindowType(), BlockFactory::get(BlockIds::CHEST), Tile::CHEST));
		}

		foreach ($this->getServer()->getOnlinePlayers() as $player) {
			self::loadBackpacks($player);
			if (Loader::wantsToWearBackpack($player))
				self::spawnBackpack($player);
		}
	}

	public function onDisable()
	{
		foreach (self::getAllBackpackEntities() as $backpack) $backpack->flagForDespawn();
		self::$skins = [];
		self::$backpacks = [];
		$this->getConfig()->save();
	}

	/**
	 * Updates the chested flag based on config entry (show/hide)
	 * @param Player $player
	 * @throws LevelException
	 * @throws \InvalidArgumentException
	 */
	public static function toggleBackpack(Player $player): void
	{
		if (!self::wearsBackpack($player)) self::spawnBackpack($player);
		else self::despawnBackpack($player);
		$player->setGenericFlag(Entity::DATA_FLAG_CHESTED, !self::wearsBackpack($player));
	}

	/**
	 * @param Player $player
	 * @param bool $ride
	 * @return BackpackEntity
	 * @throws LevelException
	 * @throws \InvalidArgumentException
	 */
	public static function spawnBackpack(Player $player, bool $ride = true): BackpackEntity
	{
		self::despawnBackpack($player);
		$type = self::getType($player);
		if (self::$skins[$type] instanceof Skin) {
			$player->setGenericFlag(Entity::DATA_RIDER_ROTATION_LOCKED, true);
			$nbt = Entity::createBaseNBT($player, null, $player->getYaw());
			$entity = new BackpackEntity(self::$skins[$type], $player->getLevel(), $nbt);
			$entity->setOwningEntity($player);
			$player->getLevel()->addEntity($entity);
			$entity->spawnToAll();
			if ($ride) {
				$player->setGenericFlag(Entity::DATA_FLAG_CHESTED, true);
			}
			return $entity;
		}
		throw new \InvalidArgumentException("Type $type not found");
	}

	public static function despawnBackpack(Player $player): void
	{
		if (($backpack = self::getBackpackEntity($player)) instanceof BackpackEntity) {
			$player->setGenericFlag(Entity::DATA_FLAG_CHESTED, false);
			$backpack->flagForDespawn();
		}
	}
}