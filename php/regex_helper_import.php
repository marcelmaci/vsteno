<?php

require_once "data.php";

// creates (imports) token groups from font section (loding actual model via data.php)
$token_groups = array();
foreach ($steno_tokens_master as $token => $definition) {
    $group = $definition[23];
    if (($group !== 0) && (mb_strlen($group)>0)) {
        $group_array = explode(":", $group); // same token can be attributed to different groups
        foreach ($group_array as $group_name) $token_groups[$group_name][] .= $token;   
    }
}

?>