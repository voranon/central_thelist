$(function(){

	function init(){
		hideSubForms();
		setReadOnly();
		hideAjaxGif();	
		$('div[id=main]').draggable();
	}
	function hideSubForms(){
		$('.subform').hide();		
	}
	
	function setReadOnly(){
		$('.readonly').attr('readonly', true);
		//$('.readonly').css('background-color', '#888888');
	}
	function hideAjaxGif(){
		$('.loading').hide();
	}
	
	function showSubForm(id){
		var form_parent_div		= $('#' + id);
		form_parent_div.show();
		var form				= form_parent_div.find('form');
		var deployment_type		= id;
		var svn_branches		= getsvnbranches(id);
		var branches			= $(svn_branches).find('entry');
		var branch_path			= $(svn_branches).find('list').attr('path');
		
		var metadatabranches= new Array();

		$('body').data('branch_path', branch_path);
		
		var i;
		var branches_options= '';

		branches.each(function(){
			//this is an object type element; need to convert it to a jquery object
			var branch			= $(this);
			var branch_name		= branch.find('name').text();
			var option			= "<option value='" + branch_name + "'>Revision " + branch_name + "</option>";
			branches_options	+= option;			
		});


		var branch_drop_down	= form.find('select[name=branch]');
		branch_drop_down.empty().append('<option></option>').append(branches_options).end();
		revision_ajax_gif.hide();


	}
	
	$('select[name=branch]').bind('change', function(){
		
		var selected_branch		= $(this).find('option:selected').val();
		var deployment_type		= $('select[id=push_type] option:selected').val();
		var form_parent_div		= $('#' + deployment_type);		
		var form				= form_parent_div.find('form');	
		var revision_number		= form.find('select[name=revision_number]');
		var revision_number_gif	= revision_number.siblings('img[class=loading]');

		revision_number_gif.show();
		var svn_log				= getsvnlog(deployment_type, selected_branch);
		var logentries			= $(svn_log).find('logentry');


		//form_parent_div.show();

		
		var select_options			= '';
		var metalogentries			= new Array();
		var productionlogentries	= new Array();
		var i;

		logentries.each(function(){
			var log_entry									= new Object();
			log_entry.revision								= $(this).attr('revision');
			log_entry.author								= $(this).find('author').text();
			log_entry.message								= $(this).find('msg').text();
			log_entry.date									= $(this).find('date').text();
			log_entry.deployer								= $(this).find('deployer').text();
			log_entry.deployment_date						= $(this).find('deployment_date').text();
			log_entry.deployer_comments						= $(this).find('deployer_comments').text();
			log_entry.deployment_id							= $(this).find('deployment_id').text();
			var option										= "<option value='" + log_entry.revision + "'>Revision " + log_entry.revision + "</option>";
			select_options									+= option;
			metalogentries[log_entry.revision] 				= log_entry;


			
			
		});		
		
		
		

		//append log entries to meta data in the body
		$('body').data('logentries', metalogentries);



		revision_number.empty().append('<option></option>').append(select_options).end();
		revision_number_gif.hide();
		
		
	});
	
	$('select[name=revision_number]').bind('change', function(){
		//fill sub form out;

		var option_selected	= $('select[name=revision_number] option:selected');
		
		//need to implement a better method for selecting the current form
		var current_form	= $('form').has(option_selected);
		var parent_div		= current_form.parent();
		if(option_selected.val()){

			var logentries		= $('body').data('logentries');
			var log_selected	= logentries[option_selected.val()];
			current_form.find('input[name=author]').val(log_selected.author);
			current_form.find('input[name=date]').val(log_selected.date);
			current_form.find('textarea[name=svn_comments]').val(log_selected.message);
			
			if (parent_div.attr('id') == 'production'){
				current_form.find('input[name=deployer]').val(log_selected.deployer);
				current_form.find('input[name=deploymentdate]').val(log_selected.deployment_date);
				current_form.find('textarea[name=deployercomments]').val(log_selected.deployer_comments);
			}
			
		}else{
			//clear form
			clearSubform(current_form);		
		}
		

		


	})
	function clearSubform(form){
		form.find('input[name=author]').val('');
		form.find('input[name=date]').val('');
		form.find('textarea[name=comments]').val('');
	}
	function fillSubForm(option_selected){

		
		
	}
	

	$('#push_type').bind('change', function(e){
		var deployment_type		= $('#push_type option:selected').val();
		if(deployment_type){
			hideSubForms();
			showSubForm(deployment_type);	
		}else{
			hideSubForms();
		}
		
		
	})
	
	
	
	
	function getsvnlog(deployment_type, branch_name){
		var log;
		var query_string	= encodeURI("deploymenttype="+deployment_type+"&branch_name="+branch_name);
		$.ajax({
			url: "/deployment/getsvnlog",
			data: query_string,
			async: false,
			dataType: "xml",
			error: function(){
				alert("Error");
			},
			success: function(xml){
				log		= xml;
			}})
			
			return log;
	}
	function getsvnbranches(deployment_type){
		var branches;
		$.ajax({
			url: "/deployment/getsvnbranches",
			data: "deploymenttype="+deployment_type,
			async: false,
			dataType: "xml",
			error: function(){
				alert("Error");
			},
			success: function(xml){
				log		= xml;
			}})
			
			return log;
		
	}
	
	init();
	
})

