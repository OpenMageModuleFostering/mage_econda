var sendOne = 1;
var orderProcess = '';
var contentLabel = '';

	if (document.addEventListener){
		document.addEventListener("mousemove", onePageTracker, false); 
	} 
	else if (document.attachEvent){ //IE
		document.attachEvent("onmousemove", onePageTracker);
	}

function onePageTracker() {
	
	orderProcess = '';
	contentLabel = '';
	                 
	if(document.getElementById('opc-login')) {
		if(stristr(document.getElementById('opc-login').className,'active') != false && sendOne == 1) {
			orderProcess = '2_' + ecStep[0];
			contentLabel = ecStep[0];
			sendOne += 1;
		}
	}
	
	if(document.getElementById('opc-billing')) {
	   if(stristr(document.getElementById('opc-billing').className,'active') != false && (sendOne == 1 || sendOne == 2)) {
		  orderProcess = '3_' + ecStep[1];
		  contentLabel = ecStep[6];
		  if(typeof(emospro.login) != 'undefined') {
		      delete emospro.login;
		  }
		  if(sendOne == 1) {
			 sendOne += 1;
		  }
		  sendOne += 1;		
	   }
	}
	
	if(document.getElementById('opc-shipping')) {
	   if(stristr(document.getElementById('opc-shipping').className,'active') != false && sendOne == 3) {
		  orderProcess = '4_' + ecStep[2];
		  contentLabel = ecStep[2];
		  sendOne += 1;
	   }
	}
	
	if(document.getElementById('opc-shipping_method')) {
	   if(stristr(document.getElementById('opc-shipping_method').className,'active') != false && (sendOne == 3 || sendOne == 4)) {
		  orderProcess = '4_' + ecStep[3];
		  contentLabel = ecStep[3];
		  if(sendOne == 3) {
			 sendOne += 1;
		  }
		  sendOne += 1;
	   }
	}
	
	if(document.getElementById('opc-payment')) {
	   if(stristr(document.getElementById('opc-payment').className,'active') != false && sendOne == 5) {
		  orderProcess = '5_' + ecStep[4];
		  contentLabel = ecStep[4];
		  sendOne += 1;
	   }
	}
	
	if(document.getElementById('opc-review')) {
	   if(stristr(document.getElementById('opc-review').className,'active') != false && sendOne == 6) {
		  orderProcess = '6_' + ecStep[5];
		  contentLabel = ecStep[5];
		  sendOne += 1;
	   }
	}		

	if (orderProcess != '') {
		emospro.orderProcess=orderProcess;
		emospro.content='Start/' + ecStep[7] + '/' + contentLabel;
		window.emosPropertiesEvent(emospro);
	}
}

function stristr(haystack,needle,bool) {
    var pos = 0;
    haystack += '';
    pos = haystack.toLowerCase().indexOf( (needle+'').toLowerCase() );
    if( pos == -1 ){
        return false;
    } else{
        if( bool ){
            return haystack.substr(0,pos);
        } else{
            return haystack.slice(pos);
        }
    }
}