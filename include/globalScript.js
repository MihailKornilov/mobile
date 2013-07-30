Highcharts.setOptions({
    lang: {
        months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
    }
});

var G = {
    T:(new Date()).getTime(),
    vkScroll:0,
    zindex:100,
    backCount:0,
    backfon:function(add) {
        if(add === undefined)
            add = true;
        var body = $('body');
        if(add) {
            this.zindex += 10;
            if(this.backCount == 0) {
                body
                    .find('.backfon').remove().end()
                    .append('<div class="backfon"></div>');
            }
            body.find('.backfon').css({'z-index':this.zindex});
            this.backCount++;
        } else {
            this.backCount--;
            this.zindex -= 10;
            if(this.backCount == 0)
                body.find('.backfon').remove();
            else
                body.find('.backfon').css({'z-index':this.zindex});
        }
    }
};
/*
 * Формирование окончаний
 * Пример: 1 день
 * G.end(count, ['ень', 'ня', 'ей']);
*/
G.end = function (count, arr) {
  if (arr.length == 2) { arr.push(arr[1]); } // если в массиве всего 2 элемента, то увеличение его, копируя второй элемент в третий
  var send = arr[2];
  if(Math.floor(count / 10 % 10) != 1) {
    switch(count % 10) {
    case 1: send = arr[0]; break;
    case 2: send = arr[1]; break;
    case 3: send = arr[1]; break;
    case 4: send = arr[1]; break;
    }
  }
  return send;
}


// вставка картинки ожидания процесса
$.fn.imgUp = function(ins) {
  $(this).find('.imgUp').remove();
  var img = "<IMG src=/img/upload.gif class=imgUp>";
  if (ins == 'append') {
    $(this).append(img);
  } else {
    $(this).html(img);
  }
}










// контактовский селект 2013-02-14 23:19
$.fn.vkSel = function (obj) {
  var t = $(this);
  var id = t.attr('id');

  $("#vkSel_" + id).remove();    // удаление select если существует

  $(document).off('click.results_hide').on('click.results_hide', function () {
    $(".vkSel")
      .find(".results").html('').end()
      .find(".ugol").css({'border-left':'#FFF solid 1px', 'background-color':'#FFF'});
    $(this)
      .off('keyup.vksel_esc')
      .off('keydown.vksel');
  });



  var obj = $.extend({
    width:150,            // ширина
    bottom:0,             // отступ снизу
    display:'block',     // расположение селекта
    title0:'',                 // поле с нулевым значением
    spisok:[],             // результаты в формате json
    spisok_new:null, // составление нового списка, если производится поиск в основном
    limit:0,                  // ограничение на вывод количества записей. Если 0 - нет ограничений
    value:$(this).val() || 0, // текущее значение
    ro:1,                     // запрет ввода в поле INPUT
    nofind:'Не найдено',  // сообщение, выводимое при пустом поиске
    func:null,              // функция, выполняемая при выборе элемента
    funcAdd:null,        // функция добавления нового значения. Если не пустая, то выводится плюсик. Функция передаёт список всех элементов, чтобы можно было добавить новый
    funcKeyup:null     // функция, выполняемая при вводе в INPUT в селекте. Нужна для вывода списка из вне, например, Ajax-запроса, либо из vk api. При этом ro должен быть = 0.
  }, obj);




  // ассоциативный массив полученного списка
  var ass; ass_create();




  var html = "<DIV class=vkSel id=vkSel_" + id + " style=width:" + obj.width + "px;display:" + obj.display + ";>";

    html += "<TABLE cellspacing=0 cellpadding=0 class=main style=width:" + obj.width + "px;>";
      var sel_width = obj.width - 17 - 4;
      if (obj.funcAdd) { sel_width -= 17; }
      html += "<TD class=selected style=width:" + sel_width + "px; val=inp_>";
      html += "<INPUT type=text class=inp style=width:" + (sel_width - 5) + "px;" + (obj.ro ? "cursor:default; readonly" : '') + " val=inp_>";
      if (obj.funcAdd) { html += "<TD class=add val=add_>"; }
      html += "<TD class=ugol val=ugol_>";
    html += "</TABLE>";
    html += "<DIV class=results style=width:" + obj.width + "px;></DIV>";
  html += "</DIV>";

  $(this).after(html);



  var vksel = $("#vkSel_" + id);       // сохранение текущего селекта
  var results = vksel.find(".results"); // сохранение ссылки на результат
  var inp = vksel.find('.inp');              // сохранение ссылки на поле для ввода
  var keyup = 0;                               // отслеживание нажатия клавиши (нужно, чтобы не раскрывался список при его замене без нажатия) для keyupFunc
  var keyup_val;                              // значение предыдущего ввода. Если изменилось, то список обновляется.

  // отступ снизу, если необходимо
  if (obj.bottom > 0) { vksel.css('margin-bottom', obj.bottom + 'px'); }

  // установка значения в INPUT
  var inp_set = function (val) {
    if (val !== undefined) { obj.value = val }
    if (obj.title0 && obj.value == 0) {
      inp.val(obj.title0).css('color', '#888');
    } else {
      inp.val(ass[obj.value]).css('color', '#000');
    }
    t.val(obj.value);
    return this;
  };

  // установка значения в INPUT
  inp_set();

  // если разрешён ввод в INPUT, разрешается поиск по списку
  if (!obj.ro) {
    inp
      .on('keyup', function (e) {
        if(e.keyCode != 38 && e.keyCode != 40 && e.keyCode != 13) {
          if (obj.funcKeyup) {
            var val = inp.val();
            if (keyup == 0 && keyup_val != val) {
              keyup_val = val;
              vksel.find(".process_inp").remove();
              inp.before("<DIV class=process_inp style=width:" + (sel_width - 5) + "px;><IMG src=/img/upload.gif></DIV>");
              keyup = 1; // клавиша была нажата. Список раскрывать нужно.
              obj.funcKeyup(val);
            }
          } else { inp_write(); }
        }
      })
      .on('blur', function () { inp_set(); });
  }


  // манипуляции с самим селектом
  vksel.on({
    mouseenter:function () { $(this).find('.ugol:first').css({'border-left':'#d2dbe0 solid 1px', 'background-color':'#e1e8ed'}); }, // подсветка треугольничка
    mouseleave:function () { if (results.find('DL').length == 0) { $(this).find('.ugol:first').css({'border-left':'#FFF solid 1px', 'background-color':'#FFF'}); } },
    click:function (e) {
      var val = $(e.target).attr('val');
      if (val) {
        var arr = val.split('_');
        switch (arr[0]) {
        case 'ugol': // клик по уголку
          $(document).off('keyup.vksel_esc').off('keydown.vksel'); // отключение действия всех клавиш в любом случае
          vksels_hide(e);
          if (!results.find('DL').length) {
            if (obj.spisok_new != null && obj.spisok_new.length == 0) { obj.spisok_new = null; } // если поиск по символам вернул пустой список и результаты тыби закрыты, то показывается весь список
            dd_create();
          } else { results.html(''); } // если список уже открыт, то закрытие
          break;

        case 'add': // клик по плюсику.
          obj.spisok_new = null; // очистка списка, если производился поиск по буквам
          obj.funcAdd(obj.spisok, t.o);
          break;

        case 'inp': // клик по инпуту
          vksels_hide(e);
          if (obj.ro != 1 && obj.title0 && obj.value == 0) { inp.val('').css('color', '#000'); }
          if (results.find('DL:first').length == 0) {
            if (obj.spisok_new != null && obj.spisok_new.length == 0) { obj.spisok_new = null; } // если поиск по символам вернул пустой список и результаты тыби закрыты, то показывается весь список
            dd_create();
          } else if (obj.ro != 1) { inp_write(); }
          break;

        case 'title0':
          inp_set(0);
          if (obj.func) { obj.func(obj.value); }
          break;

        case 'dd':
          inp_set(arr[1]);
          if (obj.func) { obj.func(obj.value); }
          break;
        }
      }
    }
  });






  // создание списка и вывод в результат
  function dd_create() {
    var spisok = obj.spisok_new != null ? obj.spisok_new : obj.spisok;
    var dd = "<DL>";
    var len = (obj.limit > 0 && spisok.length > obj.limit) ? obj.limit : spisok.length;
    if (obj.title0 && obj.ro == 1) { dd += "<DD class='" + (obj.value == 0 ? 'over' : 'out') + " title0' val=title0_0>" + obj.title0; }
    if (len > 0) {
      var reg = new RegExp(">", "ig");
      for (var n = 0; n < len; n++) {
        var sp = spisok[n];
        var c = sp.uid == obj.value ? 'over' : 'out'; // подсветка выбранного элемента
        var cont = null; // вставка val в дополнительные поля описания
        if (sp.content) { cont = sp.content.replace(reg," val=dd_" + sp.uid + ">"); }
        dd += "<DD class=" + c + " val=dd_" + sp.uid + ">" + (cont ? cont : sp.title);
      }
    } else if (obj.ro != 1) { dd += "<DT class=nofind>" + obj.nofind; }
    dd += "</DL>";
    results.html(dd);

    dd = results.find("DD");
    len = dd.length;
    if (len > 0) {
      // вычисление высоты выпадающего списка
      var dl = results.find("DL");
      var over;
      var results_h = results.css('height').split(/px/)[0]; // высота списка результатов до скрытия лишней видимости
      if (results_h > 250) {
        dl.css({height:250 + 'px', 'border-bottom':'#CCC solid 1px'});
        // выставление выбранного поля в зоне видимости
        over = results.find('.over:first')[0];
        if (over) {
          var top = over.offsetTop + over.offsetHeight;
          if(top > 170) {
            var dl_h = 250;
            if (results_h > top) { dl_h -= results_h - top > 120 ? 120 : results_h - top; }
            dl[0].scrollTop = top - dl_h;
          }
        }
      } else { results.find("DD:last").addClass('last'); }

      // установка изменения цвета элемента при наведении мыши
      dd.on('mouseenter', function () {
        $(this).parent().find('.over:first').removeClass('over').addClass('out');
        $(this).addClass('over');
      });

      // если результаты открыты, то включение ESC для скрытия результатов
      $(document).on('keyup.vksel_esc', function (ev) {
        if (ev.keyCode == 27) {
          $(document).off('keyup.vksel_esc').off('keydown.vksel');
          results.html('');
        }
      });

      dl = dl[0];
      $(document).on('keydown.vksel',function (e) {
        for (var n = 0; n < len; n++) { if(dd.eq(n).hasClass('over')) break; }
        switch (e.keyCode) {
        case 38: // перемещение вверх
          e.preventDefault();
          if (n == len) { n = 1; }
          if (n > 0) {
            if (len > 1) { dd.eq(n).removeClass('over').addClass('out'); } // если в списке больше одого элемента
            over = dd.eq(n-1);
          } else { over = dd.eq(0); }
          over.removeClass('out').addClass('over');
          over = over[0];
          if (dl.scrollTop > over.offsetTop) { dl.scrollTop = over.offsetTop; } // если элемент ушёл вверх выше видимости, ставится в самый верх
          if (over.offsetTop - 250 - dl.scrollTop + over.offsetHeight > 0) { dl.scrollTop = over.offsetTop - 250 + over.offsetHeight; } // если ниже, то вниз
          break;

        case 40: // перемещение вниз
          e.preventDefault();
          if (n == len) { dd.eq(0).removeClass('out').addClass('over'); dl.scrollTop = 0; }
          if (n < len - 1) {
            dd.eq(n).removeClass('over').addClass('out');
            over = dd.eq(n+1);
            over.removeClass('out').addClass('over');
            over = over[0];
            if (over.offsetTop + over.offsetHeight - dl.scrollTop > 250) { dl.scrollTop = over.offsetTop + over.offsetHeight - 250; } // если элемент ниже видимости, ставится в нижнюю позицию
            if (over.offsetTop < dl.scrollTop) { dl.scrollTop = over.offsetTop; } // если выше, то в верхнюю
          }
          break;

        case 13: // ентер
          e.preventDefault();
          if (n < len) {
            inp_set(dd.eq(n).attr('val').split('_')[1]);
            results.html('');
            if (obj.func) { obj.func(obj.value); }
          }
          break;
        }
      }); // end keydown.vksel
    } // end len > 0
  }










  // создание ассоциативного массива
  function ass_create() {
    var arr = [];
    for (var n = 0; n < obj.spisok.length; n++) {
      var sp = obj.spisok[n];
      arr[sp.uid] = sp.title;
    }
    ass = arr;
  }







  // скрытие результатов всех селектов кроме текущего
  function vksels_hide(e) {
    e.stopPropagation();
    var s = $(".vkSel");
    for (var n = 0; n < s.length; n++) {
      var sp = s.eq(n);
      if (sp.attr('id').split('vkSel_')[1] != id) {
        sp
          .find('.results').html('').end()
          .find(".ugol").css({'border-left':'#FFF solid 1px', 'background-color':'#FFF'});
      }
    }
  }













  // создание списка по регулярному выражению при вводе в INPUT
  function inp_write() {
    obj.value = 0;
    var val = inp.val();
    if (val.length > 0) {
      obj.spisok_new = [];
      var tag = new RegExp("(<[\/]?[_a-zA-Z0-9=\"' ]*>)", 'i'); // поиск всех тегов
      var reg = new RegExp(val, 'i'); // для замены найденного значения
      for (var n = 0; n < obj.spisok.length; n++) {
        var sp = obj.spisok[n];
        var replaced = 0; // изначально в элементе не производилась замена
        var find = sp.content || sp.title; // где будет производиться поиск
        var arr = find.split(tag); // разбивка на массив согласно тегам
        for (var k = 0; k < arr.length; k++) {
          var r = arr[k];
          if(r.length > 0) { // если строка не пустая
            if (!tag.test(r)) { // если это не тег
              if (reg.test(r)) { // если есть совпадение
                arr[k] = r.replace(reg, "<EM>$&</EM>"); // производится замена
                replaced = 1; // пометка о замене
                break; // и сразу выход из массива
              }
            }
          }
        }
        if (replaced == 1) { // если замена была, то пополнается новый массив
          obj.spisok_new.push({
            uid:sp.uid,
            title:sp.title,
            content:arr.join('')
          });
        }
        if (obj.limit > 0 && obj.spisok_new.length >= obj.limit) break;
      }
    } else { obj.spisok_new = null; }
    dd_create();
  }




  // внесение в список нового элемента. (для внешнего использования)
  var item_add = function (item) {
    obj.spisok.unshift(item);
    ass[item.uid] = item.title;
    return this;
  };

  // возвращается объект для дальнейших манипуляций с селектом
  t.o = {
    spisok:function (spisok) { // установка либо получение списка
      if (spisok != undefined) {
        obj.spisok = spisok;
        ass_create();
        vksel.find(".process:first").remove();
        if (obj.funcKeyup) { // обновление списка, если включена функция при вводе в inp
          vksel.find(".process_inp:first").remove();
          if (keyup == 1) {
            inp_write();
            keyup = 0;
          } else { inp_set(0); }
        } else { inp_set(0); }
        return this;
      } else { return obj.spisok; }
    },

    val:function (val) { // установка либо получение значения
      if (val != undefined) {
        inp_set(val);
        return this;
      } else { return obj.value; }
    },

    add:item_add, // добавление нового элемента

    process:function () { // установка в селект процесса ожидания получения нового списка. При этом старый список удаляется. Значение ставится = 0.
      inp_set(0);
      inp.val('');
      obj.spisok = [];
      vksel.find(".process").remove();
      inp.before("<DIV class=process><IMG src=/img/upload.gif></DIV>");
    },

    remove:function () { vksel.remove(); return this; }
  };

  return t;
}; // end of vkSel



















// Календарь
G.months_ass = {1:'Январь',2:'Февраль',3:'Март',4:'Апрель',5:'Май',6:'Июнь',7:'Июль',8:'Август',9:'Сентябрь',10:'Октябрь',11:'Ноябрь',12:'Декабрь'};
G.months_sel_ass = {1:'января',2:'февраля',3:'марта',4:'апреля',5:'мая',6:'июня',7:'июля',8:'августа',9:'сентября',10:'октября',11:'ноября',12:'декабря'};
(function () {
  var Calendar = function (obj, t) { this.create(obj, t); }

  Calendar.prototype.create = function (obj, t) {
    if (!obj) { var obj = {}; }

    this.id = t.attr('id');

    // если input hidden содежит дату, установка её
    var val = t.val();
    if (/^(\d{4})-(\d{1,2})-(\d{1,2})$/.test(val)) {
      var arr = val.split('-');
      obj.year = arr[0];
      obj.mon = Math.abs(arr[1]);
      obj.day = Math.abs(arr[2]);
    }

    var d = new Date();

    this.year = obj.year || d.getFullYear();     // если год не указан, то текущий год
    this.mon = obj.mon || d.getMonth() + 1; // если месяц не указан, то текущий месяц
    this.day = obj.day || d.getDate();           // то же с днём
    this.curYear = this.year;
    this.curMon = this.mon;
    this.curDay = this.day;

    this.lost = obj.lost || 0; // если не 0, то можно выбрать прошедшие дни
    this.func = obj.func || function () {}; // исполняемая функция при выборе дня
    this.place = obj.place || 'right'; // расположение календаря относительно выбора

    var html = "<DIV class=vk_calendar>" +
      "<DIV class=cal_input val=cal_input>" + this.day + " " + G.months_sel_ass[this.mon] + " " + this.year + "</DIV>" +
      "<DIV class=cal_abs id=calabs_" + this.id + "></DIV>" +
    "</DIV>";

    t.next().remove('.vk_calendar'); // удаление календаря, если был для этого элемента
    t.after(html);

    var cal = t.next();
    this.cShow = 0; // показан календарь или нет
    this.calAbs = cal.find('.cal_abs:first'); // размещение для календаря
    this.calInput = cal.find('.cal_input:first'); // выбранная дата
    this.t = t;
    var thisCal = this;

    this.setVal();

    cal.on('click', function (e) {
      var val = $(e.target).attr('val');
      if (val) {
        var arr = val.split('cal_');
        switch (arr[1]) {
          case 'input': thisCal.calPrint(e); break;
          case 'back': thisCal.back(e); break;
          case 'next': thisCal.next(e); break;
          case 'lost': e.stopPropagation(); break; // нажатие на прожедший день, когда нельзя выбирать
          default: thisCal.setDay(arr[1]); break;
        }
      }
    });
  };


  // установка значения input hidden
  Calendar.prototype.setVal = function () { this.t.val(this.dataForm()); };
  // формирование даты в виде 2000-01-01
  Calendar.prototype.dataForm = function () { return this.curYear + "-" + (this.curMon < 10 ? '0' : '') + this.curMon + "-" + (this.curDay < 10 ? '0' : '') + this.curDay; };

  // открытие и скрытие календаря
  Calendar.prototype.calPrint = function (e) {
    if (this.cShow == 0) {
      e.stopPropagation();
      // если были открыты другие календари, то закрываются, кроме текущего
      var calabs = $(".cal_abs");
      for (var n = 0; n <calabs.length; n++) {
        var sp = calabs.eq(n);
        if (sp.attr('id').split('calabs_')[1] == this.id) continue;
        sp.html('');
      }
      // закрытие текущаего календаря при нажатии на любое место экрана
      var thisCal = this;
      $(document).on('click.calendar' + this.id, function () {
        if (thisCal.cShow == 1) {
          thisCal.calAbs.html('');
          thisCal.cShow = 0;
          $(document).off('click.calendar' + thisCal.id);
        }
      });
      this.year = this.curYear;
      this.mon = this.curMon;
      var html = "<DIV class=cal_calendar style=left:" + (this.place == 'right' ? 0 : -64) + "px;>" +
          "<TABLE cellpadding=0 cellspacing=0 class=cal_head><TR><TD class=cal_back val=cal_back><TD class=cal_month><TD class=cal_next val=cal_next></TABLE>" +
          "<TABLE cellpadding=0 cellspacing=0 class=cal_week_name><TR><TD>Пн<TD>Вт<TD>Ср<TD>Чт<TD>Пт<TD>Сб<TD>Вс</TABLE>" +
          "<DIV class=cal_days></DIV>" +
        "</DIV>";
      this.calAbs.html(html);
      this.calMon = this.calAbs.find('.cal_month:first');
      this.calDays = this.calAbs.find('.cal_days:first');
      this.daysPrint();
      this.monPrint();
      this.cShow = 1;
    }
  };


  // пролистывание календаря назад
  Calendar.prototype.back = function (e) {
    e.stopPropagation();
    this.mon--;
    if (this.mon == 0) { this.mon = 12; this.year--; }
    this.daysPrint();
    this.monPrint();
  };


  // пролистывание календаря вперёд
  Calendar.prototype.next = function (e) {
    e.stopPropagation();
    this.mon++;
    if (this.mon == 13) { this.mon = 1; this.year++; }
    this.daysPrint();
    this.monPrint();
  };


  // установка дня
  Calendar.prototype.setDay = function (day) {
    this.curYear = this.year;
    this.curMon = this.mon;
    this.curDay = day;
    this.calInput.html(day + " " + G.months_sel_ass[this.mon] + " " + this.year);
    this.setVal();
    this.func(this.dataForm());
  };



  // вывод названия месяца и года сверху календаря
  Calendar.prototype.monPrint = function () {  this.calMon.html(G.months_ass[this.mon] + " " + this.year); }



  // вывод списка дней
  Calendar.prototype.daysPrint = function () {
    var html = "<TABLE cellpadding=0 cellspacing=0><TR>";

    // установка пустых ячеек
    dayFirst = getDayFirst(this.year, this.mon);
    if (dayFirst > 1) { for (var n = 0; n < dayFirst - 1; n++) { html += "<TD>"; } }

    // выделение текущего дня
    var d = new Date();
    var cur = 0;
    var year = d.getFullYear();
    var mon = d.getMonth() + 1;
    if (year == this.year && mon == this.mon) { cur = 1; }
    var today = d.getDate();

    // выделение выбранного дня
    var st = 0;
    if (this.year == this.curYear && this.mon == this.curMon) { st = 1; }

    var dayCount = getDayCount(this.year, this.mon);
    for (var n = 1; n <= dayCount; n++) {
      var active = 'cal_day';
      if (this.year < year) { active = this.lost == 1 ? active + ' lost' : 'lost'; }
      else if (this.year == year && this.mon < mon) { active = this.lost == 1 ? active + ' lost' : 'lost'; }
      else if (this.year == year && this.mon == mon && n < today) { active = this.lost == 1 ? active + ' lost' : 'lost'; }
      var bold = (cur == 1 && n == today && active == 'cal_day' ? " cal-day-cur" : '');
      var back = (st == 1 && n == this.curDay ? " cal-day-set" : '');
      var val = " val=cal_" + (this.lost == 0 && active == 'lost' ? 'lost' : n); // если нельзя выбрать прошедший день, то окно закрываться не будет
      html += "<TD class='" + active + bold + back + "'" + val + ">" + n;
      dayFirst++;
      if (dayFirst == 8 && n != dayCount) {
        html += "<TR>";
        dayFirst = 1;
      }
    }
    html += "</TABLE>";
    this.calDays.html(html);
  }






  // номер первой недели в месяце
  function getDayFirst(year, mon) {
    var first = new Date(year, mon - 1, 1).getDay();
    if (first == 0) { return 7; } else { return first; }
  }



  // количество дней в месяце
  function getDayCount(year, mon) {
    mon--;
    if (mon == 0) { mon = 12; year--; }
    return 32 - new Date(year, mon, 32).getDate();
  }


  $.fn.vkCalendar = function (obj) { new Calendar(obj, $(this)); };
})();





























/* Выпадающее меню по ссылке
 *
 * id указывается из INPUT hidden
*/
$.fn.linkMenu = function (obj) {
  var obj = $.extend({
    head:'',    // если указано, то ставится в название ссылки, а список из spisok
    spisok:[],
    func:null,
    right:0    // прижимать вправо или нет
  },obj);

  var T = $(this);
  var idSel = T.val(); // бранное значение в INPUT
  var selA = obj.head;  // выбранное имя по id
  var dl = '';
  var len = obj.spisok.length;
  for (var n = 0; n < len; n++) {
    dl += "<DD" + (n == len -1 ? ' class=last' : '') + " val=" + obj.spisok[n].uid + ">" + obj.spisok[n].title;
    if (idSel == obj.spisok[n].uid) {
      selA = obj.spisok[n].title;
    }
  }

  var attrId = "linkMenu_" + T.attr('id');
  var html = "<DIV class=linkMenu id=" + attrId + ">";
    html += "<A href='javascript:'>" + selA + "</A>";
    html += "<DIV class=fordl><DL><DT><EM>" + selA + "</EM>" + dl + "</DL></DIV>";
  html += "</DIV>";

  T.after(html);

  var ID = $("#" + attrId);
  var leftDl =  parseInt(ID.find('DL:first').css('left').split('px')[0]);

  ID.find("A:first").click(function () {
    var dd = getDD(T.val());
    if(dd) { dd.addClass('hover'); }
    $(this).next().show();
    if (obj.right) {
      var wDt = parseInt(ID.find("DT:first").css('text-align','right').css('width').split('px')[0]);
      var wEm = parseInt(ID.find('EM:first').css('width').split('px')[0]);
      ID.find('DL').css('left', (wEm - wDt + leftDl) + 'px');
    }
  });

  ID.find("DL").bind({
    mouseleave:function () {
      var forDL = $(this).parent();
      if(forDL.is(':visible')) {
        window.linkMenuDelay = window.setTimeout(function () { forDL.fadeOut(150); },500);
      }
    },
    mouseenter:function () {
      if (typeof window.linkMenuDelay == 'number') {
        window.clearTimeout(window.linkMenuDelay);
      }
    }
  });

  ID.find("DT").click(dlHide);

  ID.find("DD").bind({
    mouseenter:function () {
      ID.find(".hover").removeClass('hover');
      $(this).addClass('hover');
    },
    mouseleave:function () { $(this).removeClass('hover'); },
    click:function () {
      dlHide();
      var uid = $(this).attr('val');
      if (obj.func) { obj.func(uid); }
      // если head не указан, то можно менять имя при выборе
      if(!obj.head) {
        T.val(uid);
        var name = getDD(uid).html();
        ID.find("A:first").html(name);
        ID.find("DT:first").html(name);
      }
    }
  });

  function dlHide() { ID.find(".fordl").hide(); }

  function getDD (sel) {
    var dd = ID.find("DD");
    for (var n = 0; n < len; n++) {
      if (sel == obj.spisok[n].uid) {
        return dd.eq(n);
      }
    }
  return false;
  }
};






/* меню - список  */
$.fn.infoLink = function(obj) {
  var obj = $.extend({
    spisok:[],
    func:''
    },obj);
  var dl = '';
  for (var n = 0; n < obj.spisok.length; n++) { dl += "<DD val=" + obj.spisok[n].uid + ">" + obj.spisok[n].title; }
  var TS = $(this);
  TS
    .addClass('infoLink')
    .html("<INPUT type=hidden value='" + obj.spisok[0].uid + "'><DL>" + dl + "</DL>")
    .find('DD:first').addClass('sel');
  TS.find('DD').click(function () {
    TS.find('.sel').removeClass('sel');
    $(this).addClass('sel');
    if(obj.func) { obj.func($(this).attr('val')); }
  });
  return TS;
};
$.fn.infoLinkSet = function(id) {
  $(this).find('.sel').removeClass('sel');
  var dd = $(this).find('DD');
  for (var n = 0; n < dd.length; n++) {
    if(dd.eq(n).attr('val') == id) {
      dd.eq(n).addClass('sel');
      $(this).find("INPUT:first").val(id);
      break;
    }
  }
};






















// ПОКАЗЫВАЕТ ОЖИДАНИЕ ПРОГРЕССА ДЛЯ СИНЕЙ КНОПКИ
$.fn.butProcess = function () {
  var W=$(this).parent().css('width');
  $(this)
 // .attr('val',$(this).attr('onclick'))
//    .attr('name',$(this).html())
//    .attr('onclick',null)
    .html("&nbsp;<IMG src=/img/upload.gif>&nbsp;")
    .css({width:W});
}
// ВОССТАНОВЛЕНИЕ СИНЕЙ КНОПКИ
$.fn.butRestore = function () {
  var name = $(this).attr('name');
  var click = $(this).attr('val');
  $(this)
    .attr('onclick',click)
    .html(name);
}







/* ПОИСКОВАЯ СТРОКА */
$.fn.topSearch = function (obj) {
  var obj = $.extend({
    width:126,
    focus:0,
    txt:'',
    func:'',
    enter:0    // если 1 - функция выполняется после нажатия Enter
    },obj);

  var TS = $(this);

  TS.addClass('topSearch').html("<H5><DIV>"+obj.txt+"</DIV></H5><H6><DIV class=img_del></DIV><INPUT TYPE=text></H6>");
  TS.find("H6:first").css('width', obj.width + 'px');
  var DIV = TS.find("H5 DIV:first");
  var input = TS.find("INPUT:first");
  input.css('width', (obj.width - 20) + 'px');

  DIV.click(function(){ input.focus(); });

  input.bind({
    focus:function () { DIV.css('color','#CCC'); },
    blur:function () { DIV.css('color','#777'); },
    keyup:function () {
      if (!$(this).val()) {
        DIV.show();
        $(this).prev().hide();
      } else {
        DIV.hide();
        $(this).prev().show();
      }
      if (obj.func && obj.enter == 0) { obj.func($(this).val()); }
    }
  });

  TS.find(".img_del").click(function () {
    $(this).hide().next().val('');
    DIV.show();
    if(obj.func) obj.func($(this).next().val());
  });

  if (obj.enter == 1) {
    input.keydown(function (e) {
      if(e.which==13 && obj.func)
        obj.func($(this).val());
      });
  }

  if(obj.focus == 1) { input.focus(); }

  return TS;
}

$.fn.topSearchClear = function () {
  this.find(".img_del").hide().next().val('');
  this.find("H5 DIV:first").show();
}

$.fn.topSearchSet = function (VAL) {
  if(!VAL) {
    this.find(".img_del").hide().next().val('');
    this.find("H5 DIV:first").show();
  } else {
    this.find(".img_del").show().next().val(VAL);
    this.find("H5 DIV:first").hide();
  }
}














// чекбокс
$.fn.myCheck = function (obj) {
  if (!obj) { obj = {}; }
  obj.uid = obj.uid || null;           // id чекбокса, если нет INPUT или списка
  obj.title = obj.title || '';              // описание чекбокса
  obj.value = obj.value || 0;       // значение

  obj.top = obj.top ? " style='margin-top:" + obj.top + "px'" : ''; // отступ сверху
  obj.bottom = obj.bottom ? " style='margin-bottom:" + obj.bottom + "px'" : ''; // отступ снизу
  obj.br = obj.br ? "<BR>" : '';   // перевод на новую строку
  obj.func = obj.func || null;       // функция, выполняемая при нажатии

  obj.spisok = obj.spisok || null; // массив чекбоксов в виде [{uid:1, title:'Описание', value:0}]

  var t = $(this);
  var id = t.attr('id');

  // чекбокс для скрытого INPUT
  if (t[0].tagName == 'INPUT') {
    if($("#check_" + id).length) { $("#check_" + id).remove(); } // удаление, если такой же существует

    // установка в INPUT значения, если оно пустое
    var val = t.val();
    if(val == '') {
      t.val(obj.value);
    } else {
      obj.value = val;
    }

    t.after("<DIV class=check" + obj.value + obj.top + obj.bottom + " id=check_" + id + ">" + obj.title + "</DIV>" + obj.br);

    // действие при нажатии
    t.next().click(function () {
      var val = t.val() == 0 ? 1 : 0;
      t.val(val);
      $(this).attr('class', 'check' + val)
      if(obj.func) { obj.func(val); }
    });
  } else if (obj.spisok) { // вывод списка чекбоксов
    var html = '';
    for (var n = 0; n < obj.spisok.length; n++) {
      var sp = obj.spisok[n];
      var value = sp.value ? 1 : 0;
      html += "<INPUT type=hidden name=check_" + sp.uid + " id=check_" + sp.uid + " value=" + value + ">";
      html += "<DIV class=check" + value + obj.top + obj.bottom + " val=check_" + sp.uid + ">" + (sp.title ? sp.title : '') + "</DIV>" + obj.br;
    }
    t.html(html);
    t.unbind().bind('click',function (e) {
      var target = $(e.target);
      var val = target.attr('val');
      if (val) {
        var arr = val.split('_');
        if (arr[0] == 'check') {
          var input = $("#check_" + arr[1]);
          var o = {
            uid:arr[1],
            title:target.html(),
            value:input.val() == 0 ? 1 : 0,
            target:target
          };
          input.val(o.value);
          $(e.target).attr('class', 'check' + o.value);
          if(obj.func) { obj.func(o); }
        }
      }
    });
  } else {

  }

  return t;
};




$.fn.myCheckVal = function (val) {
  var id = $(this).attr('id');
  $("#check_" + id).attr('class','check' + (val ? val : 0));
};











// радио
$.fn.vkRadio = function (obj) {
  var t = $(this);
  var id = t.attr('id');
  var value = t.val(); // текщее значение

  if (!obj) { var obj = {}; }
  obj.spisok = obj.spisok || [];
  obj.value = /\d$/.test(value) ? value : (obj.value || -1); // установленное значение. Можно установить либо в INPUT, либо через объект
  obj.display = "display:" + (obj.display || "block") +";";
  obj.top = obj.top ? "margin-top:" + obj.top + "px;" : '';
  obj.bottom = obj.bottom ? "margin-bottom:" + obj.bottom + "px;" : '';
  obj.right = obj.right ? "margin-right:" + obj.right + "px;" : '';
  obj.light = obj.light || 0; // подсветка выбранного значения
  obj.func = obj.func || function () {};

    $('#' + id + '_radio').remove();
  var html = "<DIV class=radio id=" + id + "_radio val=end_>";
  for(var n = 0; n < obj.spisok.length; n++) {
    var sp = obj.spisok[n];
    html += "<DIV class=" + (sp.uid == obj.value ? 'on' : 'off') + " val=radio_" + obj.spisok[n].uid + " style=" + obj.display + obj.bottom + obj.top + obj.right + ">" + sp.title + "</DIV>";
  }
  html += "</DIV>";
  t.after(html);

  if (obj.light) { $("#" + id +"_radio .off").css('color', '#888'); }

  $("#" + id +"_radio").click(function (e) {
    var target = $(e.target);
    var n = 1;
    while (target.attr('val') == undefined) {
      target = target.parent();
      n--;
      if (n < 0) break;
    }
    var val = target.attr('val').split('_');
    if (val[0] == 'radio') {
      if (obj.value != val[1]) {
        obj.value = val[1];
        $(this).find(".on").attr('class', 'off').css('color', obj.light ? '#888' : '#000');
        target.attr('class', 'on').css('color', '#000');
        t.val(obj.value);
        obj.func(obj.value);
      }
    }
  });
};










// КНОПКА РАДИО
$.fn.myRadio = function(obj){
  var obj = $.extend({
    spisok:[{uid:0,title:'radio'}],
    bottom:0,
    func:''
  },obj);

  var INP = this;
  var ID = INP.attr('id');
  var VAL = INP.val();
  if (VAL.length == 0) VAL = -1;
  var HTML = "<DIV class=radio id="+ID+"_radio>";
  for(var n = 0; n < obj.spisok.length; n++) {
    HTML+="<DIV class=" + (obj.spisok[n].uid == VAL ? 'on' : 'off') + " val=" + obj.spisok[n].uid + ">" + obj.spisok[n].title + "</DIV>";
  }
  HTML += "</DIV>";
  INP.after(HTML);

  if(obj.bottom > 0) { $("#" + ID + "_radio DIV").css('margin-bottom',obj.bottom+'px'); }

  $("#"+ID+"_radio").click(function (e) {
    var target = $(e.target);
    while (target.attr('val') == undefined) { target = target.parent(); }
    var val = target.attr('val');
    $(this).find(".on").attr('class','off');
    target.attr('class','on');
    INP.val(val);
    if(obj.func) obj.func(val);
  });
};
$.fn.myRadioSet = function (VAL) {
  this.val(VAL);
  var ID=this.attr('id');
  var DIVS=$("#"+ID+"_radio DIV");
  DIVS.attr('class','off');
  var LEN=DIVS.length;
  for(var n=0;n<LEN;n++)
    if(VAL==DIVS.eq(n).attr('val'))
      DIVS.eq(n).attr('class','on');
};





















// Подсказки vkHint 2013-02-14 14:43
(function () {
  var Hint = function (t, o) { this.create(t, o); return t; };

  Hint.prototype.create = function (t, o) {
    var o = $.extend({
      msg:'Сообщение подсказки',
      width:0,
      event:'mouseenter', // событие, при котором происходит всплытие подсказки
      ugol:'bottom',
      indent:'center',
      top:0,
      left:0,
      show:0,      // выводить ли подсказку после загрузки страницы
      delayShow:0, // задержка перед всплытием
      delayHide:0, // задержка перед скрытием
      correct:0,   // настройка top и left
      remove:0     // удалить подсказку после показа
    }, o);

    var correct = o.correct == 1 ? "<DIV class=correct>top: <SPAN id=correct_top>" + o.top + "</SPAN> left: <SPAN id=correct_left>" + o.left + "</SPAN></DIV>" : '';

    var html = "<TABLE cellpadding=0 cellspacing=0 class=cont_table>" +
      "<TR><TD class=ugttd colspan=3>" + (o.ugol == 'top' ? "<DIV class=ugt></DIV>" : '') +
      "<TR><TD class=ugltd>" + (o.ugol == 'left' ? "<DIV class=ugl></DIV>" : '') +
               "<TD class=cont>" + correct + o.msg +
               "<TD class=ugrtd>" + (o.ugol == 'right' ? "<DIV class=ugr></DIV>" : '') +
      "<TR><TD class=ugbtd colspan=3>" + (o.ugol == 'bottom' ? "<DIV class=ugb></DIV>" : '') +
      "</TABLE>";

    html = "<TABLE cellpadding=0 cellspacing=0>" +
      "<TR><TD class=side012><TD>" + html + "<TD class=side012>" +
      "<TR><TD class=b012 colspan=3>" +
      "</TABLE>";

    html = "<TABLE cellpadding=0 cellspacing=0 class=hint_table>" +
      "<TR><TD class=side005><TD>" + html + "<TD class=side005>" +
      "<TR><TD class=b005 colspan=3>" +
      "</TABLE>";

    t.prev().remove('.hint'); // удаление предыдущей такой же подсказки
    t.before("<DIV class=hint>" + html + "</DIV>"); // вставка перед элементом

    var hi = t.prev(); // поле absolute для подсказки
    var hintTable = hi.find('.hint_table:first'); // сама подсказка
    if (o.width > 0) { hintTable.find('.cont_table:first').width(o.width); }

    hint_width = hintTable.width();
    hint_height = hintTable.height();

    hintTable.hide().css('visibility','visible');

    // установка направления всплытия и отступа для уголка
    var top = o.top; // установка конечного положения
    var left = o.left;
    switch (o.ugol) {
      case 'top':
        top = o.top - 15;
        var ugttd = hintTable.find('.ugttd:first');
        if (o.indent == 'center') { ugttd.css('text-align', 'center'); }
        else if (o.indent == 'right') { ugttd.css('text-align', 'right'); }
        else if (o.indent == 'left') { ugttd.css('text-align', 'left'); }
        else if (!isNaN(o.indent)) {
          ugttd.css('text-align', 'left');
          if (o.indent < 10) { o.indent = 10; }
          if (o.indent > hint_width) { o.indent = hint_width - 28; }
          hintTable.find('.ugt:first').css('margin-left', o.indent + 'px');
        }
        break;

      case 'right':
        left = o.left + 25;
        var ugrtd = hintTable.find('.ugrtd:first');
        if (o.indent == 'center') { ugrtd.css('vertical-align', 'middle'); }
        else if (o.indent == 'bottom') { ugrtd.css('vertical-align', 'bottom'); }
        else if (!isNaN(o.indent)) {
          if (o.indent < 3) { o.indent = 3; }
          if (o.indent > hint_height) { o.indent = hint_height - 31; }
          hintTable.find('.ugr:first').css('margin-top', o.indent + 'px');
        }
        break;

      case 'bottom':
        top = o.top + 15;
        var ugbtd = hintTable.find('.ugbtd:first');
        if (o.indent == 'center') { ugbtd.css('text-align', 'center'); }
        else if (o.indent == 'right') { ugbtd.css('text-align', 'right'); }
        else if (o.indent == 'left') { ugbtd.css('text-align', 'left'); }
        else if (!isNaN(o.indent)) {
          ugbtd.css('text-align', 'left');
          if (o.indent < 10) { o.indent = 10; }
          if (o.indent > hint_width) { o.indent = hint_width - 28; }
          hintTable.find('.ugb:first').css('margin-left', o.indent + 'px');
        }
        break;

      case 'left':
        left = o.left - 25;
        var ugltd = hintTable.find('.ugltd:first');
        if (o.indent == 'center') { ugltd.css('vertical-align', 'middle'); }
        else if (o.indent == 'bottom') { ugltd.css('vertical-align', 'bottom'); }
        else if (!isNaN(o.indent)) {
          if (o.indent < 3) { o.indent = 3; }
          if (o.indent > hint_height) { o.indent = hint_height - 31; }
          hintTable.find('.ugl:first').css('margin-top', o.indent + 'px');
        }
        break;
    }




    // отключение событий от предыдущей такой же подсказки
    t.off(o.event + '.hint');
    t.off('mouseleave.hint');

    // установка событий
    t.on(o.event + '.hint', show);
    t.on('mouseleave.hint', hide);
    hintTable.on('mouseenter.hint', show);
    hintTable.on('mouseleave.hint', hide);



    // процессы всплытия подсказки:
    // - wait_to_showind - ожидает показа (мышь была наведена)
    // - showing - выплывает
    // - show - показана
    // - wait_to_hidding - ожидает скрытия (мышь была отведена)
    // - hidding - скрывается
    // - hidden - скрыта
    var process = 'hidden';

    var timer = 0;

    // автоматический показ подсказки, если нужно
    if (o.show != 0) { show(); }

    // всплытие подсказки
    function show() {
       if (o.correct != 0) { $(document).off('keydown.hint'); }
      switch (process) {
      case 'wait_to_hidding': clearTimeout(timer); process = 'show'; break;
      case 'hidding':
        process = 'showing';
        hintTable
          .stop()
          .animate({top:top, left:left, opacity:1}, 200, showed);
        break;
      case 'hidden':
        if (o.delayShow > 0) {
          process = 'wait_to_showing';
          timer = setTimeout(action, o.delayShow);
        } else { action(); }
        break;
      }
      // действие всплытия подсказки
      function action() {
        process = 'showing';
        hintTable
          .css({top:o.top, left:o.left})
          .animate({top:top, left:left, opacity:'show'}, 200, showed);
      }
      // действие по завершению всплытия
      function showed() {
        process = 'show';
        if (o.correct != 0) {
          $(document).on('keydown.hint', function (e) {
            e.preventDefault();
            switch (e.keyCode) {
            case 38: o.top--; top--; break; // вверх
            case 40: o.top++; top++; break; // вниз
            case 37: o.left--; left--; break; // влево
            case 39: o.left++; left++; break; // вправо
            }
          hintTable.css({top:top, left:left});
          hintTable.find('#correct_top').html(o.top);
          hintTable.find('#correct_left').html(o.left);
          });
        }
      }
    } // end show




    // скрытие подсказки
    function hide() {
      if (o.correct != 0) { $(document).off('keydown.hint'); }
      if (process == 'wait_to_showing') { clearTimeout(timer); process = 'hidden'; }
      if (process == 'showing') { hintTable.stop(); action(); }
      if (process == 'show') {
        if (o.delayHide > 0) {
          process = 'wait_to_hidding';
          timer = setTimeout(action, o.delayHide);
        } else { action(); }
      }
      function action() {
        process = 'hidding';
        hintTable.animate({opacity:'hide'}, 200, function () {
          process = 'hidden';
          if (o.remove != 0) {
            hi.remove();
            t.off(o.event + '.hint');
            t.off('mouseleave.hint');
          }
        });
      }
    } // end hide
  };// end Hint.prototype.create

  $.fn.vkHint = function (obj) { return new Hint($(this), obj); };
})();

























/* АЛЕРТЫ */
$.fn.alertShow = function(OBJ) {
  var OBJ = $.extend({
    width:0,
    txt:'txt: текст сообщения.',
    top:0,
    left:0,
    delayShow:0,      // задержка перед появлением сообщения
    delayHide:3000,  // длительность отображения сообщения, 0 - бесконечно
    ugol:'bottom',      // с какой стороны вырисовывать треугольник. В эту же сторону будет происходить движение
    otstup:20           // отступ треугольничка
    },OBJ);
  if ($("#alert").length > 0) { $("#alert").remove(); }
  var HTML="<DIV id=alert>";

    HTML+="<TABLE cellpadding=0 cellspacing=0 id=table style=width:"+(OBJ.width>0?OBJ.width+'px':'auto')+">";
    if(OBJ.ugol=='top') HTML+="<TR><TD class=UGT><DIV>&nbsp;</DIV>";
    HTML+="<TR>";
    if(OBJ.ugol=='left') HTML+="<TD class=UGL><DIV>&nbsp;</DIV>";

    HTML+="<TD>";
      HTML+="<TABLE cellpadding=0 cellspacing=0>";
      HTML+="<TR><TD class=LR1><TD class=LR2><TD class=RAM>";
      HTML+="<DIV class=txt>"+OBJ.txt+"</DIV>";
      HTML+="<TD class=LR2><TD class=LR1>";
      HTML+="<TR><TD colspan=5 class=BOT1>";
      HTML+="<TR><TD colspan=5 class=BOT2>";
      HTML+="</TABLE>";

    if(OBJ.ugol=='right') HTML+="<TD class=UGR><DIV>&nbsp;</DIV>";
    if(OBJ.ugol=='bottom') HTML+="<TR><TD class=UGB><DIV>&nbsp;</DIV>";
    HTML+="</TABLE>";

  HTML+="</DIV>";
  this.prepend(HTML);

  var NTOP=OBJ.top, NLEFT=OBJ.left;
  switch (OBJ.ugol) {
  case 'top':    OBJ.top+=15; this.find('.UGT DIV').css('margin-left',OBJ.otstup+'px'); break;
  case 'bottom':  OBJ.top-=15; this.find('.UGB DIV').css('margin-left',OBJ.otstup + 'px'); break;
  case 'left':      OBJ.left+=25; this.find('.UGL DIV').css('margin-top',OBJ.otstup + 'px'); break;
  case 'right':    OBJ.left-=25; this.find('.UGR DIV').css('margin-top',OBJ.otstup + 'px'); break;
  }

  var TAB = $("#alert #table");

  setTimeout(function () {
    TAB
      .css({top:OBJ.top,left:OBJ.left})
      .animate({top:NTOP,left:NLEFT,opacity:'show'},250);
    aHide();
  },OBJ.delayShow);

  $("#alert").mouseenter(function (){
    clearTimeout(window.delay)
    $(this).stop().animate({opacity:1},200);
  });

  $("#alert").mouseleave(function () { aHide(2000); });

  function aHide(dh) {
    if (OBJ.delayHide > 0) {
      window.delay = setTimeout(function () {
        $("#alert").animate({opacity:0},2000,function(){ $(this).remove(); });
      },OBJ.delayHide);
    }
  }
}
















// КОММЕНТАРИИ ВК
$.fn.vkComment = function(obj){
  var obj = $.extend({
    width:400,
    title:'Добавить заметку...',
    viewer_id:0,
    first_name:'',
    last_name:'',
    photo:''
    },obj);

  var THIS=this;

  var HTML="<DIV class=vkComment style=width:"+obj.width+"px;><DIV class=headBlue><DIV id=count><IMG src=/img/upload.gif></DIV>Заметки</DIV></DIV>";
  THIS.html(HTML);

  $.getJSON("/include/comment/AjaxCommentGet.php?"+G.values+"&table_name="+obj.table_name+"&table_id="+obj.table_id,function(res){
    obj.viewer_id=res[0].autor_viewer_id;
    obj.first_name=res[0].autor_first_name;
    obj.last_name=res[0].autor_last_name;
    obj.photo=res[0].autor_photo;

    var TX="<DIV id=add><TEXTAREA style=width:"+(obj.width-28)+"px;>"+obj.title+"</TEXTAREA>";
    TX+="<DIV class=vkButton><BUTTON onclick=null>Добавить</BUTTON></DIV></DIV>";
    THIS.find(".headBlue").after(TX);

    if(res[0].count>0)
      {
      var HTML='';
      for(n=0;n<res.length;n++)
        HTML+=createUnit({
                      id:res[n].id,
                      viewer_id:res[n].viewer_id,
                      first_name:res[n].first_name,
                      last_name:res[n].last_name,
                      photo:res[n].photo,
                      txt:res[n].txt,
                      child:res[n].child,
                      dtime_add:res[n].dtime_add
                      });
      THIS.find("#add").after(HTML); // выводим список комментариев
      THIS.find(".unit").hover(function(){ $(this).find(".img_del:first").show(); },function(){ $(this).find(".img_del:first").hide(); }); // показываем и убираем картинку удаления при наведении
      THIS.find(".img_del").click(function(){ commDel($(this).attr('val')); });
      THIS.find(".cdat A").click(function(){ commDopShow($(this)); });  // показ дополнительных комментариев
      }

    THIS.find("#add TEXTAREA").bind({
      click:function(){
        var BUT=$(this).next();
        if(BUT.is(':hidden'))
          {
          $(this).val('').css('color','#000').height(26);
          BUT.show().css('display','inline-block');
          frameBodyHeightSet();
          }
        },
      blur:function(){
        if(!$(this).val())
          {
          $(this).val(obj.title).css('color','#777').height(13).next().hide();
          frameBodyHeightSet();
          }
        }
      }).autosize({callback:frameBodyHeightSet});

    THIS.find("#add BUTTON").click(commAdd);
    commCount(res[0].count);
    });

  /* новый комментарий */
  function commAdd()
    {
    THIS.find("#add BUTTON").butProcess();
    $.post("/include/comment/AjaxCommentAdd.php?"+G.values,{table_name:obj.table_name,table_id:obj.table_id,parent_id:0,viewer_id:obj.viewer_id,txt:THIS.find("#add TEXTAREA").val()},function(res){
      THIS.find(".deleted").remove();
      THIS.find("#add").after(createUnit({
                      id:res.id,
                      viewer_id:obj.viewer_id,
                      first_name:obj.first_name,
                      last_name:obj.last_name,
                      photo:obj.photo,
                      txt:res.txt,
                      child:0,
                      dtime_add:res.dtime_add
                      }));
      THIS.find(".cdat A:first").click(function(){ commDopShow($(this)); });
      THIS.find("#add TEXTAREA")
        .val(obj.title)
        .css('color','#777')
        .height(13)
        .next()
        .remove()
        .end()
        .after("<DIV class=vkButton><BUTTON onclick=null>Добавить</BUTTON></DIV>");
      THIS.find("#add BUTTON").click(commAdd);
      THIS.find(".unit:first").hover(
        function(){ $(this).find(".img_del:first").show(); },
        function(){ $(this).find(".img_del:first").hide(); }
        );
      THIS.find(".img_del:first").click(function(){ commDel($(this).attr('val')); });
      commCount(res.count);
      },'json');
    }

  /* создание комментария */
  function createUnit(RES)
    {
    var UNIT="<DIV class=unit id=unit"+RES.id+"><TABLE cellspacing=0 cellpadding=0>";
    UNIT+="<TR><TD width=50><IMG src="+RES.photo+">";
    UNIT+="<TD width="+(obj.width-50)+">";
    if(RES.viewer_id==obj.viewer_id) UNIT+="<DIV class=img_del val="+RES.id+"></DIV>";
    UNIT+="<A href='http://vk.com/id"+RES.viewer_id+"' target='_blank' class=name>"+RES.first_name+" "+RES.last_name+"</A>";
    UNIT+="<DIV class=ctxt>"+RES.txt+"</DIV>";
    UNIT+="<DIV class=cdat>"+RES.dtime_add+"<SPAN> | <A href='javascript:' val="+RES.id+">Коммент"+(RES.child>0?'арии ('+RES.child+')':'ировать')+"</A></SPAN></DIV>";
    UNIT+="<DIV class=cdop></DIV>";
    UNIT+="<INPUT type=hidden value="+RES.child+">";
    UNIT+="</TABLE></DIV>";
    return UNIT;
    }

  /* создание дополнительного комментария */
  function createUnitDop(RES)
    {
    var DOP="<DIV class=dunit id=dunit"+RES.id+"><TABLE cellspacing=0 cellpadding=0>";
    DOP+="<TR><TD width=30><IMG src="+RES.photo+" width=30>";
    DOP+="<TD width="+(obj.width-85)+">";
    if(RES.viewer_id==obj.viewer_id) DOP+="<DIV class=img_minidel val="+RES.id+"></DIV>";
    DOP+="<A href='http://vk.com/id"+RES.viewer_id+"' target='_blank' class=dname>"+RES.first_name+" "+RES.last_name+"</A>";
    DOP+="<DIV class=dtxt>"+RES.txt+"</DIV>";
    DOP+="<DIV class=ddat>"+RES.dtime_add+"</DIV>";
    DOP+="</TABLE></DIV>";
    return DOP;
    }

  /* внесение дополнительного комментария */
  function commDopAdd(OB)
    {
    var ID=OB.attr('val');
    OB.butProcess();
    $.post("/include/comment/AjaxCommentAdd.php?"+G.values,{table_name:obj.table_name,table_id:obj.table_id,parent_id:ID,viewer_id:obj.viewer_id,txt:$("#unit"+ID+" TEXTAREA").val()},function(res){
      $("#unit"+ID+" .dadd").remove();
      $("#unit"+ID+" .cadd").remove();
      $("#unit"+ID+" .deleted").remove();
      $("#unit"+ID+" .cdop").append(createUnitDop({
                      id:res.id,
                      viewer_id:obj.viewer_id,
                      first_name:obj.first_name,
                      last_name:obj.last_name,
                      photo:obj.photo,
                      txt:res.txt,
                      dtime_add:res.dtime_add
                      }));
      $("#dunit"+res.id).hover(
        function(){ $(this).find(".img_minidel").show(); },
        function(){ $(this).find(".img_minidel").hide(); }
        );
      $("#dunit"+res.id+" .img_minidel").click(function(){ commDopDel($(this).attr('val')); });
      setArea(ID);
      },'json');
    }

  /* показ дополнительных комментариев и комментирование */
  function commDopShow(OB)
    {
    THIS.find(".cdat SPAN").show(); // показываем все ссылки 'комментарии'
    THIS.find(".cadd").remove();    // удаление всех TEXTAREA для добавления дополнительных комментариев
    var ID=OB.attr('val');
    CHILD=$("#unit"+ID+" INPUT").val();
    if(CHILD>0)
      {
      OB.parent().html(" <IMG src=/img/upload.gif>");
      commDopLoad(ID);
      }
    else
      {
      OB.parent().hide();
      var HTML="<DIV class=cadd><TEXTAREA style=width:"+(obj.width-77)+"px;></TEXTAREA><DIV class=vkButton><BUTTON val="+ID+" onclick=null>Добавить</BUTTON></DIV></DIV>";
      $("#unit"+ID+" .cdop").after(HTML);
      $("#unit"+ID+" TEXTAREA")
        .focus()
        .blur(function(){
          if(!$(this).val())
            {
            $("#unit"+ID+" .cdat SPAN").show();
            $(this).parent().remove();
            frameBodyHeightSet();
            }
          })
        .autosize({callback:frameBodyHeightSet});
      $("#unit"+ID+" BUTTON").click(function(){ commDopAdd($(this)); });
      }
    }

  /* загрузка списка дополнительных комментариев */
  function commDopLoad(ID)
    {
    $.getJSON("/include/comment/AjaxCommentDopGet.php?"+G.values+"&table_name="+obj.table_name+"&table_id="+obj.table_id+"&viewer_id="+obj.viewer_id+"&parent_id="+ID,function(res){
      var HTML='';
      for(n=0;n<res.length;n++)
        HTML+=createUnitDop({
                  id:res[n].id,
                  viewer_id:res[n].viewer_id,
                  first_name:res[n].first_name,
                  last_name:res[n].last_name,
                  photo:res[n].photo,
                  txt:res[n].txt,
                  dtime_add:res[n].dtime_add
                  });
      $("#unit"+ID+" .cdop").html(HTML);
      $("#unit"+ID+" .dunit").hover(
        function(){ $(this).find(".img_minidel").show(); },
        function(){ $(this).find(".img_minidel").hide(); }
        );
      $("#unit"+ID+" .img_minidel").click(function(){ commDopDel($(this).attr('val')); });
      setArea(ID);
      });
    }

  /* вставление TEXTAREA к дополнительным комментариям */
  function setArea(ID)
    {
    var HTML="<DIV class=dadd><TEXTAREA style=width:"+(obj.width-77)+"px;>Комментировать...</TEXTAREA><DIV class=vkButton><BUTTON val="+ID+" onclick=null>Добавить</BUTTON></DIV></DIV>";
    $("#unit"+ID+" .cdop").append(HTML);
    $("#unit"+ID+" .cdat SPAN").remove();
    $("#unit"+ID+" TEXTAREA").bind({
      click:function(){
        var BUT=$(this).next();
        if(BUT.is(":hidden"))
          {
          $(this).css('color','#000').val('').height(26);
          BUT.css('display','inline-block');
          frameBodyHeightSet();
          }
        },
      blur:function(){
        if(!$(this).val())
          {
          $(this).val('Комментировать...').css('color','#777').height(13).next().hide();
          frameBodyHeightSet();
          }
        }
      }).autosize({callback:frameBodyHeightSet});
    $("#unit"+ID+" BUTTON").click(function(){ commDopAdd($(this)); });
    frameBodyHeightSet();
    }

  /* удаление комментария */
  function commDel(ID)
    {
    $.post("/include/comment/AjaxCommentDel.php?"+G.values,{del:ID},function(res){
      $("#unit"+ID)
        .append("<CENTER>Заметка удалена. <A href='javascript:' val="+ID+">Восстановить</A></CENTER>")
        .addClass('deleted')
        .find("TABLE").hide();
      $("#unit"+ID+" A").click(function(){ commRec($(this).attr('val')); });
      commCount(res.count);
      },'json');
    }

  /* восстановление комментария */
  function commRec(ID)
    {
    $.post("/include/comment/AjaxCommentRec.php?"+G.values,{rec:ID},function(res){
      $("#unit"+ID).removeClass('deleted');
      $("#unit"+ID+" CENTER").remove();
      $("#unit"+ID+" TABLE").show();
      commCount(res.count);
      },'json');
    }

  /* удаление дополнительного комментария */
  function commDopDel(ID)
    {
    $.post("/include/comment/AjaxCommentDopDel.php?"+G.values,{del:ID},function(res){
      $("#dunit"+ID)
        .append("<CENTER>Комментарий удалён. <A href='javascript:' val="+ID+">Восстановить</A></CENTER>")
        .addClass('deleted')
        .find("TABLE").hide();
      $("#dunit"+ID+" A").click(function(){ commDopRec($(this).attr('val')); });
      frameBodyHeightSet();
      });
    }

  /* восстановление дополнительного комментария */
  function commDopRec(ID)
    {
    $.post("/include/comment/AjaxCommentDopRec.php?"+G.values,{rec:ID},function(res){
      $("#dunit"+ID).removeClass('deleted');
      $("#dunit"+ID+" CENTER").remove();
      $("#dunit"+ID+" TABLE").show();
      frameBodyHeightSet();
      });
    }
  }

/* вывод количества комментариев */
function commCount(C)
  {
  var TX;
  if(C>0)
    {
    var END='ок';
    if(Math.floor(C/10%10)!=1)
      switch(C%10)
        {
        case 1: END='ка'; break;
        case 2: END='ки'; break;
        case 3: END='ки'; break;
        case 4: END='ки'; break;
        }
    TX="Всего "+C+" замет"+END;
    }
  else TX="Заметок нет";
  $(".vkComment #count").html(TX);
  frameBodyHeightSet();
  }

















//УСТАНОВКА ВЫСОТЫ ФРЕЙМА КОНТАКТА ПОД РАЗМЕР ОКНА
function frameBodyHeightSet(y) {
  var FB = document.getElementById('frameBody');
  if (!y) { FB.style.height = 'auto'; }
  var H = FB.offsetHeight-1;
  if(y && y > H) {
    H = y;
    FB.style.height = (H + 1) + 'px';
  }
  VK.callMethod('resizeWindow', 625, H);
}





//УПРАВЛЕНИЕ КУКАМИ
function setCookie(name,value) {
  var exdate = new Date();
  exdate.setDate(exdate.getDate() + 1);
  document.cookie=name + "=" + value + "; path=/; expires=" + exdate.toGMTString();
}
function delCookie(name) {
  var exdate = new Date();
  exdate.setDate(exdate.getDate()-1);
  document.cookie = name + "=; path=/; expires=" + exdate.toGMTString();
}
function getCookie(name) {
  var arr1 = document.cookie.split(name);
  if (arr1.length > 1) {
    var arr2 = arr1[1].split(/;/);
    var arr3 = arr2[0].split(/=/);
    return arr3[0] ? arr3[0] : arr3[1];
  } else {
    return null;
  }
}












//МАТОВЫЙ ФОН
function opFonSet() {
  if($("#opFon").length == 0) {
    $("#frameBody").after("<DIV id=opFon></DIV>");
    var H = document.getElementById('frameBody').offsetHeight
    $("#opFon").css('height',H);
  }
}







// диалог 2013-02-04 14:46
// * Поле диалога вставляется только в тег с id=мой_ид,
// * при этом имеет абсолютное позиционирование.
// * Таким образом можно задавать дополнительные стили в соответствии с содержимым самой страницы.
$.fn.vkDialog = function (obj) {
  G.zindex += 10;
  var t = $(this);
  var id = t.attr('id');
  var obj = $.extend({
    width:360,
    top:100,                     // отступ сверху с учётом скрола
    head:'head: Название заголовка',
    content:'content: содержимое центрального поля',
    submit:function () {},  // функция, которая выполняется при нажатии синей кнопки
    cancel:function () {}, // функция, которая выполняется при нажатии кнопки отмена
    focus:'',                     // установка фокуса на указанный элемент в виде #focus
    butSubmit:'Внести',
    butCancel:'Отмена'
  }, obj);

  var html = "<DIV class=backfon id=backfon_" + id + "></DIV>";

  html += "<DIV class=vk_dialog id=vk_dialog_" + id + ">";
    html += "<DIV class=head><DIV><A class=img_del val=dialog_close></A>" + obj.head + "</DIV></DIV>";
    html += "<DIV class=content>" + obj.content + "</DIV>";
    html += "<DIV class=bottom>";
      if (obj.butSubmit) { html += "<DIV class=vkButton><BUTTON val=dialog_submit>" + obj.butSubmit + "</BUTTON></DIV>"; }
      if (obj.butCancel) { html += "<DIV class=vkCancel><BUTTON val=dialog_cancel>" + obj.butCancel + "</BUTTON></DIV>"; }
    html += "</DIV>";
  html += "</DIV>";

  t.html(html);

  // настройка заднего фона
  var backfon = $("#backfon_" + id);
  var h = $("#frameBody").height();
  backfon.css({'z-index':G.zindex, height:h + 'px'});

  var dialog = $("#vk_dialog_" + id);

  dialog
    .css({
      width:obj.width + 'px',
      top:$(window).scrollTop() + G.vkScroll + obj.top + 'px',
      left:313 - Math.round(obj.width / 2) + 'px',
      'z-index':G.zindex + 5
    })
    .on('click', function (e) {
      var val = $(e.target).attr('val');
      if (val) {
        var arr = val.split('_');
        if (arr[0] == 'dialog') {
          switch (arr[1]) {
          case 'close': dialogClose(); break;
          case 'submit': obj.submit(); break;
          case 'cancel': obj.cancel(); dialogClose(); break;
          }
        }
      }
    });


  if(obj.focus) { $(obj.focus).focus().select(); } // установка фокуса


  function dialogClose() {
    backfon.remove();
    dialog.remove();
    G.zindex -= 10;
  }


    t.o = {
        close:dialogClose,
        process:function () {
            var but = dialog.find(".vkButton BUTTON:first");
            var width = but.parent().css('width');
            but.html("<IMG src=/img/upload.gif class=vk_dialog_upload>").css({width:width});
        },
        process_cancel:function () {
            dialog.find(".vkButton BUTTON:first").html(obj.butSubmit);
        }
    };
  return t;
};








//диалог
function dialogShow(obj) {
  var obj = $.extend({
    width:360,
    head:'head: Название заголовка',
    content:'content: содержимое центрального поля',
    submit:'',    /* функция, которая выполняется при нажатии синей кнопки */
    cancel:'',   /* функция, которая выполняется при нажатии кнопки отмена */
    top:100,    /* отступ сверху с учётом скрола */
    focus:'',    /* установка фокуса на указанный элемент в виде #focus */
    butSubmit:'Внести'
    },obj);
  opFonSet();
  var HTML="<DIV id=dialog style=width:"+obj.width+"px;><H1><DIV><A href='javascript:'>&nbsp;</A>" + obj.head + "</DIV></H1>";
  HTML+="<H2>"+obj.content+"</H2>";
  HTML+="<H3><CENTER><DIV class=vkButton><BUTTON id=butDialog onclick=null>"+obj.butSubmit+"</BUTTON></DIV>&nbsp;&nbsp;<DIV class=vkCancel><BUTTON>Отмена</BUTTON></DIV></CENTER></H3></DIV>";
  $("#frameBody").after(HTML);
  var LEFT=313-(obj.width/2);
  $("#dialog")
    .css('top',$(window).scrollTop()+G.vkScroll+obj.top)
    .css('left',LEFT)
    .show()
    .find("H1:first A").click(dialogHide).end()
    .find(".vkCancel:first BUTTON").click(dialogHide).end()
    .find(".vkButton:first BUTTON").click(obj.submit);
  if(obj.cancel) {
    $("#dialog .vkCancel BUTTON").click(obj.cancel);
  }
  if(obj.focus) { $(obj.focus).focus().select(); }
}
function dialogHide() {
  if($("#dialog").length>0) $("#dialog").remove();
  if($("#opFon").length>0) $("#opFon").remove();
}













// СООБЩЕНИЕ О РЕЗУЛЬТАТЕ ВЫПОЛНЕННЫХ ДЕЙСТВИЙ
function vkMsgOk(txt) {
  var obj = $("#vkMsgOk");
  if (obj.length > 0) { obj.remove(); }
  $("BODY").append("<DIV id=vkMsgOk>"+txt+"</DIV>");
  $("#vkMsgOk")
    .css('top',$(this).scrollTop() + 200 + G.vkScroll)
    .delay(1200)
    .fadeOut(400,function(){ $(this).remove(); });
  }



















// вывод списка 2013-01-29 18:03
(function () {
  var Spisok = function () {};

  // подготовка к выводу списка после загрузки страницы
  Spisok.prototype.create = function (obj) {
    if (!obj) { var obj = {}; }


    // ---== общие переменные ==---
    this.view = obj.view || (function () { throw new Error("Не указано место для результата: view"); })();
    this.view_prev = this.view;     // сохранение изначального места вывода списка
    this.start = 0; // начало чтения списка
    this.limit = obj.limit || 0;             // количество выводимых элементов списка. 0 - неограниченно
    this.result = obj.result || '';        // результат в верхней строке
    this.ends = obj.ends || '';         // список окончаний в виде {'$end':['1', '2-3', '0,5-20']};
    this.result_view = obj.result_view || null; // место для результата о количестве полученных элементов в верхней строке
    this.result_dop = obj.result_dop || ''; // дополнительная информация для результата в верхней строке
    this.next_txt = obj.next_txt || "Показать ещё..."; // текст, который показывается на ссылке продолжения списка
    this.continued = 0; // продолжался список или нет
    this.nofind = obj.nofind || "Запрос не дал результатов";
    this.callback = obj.callback || null; // функция, выполняемая после вывода списка


    // ---== url - переменные ==---
    this.url = obj.url ? obj.url + "?" + G.values : null ; // адрес, по которому будет получаться список
    this.cache_spisok = obj.cache_spisok || null; // если список был сохранён в кеше, тогда сначала вывод его
    this.imgup = obj.imgup || null; // место для картинки ожидания загрузки
    this.imgPlace = this.imgup;
    this.values = obj.values || {}; // список дополнительных переменных, отправляемых при запросе
    this.data = null;       // результат, полученный из url-запроса
    this.preload = null;  // массив с продолжения списка, чтобы не ожидать загрузки
    this.a = obj.a || null; // вывод готовой ссылки в верхней части экрана
    this.cache = []; // кеширование результатов
    this.key = ''; // содержимое ключа массива кеша. Если ключ совпал, то кеш по этому ключу будет обновлён

    // ---== json - переменные ==---
    this.json = obj.json || null; // готовый список элементов, если нет url

    this.print();
  };


  // составление элемента списка
  Spisok.prototype.unit = function (sp) { return sp.id; };

  // выполняемая условия перед выводо списка при json
  Spisok.prototype.condition = function () {};



  // очередная печать списка при изменении условий
  Spisok.prototype.print = function (obj) {
    // если список не продолжался, то сбрасывается стартовая позиция
    if (this.continued == 0) { this.start = 0; } else { this.continued = 0; }

    if (this.json) {
      if (obj) {
        for (var k in obj) { this.values[k] = obj[k]; }
        this.condition(this.values);
      }
      json(this);
    } else if (this.url) {
      url(this, obj);
    } else {
      throw new Error("Отсутствует выбор вывода списка: url или json");
    }
  };










  // подготовка к выводу списка из url
  function url(t, obj) {
    // рисование ожидания загрузки, если требуется
    if (typeof t.imgup == 'string') { t.imgPlace = $(t.imgup); } // если указана строка, перевод в объект. Нужно, если место для картинки меняется
    if (t.imgPlace) {
      t.imgPlace.find(".imgUpSpisok").remove();
      t.imgPlace.append("<IMG src=/img/upload.gif class=imgUpSpisok>");
    }

    // обновление дополнительных переменных, если необходимо
    if (typeof obj == 'object') {
      for (var k in obj) {
        t.values[k] = obj[k];
      }
    }

    // составление url из дополнительных переменных
    var val = '';
    for (var k in t.values) { val += "&" + k + "=" + t.values[k]; }
    if (t.preload) { t.start += t.limit; }
    val += "&start=" + t.start;
    val += "&limit=" + t.limit;

    if (t.a) { $("BODY").find("#urla").remove().end().prepend("<A href='" + t.url + val + "' id=urla>" + t.url + val + "</A>"); } // показ полного url-запроса

    var key = encodeURIComponent(val).replace(/%/g,'');

    if (t.cache[key] && t.key != key) {
      if (!t.preload) { t.key = key; } // если не предзагрузка, то обновление ключа массива кеша
      getted(t, t.cache[key]);
    } else {
      $.getJSON(t.url + val, function (data) {
        t.data = data;
        if (!t.preload) { t.key = key; }
        t.cache[key] = data;
        getted(t, data);
      });
    }

    function getted(t, data) {
      if (t.preload) {
        t.preload = null;
        $("#ajaxNext")
          .html(t.next_txt)
          .click(function () { t.view = $(this).parent(); url_print(t, data); });
      } else { url_print(t, data); }
    }
  } // end url






  // вывод списка
  function url_print(t, data) {
    var len = data.spisok.length;
    if (len > 0) {
      var html = html_create(t, data.spisok);

      if (data.next == 1) { html += "<DIV><DIV id=ajaxNext><IMG SRC=/img/upload.gif></DIV></DIV>"; }

      t.view.html(html);
      t.view = t.view_prev;

      t.cache_spisok = null; // обнуление кеша, если был

      if (data.next == 1) {
        t.preload = 1;
        url(t);
      }
    } else { t.view.html("<DIV class=findEmpty>" + t.nofind + "</DIV>"); }

    // удаление картинки ожидания загрузки
    if (t.imgPlace) { t.imgPlace.find(".imgUpSpisok").remove(); }

    print_result(t, data.all);
    if (t.callback) { t.callback(data.spisok); }

    frameBodyHeightSet();
  }







  function html_create(t, data) {
    var html = '';
    for(var n = 0; n < data.length; n++) {
      var sp = data[n];
      sp.num = n + t.start;  // добавление в массив порядкового номера элемента списка
      html += "<DIV class=unit id=unit_" + sp.id + ">" + t.unit(sp) + "</DIV>";
    }
    return html;
  }







  // подготовка к выводу списка из json
  function json(t) {
    var all = t.json.length;
    var len = 0;
    var data = [];
    var next = 0;
    if(t.limit > 0) {
      len = t.start + t.limit;
      if (len > all) { len = all; } else { next = 1; }
      for (var n = t.start; n < len; data.push(t.json[n]), n++);
    } else {
      len = all;
      data = t.json;
    }
  print(t, data, next, all);
  }




  // вывод списка
  function print(t, data, next, all) {
    var len = data.length;
    if (len > 0) {
      var html = html_create(t, data);
      if (next == 1) { html += "<DIV><DIV id=ajaxNext>" + t.next_txt + "</DIV></DIV>"; }

      t.view.html(html);
      t.view = t.view_prev;

      if (next == 1) {
        $("#ajaxNext").click(function () {
          t.view = $(this).parent();
          t.start += t.limit;
          t.continued = 1;
          t.print();
        });
      }
    } else { t.view.html("<DIV class=findEmpty>" + t.nofind + "</DIV>"); }

    print_result(t, all);
    if (t.callback) { t.callback(data); }

    frameBodyHeightSet();
  }




  // вывод результата в верхней строке
  function print_result(t, all) {
    if (t.result_view && t.result) {
      var result;
      if (all > 0) {
        result = t.result.replace("$count", all);
        if (t.ends) {
          for (var k in t.ends) { result = result.replace(k, end(all, t.ends[k])); }
        }
      } else { result = t.nofind; }
    t.result_view.html(result + t.result_dop);
    }
  }




  // формирование окончаний
  function end(count, arr) {
    if (arr.length == 2) { arr.push(arr[1]); } // если в массиве всего 2 элемента, то увеличение его, копируя второй элемент в третий
    var send = arr[2];
    if(Math.floor(count / 10 % 10) != 1) {
      switch(count % 10) {
      case 1: send = arr[0]; break;
      case 2: send = arr[1]; break;
      case 3: send = arr[1]; break;
      case 4: send = arr[1]; break;
      }
    }
    return send;
  }



  G.spisok = new Spisok();
})();





























function op(obj, name){
  var S = {};
  var T = new Date().getTime();
  if(!S.op) {
    S.op = {obj:[]};
    $("BODY").append("<DIV id=op><H1></H1><H2></H2></DIV>");
/*    S.op.elem.style.cssText = "
      position:absolute;
      top:0px;
      left:0px;
      width:800px;
      border:#CCC solid 1px;
      background-color:#FFF;
      padding:33px 10px 10px 10px;
      margin:15px;
      index:10000;";
      */
//    style.setProperty('background-color','#FFa');
 //   style.setProperty('','');
    $("#op")
      .css({
        position:'absolute',
        top:'0px',
        left:'0px',
        width:'800px',
        border:'#CCC solid 1px',
        'background-color':'#FFF',
        padding:'33px 10px 10px 10px',
        margin:'15px',
        index:'10000'
      })
      .find("H1").css({
        width:'780px',
        border:'#DDD solid 1px',
        padding:'3px 8px',
        'background-color':'#EFE',
        'margin-bottom':'10px',
        position:'fixed',
        top:'25px'
       });
    //S.op.elem.style.cssText = "position: absolute; top: 0px; left: 0px; width: 800px; border-top-color: rgb(204, 204, 204); border-left-color: rgb(204, 204, 204); border-right-color: rgb(204, 204, 204); border-bottom-color: rgb(204, 204, 204); border-top-width: 1px; border-left-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-top-style: solid; border-left-style: solid; border-right-style: solid; border-bottom-style: solid; background-color: rgb(255, 255, 255); padding-top: 33px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; margin-top: 15px; margin-right: 15px; margin-bottom: 15px; margin-left: 15px;"
// alert(S.op.elem.style.cssText)
    }

  S.op.obj.push({
    obj:obj,
    name:name ? name : 'object'
  });

  var html = '';
  for (var i in obj) {
   var o;
    if (i == 'external'
     || i == 'selectionDirection'
     || i == 'selectionEnd'
     || i == 'selectionStart'
      ) { o = 'noview'; } else { o = obj[i];}

    var rep = '';
    if (typeof o != 'undefined' && typeof o != 'object') {
      rep = o
        .toString()
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/\n/g,'<BR>');
    }
    var info = ": " + (rep ? rep : o) + " <TT>(" + typeof o + ")</TT>";
    info = "<A href='javascript:' val='" + i + "'><B>" + i + "</B></A>" + info;
    html += "<DIV>" + info + "</DIV>";
  }

  $("#op H2:first")
    .html(html)
    .find("DIV").css({margin:'3px',color:'#444'}).end()
    .find("TT").css('color','#AA0');

  html = "<EM style=float:right;margin-left:10px;><A href='javascript:' val=999>Закрыть</A></EM>";
  html += "<EM style=float:right;margin-left:10px;><A href='javascript:' val=777>Обновить</A></EM>";
  html += "<SPAN style=float:right;></SPAN>";
  var len = S.op.obj.length;
  for(var n = 0; n < len; n++) {
    html += ".";
    if (n < len - 1) {
      html += "<A href='javascript:' val=" + n + ">" + S.op.obj[n].name + "</A>";
    } else {
      html += S.op.obj[n].name;
    }
  }
  $("#op H1:first").html(html);

  $("#op H2:first A").click(function () {
    var i = $(this).attr('val');
    var len = S.op.obj.length - 1;
    op(S.op.obj[len].obj[i], i);
  });

  $("#op H1:first A").click(function () {
    var i = parseInt($(this).attr('val'));
    if (i == 999) {
      var el = document.getElementById('op');
      el.parentNode.removeChild(el);
      S.op.obj = [];
      return;
    } else if (i != 777) {
      var len = S.op.obj.length;
      var obj;
      while (len > i + 1) {
        S.op.obj.pop();
        len--;
      }
    }
    obj = S.op.obj.pop();
    op(obj.obj, obj.name);
  });

   $("#op H1:first SPAN:first").html(new Date().getTime() - T);
}



