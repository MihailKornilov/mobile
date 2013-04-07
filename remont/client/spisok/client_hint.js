var msg = "<B>Внесение нового клиента в базу.</B><BR><BR>" +
  "После внесения Вы попадаете на страницу с информацией о клиенте для дальнейших действий.<BR><BR>" +
  "Клиентов также можно добавлять при <A href='/index.php?" + G.values + "&my_page=remZayavkiAdd&back=remClient'>создании новой заявки</A>.";
$("#buttonCreate").vkHint({
  msg:msg,
  ugol:'right',
  width:215,
  top:-38,
  left:-250,
  indent:40,
  delayShow:1000
});

msg = "<B>Список должников.</B><BR><BR>" +
  "Выводятся клиенты, у которых баланс менее 0. Также в результате отображается общая сумма долга.";
$("#check_dolg").vkHint({
  msg:msg,
  ugol:'right',
  width:150,
  top:-26,
  left:-185,
  indent:20,
  delayShow:1000
});

