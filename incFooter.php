<?php
if(isset($SA[$_GET['viewer_id']])) {
  echo "<DIV id=admin><A href='".$URL."&my_page=superAdmin&pre_page=".$_GET['my_page']."&pre_id=".$_GET['id']."'>Admin</A> :: ";
  echo '<A href="https://github.com/MihailKornilov/vkmobile/issues" target="_blank">Issues</A> :: ';
  echo '<A href="http://vkmobile.reformal.ru" target="_blank">Reformal</A> :: ';
  echo "<A id=script_style>Стили и скрипты (".$G->script_style.")</A> :: ";
  echo "php ".getTime($T)." :: ";
  echo "js <EM></EM></DIV>";
  echo "<SCRIPT type='text/javascript'>$('#script_style').click(function () { $.getJSON('/superadmin/AjaxScriptStyleUp.php?' + G.values, function () { location.reload(); }); });</SCRIPT>";
}
?>
</DIV>

<SCRIPT type="text/javascript">
VK.init(frameBodyHeightSet);
VK.callMethod("setLocation","<?php echo $_GET['my_page'].($_GET['id'] ? '_'.$_GET['id'] : '' ); ?>");
VK.callMethod('scrollWindow', 0);
VK.callMethod('scrollSubscribe');
VK.addCallback('onScroll', function (top) { G.vkScroll = top; });
<?php if(isset($SA[$_GET['viewer_id']])) { echo "$('#admin EM:first').html(((new Date().getTime()) - G.T) / 1000);"; } ?>
</SCRIPT>

</BODY></HTML>
