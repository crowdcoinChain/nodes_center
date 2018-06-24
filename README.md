# CRC Nodes center
## Description
Nodes Center is a one page PHP sccript that permit to check the status of a Crowdcoin masternode and also to display the position of a masternode in the payment queue.
* mnlist.sh run at regular interval using cron

``*/5 * * * * cd /var/www/nodes.crowdcoin.site/bin && /var/www/nodes.crowdcoin.site/bin/mnlist.sh > /dev/null 2>&1``

It use the command : 

``crowdcoin-cli masternode list full``

this will create a MNLIST.txt that will be read by the PHP script

* index.php is just the Main web page displaying the form for asking th IP and running the ajax json request to grab the information.

* mnstat.json.php is the script that read the MNLIST.txt, get the IP post by the form and create a json output containing the information need (number of masternodes, masternode enabled, position, last seeen, ect...)

## Future improvement
Creating a "add to monitoring" button to get a persistent list of IP, and a uniq UID that user can recall to get the status of their masternodes list.

