<?php

declare(strict_types=1);

namespace Quests\manager;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

use Quests\Main;

use Sessions\SessionCore;

use Tools\tool\FloatingTexts\FloatingText;
use Tools\tool\PacketTool;

/**
 * Class PickupQuest
 * @package Quests\manager
 *
 * @version 1.0.0
 * @since   1.0.0
 */
final class PickupQuest {

    private static Vector3 $item_pos_1;
    private static Vector3 $item_pos_2;

    public function __construct() {
        self::$item_pos_1 = new Vector3(-101.5, 68.8, -76.5);
        self::$item_pos_2 = new Vector3(-103.5, 68.8, -76.5);
    }

    /**
     * @param int $type
     * @return array
     */
    public function randomizeQuestByType(int $type): array {
        return [
            'name' => 'test',
            'get' => new QuestType(QuestType::TYPE_ITEM, Item::get(Item::STONE, 0, 16)->jsonSerialize(), 16),
            'reward' => new QuestReward(QuestReward::TYPE_ITEM, Item::get(Item::LEATHER_BOOTS)->jsonSerialize(), 16)
        ]; //todo
    }

    /**
     * @param Player $player
     */
    public function getQuest(Player $player): void {
        $session = SessionCore::getManager()->getSession($player);

        if (!empty($session->quests)) {
            $quest = $session->quests;

            $save = false;
        } else {
            $quest = $this->randomizeQuestByType(0);

            $save = true;
        }

        /** @var QuestType $get */
        $get = $quest['get'];
        $getItemData = $get->getData();

        if (is_array($getItemData) and $get->getType() === QuestType::TYPE_ITEM) {
            $getItem = Item::jsonDeserialize($getItemData);

            $name = 'Принеси';
        } else {
            if ($get->getType() === QuestType::TYPE_BREAK) {
                $getItem = Item::get(Item::BRICK);

                $name = 'Сломай';
            } else if ($get->getType() === QuestType::TYPE_KILL) {
                $getItem = Item::get(Item::IRON_SWORD);

                $name = 'Убей';
            } else {
                $getItem = Item::get(Item::AIR);

                $name = 'Ошибка';
            }
        }

        $eidList = [];

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = PacketTool::createItemActorPacket($getItem, self::$item_pos_1, $eid);
        $packet->metadata = [Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, (1 << Entity::DATA_FLAG_IMMOBILE)]];

        $player->dataPacket($packet);

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = FloatingText::createPacket((self::$item_pos_1)->add(0.0, 0.6), $eid, TextFormat::GRAY . "x". $get->getCount());
        $player->dataPacket($packet);

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = FloatingText::createPacket((self::$item_pos_1)->add(0.0, 1.2), $eid, $name);
        $player->dataPacket($packet);

        /** @var QuestReward $reward */
        $reward = $quest['reward'];
        $rewardItemData = $reward->getData();

        if (is_array($rewardItemData) and $reward->getType() === QuestReward::TYPE_ITEM) {
            $rewardItem = Item::jsonDeserialize($rewardItemData);
        } else {
            $rewardItem = Item::get(Item::EMERALD);
        }

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = PacketTool::createItemActorPacket($rewardItem, self::$item_pos_2, $eid);
        $packet->metadata = [Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, (1 << Entity::DATA_FLAG_IMMOBILE)]];

        $player->dataPacket($packet);

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = FloatingText::createPacket((self::$item_pos_2)->add(0.0, 0.6), $eid, TextFormat::GRAY . "x". $reward->getCount());
        $player->dataPacket($packet);

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = FloatingText::createPacket((self::$item_pos_2)->add(0.0, 1.2), $eid, "Получи");
        $player->dataPacket($packet);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $eidList): void {
            if ($player->isOnline()) {
                foreach ($eidList as $id) {
                    $packet = PacketTool::createRemovePacket($id);

                    $player->dataPacket($packet);
                }
            }
        }), 20 * 10);

        if ($save) {
            $session->quests = $quest;
            $session->save();
        }
    }
}