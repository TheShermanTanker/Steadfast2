<?php

namespace pocketmine\entity\monster\walking;

use pocketmine\entity\monster\WalkingMonster;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\entity\Creature;
use pocketmine\Player;
use pocketmine\network\protocol\AnimatePacket;

class IronGolem extends WalkingMonster{
	const NETWORK_ID = 20;

	public $width = 1.9;
	public $height = 2.1;

	public function getSpeed(){
		return 0.8;
	}

	public function initEntity(){
		$this->setMaxHealth(100);
		parent::initEntity();

		$this->setDamage([0, 21, 21, 21]);
		$this->setMinDamage([0, 7, 7, 7]);
	}

	public function getName(){
		return "IronGolem";
	}

	public function attackEntity(Entity $player){
		if($this->attackDelay > 10 && $this->distanceSquared($player) < 4){
			$this->attackDelay = 0;

			$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
			$player->attack($ev->getFinalDamage(), $ev);
			$player->setMotion(new Vector3(0, 0.7, 0));
			$pk = new AnimatePacket();
			$pk->eid = $this->getId();
			$pk->action = AnimatePacket::ACTION_SWING;
			foreach($this->getViewers() as $p){
			    $p->dataPacket($pk);
			}
		}
	}

	public function targetOption(Creature $creature, float $distance){
		return $creature->isAlive() && $distance <= 60;
	}

	public function getDrops(){
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			switch(mt_rand(0, 3)){
				case 0:
					return [Item::get(Item::FEATHER, 0, 1)];
				case 1:
					return [Item::get(Item::CARROT, 0, 1)];
				case 2:
					return [Item::get(Item::POTATO, 0, 1)];
				case 3:
				    return [Item::get(Item::IRON_BLOCK, 0, 1)];
			}
		}
		return [];
	}

}
