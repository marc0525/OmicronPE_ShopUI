<?php

namespace GuiShop;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\{Item, ItemBlock};
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\event\server\DataPacketReceiveEvent;
use GuiShop\Modals\elements\{Dropdown, Input, Button, Label, Slider, StepSlider, Toggle};
use GuiShop\Modals\network\{GuiDataPickItemPacket, ModalFormRequestPacket, ModalFormResponsePacket, ServerSettingsRequestPacket, ServerSettingsResponsePacket};
use GuiShop\Modals\windows\{CustomForm, ModalWindow, SimpleForm};
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender, CommandExecutor};

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {
  public $shop;
  public $item;

  //documentation for setting up the items
  /*
  "Item name" => [item_id, item_damage, buy_price, sell_price]
  */
public $Blocks = [
    "ICON" => ["Blocks",2,0],
    "Oak Wood" => [17,0,30,15],
    "Birch Wood" => [17,2,30,15],
    "Spruce Wood" => [17,1,30,15],
    "Dark Oak Wood" => [162,1,30,15],
	"Cobblestone" => [4,0,10,5],
	"Obsidian" => [49,0,500,250],
	"Bedrock" => [7,0,1500,500],
	"Sand " => [12,0,15,7],
    "Sandstone " => [24,0,15,7],
	"Nether Rack" => [87,0,15,7],
    "Glass" => [20,0,50,25],
    "Glowstone" => [89,0,100,50],
    "Sea Lantern" => [169,0,100,50],
	"Grass" => [2,0,20,10],
	"Dirt" => [3,0,10, 5],
    "Stone" => [1,0,20,10],
    "Planks" => [5,0,20,10],
    "Prismarine" => [168,0,30,20],
    "End Stone" => [121,0,30,20],
    "Emerald Block" => [133,0,100,50],
    "Diamond Block" => [57,0,101,51],
    "Glass" => [20,0,50,30],
    "Iron Block" => [42,0,50,30],
    "Gold Block" => [41,0,50,30],
    "Purpur Blocks" => [201,0,50,30],
    "Quartz Block" => [155,0,100,30]
  ];

  public $Ores = [
    "ICON" => ["Ores",266,0],
    "Coal" => [263,0,100,50],
    "Iron Ingot" => [265,0,200,100],
    "Gold Ingot" => [266,0,300,150],
    "Diamond" => [264,0,500,250],
    "Lapis" => [351,4,500,250]
  ];

  public $Tools = [
    "ICON" => ["Tools",278,0],
    "Diamond Pickaxe" => [278,0,500,250],
    "Diamond Shovel" => [277,0,500,250],
    "Diamond Axe" => [279,0,500,250],
    "Diamond Hoe" => [293,0,500,250],
    "Diamond Sword" => [276,0,750,375],
    "Bow" => [261,0,400,200],
    "Arrow" => [262,0,25,5]
  ];

  public $Armor = [
    "ICON" => ["Armor",311,0],
    "Diamond Helmet" => [310,0,1000,500],
    "Diamond Chestplate" => [311,0,2500,1250],
    "Diamond Leggings" => [312,0,1500,750],
    "Diamond Boots" => [313,0,1000,500]
  ];

  public $Farming = [
    "ICON" => ["Farming",293,0],
    "Pumpkin" => [86,0,50,25],
    "Melon" => [360,13,50,25],
    "Carrot" => [391,0,80,40],
    "Potato" => [392,0,80,40],
    "Sugarcane" => [338,0,80,40],
    "Wheat" => [296,6,80,40],
    "Pumpkin Seed" => [361,0,20,10],
    "Melon Seed" => [362,0,20,10],
    "Seed" => [295,0,20,10]
  ];

  public $Food = [
    "ICON" => ["Food",364,0],
	"Cooked Chicken" => [366,0,10,5],
    "Steak" => [364,0,10,5],
    "Golden Apple" => [322,0,500,100],
    "Enchanted Golden Apple" => [466,0,1000,100]
  ];

  public $Miscellaneous = [
    "ICON" => ["Miscellaneous",368,0],
	"PVP Elixir" => [373,101,35000,500],
	"Raiding Elixir" => [373,100,10000,500],
	"Furnace" => [61,0,20,10],
    "Crafting Table" => [58,0,20,10],
	"Ender Chest " => [130,0,1000,500],
    "Enderpearl" => [368,0,1000,500],
    "Bone" => [352,0,50,25],
    "Book & Quill" => [386,0,100,0],
    "Elytra" => [444,0,1000,500],
    "Boats" => [333,0,1000,500],
    "Totem of Undying" => [450,0,1000,500]
  ];

  public $Raiding = [
    "ICON" => ["Raiding",46,0],
    "Flint & Steel" => [259,0,100,50],
    "Torch" => [50,0,5,2],
	"Packed Ice " => [174,0,500,250],
    "Water" => [9,0,50,10],
    "Lava" => [10,0,50,10],
    "Redstone" => [331,0,50,25],
    "Chest" => [54,0,100,50],
    "TNT" => [46,0,10000,500]
  ];
	
  public $Mobs = [
    "ICON" => ["Mobs",52,0],
    "Blaze" => [383,43,50000,1000],
    "Stray" => [383,46,50000,1000],
    "Skeleton" => [383,34,50000,1000],
    "Zombie" => [383,32,50000,1000],
    "Husk" => [383,47,50000,1000],
    "Zombie_Pigman" => [383,36,50000,1000],
    "Creeper" => [383,33,50000,1000],
    "Mob Spawner" => [52,0,55000,2000]
  ];

  public $Potions = [
    "ICON" => ["Potions",373,0],
    "Strength" => [373,33,1000,100],
    "Regeneration" => [373,28,1000,100],
    "Speed" => [373,16,1000,500],
    "Fire Resistance" => [373,13,1000,100],
    "Poison (SPLASH)" => [438,27,1000,100],
    "Weakness (SPLASH)" => [438,35,1000,100],
    "Slowness (SPLASH)" => [438,17,1000,100]
  ];

  public $Skulls = [
    "ICON" => ["Skulls",397,0],
    "Zombie Skull" => [397,2,500,50],
    "Wither Skull" => [397,1,500,50],
    "Skin Head" => [397,3,50,10],
    "Creeper Skull" => [397,4,500,50],
    "Dragon Skull" => [397,5,1000,60],
    "Skeleton Skull" => [397,0,500,50]
  ];
	
  public $MobDrop = [
    "ICON" => ["MobDrop",369,0],
    "Blaze Rod" => [369,0,500,50],
    "Gold Nuggets" => [371,0,500,10],
    "Rotten Flesh" => [367,0,500,50],
    "GunPowder" => [289,0,500,50]
  ];

  public $Spawners = [
    "ICON" => ["Spawners",52,0]
    "Monster Spawner" => [52,0,50000,40000],
    "Spawn Chicken" => [383,10,100000,80000],
    "Spawn Sheep" => [383,13,120000,100000],
    "Spawn Pig" => [383,12,110000,90000],
    "Spawn Zombie" => [383,32,150000,130000],
    "Spawn Skeleton" => [383,34,170000,150000]
  ];
	
  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    PacketPool::registerPacket(new GuiDataPickItemPacket());
		PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
    $this->item = [$this->MobDrop, $this->Skulls, $this->Potions, $this->Mobs, $this->Raiding, $this->Farming, $this->Armor, $this->Tools, $this->Food, $this->Ores, $this->Blocks, $this->Miscellaneous];
  }

  public function sendMainShop(Player $player){
    $ui = new SimpleForm("§bOmicron§4PE §cShop","       §aPurchase and Sell items Here!");
    foreach($this->item as $category){
      if(isset($category["ICON"])){
        $rawitemdata = $category["ICON"];
        $button = new Button($rawitemdata[0]);
        $button->addImage('url', "omicronpe.buycraft.net".$rawitemdata[1]."-".$rawitemdata[2].".png");
        $ui->addButton($button);
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 110;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendShop(Player $player, $id){
    $ui = new SimpleForm("§bOmicron§4PE §cShop","       §aPurchase and Sell items Here!");
    $ids = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $id){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            $button = new Button($name);
            $button->addImage('url', "omicronpe.buycraft.net".$item[0]."-".$item[1].".png");
            $ui->addButton($button);
          }
        }
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 111;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendConfirm(Player $player, $id){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $id){
              $this->item[$player->getName()] = $id;
              $iname = $name;
              $cost = $item[2];
              $sell = $item[3];
              break;
            }
          }
          $idi++;
        }
      }
    }

    $ui = new CustomForm($iname);
    $slider = new Slider("§dAmount ",1,500,0);
    $toggle = new Toggle("§5Selling");
    if($sell == 0) $sell = "0";
    $label = new Label(TF::GREEN."Buy: $".TF::GREEN.$cost.TF::RED."\nSell: $".TF::RED.$sell);
    $ui->addElement($label);
    $ui->addElement($toggle);
    $ui->addElement($slider);
    $pk = new ModalFormRequestPacket();
    $pk->formId = 112;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sell(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if($sell == 0){
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§cThis is not sellable!");
                return true;
              }
              if($player->getInventory()->contains(Item::get($id,$damage,$amount))){
                $player->getInventory()->removeItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->addMoney($player, $sell);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§bYou have sold §3$amount $iname §bfor §3$$sell");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§2You do not have §5$amount $iname!");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function purchase(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if(EconomyAPI::getInstance()->myMoney($player) > $cost){
                $player->getInventory()->addItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->reduceMoney($player, $cost);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§bYou purchased §3$amount $iname §bfor §3$$cost");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§2You do not have enough money to buy §5$amount $iname");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function DataPacketReceiveEvent(DataPacketReceiveEvent $event){
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    if($packet instanceof ModalFormResponsePacket){
      $id = $packet->formId;
      $data = $packet->formData;
      $data = json_decode($data);
      if($data === Null) return true;
      if($id === 110){
        $this->shop[$player->getName()] = $data;
        $this->sendShop($player, $data);
        return true;
      }
      if($id === 111){
        //$this->shop[$player->getName()] = $data;
        $this->sendConfirm($player, $data);
        return true;
      }
      if($id === 112){
        $selling = $data[1];
        $amount = $data[2];
        if($selling){
          $this->sell($player, $data, $amount);
          return true;
        }
        $this->purchase($player, $data, $amount);
        return true;
      }
    }
    return true;
  }

  public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool{
    switch(strtolower($command)){
      case "shop":
        $this->sendMainShop($player);
        return true;
    }
  }

}
