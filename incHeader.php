<?php
$G = $VK->QueryObjectOne("select * from setup_global limit 1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<HEAD>
<meta http-equiv="content-type" content="text/html; charset=windows-1251">
<LINK href="/include/globalStyle.css?<?php echo $G->script_style; ?>" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="/include/jquery-1.9.1.min.js"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/xd_connection.js"></SCRIPT>
<TITLE> Приложение 2031819 Hi-tech Service </TITLE>
</HEAD>
<BODY>
<?php if (isset($SA[$_GET['viewer_id']])) { echo "<SCRIPT type='text/javascript' src='http://nyandoma".($_SERVER["SERVER_NAME"] == 'vkmobile' ? '' : '.ru')."/js/errors.js?".$G->script_style."'></SCRIPT>"; } ?>
<SCRIPT type="text/javascript" src="/include/globalScript.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/G_values.js?<?php echo $G->g_values; ?>"></SCRIPT>
<SCRIPT type="text/javascript">
if (document.domain == 'vkmobile') {
  for(var i in VK) {
    if (typeof VK[i] == 'function') {
      VK[i] = function () { return false; };
    }
  }
}
G.values = '<?php echo $VALUES; ?>';
G.vku = {
  viewer_id:<?php echo $vku->viewer_id; ?>,
  first_name:"<?php echo $vku->first_name; ?>",
  last_name:"<?php echo $vku->last_name; ?>",
  name:"<?php echo $vku->first_name." ".$vku->last_name; ?>",
  ws_id:<?php echo $vku->ws_id; ?>,
  country_id:<?php echo $vku->country_id; ?>,
  city_id:<?php echo $vku->city_id; ?>
};
G.clients = [];
G.ws = {
  devs:[<?php echo $WS ? $WS->devs : ''; ?>]
};
</SCRIPT>
<DIV id=frameBody>
