<?php

namespace SuperXingKong;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat as CL;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

use onebone\economyapi\EconomyAPI;


class KillCount extends PluginBase implements Listener{

public $prefix = [];

public function getKillCount($pn){
$killcount = $this->KC->get($pn);
return $killcount;
//TODO:More plugins
}

public function onEnable(){
$this->getLogger()->info(CL::BLUE."欢迎使用KillCount\n作者:SuperXingKong");
$this->getServer()->getPluginManager()->registerEvents($this,$this);
@mkdir($this->getDataFolder(),0777,true);
$this->KC = new Config($this->getDataFolder()."KillCount.yml",Config::YAML,array());
$this->saveDefaultConfig();
$this->reloadConfig();
$this->Config = new Config($this->getDataFolder()."config.yml");
$this->PF =new Config($this->getDataFolder()."prefix.yml",Config::YAML,array(
      1 => "战神",
      2 => "战帝",
      3 => "战皇",
      4 => "战王",
      5 => "战将",
      "others" => "战兵"
));
}

public function onJoin(PlayerJoinEvent $e){
$p=$e->getPlayer();
$pn = strtolower($p->getName());
if (!$this->KC->exists($pn)){
			$this->KC->set($pn,0);
			$this->KC->save();
}
}

public function onKill(PlayerDeathEvent $event){
        $cause = $event->getEntity()->getLastDamageCause();
        if($cause instanceof EntityDamageByEntityEvent){
            $killer = $cause->getDamager();
			$kn=$killer->getName();
			$sl=$this->KC->get($kn);
            if($killer instanceof Player){
$x=$killer->getX();
$y=$killer->getY();
$z=$killer->getZ();
$level=$killer->getLevel();
        $this->KC->set($kn,$sl + 1);
				$this->KC->save();
        $money = $this->getKillMoney();
				$killer->sendMessage(CL::GREEN."[KillCount]已累计一个人头\n你现在的人头数 : ".CL::RED."$sl + 1"."获得金钱".$money);
       EconomyAPI::getInstance()->addMoney($kn,$money);
            }
        }
    }

public function setKillMoney($money){
$this->Config->set("killmoney",$money);
$this->Config->save();
}
public function getKillMoney(){
return $this->getConfig()->get("killmoney");
}

public function onCommand(CommandSender $sender,Command $cmd,$label,array $args)
{
		$sn = strtolower($sender->getName());

		switch(strtolower($cmd->getName())){
			case"mykc":
	    if ($sender instanceof Player){
$kc=$this->getKillCount($sn);
$sender->sendMessage("你拥有的人头".$kc."个");
}else{
$sender->sendMessage("控制台差个卵人头数");
}
return true;

			case"kc":
if (isset($args[0])){
				$br=$args[0];
				if($this->KC->exists($br)){
         $kc=$this->getKillCount($br);
					$sender->sendMessage("[KillCount]他有".$kc."人头");
return true;
}else{
					$sender->sendMessage("[KillCount]他没有加入过游戏");
return true;
}
}

      case"setkillmoney":
if (count($args) == 1){
$this->setKillMoney($args[0]);
return true;
}

      case"killlist":
      $kl = $this->KC->getAll();
      arsort($kl);
      $msg = CL::YELLOW."杀人排行榜\n";
      $num = 0;
      foreach ($kl as $pn => $kc){
      $num++;
      if ($num > 5){
      break;
      }
      $msg .= CL::GREEN."[{$num}]>>".CL::RED.$pn.CL::BLUE."人头数:{$kc}\n";
}     
      $sender->sendMessage($msg);
      return true;
}
}

}
?>