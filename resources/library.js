/* This displays a notification of the type specified. 
   It is located wherever the following is: <div id="error_message"></div>
   NOTE: you cannot display more than one error at a time. The second one replaces the earlier ones
*/
function displayNotification(type, message, duration = 3500) {
	var commonStyle = "alert border alert-success border-success border-4 fw-bold w-50 mt-3";
	var text;
	switch(type){
	case "success":
		text = `<div id="notif_msg" class="${commonStyle}"><i class="h3 fa fa-check"></i> SUCCESS: ${message}</div>`;
		break;
	case "info":
		commonStyle = commonStyle.replaceAll("success","primary");
		text = `<div id="notif_msg" class="${commonStyle}"><i class="h3 fa fa-comment-dots"></i> INFO: ${message}</div>`;
		break;
	case "warning":
		commonStyle = commonStyle.replaceAll("success","warning");
		text = `<div id="notif_msg" class="${commonStyle}"><i class="h3 fa fa-triangle-exclamation"></i> WARNING: ${message}</div>`;
		break;
	case "error":
	default:
		commonStyle = commonStyle.replaceAll("success","danger");
		text = `<div id="notif_msg"  class="${commonStyle}"><i class="h3 fa fa-sack-xmark"></i> ERROR: ${message}</div>`;
		break;
	}
	var container = document.getElementById("notif_container");
	document.getElementById("notif_container").innerHTML = text;
	/**** uncomment next line to make popup hover above page *****/
	//document.getElementById("notif_container").classList.add("hover");
    //for multiple notifications, make these nodes
	//document.getElementById("error_message").appendChild(text);
	const notification = document.getElementById("notif_msg");
	const timeout = setTimeout(() => { container.removeChild(notification); }, duration);
}

