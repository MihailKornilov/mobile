Highcharts.setOptions({
	lang: {
		months: ['������', '�������', '����', '������', '���', '����', '����', '������', '��������', '�������', '������', '�������']
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
		text: '����� ������� �� �������'
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