/*************************************
 This displays a notification of the type specified. 
 It is located wherever the following is: <div id="error_message"></div>
 NOTE: you cannot display more than one error at a time. The second one replaces the earlier ones
**************************************/
function displayNotification(type, message, duration = 3500) {
	var commonStyle = "alert border alert-success border-success border-4 fw-bold w-50 mt-3";
	if (duration == "") duration = 3500;
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

/********************************
 Validates Patron input before patron record is updated.
 Returns T/F and also calls "displayNotification" for errors.
 Called from patronEdit.php and patronAdd.php
********************************/
function validatePatronForm() {
	const inputs = ["firstname", "lastname", "birthdate", "address", "city", "prov", "postalCode"];

	//Make sure all the the inputs are filled. This is actually done by the "required" attribute in <input>
	let retval = true;
	inputs.forEach( function(input) {
		let element = document.getElementById(input);
		if(element.value === "") {
			element.className = "form-control is-invalid";
			retval = false;
		} else {
			element.className = "form-control is-valid";
		}
	});
	if (retval === false) {
		displayNotification("error", "Missing input");
		return false;
	}

	//validate email if it exists
	const email = document.getElementById("email");
	let emailText = email.value.trim();
	//if (emailText.length > 0) {
	if (emailText != "") {
		let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		if(! emailText.match(mailformat)) {
			email.className = "form-control is-invalid";
			displayNotification("error", "Email is invalid");
			return false;
		} 		
	}

	//validate PROV.
	const prov = document.getElementById("prov");
	let provText = prov.value.trim().toUpperCase();
	if (! provText.match('^[A-Z]{2}$')) {
		prov.className = "form-control is-invalid";
		displayNotification("error", "Province is invalid");
		return false;
	}
	prov.value = provText;

	//Rudimentary validation of postal code. It must be 5 or 6 charactes long. (US or Canada).
	const postal = document.getElementById("postalCode");
	let postalText = postal.value.trim().toUpperCase();
	if (postalText.length == 5 && Number.isInteger(1*postalText)) return true;   //valid US code
	//check Canadian code. (1) remove all spaces and make it uppercase (2) it must be 6 characters long
	postalText = postalText.replace(/\s/g, '');
	if (postalText.length != 6) {
		postal.className = "form-control is-invalid";
		displayNotification("error", "Postal Code is invalid");
		return false;
	}
	postal.value = postalText; //uppercased with spaces removed, to submit to PHP.
	console.log(postalText);


/*  //FIXME this does not work to validate phone numbers!
	let regex = '^[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$';
	const phone = document.getElementById("phone");
	phone.className = "form-control is-valid";
	let phoneText = prov.value.trim();
	if (! phoneText.match(regex)) {
		phone.className = "form-control is-invalid";
		document.getElementById("error_message").innerHTML = "Invalid phone number format";
		return false;
	}
*/

	return true;
}
