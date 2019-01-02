
// contains data operations for vsteno-editor (import / export etc.)

var tokenPullDownSelection = [];
tokenPullDownSelection.push("-select-");

function addNewTokenToPullDownSelection(token) {
	tokenPullDownSelection.push(token);
	var optionList = "";
	for (var i=0;i<tokenPullDownSelection.length; i++) {
		optionList += "<option value=\"" + tokenPullDownSelection[i] + "\">" + tokenPullDownSelection[i] + "</option>\n";
	}
	var element = document.getElementById("tokenpulldown");
	element.innerHTML = optionList;
}
