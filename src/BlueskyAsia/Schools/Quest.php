
<?php

namespace BlueskyAsia\Schools;


use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use BlueskyAsia\Schools\libs\jojoe77777\FormAPI\SimpleForm;

Class Quest extends PluginBase {
	/** @var Quests */
	public $quests;
	/** @var Config*/
	public $questData;
	/* @var \Sqlite3*/
	public $db;
	/**@var Config*/
	private $config;

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->saveResource("quests.yml");
		$this->saveResource("config.yml");
		$this->config = new Config($this->getDataFolder(). "config.yml". Config::YAML);
		$this->questData = new Config($this->getDataFolder() . "quests.yml", CONFIG::YAML);
		$this->db = new \SQLite3($this->getDataFolder() . "quest.db"); 
		$this->db->exec("CREATE TABLE IF NOT EXISTS pquests (name TEXT PRIMARY KEY COLLATE NOCASE, quest TEXT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS pcompleted (name TEXT PRIMARY KEY COLLATE NOCASE, quests TEXT);");
		$this->quests = new Quests($this); 
	}


	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
		if(strtolower($command->getName()) == "tugas"){
			if($sender instanceof Player){
				$this->sendForm($sender);
			}else $sender->sendMessage("§c§lERROR! §r§7Please use the command in-game only!");
		}	
		return true;
	}


	public function rca(Player $player, string $string){
		$command = str_replace("{player}", $player->getName(), $string);
		Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $command);
	}
	/**
	* @param Player $player
	* @return string|null
	*/
	public function getPlayerQuest(Player $player) : ?string
	{
		$name = $player->getName();
		$result = $this->getPlugin()->db->query("SELECT * FROM pquests WHERE name = '$name';");
		$resultArr = $result->fetchArray(SQLITE3_ASSOC);
		return $resultArr["quest"];
	}
	/**
	* @param Player $player
	*/
	public function sendForm(Player $player){
		$quest = new Quests($this);
		$form = new SimpleForm(function(Player $player, ?int $data) use ($quest){
			if($data == 0) $quest->sendQuestApplyForm($player);
			if($data == 1) $quest->Completed($player);
			if($data == 2) $quest->showQuest($player);
		});
		$form->setTitle($this->getConfig()->get("Title-form"));
		$form->addButton($this->getConfig()->get("Quests-button"));
		$form->addButton($this->getConfig()->get("Complete-button"));
		$form->addButton($this->getConfig()->get("Player-quests-button"));
		$form->sendToPlayer($player);
	}
}
		
