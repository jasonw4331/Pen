<?php
declare(strict_types=1);
namespace jasonwynn10\Pen;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
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
		$this->setCustomName("Pen");
		$this->setLore([""]);
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
			$form = new SimpleForm(function(Player $player, $data) {
				var_dump($data);
				if($data === null)
					return;
				$form = new SimpleForm(function(Player $player, $data) {
					var_dump($data);
					if($data === null)
						return;
					if($data == 0) {
						$form = new CustomForm(function(Player $player, $data) {
							var_dump($data);
							if($data === null)
								return;
							// TODO: do not add if no input
							// TODO: roman numeral converter
							$this->applyDamage(1);
						});
						$form->setTitle("Enchantments");
						$i = 1;
						foreach($this->getEnchantments() as $enchantment) {
							$form->addInput("Enchantment Slot ".$i++, "", $enchantment->getType()->getName());
						}
						$form->addInput("Enchantment Slot ".$i++);
						$form->addInput("Enchantment Slot ".$i++);
						$form->addInput("Enchantment Slot ".$i++);
						$form->addInput("Enchantment Slot ".$i);
						$player->sendForm($form);
					}else{
						$form = new CustomForm(function($data) use ($player) {
							var_dump($data);
							// TODO: do not add if no input
							$this->applyDamage(1);
						});
						$form->setTitle("Lore Text");
						$i = 1;
						foreach($this->getLore() as $line) {
							$form->addInput("Line ".$i++, "", $line);
						}
						$form->addInput("Line ".$i++);
						$form->addInput("Line ".$i++);
						$form->addInput("Line ".$i++);
						$form->addInput("Line ".$i);
						$player->sendForm($form);
					}
				});
				$form->setTitle("Pen Selection");
				$form->addButton("Write Enchantment");
				$form->addButton("Write Lore");
				$player->sendForm($form);
			});
			foreach($player->getInventory()->getContents() as $item) {
				if(!$item instanceof Pen and $item->getCount() > 1) {
					$form->addButton($item->getName());
				}
			}
			$player->sendForm($form);
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