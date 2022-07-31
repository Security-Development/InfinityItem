<?php

/**
 * @name InfinityItem
 * @author Neo-Developer
 * @main Neo\InfinityItem
 * @version 0.1.0
 * @api 5.0.0
 */

namespace Neo;

use Closure;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Durable;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\scheduler\ClosureTask;

class InfinityItem extends PluginBase {

    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents(new class(($task = $this->getScheduler())) implements Listener { 
            public function __construct(public $task){}

            public function TurnToInfinity(Item $item, Closure $function) : void {
                if( $item instanceof Durable ) {

                    $item->setUnbreakable();
                    call_user_func($function, $item);

                }

            }

            public function onHandle(PlayerItemHeldEvent $event) : void {

                $player = $event->getPlayer();
                $slot = $event->getSlot();

                $this->TurnToInfinity(
                    $event->getItem(),
                    function($item) use($player, $slot) {
                        $player->getInventory()->setItem($slot, $item);
                    }
                );

            }

            public function onInventoryHandle(InventoryTransactionEvent $event) : void {

                $saction = $event->getTransaction();

                foreach($saction->getActions() as $action) {
                    
                    if( $action instanceof SlotChangeAction ) {
                        
                        $inventory = $action->getInventory();

                            $this->TurnToInfinity(
                                $action->getTargetItem(),
                                function($item) use($inventory, $action) {

                                if( $inventory instanceof ArmorInventory ) {

                                    $this->task->scheduleDelayedTask(new ClosureTask(
                                        function() use(&$inventory, $action, $item) : void {
    
                                            $inventory->setItem($action->getSlot(), $item);
    
                                        }
    
                                    ), 1);


                                 }
            
                            }

                        );

                    }

                }

            }

        }, $this);

    }

}
?>