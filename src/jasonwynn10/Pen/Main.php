<?php
declare(strict_types=1);
namespace jasonwynn10\Pen;

/*
1. let players sign items with lore using a "pen" item
2. give the pen the ability to write enchantments
3. resource pack to make the saddle item a pen
4. craftable with 2 iron and 1 ink sac
5. UI or sign interface
If I use a UI, then it will be dependant on an API from a virion or other plugin but if I use a sign interface, the plugin must hook on datapackets directly
*/

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ZippedResourcePack;

class Main extends PluginBase implements Listener {
	/** @var array[] $data */
	protected $data = [];
	public function onLoad() {
		$manager = $this->getServer()->getResourcePackManager();
		$pack = new ZippedResourcePack($this->getFile()."resources/PenPlugin.mcpack");

		$reflection = new \ReflectionClass($manager);

		$property = $reflection->getProperty("resourcePacks");
		$property->setAccessible(true);
		$currentResourcePacks = $property->getValue($manager);
		$currentResourcePacks[] = $pack;
		$property->setValue($manager, $currentResourcePacks);

		$property = $reflection->getProperty("uuidList");
		$property->setAccessible(true);
		$currentUUIDPacks = $property->getValue($manager);
		$currentUUIDPacks[strtolower($pack->getPackId())] = $pack;
		$property->setValue($manager, $currentUUIDPacks);

		$property = $reflection->getProperty("serverForceResources");
		$property->setAccessible(true);
		$property->setValue($manager, true);
	}
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$maxUses = (int) $this->getConfig()->get("max-uses", 1);
		ItemFactory::registerItem(new Pen($maxUses), true);
		$this->getServer()->getCraftingManager()->registerShapedRecipe(
			new ShapedRecipe(
				[" A"," A"," B"],
				["A" => ItemFactory::get(ItemIds::IRON_INGOT), "B" => ItemFactory::get(ItemIds::DYE, 0)],
				[ItemFactory::get(ItemIds::SADDLE)]) // TODO: result lore
		);
	}
	public function onDisable() {
		$manager = $this->getServer()->getResourcePackManager();
		$pack = new ZippedResourcePack($this->getFile()."resources/PenPlugin.mcpack");

		$reflection = new \ReflectionClass($manager);

		$property = $reflection->getProperty("resourcePacks");
		$property->setAccessible(true);
		$currentResourcePacks = $property->getValue($manager);
		$key = array_search($pack, $currentResourcePacks);
		if($key !== false){
			unset($currentResourcePacks[$key]);
			$property->setValue($manager, $currentResourcePacks);
		}

		$property = $reflection->getProperty("uuidList");
		$property->setAccessible(true);
		$currentUUIDPacks = $property->getValue($manager);
		if(isset($currentResourcePacks[strtolower($pack->getPackId())])) {
			unset($currentUUIDPacks[strtolower($pack->getPackId())]);
			$property->setValue($manager, $currentUUIDPacks);
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function onTransaction(InventoryTransactionEvent $event) {
		$rewrite = null;
		$slot = null;
		$inventory = null;
		foreach($event->getTransaction()->getActions() as $action) {
			if($action instanceof SlotChangeAction and $action->getInventory() instanceof PenInventory) {
				$rewrite = clone $action->getSourceItem();
				$slot = $action->getSlot();
				$inventory = $action->getInventory();
				break;
			}
		}
		if($rewrite !== null and $slot !== null) {
			$event->getTransaction()->getSource()->removeWindow($inventory, true);
			$this->data[$event->getTransaction()->getSource()->getName()] = [$rewrite, $slot];
		}
	}

	/**
	 * @param InventoryCloseEvent $event
	 */
	public function onInvClose(InventoryCloseEvent $event) {
		if($event->getInventory() instanceof PenInventory) {
			$player = $event->getPlayer();
			$form = new SimpleForm(function($data){
				var_dump($data);
				/*
				$pk = new InventoryTransactionPacket();
				$pk->transactionType = InventoryTransactionPacket::TYPE_USE_ITEM;
				$pk->actions = [];
				$pk->trData = new \stdClass();
				$pk->trData->actionType = InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_BLOCK;
				$pk->trData->x = $player->getFloorX();
				$pk->trData->y = $player->getFloorY() + ceil($player->height);
				$pk->trData->z = $player->getFloorZ();
				$pk->trData->face = Vector3::SIDE_UP;
				$pk->trData->hotbarSlot = $player->getInventory()->getHeldItemIndex();
				$pk->trData->itemInHand = ItemFactory::get(ItemIds::SIGN);
				$pk->trData->playerPos = $event->getPlayer()->asVector3();
				$pk->trData->clickPos = new Vector3(); // different from x/y/z
				$player->sendDataPacket($pk);
				*/
			});
			$form->setTitle("Pen Selection");
			$form->addButton("Write Enchantment");
			$form->addButton("Write Lore");
			$player->sendForm($form);
		}
	}

	public function onPacket(DataPacketReceiveEvent $event) {
		if($event->getPacket() instanceof InventoryTransactionPacket) {
			//var_dump($event->getPacket());
			echo ":P\n";
		}
	}
}