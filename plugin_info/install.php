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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function OneWireExt_install() {
    $cron = cron::byClassAndFunction('OneWireExt', 'pull');
	if ( ! is_object($cron)) {
        $cron = new cron();
        $cron->setClass('OneWireExt');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->save();
	}
	config::remove('listChildren', 'OneWireExt');
	config::save('subClass', 'OneWireExt_bouton;OneWireExt_relai', 'OneWireExt');
	jeedom::getApiKey('OneWireExt');
	if (config::byKey('api::OneWireExt::mode') == '') {
		config::save('api::OneWireExt::mode', 'enable');
	}
}

function OneWireExt_update() {
	config::remove('listChildren', 'OneWireExt');
	config::save('subClass', 'OneWireExt_bouton;OneWireExt_relai', 'OneWireExt');
    $cron = cron::byClassAndFunction('OneWireExt', 'pull');
	if ( ! is_object($cron)) {
        $cron = new cron();
        $cron->setClass('OneWireExt');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->save();
	}
    $cron = cron::byClassAndFunction('OneWireExt', 'cron');
	if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	}
	foreach (eqLogic::byType('OneWireExt_bouton') as $SubeqLogic) {
		$SubeqLogic->save();
	}
	foreach (eqLogic::byType('OneWireExt_relai') as $SubeqLogic) {
		$SubeqLogic->save();
	}
	foreach (eqLogic::byType('OneWireExt') as $eqLogic) {
		$eqLogic->save();
	}
	if ( config::byKey('api', 'OneWireExt', '') == "" )
	{
		log::add('OneWireExt', 'alert', __('Une clef API "OneWireExt" a été configurée. Pensez à reconfigurer le push de chaque carte OneWireExt', __FILE__));
	}
	jeedom::getApiKey('OneWireExt');
	if (config::byKey('api::OneWireExt::mode') == '') {
		config::save('api::OneWireExt::mode', 'enable');
	}
}

function OneWireExt_remove() {
    $cron = cron::byClassAndFunction('OneWireExt', 'pull');
    if (is_object($cron)) {
		$cron->stop();
        $cron->remove();
    }
    $cron = cron::byClassAndFunction('OneWireExt', 'cron');
    if (is_object($cron)) {
		$cron->stop();
        $cron->remove();
    }
	config::remove('listChildren', 'OneWireExt');
	config::remove('subClass', 'OneWireExt');
}
?>
