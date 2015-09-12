/* Copyright (C) YOOtheme GmbH, http://www.gnu.org/licenses/gpl.html GNU/GPL */

(function($){var Plugin=function(){};$.extend(Plugin.prototype,{name:"search",options:{url:document.location.href,param:"search",method:"post",minLength:3,delay:300,match:":not(li.skip)",skipClass:"skip",loadingClass:"loading",filledClass:"filled",resultClass:"result",resultsHeaderClass:"results-header",moreResultsClass:"more-results",noResultsClass:"no-results",listClass:"results",hoverClass:"selected",msgResultsHeader:"Search Results",msgMoreResults:"More Results",msgNoResults:"No results found"},initialize:function(input,options){this.options=$.extend({},this.options,options);var $this=this;this.timer=null;this.value=null;this.form=input.parent("form:first");this.input=input;this.input.attr("autocomplete","off");this.input.bind({keydown:function(event){$this.form[$this.input.val()?"addClass":"removeClass"]($this.options.filledClass);if(event&&event.which&&!event.shiftKey){switch(event.which){case 13:$this.done($this.selected);event.preventDefault();break;case 38:$this.pick("prev");event.preventDefault();break;case 40:$this.pick("next");event.preventDefault();break;case 27:case 9:$this.hide();break}}},keyup:function(event){$this.trigger()},blur:function(event){$this.hide(event)}});this.form.find("button[type=reset]").bind("click",function(){$this.form.removeClass($this.options.filledClass);$this.value=null;$this.input.focus()});this.choices=$("<ul>").addClass(this.options.listClass).hide().insertAfter(this.input)},request:function(options){var $this=this;this.form.addClass(this.options.loadingClass);$.ajax($.extend({url:this.options.url,type:this.options.method,dataType:"json",success:function(data){$this.form.removeClass($this.options.loadingClass);$this.suggest(data)}},options))},pick:function(item){var selected=null;if(typeof item!=="string"&&!item.hasClass(this.options.skipClass)){selected=item}if(item=="next"||item=="prev"){selected=this.selected?this.selected[item](this.options.match):this.choices.children(this.options.match)[item=="next"?"first":"last"]()}if(selected!=null&&selected.length){this.selected=selected;this.choices.children().removeClass(this.options.hoverClass);this.selected.addClass(this.options.hoverClass)}},done:function(selected){if(!selected){this.input.parent("form").submit();return}if(selected.hasClass(this.options.moreResultsClass)){this.input.parent("form").submit()}else if(selected.data("choice")){window.location=selected.data("choice").url}this.hide()},trigger:function(){var old=this.value;var $this=this;this.value=this.input.val();if(this.value.length<this.options.minLength){return this.hide()}if(this.value!=old){if(this.timer)window.clearTimeout(this.timer);this.timer=window.setTimeout(function(){var data={};data[$this.options.param]=$this.value;$this.request({data:data})},this.options.delay,this)}},suggest:function(data){if(!data)return;var $this=this;var events={mouseover:function(){$this.pick($(this))},click:function(){$this.done($(this))}};if(data===false){this.hide()}else{this.selected=null;this.choices.empty();$("<li>").addClass(this.options.resultsHeaderClass+" "+this.options.skipClass).html(this.options.msgResultsHeader).appendTo(this.choices).bind(events);if(data.results&&data.results.length>0){$(data.results).each(function(i){$("<li>").data("choice",this).addClass($this.options.resultClass).append($("<h3>").html(this.title)).append($("<div>").html(this.text)).appendTo($this.choices).bind(events)});$("<li>").addClass($this.options.moreResultsClass+" "+$this.options.skipClass).html($this.options.msgMoreResults).appendTo($this.choices).bind(events)}else{$("<li>").addClass(this.options.resultClass+" "+this.options.noResultsClass+" "+this.options.skipClass).html(this.options.msgNoResults).appendTo(this.choices).bind(events)}this.show()}},show:function(){if(this.visible)return;this.visible=true;this.choices.fadeIn(200)},hide:function(){if(!this.visible)return;this.visible=false;this.choices.removeClass(this.options.hoverClass).fadeOut(200)}});$.fn[Plugin.prototype.name]=function(){var args=arguments;var method=args[0]?args[0]:null;return this.each(function(){var element=$(this);if(Plugin.prototype[method]&&element.data(Plugin.prototype.name)&&method!="initialize"){element.data(Plugin.prototype.name)[method].apply(element.data(Plugin.prototype.name),Array.prototype.slice.call(args,1))}else if(!method||$.isPlainObject(method)){var plugin=new Plugin;if(Plugin.prototype["initialize"]){plugin.initialize.apply(plugin,$.merge([element],args))}element.data(Plugin.prototype.name,plugin)}else{$.error("Method "+method+" does not exist on jQuery."+Plugin.name)}})}})(jQuery);(function($){var isInputSupported="placeholder"in document.createElement("input"),isTextareaSupported="placeholder"in document.createElement("textarea");if(isInputSupported&&isTextareaSupported){$.fn.placeholder=function(){return this}}else{$.fn.placeholder=function(){return this.filter((isInputSupported?"textarea":":input")+"[placeholder]").bind("focus.placeholder",clearPlaceholder).bind("blur.placeholder",setPlaceholder).trigger("blur.placeholder").end()}}function args(elem){var newAttrs={},rinlinejQuery=/^jQuery\d+$/;$.each(elem.attributes,function(i,attr){if(attr.specified&&!rinlinejQuery.test(attr.name)){newAttrs[attr.name]=attr.value}});return newAttrs}function clearPlaceholder(){var $input=$(this);if($input.val()===$input.attr("placeholder")&&$input.hasClass("placeholder")){if($input.data("placeholder-password")){$input.hide().next().show().focus()}else{$input.val("").removeClass("placeholder")}}}function setPlaceholder(elem){var $replacement,$input=$(this);if($input.val()===""||$input.val()===$input.attr("placeholder")){if($input.is(":password")){if(!$input.data("placeholder-textinput")){try{$replacement=$input.clone().attr({type:"text"})}catch(e){$replacement=$("<input>").attr($.extend(args($input[0]),{type:"text"}))}$replacement.removeAttr("name").data("placeholder-password",true).bind("focus.placeholder",clearPlaceholder);$input.data("placeholder-textinput",$replacement).before($replacement)}$input=$input.hide().prev().show()}$input.addClass("placeholder").val($input.attr("placeholder"))}else{$input.removeClass("placeholder")}}$(function(){$("form").bind("submit.placeholder",function(){var $inputs=$(".placeholder",this).each(clearPlaceholder);setTimeout(function(){$inputs.each(setPlaceholder)},10)})});$(window).bind("unload.placeholder",function(){$(".placeholder").val("")})})(jQuery);