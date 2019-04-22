<?php require "vsteno_template_top.php"; require_once "session.php"; require_once "captcha.php"; $_SESSION['return_address'] = "show_account_information.php"; $_SESSION['output_format'] = "inline";

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="change_password.php"><br><button>zurück</button></a><br><br>';   
    require_once "vsteno_template_bottom.php";
    die();
}

function RandomString( $length ) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < $length; $i++) {
        $randstring .= $characters[rand(0, strlen($characters))];
    }
    return $randstring;
}

function validate_form_data($po, $pn1, $pn2, $captcha) {
    $valid = false;
    $pn1_length = mb_strlen($pn1);
    if ($po === "") {
        die_more_elegantly("<p>Please enter the old password.</p>");
    } elseif ($pn1 !== $pn2) {
        die_more_elegantly("<p>New passwords are not identical!<br>(Nothing has been changed.)</p>");
    } elseif ($pn1_length < 8) {
        die_more_elegantly("<p>New passwords must have at least 8 characters.</p>");
    } elseif ($captcha !== $_SESSION['captcha']) {
        die_more_elegantly("<p>Wrong captcha! (correct: " . $_SESSION['captcha'] . ")</p>");  
    } else $valid = true;
    return $valid;
}

?>

<br>
<h1>Passwort ändern</h1>

<?php
if ((isset($_SESSION['user_logged_in'])) && ($_SESSION['user_logged_in'])) {

    $conn = Connect2DB();
    
    // Check connection
    if ($conn->connect_error) {
        die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }

    // prepare data
    $safe_username = $conn->real_escape_string($_SESSION['user_username']);
    $safe_password_old = $_POST['password_old'];  // don't escape pw here, because numbers will be escaped
    $safe_password_new1 = $_POST['password_new1'];  // don't escape pw here, because numbers will be escaped
    $safe_password_new2 = $_POST['password_new2'];  // don't escape pw here, because numbers will be escaped
    $safe_captcha = mb_strtolower($_POST['captcha']);
    
    //echo "username: $safe_username<br>pwo: $safe_password_old<br>pwn1: $safe_password_new1<br>pwn2: $safe_password_new1<br>captcha: $safe_captcha<br>";

    if (validate_form_data($safe_password_old, $safe_password_new1, $safe_password_new2, $safe_captcha)) {
        
        // check if account exists and old password is correct
        $sql = "SELECT * FROM users WHERE username='$safe_username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $salt = $row['salt'];
            $safe_pwhash = $conn->real_escape_string(hash( 'sha256', $safe_password_old . $salt ));
    
            if (($safe_username === $row['username']) && ($safe_pwhash === $row['pwhash'])) {
            
                $new_salt = RandomString(8);
                $new_pwhash = $conn->real_escape_string(hash('sha256', $safe_password_new1 . $new_salt));
    
                //echo "new salt: $new_salt<br>new_pwhash: $new_pwhash<br>";
                
                // write new password and last_activity date
                $timestamp = date('Y-m-d G:i:s');
                $sql = "UPDATE users SET last_activity = '$timestamp', salt = '$new_salt', pwhash = '$new_pwhash' WHERE username = '$safe_username';";
                //echo "query: $sql<br>";
                $result = $conn->query($sql);   // test if error!
                if ($result !== true) die_more_elegantly("Error updating database.");
                else { 
                    echo "<p>Password has been changed.</p>";
                    echo '<a href="show_account_information.php"><br><button>zurück</button></a><br><br>';   
                }
            } else {
                //echo "safe_pw_hash: $safe_pwhash<br>";
                die_more_elegantly("Incorrect login data.<br>");
            }
        } else {
            die_more_elegantly("Database error.<br>");
        }
    
    } else {
        die_more_elegantly("<p>Bitte loggen Sie sich zuerst ein!</p>");
    }
}
?>

<?php require "vsteno_template_bottom.php"; ?>