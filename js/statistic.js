Highcharts.setOptions({
	lang: {
		months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
	}
});
$('#statistic').highcharts('StockChart', {
	chart: {
		zoomType: 'x',
		type: 'column'
	},

	rangeSelector: {
		enabled: true,
		selected: 4
	},
	title: {
		text: 'Сумма прихода и расхода по месяцам'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: 'Приход',
			color:'#2A2',
			data: statPrihod,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: 'Расход',
			color:'#b22',
			data: statRashod,
			tooltip: {
				valueDecimals: 0
			}
		}
	]
});

$('#client-count').highcharts('StockChart', {
	chart: {
		zoomType: 'x',
		type: 'spline'
	},

	rangeSelector: {
		enabled: true,
		selected: 4
	},
	title: {
		text: 'Количество новых клиентов по месяцам'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: 'Клиенты',
			color:'#48a',
			data: CLIENT_COUNT,
			tooltip: {
				valueDecimals: 0
			}
		}
	]
});

$('#zayav-count').highcharts('StockChart', {
	chart: {
		zoomType: 'x',
		type: 'spline'
	},

	rangeSelector: {
		enabled: true,
		selected: 0
	},
	title: {
		text: 'Заявки по дням'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: 'Новые заявки',
			color:'#6E6EC5',
			data: ZAYAV_COUNT,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: 'Выполнены',
			color:'#25B025',
			data: ZAYAV_OK,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: 'Отменены',
			color:'#D30000',
			data: ZAYAV_FAIL,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: 'Выданы',
			color:'#eeee00',
			data: ZAYAV_SENT,
			tooltip: {
				valueDecimals: 0
			}
		}
	]
});

$('#zayav-count-mon').highcharts('StockChart', {
	chart: {
		zoomType: 'x',
		type: 'spline'
	},

	rangeSelector: {
		enabled: true,
		selected: 4
	},
	title: {
		text: 'Заявки по месяцам'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: 'Новые заявки',
			color:'#6E6EC5',
			data: ZAYAVMON_COUNT,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: 'Выполнены',
			color:'#25B025',
			data: ZAYAVMON_OK,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: 'Отменены',
			color:'#D30000',
			data: ZAYAVMON_FAIL,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: 'Выданы',
			color:'#eeee00',
			data: ZAYAVMON_SENT,
			tooltip: {
				valueDecimals: 0
			}
		}
	]
});


$(document).ready(function() {
});