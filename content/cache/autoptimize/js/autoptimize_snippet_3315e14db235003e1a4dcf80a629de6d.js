(function($)
{"use strict";$(document).ready(function()
{$('body').on('avia_burger_list_created','.av-burger-menu-main a',function(){var s=$(this);setTimeout(function(){var switchers=s.closest('.avia-menu.av-main-nav-wrap').find('.av-burger-overlay').find('.language_flag');switchers.each(function(){$(this).closest('li').remove();});},200);});});})(jQuery);