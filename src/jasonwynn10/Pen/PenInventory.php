<?php
declare(strict_types=1);
namespace jasonwynn10\Pen;

use pocketmine\inventory\ContainerInventory;
use pocketmine\network\mcpe\protocol\types\WindowTypes;

class PenInventory extends ContainerInventory {
	public function getNetworkType() : int {
		return WindowTypes::CONTAINER;
	}
	public function getName() : string {
		return "Pen";
	}
	public function getDefaultSize() : int {
		return 36;
	}
}