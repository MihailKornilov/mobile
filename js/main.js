
$(document)
	.on('click', '.sa_viewer_msg .leave', function() {
		_cookie('sa_viewer_id', '');
		document.location.href = URL + '&p=sa&d=ws';
	});
