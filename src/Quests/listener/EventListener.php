<?php

declare(strict_types=1);

namespace Quests\listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\Player;

use pocketmine\utils\TextFormat;

use Quests\Main;
use Quests\manager\QuestType;
use Sessions\SessionCore;

/**
 * Class EventListener
 * @package Quests\listener
 *
 * @version 1.0.0
 * @since   1.0.0
 */
class EventListener implements Listener {

    private static array $playersBreak = [];
    private static array $playersKill = [];

    /**
     * @param EntityDamageEvent $event
     */
    public function onHit(EntityDamageEvent $event): void {
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();

            if ($entity->namedtag->getString("fox_nametag", "") === "quests") {
                $damager = $event->getDamager();

                if ($damager instanceof Player) {
                    Main::getInstance()->getQuestManager()->getQuest($damager);
                }
            }
        }
    }

    /**
     * @param Player $player
     * @return int
     */
    public static function getPlayerBreak(Player $player): int {
        if (isset(self::$playersBreak[$player->getUniqueId()->toString()])) {
            return self::$playersBreak[$player->getUniqueId()->toString()];
        }

        return 0;
    }

    /**
     * @param Player $player
     * @return int
     */
    public static function getPlayerKills(Player $player): int {
        if (isset(self::$playersKill[$player->getUniqueId()->toString()])) {
            return self::$playersKill[$player->getUniqueId()->toString()];
        }

        return 0;
    }

    /**
     * @param Player $player
     */
    public static function addPlayerBreak(Player $player): void {
        $total = 0;

        if (isset(self::$playersBreak[$player->getUniqueId()->toString()])) {
            $total = self::$playersBreak[$player->getUniqueId()->toString()];
        }

        self::$playersBreak[$player->getUniqueId()->toString()] = $total + 1;
    }

    /**
     * @param Player $player
     */
    public static function addPlayerKills(Player $player): void {
        $total = 0;

        if (isset(self::$playersKill[$player->getUniqueId()->toString()])) {
            $total = self::$playersKill[$player->getUniqueId()->toString()];
        }

        self::$playersKill[$player->getUniqueId()->toString()] = $total + 1;
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void {
        unset(self::$playersKill[$event->getPlayer()->getUniqueId()->toString()]);
        unset(self::$playersBreak[$event->getPlayer()->getUniqueId()->toString()]);
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onKill(PlayerDeathEvent $event): void {
        $cause = $event->getPlayer()->getLastDamageCause();

        if($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();

            if($damager instanceof Player){
                $session = SessionCore::getManager()->getSession($damager);

                if (!empty($session->quests)) {
                    $quest = $session->quests;

                    $get = QuestType::fromArray($quest['get']);

                    if ($get->getType() === QuestType::TYPE_KILL) {
                        self::addPlayerKills($damager);

                        if (self::getPlayerKills($damager) >= $get->getCount()) {
                            $damager->sendMessage(TextFormat::colorize(TextFormat::GREEN . 'Вы можете завершить квест.'));
                        }
                    }
                }
            }
        }
    }


    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionCore::getManager()->getSession($player);

        if (!empty($session->quests)) {
            $quest = $session->quests;

            $get = QuestType::fromArray($quest['get']);

            if ($get->getType() === QuestType::TYPE_KILL) {
                self::addPlayerKills($player);

                if (self::getPlayerKills($player) >= $get->getCount()) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::GREEN . 'Вы можете завершить квест.'));
                }
            }
        }
    }
}