<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class candy extends eqLogic {
	public static function cron5() {
		$eqLogics = eqLogic::byType('candy', true);
		foreach ($eqLogics as $eqLogic) {
			$eqLogic->refresh();
		}
	}

	public function loadCmdFromConf($type) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $liste_cmd) {
				if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
				|| (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
					$cmd = $liste_cmd;
					break;
				}
			}
			if ($cmd == null || !is_object($cmd)) {
				$cmd = new candyCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
			}
		}
	}

	public function postAjax() {
		$this->loadCmdFromConf('candy');
	}

	public function refresh() {
		$this->getStatus();
		//$this->getStatistics();
	}

	public function getKey() {
		if ($this->getConfiguration('key', '0000') == '0000') {
			$result = $this->command('key');
			$this->setConfiguration('key', $result);
			$this->save();
		}
	}

	public function getStatus() {
		$result = $this->command('status');
		foreach (json_decode($result,true) as $key => $value) {
			$this->checkCmd($key, $value);
			$this->checkAndUpdateCmd($_cmd, $_value);
		}
	}

	public function getStatistics() {
		$result = $this->command('stats');
		foreach (json_decode($result,true) as $key => $value) {
			$this->checkCmd($key, $value);
			$this->checkAndUpdateCmd($_cmd, $_value);
		}
	}

	public function checkCmd($_cmd, $_value) {
		$cmdtest = $this->getCmd(null, $_cmd);
		if (!is_object($cmdtest)) {
			$cmd = new candyCmd();
			$cmd->setName('Statistique ' . $_cmd);
			$cmd->setEqLogic_id($this->id);
			$cmd->setEqType('candy');
			$cmd->setLogicalId($_cmd);
			$cmd->setType('info');
			$cmd->setSubType('string');
			$cmd->save();
		}
	}

	public function command($_key = 'status') {
		if ($this->pingHost($this->getConfiguration('ip'))) {
			$cmd = 'python3 ' . realpath(dirname(__FILE__) . '/../../resources') . '/candy.py ' . $this->getConfiguration('ip') . ' ' . $this->getConfiguration('key', '0000') . ' ' . $_key;
			$result = shell_exec($cmd);
			log::add('candy', 'debug', 'Cmd : ' . $cmd);
			log::add('candy', 'debug', 'Result : ' . $result);
			return $result;
		} else {
		  return '0000';
		}
	}

	public function pingHost($host, $timeout = 1) {
	  exec(system::getCmdSudo() . "ping -c1 " . $host, $output, $return_var);
	  if ($return_var == 0) {
	    $result = true;
	    $this->checkAndUpdateCmd('online', 1);
	  } else {
	    $result = false;
	    $this->checkAndUpdateCmd('online', 0);
	  }
	  return $result;
	}
}

class candyCmd extends cmd {
	public function execute($_options = null) {
			if ($this->getType() == 'action') {
				$eqLogic = $this->getEqLogic();
				if ($this->getLogicalId() == 'refresh') {
					$eqLogic->refresh();
					return;
				}
			}
		}
}
?>
