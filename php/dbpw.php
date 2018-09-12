<?php

// contains login and password for database
const db_servername = "127.0.0.1";
const db_username = "";
const db_password = "";
const db_dbname = "";

// privileges
const normal_user = 1;          // can write to purgatorium
const super_user = 2;           // can write to elysium

// connects to database

function Connect2DB() {
    return new mysqli(db_servername, db_username, db_password, db_dbname);
}

?>