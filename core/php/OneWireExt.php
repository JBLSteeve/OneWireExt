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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (!jeedom::apiAccess(init('apikey'), 'OneWireExt')) {
	echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
	die();
}
if (isset($_GET['test'])) {
	echo 'OK';
	die();
}
$result = json_decode(file_get_contents("php://input"), true);
if (!is_array($result)) {
	die();
}
$eqLogics = eqLogic::byType('OneWireExt');


if (isset($result['devices'])) {
							
	foreach ($result['devices'] as $key => $device){
		if (!isset($device['address'])) {
			continue;
		}
		
			if ( ! is_object(OneWireExt::byLogicalId($device['address'], 'OneWireExt'))){
				OneWireExt::createFromDef($device['address']);
			}
			if (isset($device['value'])){
				//log::add('OneWireExt','debug','Mise à jour de la valeur:' . $device['value']);
				OneWireExt::updateValue($device['address'],$device['value']);				
			}
 	}
}