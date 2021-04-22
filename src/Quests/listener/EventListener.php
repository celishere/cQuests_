<?php

declare(strict_types=1);

namespace Quests\listener;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\Player;

use Quests\Main;

/**
 * Class EventListener
 * @package Quests\listener
 *
 * @version 1.0.0
 * @since   1.0.0
 */
class EventListener implements Listener {

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
}