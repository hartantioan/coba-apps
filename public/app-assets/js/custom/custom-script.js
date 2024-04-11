$(function() {
	$('#loader').fadeOut();
	initializeMasking();
	$('.select2-hidden-accessible').on('select2:unselect',function(){
		$(this).html(null);
	});
});

function emptyThis(element){
	if($(element).val()){
		if(parseFloat($(element).val().replaceAll(".", "").replaceAll(",",".")) == 0){
			$(element).val('');
		}
	}
}

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
	let val = angka.value ? angka.value : '';
	var number_string = val.replace(/[^,\d]/g, '').toString()
	sign = val.charAt(0),
	split   		= number_string.toString().split(','),
	sisa     		= parseFloat(split[0]).toString().length % 3,
	rupiah     		= parseFloat(split[0]).toString().substr(0, sisa),
	ribuan     		= parseFloat(split[0]).toString().substr(sisa).match(/\d{3}/gi);
 
	if(ribuan){
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}
 
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	angka.value = sign == '-' ? sign + rupiah : rupiah;
}

function formatRupiahNoMinus(angka){
	let val = angka.value ? angka.value : '';
	var number_string = val.replace(/[^,\d]/g, '').toString()
	split   		= number_string.split(','),
	sisa     		= parseFloat(split[0]).toString().length % 3,
	rupiah     		= parseFloat(split[0]).toString().substr(0, sisa),
	ribuan     		= parseFloat(split[0]).toString().substr(sisa).match(/\d{3}/gi);
 
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
				$('#version-app').text(response.version);
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

$(document).keydown(function(e) {
	if (e.ctrlKey && e.keyCode == 13) {
		if($('#modal1').length > 0){
			$('#modal1').modal('open');
		}
	}
});

$(document).on('focus', '.select2.select2-container', function (e) {
	if (e.originalEvent && $(this).find(".select2-selection--single").length > 0) {
		$(this).siblings('select').select2('open');
	}
});

Date.prototype.yyyymmdd = function(showtime) {
	var dateString = this.getFullYear() +"-"+
		("0" + (this.getMonth()+1)).slice(-2) +"-"+
		("0" + this.getDate()).slice(-2) + " ";
	if (showtime || false) {
		dateString += ("0" + this.getHours()).slice(-2) + ":" +
		("0" + this.getMinutes()).slice(-2) + ":" +
		("0" + this.getSeconds()).slice(-2) + "." +
		("00" + this.getMilliseconds()).slice(-3)
	}
	return dateString;
}

function loadCurrency(){
	let code = $('#currency_id').find(':selected').data('code'), date = $('#post_date').val();
	var yesterday = new Date(date);
	yesterday.setDate(yesterday.getDate() -2);
	let dateString = yesterday.yyyymmdd();
	$.ajax({
		url: 'https://api.vatcomply.com/rates?base=' + code +'&date='+ dateString,
		type: 'GET',
		beforeSend: function() {
			loadingOpen('#currency_rate');
		},
		data: {

		},
		success: function(response) {
			loadingClose('#currency_rate');
			
			$('#currency_rate').val(formatRupiahIni(parseFloat(response['rates']['IDR']).toFixed(2).toString().replace('.',','))).trigger('keyup');
		},
		error: function() {
			swal({
				title: 'Ups!',
				text: 'Check your internet connection.',
				icon: 'error'
			});
		}
	});
}

function getRandomColor() {
	color = "hsl(" + Math.random() * 360 + ", 100%, 75%)";
	return color;
}