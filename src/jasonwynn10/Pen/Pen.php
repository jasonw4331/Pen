<?php
declare(strict_types=1);
namespace jasonwynn10\Pen;

use pocketmine\block\Block;
use pocketmine\item\Durable;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Pen extends Durable {
	private $maxDurability;

	public function __construct(int $uses) {
		parent::__construct(self::SADDLE, 0, "Pen");
		$this->maxDurability = $uses;
		$this->name = "Pen";
	}

	/**
	 * @param Player $player
	 * @param Block $blockReplace
	 * @param Block $blockClicked
	 * @param int $face
	 * @param Vector3 $clickVector
	 *
	 * @return bool
	 */
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool {
		if($player->hasPermission("pen.use")) {
			// TODO: task delay?
			$inventory = new PenInventory($player, $player->getInventory()->getContents(true), 36, "Item Selection");
			$player->addWindow($inventory);
			$this->applyDamage(1);
		}
		return true;
	}

	/**
	 * Returns the maximum amount of damage this item can take before it breaks.
	 *
	 * @return int
	 */
	public function getMaxDurability() : int {
		return $this->maxDurability;
	}
}