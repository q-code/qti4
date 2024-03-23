<?php
/**
* @var string $s
* @var string $st
*/
echo '<div id="searchcmd">';
echo '<a id="btn_recent" data-s="'.$s.'" class="button" href="'.url(APP.'_items.php').'?fq=last" onclick="addHrefData(this,[`s`])">'.asImg( QT_SKIN.'img/topic_t_0.gif', 'alt=T|class=btn-prefix' ).L('Recent_items').'</a>';
echo '<a id="btn_news" data-s="'.$s.'" class="button" href="'.url(APP.'_items.php').'?fq=news" onclick="addHrefData(this,[`s`])">'.asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=N|class=btn-prefix' ).L('All_news').'</a>';
echo '<a id="btn_insp"  data-s="'.$s.'" class="button" href="'.url(APP.'_items.php').'?fq=insp" onclick="addHrefData(this,[`s`])">'.asImg( QT_SKIN.'img/topic_i_0.gif', 'alt=N|class=btn-prefix' ).L('Inspections').'</a>';
if ( SUser::id()>0 ) echo '<a id="btn_my" data-s="'.$s.'" class="button" href="'.url(APP.'_items.php').'?fq=user&fw='.SUser::id().'&fv='.urlencode(SUser::name()).'" onclick="addHrefData(this,[`s`])">'.qtSVG('user', 'class=btn-prefix').''.L('All_my_items').'</a>';
echo '</div>';