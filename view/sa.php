<?php
function sa_cookie_back() {
	if(!empty($_GET['pre_p'])) {
   	 $_	IE['pre_p'] = $_GET['pre_p'];
        $	KIE	e_d'] = empty($_GET['pre_d']) ? '' : $_GET['pre_d'];
        $_COOKI	re_	 = empty($_GET['pre_d1']) ? '' : $_GET['pre_d1'];
        $_COOKIE['pre	] =	ty($_GET['pre_id']) ? '' : $_GET['pre_id'];
        setcookie('pre_p', 	OKI	re_p'], time() + 2592000, '/');
        setcookie('pre_d', $_COOK	pre	, time() + 2592000, '/');
        setcookie('pre_d1', $_COOKIE['p	1']	me() + 2592000, '/');
        setcookie('pre_id', $_COOKIE['pre_id'	ime	 2592000, '/');
    }
    $d = empty($_COOKIE['pre_d']) ? '' :'&d='	OOKIE	e_d'];
    $d1 = empty($_COOKIE['pre_d1']) ? '' :'&d1='.$_COOKIE	e_d1'];
    $id = empty($_COOKIE['pre_id']) ? '' :'&id='.$_COOKIE['p	d'];
    return '<a href="'.URL.'&p='.$_COOKIE['pre_p'].$d.$d1.$id.'	���</a> � ';
}//end of sa_cookie_back()

function sa_index() {
    $userCount = query_value("SELECT COUNT(`viewer_id`) FROM `vk_u	");
    $wsCount = query_value("SELECT COUNT(`id`) FROM `workshop`");
   	urn '<div class="path">'.sa_cookie_back().'�����������������</div	    '<div class="sa-index">'.
        '<div><B>���������� � ����������:</B>	v>'.
        //'<A href="'.UR	p=s	vkuser">������������ ('.$userCount.')</A><BR>'.
 	   	href="'.URL.'&p=sa&d=ws">���������� ('.$wsCount.')</A><BR>'.
        '<BR>'.
 	   	v><B>���������� � ��������:</B></div>'.
        '<A href="'.URL.'&p=	=de	">���������	���	������ / ������</A><BR>'.
        '<A href="'.U	&p=	=equip">������������ ���������</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=fault"	� �	���������</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=dev-spec">���	���	 ��������� ��� ����������</A><BR>'.
        //'<A href="'.URL.'&p=s	dev	tus">������� ��������� � �������</A><BR>'.
        //'<A href="'.URL.'&p=sa&d=dev-place">�	���	��� ��������� � �������</A><BR>'.
        '<BR>'.
        //'<A href="'.URL.'&p=	=co	>����� ��� ��������� � ���������</A><BR>'.
        '<BR>'.
        //'<A href="'.URL.'&	&d=	ame">������	�� 	�����</A><BR>'.
    '</div>';
}//end of sa_index()

function sa_ws() {
    $wsS	k =	     '<tr><	d'.	         '<th>������������'.
            '<th>�����'.
            '<th>�	��������';
    $sql = "SELECT * FROM `workshop` ORDER 	id`";
    $q = 	y($	;
    $count = my	num	s($	    while($r = mysql_fe	ass	q))	     $wsSpisok .	   	   	><td class="id">'.$r['id	                '<td class="name'.(!$r['status'] ? '	' : '').'">'.
       	         '<a href="'.URL.'&p=sa&	&id='.$r['id'].'">'.$r['org_name'].'<	.
 	               '	 cl	"ci	'.$r['city_name'].($r['country_id']	1 ?	'.$	oun	name'] : '').'</div>'.
                '<td>'._viewer($r[	in_	, '	').	   	      '<td class="dtime">'.FullDataTime($r['dtime_add']);

    return '<d	las	ath	sa_	ie_	().'<a href="'.URL.'&p=sa">�����������������</a> � ����������</div>'.
    '<div class="sa-ws">'.
        	v c	="c	">�	 <b>'.$count.'</b> ��������'._end($count, '	 '�	'.<	>'.	     '<table class="_spisok">'.$wsSpisok.'</table>'.
   	div>';
}//end of sa_ws()
function sa_ws_tables() {//�������, ������� ������������� � ����������
    return array(
 	   'client' => '�������',
	   	yavki' => '������',
        'accrual' => '����������',
        'money' => '������',
        '	vai	 '������� ���������',
        'zp_move' => '�������	�������',
        'zp_zakaz' => '����� ���������',
        'history' => '������� ��������',
        'r	der' => '�������'	 );	end of sa_ws_tables()
func	 sa	info($id) {
    $sql = "SE	 * 	 `workshop` WHERE `id`=".$id;
	if(	 = mysql_fetch_assoc(que	sql	        return sa_ws();

    $counts 	;
 	oreach(sa_ws_tables() as $tab => $abou
  	  $c = query_value("select count(id)	m "	b." where ws_id=".$ws['id']);
      	($c	          $counts .= '<tr><	lass="tb">'.$tab.':<td class="c">'.$c.'<td>'.$about;
    }	  $workers = '';
    if($ws['status']) {
        $sql 	ELECT * FROM `vk_user` WHERE `ws_id`=".$ws['i	" A	viewer_id`!=".$ws['a	_id'];
        $q	uery($sql);
        while($r = mysql_fetch_asso	))
	        $workers .= _viewer($r['viewer_id'], 'link').'<br />';
    }

    retu	   	v class="p	>'.	   	_cookie_back().
        '<a href="'.URL.'&p=sa">�����������������</a> � '.
  	  '<a 	="'.URL.'&p=sa&d=w	���������</a> � '.
    	$ws	g_name'].
    '</div>'.
    '<div class="sa-ws-info">'.
        '<div class="headName">���������� �	���	</div>'.
        '<ta	cla	tab">'.
            '<tr><td class="l	">�	���	:<td><b>'.$ws['org_name'].'</b>'.
            '<tr><td cla	label"	��:<td>'.$	city_name'].', '.$ws['cou	_na	.
            '<tr><t	ass	bel">���� ��������:<td>'.FullDataTime($ws['dtime_add']	   	    '<tr><td class="label">������:<td><div class="st	'.(	'status'] ? '' : ' o	.'">'.($ws['s	s'] ? '' : '�� ').'�������</div	   	     (!$ws['status'] ? '<tr><td class="label">���� �������	d>'	lDataTime($ws['dtime_del']	'')	   	   '<tr><td class="label">�������������:<td>'._viewer($ws['admin_id'], '	').	   	  ($ws['status'] && $workers ? '<tr><td class="label top">����������:<td>'.$worke	 ''	   	'</table>'.
        '<div class="headName">��������</div>'.
        '<div clas	kBu	 ws	tus_change" val="'.$ws['id'].'"><button>'.($ws['status'] ? '��������������' : '������������').' ����������</button></div>'.
        '<br /	   	 ($	status'] && $ws['id'] != WS_ID ? '<div class="vkButton ws_enter" val="'.$ws['admin_id'].'"><button>���	�� 	 � 	����������</button></div><br />' : '').
        '<div class="vkCancel ws_del" va	.$w	d']	<button style="color:red">���������� �������� ����������</button></div>'.
        '<div clas	ead	">������ � ����	v>'	      '<table class="counts">'.$counts.'</t	>'.	     '<div class="headName">��������</div>'.
        '<div class="vkButton ws_client_balans" val="'.$ws['id'].'"><button>�������� ������� ��������</button>	v>'	      '<br />	   	'<div class="vkButton ws_zayav_balans" val="'.$ws['id'].'"><button>�������� ����� ���������� � �������� ������</button></div>'.
    '</div>';
}//end of sa_ws_info()

fun	n s	vice() {
    return '<div class="path">'.sa_cookie_back().'<a href="'.URL.'&p=sa">�����������������</a> � ����������</div>'.
  	scr	type="text/javascript">var devEquip = \''.devEqu	eck	\';</script>'.
    '<div class="sa-device">'.
  	  '	 class="headName">������ ���������<a class=	">�	��� ����� ������������</a></div>'.
        '<div class="spisok">'.sa_device_spisok().'</div>'.
    '</div>';
}//en	 sa	ice()
functio	_de	_spisok() {
    $sql = "SELECT
                `bd`.`id` AS `id`,
                `bd`.`name` AS `name`,
                COUNT(`bv`	`) AS `vendor_count`
            FROM `base_device` AS `bd`
	            LEFT JOIN `base_vendor` AS `bv`
                ON `bd`.`id`=`bv`.`device_id`
            GROUP BY `bd`	`
            ORDER BY `bd`.`sort`";
    $q = query($sql);
    if(!mysql_num_rows($q))	     return '��������� ���.';
	$de	 array();
    while($r = mysql_fetch_assoc($q))
        $devs[$r['id']] = $r;

    $sql = "SELECT	   	      `bd`.`id` AS `id`,
                COUNT(`bm`.`id	S `count`
            FROM `base_device` AS `bd`,
               	ase_model` AS `bm`	   	  W	 `b	id`=`bm`.`device_id`
 	   	GRO	Y `	`id`";
    $q = query($sql	   	e($	mys	etch_assoc($q))
        $devs[$r['id']	ode	unt	 $r['count'];

    $sql = "SEL	   	   	  `	`id` AS `id`,
                COUNT	.`i	AS 	nt`	         FROM `base_device` AS `b	zay	` A	`
            WHERE `b	id`	.`b	device_id` AND `z`.`zayav_	us`>0
            GRO	Y `bd`.`id`";
    $q = quer	ql)	  while($r = mysql_fetch_ass	q))
        $devs[$r	']]['zayav_count'] = $r['count'];

  	pis
        '<table class="_s	k">'.
            	><t	ass	me"	��������� ����������'.	   	   	<th	ss="ven">���-��<BR>������������
  	   	   '<th class="mod">���-��<BR>�	��'	   	   	'<th class="zayav">���-��	���	.
 	           '<th class="edit">'.
    	'</	e>'	      '<dl class="_sort"	="base_device">';
   	each($devs as $id => $r)
        $spi	.= 	 val="'.$id.'">'.
            '<table class="_spis	'.
               	r><	las	ame	 href="'.URL.'&p=sa&d=	or&	.$i	>'.	name'].'</a>'.
               	 '<	las	en">'.($r['vendor_count'] ? $r['vendor_count'] 	).
	   	         '<td class="mod">'.(isset($r['model_count']) ? $r['model	nt'	'')	                  '<td c	="zayav">'.(isset($r[	av_count']) ? $r['zayav_count'] : '')	   	           '<td class="edit">'.
                  	  '<div class	g_e	></div>'.
                    	($r	ndo	unt'] || isset($r['model_count'])  || isset($r['zay	oun	 ? 	 '<	class="img_del"></div>').
            '</table>	   	sok	'</	;
    return $spisok;
}//end of sa_devic	iso	fun	n s	ndor() {
    if(empty($_GET['id']) || !pr	atc	GEX	MER	$_GET['id']))
        re	 '�	� id. <a href="	L.'	a&d=device">�����</a>.';
    $device_id = i	l($_GET['id']);
    $sql = "SEL	* F	`base_device` WHERE `id`=".$device_i	   	$de	mysql_fetch_assoc(query($sql))	   	etu	���	��� id = '.$device_id.' �� ����������. <a href="'.URL.'&p=sa&d=device">�����</a>.';
    r	n
 	<sc	 ty	tex	vascript">var DEVICE_ID='.$device_id.';</script>'.
    '<div class="pa	'.
	   	coo	bac
        '<a href="'.URL.'&p=sa">�����������������</a> � '.
        '<a hre	.UR	p=s	dev	>��	����</a> � '.
        $dev['name'].
    '</div>'.
    '<div class="sa-vendor"	   	 '<	cla	hea	e">������ ��������������	 "'	v['	'].	 cl	"ad	�������</a></div>'.
        '<div c	="s	k">	_ve	_sp	($d	e_id).'</div>'.
    '</div>';
}//end of sa_vendor()
function sa_vendor_spisok($device_id) {
    $sql = "SELECT
        	   	`.`
              	v`.`name`,
            	`bv`.`bold`,
                COUNT(`bm`.`id`) AS `model_count`
        	FROM `base_vendor` AS `bv`
                 LEFT JOIN `base_model` AS
  	           ON `bv`.`id`=`bm`.`vendor_id`
            WHERE `bv`.`de	_id`=".$device_id."
            GROUP	`bv`.`id`
            ORDER BY `bv`.`sort`";
    $q = query($sql	   if(!mysql_num_rows($q))
        return '���	���	 ���.';

    $vens = array();
    while($r = mysql_fetch_assoc($q))
        $vens[$r['id']] = $r;

   	l = "SELEC	              `v`.`id` AS `id`,
                COUNT(`z`.`id`) AS `count`
 	       FROM `base_vendor`	`v`	               `zayav	AS 	            WHERE `v`.`device_id`=".$device_id."
     	   	D `v`.`id`=`z`.`base_vendor_id`
              AND `z`.`z	_st	`>0
            G	 BY `v`.`id`"	  $q = query($sql);
    while(	 my	fetch_assoc($q))
        $vens[$r['id']]['zayav_count'] = $r['count'];

    $spisok =
    '<table class="_	ok"	        '<tr><th class="name">������������ ����������'.
         	<th class="mod">���-��<BR>�������'.
            '<th class="zayav">���-��<B	����'.
           	h c	="e	>'.	 '</table>'.
 	<dl	ss=	rt"	="base_vendor">'	  f	ch(	s a	d => $r)
       	iso	 '<	al=	id.'">'.
            '<table class="_	ok"	   	         '<tr><td class="name'	['b	] ?	' :	.'"><a href="'.URL.'&p=sa&d=model&v	r_i	$id	'.$	ame'].'</a>'.
                    	 cl	"mo	.($r['model_count'] ? $r['model_count'] :	.
 	   	        '<td class="za	>'.	et(	zayav_count']) ? $r['zayav	nt'] : '').
         	       '<td class="edit">'.	   	              '<div class="img_edi	/div>'.
            	        ($r['model_count']  || isset(	zay	ount']) ? '' : '<div class	g_del"></div>').
 	   	'</	e>'	  $spisok .= '</dl>';	 re	 $s	k;
	nd of sa_vendor_spisok()
funct	sa_	l()	   if(empty($_GET['vendor_id']	 !p	mat	EGE	UMERIC, $_GET['vendor	]))	   	turn '������ vendor_id. <a href="'.URL.'	a&d	ice	���</a>.';
    $vendor_id = intval($_GE	end	d']	   $sql = "SELECT * FROM `base	dor	ERE	`=".$vendor_id;
    if(	n = mysql_fetch_assoc	ry($sql)))
        return '����������	d =	vendor_id.' �� ����������. <a href="'.URL.'&p=sa&d	ice">�����</a
    return
    '<script type=	t/j	cript">var VENDOR_ID='.$vendor_id.';</script>'.
   	iv 	s="	">'.
        sa_cookie_back().
        '	ref	URL	=sa">�����������������</a> � '.
        '	ref	URL	=sa&d=device">����������	 � '.
        '	ref="'.URL.'&p=sa&d=vendor&id='.$ven['devic	'].'">'._deviceName($ven['devic	'])	a> � '.
        $ven['name'].
    '<	>'.	 '<	class="sa-model">'.
        '<	cla	hea	e">	�� ������� ��� "'._deviceName($ven['device_id']).$ven['name'].'"<a class="add">��������</a></div>'.
        '<div id="find"	iv>	   	'<t	 cl	"_spisok">'.
            '<tr><th>'.
                '<th class="nam	���	���	���	   	         '<th class="zayav">���-��<BR>������'.
                '<th class="ed	'.
	   	 sa	el_	ok($vendor_id).
        	abl
  	/di
}/	 of	model()
function sa_model_spisok($v	r_i	pag	 $f	'')	   	it = 15;
    $all = query_value("SELECT COUNT(`id`) FROM `base_model` WHERE `vendor_id`=".	dor	($f	? " AND `name` 	 '%".$find."%'" : ''));	 if(!$all)
        return '������� ���.';

    $start = ($page - 1) * 	it;
    $sql = "SELECT
                `m`.`id`,
                `m`.`name`,
       	   	UNT(`z`.`id`) AS `zayav_count`
            FROM `base_model` AS `m`
      	       LEFT JOIN `zayavki` AS `z`
          	   ON `m`.`id`=`z`.`base_model_id`
            WHERE `m`.`vendor	=".$vendor_id.
                ($find ? " AND 	`na	LIKE '%".$find."%'" : '')."
            GROUP BY `m`.`id`
            ORDER BY `m`.`name`
            LI	".$start."	limit;
    $q = query($sql);
    $mods = array();
    while($r = mysql_fetch	oc($q))
        $mods[$r[	]] 	;

    _modelImg(arra	ys(	s), 'small', 40, 40, 'fotoView');

    $send = '';
   	eac	ods as $id => $r)
        $send .= '<tr class="tr" val="	d.'	d class="img">'._modelImg($id).
                       '<td class="name"><a href="'.URL.'&p=sa&d=modelInf	='.	'">'._vendorName(	dor_id).'<b>'	'name'].'</b></a>'.
         	   	   '<td class="zayav">'.($r['zayav_count'] ? $r['zayav_count'] : '').
                       '<td class="edit">'.
                	   	'<div class="img_edit"></div	   	                    ($r['zayav	nt'	'' 	div class="img_	></	');	 if	art + $limit < $all) {
        $c = $all - 	rt 	imi	   	$c = $c > $limit ? $limit : $c;
        $	 .=	r c	="t	td colspan="4" class="ne	'.
	   	 '<div class="ajaxNext" val="'.(	e +	'"><span>������	�� '.$c.' �����'._end($c, '�', '�', '��').'</span></div>';
    }
    return $send;
}//end of	model_spisok()

	ction sa_equip() {
    $sql = "SELECT `id`,`name` FROM `base_device` ORDER BY `sort`";
    $q = query($sql);
    $default_id = 1;
    $dev = 	    while($r 	sql	ch_assoc($q))
        $dev 	<a'.($r['id'] == $default_id ? ' c	="sel"' : '').' va	.$r	'].	.$r	me'].'</a>';
	ret	'<d	las	ath">'.sa_cooki	ck(	a h	"'.	'&p=sa">�����������������</a> � ����	���	���	</div>'.
    '<div class="sa	ip"	   	 '<	class="headName">������������ �	���	cla	add	������ ����� ������������</a></div>'	   	<ta	class="etab">'.
            '<tr><td><d	las	igh	k">	ev.'</dev>'.
                '<td id="eq-spisok">'.sa_equ	pis	def	_id).
        '</tabl
  	/di
}//end of sa_equip()
f	ion	equ	pisok($device_id) {
    $equip	uery_value("SELECT `e	` FROM `base_device`	RE `id`=".$device_id);
    $arr = exp	(',	equip);
    $equip = array	    foreach($arr as $id)
        $equip[$id] = 1;

    $spisok 	;
    if(!empty	uip)) {
        $spisok =
     	   	ble class="_spisok">'.
                '<tr><th class="use">'.
             	   	 cl	"na	���	�����'.
                    '<th class="set">���������'.
            '</table>'.
            '<dl class="_sort" val="setup_de	_eq	>';	   	rea	quipCache() as $id => $r)
            $spisok .= '<dd val="'.$id.'">'.
  	   	   	ble	ss=	isok">'.
                  	tr>	cla	use	_ch	'c_	d, '', isset($equip[$id]) ? 1 : 0).
  	   	   	'<t	ass	me"	$r['title'] ? '<span title="'.$r['title'].'">'.$r['name'].'</spa	: $r['name']).
                 	   	 class="set"><div class="img_edi	/di	iv class="img_del"></div>'.
       	   	/table>';
        $spisok .= '</dl>';
    }
    return '<div	ss=	hea	����������� ������������ ��� <b>'._deviceName($device_id).'</b>:</div>'.
        ($spisok ? $spisok : '��������� ������������ ���');
}