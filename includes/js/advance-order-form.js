jQuery(document).ready(function($) {
	
	$('.customer-select').hide();
	
	 initailizeProductSelect2();
	 intailizeCustomerSelect2();

	 function intailizeCustomerSelect2(){
		$(".wc-customer-search").select2({
			minimumInputLength: 2,
			allowClear:  false,
			ajax: {
			  url: orderObj.ajaxurl,
			  dataType: 'json',
			  delay: 1000,
			  data: function (params) {
				return {
					term: params.term,
					action: 'get_custom_ajax_data',
				};
			  },
			  processResults: function( data ) {
					var terms = [];
					
					if ( data ) {
						var exist = 1;
						if(data.length == 0){
							exist = 0;
						}
						$('.exist_customer').val(exist);
						$.each( data, function( id, text ) {
							terms.push({
								id: id,
								text: text.username + ' (' + text.id + ' - '+ text.email+')',
								address1: text.address1,
								address2: text.address2,
								city: text.city,
								state: text.state,
								postcode: text.postcode,
								country: text.country,
								fname: text.fname,
								lname: text.lname,
								email: text.email,
								dob: text.dob,
								phone: text.phone,
								code: text.code,
							});
						});
					}
					return {
						results: terms
					};
				},
				// cache: true
			},
			templateSelection: formatCustomerSelection
		 });
	}
	function formatCustomerSelection(element){
		if(typeof element.address1 != 'undefined'){
			$('.billing_city').val(element.city);
			$('.billing_address_1').val(element.address1);
			$('.billing_address_2').val(element.address2);
			$('.billing_state').val(element.state);
			$('.billing_postcode').val(element.postcode);
		}
		if(typeof element.fname != 'undefined'){
			$('.fname').val(element.fname);
		}
		if(typeof element.lname != 'undefined'){
			$('.lname').val(element.lname);
		}
		if(typeof element.dob != 'undefined'){
			$('.dob').val(element.dob);
		}
		if(typeof element.phone != 'undefined'){
			$('.phone').val(element.phone);
		}
		if(typeof element.email != 'undefined'){
			$('.email_address').val(element.email);
		}
		if (typeof element.code !== 'undefined') {
			const selectElement = $('#code');  // Assuming the ID of your select element is "code"
			const optionValue = element.code;
			jQuery('#code').val(element.code).change();
		}
		return element.text;
	} 
	
	function add_more(text,id){
		var html = '<div class="clone_data productdata product_obj_'+id+' field__wrapper">'
					+ '<div class="chck_order_wrap">'
					+ '<div class="override_price_div">'
					+ '<label class="customer_order_lbl">Override pricing</label>'
					+ '<input type="checkbox" name="override_pricing[]" value="1" class="overide-price order-form-cls">'
					+ '</div>'
					+ '</div>'	
					+ '<div class="field-left">'
					+ '<label class="customer_order_lbl">Product name</label>'
					+ '<input type="text" name="product[]" value="'+text+'" class="product-field-name order-form-cls" readonly>'
					+ '<input type="hidden" name="productId[]" value="'+id+'" class="product-field-id"></input>'
					+ '</div>'
					+ '<div class="field-right field-qty-row">'
					+ '<div class="field-qty-left">'
					+ '<label class="customer_order_lbl">Qty</label>'
					+	'<div class="qty-wrapper">'
					+		'<input type="number" step="1" min="1" max="99" name="qty[]" id="qty" value="1"  class="product-field-qty qty-field">'
					+	'</div>'
					+ '</div>'
					+ '<div class="field-qty-right">'
					+ '<label class="customer_order_lbl">Price</label>'
					+		'<div class="qty-wrapper">'
					+ 			'<input type="number" name="original_price[]" id="price" value="0" readonly="readonly" class="origin-price price-field">'
					+		'</div>'
					+	'</div>'
					+ '<div class="field-qty-right">'
					+ '<label class="customer_order_lbl">Item Price</label>'
					+ '		<div class="qty-wrapper">'
					+ '			<input type="text" name="price[]" id="price" value="0" readonly class="product-field-price price-field">'
					+ '		</div>'
					+ '	</div>'
					+ '	<button name="add_more" type="button" class="removeProduct qty-btn">-</button>'
					+ ' </div>'
					+ '</div>';
		$(html).insertBefore($('.product-total'));
		
	}
	$(document).on("click",".removeProduct",function() {
		var parentObj = $(this).closest('.clone_data');
		parentObj.remove(); 
		var i = 0;
		$('.productdata').each(function(){
			if(i == 0){
				$(this).find('.customer_order_lbl').show();
			}
			if(i > 0){
				$(this).find('.customer_order_lbl').hide();
			}
			i++;
		});
		sync_price();
	});

	$(document).on("change",".customer_exist_check",function() {
		
		var cusExist = $(this).val();
		if(cusExist == '0'){
			$('.not_exist_customer').show();
			$('.customer-select').hide();
			intailizeCustomerSelect2();
			$(".wc-customer-search").empty();
			$(".wc-customer-search").append('<option>-- Select customer --</option>').trigger('change'); 
			$('.billing_city').val('');
			$('.billing_address_1').val('');
			$('.billing_address_2').val('');
			$('.billing_state').val('');
			$('.billing_postcode').val('');
			$('.email_address').prop('readonly', false);
			$('.email_address').addClass('email_add');
		}
		if(cusExist == '1'){
			$('.user_exist_error').html('');
			var emailAddress = $('.email_address').val();
			var user_id = $('.user_exist_id').val();
			if(emailAddress != ''){
				intailizeCustomerSelect2();
				var option = $('<option value='+user_id+' selected>#'+user_id+' - '+emailAddress+'</option>').val(user_id);
				$(".wc-customer-search").append(option).trigger('change'); 
			}
			$('.not_exist_customer').show();
			$('.email_address').prop('readonly', true);
			$('.email_address').removeClass('email_add');
			$('.customer-select').show();
		}
	});
	
	
	
	function initailizeProductSelect2(){
		$(".wc-product-search").select2({
			minimumInputLength: 3,
			minimumResultsForSearch: 7,
			allowClear:  false,
			ajax: {
			  url: orderObj.ajaxurl,
			  dataType: 'json',
			  delay: 250,
			  data: function (params) {
				 return {
					term: params.term,
					exclude_type:'variable',
					action: 'get_product_search_data',
				 };
			  },
			  processResults: function( data ) {
					var terms = [];
					
					if ( data ) {
						var textArr;
						$.each( data, function( id, text ) {
							textArr = text.split('||');
							terms.push({
								id: id,
								text: textArr[0],
								price: textArr[1],
								cat: textArr[2]
							});
						});
					}
					return {
						results: terms
					};
				},
				cache: true
			},
		 });
	}
	$('.wc-product-search').on('select2:select', function (e) {
		var data = e.params.data;
		if(typeof data.text != 'undefined'){
			
			var productExist = false;
			var i = 0;
			$('.productdata').each(function(){
				var productId = $(this).find('.product-field-id').val();
				
				if(productId == data.id){
					productExist = true;
				}
				
			});
			if(!productExist){
				
				add_more(data.text,data.id,data.price);
			
				var mainObj = $('.product_obj_'+data.id);
				mainObj.find('.product-field-price').val(data.price);
				mainObj.find('.origin-price').val(data.price);
				if(mainObj.cat != ''){
					mainObj.find('.origin-price').attr('data-cat',data.cat);
				} else {
					mainObj.find('.origin-price').removeAttr('data-cat');
				}
				$('.productdata').each(function(){
					if(i > 0){
						$(this).find('.customer_order_lbl').hide();
					}
					i++;
				});
			}
			$(this).empty();
			$(this).append('<option>-- Select Product --</option>');
			calculate_total();
		}
	});
	
	$(document).on("click",".order-form-btn",function() {
		var formdata = $( "#custom_order_form" ).serialize();
		$('.success_msg').hide();
		sync_price();
		
	});
	function submit_order_form(formdata){
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: orderObj.ajaxurl,
			data: {
				formdata: formdata,
				action: 'add_custom_order_data',
				nonce: orderObj.nonce,
			},
			beforeSend: function(){
				$('.order-form-btn').attr('disabled', 'disabled');
				$('.loader_main_wrap').show();
			},
			success: function(data) {
				$('.loader_main_wrap').hide();
				$('.order-form-btn').removeAttr('disabled');
				if(data.error.length > 0){
					
					$("html, body").animate({ scrollTop: 100 }, "slow");
					var errorStr = data.error.toString();
					errorArr = errorStr.split(",");
					var errorData = "<ul>";
					for (var i = 0; i < errorArr.length; i++) {
						errorData += '<li>'+errorArr[i]+'</li>';
					}
					errorData += "</ul>";
					$('.error').append(errorData);
				} else {
					location.reload(true);
				}
			}
		});
	}
	$(document).on("keyup",".email_add",function() {
		var email = $( this ).val();
		$('.user_exist_error').html('');
		if(email.length <= 6){
			return false;
		}
		
		$('.success_msg').hide();
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: orderObj.ajaxurl,
			data: {
				email: email,
				action: 'check_email_exist',
				nonce: orderObj.nonce,
			},
			beforeSend: function(){
				$('.loader_main_wrap').show();
			},
			success: function(data) {
				$('.loader_main_wrap').hide();
				if(data.success){
					$('.user_exist_id').val(data.user_id);
					$('.user_exist_error').html(data.message);
				}
			}
		});
	});
	
	$(document).on("click",".sync_data",function() {
		sync_price();
	});
	function sync_price(){
		$('.error').html('');
		var productArr = [];
		
		$('.productdata').each(function(){
			var productId = $(this).find('.product-field-id').val();
			var qty = $(this).find('.product-field-qty').val();
			var override_price = $(this).find('.overide-price:checked').val();
			var origin_price = $(this).find('.origin-price').val();
			
			productArr.push( {
				productId: productId,
				qty: qty,
				overridePrice:override_price,
				originPrice:origin_price,
			  });
		});
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: orderObj.ajaxurl,
			data: {
				productArr: productArr,
				nonce: orderObj.nonce,
				action: 'get_product_cart_data',
			},
			beforeSend: function(){
				$('.loader_main_wrap').show();
			},
			success: function(data) {
				$('.loader_main_wrap').hide();
				if(data.success){
					if(data.error.length > 0){
					
						$("html, body").animate({ scrollTop: 100 }, "slow");
						var errorStr = data.error.toString();
						errorArr = errorStr.split(",");
						var errorData = "<ul>";
						for (var i = 0; i < errorArr.length; i++) {
							errorData += '<li>'+errorArr[i]+'</li>';
						}
						errorData += "</ul>";
						$('.error').append(errorData);
					}
					$('.qty-subtotal').val(data.sub_total);
					$('.qty-total').val(data.total);
					if(data.items != ''){
						let items = data.items;
						
						$.each(items, function (i, item) {
							$('.productdata').each(function(){
								let obj = $(this);
								let productId = $(this).find('.product-field-id').val();
								if(item.id == productId){
									
									$(this).find('.price-field').val(Math.round(item.line_total));
									let single= (item.line_total / item.qty).toFixed(2);
									if(obj.find('.cs-replace-price').is(":checked")) {
										$(this).find('.origin-price').val(0);
										$(this).find('.product-field-price').val(0);
									} else {
										$(this).find('.origin-price').val(single);
									}
								}
							});
						});
						
					}
					if(data.fees != ''){
						let fees = data.feesData;
						let feehtml = '';
						$.each(fees, function (i, fee) {
							let singleFee = (fee.totals.total / 100);
							feehtml += '<div class="qty-fees-wrap"><label class="fee-label field-label" for="qty-fees">'+fee.name+' :</label>'
									+ '<div class="qty-wrapper">'
									+ '<input type="text" name="qty-fees[]" id="qty-fees" class="qty-fees" value="'+singleFee+'" readonly>'
									+ '<input type="hidden" name="qty-fees-label[]" class="qty-fees-label" value="'+fee.name+'">'
									+ '</div></div>';
						});
						
						$('.fees_main_wrap').show();
						$('.fees_main_wrap').html(feehtml);
					} else {
						$('.fees_main_wrap').hide();
						$('.qty-fees').val(0);
						$('.fees_main_wrap').html('');
					}
					calculate_total();
					if(data.error.length > 0){
					} else {
						var formdata = $( "#custom_order_form" ).serialize();
						submit_order_form(formdata);
					}
				}
			}
		});
	}
	$(document).on("change",".overide-price",function() {
		var obj = $(this).closest('.productdata');
	    if($(this).is(":checked")) {
            obj.find('.origin-price').attr("readonly", false); 
        } else {
			obj.find('.origin-price').attr("readonly", true); 
		}     
    });
	$(document).on("change",".cs-replace-price",function() {
		var obj = $(this).closest('.productdata');
		
	    if($(this).is(":checked")) {
			var price = obj.find('.origin-price').val();
			obj.find('.origin-price').attr("data-price", price);
			obj.find('.origin-price').val(0); 
			obj.find('.product-field-price').val(0); 
        } else {
			var price = obj.find('.origin-price').attr("data-price");
			obj.find('.origin-price').val(price);
			let qty = obj.find('.product-field-qty').val();
			var item_price = price * qty;
			obj.find('.product-field-price').val(item_price);
		}     
		calculate_total();
    });
	
	
	function calculate_total(){
		var totalPrice = 0.00;
		var csProductExist = false;
		
		$('.productdata').each(function(){
			if($(this).find('.cs-replace-price').is(":checked")) {
				csProductExist = true;
			}
			totalPrice += parseFloat($(this).find('.product-field-price').val());
		});
		
		$('.qty-subtotal').val(totalPrice);

		if($('.qty-fees').length > 0){
			var fees = 0;
			$('.qty-fees').each(function(){
				fees += Math.abs($(this).val());
			});
			totalPrice = totalPrice - Math.abs(fees);
		}
		
		$('.qty-total').val(totalPrice);
	}
	
});