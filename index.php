<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>CRC Nodes</title>
 <link rel="icon" href="favicon.ico">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
</head>
<body>
 <div class="jumbotron text-center">
  <img src="/img/crc-logo.png">
  <h1>Node Center</h1>
 </div>

 <div class="container">
  <div class="row">
    <div class="col-sm-4">
      <h4>IPV4 nodes</h4>
	<p>
	If you have issue to synchronize your wallet or your masternode, you can copy/paste the list of nodes below in your crowdcoin.conf.
	</p>
      <code class="small">
	<?php
	 $result = dns_get_record("seed.crowdcoin.site",DNS_A);
	 foreach ($result as $node) {
	    echo "addnode=".$node["ip"]."<br>";
	 }
	?>
       </code>
    </div>
    <div id="stat_form" class="col-sm-8">
      <h3>Check your masternode</h3>
	<p>
	This form permit to check the position of your masternode in the payment queue.
	It is provided for your information only and without any warranty. Database is updated every 5 minutes.
	</p>
      <form id="mnform" class="form-inline">
  	<div class="form-group">
    	 <label for="mnip">Enter your masternode IP :&nbsp; </label>
    	  <input type="text" class="form-control" id="mnip" name="mnip">
  	</div>
	<button id="view" type="submit" class="btn btn-warning">Check</button>
	</form>
	<br>
    	<div id="mnstat"></div>
    </div>
  </div>
 </div>
 <script>
   $(function() {
	$("#mnform").submit(function (e) {
	 e.preventDefault();
	  //console.log ("From Submitted");
	  $.ajax({
	    url: './json/mnstat.json.php',
	    type: 'post',
	    data : $(this).serialize(),
	    success: function(jsondata){
		console.log (jsondata);
		var mnstat = JSON.parse(jsondata);
		var dt = new Date(mnstat[0]["update"]*1000);

		var total = mnstat[1]["masternodes"]["total"];
		var enabled = mnstat[1]["masternodes"]["enabled"];
		var unhealthy = mnstat[1]["masternodes"]["unhealthy"];
		var pos = mnstat[3]["stats"]["pos"];
		var result = mnstat[3]["stats"]["result"];
		var message = mnstat[3]["stats"]["message"];
		var status = mnstat[3]["stats"]["status"];
		var dlastseen = new Date(mnstat[3]["stats"]["lastseen"]*1000);
		var lastseen = dlastseen.toLocaleString();

		var response = '<div id="mnstat">';
		response += '<hr>';
		response += 'Last database update : '+dt.toLocaleString()+'<br>';
		response += 'Total masternodes: <span class="badge badge-primary">'+total+ '</span>&nbsp;'
		response += 'Enabled: <span class="badge badge-success">'+enabled+ '</span>&nbsp;'
		response += 'Warning: <span class="badge badge-warning">'+unhealthy+ '</span>'
		response += '<hr>';

		switch (result) {
		 case 'success':
			response += '<div id="mynode" class="alert alert-success" role="alert">';
		 	break;
		case 'warning':
			response += '<div id="mynode" class="alert alert-warning" role="alert">';
		 	break;
		case 'critical':
			response += '<div id="mynode" class="alert alert-danger" role="alert">';
		 	break;
		default:
			response += '<div id="mynode" class="alert alert-primary" role="alert">';
			
		}
		if (typeof pos !== 'undefined') {
			if (pos != -1) {
				response +='Total of masternodes in the payment queue: '+total+'<br>';
				response +='Your position: ' +pos +' (Lower is better)<br>';
				response +='<i>When you hit 0, your masternode will receive payment at the next round</i>';
				response += '</div>';
			} else {
				response +='Total of Masternodes in the payment queue: '+total+'<br>';
				response += message;
				response += '</div>';
			}
			
		} else {
			response +='Error code -1';
			response += "</div>";
		}
		response += '<div>';
		response += 'Your masternode status: ' + status + '<br>';
		response += 'Last seen: ' + lastseen + '<br>';
		response += '</div>';
		$("#mnstat").replaceWith(response);
	    },
	    error: function (xhr, status, error){
		var response = '<div id="mnstat" class="alert alert-danger" role="alert">';
		response +='Error code -2';
		response += "</div>";
		response += "</div>";
		$("#mnstat").replaceWith(response);
	    }
	  });
	  return false;
	});
   });
 </script>
</body>
</html>
