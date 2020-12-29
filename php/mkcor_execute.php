<?php require "vsteno_template_top.php"; ?>

<h1>Korpus</h1>
<p>

<?php

function FilterAll($text, $filter) {
     // filter out tags: <...>
     $output = preg_replace( "/<.*?>/", "", $text);
     // filter out filter
     $escaped = preg_quote($filter);
     $output = preg_replace( "/[$escaped]/", "", $output);
     // filter out ' ...
     // as always ... for un unknown reason, ' doesn't get filtered out ... this escaping mumbo jumbo sucks ... do it here separately ...
     $output = preg_replace("/'/", "", $output);
     return $output;
}

function StringArrayToAssociative($array) {
        foreach ($array as $element) {
                if (!isset($output[$element])) {
                    $output[$element] = 1;
                } else $output[$element]++; // maybe later for statistics
        }
        return $output;
}

// get data
$text = isset($_POST['original_text']) ? $_POST['original_text'] : "";
$exclude = isset($_POST['exclude']) ? $_POST['exclude'] : "";
$filter = isset($_POST['filter']) ? $_POST['filter'] : "";

// make copy to post back with back button
$cp_filter = $filter;
$cp_text = $text;
$cp_exclude = $exclude;


// apply filter
$text = FilterAll($text, $filter);
$exclude = FilterAll($exclude, $filter);

// explode to arrays
$text = explode(" ", $text);
$exclude = explode(" ", $exclude);

// convert to associative arrays
$text = StringArrayToAssociative($text);
$exclude = StringArrayToAssociative($exclude);

// delete excludes in $text => $text becomes corpus
foreach ($text as $key => $element) 
    if (isset($exclude[$key])) unset($text[$key]);

// print results
foreach ($text as $key => $value) echo "$key ";

?>

</p>

<form action="mkcor.php" method="post">
    <input type="hidden" id="original_text" name="original_text" value="<?php echo $cp_text ?>"> 
    <input type="hidden" id="exclude" name="exclude" value="<?php echo $cp_exclude ?>"> 
    <input type="hidden" id="filter" name="filter" value="<?php echo $cp_filter ?>"> 
    <input type="submit" name="action" value="zur&uuml;ck">
</form>

<?php require "vsteno_template_bottom.php"; ?>
