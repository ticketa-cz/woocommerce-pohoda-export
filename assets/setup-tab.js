jQuery(document).ready( function ($) {
	
	//// BASIC SETTING ////
 
	var section_mserver = $( "#wc_settings_pohoda_export_mserver_address" ).parents( "table" );
	var section_numbering = $( "#wc_settings_pohoda_export_invoice_prefix" ).parents( "table" );
	var section_invoice = $( "#wc_settings_pohoda_export_invoice_data_payment_due" ).parents( "table" );
	var section_billing = $( "#wc_settings_pohoda_export_billing_company" ).parents( "table" );
	var section_export = $( "#wc_settings_pohoda_export_invoice_export_type" ).parents( "table" );
	var section_payments = $( "#wc_settings_pohoda_export_payment_methods_card" ).parents( "table" );
	var section_actions = $( "#wc_settings_pohoda_export_actions_buttons" ).parents( "table" );
	var section_pdf = $( "#wc_settings_pohoda_export_pdf_notice" ).parents( "table" );
	var section_log = $( "#wc_settings_pohoda_export_action_log_window" ).parents( "table" );
	var section_billingusetype = $( ".billingusetype" ).parents( "tr" );
	
	var heading_mserver = section_mserver.prev( "h2" );
	var heading_numbering = section_numbering.prev( "h2" );
	var heading_invoice = section_invoice.prev( "h2" );
	var heading_billing = section_billing.prev( "h2" );
	var heading_export = section_export.prev( "h2" );
	var heading_payments = section_payments.prev( "h2" );
	var heading_pdf = section_pdf.prev( "h2" );
	var heading_log = section_log.prev( "h2" );
	var heading_actions = section_actions.prev( "h2" );
	
	heading_mserver.addClass("section_heading heading_mserver").wrap( "<div id='section_mserver' class='setting_section'></div>" );
	heading_numbering.addClass("section_heading heading_numbering").wrap( "<div id='section_numbering' class='setting_section'></div>" );
	heading_invoice.addClass("section_heading heading_invoice").wrap( "<div id='section_invoice' class='setting_section'></div>" );
	heading_billing.addClass("section_heading heading_billing").wrap( "<div id='section_billing' class='setting_section'></div>" );
	heading_export.addClass("section_heading heading_export").wrap( "<div id='section_export' class='setting_section'></div>" );
	heading_payments.addClass("section_heading heading_payments").wrap( "<div id='section_payments' class='setting_section'></div>" );
	heading_pdf.addClass("section_heading heading_pdf").wrap( "<div id='section_pdf' class='setting_section'></div>" );
	heading_log.addClass("section_heading heading_log").wrap( "<div id='section_log' class='setting_section'></div>" );
	heading_actions.addClass("section_heading heading_actions").wrap( "<div id='section_actions' class='setting_section'></div>" );

	var mserver_connect = "<div id='mserver_connect' class='button-primary woocommerce-save-button'>" + tckpoh_lang.mserver_connect + "</div>";
	var save_options = "<div id='save_options' class='button-primary woocommerce-save-button'>" + tckpoh_lang.save_options + "</div>";
	var check_this_year = "<div id='check_this_year' class='button-primary woocommerce-save-button'>" + tckpoh_lang.check_this_year + "</div>";
	var export_xml = "<div id='export_xml' class='button-primary woocommerce-save-button'>" + tckpoh_lang.export_xml + "</div>";
	var action_log_content = "<div id='action_log_content'></div>";
	var send_log_to_support = "<div id='send_log_to_support' class='button-primary woocommerce-save-button'>" + tckpoh_lang.send_log_to_support + "</div>";
	var erase_action_log = "<div id='erase_action_log' class='button-primary woocommerce-save-button'>" + tckpoh_lang.erase_action_log + "</div>";
	var reload_action_log = "<div id='reload_action_log' class='button-primary woocommerce-save-button'>" + tckpoh_lang.reload_action_log + "</div>";
	var last_invoice_number = "<div class='navod'><span id='last_invoice_number'>" + tckpoh_lang.last_invoice_number + "</span><span id='reset_core_number'>" + tckpoh_lang.reset_core_number + "</span></div>";
	var erase_invoice_numbers = "<div id='reset_core_number_erase' class='button-primary woocommerce-save-button'>" + tckpoh_lang.reset_core_number_erase + "</div>";
	var reset_queue = "<div id='reset_queue' class='button-primary woocommerce-save-button'>" + tckpoh_lang.reset_queue + "</div>";
	var billing_info_note = "<div id='reset_numbering' class='navod'>" + tckpoh_lang.billing_info_note + "</div>";
	var reset_numbering = "<div id='reset_numbering' class='navod'>" + tckpoh_lang.reset_numbering + "</div>";
	var example_numbering = "<div id='example_numbering' class='navod'></div>";
	var info_payments = "<div id='info_payments' class='navod'>" + tckpoh_lang.info_payments + "</div>";
	var plugin_switch = "<div id='plugin_switch_div' class='setting_section'><input id='plugin_switch' type='checkbox' value='" + tckpoh_lang.plugin_switch + "' /><p>" + tckpoh_lang.plugin_switch_note + "</p></div>";
	
	section_mserver.append( mserver_connect );
	section_mserver.appendTo( "#section_mserver" );
	heading_billing.after( billing_info_note );
	heading_numbering.after( reset_numbering );
	section_numbering.append( example_numbering );
	section_numbering.appendTo( "#section_numbering" );
	section_invoice.appendTo( "#section_invoice" );
	section_billing.appendTo( "#section_billing" );
	section_export.appendTo( "#section_export" );
	section_pdf.appendTo( "#section_pdf" );
	section_log.appendTo( "#section_log" );
	heading_payments.after( info_payments );
	section_payments.appendTo( "#section_payments" );
	section_log.append( action_log_content + erase_action_log + reload_action_log + send_log_to_support );
	section_actions.appendTo( "#section_actions" );
	heading_actions.after( "<div id='actions_div'>" + check_this_year + export_xml + erase_invoice_numbers + reset_queue + "</div>" );
	$( ".submit" ).append( save_options );
	$( ".submit" ).before( plugin_switch );
	$('#plugin_switch').lc_switch();
	if ( tckpoh_lang.plugin_switch == 1 ) {
		$('#plugin_switch').lcs_on();
	} else {
		$('#plugin_switch').lcs_off();
	}
	$("#wc_settings_pohoda_export_invoice_number_start").after(last_invoice_number);
		
	//section_mserver.hide();
	section_numbering.hide();
	section_invoice.hide();
	section_billing.hide();
	section_export.hide();
	section_payments.hide();
	section_pdf.hide();
	section_log.hide();
	$( ".submit button" ).hide();
	section_billingusetype.hide();
	$( "#wc_settings_pohoda_export_actions_buttons" ).hide();
		
	heading_mserver.on("click", function(){
		section_mserver.toggle("slow");
	});
	heading_numbering.on("click", function(){
		section_numbering.toggle("slow");
	});
	heading_invoice.on("click", function(){
		section_invoice.toggle("slow");
	});
	heading_billing.on("click", function(){
		section_billing.toggle("slow");
	});
	heading_export.on("click", function(){
		section_export.toggle("slow");
	});
	heading_payments.on("click", function(){
		section_payments.toggle("slow");
	});
	heading_pdf.on("click", function(){
		section_pdf.toggle("slow");
	});
	heading_log.on("click", function(){
		section_log.toggle("slow");
	});


	// load action log content //

	function load_action_log() {
		$.ajax({
			url : tckpoh_lang.action_log_url,
			dataType : "html",
			cache : false,
			beforeSend: function() {
				$("#action_log_content").empty();
			},
			complete : function () {
				$("#action_log_content").html(function(i,c) {
					return c.replace(/\d+/g, function(v) {
						return "<span class='numbers'>" + v + "</span>";
					});
				});
			},
			success : function (data) {
				$("#action_log_content").html(data);
			}
		});
	}
	$("#reload_action_log").on("click", function() {
		load_action_log();
	});
	
	load_action_log();
	
	
	// minor setting //
		
	$( "#wc_settings_pohoda_export_invoice_classification_type").addClass("obligatorytofill");

	$("#wc_settings_pohoda_export_invoice_plugin_numbering").addClass("separated_field");
	if (tckpoh_lang.numbering_type == 'yes') {
		$("#wc_settings_pohoda_export_invoice_prefix_type, #wc_settings_pohoda_export_invoice_prefix, #wc_settings_pohoda_export_invoice_number_count, #wc_settings_pohoda_export_invoice_number_start, #wc_settings_pohoda_export_invoice_suffix_type, #wc_settings_pohoda_export_invoice_prefix_pohoda, #wc_settings_pohoda_export_invoice_suffix").addClass("befilled");
		$("#wc_settings_pohoda_export_invoice_plugin_numbering").removeClass("befilled");
	} else {
		if (tckpoh_lang.prefix == 'pohoda') {
			$("#wc_settings_pohoda_export_invoice_prefix").addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_prefix_pohoda").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_prefix").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_prefix_pohoda").addClass("befilled");
		}
	}
	if (tckpoh_lang.export_orders == 'yes') {
		$("#wc_settings_pohoda_export_invoice_export_orders_number").removeClass("befilled");
	}
	if (tckpoh_lang.specific !== 'custom' && tckpoh_lang.specific !== '') {
		$("#wc_settings_pohoda_export_invoice_data_specific_custom").addClass("befilled");	
	}
	if (tckpoh_lang.headingtext !== 'custom' && tckpoh_lang.headingtext !== '') {
		$("#wc_settings_pohoda_export_invoice_text_custom").addClass("befilled");	
	}
	if (tckpoh_lang.center_type == 'custom') {
		$("#wc_settings_pohoda_export_invoice_center_custom").removeClass("befilled");	
		$("#wc_settings_pohoda_export_invoice_center_select").addClass("befilled");	
	}
	if (tckpoh_lang.activity_type == 'custom') {
		$("#wc_settings_pohoda_export_invoice_activity_custom").removeClass("befilled");	
		$("#wc_settings_pohoda_export_invoice_activity_select").addClass("befilled");	
	}
	if (tckpoh_lang.enable_pdf == 'yes') {
		$("#wc_settings_pohoda_export_pdf_notice").removeClass("befilled");
	}
	if (tckpoh_lang.enable_pdf_emails == 'yes') {
		$("#wc_settings_pohoda_export_pdf_status").removeClass("befilled");
	}
	if (tckpoh_lang.enable_pdf_qrcode == 'yes') {
		$("#wc_settings_pohoda_export_pdf_iban").removeClass("befilled");
	}
	$("#wc_settings_pohoda_export_invoice_export_day_in_month").addClass("separated_field");
	
	if (!tckpoh_lang.export_status) { $("#wc_settings_pohoda_export_invoice_export_status").val('wc-processing').change(); }

	$( ".separated_field" ).parents( "tr" ).after( "<tr><td colspan='2'><hr class='separated_hr' /></td></tr>" );
	
	// connect to mserver if info filled //
	
	if ($("#wc_settings_pohoda_export_mserver_address").val().length > 1
		&& $("#wc_settings_pohoda_export_mserver_login").val().length > 1
		&& $("#wc_settings_pohoda_export_mserver_password").val().length > 1)
	{
		pohoda_mserver_connect(true);	
	}
	
	// set saved options //
	
	function set_saved_options() {
		
		if (tckpoh_lang.account) { $("#wc_settings_pohoda_export_billing_account").val(tckpoh_lang.account); }
		if (tckpoh_lang.pohoda_prefix) { $("#wc_settings_pohoda_export_invoice_prefix_pohoda").val(tckpoh_lang.pohoda_prefix); }
		if (tckpoh_lang.classification_type) { $("#wc_settings_pohoda_export_invoice_classification_type").val(tckpoh_lang.classification_type); }
		if (tckpoh_lang.centre) { $("#wc_settings_pohoda_export_invoice_center_select").val(tckpoh_lang.centre); }
		if (tckpoh_lang.activity) { $("#wc_settings_pohoda_export_invoice_activity_select").val(tckpoh_lang.activity); }
		if (tckpoh_lang.export_type) { $("#wc_settings_pohoda_export_invoice_export_type").val(tckpoh_lang.export_type).change(); }
		if (tckpoh_lang.export_status) { $("#wc_settings_pohoda_export_invoice_export_status").val(tckpoh_lang.export_status).change(); }
		if (tckpoh_lang.payment_card) { $("#wc_settings_pohoda_export_payment_methods_card_text").val(tckpoh_lang.payment_card).change(); }
		if (tckpoh_lang.payment_cash) { $("#wc_settings_pohoda_export_payment_methods_cash_text").val(tckpoh_lang.payment_cash).change(); }
		if (tckpoh_lang.payment_bacs) { $("#wc_settings_pohoda_export_payment_methods_transfer_text").val(tckpoh_lang.payment_bacs).change(); }
		if (tckpoh_lang.payment_cod) { $("#wc_settings_pohoda_export_payment_methods_cod_text").val(tckpoh_lang.payment_cod).change(); }
		if (tckpoh_lang.payment_special) { $("#wc_settings_pohoda_export_payment_methods_special_text").val(tckpoh_lang.payment_special).change(); }
		
	}
	
	//// BASIC FUNCTIONS ////
	
	function openloader(loading_text) {
		$('.wrap').css('opacity', '0.25');
		$('#wpwrap').append('<div class="wrap_loader"><p class="loading_text">'+ loading_text +'</p></div>');
	}
	
	function closeloader() {
	    $('#wpwrap').find(".wrap_loader").remove();  
	    $('.wrap').css('opacity', '1');
	}
	
	//// AJAX FUNCTIONS ////
	
	// connect to mserver //
	
	function pohoda_mserver_connect(filled) {
		$.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  dataType: 'json',
			  data: {
				  'action': 'pohoda_mserver_connect',
				  'host': $("#wc_settings_pohoda_export_mserver_address").val(),
				  'login': $("#wc_settings_pohoda_export_mserver_login").val(),
				  'pass': $("#wc_settings_pohoda_export_mserver_password").val(),
			  },
			  beforeSend: function() {
				  openloader(tckpoh_lang.loader_connecting);
			  },
			  complete: function(){
				  closeloader();
			  },
			  success: function( data ) {
				  if (data.error != true) {
					  
					  $.each(data.units, function(index, obj) {
						  $("#wc_settings_pohoda_export_mserver_accounting").append(new Option(index, obj[0])).focus();
					  });

						if (tckpoh_lang.accounting_key) {
							$("#wc_settings_pohoda_export_mserver_accounting").val(tckpoh_lang.accounting_key).change();
							if ($("input[type=radio][name=wc_settings_pohoda_export_billing_use_type]").val() == 'pohoda') {
								pohoda_load_billing_info();
							}
						}
					  
				  } else {
					  alert(tckpoh_lang.mserver_connect_failed);
				  }
			  }
		});
	}
	$("#mserver_connect").on("click", function(){
		pohoda_mserver_connect();
	});
	
	
	// choose an accounting //
	
	$("#wc_settings_pohoda_export_mserver_accounting").change(function() {
		$("#wc_settings_pohoda_export_accounting_key").val($(this).val());
		pohoda_load_billing_info();
		//section_billing.show("slow");
		$('html, body').animate({ scrollTop: (section_billing.offset().top - 115) }, 500);
	});
	
	
	// choose billing type //
	
	function pohoda_load_billing_info() {
		$.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  dataType: 'json',
			  data: {
				  'action': 'pohoda_load_billing_info',
				  'accounting_key': $("#wc_settings_pohoda_export_accounting_key").val(),
				  'host': $("#wc_settings_pohoda_export_mserver_address").val(),
				  'login': $("#wc_settings_pohoda_export_mserver_login").val(),
				  'pass': $("#wc_settings_pohoda_export_mserver_password").val(),
			  },
			  beforeSend: function() {
				  openloader(tckpoh_lang.loader_loading);
			  },
			  complete: function(){
				  closeloader();
				  set_saved_options();
			  },
			  success: function( data ) {
				  if (data.error != true) {
					  
					    $("#wc_settings_pohoda_export_billing_company").val(data.company[0]);
					    $("#wc_settings_pohoda_export_billing_company_title").val(data.title[0]);
					    $("#wc_settings_pohoda_export_billing_surname").val(data.surname[0]);
					    $("#wc_settings_pohoda_export_billing_name").val(data.name[0]);
						$("#wc_settings_pohoda_export_billing_address_street").val(data.street[0]);
						$("#wc_settings_pohoda_export_billing_address_street_number").val(data.number[0]);
						$("#wc_settings_pohoda_export_billing_address_city").val(data.city[0]);
						$("#wc_settings_pohoda_export_billing_address_code").val(data.zip[0]);
						$("#wc_settings_pohoda_export_billing_ico").val(data.ico[0]);
						$("#wc_settings_pohoda_export_billing_dic").val(data.dic[0]);
						$("#wc_settings_pohoda_export_billing_phone").val(data.phone[0]);
						$("#wc_settings_pohoda_export_billing_email").val(data.email[0]);
						$("#wc_settings_pohoda_export_billing_website").val(data.www[0]);
						
						// bankovni_ucty //
						$("#wc_settings_pohoda_export_billing_account").empty().append("<option value=''>"+ tckpoh_lang.choose_one +"</option>");
						$.each(data.bankovni_ucty, function(index, obj) {
						   $("#wc_settings_pohoda_export_billing_account").append($('<option />')
								.val(obj.account_ids[0])
								.text(obj.account_bank_name[0] + ' - ' + obj.account_number[0])
								.data({
								    bank_code: obj.account_bank[0],
								    account_number: obj.account_number[0]
								})
						   );
					  	});
						// predkontace //
						$("#wc_settings_pohoda_export_invoice_classification_type").empty().append("<option value=''>"+ tckpoh_lang.choose_one +"</option>");
						$.each(data.predkontace, function(index, obj) {
						  $("#wc_settings_pohoda_export_invoice_classification_type").append(new Option(obj.predkontace_name[0] + ' - ' + obj.predkontace_code[0], obj.predkontace_code[0]));
					  	});
						// prefixy //
						$("#wc_settings_pohoda_export_invoice_prefix_pohoda").empty().append("<option value=''>"+ tckpoh_lang.choose_one +"</option>");
						$.each(data.prefixy, function(index, obj) {
						  $("#wc_settings_pohoda_export_invoice_prefix_pohoda").append(new Option(obj.prefix_name[0] + ' - ' + obj.prefix_code[0], obj.prefix_code[0]));
					  	});
						// cinnosti //
						$("#wc_settings_pohoda_export_invoice_activity_select").empty().append("<option value=''>"+ tckpoh_lang.choose_one +"</option>");
						$.each(data.cinnosti, function(index, obj) {
						  $("#wc_settings_pohoda_export_invoice_activity_select").append(new Option(obj.cinnost_name[0] + ' - ' + obj.cinnost_code[0], obj.cinnost_code[0]));
					  	});
						// strediska //
						$("#wc_settings_pohoda_export_invoice_center_select").empty().append("<option value=''>"+ tckpoh_lang.choose_one +"</option>");
						$.each(data.strediska, function(index, obj) {
						  $("#wc_settings_pohoda_export_invoice_center_select").append(new Option(obj.centrum_name[0] + ' - ' + obj.centrum_code[0], obj.centrum_code[0]));
					  	});
						
						// formy uhrad //
						$("#wc_settings_pohoda_export_payment_methods_card_text, #wc_settings_pohoda_export_payment_methods_cash_text, #wc_settings_pohoda_export_payment_methods_transfer_text, #wc_settings_pohoda_export_payment_methods_cod_text, #wc_settings_pohoda_export_payment_methods_special_text").empty().append("<option value=''></option>");
						$.each(data.formy_uhrad, function(index, obj) {
						  $("#wc_settings_pohoda_export_payment_methods_card_text, #wc_settings_pohoda_export_payment_methods_cash_text, #wc_settings_pohoda_export_payment_methods_transfer_text, #wc_settings_pohoda_export_payment_methods_cod_text, #wc_settings_pohoda_export_payment_methods_special_text").append(new Option(obj.forma_text[0], obj.forma_name[0]));
					  	});
										  
				  } else {
					  alert(tckpoh_lang.mserver_connect_failed);
				  }
			  }
		});
	}
	
	$("input[type=radio][name=wc_settings_pohoda_export_billing_use_type]").change(function() {
		
		if ( $(this).val() == 'pohoda' ) {
			
			$("#wc_settings_pohoda_export_billing_account").removeClass("befilled");
			pohoda_load_billing_info();
			
		} else if ( $(this).val() == 'woo' ) {
			
			$("#wc_settings_pohoda_export_billing_account").empty().addClass("befilled");
			$("#wc_settings_pohoda_export_billing_company").val(tckpoh_lang.billing_company);
			$("#wc_settings_pohoda_export_billing_address_street").val(tckpoh_lang.billing_address_street);
			$("#wc_settings_pohoda_export_billing_address_street_number").val(tckpoh_lang.billing_address_street_number);
			$("#wc_settings_pohoda_export_billing_address_city").val(tckpoh_lang.billing_address_city);
			$("#wc_settings_pohoda_export_billing_address_code").val(tckpoh_lang.billing_address_code);
			$("#wc_settings_pohoda_export_billing_email").val(tckpoh_lang.billing_email);
			$("#wc_settings_pohoda_export_billing_website").val(tckpoh_lang.billing_website);
			
		} else if ( $(this).val() == 'custom' ) {
			
			$("#wc_settings_pohoda_export_billing_account").empty().addClass("befilled");
			
		}
	});
	
	
	// vyber bankovniho uctu //
	$("#wc_settings_pohoda_export_billing_account").change(function() {
			$("#wc_settings_pohoda_export_billing_account_number").val($(this).find(':selected').data('account_number'));
			$("#wc_settings_pohoda_export_billing_account_bank_id").val($(this).find(':selected').data('bank_code'));
			$("#wc_settings_pohoda_export_billing_account_id").val($(this).val());			
	});
	
	// vyber prefixu //
	$("#wc_settings_pohoda_export_invoice_prefix_type").change(function() {
		if ( $(this).val() == 'pohoda' ) {
			$("#wc_settings_pohoda_export_invoice_prefix").val($("#wc_settings_pohoda_export_invoice_prefix_pohoda").val());
			$("#wc_settings_pohoda_export_invoice_prefix").addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_prefix_pohoda").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_prefix").val("").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_prefix_pohoda").addClass("befilled");
		}
	});
	$("#wc_settings_pohoda_export_invoice_prefix_pohoda").change(function() {
			$("#wc_settings_pohoda_export_invoice_prefix").val($(this).val()).change();			
	});

	// vyber typu cislovani //
	$("input[type=radio][name=wc_settings_pohoda_export_invoice_numbering_type]").change(function() {
		if ( $(this).val() == 'yes' ) {
			$("#wc_settings_pohoda_export_invoice_prefix_type, #wc_settings_pohoda_export_invoice_prefix, #wc_settings_pohoda_export_invoice_number_count, #wc_settings_pohoda_export_invoice_number_start, #wc_settings_pohoda_export_invoice_suffix_type, #wc_settings_pohoda_export_invoice_prefix_pohoda, #wc_settings_pohoda_export_invoice_suffix").addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_plugin_numbering").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_prefix_type, #wc_settings_pohoda_export_invoice_prefix, #wc_settings_pohoda_export_invoice_number_count, #wc_settings_pohoda_export_invoice_number_start, #wc_settings_pohoda_export_invoice_suffix_type").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_plugin_numbering").addClass("befilled");
		}
	});

	// vyber exportu objednavek //
	$("input[type=radio][name=wc_settings_pohoda_export_invoice_export_orders]").change(function() {
		if ( $(this).val() == 'yes' ) {
			$("#wc_settings_pohoda_export_invoice_export_orders_number").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_export_orders_number").addClass("befilled");
		}
	});
	
	// vyber sufixu //
	$("#wc_settings_pohoda_export_invoice_suffix_type").change(function() {
		if ( $(this).val() == 'order' ) {
			$("#wc_settings_pohoda_export_invoice_suffix").val("1234").change().addClass("befilled");
		} else if ( $(this).val() == 'none' ) {
			$("#wc_settings_pohoda_export_invoice_suffix").val("").change().addClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_suffix").val("").change().removeClass("befilled");
		}
	});
	
	// editace cislovani //
	function show_the_number() {
		var prefix = $("#wc_settings_pohoda_export_invoice_prefix").val();
		var number_count = parseInt($("#wc_settings_pohoda_export_invoice_number_count").val());
		var number_start = parseInt($("#wc_settings_pohoda_export_invoice_number_start").val());
		var core = (number_start).toLocaleString('en-US', {minimumIntegerDigits: number_count, useGrouping:false});
		if ($("#wc_settings_pohoda_export_invoice_suffix").val().length > 0) {
			var sufix = '-' + $("#wc_settings_pohoda_export_invoice_suffix").val();
		} else { 
			var sufix = '';
		}
		$("#example_numbering").html(tckpoh_lang.numbering_example + '<br/><b>' + prefix + core + sufix + '</b>' );
	}
	show_the_number();
	$("#wc_settings_pohoda_export_invoice_prefix").on('keyup change', function () {
		show_the_number();			
	});
	$("#wc_settings_pohoda_export_invoice_number_count").change(function() {
		show_the_number();			
	});
	$("#wc_settings_pohoda_export_invoice_number_start").keyup(function() {
		show_the_number();			
	});
	$("#wc_settings_pohoda_export_invoice_suffix").on('keyup change', function () {
		show_the_number();			
	});
	
	// vyber specifickeho //
	$("#wc_settings_pohoda_export_invoice_data_specific").change(function() {
		if ( $(this).val() == 'custom' ) {
			$("#wc_settings_pohoda_export_invoice_data_specific_custom").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_data_specific_custom").val("").change().addClass("befilled");
		}
	});
	
	// vyber nadpisu //
	$("#wc_settings_pohoda_export_invoice_text").change(function() {
		if ( $(this).val() == 'custom' ) {
			$("#wc_settings_pohoda_export_invoice_text_custom").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_text_custom").val("").change().addClass("befilled");
		}
	});
	
	// vyber strediska //
	$("#wc_settings_pohoda_export_invoice_center_type").change(function() {
		if ( $(this).val() == 'custom' ) {
			$("#wc_settings_pohoda_export_invoice_center_custom").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_center_select").addClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_center_custom").val($("#wc_settings_pohoda_export_invoice_center_select").val()).change().addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_center_select").removeClass("befilled");
		}
	});
	
	$("#wc_settings_pohoda_export_invoice_center_select").change(function() {
			$("#wc_settings_pohoda_export_invoice_center_custom").val($(this).val());			
	});
	
	// vyber aktivity //
	$("#wc_settings_pohoda_export_invoice_activity_type").change(function() {
		if ( $(this).val() == 'custom' ) {
			$("#wc_settings_pohoda_export_invoice_activity_custom").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_activity_select").addClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_invoice_activity_custom").val($("#wc_settings_pohoda_export_invoice_activity_select").val()).change().addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_activity_select").removeClass("befilled");
		}
	});
	
	$("#wc_settings_pohoda_export_invoice_activity_select").change(function() {
			$("#wc_settings_pohoda_export_invoice_activity_custom").val($(this).val());			
	});
		
	// vyber exportu //
	$("#wc_settings_pohoda_export_invoice_export_type").change(function() {
		if ( $(this).val() == 'order' ) {
			$("#wc_settings_pohoda_export_invoice_export_time").addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_week").addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_month").addClass("befilled");
		} else if ( $(this).val() == 'daily' ) {
			$("#wc_settings_pohoda_export_invoice_export_time").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_week").addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_month").addClass("befilled");
		} else if ( $(this).val() == 'weekly' ) {
			$("#wc_settings_pohoda_export_invoice_export_time").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_week").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_month").addClass("befilled");
		} else if ( $(this).val() == 'monthly' ) {
			$("#wc_settings_pohoda_export_invoice_export_time").removeClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_week").addClass("befilled");
			$("#wc_settings_pohoda_export_invoice_export_day_in_month").removeClass("befilled");
		}
	});
	$("#wc_settings_pohoda_export_invoice_export_time").prop('type', 'time');


	// vyber pdf exportu //

	$("input[type=radio][name=wc_settings_pohoda_export_pdf_enable]").change(function() {
		if ( $(this).val() == 'yes' ) {
			$("#wc_settings_pohoda_export_pdf_notice").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_pdf_notice").addClass("befilled");
		}
	});
	$("input[type=radio][name=wc_settings_pohoda_export_pdf_emails]").change(function() {
		if ( $(this).val() == 'yes' ) {
			$("#wc_settings_pohoda_export_pdf_status").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_pdf_status").addClass("befilled");
		}
	});
	$("input[type=radio][name=wc_settings_pohoda_export_pdf_qrcode]").change(function() {
		if ( $(this).val() == 'yes' ) {
			$("#wc_settings_pohoda_export_pdf_iban").removeClass("befilled");
		} else {
			$("#wc_settings_pohoda_export_pdf_iban").addClass("befilled");
		}
	});
        
	
	//// ulozit zmeny ////
	
	function pohoda_save_options() {
		
		if ( $("#wc_settings_pohoda_export_accounting_key").val().length < 1 ) {
			if (confirm(tckpoh_lang.choose_accounting) == false) {
				return false;
			}
		}

		var power = ($("#plugin_switch").is(':checked')) ? '1' : '0';
		
		$.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  dataType: 'json',
			  data: {
				  'action': 'pohoda_save_options',
				  'options': $('#mainform :input').not("#mainform input[type=hidden]").serializeJSON(),
				  'power' : power,
			  },
			  beforeSend: function() {
				  openloader(tckpoh_lang.saving_options);
			  },
			  complete: function(){
				  closeloader();
			  },
			  success: function( data ) {
				  if (data.error == 'true') {
					  alert(tckpoh_lang.could_not_switch);
					  if (power == 1) {
						 $("#plugin_switch").val(0).change();
					  } else {
						 $("#plugin_switch").val(1).change(); 
					  }
				  }
				  // else saved and switched //
			  }
		});
	}
	$("#save_options").on("click", function(){
		pohoda_save_options();
	});
	
	//// plugin switch ////	
	
	$('body').delegate('#plugin_switch', 'lcs-statuschange', function() {
		pohoda_save_options();
	});
	
	
	//// zkontrolovat nevyexportovane objednavky z tohoto roku ////
	
	function pohoda_check_this_year() {
		
		let dates_chosen = prompt(tckpoh_lang.will_check_this_year, "");
		if ( dates_chosen !== null ) {
		
			$.ajax({
				  type: 'POST',
				  url: ajaxurl,
				  dataType: 'json',
				  data: {
					  'action': 'pohoda_check_this_year',
					  'dates_chosen': dates_chosen
				  },
				  beforeSend: function() {
					  openloader(tckpoh_lang.checking_orders);
				  },
				  complete: function(){
					  closeloader();
				  },
				  success: function( data ) {
					  					  
					  if (data.error === 'bezproblemu') {
						  alert(data.order_count + ' ' + tckpoh_lang.check_this_year_ok);
					  } else {
						  alert(tckpoh_lang.check_this_year_error);
					  }
				  },
				  error : function (data) {
					  alert(tckpoh_lang.check_this_year_error);
				  }
			});
		}
	}
	$("#check_this_year").on("click", function(){
		pohoda_check_this_year();
	});
	

	//// odeslat error log na podporu ////
	
	function log_to_support() {
				
		$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					'action': 'pohoda_send_log_to_support',
				},
				beforeSend: function() {
					openloader(tckpoh_lang.sending_log_to_support);
				},
				complete: function(){
					closeloader();
				},
				success: function( data ) {
										
					if (data === 'bezproblemu') {
						$("#send_log_to_support").addClass("log_sent");
					} else {
						alert(tckpoh_lang.could_not_send);
					}
				},
				error : function (data) {
					alert(tckpoh_lang.could_not_send);
				}
		});
	}
	$("#send_log_to_support").on("click", function(){
		log_to_support();
	});
	

	
	//// resetovat cislo posledni faktury ////
	
	function pohoda_reset_core_number( whattodo ) {
				
			$.ajax({
				  type: 'POST',
				  url: ajaxurl,
				  dataType: 'json',
				  data: {
					  'action': 'pohoda_reset_core_number',
					  'whattodo' : whattodo,
				  },
				  beforeSend: function() {
					  openloader(tckpoh_lang.reseting_number);
				  },
				  complete: function(){
					  closeloader();
				  },
				  success: function( data ) {
					  
					  if ( data.error === 'bezproblemu' ) {
						  $( "#wc_settings_pohoda_export_invoice_number_start" ).val('1');
						  $( "#last_invoice_number" ).html( '' );
					  }
					  
				  }
			});
	}
	$("#reset_core_number").on("click", function(){
		if (confirm(tckpoh_lang.this_will_reset_core_number) == true) {
			pohoda_reset_core_number('just_reset');
		}
	});
	$("#reset_core_number_erase").on("click", function(){
		if (confirm(tckpoh_lang.this_will_delete_invoice_numbers) == true) {
			pohoda_reset_core_number('and_erase_invoice_numbers');
		}
	});


	//// resetovat cislo posledni faktury ////
	
	function pohoda_reset_queue() {
				
		$.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data: {
				  'action': 'pohoda_reset_queue',
			  },
			  beforeSend: function() {
				  openloader(tckpoh_lang.reseting_number);
			  },
			  complete: function(){
				  closeloader();
			  },
			  success: function( data ) {
				  
					if ( data === 'bezproblemu' ) {
						alert(tckpoh_lang.queue_was_erased);
					}
				  
			  }
		});
	}
	$("#reset_queue").on("click", function() {
		if (confirm(tckpoh_lang.this_will_reset_queue) == true) {
			pohoda_reset_queue();
		}
	});


	//// exportova xml soubor ////
	
	function export_xml_file() {
				
		$.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data: {
				  'action': 'pohoda_export_xml_file',
			  },
			  beforeSend: function() {
				  openloader(tckpoh_lang.exporting_xml);
			  },
			  complete: function(){
				  closeloader();
			  },
			  success: function( data ) {
				  
					if ( data !== 'no_file' ) {
						alert( data );
						window.open( data, '_blank' ).focus();
					} else {
						alert(tckpoh_lang.xml_could_not_be_created);
					}
				  
			  }
		});
	}
	$("#export_xml").on("click", function() {
		if (confirm(tckpoh_lang.this_will_export_xml) == true) {
			export_xml_file();
		}
	});

	//// vymazat protokol chyb ////
	
	function pohoda_erase_action_log() {
				
		$.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data: {
				  'action': 'erase_action_log',
			  },
			  beforeSend: function() {
				  openloader(tckpoh_lang.erasing_action_log);
			  },
			  complete: function(){
				  closeloader();
			  },
			  success: function( data ) {
				  $("#action_log_content").html('');
			  }
		});
	}
	$("#erase_action_log").on("click", function() {
		pohoda_erase_action_log();
	});
	

	window.removeEventListener('beforeunload', onbeforeunload);
			
});