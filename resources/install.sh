touch /tmp/dependancy_OneWireExt_in_progress
echo 0 > /tmp/dependancy_OneWireExt_in_progress
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
apt-get update
echo 50 > /tmp/dependancy_OneWireExt_in_progress
apt-get install -y python-requests python-pyudev python-smbus i2c-tools
echo 80 > /tmp/dependancy_OneWireExt_in_progress
modprobe w1-gpio && modprobe w1-therm
echo 100 > /tmp/dependancy_OneWireExt_in_progress
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm /tmp/dependancy_OneWireExt_in_progress