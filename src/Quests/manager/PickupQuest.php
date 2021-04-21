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

    private array $players = [];
    private array $playerEid = [];

    public function __construct() {
        self::$item_pos_1 = new Vector3(-101.5, 69, -76.5);
        self::$item_pos_2 = new Vector3(-103.5, 69, -76.5);
    }

    /**
     * @return array
     */
    public function randomizeQuest(): array {
        $type = mt_rand(1, 1);

        switch ($type) {
            case QuestType::TYPE_ITEM:
                $questId = mt_rand(1, 1);

                switch ($questId) {
                    case 1:
                        return [
                            'name' => 'Принеси &aБулыжник &7x&f64 &fи получи &aЖелезную кирку &7x&f1',
                            'get' => new QuestType(QuestType::TYPE_ITEM, Item::get(Item::COBBLESTONE, 0, 64)->jsonSerialize(), 64),
                            'reward' => new QuestReward(QuestReward::TYPE_ITEM, Item::get(Item::IRON_PICKAXE)->jsonSerialize(), 1)
                        ];
                }
        }
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

            $get = QuestType::fromArray($quest['get']);
            $reward = QuestReward::fromArray($quest['reward']);

            $save = false;

            $getItemData = $get->getData();

            if (is_array($getItemData) and $get->getType() === QuestType::TYPE_ITEM) {
                $getItem = Item::jsonDeserialize($getItemData);
                $inv = $player->getInventory();
                $hand = $inv->getItemInHand();

                if ($hand->equals($getItem)) {
                    if ($hand->getCount() >= $getItem->getCount()) {
                        $newCount = $hand->getCount() - $getItem->getCount();

                        if ($newCount > 0) {
                            $inv->setItemInHand(Item::get($hand->getId(), $hand->getDamage(), $newCount));
                        }

                        $rewardItemData = $reward->getData();

                        if (is_array($rewardItemData) and $reward->getType() === QuestReward::TYPE_ITEM) {
                            $rewardItem = Item::jsonDeserialize($rewardItemData);

                            if ($inv->canAddItem($rewardItem)) {
                                $inv->addItem($rewardItem);
                            } else {
                                $player->getLevel()->dropItem($player, $rewardItem);
                            }
                        }

                        $player->sendMessage(TextFormat::GREEN . 'Квест выполнен! Награда была выдана.');

                        $session->quests = [];
                        $session->save();

                        unset($this->players[$player->getUniqueId()->toString()]);

                        if (isset($this->playerEid[$player->getUniqueId()->toString()])) {
                            $this->forceClose($player, $this->playerEid[$player->getUniqueId()->toString()]);
                        }
                        return;
                    }
                }
            }
        } else {
            $quest = $this->randomizeQuest();

            $save = true;
        }

        if (!isset($this->players[$player->getUniqueId()->toString()])) {
            $this->players[$player->getUniqueId()->toString()] = true;

            $this->createHolo($player, $quest);
        }

        if ($save) {
            /** @var QuestType $saveGet */
            $saveGet = $quest['get'];

            $quest['get'] = $saveGet->toArray();

            /** @var QuestReward $saveGet */
            $saveReward = $quest['reward'];

            $quest['reward'] = $saveReward->toArray();

            $session->quests = $quest;
            $session->save();

            $player->sendMessage(TextFormat::GREEN . 'Вы взяли новый квест!');
        } else {
            $player->sendMessage(TextFormat::AQUA . 'Информация о текущем квесте:');
        }

        $player->sendMessage(TextFormat::colorize($quest['name']));
    }

    /**
     * @param Player $player
     * @param array $quest
     */
    private function createHolo(Player $player, array $quest): void {
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

        $packet = FloatingText::createPacket((self::$item_pos_1)->add(0.0, 0.6), $eid, TextFormat::GRAY . "x" . $get->getCount());
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

        $packet = FloatingText::createPacket((self::$item_pos_2)->add(0.0, 0.6), $eid, TextFormat::GRAY . "x" . $reward->getCount());
        $player->dataPacket($packet);

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = FloatingText::createPacket((self::$item_pos_2)->add(0.0, 1.2), $eid, "Получи");
        $player->dataPacket($packet);

        $this->playerEid[$player->getUniqueId()->toString()] = $eidList;

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $eidList): void {
            $this->forceClose($player, $eidList);
        }), 20 * 10);
    }

    /**
     * @param Player $player
     * @param array $eidList
     */
    private function forceClose(Player $player, array $eidList): void {
        if ($player->isOnline()) {
            foreach ($eidList as $id) {
                $packet = PacketTool::createRemovePacket($id);

                $player->dataPacket($packet);
            }

            unset($this->playerEid[$player->getUniqueId()->toString()]);
        }
    }
}