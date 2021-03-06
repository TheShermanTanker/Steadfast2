<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\level\generator\normal\biome;

use pocketmine\level\generator\populator\Cactus;
use pocketmine\level\generator\populator\Deadbush;
use pocketmine\level\generator\populator\SugarCane;

class DesertBiome extends SandyBiome{

	public function __construct(){
		parent::__construct();
		$this->setElevation(63, 69);

		$deadbush = new Deadbush();
		$deadbush->setBaseAmount(1);
		$deadbush->setRandomAmount(2);
		
		$cactus = new Cactus();
		$cactus->setBaseAmount(1);
		$cactus->setRandomAmount(2);
		
		$sugarcane = new SugarCane();
		$sugarcane->setRandomAmount(20);
		$sugarcane->setBaseAmount(3);
		
		$this->addPopulator($deadbush);
		$this->addPopulator($cactus);
		$this->addPopulator($sugarcane);

		$this->temperature = 2;
		$this->rainfall = 0;
	}

	public function getName(){
		return "Desert";
	}
}