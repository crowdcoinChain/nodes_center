<?php
date_default_timezone_set('Europe/London');

$filename="../../bin/MNLIST.txt";
$datefile="";
$allowed_status=array("NEW_START_REQUIRED","ENABLED","WATCHDOG_EXPIRED");
function read_mnlist($filename) {
 global $datefile;
 // Open the file
 if (file_exists($filename)) {
   #$datefile=date ("F d Y H:i:s.", filemtime($filename));
   $datefile=filemtime($filename);
   $MNLIST=array();
   $fp = @fopen($filename, 'r');
   // Add each line to an array
   if ($fp) {
     $database = @fread($fp, filesize($filename));
     if ($database !==false) {
       $MNLIST = explode("\n",$database);
       return $MNLIST;
     } else {
	return FALSE;
     }
   } else {
    return FALSE;
   }
 } else {
    return FALSE;
 }
}


//Init MN List Array
$MN_LIST=read_mnlist($filename);

if ($MN_LIST !== FALSE) {
  
  $ip="";
  if (!empty($_POST["mnip"])) {
	  if(filter_var($_POST["mnip"], FILTER_VALIDATE_IP)) {
	    $ip = (string)$_POST["mnip"];
	  }
  }

  $MN_TOTAL=array();
  $MN_ENABLE=array();
  $MN_UNHEALTHY=array();
  $MN_UNHEALTHY_STATUS=array();
  $MNIP_STATUS="";
  $MNIP_LASTSEEN="";
  $MNIP_ACTIVESEC="";
  $MNIP_LASTPAYMENT="";
  $MNIP_LASTPAID_BLOCK="";

  //Building general statistics
  $POS=0;
  foreach ($MN_LIST as $KEY=>$LINE) {
    //removing consecutive space
    $clean_LINE = preg_replace('!\s+!', ' ', TRIM($LINE));
    //don't process empty line
    if ($clean_LINE<>"") {

	$tmp=explode(' ',$clean_LINE);
	//check we have all our fields
	if (count($tmp)==9) {

 		$MN_TOTAL[]=$tmp;
		$MN_STATUS=$tmp[1];

		//remove the ":" at the end of the TXID
		$TXID=substr($tmp[0], 0, -1);

		// Define each fields
		$MN_LASTSEEN=$tmp[4];
		$MN_ACTIVESEC=$tmp[5];
		$MN_LASTPAYMENT=$tmp[6];
		$MN_LASTPAID_BLOCK=$tmp[7];

		// remove port to get IP only
		$TMP_IP=explode(':',$tmp[8]);
		$MN_IP=$TMP_IP[0];

		//store MNIP_INFO if match out IP
		if ($MN_IP==$ip) {
			$MNIP_STATUS=(in_array($MN_STATUS, $allowed_status) ? $MN_STATUS : "NA" );
  			$MNIP_LASTSEEN=$MN_LASTSEEN;
  			$MNIP_ACTIVESEC=$MN_ACTIVESEC;
  			$MNIP_LASTPAYMENT=$MN_LASTPAYMENT;
  			$MNIP_LASTPAIS_BLOCK=$MN_LASTPAID_BLOCK;
		}

		// if MN is "ENABLED" we calculate last Payment in second
		if ($MN_STATUS=="ENABLED") {
			$POS++;
			$TIME=0;
			//This node never get any payment so we take since how long it is active
			if ($MN_LASTPAYMENT==0) {
			  $TIME=$MN_ACTIVESEC;
			} else {
			  // we Calculate number of sec since last payment
			  $MN_PAYMENT_SEC=time()-$MN_LASTPAYMENT;
			  // if MN has been desactivate since last payment we take number of seconds it is active
			  if ($MN_PAYMENT_SEC>=$MN_ACTIVESEC) {
				$TIME=$MN_ACTIVESEC;
			  // else we take number of seconds since last payement
			  } else {
				$TIME=$MN_PAYMENT_SEC;
			  }
			}
			$tmp[0]=$TXID;
			$tmp[8]=$MN_IP;
			$tmp[9]=$TIME;
			$MN_ENABLE[$POS]=$tmp;
		// If MN is not "ENABLED" we just take counter for each MN status
		} else {
			if (array_key_exists($MN_STATUS,$MN_UNHEALTHY_STATUS)) {
				$MN_UNHEALTHY_STATUS[$MN_STATUS]++;
			} else {
				$MN_UNHEALTHY_STATUS[$MN_STATUS]=1;
			}
			$tmp[0]=$TXID;
			$tmp[8]=$MN_IP;
			$MN_UNHEALTHY[]=$tmp;
		}
	}
     }
  }
  array_multisort( array_column($MN_ENABLE,9), SORT_DESC, $MN_ENABLE );
  $POS_IP=array_column($MN_ENABLE,8);
  $POS_TXID=array_column($MN_ENABLE,0);
  $TOTAL_ENABLE=count($MN_ENABLE);
  $TOTAL_UNHEALTHY=count($MN_UNHEALTHY);
  $TOTAL_MN=count($MN_TOTAL);

	
  //building Json Answer
  $json_answer=array();
  $json_answer[]=array("update"=>$datefile);
  
  $json_answer[]=array("masternodes"=>array("total"=>$TOTAL_MN,"enabled"=>$TOTAL_ENABLE,"unhealthy"=>$TOTAL_UNHEALTHY));
  $json_answer[]=array("unhealthy"=>$MN_UNHEALTHY_STATUS);


  if (filter_var($ip, FILTER_VALIDATE_IP)) {
	$Position = array_search($ip, $POS_IP);
	if ($Position !==false) {
		$json_answer[]=array("stats"=>array("pos"=>$Position,"result"=>"success","message"=>"ok","status"=>$MNIP_STATUS,"lastseen"=>$MNIP_LASTSEEN));	
	} else {
		$json_answer[]=array("stats"=>array("pos"=>-1,"result"=>"warning","message"=>"IP not found in the enabled masternode list","status"=>$MNIP_STATUS,"lastseen"=>$MNIP_LASTSEEN));	
	}
  } else {
	$Position=-1;
	$json_answer[]=array("stats"=>array("pos"=>-1,"result"=>"warning","message"=>"You need to enter an IPV4 address","status"=>"","lastseen"=>"0"));
  }
} else {
  $json_answer[]=array("stats"=>array("pos"=>-1,"result"=>"critical","message"=>"Error while reading the database","status"=>"","lastseen"=>0));
}
print_r(json_encode($json_answer));

