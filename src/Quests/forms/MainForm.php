<?php

declare(strict_types=1);

namespace Quests\forms;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\Player;

/**
 * Class MainForm
 * @package Quests\forms
 *
 * @version 1.0.0
 * @since   1.0.0
 */
class MainForm extends SimpleForm {

    /**
     * MainForm constructor.
     */
    public function __construct() {
        $this->addButton(new Button('Test'));
        $this->addButton(new Button('Test'));
        $this->addButton(new Button('Test'));
        $this->addButton(new Button('Test'));

        parent::__construct('Квесты | Главное меню', 'null');
    }

    /**
     * @param Player $player
     * @param Button $button
     * @param int $index
     */
    public function onClickButton(Player $player, Button $button, int $index): void {
        switch ($index) {
            case 1:
                $player->sendMessage('1');
            case 2:
                $player->sendMessage('2');
            case 3:
                $player->sendMessage('3');
            case 4:
                $player->sendMessage('4');
        }
    }
}