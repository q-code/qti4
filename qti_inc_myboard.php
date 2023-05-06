<?php // v4.0 build:20230430

echo '<div class="myboard">'.PHP_EOL;
echo '<div class="myboardheader"><p class="title">'.L('My_last_item').'</p>'.PHP_EOL;

if ( $intMyTopics>0 ) {
  echo '<a class="button" href="'.url('qti_items.php').'?q=user&v2='.SUser::id().'&v='.urlencode(SUser::name()).'">'.L('All_my_items').'&nbsp;('.$intMyTopics.')</a>';
} else {
  echo '<span>'.L('None').'</span>';
}
if ( SUser::isStaff() && $intMyAssign>0 ) {
  echo '<a class="button" href="'.url('qti_items.php').'?q=actor&v2='.SUser::id().'&v='.urlencode(SUser::name()).'">'.L('Handled_by').' '.L('me').'&nbsp;('.$intMyAssign.')</a>';
}
echo '</div>'.PHP_EOL;

// MY LAST TICKET

if ( $intMyTopics>0 )
{
  $arr = SMem::get('_Sections'); // arrSections is by domain
  // User's query: tickets issued by user (for staff, tickets assigned to [lastpostuser])
  $oDB->query( sqlLimit('t.*,p.icon,p.title,p.textmsg FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid = p.id WHERE t.'.($intMyTopics>0 && SUser::isStaff() ? 'last' : 'first').'postuser='.SUser::id().' AND t.type="T" AND t.status<>"Z"','t.lastpostdate DESC',0,1,1) );
  $row = $oDB->getRow();
  if ( is_array($row) )
  {
    $oT = new CTopic($row);
    $strTitle = CTopic::makeIcon( $oT->type, $oT->status, '', 't'.$oT->id.'-itemicon', QT_SKIN, url('qti_item.php?t='.$oT->id)).' ';
    $strTitle .= CTopic::getRef( $oT->numid, $oT->pid ).' ';
    $strTitle .= '<a class="item" href="'.url('qti_item.php').'?t='.$oT->id.'">'.qtTrunc($oT->title,42).'</a>';
    $strTitle .= '<span class="item-section">&#8201;&middot;&#8201;'.(isset($arr[$oT->pid]['title']) ? $arr[$oT->pid]['title'] : 'unknown section').'</span>';
    echo '<div id="mylastitem" class="myboardcontent">'.PHP_EOL;
    echo '<p class="title">'.$strTitle.'</p>'.PHP_EOL;
    echo '<div class="messages">'.PHP_EOL;
    echo '<p class="date">'.qtDatestr($oT->firstpostdate,'$','$',true,true).', '.($oT->firstpostuser==SUser::id() ? L('I_wrote') : L('By').' '.qtTrunc($oT->firstpostname,20)).'</p>'.PHP_EOL;
    echo '<p class="content" onclick="window.location=`'.url('qti_item.php').'?t='.$oT->id.'`;">'.qtInline($row['textmsg'],120).'</p>'.PHP_EOL;
    if ( $oT->firstpostid!=$oT->lastpostid ) {
    $oP = new CPost($oT->lastpostid,-1,true); //text is 255 char max
    if ( $oT->items>1 ) echo '<p class="date">'.L('reply',$oT->items).', '.L('last_message').': '.qtDatestr($oT->lastpostdate,'$','$',true,true).', '.($oT->lastpostuser==SUser::id() ? L('I_wrote') : L('By').' '.qtTrunc($oT->lastpostname,20)).'</p>'.PHP_EOL;
    echo '<p class="content" onclick="window.location=`'.url('qti_item.php').'?t='.$oT->id.'`;">'.qtInline($oP->text,120).'</p>'.PHP_EOL;
    }
    echo '</div>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
  }
}
echo '</div>'.PHP_EOL;