<?php

declare(strict_types=1);

namespace Quests\manager;

use onebone\economyapi\EconomyAPI;
use pocketmine\entity\Entity;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

use Quests\listener\EventListener;
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
        $type = mt_rand(1, 3);

        switch ($type) {
            case QuestType::TYPE_ITEM:
                $questId = mt_rand(1, 5);

                switch ($questId) {
                    case 1:
                        return [
                            'name' => 'Принеси &aБулыжник &7x&f64 &fи получи &aЖелезную кирку &7x&f1',
                            'get' => new QuestType(QuestType::TYPE_ITEM, Item::get(Item::COBBLESTONE, 0, 64)->jsonSerialize(), 64),
                            'reward' => new QuestReward(QuestReward::TYPE_ITEM, Item::get(Item::IRON_PICKAXE)->jsonSerialize(), 1)
                        ];
                    case 2:
                        return [
                            'name' => 'Принеси &aДубовое бревно &7x&f64 &fи получи &a100$',
                            'get' => new QuestType(QuestType::TYPE_ITEM, Item::get(Item::WOOD, 0, 64)->jsonSerialize(), 64),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 100, 100)
                        ];
                    case 3:
                        return [
                            'name' => 'Принеси &aСаженец дуба &7x&f32 &fи получи &aАлмазный топор &7x&f1',
                            'get' => new QuestType(QuestType::TYPE_ITEM, Item::get(Item::SAPLING, 0, 32)->jsonSerialize(), 32),
                            'reward' => new QuestReward(QuestReward::TYPE_ITEM, Item::get(Item::DIAMOND_AXE)->jsonSerialize(), 1)
                        ];
                    case 4:
                        return [
                            'name' => 'Принеси &aСундук &7x&f16 &fи получи &a75&2$',
                            'get' => new QuestType(QuestType::TYPE_ITEM, Item::get(Item::CHEST, 0, 16)->jsonSerialize(), 16),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 75, 75)
                        ];
                    case 5:
                        return [
                            'name' => 'Принеси &aСтекло &7x&f64 &fи получи &a150&2$',
                            'get' => new QuestType(QuestType::TYPE_ITEM, Item::get(Item::GLASS, 0, 64)->jsonSerialize(), 64),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 150, 150)
                        ];
                }
                break;
            case QuestType::TYPE_BREAK:
                $questId = mt_rand(1, 5);

                switch ($questId) {
                    case 1:
                        return [
                            'name' => 'Сломай &b150 &fблоков &fи получи &a10&2$',
                            'get' => new QuestType(QuestType::TYPE_BREAK, 150, 150),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 10, 10)
                        ];
                    case 2:
                        return [
                            'name' => 'Сломай &b500 &fблоков &fи получи &a30&2$',
                            'get' => new QuestType(QuestType::TYPE_BREAK, 500, 500),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 30, 30)
                        ];
                    case 3:
                        return [
                            'name' => 'Сломай &b1000 &fблоков &fи получи &a75&2$',
                            'get' => new QuestType(QuestType::TYPE_BREAK, 1000, 1000),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 75, 75)
                        ];
                    case 4:
                        return [
                            'name' => 'Сломай &b1500 &fблоков &fи получи &a100&2$',
                            'get' => new QuestType(QuestType::TYPE_BREAK, 1500, 1500),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 100, 100)
                        ];
                    case 5:
                        return [
                            'name' => 'Сломай &b2000 &fблоков &fи получи &a200&2$',
                            'get' => new QuestType(QuestType::TYPE_BREAK, 2000, 2000),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 200, 200)
                        ];
                }
                break;
            case QuestType::TYPE_KILL:
                $questId = mt_rand(1, 3);

                switch ($questId) {
                    case 1:
                        return [
                            'name' => 'Убей &b3 &fигрока &fи получи &a50&2$',
                            'get' => new QuestType(QuestType::TYPE_KILL, 3, 3),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 50, 50)
                        ];
                    case 2:
                        return [
                            'name' => 'Убей &b6 &fигроков &fи получи &a110&2$',
                            'get' => new QuestType(QuestType::TYPE_KILL, 6, 6),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 110, 110)
                        ];
                    case 3:
                        return [
                            'name' => 'Убей &b10 &fигроков &fи получи &a150&2$',
                            'get' => new QuestType(QuestType::TYPE_KILL, 10, 10),
                            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 150, 150)
                        ];
                }
        }

        return [
            'name' => 'Ошибка',
            'get' => new QuestType(QuestType::TYPE_KILL, 0, 0),
            'reward' => new QuestReward(QuestReward::TYPE_MONEY, 0, 0)
        ];
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

            $inv = $player->getInventory();
            if (is_array($getItemData) and $get->getType() === QuestType::TYPE_ITEM) {
                $getItem = Item::jsonDeserialize($getItemData);
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
                        } else if ($reward->getType() === QuestReward::TYPE_MONEY) {
                            EconomyAPI::getInstance()->addMoney($player, $rewardItemData);
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
            } else if ($get->getType() === QuestType::TYPE_BREAK) {
                if (EventListener::getPlayerBreak($player) >= $getItemData) {
                    $rewardItemData = $reward->getData();

                    if (is_array($rewardItemData) and $reward->getType() === QuestReward::TYPE_ITEM) {
                        $rewardItem = Item::jsonDeserialize($rewardItemData);

                        if ($inv->canAddItem($rewardItem)) {
                            $inv->addItem($rewardItem);
                        } else {
                            $player->getLevel()->dropItem($player, $rewardItem);
                        }
                    } else if ($reward->getType() === QuestReward::TYPE_MONEY) {
                        EconomyAPI::getInstance()->addMoney($player, $rewardItemData);
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
            } else if ($get->getType() === QuestType::TYPE_KILL) {
                var_dump($getItemData);
                if (EventListener::getPlayerKills($player) >= $getItemData) {
                    $rewardItemData = $reward->getData();

                    if (is_array($rewardItemData) and $reward->getType() === QuestReward::TYPE_ITEM) {
                        $rewardItem = Item::jsonDeserialize($rewardItemData);

                        if ($inv->canAddItem($rewardItem)) {
                            $inv->addItem($rewardItem);
                        } else {
                            $player->getLevel()->dropItem($player, $rewardItem);
                        }
                    } else if ($reward->getType() === QuestReward::TYPE_MONEY) {
                        EconomyAPI::getInstance()->addMoney($player, $rewardItemData);
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
        $get = $quest['get'];

        if ($get instanceof QuestType) {
            $getItemData = $get->getData();
            $type = $get->getType();
            $count = $get->getCount();
        } else {
            $getItemData = $get['data'];
            $type = $get['type'];
            $count = $get['count'];
        }

        if (is_array($getItemData) and $type === QuestType::TYPE_ITEM) {
            $getItem = Item::jsonDeserialize($getItemData);

            $name = 'Принеси';
        } else {
            if ($type === QuestType::TYPE_BREAK) {
                $getItem = Item::get(Item::BRICK);

                $name = 'Сломай';
            } else if ($type === QuestType::TYPE_KILL) {
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

        $packet = FloatingText::createPacket((self::$item_pos_1)->add(0.0, 0.6), $eid, TextFormat::GRAY . "x" . $count);
        $player->dataPacket($packet);

        $eid = Entity::$entityCount++;
        $eidList[] = $eid;

        $packet = FloatingText::createPacket((self::$item_pos_1)->add(0.0, 1.2), $eid, $name);
        $player->dataPacket($packet);

        $reward = $quest['reward'];

        if ($reward instanceof QuestReward) {
            $rewardItemData = $reward->getData();
            $type = $reward->getType();
            $count = $reward->getCount();
        } else {
            $rewardItemData = $reward['data'];
            $type = $reward['type'];
            $count = $reward['count'];
        }

        if (is_array($rewardItemData) and $type === QuestReward::TYPE_ITEM) {
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

        $packet = FloatingText::createPacket((self::$item_pos_2)->add(0.0, 0.6), $eid, TextFormat::GRAY . "x" . $count);
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