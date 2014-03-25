Highcharts.setOptions({
	lang: {
		months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
	}
});
$('#statistic').highcharts('StockChart', {
	chart: {
		zoomType: 'x',
		type: 'spline'
	},

	rangeSelector: {
		enabled: false
	},
	title: {
		text: 'Сумма прихода по месяцам'
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
			color:'#922',
			data: statRashod,
			tooltip: {
				valueDecimals: 0
			}
		}
	]
});

$(document).ready(function() {

});