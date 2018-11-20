# This file is part of Jeedom.
#
# Jeedom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# Jeedom is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Jeedom. If not, see <http://www.gnu.org/licenses/>.

import globals
import struct
import logging
import string
import sys
import os
import time
import argparse
import datetime
import binascii
import re
import signal
import traceback
import xml.dom.minidom as minidom
from optparse import OptionParser
from os.path import join
import json
import glob
try:
	from jeedom.jeedom import *
except ImportError:
	print "Error: importing module from jeedom folder"
	sys.exit(1)

os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')
# ----------------------------------------------------------------------------
def write_socket(address,value):	#type=input or output or status
	logging.debug("Send update for the board @:" + str(address))
	#Construction JSON
	message ={}
	message['address'] = str(address).replace('\x00', '')
	message['value'] = str(value).replace('\x00', '')
		
	try:
		globals.JEEDOM_COM.add_changes('devices::'+message['address'],message)
	except Exception:
		logging.error("Send to jeedom error for message " +str(message))
				
# ----------------------------------------------------------------------------	
def read_1wBus(_base_dir):
	device_folder = glob.glob(_base_dir + '10*')
	for device in device_folder:
		#logging.debug("lecture du capteur:" + device)
		device_file = device + '/w1_slave'
		f = open(device_file, 'r')
		lines = f.readlines()
		f.close()
		#logging.debug("donnees du capteur:")
		#logging.debug(lines[0])
		#logging.debug(lines[1])
		i=0
		while i<3:
			if lines[0].strip()[-3:] == 'YES':
				equals_pos = lines[1].find('t=')
				if equals_pos != -1:
					temp_string = lines[1][equals_pos+2:]
					temp_c = float(temp_string) / 1000.0
					#temp_f = temp_c * 9.0 / 5.0 + 32.0
					if temp_c!=85:
						write_socket(device[23:],round(temp_c,1))
						logging.debug("Send for the sensor @" + device[23:] + " the temperature =" + str(round(temp_c,1)))
						break
			else:
				time.sleep(0.2)
				i=i+1
		if i==3:
			logging.debug("Error for the sensor @" + device[23:])
			#write_socket(device[23:],"-55")
# ----------------------------------------------------------------------------	
def read_temp_raw(device_file):
	f = open(device_file, 'r')
	lines = f.readlines()
	f.close()
	return lines
# ----------------------------------------------------------------------------	
def main(_cycle,_base_dir):
	logging.debug("Start deamon")
	try:
		while 1:
			read_1wBus(_base_dir)
			time.sleep(_cycle)
				
	except KeyboardInterrupt:
		shutdown()

# ----------------------------------------------------------------------------
def handler(signum=None, frame=None):
	logging.debug("Signal %i caught, exiting..." % int(signum))
	shutdown()
	
# - Shutdown ---------------------------------------------------------------------------
def shutdown():
	logging.debug("Shutdown")
	
	logging.debug("Removing PID file " + str(_pidfile))
	try:
		os.remove(_pidfile)
	except:
		pass
	try:
		jeedom_socket.close()
	except:
		pass
	logging.debug("Exit 0")
	sys.stdout.flush()
	os._exit(0)



# - Main program ---------------------------------------------------------------------------
_log_level = "error"
_socket_port = 55550
_socket_host = '127.0.0.1'
_device = '1'
_pidfile = '/tmp/OneWireExt.pid'
_apikey = ''
_callback = ''
_cycle = 5.0
_base_dir = '/sys/bus/w1/devices/'

parser = argparse.ArgumentParser(description='OneWireExt Daemon for Jeedom plugin')
parser.add_argument("--socketport", help="Socketport for server", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--callback", help="Callback", type=str)
parser.add_argument("--apikey", help="Apikey", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
parser.add_argument("--pid", help="Pid file", type=str)
args = parser.parse_args()


if args.socketport:
	_socket_port = int(args.socketport)
if args.loglevel:
	_log_level = args.loglevel
if args.callback:
	_callback = args.callback
if args.apikey:
	_apikey = args.apikey
if args.pid:
	_pidfile = args.pid
if args.cycle:
	_cycle = float(args.cycle)

	
jeedom_utils.set_log_level(_log_level)

logging.info('Start OneWireExt daemon')
logging.info('Log level : '+str(_log_level))
logging.info('Socket port : '+str(_socket_port))
logging.info('Socket host : '+str(_socket_host))
logging.info('PID file : '+str(_pidfile))
logging.info('Apikey : '+str(_apikey))
logging.info('Callback : '+str(_callback))
logging.info('Cycle : '+str(_cycle))

signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)	

try:
	jeedom_utils.write_pid(str(_pidfile))
	globals.JEEDOM_COM = jeedom_com(apikey = _apikey,url = _callback,cycle=0)
	print('api ',_apikey)
	if not globals.JEEDOM_COM.test():
		logging.error('Network communication issues. Please fixe your Jeedom network configuration.')
		shutdown()
	jeedom_i2c = jeedom_i2c(port=_device)
	jeedom_socket = jeedom_socket(port=_socket_port,address=_socket_host)

	logging.debug("Start ...")
	
	#Start main program
	main(_cycle,_base_dir)
except:
	logging.error('Fatal error : '+ str(sys.exc_info()[0]))
	logging.debug(traceback.format_exc())
	shutdown()