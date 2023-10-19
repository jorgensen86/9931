$(document).on('change', '#maintenance', function(){
	var self = $(this);
	$.ajax({
		url: 'index.php?route=event/icop/maintenance&user_token=' + getURLVar('user_token').split('#')[0] + '&maintenance=' + (self.is(':checked') ? 0 : 1),
		beforeSend: function() {
			self.attr('disabled', true);
		},
		complete: function() {
			self.attr('disabled', false);
		},
		error: function (xhr, ajaxOptions, thrownError) {
     		alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
    	}
	})
});

$(function(){
	$('.caches a').on('click', function(e) {
		e.preventDefault();
		var self = $(this);
		var url = '';
		
		if(self.attr('data-target') == 'ocmod') {
			url = 'index.php?route=marketplace/modification/refresh&user_token=' + getURLVar('user_token') + '&is_ajax=true';
		} else if (self.attr('data-target') == 'journal') {
			url = 'index.php?jf=1&route=journal3/journal3/clear_cache&user_token=' + getURLVar('user_token');
		} else {
			url = 'index.php?route=event/icop/clearCache&user_token=' + getURLVar('user_token') + '&cache=' + self.attr('data-target')
		}


		$.ajax({
			url: url,
			beforeSend: function() {
				$('.caches a').attr('disabled', true);
			},
			complete: function() {
				$('.caches a').attr('disabled', false);
			},
			success: function() {
				$('#content').prepend('<div class="alert alert-success alert-cache text-center">' + self.text() + ' cleared succesfully!</div>');
				setTimeout(function () {
					$('.alert-cache').fadeOut(1000, function() { 
						$(this).remove(); 
					});
				}, 1000);
			},
			error: function (xhr, ajaxOptions, thrownError) {
     		 	alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
    		}
    	});

	});
	$('#journal3-theme li a').on('click', function(e) {
		if ($(this).attr('href') === '#') {
			$('#journal3ModulePermissions').remove();
			e.preventDefault();
			$.ajax({
				url: 'index.php?route=event/icop/journal3ModulePermissions&user_token=' + getURLVar('user_token'),
				success: function(html) {
					$('body').append(html);
					$('#journal3ModulePermissions').modal('show');
				},
				error: function (xhr, ajaxOptions, thrownError) {
		     		alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
		    	}
		    });
		}
	});
});
