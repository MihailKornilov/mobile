var	hashLoc,
	hashSet = function(hash) {
		if(!hash && !hash.p)
			return;
		hashLoc = hash.p;
		var s = true;
		switch(hash.p) {
			case 'client':
				if(hash.d == 'info')
					hashLoc += '_' + hash.id;
				break;
			case 'zayav':
				if(hash.d == 'info')
					hashLoc += '_' + hash.id;
				else if(hash.d == 'add')
					hashLoc += '_add' + (REGEXP_NUMERIC.test(hash.id) ? '_' + hash.id : '');
				else if(!hash.d)
					s = false;
				break;
			case 'zp':
				if(hash.d == 'info')
					hashLoc += '_' + hash.id;
				else
					s = false;
				break;
			default:
				if(hash.d) {
					hashLoc += '_' + hash.d;
					if(hash.d1)
						hashLoc += '_' + hash.d1;
				}
		}
		if(s)
			VK.callMethod('setLocation', hashLoc);
	};

$(document)
	.on('click', '.sa_viewer_msg .leave', function() {
		_cookie('sa_viewer_id', '');
		document.location.href = URL + '&p=sa&d=ws';
	});
