<?php

namespace pocketmine\block;

use pocketmine\block\Block;
use pocketmine\block\Solid;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Dispenser as DispenserTile;
use pocketmine\tile\Tile;

class Dispenser extends Solid {
		
	public function __construct($meta = 0){
		$this->id = self::DISPENSER;
		$this->meta = $meta;
	}
	
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null) {
		// pitch part not so accurate as wanted
		if ($player->pitch > 45) {
			$this->meta = 1;
		} else if ($player->pitch < -45) {
			$this->meta = 0;
		} else {
			if ($player->yaw <= 45 || $player->yaw > 315) {
				$this->meta = 2;
			} else if ($player->yaw > 45 && $player->yaw <= 135) {
				$this->meta = 5;
			} else if ($player->yaw > 135 && $player->yaw <= 225) {
				$this->meta = 3;
			} else {
				$this->meta = 4;
			}
		}
		if (parent::place($item, $block, $target, $face, $fx, $fy, $fz, $player)) {
			$nbt = new Compound("", [
				new Enum("Items", []),
				new StringTag("id", Tile::DISPENSER),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			$nbt->Items->setTagType(NBT::TAG_Compound);
			Tile::createTile(Tile::DISPENSER, $this->level->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			return true;
		}
		return false;
	}
	
	public function onUpdate($type) {
		static $offsets = [
			self::SIDE_UP => [0, 1, 0],
			self::SIDE_DOWN => [0, -1, 0],
			self::SIDE_EAST => [1, 0, 0],
			self::SIDE_WEST => [-1, 0, 0],
			self::SIDE_SOUTH => [0, 0, 1],
			self::SIDE_NORTH => [0, 0, -1],
		];
		$tmpVector = new Vector3();
		foreach ($offsets as $side => $offset) {
			$isShouldBeActivated = false;
			$tmpVector->setComponents($this->x + $offset[0], $this->y + $offset[1], $this->z + $offset[2]);
			$blockId = $this->level->getBlockIdAt($tmpVector->x, $tmpVector->y, $tmpVector->z);
			$isSolidBlock = isset(self::$solid[$blockId]) && self::$solid[$blockId];
			if ($side == self::SIDE_UP) {
				if ($isSolidBlock) {
					$block = $this->level->getBlock($tmpVector);
					$isShouldBeActivated = $block->getPoweredState() != Solid::POWERED_NONE;
				}
			} else {
				switch ($blockId) {
					case self::REDSTONE_WIRE:
						$wire = $this->level->getBlock($tmpVector);
						$isShouldBeActivated = $wire->meta > 0;
						break;
					case self::REDSTONE_TORCH_ACTIVE:
						$isShouldBeActivated = true;
						break;
					case self::WOODEN_BUTTON:
					case self::STONE_BUTTON:
					case self::LEVER:
					case self::WOODEN_PRESSURE_PLATE:
					case self::STONE_PRESSURE_PLATE:
						$backBlock = $this->level->getBlock($tmpVector);
						$isShouldBeActivated = $backBlock->isActive();
						break;
					case self::REDSTONE_REPEATER_BLOCK_ACTIVE:
						$activeRepeater = $this->level->getBlock($backPosition);
						$activeRepeater->getFace();
						break;
					default:
						if ($isSolidBlock) {
							$solidBlock = $this->level->getBlock($tmpVector);
							$isNeedSetBlock = $solidBlock->getPoweredState() != Solid::POWERED_NONE;
						}
						break;
				}
			}
			if ($isShouldBeActivated) {
				if (!$this->isWasActivated()) {
					$this->activate();
					return;
				}
				break;
			}
		}
		if (!$isShouldBeActivated && $this->isWasActivated()) {
			$this->deactivate();
		}
	}
	
	private function isWasActivated() {
		return $this->meta >> 3;
	}
	
	private function activate() {
		$this->meta |= 0x08;
		$this->level->setBlock($this, $this, false, false);
		/** @todo add item shooting */
	}
	
	private function deactivate() {
		$this->meta &= 0x07;
		$this->level->setBlock($this, $this, false, false);
	}
	
	public function canBeActivated() {
		return true;
	}

	public function onActivate(Item $item, Player $player = null) {
		$tile = $this->level->getTile($this);
		if (!($tile instanceof DispenserTile)) {
			$nbt = new Compound("", [
				new Enum("Items", []),
				new StringTag("id", Tile::DISPENSER),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			$nbt->Items->setTagType(NBT::TAG_Compound);
			$tile = Tile::createTile(Tile::DISPENSER, $this->level->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		}
		$player->addWindow($tile->getInventory());
		return true;
	}
}
