<DIV id=dopMenu>
	<A HREF='<?php echo $URL; ?>&my_page=remZayavkiInfo&id=<?php echo $zayav->id; ?>' class=link<?php echo isset($dLink1) ? $dLink1 : ''; ?>><I></I><B></B><DIV>����������</DIV><B></B><I></I></A>
	<A HREF='<?php echo $URL; ?>&my_page=remZayavkiInfoEdit&id=<?php echo $zayav->id; ?>' class=link<?php echo isset($dLink2) ? $dLink2 : ''; ?>><I></I><B></B><DIV>��������������</DIV><B></B><I></I></A>
<?php if ($_GET['my_page'] == 'remZayavkiInfo') { ?>
  <A class=link onclick=accrualAdd();><I></I><B></B><DIV>���������</DIV><B></B><I></I></A>
	<A class=link onclick=oplataAdd();><I></I><B></B><DIV>������� �����</DIV><B></B><I></I></A>
  <A class=del onclick=zayavDel(<?php echo $zayav->id; ?>); style=display:<?php echo $zayavDel > 0 ? 'none' : 'block'; ?>;>������� ������</A>
<?php } ?>
	<DIV style=clear:both;></DIV>
</DIV>

