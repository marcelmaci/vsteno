<?php 
require_once "dbpw.php";
require_once "constants.php";

function create_tables() {
  global $conn;
    // sql to create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(30),
    realname VARCHAR(30),
    newsletter INT(1),
    salt VARCHAR(10),
    pwhash VARCHAR(80),
    privilege INT(1),
    visibility_model INT(1),
    visibility_database INT(1),
    last_activity DATE,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table users created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create purgatorium table
    // add entry for trickster (= REGEX entry that checks for declined or conjugated forms like: Zelt => Zelte, Zelten, Zeltes, Zelts etc.
    $sql = "CREATE TABLE IF NOT EXISTS ZPDESSBAS (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    std VARCHAR(50),
    prt VARCHAR(50),
    composed VARCHAR(50),
    result CHAR(1),
    user_id INT(6),
    comment VARCHAR(250),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table purgatorium created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create elysium table
    // add entry for trickster (= REGEX entry that checks for declined or conjugated forms like: Zelt => Zelte, Zelten, Zeltes, Zelts etc.
    $sql = "CREATE TABLE IF NOT EXISTS ZEDESSBAS (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    number_forms INT(1),
    recommended_form INT(1),
    submitted_by INT(6),
    reviewed_by INT(6),
    single_bas VARCHAR(50),
    single_std VARCHAR(50),
    single_prt VARCHAR(50),
    separated_bas VARCHAR(50),
    separated_std VARCHAR(50),
    separated_prt VARCHAR(50),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table elysium created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create olympus table
    $sql = "CREATE TABLE IF NOT EXISTS ZODESSBAS (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    number_forms INT(1),
    recommended_form INT(1),
    submitted_by INT(6),
    reviewed_by INT(6),
    single_bas VARCHAR(50),
    single_std VARCHAR(50),
    single_prt VARCHAR(50),
    separated_bas VARCHAR(50),
    separated_std VARCHAR(50),
    separated_prt VARCHAR(50),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table elysium created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create models table
    $sql = "CREATE TABLE IF NOT EXISTS models (
    model_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6),
    name VARCHAR(50),
    header MEDIUMTEXT,
    font LONGTEXT,
    rules LONGTEXT,
    modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table models created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    /// do the same for spanisch tables
    // sql to create purgatorium table
    // add entry for trickster (= REGEX entry that checks for declined or conjugated forms like: Zelt => Zelte, Zelten, Zeltes, Zelts etc.
    $sql = "CREATE TABLE IF NOT EXISTS ZPSPSSBAS (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    std VARCHAR(50),
    prt VARCHAR(50),
    composed VARCHAR(50),
    result CHAR(1),
    user_id INT(6),
    comment VARCHAR(250),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table purgatorium created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create elysium table
    // add entry for trickster (= REGEX entry that checks for declined or conjugated forms like: Zelt => Zelte, Zelten, Zeltes, Zelts etc.
    $sql = "CREATE TABLE IF NOT EXISTS ZESPSSBAS (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    number_forms INT(1),
    recommended_form INT(1),
    submitted_by INT(6),
    reviewed_by INT(6),
    single_bas VARCHAR(50),
    single_std VARCHAR(50),
    single_prt VARCHAR(50),
    separated_bas VARCHAR(50),
    separated_std VARCHAR(50),
    separated_prt VARCHAR(50),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table elysium created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create olympus table
    $sql = "CREATE TABLE IF NOT EXISTS ZOSPSSBAS (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    number_forms INT(1),
    recommended_form INT(1),
    submitted_by INT(6),
    reviewed_by INT(6),
    single_bas VARCHAR(50),
    single_std VARCHAR(50),
    single_prt VARCHAR(50),
    separated_bas VARCHAR(50),
    separated_std VARCHAR(50),
    separated_prt VARCHAR(50),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table elysium created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }


}

////////////////////////////////////////////////////////////////////////////////////////////////////////

function CreateStandardUser() {
global $conn;    
// standard user will have superuser priviledges
    // prepare data
    $safe_username = "standard";
    $safe_email = "";
    $safe_realname = "";
    $safe_infos = 0;

    // check if account exists already
    $sql = "SELECT * FROM users WHERE username='$safe_username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "Username bereits verwendet.<br>";
    } else {
        echo "Username ist noch frei.<br>";
    

    // insert new account in db
    $account_privilege = super_user;
    $salt = "ghEybGJv";
    $account_pwhash = "8118f331c255a3b0fe66496f659182d4827cdbb47e4ee2daf481e7ab4391c7fd";
    $sql = "INSERT INTO users (username, email, realname, newsletter, salt, pwhash, privilege, visibility_model, visibility_database )
    VALUES ( '$safe_username', '$safe_email', '$safe_realname', '$safe_infos', '$salt', '$account_pwhash', '$account_privilege', '0', '0')";

    if ($conn->query($sql) === TRUE) {
        echo "Neues Konto angelegt.<br>";
    } else {
        die_more_elegantly("Fehler: " . $sql . "<br>" . $conn->error . "<br>");
    }

    // get user id
    $sql = "SELECT * FROM users WHERE username='$safe_username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $db_user_id = $row['user_id'];
    } else {
        die_more_elegantly("Username nicht gefunden.<br>");
    }

    // mark user as logged in
    $_SESSION['user_id'] = $db_user_id;    
    $model_name = "XM" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
    
    // create custom databases
    // sql to create purgatorium table
    $purgatorium = "XP" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
    $sql = "CREATE TABLE IF NOT EXISTS $purgatorium (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    std VARCHAR(50),
    prt VARCHAR(50),
    composed VARCHAR(50),
    result CHAR(1),
    user_id INT(6),
    comment VARCHAR(250),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Purgatorium ($purgatorium) angelegt<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create elysium table
    // add entry for trickster (= REGEX entry that checks for declined or conjugated forms like: Zelt => Zelte, Zelten, Zeltes, Zelts etc.
    $elysium = "XE" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
    $sql = "CREATE TABLE IF NOT EXISTS $elysium (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    number_forms INT(1),
    recommended_form INT(1),
    submitted_by INT(6),
    reviewed_by INT(6),
    single_bas VARCHAR(50),
    single_std VARCHAR(50),
    single_prt VARCHAR(50),
    separated_bas VARCHAR(50),
    separated_std VARCHAR(50),
    separated_prt VARCHAR(50),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Elysium ($elysium) angelegt.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create olympus table
    // add entry for trickster (= REGEX entry that checks for declined or conjugated forms like: Zelt => Zelte, Zelten, Zeltes, Zelts etc.
    $olympus = "XO" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
    $sql = "CREATE TABLE IF NOT EXISTS $olympus (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    number_forms INT(1),
    recommended_form INT(1),
    submitted_by INT(6),
    reviewed_by INT(6),
    single_bas VARCHAR(50),
    single_std VARCHAR(50),
    single_prt VARCHAR(50),
    separated_bas VARCHAR(50),
    separated_std VARCHAR(50),
    separated_prt VARCHAR(50),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Olympus ($olympus) angelegt.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function ReadModelFromFile( $filename ) {
    $myfile = fopen( $filename, "r") or die("Unable to open file $filename!");
    $content = fread($myfile,filesize( $filename ));
    fclose($myfile);
//echo "content: $content<br>";
    return $content;
}

function GetSection( $text, $type ) {
    //$pattern = "(#BeginSection\($type\)(?:.|[\n\t\r])*?#EndSection\($type\))";
    //$pattern = "(#BeginSection\($type\))(.|~\R~)*?(#EndSection\($type\))"; // holy sh... ! you have to use this ~\R~ expression in order to match all \v \n \r \t ... I hate escaping and old ASCII incompatibilities ...
    //$replacement = "$1";
    $pattern1 = "#BeginSection($type)";
    $pattern2 = "#EndSection($type)";
    $pos1 = mb_strpos($text, $pattern1);
    $pos2 = mb_strpos($text, $pattern2);
    if (($pos1 !== false) && ($pos2 !== false)) {
        $content = mb_substr($text, $pos1, $pos2-pos1+mb_strlen($pattern2));
    if ($type === "font") {
            // for an unknown reason the font section is always too long and includes beginning of rules section
            // no idea why this happens (probably some multibyte-string-escaping mumbojumbo ...)
            // as a workaround: cut truncated rules section part ...
            echo "correcting font section (cutting truncated rules section ...)<br>";
            $pos_rules = mb_strpos($content, "#BeginSection(rules)");
            $content = mb_substr($content, 0, $pos_rules);
        
    }
    //echo "Pattern: $pattern<br>";
    //$content = preg_replace("/$pattern/", "$replacement", $text);
    //$result = preg_match("/$pattern/", $text, $matches);
    //$content = $matches[1];
    //echo "content: $content<br><br>###<br>";
    //if ($result !== false) return $content;
    //if (mb_strlen($content)>0) return $content;
    return $content; 
    }else echo "Unable to open Section \"$type\"<br>";
}

function CreateModelEntry($model_name) {
    global $conn;
    echo "Create empty model ...<br>";
    $sql = "SELECT * FROM models WHERE name = '$model_name'";
    $result = $conn->query($sql);
    if (($result) && ($result->num_rows > 0)) {
        echo "Model $model_name exists (don't create duplicate)<br>";
    } else { 
        $sql = "INSERT models (user_id, name, header, font, rules) 
        VALUES ('99999', '$model_name', '', '', '');"; // REPLACE means: INSERT IF NOT EXISTS
        //$sql = "INSERT INTO `sys`.`models` (`user_id`, `name`) VALUES ('99999', 'SPSSBAS');
        //INSERT INTO `sys`.`models` (`user_id`, `name`) VALUES ('99999', 'SPSSBAS');
        echo "QUERY: $sql<br>";
        $result = $conn->query($sql);
        if ($result) {
            echo "An empty section $type in model $model_name has been created.<br>";    
        } else {
            //echo "Query: $sql<br>";
            die_more_elegantly("Error creating empty section $type in $model_name<br>");
        }
    }
}

function WriteSection( $data, $model_name, $type ) {
    global $conn;    
    echo "write $model_name => $type<br>";
    $escaped_data = $conn->real_escape_string($data);
    $sql = "UPDATE models
            SET $type = '$escaped_data'
            WHERE name='$model_name';";
    echo "QUERY: $sql<br>";
    $result = $conn->query($sql);

    if ($result) {
        echo "The section $type in model $model_name has been updated.<br>";    
    } else {
        //echo "Query: $sql<br>";
        die_more_elegantly("Error writing section $type in $model_name<br>");
    }
}

function CreateModels() {
    global $standard_models_list;
    //var_dump($standard_models_list);
    foreach ($standard_models_list as $key => $description) {
        echo "create model: $key<br>";
        echo "read data from file ...<br>";
        $model_as_text = ReadModelFromFile( "../ling/$key.txt" );
        $header = GetSection( $model_as_text, "header" );
        $font = GetSection( $model_as_text, "font" );
        $rules = GetSection( $model_as_text, "rules" );
        echo "all data read => write it to database ...<br>";
        // write sections
        CreateModelEntry($key);
        WriteSection( $header, $key, "header" );
        WriteSection( $font, $key, "font" );
        WriteSection( $rules, $key, "rules" );
    }
}

?>
