<?php

namespace xenialdan\Backpack;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BackpackEntity extends Human
{
	public $height = 0;
	public $width = 0;
	public $gravity = 0;
	public $canCollide = false;
	protected $drag = 0;

	public function __construct(Skin $skin, Level $level, CompoundTag $nbt)
	{
		$this->setSkin($skin);
		parent::__construct($level, $nbt);
		$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_WIDTH, 0.0);
		$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_HEIGHT, 0.0);
		$this->setNameTagVisible(false);
		$this->setNameTagAlwaysVisible(false);
		$this->getDataPropertyManager()->setVector3(self::DATA_RIDER_SEAT_POSITION, new Vector3());
	}

	public function updateProperties()
	{
		$this->setSneaking($this->getOwningEntity()->isSneaking());
		$this->setScale($this->getOwningEntity()->getScale());
		if (Loader::wearsBackpack($this->getOwningEntity())) {
			$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_WIDTH, 0.0);
			$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_HEIGHT, 0.0);
		} else {
			$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_WIDTH, 1.0);
			$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_HEIGHT, 1.0);
		}
		if (!empty($this->getDataPropertyManager()->getDirty()))//TODO TEST forced update
			$this->sendData($this->getViewers());
	}

	public function entityBaseTick(int $tickDiff = 1): bool
	{
		$hasUpdate = Entity::entityBaseTick($tickDiff);
		/** @var Player $player */
		if (($player = $this->getOwningEntity()) instanceof Player && $player->isConnected()) {
			$this->updateProperties();
			$this->setInvisible(($player->isInvisible() or !$player->isAlive()));
			//Only set this if player wears
			if (Loader::wearsBackpack($player)) {
				if (!($this->asPosition()->equals($player->asPosition()) && $this->yaw === $player->yaw)) {
					$this->setPositionAndRotation($player, $player->getYaw(), 0);
					$hasUpdate = true;
				}
			}
		} else {
			$this->flagForDespawn();
			return true;
		}
		return $hasUpdate;
	}

	public function isFireProof(): bool
	{
		return true;
	}

	public function canBeCollidedWith(): bool
	{
		return false;
	}

	protected function checkBlockCollision(): void
	{
	}

	public function canCollideWith(Entity $entity): bool
	{
		return false;
	}

	public function canBeMovedByCurrents(): bool
	{
		return false;
	}

	public function canBreathe(): bool
	{
		return true;
	}

	public function canSaveWithChunk(): bool
	{
		return false;
	}

	protected function applyGravity(): void
	{
		if (!Loader::wearsBackpack($this->getOwningEntity())) parent::applyGravity();
	}

	public function attack(EntityDamageEvent $source): void
	{
		$source->setCancelled();
		/** @var Player $player */
		if ($source instanceof EntityDamageByEntityEvent && ($player = $source->getDamager()) instanceof Player && !Loader::wearsBackpack($player)) {
			if (($backpack = Loader::getBackpack($player)) instanceof Backpack) {
				if ($player !== $this->getOwningEntity()) {
					$player->sendMessage(TextFormat::RED . "This is not your backpack!");
					return;
				}
				$backpack->send($player);
			}
		}
		return;
	}

	public function flagForDespawn(): void
	{
		if (($backpack = Loader::getBackpack($this->getOwningEntity())) instanceof Backpack) {
			@mkdir(pathinfo(Loader::getSavePath($this->getOwningEntity()), PATHINFO_DIRNAME), 0777, true);
			file_put_contents(Loader::getSavePath($this->getOwningEntity()), $backpack->write());
		}
		parent::flagForDespawn();
	}
}