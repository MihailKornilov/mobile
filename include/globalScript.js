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

// контактовский селект 2013-02-14 23:19
$.fn.vkSel = function(obj) {
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

    html += "<TABLE class=main style=width:" + obj.width + "px;>";
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

    val:function(val) { // установка либо получение значения
      if(val != undefined) {
        inp_set(val);
        return this;
      } else { return obj.value; }
    },

    title:function() {
        return inp.val();
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
          "<TABLE class=cal_head><TR><TD class=cal_back val=cal_back><TD class=cal_month><TD class=cal_next val=cal_next></TABLE>" +
          "<TABLE class=cal_week_name><TR><TD>Пн<TD>Вт<TD>Ср<TD>Чт<TD>Пт<TD>Сб<TD>Вс</TABLE>" +
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
    var html = "<TABLE><TR>";

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

$.fn.linkMenu = function (obj) {
    /* Выпадающее меню по ссылке
     * id указывается из INPUT hidden
     */
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

$.fn.infoLink = function(obj) {/* меню - список  */
  var obj = $.extend({
    spisok:[],
    func:''
    },obj);
  var dl = '';
  for (var n = 0; n < obj.spisok.length; n++) {
      dl += "<DD val=" + obj.spisok[n].uid + ">" + obj.spisok[n].title;
  }
  var TS = $(this);
  TS.addClass('infoLink')
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
