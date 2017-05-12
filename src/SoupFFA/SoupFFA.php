<?php

namespace SoupFFA;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\command\CommandMap;
use pocketmine\utils\{Textformat as C, Config};
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\tile\Sign;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class SoupFFA extends PluginBase implements Listener{
	
	public $prefix = C::GRAY."[".C::GREEN."SoupFFA".C::GRAY."]";

	public function onEnable(){
		$this->getLogger()->info($this->prefix . " by McpeBooster!");
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		@mkdir($this->getDataFolder());
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);    
		if(empty($config->get("arena"))) {
			$config->set("arena", "debug123");
			$config->save();
		}
		if($config->get("arena") == "debug123"){
			$plugin = $this->getServer()->getPluginManager()->getPlugin("SoupFFA");
			$this->getLogger()->emergency("###############################################");
			$this->getLogger()->emergency(" Please change the SoupFFA world in the config.yml!!!");
			$this->getLogger()->emergency("###############################################");
			$this->getServer()->getPluginManager()->disablePlugin($plugin);
		}
		
		$this->getServer()->loadLevel($config->get("arena"));
		
	}
	
	#Events
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$item = $event->getItem();
		$block = $event->getBlock();
		if($item->getId() === Item::MUSHROOM_STEW){
			$player->getInventory()->removeItem($item);
			$player->setHealth($player->getHealth() + 5);
			$player->sendTip("§cYou are healed!");
			return;
		}elseif($player->getLevel()->getTile($block) instanceof Sign) {
			$tile = $player->getLevel()->getTile($block);
			$text = $tile->getText();
			if ($text[0] == $this->prefix) {
				if($text[2] == "§2Join"){
					$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);    
					$arenaname = $config->get("arena");
					$arenalevel = $this->getServer()->getLevelByName($arenaname);
					$arenaspawn = $arenalevel->getSafeSpawn();
					$player->teleport($arenaspawn, 0, 0);
					$this->SoupItems($player);
					$player->sendMessage( $this->prefix ." you have joined SoupFFA!");
					$player->addTitle("§6|§2SoupFFA§6|", "§8by McpeBooster");
					return;
				}
				else{
					$player->sendMessage( $this->prefix ." §c you can not join SoupFFA!");
					return;
				}
			}
		}
	}
	
	public function onSignCreate(SignChangeEvent $event){
		$player = $event->getPlayer();
		if($event->getLine(0) == "SoupFFA"){
			if($player->isOp()){
				$event->setLine(0, $this->prefix);
				$event->setLine(2, "§2Join");
				$player->sendMessage("JoinSign set!");
				return;
			}
			$player->sendMessage("You do not have the Permission to do that!");
			return;
		}
	}
	
	public function onDamage(EntityDamageEvent $event) {
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$entity = $event->getEntity();
		$cause = $event->getCause();
		
		if ($cause == EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
			if ($event instanceof EntityDamageByEntityEvent) {
				$killer = $event->getDamager();
				$welt = $killer->getLevel()->getFolderName();
				$arenaname = $config->get("arena");
				if($arenaname == $welt){
					if ($killer instanceof Player) {
					$message = $killer->getName();
					$x = $entity->getX();
					$y = $entity->getY();
					$z = $entity->getZ();
					
					$xx = $entity->getLevel()->getSafeSpawn()->getX();
					$yy = $entity->getLevel()->getSafeSpawn()->getY();
					$zz = $entity->getLevel()->getSafeSpawn()->getZ();
					
					if (abs($xx - $x) < 7 && abs($yy - $y) < 7 && abs($zz - $z) < 7){
						
						$event->setCancelled(true);
						
						$killer->sendMessage($this->prefix . "PvP is only allowed further away from the spawn!");
						return;
					}elseif ($event->getDamage() >= $entity->getHealth()) {
						$event->setCancelled(true);
						
						$arenalevel = $this->getServer()->getLevelByName($arenaname);
						$arenaspawn = $arenalevel->getSafeSpawn();
						$entity->teleport($arenaspawn, 0, 0);
						$level->loadChunk($spawn->getX(), $spawn->getZ());
						$entity->teleport($spawn, 0, 0);
						
						$entity->addTitle("§4Death");
						
						$this->SoupItems($entity);
						
						$entity->sendMessage($this->prefix . C::GRAY . "The Player " . C::RED . $killer->getName() . C::GRAY . " has killed you!");
						$killer->sendMessage($this->prefix . C::GRAY . "You have killed " . C::RED . $entity->getName() . C::GRAY . " !");
						return;
						}
					}
				}
			}
		}
	}
	
	
	
	public function SoupItems($player){
		$player->getInventory()->clearAll();
		$slots = array(1,2,3,4,5,6,7,8);
		foreach($slots as $s){
		    $player->getInventory()->setItem($s, Item::get(282));
		}
		$player->getInventory()->setItem(0, Item::get(267));
		$player->setHealth(20);
	}
}