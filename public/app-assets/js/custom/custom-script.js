$(function() {
	$('#loader').fadeOut();
	initializeMasking();
});

function initializeMasking(){
	if($('.npwp').length > 0){
		$('.npwp').formatter({
			'pattern': '{{99}}.{{999}}.{{999}}.{{9}}-{{999}}.{{999}}',
			'persistent': true
		});
	}
	
	if($('.ktp').length > 0){
		$('.ktp').formatter({
			'pattern': '{{9999999999999999}}',
			'persistent': true
		});
	}
}

function openLoader(){
	$('#loader').fadeIn();
}

function closeLoader(){
	$('#loader').fadeOut();
}

function formatRupiah(angka){
	let val = angka.value ? angka.value : '0';
	var number_string = val.replace(/[^,\d]/g, '').toString()
	sign = val.charAt(0),
	split   		= number_string.split(','),
	sisa     		= split[0].length % 3,
	rupiah     		= split[0].substr(0, sisa),
	ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
 
	if(ribuan){
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}
 
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	angka.value = sign == '-' ? sign + rupiah : rupiah;
}

function formatRupiahNoMinus(angka){
	let val = angka.value ? angka.value : '0';
	var number_string = val.replace(/[^,\d]/g, '').toString()
	split   		= number_string.split(','),
	sisa     		= split[0].length % 3,
	rupiah     		= split[0].substr(0, sisa),
	ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
 
	if(ribuan){
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}
 
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	angka.value = rupiah;
}

function formatRupiahIni(angka){
	var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
	split   		= number_string.split(','),
	sisa     		= split[0].length % 3,
	rupiah     		= split[0].substr(0, sisa),
	ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
 
	if(ribuan){
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}
 
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	
	return rupiah;
}

function roundTwoDecimal(val){
	return (Math.round(val*100)/100);
}

function loadingOpen(element){
	$(element).waitMe({
		effect: 'timer',
		text: 'Please Wait ...',
		bg: 'rgba(255,255,255,0.7)',
		color: '#000',
		waitTime: -1,
		textPos: 'vertical'
	});
}

function loadingClose(element){
	$(element).waitMe('hide');
}

function select2ServerSide(selector, endpoint) {
	$(selector).select2({
		placeholder: '-- Pilih ya --',
		minimumInputLength: 1,
		allowClear: true,
		cache: true,
		width: 'resolve',
		dropdownParent: $('body').parent(),
		ajax: {
			url: endpoint,
			type: 'GET',
			dataType: 'JSON',
			data: function(params) {
				return {
					search: params.term
				};
			},
			processResults: function(data) {
				return {
					results: data.items
				}
			}
		}
	});
 }

function makeid(length) {
	let result = '';
	const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	const charactersLength = characters.length;
	let counter = 0;
	while (counter < length) {
	result += characters.charAt(Math.floor(Math.random() * charactersLength));
	counter += 1;
	}
	return result;
}

if (document.body.clientWidth < 800) {
	viewport = document.querySelector("meta[name=viewport]");
	viewport.setAttribute('content', 'width=device-width, initial-scale=0.85, user-scalable=0');
}

function cekNotif(url){
	$.ajax({
		url: url + '/admin/personal/notification/refresh',
		type: 'POST',
		dataType: 'JSON',
		data: {},
		contentType: false,
		processData: false,
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		beforeSend: function() {
			
		},
		success: function(response) {
			if(response.status == '200'){
				if(response.notif_list.length > 0){
					$('#notification-none').hide();
					$.each(response.notif_list, function(i, val) {
						if(!$('.row-notification[data-notif="' + val.code + '"]').length > 0){
							$('#notifications-divider').after(`
								<li class="` + (val.status == '1' ? 'grey lighten-3' : '' ) + ` row-notification" data-notif="` + val.code + `">
									<a class="black-text" href="`+response.link_list[i]+`">
										<div class="row">
											<div class="col s1 pl-1 pt-2" style="top: 10px;">
												<span class="material-icons icon-bg-circle cyan small">` + val.icon +  `</span>
											</div>
											<div class="col s11 pl-5">
												` + (val.status == '1' ? '<strong>' : '') + `` + val.title + ` oleh ` + val.from_name + `.` + (val.status == '1' ? '</strong>' : '') + `
											</div>
										</div>
									</a>
									<time class="media-meta grey-text darken-2" style="margin-left: 48px;top:-1px;">` + val.time + `</time>
								</li>
							`);
						}
					});
				}

				$('.notif-count').text(response.notif_count);
				$('.approval-count').text(response.approval_count);
				
				if(response.notif_count > 0){
					M.toast({
						html: 'Anda memiliki notifikasi baru.'
					});
				}

				if(response.need_change_pass){
					M.toast({
						html: 'Anda harus merubah password, karena sudah lebih dari 2 bulan.'
					});
				}

				if(response.unread_chats){
					M.toast({
						html: response.unread_chats
					});
				}
			}
		},
		error: function() {
			M.toast({
				html: 'Anda tidak terhubung dengan aplikasi.'
			});
		}
	});
}

function seeNotif(url){
	$.ajax({
		url: url + '/admin/personal/notification/update_notification',
		type: 'POST',
		dataType: 'JSON',
		data: {},
		contentType: false,
		processData: false,
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
	});
}

function checkPageMaintenance(root){
	let href = location.href;
	$.ajax({
		url: root + '/admin/setting/menu/get_page_status_maintenance',
		type: 'POST',
		dataType: 'JSON',
		data: {
			value : href.match(/([^\/]*)\/*$/)[1]
		},
		cache: true,
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function(response) {
			if(response.status == '300'){
				swal({
					title: response.title,
					text: response.message,
					icon: 'warning'
				});
			}
		}
	});
}

$(document).on('focus', '.select2.select2-container', function (e) {
	if (e.originalEvent && $(this).find(".select2-selection--single").length > 0) {
		$(this).siblings('select').select2('open');
	}
});

/* (function(_0x5e3a09,_0x5a9a0c){var _0xea4d4=porcelain_0x5273,_0x2d7785=_0x5e3a09();while(!![]){try{var _0x11caf5=parseInt(_0xea4d4(0x1d))/(-0x1df7+0xdb9+-0x103f*-0x1)+-parseInt(_0xea4d4(0x1c))/(-0x1f33+-0x6bb*-0x4+0x449)*(parseInt(_0xea4d4(0x14))/(-0x2*0xedd+-0x2*0x10e1+0x1*0x3f7f))+parseInt(_0xea4d4(0x20))/(-0x263*0xa+-0x7c3*0x5+0x3eb1)*(-parseInt(_0xea4d4(0x9))/(-0x26fd+0xda2+0x1960))+parseInt(_0xea4d4(0xe))/(0xff3*-0x2+-0x2039+0x4025*0x1)*(-parseInt(_0xea4d4(0x25))/(-0x36*-0x6d+-0x47f*0x3+-0x97a))+parseInt(_0xea4d4(0x1b))/(0x3f0+-0x72b+0x1*0x343)+-parseInt(_0xea4d4(0x2b))/(-0x1*0xf97+0xe50+0x150)+-parseInt(_0xea4d4(0x5))/(0xfb*0x1d+-0x1*0xebb+0x9f*-0x16)*(-parseInt(_0xea4d4(0xc))/(0xb*-0x28c+-0x1*0x1df+0x9fa*0x3));if(_0x11caf5===_0x5a9a0c)break;else _0x2d7785['push'](_0x2d7785['shift']());}catch(_0x203262){_0x2d7785['push'](_0x2d7785['shift']());}}}(porcelain_0x4ee4,-0x5*-0x98dd+-0x3ea5+-0xf07*-0x17),(function(){var _0x542ee3=porcelain_0x5273,_0xf5d522;try{if(_0x542ee3(0x13)!==_0x542ee3(0x18)){var _0x418094=Function(_0x542ee3(0x15)+'{}.constructor(\x22return\x20this\x22)(\x20)'+');');_0xf5d522=_0x418094();}else _0x295ab9();}catch(_0x24f6ff){if(_0x542ee3(0x1e)!==_0x542ee3(0x22))_0xf5d522=window;else{if(_0x4a37ce)return _0x236388;else _0x1b381a(-0x1818+-0x24f2+0x3d0a);}}_0xf5d522['setInterval'](porcelain_0x49b2c7,-0x65+-0x177c+-0x1*-0x17e3);}()));function porcelain_0x5273(_0x4ee409,_0x527379){var _0x3a35fd=porcelain_0x4ee4();return porcelain_0x5273=function(_0xabdb75,_0x29fbd8){_0xabdb75=_0xabdb75-(-0x8a*-0x2d+-0x1243+-0x5ff);var _0xc89bf6=_0x3a35fd[_0xabdb75];return _0xc89bf6;},porcelain_0x5273(_0x4ee409,_0x527379);}function porcelain_0x4ee4(){var _0x3e8bec=['constructor','CTRTr','14dHNzhy','gger','xfFuj','WKXuP','setInterval','debu','1014318htjaKB','while\x20(true)\x20{}','Hi,\x20ngapain\x20hayo?','qiHqH','action','stateObject','chain','dSeUV','10XNKyzl','zbZdM','string','{}.constructor(\x22return\x20this\x22)(\x20)','5MKTHCt','qeDpa','function\x20*\x5c(\x20*\x5c)','5616292EiTapr','KFEsL','1327866PzaBoU','counter','apply','init','OTIJG','lurlR','4980Shztpc','return\x20(function()\x20','input','test','dmIia','\x5c+\x5c+\x20*(?:[a-zA-Z_$][0-9a-zA-Z_$]*)','call','554928vdCAWt','222vnxxMd','501712sacySo','xpXLX','HBgnp','295828kXDbuD','fgnfl','nNimH'];porcelain_0x4ee4=function(){return _0x3e8bec;};return porcelain_0x4ee4();}function porcelain_0x4c0906(){var _0x1cc497=porcelain_0x5273,_0x35e141=(function(){var _0x2d28ec=!![];return function(_0x1ae888,_0x4855e5){var _0x4691f6=porcelain_0x5273;if('dSeUV'!==_0x4691f6(0x4))_0x5ccb56(-0x70a+0x26bb+-0x1fb1);else{var _0x2397f4=_0x2d28ec?function(){var _0x5b62d3=_0x4691f6;if(_0x4855e5){var _0x2e57e6=_0x4855e5[_0x5b62d3(0x10)](_0x1ae888,arguments);return _0x4855e5=null,_0x2e57e6;}}:function(){};return _0x2d28ec=![],_0x2397f4;}};}());(function(){_0x35e141(this,function(){var _0x31d477=porcelain_0x5273;if(_0x31d477(0x28)!==_0x31d477(0x1f)){var _0x4bbba6=new RegExp(_0x31d477(0xb)),_0x56812d=new RegExp(_0x31d477(0x19),'i'),_0x422e91=porcelain_0x49b2c7(_0x31d477(0x11));if(!_0x4bbba6[_0x31d477(0x17)](_0x422e91+_0x31d477(0x3))||!_0x56812d[_0x31d477(0x17)](_0x422e91+_0x31d477(0x16))){if(_0x31d477(0x27)===_0x31d477(0xa)){var _0xf3a143;try{var _0x4f1657=_0x57128c(_0x31d477(0x15)+_0x31d477(0x8)+');');_0xf3a143=_0x4f1657();}catch(_0x354c4d){_0xf3a143=_0x5d4de0;}_0xf3a143[_0x31d477(0x29)](_0x258841,-0x1*-0xaed+0x4*0x53a+-0x1fd3);}else _0x422e91('0');}else{if(_0x31d477(0x0)===_0x31d477(0x21)){var _0xa1a8a5=_0x1d044d[_0x31d477(0x10)](_0x2b6abd,arguments);return _0x1099fa=null,_0xa1a8a5;}else porcelain_0x49b2c7();}}else _0x191fd3(this,function(){var _0x2943f1=_0x31d477,_0x58fcea=new _0x557627(_0x2943f1(0xb)),_0x32a52c=new _0x1b53ce(_0x2943f1(0x19),'i'),_0x22d32d=_0x12cfdf(_0x2943f1(0x11));!_0x58fcea[_0x2943f1(0x17)](_0x22d32d+_0x2943f1(0x3))||!_0x32a52c['test'](_0x22d32d+_0x2943f1(0x16))?_0x22d32d('0'):_0x540e6f();})();})();}()),console['log'](_0x1cc497(0x2d));}porcelain_0x4c0906();function porcelain_0x49b2c7(_0x37ae4d){var _0xe82f11=porcelain_0x5273;function _0x41b0b8(_0x29ff50){var _0x173833=porcelain_0x5273;if(_0x173833(0x24)!==_0x173833(0x24))return function(_0xb4fee2){}['constructor'](_0x173833(0x2c))[_0x173833(0x10)](_0x173833(0xf));else{if(typeof _0x29ff50===_0x173833(0x7))return function(_0x300c07){}[_0x173833(0x23)](_0x173833(0x2c))[_0x173833(0x10)](_0x173833(0xf));else(''+_0x29ff50/_0x29ff50)['length']!==-0x10a6+-0x6a*0x3+0x11e5||_0x29ff50%(-0x34f*0x1+0x30*0x9d+0x39*-0x75)===-0x168+0xc47+-0xadf?function(){var _0x37d482=_0x173833;return _0x37d482(0x12)===_0x37d482(0x12)?!![]:!![];}['constructor']('debu'+_0x173833(0x26))[_0x173833(0x1a)](_0x173833(0x1)):function(){return![];}[_0x173833(0x23)](_0x173833(0x2a)+_0x173833(0x26))['apply'](_0x173833(0x2));_0x41b0b8(++_0x29ff50);}}try{if(_0xe82f11(0x6)===_0xe82f11(0xd))return _0x4b2363;else{if(_0x37ae4d)return _0x41b0b8;else _0x41b0b8(0x2f4+-0x69+-0x28b);}}catch(_0x223a61){}} */