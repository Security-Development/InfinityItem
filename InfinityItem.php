<?php

/**
 * @name InfinityItem
 * @author Neo-Developer
 * @main Neo\InfinityItem
 * @version 0.1.0
 * @api 5.0.0
 */

namespace Neo;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Durable;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;

class InfinityItem extends PluginBase {

    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents(new class(($task = $this->getScheduler())) implements Listener { 
            public function __construct(public TaskScheduler $task){}

            public function TurnToInfinity(Item $item) : Item {
                if( $item instanceof Durable ) {
                    $item->setUnbreakable();
                }
                return $item;

            }

            public function onHandle(PlayerItemHeldEvent $event) : void {
                $player = $event->getPlayer();
                $slot = $event->getSlot();
                $item = $event->getItem();
                if( $item instanceof Durable ) {
                    if( $item->isUnbreakable() ) // Recusive call Error Prevention
                        return;

                        $player->getInventory()->setItem($slot, $this->TurnToInfinity($item));
                }
     
            }

            public function onInventoryHandle(InventoryTransactionEvent $event) : void {

                $saction = $event->getTransaction();

                foreach($saction->getActions() as $action) {
                    
                    if( $action instanceof SlotChangeAction ) {
                        $inventory = $action->getInventory();
                        if( $inventory instanceof ArmorInventory ) {
                            $this->task->scheduleDelayedTask(new ClosureTask(function () use($inventory, $action) :void {
                                $inventory->setItem($action->getSlot(), $this->TurnToInfinity($action->getTargetItem()));
                            }), 1);
                           
                        }

                    }

                }

            }

        }, $this);

    }

}
