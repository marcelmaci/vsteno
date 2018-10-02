
            </div>
		</div>
		<div id="footer">
            <div id="footer_left">
            <?php require_once "session.php"; $link_toggle_model = ($_SESSION['model_standard_or_custom'] === 'standard') ? "<a href='toggle_model.php'><button>standard</button></a>" : "<a href='toggle_model.php'><button>custom</button></a>";
                        //echo "$link_toggle_model";
            if ($_SESSION['user_logged_in']) echo "<p>&nbsp;&nbsp;&nbsp;&nbsp;$link_toggle_model</p>";  
            else echo "<p>&nbsp;&nbsp;</p>";
            ?>
                
            </div>
            <div id="footer_middle">
                <center><p>VSTENO is FREE SOFTWARE - Source code at: <a href="https://github.com/marcelmaci/vsteno">https://github.com/marcelmaci/vsteno</a></p></center>
            </div>
            <div id= "footer_right">
                <p></p>
            </div>
        </div>
	</div>
</body>
</html> 
