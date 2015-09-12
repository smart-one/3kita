/* Copyright (C) YOOtheme GmbH, http://www.gnu.org/licenses/gpl.html GNU/GPL */

(function($){var Plugin=function(){};$.extend(Plugin.prototype,{name:"accordionMenu",options:{mode:"default",display:null,collapseall:false},initialize:function(element,options){var options=$.extend({},this.options,options);var elms=element.find("ul.accordion");var togglers=element.find("li.toggler");if(!togglers.length)return;var contents=[];togglers.each(function(i){var toggler=$(this);var span=toggler.find("span:first");var content=$(elms[i]).parent().css("overflow","hidden");var contentheight=content.height();contents.push(content);toggler.hasClass("active")||i==options.display?content.show():content.hide().css("height",0);span.bind("click",function(){if(options.collapseall){$(contents).each(function(){$(this).hide().css("height",0)});togglers.each(function(t){if(t!=i){$(this).removeClass("active").find("span:first").removeClass("active")}})}if(options.mode=="slide"){if(toggler.hasClass("active")){content.stop().animate({height:0},function(){content.hide()})}else{content.stop().show().animate({height:contentheight})}}else{if(!toggler.hasClass("active")){content.show().css("height",contentheight)}else{content.hide().css("height",0)}}span.toggleClass("active");toggler.toggleClass("active")})})}});$.fn[Plugin.prototype.name]=function(){var args=arguments;var method=args[0]?args[0]:null;return this.each(function(){var element=$(this);if(Plugin.prototype[method]&&element.data(Plugin.prototype.name)&&method!="initialize"){element.data(Plugin.prototype.name)[method].apply(element.data(Plugin.prototype.name),Array.prototype.slice.call(args,1))}else if(!method||$.isPlainObject(method)){var plugin=new Plugin;if(Plugin.prototype["initialize"]){plugin.initialize.apply(plugin,$.merge([element],args))}element.data(Plugin.prototype.name,plugin)}else{$.error("Method "+method+" does not exist on jQuery."+Plugin.name)}})}})(jQuery);