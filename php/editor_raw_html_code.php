
<center><br><br>

	<table>
	<tr>
		<td>Token: 
		<!-- <div id="tokenpulldowndiv"> -->
        <select id="tokenpulldown">
			<option value="empty">(empty)</option>
		</select> 
        <!-- </div> -->
		<button id="addnew" onClick="document.onClick()"><= ADD</button>
		<input id="token" type="text" size="6"><br>
		
		Action:<button id="load" onClick="document.onClick()">Load</button>
		<button id="save" onClick="document.onClick()">Save</button>
		<button id="delete" onClick="document.onClick()">Delete</button>
		Database:
		<button id="savetodatabase" onClick="document.onClick()">->DB</button>
		<button id="loadfromdatabase" onClick="document.onClick()">Load</button>
		
		
		</td>
		
	</tr>
   </table>
   <br>
   
   <canvas id="drawingArea" width="800" height="600" style="background-color:#eee"></canvas>
   <p id='se1_knottype'>SE1-Knottype: (none)</p>
   <table id="headertable">
	<tr>
        <td>
            type: <select id='tokenpulldown'><option value='normal' selected>normal</option><option value='shadowed'>shadowed</option><option value='virtual'>virtual</option></select><br>
            width: before <input id='width_before' type='text' size='4' value='4'> token <input id='width_middle' type='text' size='4' value='7'> after <input id='width_after' type='text' size='4' value='0'><br>
            following: <select id='higherpositionpulldown'><option value='higher'>higher</option><option value='same_line'>same line</option><option value='lower'>lower</option><option value='none' selected>---</option></select><select id='shadowingpulldown'><option value='shadowed'>shadowed</option><option value='not_shadowed'>normal</option><option value='shadow_none' selected>---</option></select><select id='distancepulldown'><option value='narrow'>narrow</option><option value='wide'>wide</option><option value='none' selected>---</option></select><br>
            delta-Y: if higher: before <input id='conddeltaybefore' type='text' size='4' value='0'> after <input id='conddeltayafter' type='text' size='4' value='0'><br>
            inconditional: before <input id='inconddeltaybefore' type='text' size='4' value='0'> after <input id='inconddeltayafter' type='text' size='4' value='0'><br>
            2nd: x <input id='altx' type='text' size='4' value='0'> y <input id='alty' type='text' size='4' value='0'> <input type='radio' name='relative_or_absolute' id='relative_or_absolute' value='relative' checked> relative <input type='radio' name='relative_or_absolute' id='relative_or_absolute' value='absolute'> absolute<br>
            use: <input type='radio' name='whichexit' id='whichexit' value='normal' checked> normal <input type='radio' name='whichexit' id='whichexit' value='alternative'> alternative <br>
            connect: <input type='radio' name='connect' id='connect' value='yes' checked> yes <input type='radio' name='connect' id='connect' value='no'> no <br>
            offset 6: <input type='text' id='offset6' size='4' value='0'><br>
        </td>
    </tr>
   </table>
   
   
   
   </center>