// make it safe to use console.log always
(function(b){function c(){}for(var d="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,timeStamp,profile,profileEnd,time,timeEnd,trace,warn".split(","),a;a=d.pop();){b[a]=b[a]||c}})((function(){try
{console.log();return window.console;}catch(err){return window.console={};}})());

Pancake = {};
Pancake.toolbars = {};
Pancake.base_url = baseURL;
Pancake.site_url = siteURL;
Pancake.toolbars.basic = ["bold", "italic", "underline", "|", "h2", "h3", "h4", "|", "orderedlist", "unorderedlist"];

$('form').live('submit', function() {
    $('.hasDatepicker').each(function() {
        $(this).datepicker('getDate') !== null && $(this).val($(this).datepicker('getDate').getTime());
    });
});

function get_widest_width(elements) {
    var widest = null;
    $(elements).each(function() {
      if (widest == null)
	widest = $(this);
      else
      if ($(this).width() > widest.width())
	widest = $(this);
    });
    
    return widest.width();
}

function hide_notification(notification_id) {
    $.get(baseURL+'ajax/hide_notification/'+notification_id);
}

$(function(){
	
    $.fn.forceNumeric = function () {
        return this.each(function () {

            $(this).keydown(function() {$(this).data('old-val', $(this).val())}).keyup(function() {
                if ($(this).data('old-val') != $(this).val() && $(this).val().replace(/[^0-9\-\.]/g, '') != $(this).val()) {
                    $(this).val($(this).val().replace(/[^0-9\-\.]/g, ''));
                }
            });
        });
    };

	if ($.livequery != undefined) {
		
	    $("select:not(.not-uniform), textarea, input:not(.hidden-submit), button").livequery(function () {
	        // Update uniform if enabled
	        if ($(this).attr('class') != 'tax_id')
	        {
	        	$.uniform && $(this).uniform();
	        }
	    });
	    
	    $('label.use-label').livequery(function() {
		var placeholder = $(this).hide().html();
		var input = $('#'+$(this).attr('for')).addClass('placeholded-input');
		if (input.length != 0) {
		    var div = $('<div style="position:relative;float:left;" class="placeholded-input-container"></div>');
		    input.before(div);
		    div.append(input);
		    var placeholderel = $('<div class="placeholder">'+placeholder+'</div>');
		    input.before(placeholderel);
		    placeholderel.click(function() {$(this).siblings('.placeholded-input').focus();return false;})
		    input.css('padding-left', placeholderel.width() + 10);
		}
	    });
	    
	    $('.numeric').livequery(function() {
			$(this).forceNumeric();
		});
    
	    $('.datePicker').livequery(function () {

			// Old name is put in data() for use by the partial payments.
			// The reason to do this is for partial payments to keep working.
			var name = $(this).data('old-name', $(this).attr('name')).attr('name');

			if ($('[name='+name.replace('[', '\\[').replace(']', '\\]')+'][type=hidden]').length == 0) {
				// If there's no hidden input for this datepicker yet, make one, and remove the name of the datepicker.
				var newField = $('<input type="hidden" name="'+name+'" />');
				$(this).parents('form').append(newField);
				$(this).attr('name', '');
			}
	
			$(this).datepicker({
				dateFormat: datePickerFormat,
				altFormat: '@',
				altField: newField
			});

			$(this).datepicker('getDate') !== null && newField.val($(this).datepicker('getDate').getTime());
		});
    
	} else {
	    $("select:not(.not-uniform), textarea, input:not(.hidden-submit), button").each(function () {
	        // Update uniform if enabled
	        if ($(this).attr('class') != 'tax_id')
	        {
	                $.uniform && $(this).uniform();
	        }
	    });
	}

	$.uniform && $("select.tax_id").uniform();

	setTimeout(function() {$('.fadeable').css('overflow', 'hidden').slideUp(1000);}, 5000);

	if ($.facebox != undefined) {
		$('a[rel=facebox], a.modal').facebox();
	}
        
});

Tasks = {
	
	timer_intervals: [],

    toggleStatus: function(id)
    {
        $('#task-row-'+id).load(Pancake.site_url+'admin/projects/tasks/toggle_status/' + id + ' #task-row-'+id+' > *');
    },

    startTimer: function(button)
    {
		var timer = button.parents('.timer').addClass('running');
	    button.addClass('running').html(button.data('stop'));
	
		var date = new Date();
		var hour_difference = date.getHours() - 12;
		date.setHours(12);
		timer.data('time-start', date.getTime());
		timer.data('hour-difference', hour_difference);
		
        $.post(Pancake.site_url+'admin/projects/times/ajax_start_timer', { 
			project_id : timer.data('project-id'),
			task_id : timer.data('task-id')
		});
		
		timer.data('interval', setInterval(function() {Tasks.updateTimer(timer)}, 1000));
    },

    continueTimer: function(button)
    {
		timer = button.parents('.timer');
		timer.data('interval', setInterval(function() {Tasks.updateTimer(timer)}, 1000));
    },

    stopTimer: function(button)
    {
		button.removeClass('running').html(button.data('start'));
	    timer = button.parents('.timer');

		$.post(Pancake.site_url+'admin/projects/times/ajax_stop_timer', {
			project_id : timer.data('project-id'),
			task_id : timer.data('task-id')
		}, function(data) {
		    timer.find('.timer-time').text('00:00:00');

			timer.closest('tr').find('.tracked-hours').text(data.new_total_time);
		}, 'json');
	
		clearInterval(timer.data('interval'));
	},
	
	updateTimer: function (timer) {
		
	    date = new Date();
	    if (timer.data('hour-difference') > 0) {
		date.setHours(date.getHours() - timer.data('hour-difference'));
	    } else if (timer.data('hour-difference') < 0) {
		date.setHours(date.getHours() + Math.abs(timer.data('hour-difference')));
	    }
	    date.setTime(date.getTime() - timer.data('time-start'));
		
	    hours = date.getHours();
	    if (hours < 10) {hours = '0'+hours;}

	    minutes = date.getMinutes();
	    if (minutes < 10) {minutes = '0'+minutes;}
	
	    seconds = date.getSeconds();
	    if (seconds < 10) {seconds = '0'+seconds;}

	    timer.find('.timer-time').text(hours+':'+minutes+':'+seconds);
	}

}

jQuery(function($) {
	
	$('.timer-button.running').each(function() {
		Tasks.continueTimer($(this));
	});

	$('.timer .timer-button').live('click', function() {		
		$(this).hasClass('running')
			? Tasks.stopTimer($(this))
			: Tasks.startTimer($(this));
	});
});


function refreshTrackedHours(element) {
    var task_id = element.data('task-id');
    $.get(refreshTrackedHoursUrl+'/'+task_id, function(data) {
        element.html(data);
    });
}