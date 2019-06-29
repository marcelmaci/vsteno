<?php 

require_once "constants.php";

function AddError($error) {
    global $global_error_string;
    $global_error_string .= "$error<br>";
}

function AddWarning($warning) {
    global $global_warnings_string;
    $global_warnings_string .= "$warning<br>";
}

function GetErrorSection() {
    global $global_error_string;
    if (mb_strlen($global_error_string)==0) return "";
    else return "<h2>ERRORS</h2><p>$global_error_string</p>";
}

function GetWarningSection() {
    global $global_warnings_string;
    if (mb_strlen($global_warnings_string)==0) return "";
    else return "<h2>WARNINGS</h2><p>$global_warnings_string</p>";
}

function GetErrorAndWarningSection() {
    $errors = GetErrorSection();
    $warnings = GetWarningSection();
    return $errors . $warnings;
}

?>
