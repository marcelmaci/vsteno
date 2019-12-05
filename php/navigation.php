                <p><b>Program</b></p>
                <ul>
                <li><A href="introduction.php">Einführung</A></li>
                <li><A href="documentation.php">Dokumente</a></li>
                <li><A href="installation.php">Installation</a></li>
                <li><A href="versions.php">Versionen</a></li>
                <li><A href="copyright.php">Copyright</a></li>
                <li><A href="donation.php">Spende</a></li>
                </ul>
                <p><b>Konto</b></p>
                <ul>
                <li><A href="create_account.php">Anlegen</A></li>
                <li><A href="login.php">Einloggen</A></li>
                <li><A href="logout.php">Ausloggen</A></li>
                <li><A href="delete_account.php">Löschen</A></li>
                </ul>
                <p><b>Stenografie</b></p>
                <ul>
                <li><A href="thoughts.php">Gedanken</A></li>
                <li><A href="links.php">Wegweiser</A></li>
                </ul>
                <p><b>Kontakt</b></p>
                <ul>
                <li><A href="collaborate.php">Mitmachen!</A></li>
                <li><A href="mailto:m.maci@gmx.ch"><img src="../web/email_icon_grau_transparent.png" height="13" width="23"> Mail</A></li>
                </ul>
                <p><b>Start</b></p>
                <ul>
                <li><A href="mini.php">->Mini</a></li>
                <li><A href="input.php">->Maxi</a></li>
                </ul>
                <?php   require_once "session.php";
                        if (isset($_SESSION['user_logged_in']) && ($_SESSION['user_logged_in'])) {
                        $username_string = (mb_strlen($_SESSION['user_username']) > 10) ? $username_string = mb_substr( $_SESSION['user_username'], 0, 10) . "…" : $username_string = $_SESSION['user_username'];
                        //$link_toggle_model = ($_SESSION['model_standard_or_custom'] === 'standard') ? "<a href='toggle_model.php'>standard</a>" : "<a href='toggle_model.php'>custom</a>";
                        echo "<p><b>User</b></p><ul><li><a href='show_account_information.php'>" . $username_string . "(" . $_SESSION['user_privilege'] . ")</a></li>";
                        //echo "<li>$link_toggle_model</li>";
                        echo "</ul><p><b>Data</b></p></ul>";
                        echo "<li><a href='olympus.php'>Olympus</a></li>";
                        echo "<li><a href='elysium.php'>Elysium</a></li>";
                        /*if (($_SESSION['user_privilege'] > 1) || (($_SESSION['user_privilege'] == 1) && $_SESSION['model_standard_or_custom'] === "custom"))*/ echo "<li><a href='purgatorium.php'>Purgatorium</a></li>";
                        echo "<ul><p><b>Edit</b></p>";
                        echo "<li><a href='edit_header.php'>->Header</a></li>";
                        echo "<li><a href='edit_font.php'>->Zeichen</a></li>";
                        echo "<li><a href='edit_rules.php'>->Regeln</a></li>";
                        echo "</ul>";
                        echo "<ul><p><b>Tools</b></p>";
                        echo "<li><a href='regex_helper_variable.php'>->RX-GEN</a></li>";
                        echo "<li><a href='export_se1data_to_editor.php'>->VPAINT</a></li>";
                        echo "<li><a href='dump_models.php'>->MDUMP</a></li>";
                        echo "<li><a href='load_models.php'>->MLOAD</a></li>";
                        echo "</ul>";
                      }
                ?>