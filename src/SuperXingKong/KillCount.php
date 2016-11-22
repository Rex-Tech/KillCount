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
use pocketmine\level\particle\AngryVillagerParticle;


class KillCount extends PluginBase implements Listener{

public function getKillCount($pn){
$killcount = $this->KC->get("$pn");
return $killcount;
//TODO:More plugins
}

public function onEnable(){
$this->getLogger()->info(CL::BLUE."欢迎使用KillCount\n作者:SuperXingKong");
$this->getServer()->getPluginManager()->registerEvents($this,$this);
@mkdir($this->getDataFolder(),0777,true);
$this->KC=new Config($this->getDataFolder()."KillCount.yml",Config::YAML,array());
$this->saveDefaultConfig();
}

public function onJoin(PlayerJoinEvent $e){
$p=$e->getPlayer();
$pn=$p->getName();
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
$level->addParticle(new AngryVillagerParticle(new Vector3($x,$y,$z)));
        $this->KC->set($kn,$sl + 1);
				$this->KC->save();
        $money = $this->getKillMoney();
				$killer->sendMessage(CL::GREEN."[KillCount]已累计一个人头\n你现在的人头数 : ".CL::RED."$sl + 1"."获得金钱".$money);
       EconomyAPI::getInstance()->addMoney($kn,$money);
            }
        }
    }

public function setKillMoney($money){
$this->getConfig()->set("killmoney",$money);
}
public function getKillMoney(){
return $this->getConfig()->get("killmoney");
}

public function onCommand(CommandSender $sender,Command $cmd,$label,array $args)
{
		$sn=$sender->getName();

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

      case"url":
$sender->sendMessage("本插件路径:".__FILE__."\n操作系统:".PHP_OS."\nPHP版本:".PHP_VERSION);
reture true;

      case"setkillmoney":
if (count($args) == 1){
$this->setKillMoney($args[0]);
return true;
}

}
}

}
?>