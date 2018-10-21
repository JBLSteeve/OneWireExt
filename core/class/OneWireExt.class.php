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

class OneWireExt extends eqLogic {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

	public function createFromDef($_address){
	log::add('OneWireExt','debug',"Creation du la temperature");
			$OneWireExt = OneWireExt::byLogicalId($_address, 'OneWireExt');
			if ( ! is_object($OneWireExt)) {
			$OneWireExt = new OneWireExt();
			$OneWireExt->setName('Sonde');
			$OneWireExt->setLogicalId($_address);
            $OneWireExt->setEqType_name('OneWireExt');
            $OneWireExt->setIsEnable(1);
            $OneWireExt->setIsVisible(1);

			$OneWireExt->save();		
		}

    }
    public function updateValue($_address,$_value){
        if (!isset($_address)) {
            log::add('OneWireExt', 'error', 'addresse manquante pour mettre à jour la valeur de la température');
            return false;
        }
        if (!isset($_value)) {
            log::add('OneWireExt', 'error', 'Valeur manquante pour mettre à jour de la température de la sonde @'. print_r($_address, true));
            return false;
        }
        $OneWireExt = OneWireExt::byLogicalId($_address, 'OneWireExt');
        if (!is_object($OneWireExt)) {
            return false;
        }
        
        $cmd = $OneWireExt->getCmd('info',$_address);
    	if ($cmd == false){
        	$cmd = (new OneWireExtCmd())
				->setName('Temperature')
				->setLogicalId($_address)
				->setType('info');
			$cmd->setEqLogic_id($OneWireExt->id);
			$cmd->setIsHistorized(1)
				->setIsVisible(1);
			$cmd->setSubType('numeric')
				->setUnite('°C')
				->setIsHistorized(1)
				//->setconfiguration("minvalue",-50 )
				//->setconfiguration("maxvalue",70 )
				->setconfiguration("returnState`time",10 )
				->setTemplate("dashboard","thermometre" )
          		->setTemplate("mobile","badge" )
                ->setDisplay('generic_type', 'TEMPERATURE');	
			$cmd->setEventOnly(1);
			$cmd->save();
			}
			else {
			$cmd->event($_value);
			}
			//return $cmd;

    }
    
    
    public static function deamon_info() {
		$return = array();
		$return['log'] = 'OneWireExt';
		$return['state'] = 'nok';
		$pid_file = jeedom::getTmpFolder('OneWireExt') . '/deamon.pid';
		if (file_exists($pid_file)) {
			$pid = trim(file_get_contents($pid_file));
			if (is_numeric($pid) && posix_getsid($pid)) {
				$return['state'] = 'ok';
			} else {
				shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null;rm -rf ' . $pid_file . ' 2>&1 > /dev/null;');
			}
		}
		$return['launchable'] = 'ok';
		return $return;
	}

	public static function deamon_start() {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$OneWireExt_path = realpath(dirname(__FILE__) . '/../../resources/OneWireExt');
		$cmd = '/usr/bin/python ' . $OneWireExt_path . '/OneWireExt.py';
		$cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('OneWireExt'));
		$cmd .= ' --socketport ' . config::byKey('socketport', 'OneWireExt');
		$cmd .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/OneWireExt/core/php/OneWireExt.php';
		$cmd .= ' --apikey ' . jeedom::getApiKey('OneWireExt');
		$cmd .= ' --cycle ' . config::byKey('cycle', 'OneWireExt');
		$cmd .= ' --pid ' . jeedom::getTmpFolder('OneWireExt') . '/deamon.pid';
		log::add('OneWireExt', 'info', 'Lancement démon OneWireExt : ' . $cmd);
		exec($cmd . ' >> ' . log::getPathToLog('OneWireExt') . ' 2>&1 &');
		$i = 0;
		while ($i < 30) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 30) {
			log::add('OneWireExt', 'error', 'Impossible de lancer le démon OneWireExt, vérifiez le log', 'unableStartDeamon');
			return false;
		}
		message::removeAll('OneWireExt', 'unableStartDeamon');
		sleep(2);
		config::save('include_mode', 0, 'OneWireExt');
		log::add('OneWireExt', 'info', 'Démon OneWireExt lancé');
		return true;
	}

	public static function deamon_stop() {
		$pid_file = jeedom::getTmpFolder('OneWireExt') . '/deamon.pid';
		if (file_exists($pid_file)) {
			$pid = intval(trim(file_get_contents($pid_file)));
			system::kill($pid);
		}
		system::kill('OneWireExt.py');
		system::fuserk(config::byKey('socketport', 'OneWireExt'));
		sleep(1);
	}


	
	public static function pull() {
	//Appele toute les secondes scan ou verification status
	}
	
	public function preInsert(){
		$this->setIsVisible(0);
	}

	public function postInsert(){
		log::add('OneWireExt','debug',"function post");

	}
		
	public function preUpdate(){
		log::add('OneWireExt','debug',"function preUp");
		if ( $this->getIsEnable() )
		{
			// todo
		}
	}

	public function postUpdate(){

		

	}


	public function preRemove(){

	}


 /*   public function event() {
		log::add('OneWireExt','debug',"function event");
		
		   'S' => array( // 'S_TYPE', 'Nom', 'widget', 'variable, 'unité', 'historique', 'affichage'
      0 => array('S_DOOR','Ouverture','door','binary','','','1','OPENING',),
      1 => array('S_MOTION','Mouvement','presence','binary','','','1','PRESENCE',),
      2 => array('S_SMOKE','Fumée','line','binary','','','1','SMOKE',),
      3 => array('S_LIGHT','Relais','light','binary','','','0','ENERGY_STATE',),
      4 => array('S_DIMMER','Variateur','light','numeric','%','','0','ENERGY_STATE',),
      5 => array('S_COVER','Store','store','binary','','','1','FLAP_STATE',),
      6 => array('S_TEMP','Température','line','numeric','°C','1','1','TEMPERATURE',),
      
        $temp = $this->getCmd(null, 'Température');
        if ( ! is_object($state) ) {
            $temp = new OneWireExt_Temp();
			$temp->setName('Température');
			
			$temp->setIsHistorized(1);
			$temp->setEqLogic_id($this->getId());
			$temp->setLogicalId('temperature');
			$temp->setUnite('°C');
			$temp->setType('info');
			$temp->setSubType('numeric');
			
			//$temp->setDisplay('generic_type','LIGHT_STATE');
			$temp->setTemplate('dashboard', 'temperature');
			$temp->setTemplate('mobile', 'temperature'); 
			$temp->setStatus('lastCommunication', date('Y-m-d H:i:s'));     
			$temp->save();
      

	}
		*/
	public function getImage() {
			return 'plugin/OneWireExt/core/config/device/' . $this->getConfiguration('board') . '.jpg';
	}
    
    /*     * **********************Getteur Setteur*************************** */
}

class OneWireExtCmd extends cmd 
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
    public function execute($_options = null) {
		}

}
?>
