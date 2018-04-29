var wprc = {
    
    utils:
    {
        alert: function(alert_title, alert_message)
        {
            jQuery('#wprc-alert').remove();
            jQuery( '<div id="wprc-alert"></div>' ).dialog({
                autoOpen: false,
                title: alert_title,
    			modal: true,
    			buttons: {
    				Ok: function() {
    					jQuery(this).dialog('close');
    				}
    			}
    		});
            jQuery('#wprc-alert').html(alert_message).dialog('open');
        },
        
        urlQuery:
        {
            getUrlParams: function()
            {
            	var url = document.location.href.split('?');
            	if(url[1]==undefined) return false;
            	var arr = url[1].split('&');
            	var params = new Object();
            	for(i=0;i<arr.length;i++)
            	{
            		var param = arr[i].split('=');
            		params[param[0]] = param[1];	
            	}
            	return params;
            }
        }
    },
    
    uninstallReport: 
    {
        init: function()
        {
            jQuery('#wprc-loader').hide();
			jQuery('#plugin-select').multiselect({
                header: false,
                noneSelectedText: wprcLang.select_plugins
            }); 
           
           jQuery('#theme-select').multiselect({
                header: false,
                noneSelectedText: wprcLang.select_themes
           });   
           
           jQuery(':radio').bind('change', function()
           {
                wprc.uninstallReport.cleanValidationErrors();
           });
        },
        
        cleanValidationErrors: function()
        {
            jQuery('.wprc.validation-errors').empty();
        },
        
        outputValidationError: function(msg, container_id)
        { 
            var default_container = '#below-submit-errors';
            
            wprc.uninstallReport.cleanValidationErrors();
            if(container_id !== undefined)
            {
                container_id = '#' + container_id + '-errors';
                jQuery('.wprc.validation-errors'+container_id).append(msg);
            }
           // alert(container_id);
          //  alert(jQuery('.wprc.validation-errors#below-submit-errors').val());
            //jQuery('.wprc.validation-errors'+container_id).empty().append(msg);

            jQuery('.wprc.validation-errors'+default_container).append(msg);
        },
        
        showDetails: function(container)
        { 
            jQuery('.details-boxes').hide();
        
            if(jQuery(container+'-box')!== undefined && container !== '')
            {
                jQuery('.option-wrap').removeClass('selected');
                jQuery(container+'-wrap').addClass('selected');
                jQuery(container+'-box').show();
            }
        },
        
        showSubdetails: function(container)
        {
            jQuery('.subdetails-boxes').hide();
        
            if(jQuery(container+'-subbox')!== undefined && container !== '')
            {
                jQuery(container+'-subbox').show();
            }
        },
        
        submitDeactivationForm: function(deactivation_report_form_id,deactivation_form_id)
        {
            var form = jQuery('#'+deactivation_report_form_id);
            wprc.uninstallReport.outputValidationError();
            
            var code = form.find(':radio[name="uninstall_reason_code"]:checked').val();
            
            if(code == undefined)
            { 
                //wprc.utils.alert(wprcLang.warning, wprcLang.please_check_the_option);
                wprc.uninstallReport.outputValidationError(wprcLang.please_check_the_option);
                return false;
            }
            
            if(code == 3)
            {
                var child_code = form.find(':radio[name="uninstall_reason_code_child"]:checked').val();
         
                if(child_code == undefined)
                {
                    //wprc.utils.alert(wprcLang.warning, wprcLang.please_check_the_child_option);
                    wprc.uninstallReport.outputValidationError(wprcLang.please_check_the_child_option);
                    wprc.uninstallReport.outputValidationError(wprcLang.please_check_the_child_option, 'urc3');
                    return false;
                }
                
                if(child_code == 1) // themes
                {
                    var themes = form.find('#theme-select').val();
                    if(themes == null)
                    {
                        //wprc.utils.alert(wprcLang.warning, wprcLang.please_check_the_problem_themes);
                        wprc.uninstallReport.outputValidationError(wprcLang.please_check_the_problem_themes);
                        wprc.uninstallReport.outputValidationError(wprcLang.please_check_the_problem_themes, 'urc31');
                        return false;
                    }
                }
                
                if(child_code == 2) // plugins
                {
                    var plugins = form.find('#plugin-select').val();
                    if(plugins == null)
                    {
                        //wprc.utils.alert(wprcLang.warning, wprcLang.please_check_the_problem_plugins);
                        wprc.uninstallReport.outputValidationError(wprcLang.please_check_the_problem_plugins);
                        wprc.uninstallReport.outputValidationError(wprcLang.please_check_the_problem_plugins, 'urc32');
                        return false;
                    }
                }
            }
			var deactivation_form = jQuery('#'+deactivation_form_id);
            jQuery('#wprc-loader').show();
			jQuery.post(wprc.uninstallReport.ajaxurl, form.serialize(),function(){
				if (wprc.uninstallReport.action=="bulk_action")
					deactivation_form.submit();
				else
					document.location=wprc.uninstallReport.gotourl;
			});			
			//form.submit();
			return false;
        },
        
        skipUninstallReport: function(deactivation_report_form_id,deactivation_form_id)
        {
            /*var report_form = jQuery('#'+form_id);
            
            var new_action = form.attr('action').replace('sendUninstallReport', 'skipUninstallReport');
            form.attr('action', new_action);
            
            form.submit();*/
            
			var deactivation_form=jQuery('#'+deactivation_form_id);
			
			if (wprc.uninstallReport.action=="bulk_action")
				deactivation_form.submit();
			else
				document.location=wprc.uninstallReport.gotourl;
				
			return false;
        }
    }, // uninstall report end
    
    repositories:
    {
        updateExtensionMap : function(el)
        {
            jQuery('.wprc-loader').show();
            
            var timedout=false;
            var timeout=null;
            
            var xhr=jQuery.ajax({
                url: jQuery(el).attr('href'),
                data: 'foo123',
                dataType: 'json',
                error:function(){
                    if (timeout!=null) clearTimeout(timeout);
                    jQuery('.wprc-loader').hide();
                    if (!timedout)
                        alert(wprc_config.unknown_error);
                    else
                        alert(wprc_config.timeout_error);
                },
                success: function(resp){
                    if (timeout!=null) clearTimeout(timeout);
                    jQuery('.wprc-loader').hide();
                    if(resp.result != false){
                        alert(sprintf(wprc_config.d_ext_updated_msg,resp.result));
                    }else{
                        alert(wprc_config.no_ext_updated_msg);
                    }
                }                    
            });
            setTimeout(function(){
                timedout=true;
                xhr.abort();
            },parseInt(wprc_config.TIMEOUT));
            return false;
        },
        
        clearLoginInfo : function(el,repository_name)
        {
            
            doit=confirm(sprintf(wprc_config.login_clear_confirm,repository_name));
            if (!doit) return false;
            
            jQuery('.wprc-loader').show();
            
            var timedout=false;
            var timeout=null;
            
            var xhr=jQuery.ajax({
                url: jQuery(el).attr('href'),
                data: 'foo123',
                dataType: 'json',
                error:function(){
                    if (timeout!=null) clearTimeout(timeout);
                    jQuery('.wprc-loader').hide();
                    if (!timedout)
                        alert(wprc_config.unknown_error);
                    else
                        alert(wprc_config.timeout_error);
                },
                success: function(resp){
                    if (timeout!=null) clearTimeout(timeout);
                    jQuery('.wprc-loader').hide();
                    if(resp.result != false){
                        alert(sprintf(wprc_config.login_cleared));
                        document.location.reload(1);
                    }else{
                        alert(wprc_config.login_cleared_failed);
                    }
                }                    
            });
            setTimeout(function(){
                timedout=true;
                xhr.abort();
            },parseInt(wprc_config.TIMEOUT));
            return false;
        },
        
        validateForm: function(form_id)
        {
            var form = jQuery('#'+form_id);
            
            var name = form.find('input[name="repository_name"]').val();
            var endpoint_url = form.find('input[name="repository_endpoint_url"]').val();
            
            if(name == undefined || name == '')
            {
                wprc.utils.alert(wprcLang.warning,wprcLang.enter_repository_name);
                return false;
            }
           
            if(endpoint_url == undefined || endpoint_url == '')
            {
                wprc.utils.alert(wprcLang.warning,wprcLang.enter_repository_endpoint_url);
                return false;
            }
            
            var types = form.find('#repository_types').val();
            if(types == null || types == undefined)
            {
                wprc.utils.alert(wprcLang.warning,wprcLang.choose_repository_types);
                return false;
            }
   
           form.submit();
        },
        
        renderExtensionTypes: function(container, json_types)
        {
            // extension types list
            types_ui = '<select name="repository_types[]" id="repository_types" multiple="true">';
            jQuery.each(json_types, function(i,item)
            {
                var selected = '';
                if(item['type_enabled']==1)
                {
                    selected = ' selected="selected"';
                }
                types_ui += '<option value="'+item['id']+'"'+selected+'> '+item['type_caption']+'</option>';
            });
            types_ui += '</select>';
            
            jQuery(container).append(types_ui);
            
            jQuery('#repository_types').multiselect({
                header: false,
                selectedText: wprcLang.repository_have_N_types,
                noneSelectedText: wprcLang.select_extension_types
            });   
        }
    },
    
    search:
    {
        renderAdditionalUI: function(json_repositories, json_prices, extension_type)
        {     
            if((extension_type !== 'plugins' && extension_type !== 'themes') || extension_type == undefined)
            { 
                return false;
            }
        
            if(json_repositories == undefined)
            {
                return false;
            }
            
            var extension_select_name = '#search-'+extension_type;
           
            // set languages texts for select lists
            var lang_search_free_extensions = '';
            var lang_search_paid_extensions = '';
            var lang_search_free_and_paid_extensions = '';
            var lang_search_free_or_paid_extensions = '';            
            
            if(extension_type == 'plugins')
            {
                lang_search_free_extensions = wprcLang.search_free_plugins;
                lang_search_paid_extensions = wprcLang.search_paid_plugins;
                lang_search_free_and_paid_extensions = wprcLang.search_free_and_paid_plugins;
                lang_search_free_or_paid_extensions = wprcLang.search_free_or_paid_plugins;                
            }
            
            if(extension_type == 'themes')
            {
                lang_search_free_extensions = wprcLang.search_free_themes;
                lang_search_paid_extensions = wprcLang.search_paid_themes;
                lang_search_free_and_paid_extensions = wprcLang.search_free_and_paid_themes;
                lang_search_free_or_paid_extensions = wprcLang.search_free_or_paid_themes;                
            }
      
             // paid/free select
            var additional_ui = '';
            
            additional_ui += '<select name="prices[]" id="prices" multiple="true" style="display: none;">';
            jQuery.each(json_prices.items, function(price,filter_enabled)
            {
                var selected = '';
                if(filter_enabled==1)
                {
                    selected = ' selected="selected"';
                }
                additional_ui += '<option value="'+price+'"'+selected+'> '+price+'</option>';
            });
            additional_ui += '</select>&nbsp;';         
            
            // repositories list
            additional_ui += '<select name="repos[]" id="repos" multiple="true" style="display: none;">';
            jQuery.each(json_repositories, function(i,item)
            {
                var selected = '';
                if(item['repository_enabled']==1)
                {
                    selected = ' selected="selected"';
                }
                additional_ui += '<option value="'+item['id']+'"'+selected+'> '+item['repository_name']+'</option>';
            });
            additional_ui += '</select>';
            
            jQuery(additional_ui).prependTo(extension_select_name);
            
            jQuery('#repos').multiselect({
                header: false,
                selectedText: wprcLang.search_in_N_repositories,
                noneSelectedText: wprcLang.select_repositories
            }); 
            
            jQuery('#prices').multiselect({
                header: false,
                selectedText: json_prices.caption,
                noneSelectedText: lang_search_free_or_paid_extensions,
                
                click:  function(event, ui)
                {
                    var cur_value = jQuery(this).val();
            
                    if(ui.value == 'Free')
                    {
                        if(cur_value == null)
                        {
                            jQuery('#prices').multiselect({ selectedText: lang_search_free_extensions});
                        }
                        else
                        {
                            if(cur_value == 'Free,Paid' && ui.checked == true)
                            {
                                jQuery('#prices').multiselect({ selectedText: lang_search_free_and_paid_extensions});
                            }
                            else
                            {
                                if(cur_value == 'Paid')
                                {
                                    jQuery('#prices').multiselect({ selectedText: lang_search_free_and_paid_extensions});
                                }
                                else
                                {
                                    jQuery('#prices').multiselect({ selectedText: lang_search_paid_extensions});
                                }
                            }
                        }
                    }
                    
                    if(ui.value == 'Paid')
                    {
                        if(cur_value == null)
                        {
                            jQuery('#prices').multiselect({ selectedText: lang_search_paid_extensions});
                        }
                        else
                        {
                            if(cur_value == 'Free,Paid' && ui.checked == true)
                            {
                                jQuery('#prices').multiselect({ selectedText: lang_search_free_and_paid_extensions});
                            }
                            else
                            {
                                if(cur_value == 'Free')
                                {
                                    jQuery('#prices').multiselect({ selectedText: lang_search_free_and_paid_extensions});
                                }
                                else
                                {
                                    jQuery('#prices').multiselect({ selectedText: lang_search_free_extensions});
                                }
                            }
                        }
                    }
                }
            }); 
                        
            jQuery(extension_select_name).show();
        }
    },
    
    login : {
        xhr : null,
        init : function(){
            
            //jQuery('#wprc-login-cancel').hide();
            //jQuery('#wprc-login-submit').show();
            
            var ajaxurl=wprc.login.ajaxloginurl;
            
            /*if (wprc_config.DEBUG)
            {
                if (window.console)
                    console.log(ajaxurl);
            }*/
			
            jQuery('#wprc-loader').hide();
            jQuery('#wprc_repository_login_form').live('submit', function(event){
                
                event.preventDefault();
                
                wprc.login.xhr=null;
                var timeout=null;
                var timedout=false;
                
                if(jQuery(this).find("input[name=username]").length && jQuery(this).find("input[name=username]").val()=='') return false;
                if(jQuery(this).find("input[name=password]").length && jQuery(this).find("input[name=password]").val()=='') return false;
                
                jQuery('#wprc_repository_login_fail p').html('');
                jQuery('#wprc_repository_login_fail').hide('');
                
                jQuery('#wprc-loader').show();
                //jQuery('#wprc-login-cancel').show();
                //jQuery('#wprc-login-submit').hide();
				
                
                wprc.login.xhr=jQuery.ajax({
                    url: ajaxurl,
                    data: jQuery(this).serialize(),
                    dataType: 'json',
                    //timeout: 5000,
                    error:function(){
                        if (timeout!=null) clearTimeout(timeout);
                        if (!timedout)
                            jQuery('#wprc_repository_login_fail p').html(wprc_config.unknown_error);
                        else
                            jQuery('#wprc_repository_login_fail p').html(wprc_config.timeout_error);
                        jQuery('#wprc_repository_login_fail').fadeIn();
                        jQuery('#wprc-loader').hide();
                        //jQuery('#wprc-login-cancel').hide();
                        //jQuery('#wprc-login-submit').show();
                    },
                    success: function(resp){
                        if (timeout!=null) clearTimeout(timeout);
                        if(resp.success == 1){
                            jQuery('#wprc_repository_login_wrap').hide();
                            jQuery('#wprc_repository_login_success').fadeIn();
                        }else{
                            jQuery('#wprc_repository_login_fail p').html(resp.message);
                            jQuery('#wprc_repository_login_fail').fadeIn();
                        }
                    //jQuery('#wprc-login-cancel').hide();
                    //jQuery('#wprc-login-submit').show();
					jQuery('#wprc-loader').hide();
					}                    
                });
                
                timeout=setTimeout(function(){
                        timedout=true;
                        if (wprc.login.xhr != null)
                            wprc.login.xhr.abort();
                },parseInt(wprc_config.TIMEOUT));
                
                return false;
            });
            
            jQuery('#wprc-login-cancel').unbind('click').click(function(){
                if (wprc.login.xhr!=null)
                    wprc.login.xhr.abort();
                tb_remove();
            });
            
            jQuery('#wprc_repository_login_close').live('click', function(){
                parent.location.reload(1);
                tb_remove();
            });
        }
    }
}