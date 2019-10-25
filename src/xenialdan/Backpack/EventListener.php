<?php

namespace xenialdan\Backpack;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class EventListener implements Listener
{

    //Generic events to handle the entity

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        Loader::loadBackpacks($player);
        if (Loader::wantsToWearBackpack($player))
            Loader::spawnBackpack($player);
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        Loader::despawnBackpack($event->getPlayer());
    }

    public function onRespawn(PlayerRespawnEvent $event): void
    {
        $player = $event->getPlayer();
        if (Loader::wantsToWearBackpack($player)) {
            Loader::spawnBackpack($player);
        } else Loader::despawnBackpack($player);
    }

    public function onTeleport(EntityTeleportEvent $event): void
    {
        if (!$event->getEntity() instanceof Player) return;
        $player = $event->getEntity();
        if (Loader::wantsToWearBackpack($player)) {
            Loader::spawnBackpack($player);
        } else Loader::despawnBackpack($player);
    }

    public function onBedLeave(PlayerBedLeaveEvent $event): void
    {
        $player = $event->getPlayer();
        if (Loader::wantsToWearBackpack($player)) {
            Loader::spawnBackpack($player);
        } else Loader::despawnBackpack($player);
    }

    public function onBedEnter(PlayerBedEnterEvent $event): void
    {
        Loader::despawnBackpack($event->getPlayer());
    }

    public function onSneak(PlayerToggleSneakEvent $event): void
    {
        if (($backpack = Loader::getBackpackEntity($event->getPlayer())) instanceof Backpack) {
            $backpack->updateProperties();
        }
    }

    public function onDrop(PlayerDropItemEvent $event)
    {
        /** @var CompoundTag $entry */
        if (!is_null(($entry = $event->getItem()->getNamedTagEntry(Loader::TAG_BACKPACK)))) {
            $event->setCancelled();
            if (($player = $event->getPlayer())->isOnline()) {
                /** @var Backpack $backpack */
                if (!($backpack = Loader::getBackpack($player)) instanceof Backpack) {
                    $player->sendMessage("You have no backpack");
                    return;
                }
                if (Loader::wearsBackpack($player)) {
                    $player->sendMessage("You already wear a backpack");
                    return;
                }
                $owner = $entry->getString(Loader::TAG_BACKPACK_OWNER);
                if ($owner === null || $owner !== $player->getName()) {
                    $player->sendMessage("Invalid backpack found, removing");
                    $player->getInventory()->remove($event->getItem());
                    return;
                }
                $player->sendMessage("$owner");
                $player->getInventory()->remove($event->getItem());
                #$backpack->write();TODO
                Loader::toggleBackpack($player);
            }
        }
    }

}