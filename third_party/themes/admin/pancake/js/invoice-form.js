function getRemainingPercentage() {
    
    var amount = 0;
    var percentage_left = 100;
    
    if ($('input[name=type]:checked').val() == 'DETAILED') {
        $('input.item_cost').each(function() {
            var val = $(this).val();
            val = val == '' ? 0 : parseFloat(val);
            amount = amount + val;
        });
    } else {
        var val = parseInt($('input[name=amount]').val());
        amount = val;
    }
    
    $('.partial-inputs').each(function() {
       var val = $(this).find('input.partial-amount').val();
       var is_percentage = $(this).find('.partial-percentage select').val();
       
       if (is_percentage == '1') {
               percentage_left = percentage_left - val;
       } else {
           if (amount == 0) {
               percentage_left = 0;
               return;
           } else {
               val = ((val / amount) * 100).toFixed(2);
               percentage_left = percentage_left - val;
           }
       }
    });
    
    return percentage_left < 0 ? 0 : percentage_left;
            
}
function fix_item_cost_width() {
    $('span.item_cost').css('width', 'auto');
    var width = get_widest_width('span.item_cost');
    $('span.item_cost').css({display: 'block'}).width(width);
    $('#invoice-items th:nth-child(5)').width(width + 30);
}

paymentDetailsSaved = false;

function savePaymentDetails() {
    if (!paymentDetailsSaved) {
        paymentDetailsSaved = true;
	
	// Change to Payment Details if is_paid, otherwise change to Mark As Paid
	if ($('[name=payment-status]').val() != '' || $('[name=payment-gateway]').val() != '') {
	    $('.partial-payment-details.key_'+ppm_key+' span').html($('.partial-input-container').data('paymentdetails'));
	} else {
	    $('.partial-payment-details.key_'+ppm_key+' span').html($('.partial-input-container').data('markaspaid'));
	}
	
        $.post(baseURL+'ajax/set_payment_details/'+invoice_unique_id+'/'+ppm_key, {
            status: $('[name=payment-status]').val(),
            gateway: $('[name=payment-gateway]').val(),
            date: ($('[name=payment-date]').val()/1000),
            tid: $('[name=payment-tid]').val(),
	    fee: $('[name=transaction-fee]').val()
        } , function(data) {
            
        });
    }
}

function hideMultiparts() {
    $('.partial-addmore span').html($('.partial-addmore a').data('disabled'));
    $('.partial-addmore a').addClass('disabled');
    
    if ($('.partial-inputs').length > 1) {
        $('.partial-inputs:not(:first-child)').slideUp();
        $('.partial-inputs:first-child .partial-amount').data('old-value', $('.partial-inputs:first-child .partial-amount').val()).val(100);
        $('.partial-inputs:first-child .partial-percentage select').data('old-value', $('.partial-inputs:first-child .partial-percentage select').val()).val(1);
        $('.partial-inputs:first-child .partial-percentage .selector span').html($('.partial-inputs:first-child .partial-percentage select option:selected').html());
    }
}

function showMultiparts() {
    $('.partial-addmore span').html($('.partial-addmore a').data('enabled'));
    $('.partial-addmore a').removeClass('disabled');
    
    if ($('.partial-inputs').length > 1 && $('.partial-inputs:first-child .partial-amount').data('old-value') != undefined) {
        $('.partial-inputs:not(:first-child)').slideDown();
        $('.partial-inputs:first-child .partial-amount').val($('.partial-inputs:first-child .partial-amount').data('old-value'));
        $('.partial-inputs:first-child .partial-percentage select').val($('.partial-inputs:first-child .partial-percentage select').data('old-value'));
        $('.partial-inputs:first-child .partial-percentage .selector span').html($('.partial-inputs:first-child .partial-percentage select option:selected').html());
    }
}

$('.partial-payment-delete').live('click', function() {
    $(this).parents('.partial-inputs').slideUp(function() {$(this).remove()});
    return false;
});

$('#invoice-items .delete').live('click', function() {
    $(this).parents('table:first').parents('tr:first').fadeOut(function() {$(this).remove();});
    return false;
});


$(function(){
	fix_item_cost_width();
        if ($('.partial-payment-details').length == 0) {
            $('div.partial-inputs .partial-notes').width(386);
        }
	
	$('.partial-payment-delete:first').hide();
    
        $('a.partial-payment-details').live('click', function() {
            ppm_key = $(this).data('details');
            $(document).bind('reveal.facebox', function() { 
                $('#facebox .not-uniform:not(.uniformized)').addClass('uniformized').uniform();
                
                $('#partial-payment-details form').submit(function() {
                    $(document).trigger('close.facebox');return false;
                });
                
                $('.savepaymentdetails').click(function() {$(document).trigger('close.facebox');return false;});
            });
            $(document).bind('close.facebox', function() {
                savePaymentDetails();
            });
            paymentDetailsSaved = false;
            jQuery.facebox({ajax: baseURL+'ajax/get_payment_details/'+invoice_unique_id+'/'+ppm_key});
            return false;
        });
    
        currentSymbol = '';
    
        $('#currency').change(function() {
            currentSymbol = $(this).find(':selected').data('symbol');
            $('.partial-percentage option[value=0]').html(currentSymbol);
            $('.partial-percentage .selector').each(function() {
                if ($(this).find('select').val() == 0) {
                    $(this).find('span').html(currentSymbol);
                }
            });
        });
        
        $('input.partial-amount').livequery(function() {
            $(this).forceNumeric();
        });
    
        $('#is_recurring').change(function() {
            if ($(this).val() == '1') {
                // This invoice is recurring, partial payments are disabled.
                hideMultiparts();
            } else {
                // This invoice is NOT recurring, partial payments are enabled.
                showMultiparts();
            }
        });
        
        $('.partial-addmore a').click(function() {
            if (!$(this).is(':disabled')) {
                // Button is not disabled, let's create another row for partial payments.
                
                newLength = ($('.partial-inputs').length + 1);
		// Destroy the first date picker, then rebuild it after cloning.
                $('.partial-inputs:first-child .datePicker').datepicker('destroy');
                newPartial = $('.partial-inputs:first-child').clone();
		newPartial.find('.datePicker').attr('name', 'partial-due_date'+'['+newLength+']').datepicker('destroy');
		// Set the new name, then call datepicker again.
		$('.partial-inputs:first-child .datePicker').each(function() {
		    $(this).datepicker({dateFormat: datePickerFormat, altFormat: '@',altField: $('[name='+$(this).data('old-name').replace('[', '\\[').replace(']', '\\]')+']')});
		});
		
		newPartial.find('a').data('details', newLength).removeClass('key_1').addClass('key_'+newLength);
		newPartial.find('.partial-payment-details span').html($('.partial-input-container').data('markaspaid'));
                newPartial.find('input:not([type=checkbox])').val('');
                newPartial.find('input.partial-amount').val(getRemainingPercentage());
                newPartial.find('input[type=checkbox]:checked').click();
                newPartial.find('input:not(.datePicker), select').each(function() {
                    $(this).attr('name', $(this).attr('name').replace('[1]', '['+ newLength +']'))
                });
                select = newPartial.find('select');
                check = newPartial.find('input[type=checkbox]');
                
                check.attr('id', check.attr('id') + newLength);
                select.attr('id', select.attr('id') + newLength);
		
		newPartial.find('input[type=text]').each(function() {
		    $(this).attr('id', $(this).attr('id') + newLength);
		});
                
                $(newPartial).find('.partial-percentage > .selector').replaceWith(select);
                
                $(newPartial).find('.checker').replaceWith(check);
                $(newPartial).find('.partial-payment-delete').show();
                newPartial.hide().appendTo('.partial-input-container');
                $('.partial-input-container *:hidden').slideDown('normal');
		$('.partial-payment-delete:first').hide();
                return false;
                
            }
        });
        
    
	// Select wether its an invoice or payment request
	$('input[name=type]').live('change', function(){
		type = this.value;

		$('.type-wrapper').hide();
		if (type == 'ESTIMATE')
		{
			$('.hide-estimate').hide();
			type = 'DETAILED';
		}
		else
		{
			$('.hide-estimate').show();
		}

		$('#' + type + '-wrapper').show();
	});

	if( $('input[name=type]:checked').length == 0 )
	{
		$('input[name=type][value=REQ]').attr('checked', true);
	}

	$('input[name=type]:checked').change();

	$( "input.item_name" ).livequery(function(){
		$(this).autocomplete({
			source: baseURL + 'admin/items/ajax_auto_complete',
			minLength: 2,
			select: function( event, ui ) {
				
				details = $(event.target).closest('tr.details');
				description = details.next('tr.description');
				
				cost = ui.item.qty * ui.item.rate;
				
				$('input.item_name', details).val(ui.item.name);
				$('input.item_quantity', details).val(ui.item.qty);
				$('input.item_rate', details).val(ui.item.rate);
				$('input.tax_id', details).val(ui.item.tax_id);
				$('input.item_cost', details).val( cost );
				$('span.item_cost', details).text( cost );
				fix_item_cost_width();
				
				$('textarea.item_description', description).val(ui.item.description);
				
				$.uniform.update('.tax_id');
			}
		});
	});

	// Count up a row total
	$('input.item_quantity, input.item_rate').forceNumeric().live('keyup', function(){
		row = $(this).closest('tr');
		qty = $('input.item_quantity', row).val();
		rate = $('input.item_rate', row).val();

		
		cost = (Math.round( (qty * rate ) *100)/100).toFixed(2);

		$('input.item_cost', row).val( cost );
		$('span.item_cost', row).text( cost );
		fix_item_cost_width();
	});

	// Add a new row
	$('a#add-row').click(function(){

		// Remove if there are others to clone
		details = $('#invoice-items tbody tr.details:first');
		description = $('#invoice-items tbody tr.description:first');
		
		if ($('#invoice-items tbody').children('tr.details:visible').length == 0) {
			details = details.show();
			description = description.show();
		}
		else {
			details = details.clone();
			description = description.clone();
		}

		$('input.item_name', details).val('');
		$('input.item_quantity', details).val(1);
		$('input.item_rate', details).val('1.00');
		$('input.tax_id', details).val('0');
		$('input.item_cost', details).val( '1.00' );
		$('span.item_cost', details).text( '1.00' );
		fix_item_cost_width();
		
		$('textarea.item_description', description).val('');
		
		$('#invoice-items > tbody').append('<tr><td colspan="6"><table></table></td></tr>');
		
		$('#invoice-items > tbody > tr:last table').append(details);
		$('#invoice-items > tbody > tr:last table').append(description);
		$.uniform.update('.tax_id');
		return false;
	});
	
	$('.tax_id').live('change', function() {
		$.uniform.update(this);
	})
	// Remove (or at least hide) a row
	$('a.remove.icon').live('click', function(){

		// Last one, keep it!
		if( $('#invoice-items tbody').children('tr.details').length == 1)
		{
			row = $(this).closest('tr.details').hide();
			row.next('tr.description').hide();
			row.find('input').val('');
		}

		// Remove if there are others to clone
		else
		{
			row = $(this).closest('tr.details');
			row.next('tr.description').andSelf().remove();
		}
		return false;
	});

	$('#add-file-input').click(function (e) {
		e.preventDefault();
		$('#file-inputs').append('<li><input name="invoice_files[]" type="file" /></li>');
		$.uniform && $.uniform.update();
	});

	$('.remove_file').click(function () {
		$(this).parent().parent().toggleClass('file_remove');
	});

	$('#add_files_edit').click(function () {
		$('#files tbody').append('<tr><td colspan="4"><input name="invoice_files[]" type="file" /></td></tr>');
		return false;
	});

/*	$('#description, #notes').htmlarea({
		toolbar: Pancake.toolbars.basic,
		css: Pancake.site_url+"third_party/themes/admin/pancake/css/jHtmlArea.Editor.css",
	});
*/

	$('select[name=is_recurring]').change(function() {

		this.value == 1
			? $('div#recurring-options').slideDown('slow')
			: $('div#recurring-options').slideUp('slow')
			
		return false;
	}).change();
	
	$('table#invoice-items tbody').sortable({
		handle: 'a.sort	',
		items: '> tr'
	});
	
});