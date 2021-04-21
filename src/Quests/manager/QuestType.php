<?php

declare(strict_types=1);

namespace Quests\manager;

/**
 * Class QuestType
 * @package Quests\manager
 *
 * @version 1.0.0
 * @since   1.0.0
 */
final class QuestType {

    public const TYPE_ITEM = 1;
    public const TYPE_BREAK = 2;
    public const TYPE_KILL = 3;

    private int $type;
    private int $count;

    /**
     * @var int|string|array|int[]
     */
    private $data;

    /**
     * QuestType constructor.
     * @param int $type
     * @param int|string|array|int[] $data
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
     * @return array|int|int[]|string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return ['type' => $this->getType(), 'count' => $this->getCount(), 'data' => $this->data];
    }

    /**
     * @return $this
     */
    public static function fromArray(array $data): self {
        return new QuestType($data['type'], $data['data'], $data['count']);
    }
}