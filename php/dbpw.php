<?php
/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
// contains login and password for database
const db_servername = "127.0.0.1";
const db_username = "";
const db_password = "";
const db_dbname = "";

const master_pwhash = "ee79976c9380d5e337fc1c095ece8c8f22f91f306ceeb161fa51fecede2c4ba1";

// privileges
const normal_user = 1;          // can write to purgatorium
const super_user = 2;           // can write to elysium

// connects to database
function Connect2DB() {
    return new mysqli(db_servername, db_username, db_password, db_dbname);
}

function connect_or_die() {
        // Create connection
        $conn = Connect2DB();
        // Check connection
        if ($conn->connect_error) {
            die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
        }
        return $conn;
}

function GetPurgatoriumDBName() {
    switch ($_SESSION['model_standard_or_custom']) {
        case "standard" : return "purgatorium"; breakt;
        case "custom" : 
            $purgatorium = "XP" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
            return $purgatorium;
        default : return "error"; break;
    }
}

function GetElysiumDBName() {
    switch ($_SESSION['model_standard_or_custom']) {
        case "standard" : return "elysium"; breakt;
        case "custom" : 
            $elysium = "XE" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
            return $elysium;
        default : return "error"; break;
    }
}

?>