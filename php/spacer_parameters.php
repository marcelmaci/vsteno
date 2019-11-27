<?php require "vsteno_template_top.php"; ?>
<h1>Spacer</h1>
<h2>Parameters</h2>

<h2>spacer_vowel_groups:</h2>
<?php echo "<p>" . $_SESSION['spacer_vowel_groups'] . "</p>"; ?>

<h2>spacer_rules_list</h2>
<?php echo "<p>" . $_SESSION['spacer_rules_list'] . "</p>"; ?>

<h2>token_combinations:</h2>
<?php echo "<p>" . $_SESSION['spacer_token_combinations'] . "</p>";

  if (!$_SESSION['output_without_button_yesno']) {
        echo '<br><a href="' . $_SESSION['return_address'] . '"><button>zurück</button></a><br><br>';   
  }
?>

<?php require "vsteno_template_bottom.php"; ?>
