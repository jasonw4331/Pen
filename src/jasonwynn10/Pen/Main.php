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

use pocketmine\event\Listener;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ZippedResourcePack;

class Main extends PluginBase implements Listener {
	/** @var ZippedResourcePack $pack **/
	private $pack;

	public function onLoad() {
		$this->saveResource("PenPlugin.mcpack");
		$manager = $this->getServer()->getResourcePackManager();
		$this->pack = $pack = new ZippedResourcePack($this->getDataFolder()."PenPlugin.mcpack");

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
		Item::addCreativeItem(ItemFactory::get(ItemIds::SADDLE));
		$manager = $this->getServer()->getCraftingManager();
		$manager->registerShapedRecipe(
			new ShapedRecipe(
				["A","A","B"],
				["A" => ItemFactory::get(ItemIds::IRON_INGOT), "B" => ItemFactory::get(ItemIds::DYE, 0)],
				[ItemFactory::get(ItemIds::SADDLE)->setLore(["Writes Enchantments, Lore, and Names onto items"])])
		);
		$manager->registerShapedRecipe(
			new ShapedRecipe(
				[" A"," A"," B"],
				["A" => ItemFactory::get(ItemIds::IRON_INGOT), "B" => ItemFactory::get(ItemIds::DYE, 0)],
				[ItemFactory::get(ItemIds::SADDLE)->setLore(["Writes Enchantments, Lore, and Names onto items"])])
		);
		$manager->registerShapedRecipe(
			new ShapedRecipe(
				["  A","  A","  B"],
				["A" => ItemFactory::get(ItemIds::IRON_INGOT), "B" => ItemFactory::get(ItemIds::DYE, 0)],
				[ItemFactory::get(ItemIds::SADDLE)->setLore(["Writes Enchantments, Lore, and Names onto items"])])
		);
	}

	public function onDisable() {
		$manager = $this->getServer()->getResourcePackManager();
		$pack = $this->pack;

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
		unlink($this->getDataFolder()."PenPlugin.mcpack");
	}
}