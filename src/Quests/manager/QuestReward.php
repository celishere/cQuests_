<?php

declare(strict_types=1);

namespace Quests\manager;

/**
 * Class QuestReward
 * @package Quests\manager
 *
 * @version 1.0.0
 * @since   1.0.0
 */
final class QuestReward {

    public const TYPE_ITEM = 1;
    public const TYPE_MONEY = 2;

    private int $type;
    private int $count;

    /**
     * @var int|string|array|int[]
     */
    private $data;

    /**
     * QuestReward constructor.
     * @param int $type
     * @param int|static|array|int[] $data
     */
    public function __construct(int $type, $data, int $count) {
        $this->type = $type;
        $this->data = $data;
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getCount(): int {
        return $this->count;
    }

    /**
     * @return $this|array|int|int[]
     */
    public function getData() {
        return $this->data;
    }
}