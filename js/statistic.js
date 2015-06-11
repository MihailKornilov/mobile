Highcharts.setOptions({
	lang: {
		months: ['������', '�������', '����', '������', '���', '����', '����', '������', '��������', '�������', '������', '�������']
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
		text: '����� ������� � ������� �� �������'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: '������',
			color:'#2A2',
			data: statPrihod,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: '������',
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
		text: '���������� ����� �������� �� �������'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: '�������',
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
		text: '������ �� ����'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: '����� ������',
			color:'#6E6EC5',
			data: ZAYAV_COUNT,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: '���������',
			color:'#25B025',
			data: ZAYAV_OK,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: '��������',
			color:'#D30000',
			data: ZAYAV_FAIL,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: '������',
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
		text: '������ �� �������'
	},
	legend: {
		enabled: true
	},
	series: [
		{
			name: '����� ������',
			color:'#6E6EC5',
			data: ZAYAVMON_COUNT,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: '���������',
			color:'#25B025',
			data: ZAYAVMON_OK,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: '��������',
			color:'#D30000',
			data: ZAYAVMON_FAIL,
			tooltip: {
				valueDecimals: 0
			}
		},
		{
			name: '������',
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