<?php
/**
* @var CTopic $oT
*/
echo '<div id="optionsbar">
<form method="post" action="'.url('qti_item.php').'" id="modaction">
<input type="hidden" name="s" value="'.$oT->pid.'"/>
<input type="hidden" name="t" value="'.$oT->id.'"/>
'.qtSVG('user-M').'&nbsp;<select name="Maction" onchange="document.getElementById(`modaction`).submit()">
<option hidden disabled selected>'.L('Staff').' '.L('commands').'...</option>
<optgroup label="'.L('Item').'">
<option value="reply">'.L('Reply').'...</option>
<option value="move">'.L('Move').'...</option>
<option value="delete">'.L('Delete').'...</option>
</optgroup>
<optgroup label="'.L('Status').'">';
if ( $oT->type!=='T' && $oT->status!=='Z' ) $oT->status = 'A'; // only status A|Z for ticket type A|I
foreach(CTopic::getStatuses($oT->type,true) as $k=>$strValue) echo '<option value="status_'.$k.'"'.($oT->status==$k ? ' disabled' : '').'>'.$strValue.($oT->status==$k ? ' &#10004;' : '').'</option>'; // caution == array keys can be [int]
echo '</optgroup>
<optgroup label="'.L('Type').'">';
foreach(CTopic::getTypes() as $k=>$strValue) echo '<option value="type_'.$k.'"'.($oT->type==$k ? ' disabled' : '').'>'.$strValue.($oT->type==$k ? ' &#10004;' : '').'</option>'; // caution == array keys can be [int]
echo '</optgroup>
</select>
</form>
';
echo '<form method="post" action="'.url('qti_item.php').'" id="modactor" autocomplete="off">
<input type="hidden" id="usr-t" value="M"/>
<input type="hidden" name="s" value="'.$oT->pid.'"/>
<input type="hidden" name="t" value="'.$oT->id.'"/>
<input type="hidden" id="actorid" name="actorid" value="'.$oT->actorid.'"/>
<div id="ac-wrapper-user" class="ac-wrapper">'.L('Actor').' <input type="text" id="user" name="actorname" value="'.$oT->actorname.'" placeholder="'.L('Assign_to').'..." style="width:175px"></div>
<button type="submit" id="submitactor" onclick="if ( document.getElementById(`user`).value.length<2) return false;">'.L('Ok').'</button>
</form>
</div>
';