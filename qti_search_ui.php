<?php
/**
* @var string $s
* @var string $st
*/
echo '<div id="searchcmd">';
echo '<a id="btn_recent" data-s="'.$s.'" data-st="'.$st.'" class="button" href="'.url(APP.'_items.php').'?q=last" onclick="addHrefArg(this,[`s`,`st`])">'.asImg( QT_SKIN.'img/topic_t_0.gif', 'alt=T|class=btn-prefix' ).L('Recent_items').'</a>';
echo '<a id="btn_news" data-s="'.$s.'" data-st="'.$st.'" class="button" href="'.url(APP.'_items.php').'?q=news" onclick="addHrefArg(this,[`s`,`st`])">'.asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=N|class=btn-prefix' ).L('All_news').'</a>';
echo '<a id="btn_insp"  data-s="'.$s.'" data-st="'.$st.'" class="button" href="'.url(APP.'_items.php').'?q=insp" onclick="addHrefArg(this,[`s`,`st`])">'.asImg( QT_SKIN.'img/topic_i_0.gif', 'alt=N|class=btn-prefix' ).L('Inspections').'</a>';
echo (SUser::role()==='V' ? '' : '<a id="btn_my" data-s="'.$s.'" data-st="'.$st.'" class="button" href="'.url(APP.'_items.php').'?q=user&v2='.SUser::id().'&v='.urlencode($_SESSION[QT.'_usr']['name']).'" onclick="addHrefArg(this,[`s`,`st`])">'.qtSVG('user', 'class=btn-prefix').''.L('All_my_items').'</a>');
echo '</div>';