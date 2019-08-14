// econda one page checkout helper
// Copyright 2009-2015 econda GmbH
var sendOne = 1;
var orderProcess = '';
var contentLabel = '';
var pageId = '';

// set event handler
if (document.addEventListener){
    document.addEventListener("mousemove", onePageTracker, false); 
} 
else if (document.attachEvent){ //IE
    document.attachEvent("onmousemove", onePageTracker);
}

// get checkout steps
function onePageTracker()
{
    orderProcess = '';
    contentLabel = '';
    pageId = '';
    
    // content path for step 2 :login
    if($('opc-login')) {
        if(stristr($('opc-login').className,'active') != false && sendOne == 1) {
            orderProcess = '2_' + ecStep[0];
            contentLabel = ecStep[0];
            pageId = 'onepagecheckout1';
            sendOne += 1;
        }
    }

    // content path for step 3 :billing
    if($('opc-billing')) {
        if(stristr($('opc-billing').className,'active') != false && (sendOne == 1 || sendOne == 2)) {
            orderProcess = '3_' + ecStep[1];
            contentLabel = ecStep[6];
            pageId = 'onepagecheckout2';
            if(typeof(emospro.login) != 'undefined') {
                delete emospro.login;
            }
            if(sendOne == 1) {
                sendOne += 1;
            }
            sendOne += 1;		
        }
    }

    // content path for step 4 :shipping
    if($('opc-shipping')) {
        if(stristr($('opc-shipping').className,'active') != false && sendOne == 3) {
            orderProcess = '4_' + ecStep[2];
            contentLabel = ecStep[2];
            pageId = 'onepagecheckout3';
            sendOne += 1;
        }
    }

    // content path for step 4 :shipping/method
    if($('opc-shipping_method')) {
        if(stristr($('opc-shipping_method').className,'active') != false && (sendOne == 3 || sendOne == 4)) {
            orderProcess = '4_' + ecStep[3];
            contentLabel = ecStep[3];
            pageId = 'onepagecheckout4';
            if(sendOne == 3) {
                sendOne += 1;
            }
            sendOne += 1;
        }
    }

    // content path for step 5 :payment
    if($('opc-payment')) {
        if(stristr($('opc-payment').className,'active') != false && sendOne == 5) {
            orderProcess = '5_' + ecStep[4];
            contentLabel = ecStep[4];
            pageId = 'onepagecheckout5';
            sendOne += 1;
        }
    }

    // content path for step 6 :order review
    if($('opc-review')) {
        if(stristr($('opc-review').className,'active') != false && sendOne == 6) {
            orderProcess = '6_' + ecStep[5];
            contentLabel = ecStep[5];
            pageId = 'onepagecheckout6';
            sendOne += 1;
        }
    }

    // send content path and order process step
    if (orderProcess != '') {
        emospro.orderProcess = orderProcess;
        emospro.content = 'Start/' + ecStep[7] + '/' + contentLabel;
        emospro.pageId = pageId; 
        if(typeof(window.emosPropertiesEvent) == 'function') {
            window.emosPropertiesEvent(emospro);
        }
    }
}

// stristr helper function
function stristr(haystack,needle,bool)
{
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
