<?php

declare(strict_types=1);

namespace Quests;

use pocketmine\plugin\PluginBase;

use Quests\listener\EventListener;
use Quests\manager\PickupQuest;

/**
 * Class Main
 * @package Quests
 *
 * @version 1.0.0
 * @since   1.0.0
 */
class Main extends PluginBase {

    private static Main $instance;

    private PickupQuest $questManager;

    public function onLoad(): void {
        self::$instance = $this;

        $this->questManager = new PickupQuest();
    }

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main {
        return self::$instance;
    }

    /**
     * @return PickupQuest
     */
    public function getQuestManager(): PickupQuest {
        return $this->questManager;
    }
}