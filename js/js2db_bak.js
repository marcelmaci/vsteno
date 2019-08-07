
// JSON / PHP XMLHTTPRequest export / import to / from database

function writeDataToDB() {
	var data = JSON.stringify(actualFont);
	console.log("data: ", data, actualFont);
	var request = new XMLHttpRequest();
	request.open("POST", "importfromjs.php", true);

	request.setRequestHeader("Content-Type", "application/json");

	request.onreadystatechange = function() {
		if (request.readyState == 4 && request.status == 200) {
			console.log("it worked");
			// it worked: response if needed
			//var resp = request.responseText;
		} 
    }
	request.send(data);
}
