#!/bin/bash
# This script extract the masternode list full and remove some characters to get a cleaner fixed size list.
# Version 1.0
# Date : 17.06.2018
cd /var/www/nodes.crowdcoin.site/bin
/opt/node/Crowdcoin/bin/node.sh 1 masternode list full | sed -e 's/[}|{]//' -e 's/"//g' -e 's/,//g' | grep -v ^$ > MNLIST.txt
chown www-data.www-data MNLIST.txt

