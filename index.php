<?php
/**
 * Library Stack Management System (LSMS)
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

/**
 * LSMS Index
 *
 * Outputs the front-end interface.  User will be scanning books
 * and it will use ajax calls to populate data on the screen and
 * also interaction with background databases.
 *
 * @package		LSMS
 * @author		Nackil Sung, Erin Kim
 * @since		Version 1.0.2
 */

// Authentication File
include_once 'shared/Auth.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=iso-8859-1">
  <title>Library Stacks Management System</title>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="css/reset.css" type="text/css">
  <link rel="stylesheet" href="css/lsms.css" type="text/css">
  <!--<script src="http://code.jquery.com/jquery.min.js" type="text/javascript"></script>-->
  <script src="js/jquery.js" type="text/javascript"></script>


<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.3/themes/base/jquery-ui.css" type="text/css" media="all" />
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.3/jquery-ui.min.js" type="text/javascript"></script>
<script language=javascript type=text/javascript>
$(document).ready(function() {




});

function tester() {

  errfound_ready.play();
}

var sound_ready = new Audio("sounds/Ready.wav");
var sound_error = new Audio("sounds/Error.wav");

var vcounter = 0;
var vvalidcnt = 0;
var vstartLoc="";
var vstartLocID = new Array();
var visNewLoc = "";
var vmycookie;
var vfixed_cookie;
var vthe_pairs;
var vpair1;
var vpair2;
var vpair3;
var vnamevalue1;
var vnamevalue2;
var vnamevalue3;
var vpatlenbar;
var vpatprebar;
var vpatlenprebar;
var vCloseDialog=false;
var vRet;
var vRet_pairs;
var vItemBar;
var vItemID;
var vStatusID;
var vStatus;
var vLocID;
var vLoc;
var vCallNorm;
var vCallDisp;
var vEnum="";
var vPreEnum="";
var vCopynum="";
var vPreCopynum="";
var vSession;
var vIsWriteSessionInfo=false;

function aKeyWasPressed(e) {
  if (window.event) {
    var key = window.event.keyCode;
  }
  else {
    var key=e.which;
  }

  var vBarcode;
  if (key==13) {
    vBarcode = document.getElementById('barcode').value;
    //vBarcode = vBarcode.replace(/^\s+|\s+$/g,"");
    vBarcode = vBarcode.replace(" ","");
    vcounter = vcounter + 1;
    document.getElementById('counter').innerHTML=vcounter;
	var barlen = $("#LenBar").text();
    var prefix = $("#PreBarcode").text();

    if (vBarcode.length>0) {
      if (String(vBarcode).substring(0,prefix.length) != prefix && document.getElementById('patcheck').checked) {
        EvalSound('error');
        changeBG(4);
        alert ("Prefix doesn't match!");
        EvalSound('ready');
        changeBG(3);
        document.getElementById('barcode').value = "";
      }
      else if (vBarcode.length != barlen && document.getElementById('patcheck').checked) {
        EvalSound('error');
        changeBG(4);
        alert ("Incorrect barcode length!");
        EvalSound('ready');
        changeBG(3);
        document.getElementById('barcode').value = "";
      }
      else {
        vIsWriteSessionInfo = false;
        ajaxFunction(vBarcode);
      }
    }
  }  //if return key process the barcode!
}



function initializeVar() {
	vItemBar="";
	vItemID="";
	vCallDisp="";
	vEnum="";
	vLocID="";
	vCopynum = "";
	vStatus="";
	vCallNorm="";
	vAnsWrongOrder="";
	visNewLoc = "";
}

function defaultfocus() {
  var clickedObject = $(document.activeElement).attr("id");
  if(clickedObject == "edbarlen" || clickedObject == "edprefix" || clickedObject == "edbtn") {
    // Do nothing
  }
  else {
    document.getElementById('barcode').focus();
  }
}

function EvalSound(soundobj) {

  switch(soundobj) {
    case "ready":
      sound_ready.play();
      break;
    case "error":
      sound_error.play();
      break;
  }

}


var backColor = new Array();

backColor[0] = '#FF0000';  //Red   - wrong order
backColor[1] = '#FFFF00';  //Yellow - not found
backColor[2] = '#00FF00';  //Green  - active status
backColor[3] = '#FFF';  //nomal
backColor[4] = '#0000FF';  //Blue - Scan Error: barcode length, prefix

function changeBG(whichColor){
  $("body").css("background-color", backColor[whichColor]);
  console.log("I am in in changeBG ");
  console.log(whichColor);

}

function set_mycookie(plen, pprefix) {
  document.cookie="LenBar="+plen+"&PreBarcode="+pprefix+"&LenPreBar="+pprefix.length+"&dummy=1;expires=Mon, 1 Feb 2016 13:00:00 UTC;";
  alert("set");
}

function clearText(thefield){
	if (thefield.defaultValue == thefield.value) {
    thefield.value = "";
  }
}

function hideinput() {
  document.getElementById('edbarlen').style.visibility='hidden';
  document.getElementById('edprefix').style.visibility='hidden';
  document.getElementById('lblLen').style.visibility='hidden';
  document.getElementById('lblPre').style.visibility='hidden';
  document.getElementById('edbtn').style.visibility='hidden';
}

function enableedit() {
  document.getElementById('edbarlen').style.visibility='visible';
  document.getElementById('edprefix').style.visibility='visible';
  document.getElementById('lblLen').style.visibility='visible';
  document.getElementById('lblPre').style.visibility='visible';
  document.getElementById('edbtn').style.visibility='visible';
}

function updatepattern() {

  var barlen = $("#edbarlen").val();
  var prefix = $("#edprefix").val();

  $.ajax({
    type: "POST",
    url: "ajax/updateParameters.php",
    data: { barlength : barlen, prefix : prefix }
  }).done(function( msg ) {
    alert(msg);
  });

  document.getElementById('LenBar').innerHTML = barlen;
  document.getElementById('PreBarcode').innerHTML = prefix;

  document.getElementById('edbarlen').style.visibility='hidden';
  document.getElementById('edprefix').style.visibility='hidden';
  document.getElementById('lblLen').style.visibility='hidden';
  document.getElementById('lblPre').style.visibility='hidden';
  document.getElementById('edbtn').style.visibility='hidden';
}

function updateLocations() {
  set_mycookie(document.getElementById('edbarlen').value,document.getElementById('edprefix').value);
}




function isWrongStatus() {
	if (vStatusID == 1 || vStatusID == 11) {
    return false;
	}
  else {
    return true;
	}
}

function isWrongLoc() {
  var ans;
  for (var i=0; i < vstartLocID.length ; i++) {
    if (vstartLocID[i] == vLocID) {
      return false;
    }
  }

  changeBG(1);
  EvalSound('error');
  if(confirm("Location Unknown! *** " + vLoc + " *** \nIs this a Valid location?")) {
    vstartLocID[vstartLocID.length] = vLocID;
    document.getElementById("Location").innerHTML=document.getElementById("Location").innerHTML + ";" + vLoc;
    visNewLoc = "Y";
    return false;
  }
  else {
  return true;
  }
}

function getNumber(sText) {
  var ValidChars = "0123456789";
  var IsNumber=true;
  var Char;
  var startNum = false;
  var endNum = false;
  var myNum="";
  for (i = 0; i < sText.length && endNum == false; i++) {
    Char = sText.charAt(i);
    if (ValidChars.indexOf(Char) == -1) {
      if (startNum == true) {
        endNum = true;
      }
    }
    else {
      myNum = myNum+Char;
      startNum = true;
    }
  }
  return parseFloat(myNum);
}

function isWrongOrder() {
	var CurrCall = document.getElementById('currentCall').value;
	if (CurrCall < vCallNorm) {
    return false;
	}
  else if (CurrCall == vCallNorm) {
  if (getNumber(vPreEnum) < getNumber(vEnum)){
    return false;
    } else if (getNumber(vPreEnum) > getNumber(vEnum)) {
      return true;
    }
    else {
      if (getNumber(vPreCopynum) <= getNumber(vCopynum)) {
        return false;
      }
      else {
        return true;
      }
    }
  }
  else {
    return true;
  }
}

function Finish() {
  var v1 = document.getElementById('ValidCNT').innerHTML;
  if (v1 >= 2 && !vIsWriteSessionInfo) {
    ajaxFunction4();
  }
}

function createsession() {
  document.getElementById('firstCall').value=""
  document.getElementById('currentCall').value=""
  ajaxFunction3();
  document.getElementById('barcode').focus();
}

function ajaxFunction(str) {
  var xmlHttp;
  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp=new XMLHttpRequest();
  }
  catch (e) {
    // Internet Explorer
    try {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e) {
      try {
        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e) {
        alert("Your browser does not support AJAX!");
        return false;
      }
    }
  }

  if (str.length==0) {
    document.getElementById("LSMS").innerHTML="";
    return;
  }

  var url = "ajax/LSMS.php";
  url = url + "?barcode="+ str;
  xmlHttp.onreadystatechange=function() {
    if(xmlHttp.readyState==4) {
      vRet = xmlHttp.responseText;
      vRet_pairs = vRet.split("~");
      vItemBar = vRet_pairs[0];
      vItemID = vRet_pairs[1];
      vStatusID = vRet_pairs[2];
      vStatus = vRet_pairs[3];
      vLocID = vRet_pairs[4];
      vLoc = vRet_pairs[5];
      vCallNorm = vRet_pairs[6];
      vCallDisp = vRet_pairs[7];
      vEnum = vRet_pairs[8];
      vCopynum = vRet_pairs[9];
      vStr = "";
      vStrStatus = "";

      if (vItemID == "") {
        changeBG(1);
        EvalSound('error');
        //alert("Not Found");
        document.getElementById("LSMS").innerHTML= "** Not Found ** " + vItemBar+ "\<br\>" + document.getElementById("LSMS").innerHTML;
        vStr = "?barcode="+vItemBar+"&Session="+vSession+"&Status=N";
        ajaxFunction2(vStr);
      }
      else if (vItemID==null) {
        changeBG(1);
        EvalSound('error');
        //alert("Not Found.\n\n  Try again with other items.  If you experience the same error, check the network connection.");
        document.getElementById("LSMS").innerHTML= "** Not Found ** " + vItemBar+ "\<br\>" + document.getElementById("LSMS").innerHTML;
      }
      else {
        if (vstartLoc == "") {
          EvalSound('ready');
          vstartLoc = vLoc;
          visNewLoc = "Y";
          vstartLocID[vstartLocID.length] = vLocID;
          //alert("First Scanned Item.\n\nLocation: "+vstartLoc);
          document.getElementById("Location").innerHTML=vstartLoc;
          vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession;
        }

        if (isWrongStatus()) {
          vvalidcnt=vvalidcnt + 1;

          if (vvalidcnt==1) {
            document.getElementById('firstBar').innerHTML=vItemBar;
            document.getElementById('firstCall').value=vCallNorm;
          }
          else {
            document.getElementById('currentBar').innerHTML=vItemBar;
            document.getElementById('currentCall').value=vCallNorm;
          }

          vPreEnum = vEnum;
          vPreCopynum = vCopynum;
          changeBG(2);
          EvalSound('error');
          //alert("Active status item!\n\n"+vStatus);
          vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession+"&Status=A"
          ajaxFunction2(vStr);
          document.getElementById("LSMS").innerHTML= "** Wrong Status ** " + vItemBar+" "+ vStatus+" " + vLocID+" " + vCallDisp+" " + vEnum+" " + vCopynum +"\<br\>" + document.getElementById("LSMS").innerHTML;
        }
        else if (isWrongLoc()) {
          vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession+"&Status=L"
          ajaxFunction2(vStr);
          document.getElementById("LSMS").innerHTML= "** Wrong Location ** " + vItemBar+" "+ vStatus+" " + vLocID+" " + vCallDisp+" " + vEnum+ " " + vCopynum + "\<br\>" + document.getElementById("LSMS").innerHTML;
        }
        else if (isWrongOrder()) {
          vvalidcnt=vvalidcnt + 1;
          changeBG(0);
          EvalSound('error');

          if (vvalidcnt == 2) {
            document.getElementById('firstBar').innerHTML=vItemBar;
            document.getElementById('firstCall').value=vCallNorm;
            document.getElementById("LSMS").innerHTML= "** Wrong Order ** " + vItemBar+" "+ vStatus+" " + vLocID+" " + vCallDisp+" " + vEnum+" "+vCopynum+ "\<br\>" + document.getElementById("LSMS").innerHTML;
            vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession+"&Status=W"   //Uppercase!!!
            ajaxFunction2(vStr);
          }
          else { //wrong order after three or more scans
            document.getElementById('dialogWrongOrder').innerHTML = "Current Item<br />Barcode: " + vItemBar + "<br />Call Num: " + vCallDisp+ "<br />Enum: " + vEnum+"<br />Copy Num: " + vCopynum;
            var doPrevious = function() {
              vCloseDialog = true;
              document.getElementById('currentCall').value = vCallNorm;
              document.getElementById('currentBar').value = vItemBar;
              document.getElementById("LSMS").innerHTML= vItemBar+" "+ vStatus+" " + vLocID+" " + vCallDisp+" " + vEnum+" " + vCopynum+ "\<br\>** Wrong Order ** " + document.getElementById("LSMS").innerHTML;
              vPreEnum = vEnum;
              vPreCopynum = vCopynum;
              vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession+"&Status=w"           //Lowercase !!!
              ajaxFunction2(vStr);
              $('#dialogWrongOrder').dialog("close");
            }
            var doCurrent = function() {
              vCloseDialog = true;
              document.getElementById("LSMS").innerHTML= "** Wrong Order ** " + vItemBar+" "+ vStatus+" " + vLocID+" " + vCallDisp+" " + vEnum+" " + vCopynum+ "\<br\>" + document.getElementById("LSMS").innerHTML;
              vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession+"&Status=W"           //Uppercase !!!
              ajaxFunction2(vStr);
              $('#dialogWrongOrder').dialog("close");
            }
            var doIgnore = function() {
              vCloseDialog = true;
              document.getElementById('currentCall').value = vCallNorm;
              document.getElementById('currentBar').value = vItemBar;
              document.getElementById("LSMS").innerHTML= vItemBar+" "+ vStatus+" " + vLocID+" " + vCallDisp+" " + vEnum+" " + vCopynum+ "\<br\>** Wrong Order ** " + document.getElementById("LSMS").innerHTML;
              vPreEnum = vEnum;
              vPreCopynum = vCopynum;
              vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession+"&Status=w"           //Lowercase !!!
              ajaxFunction2(vStr);
              $('#dialogWrongOrder').dialog("close");
            }
            var dialogOpts = {
              modal: true,
              buttons: {"Current item is out of order.": doCurrent, "Previous item is out of order.": doPrevious, "Ignore": doIgnore },
              beforeclose: function() {if(!vCloseDialog){return false} else {vCloseDialog=false;document.getElementById('ValidCNT').innerHTML=vvalidcnt;document.getElementById('barcode').value = "";changeBG(3);EvalSound('ready');defaultfocus();initializeVar();}},
              autoOpen: false
            };
            $('#dialogWrongOrder').dialog(dialogOpts);
            $('#dialogWrongOrder').dialog('open');
            return;
          }
        }
        else { // when no error
          vvalidcnt=vvalidcnt + 1;

          if (vvalidcnt==1){
            document.getElementById('firstBar').innerHTML=vItemBar;
            document.getElementById('firstCall').value=vCallNorm;
            document.getElementById('currentBar').innerHTML=vItemBar;
            document.getElementById('currentCall').value=vCallNorm;
          }
          else {
            document.getElementById('currentBar').innerHTML=vItemBar;
            document.getElementById('currentCall').value=vCallNorm;
          }

          vPreEnum = vEnum;
          vPreCopynum = vCopynum;
          changeBG(3); //added sg
          vStr = "?Loc="+vLoc+"&barcode="+vItemBar+"&ItemID="+vItemID+"&CallNum="+vCallDisp+"&ENum="+vEnum+"&Copynum="+vCopynum+"&LocID="+vLocID+"&ActiveStatusID="+vStatusID+"&Session="+vSession;
          document.getElementById("LSMS").innerHTML= vItemBar+" "+ vStatus+" " + vLocID+" " + vCallDisp+" " + vEnum+" " + vCopynum+ "\<br\>" + document.getElementById("LSMS").innerHTML;
          ajaxFunction2(vStr);
        }

        document.getElementById('ValidCNT').innerHTML=vvalidcnt;
      }

      document.getElementById('barcode').value = "";
      //changeBG(3);
      EvalSound('ready');
      defaultfocus();
      initializeVar();
    }
  }

  xmlHttp.open("GET",url,true);
  xmlHttp.send(null);
}

function ajaxFunction2(str) {
  var xmlHttp2;
  var v1 = document.getElementById('firstCall').value;
  var v2 = document.getElementById('currentCall').value;
  var v3 = vcounter;
  var v4 = vvalidcnt;
  var v5 = document.getElementById('firstBar').innerHTML;
  var v6 = document.getElementById('currentBar').innerHTML;
  var strLocal = "&firstCall="+v1+"&currentCall="+v2+"&counter="+v3+"&validcnt="+v4+"&firstBar="+v5+"&currentBar="+v6+"&NewLoc="+visNewLoc;

  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp2=new XMLHttpRequest();
  }
  catch (e) {
    // Internet Explorer
    try {
      xmlHttp2=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e) {
      try {
        xmlHttp2=new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e) {
        alert("Your browser does not support AJAX!");
        return false;
      }
    }
  }

  var 	url = "ajax/LSMSLog.php";
  url = url + str + strLocal;
  xmlHttp2.onreadystatechange=function() {
    if(xmlHttp2.readyState==4) {
      var locRet = xmlHttp2.responseText;

      if(locRet != 0){ //if error!
        alert("Error Code: "+locRet);
      }
    }
  }
  xmlHttp2.open("GET",url,true);
  xmlHttp2.send(null);
}

function ajaxFunction3() {
  var xmlHttp2;
  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp2=new XMLHttpRequest();
  }
  catch (e) {
    // Internet Explorer
    try {
      xmlHttp2=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e) {
      try {
        xmlHttp2=new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e) {
        alert("Your browser does not support AJAX!");
        return false;
      }
    }
  }

  var d = new Date();
  var mytime = "?my=" + d.getMinutes() + d.getSeconds();
  var 	url = "ajax/newSession.php";
  url = url + mytime;
  xmlHttp2.onreadystatechange=function() {
    if(xmlHttp2.readyState==4) {
      var locRet = xmlHttp2.responseText;
      var locRet_pairs = locRet.split("~");
      var locErr = locRet_pairs[0];
      vSession = locRet_pairs[1];
      var vLenBar = locRet_pairs[2];
      var vPrefix = locRet_pairs[3];

      if(locErr!=0) { //if error!
        alert("Error Code: "+locErr);
      }
      else {
        document.getElementById('session').innerHTML = vSession;
        document.getElementById('LenBar').innerHTML = vLenBar;
        vpatlenbar = vLenBar;
        document.getElementById('PreBarcode').innerHTML = vPrefix;
        vpatprebar = vPrefix;
        vpatlenprebar = vPrefix.length
      }
    }
  }
  xmlHttp2.open("GET",url,true);
  xmlHttp2.send(null);
}

function ajaxFunction4() {
  var sessionID = document.getElementById('session').innerHTML;
  var url = "ajax/processNotOnShelfItemsSilent.php";
  $.ajax({
    type: "GET",
    url: url,
    data: { sessionID: sessionID }
  }).done(function( msg ) {
    alert(msg);
  });

}

</script>
</head>

<body onload="hideinput();defaultfocus();" onclick="defaultfocus();">

<div id="home-title">
  <h1><img src="img/home-title.png" alt="Library Stacks Management System" title="Library Stacks Management System" ></h1>
</div>
<div id="nav">
  <ul>
    <li><a href="index.php">LSMS Home</a></li>
    <li><a href="sessionmanager">Session Manager</a></li>
    <li><a href="locationmanager">Location Manager</a></li>
  </ul>
</div>
<div id="home-container">
  <div style="font-size:80%;" id="dialogWrongOrder" title="Out of Order!"></div>
  <div style="font-size:80%;" id="dialogWrongLocation" title="Location Conflict!"></div>
  <p>Scan Barcode: <input id="barcode" type="text" size="40"  onkeyup="aKeyWasPressed(event);" name="barcode" />&nbsp;&nbsp;&nbsp;<label id='Location'></label></p>
  <br />
  <div id="LSMS" ></div><br />
  <table><tr><td colspan=3>
  Session ID: <label id='session'></label></td></tr><td colspan=3>Total Scan: <label id="counter">0</label>&nbsp;&nbsp;&nbsp;Valid Scan: <label id="ValidCNT">0</label></td><td></td></tr>
  <tr><td colspan=3>First Item: <label id="firstBar"></label>&nbsp;&nbsp;&nbsp;<input id="firstCall" disabled="disabled" value=""></td></tr>
  <tr><td colspan=3>Last Item: <label id="currentBar"></label>&nbsp;&nbsp;&nbsp;<input id="currentCall" disabled="disabled" value=""></td></tr>
  <tr><td colspan=3><br /><font size=-1>At the end of the session:<br /><span href="" class="button medium green" style="margin:10px 0" id="finish-btn" onClick="Finish();">Generate not-on-shelf items</span> <br />Then, close the browser.</font><BR><BR></td></tr>
  <tr><td colspan=3>Barcode Pattern: </td></tr>
  <tr><td>Length: <label id="LenBar"></label></td><td>Prefix: <label id="PreBarcode"></label></td><td ><input type="checkbox" id="patcheck" checked="check" >check the pattern for every scan</td></tr>
  <tr><td colspan=6><span href="" class="button white medium" onClick="enableedit();" />Change Barcode Pattern</span></td></tr>
  </table>
  <label id='lblLen'>Barcode length:</label><input  id='edbarlen' size='2'> <label id='lblPre'>Prefix: </label><input id='edprefix' size='14'><input type='button' value='Update' id='edbtn' onclick="updatepattern();">

</div>
</body>
<script language=javascript type=text/javascript>
  createsession();
</script>
</html>
