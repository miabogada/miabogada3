// -------------------------------------------------------------------------------------------
// 
// AVIA VIDEO API - make sure that youtube, vimeo and html 5 use the same interface
// 
// requires froogaloop vimeo library and youtube iframe api (yt can be loaded async)
// 
// -------------------------------------------------------------------------------------------


(function($)
{
    "use strict";

	$.AviaVideoAPI  =  function(options, video, option_container)
	{	
		this.videoElement = video;
		
		// actual video element. either iframe or video
		this.$video	= $( video );
		
		// container where the AviaVideoAPI object will be stored as data, and that can receive events like play, pause etc 
		// also the container that allows to overwrite javacript options by adding html data- attributes
		this.$option_container = option_container ? $( option_container ) : this.$video; 
		
		//mobile device?
		this.isMobile 	= $.avia_utilities.isMobile;
		
		//iamge fallback use
		this.fallback = this.isMobile ? this.$option_container.is('.av-mobile-fallback-image') : false;
		
		if(this.fallback) return;
		
		// set up the whole api object
		this._init( options );
		
	}

	$.AviaVideoAPI.defaults  = {
	
		loop: false,
		mute: false,
		controls: false,
		events: 'play pause mute unmute loop toggle reset unload'

	};
	
	$.AviaVideoAPI.apiFiles =
    {
    	youtube : {loaded: false, src: 'https://www.youtube.com/iframe_api' }
    }
    
    $.AviaVideoAPI.players =
    {
    	
    }
	
  	$.AviaVideoAPI.prototype =
    {
    	_init: function( options )
    	{	
			// set slider options
			this.options = this._setOptions(options);
			
			// info which video service we are using: html5, vimeo or youtube
			this.type = this._getPlayerType();
			
			this.player = false;
			
			// store the player object to the this.player variable created by one of the APIs (mediaelement, youtube, vimeo)
			this._setPlayer();			
			
			// set to true once the events are bound so it doesnt happen a second time by accident or racing condition
			this.eventsBound = false;
			
			// info if the video is playing
			this.playing = false;
			
			//set css class that video is currently not playing
			this.$option_container.addClass('av-video-paused');
			
			//play pause indicator
			this.pp = $.avia_utilities.playpause(this.$option_container);
    	},
		
		
    	//set the video options by first merging the default options and the passed options, then checking the video element if any data attributes overwrite the option set
    	_setOptions: function(options)
		{	
			var newOptions 	= $.extend( true, {}, $.AviaVideoAPI.defaults, options ),
				htmlData 	= this.$option_container.data(),
				i 			= "";

			//overwritte passed option set with any data properties on the html element
			for (i in htmlData)
			{
				if (htmlData.hasOwnProperty(i) && (typeof htmlData[i] === "string" || typeof htmlData[i] === "number" || typeof htmlData[i] === "boolean"))
				{
					newOptions[i] = htmlData[i]; 
				}
			}
		
			return newOptions;
		},
		
		
		//get the player type
		_getPlayerType: function()
		{
			var vid_src = this.$video.get(0).src || this.$video.data('src');
			
			
			if(this.$video.is('video')) 				return 'html5';
			if(this.$video.is('.av_youtube_frame')) 	return 'youtube';
			if(vid_src.indexOf('vimeo.com') != -1 ) 	return 'vimeo';
			if(vid_src.indexOf('youtube.com') != -1) 	return 'youtube';
		},
		
		//get the player object
		_setPlayer: function()
		{
			var _self = this;
			
			switch(this.type)
			{
				case "html5": 	
				
				this.player = this.$video.data('mediaelementplayer');  
				
				//appply fallback. sometimes needed for safari
				if(!this.player)
				{
					this.$video.data('mediaelementplayer', $.AviaVideoAPI.players[ this.$video.attr('id').replace(/_html5/,'') ] );
					this.player = this.$video.data('mediaelementplayer'); 
				}
				
				this._playerReady(); 
					
				break; 
					
				case "vimeo": 	
					
					this.player = Froogaloop(this.$video.get(0)); 
					this._playerReady(); 
					
				break;
					
				case "youtube": 
				
					this._getAPI(this.type);
					$('body').on('av-youtube-iframe-api-loaded', function(){ _self._playerReady(); });
					
				break;
			}
		},
		
		_getAPI: function( api )
		{	
			//make sure the api file is loaded only once
			if($.AviaVideoAPI.apiFiles[api].loaded === false)
			{	
				$.AviaVideoAPI.apiFiles[api].loaded = true;
				//load the file async
				var tag		= document.createElement('script'),
					first	= document.getElementsByTagName('script')[0];
					
				tag.src = $.AviaVideoAPI.apiFiles[api].src;
      			first.parentNode.insertBefore(tag, first);
			}
		},
		
		
		
		//wait for player to be ready, then bind events
		_playerReady: function()
		{	
			var _self = this;
			
			this.$option_container.on('av-video-loaded', function(){ _self._bindEvents(); });
			
			switch(this.type)
			{
				case "html5": 
						
					this.$video.on('av-mediajs-loaded', function(){ _self.$option_container.trigger('av-video-loaded'); });
					this.$video.on('av-mediajs-ended' , function(){ _self.$option_container.trigger('av-video-ended');  });
					
				break;
				case "vimeo": 	
					
					//finish event must be applied after ready event for firefox
					_self.player.addEvent('ready',  function(){ _self.$option_container.trigger('av-video-loaded'); 
					_self.player.addEvent('finish', function(){ _self.$option_container.trigger('av-video-ended');  });
					}); 
					
					// vimeo sometimes does not fire. fallback jquery load event should fix this
					// currently not used because it causes firefox problems
					/*
					this.$video.load(function()
					{ 	
						if(_self.eventsBound == true || typeof _self.eventsBound == 'undefined') return;
				        _self.$option_container.trigger('av-video-loaded');
						$.avia_utilities.log('VIMEO Fallback Trigger');
				    });
					*/
					
				
				break;
				
				case "youtube": 
					
					var params = _self.$video.data();
					
					if(_self._supports_video()) params.html5 = 1;
					
					_self.player = new YT.Player(_self.$video.attr('id'), {
						videoId: params.videoid,
						height: _self.$video.attr('height'),
						width: _self.$video.attr('width'),
						playerVars: params,
						events: {
						'onReady': function(){ _self.$option_container.trigger('av-video-loaded'); },
						'onError': function(player){ $.avia_utilities.log('YOUTUBE ERROR:', 'error', player); },
						'onStateChange': function(event){ 
							if (event.data === YT.PlayerState.ENDED)
							{	
								var command = _self.options.loop != false ? 'loop' : 'av-video-ended';
								_self.$option_container.trigger(command); 
							} 
						  }
						}
					});
					
					
				break;
			}
			
			//fallback always trigger after 2 seconds
			setTimeout(function()
			{ 	
				if(_self.eventsBound == true || typeof _self.eventsBound == 'undefined' || _self.type == 'youtube' ) { return; }
				$.avia_utilities.log('Fallback Video Trigger "'+_self.type+'":', 'log', _self);
				
				_self.$option_container.trigger('av-video-loaded'); 
				
			},2000);
			
		},
		
		//bind events we should listen to, to the player
		_bindEvents: function()
		{	
			if(this.eventsBound == true || typeof this.eventsBound == 'undefined')
			{
				return;
			}
			
			var _self = this, volume = 'unmute';
			
			this.eventsBound = true;
			
			this.$option_container.on(this.options.events, function(e)
			{
				_self.api(e.type);
			});
			
			if(!_self.isMobile)
			{
				//set up initial options
				if(this.options.mute != false) { volume = "mute"; 	 }
				if(this.options.loop != false) { _self.api('loop'); }
				
				_self.api(volume);
			}
			
			//set timeout to prevent racing conditions with other scripts
			setTimeout(function()
			{
				_self.$option_container.trigger('av-video-events-bound').addClass('av-video-events-bound');
			},50);
		},
		
		
		_supports_video: function() {
		  return !!document.createElement('video').canPlayType;
		},
		
		
		/************************************************************************
		PUBLIC Methods
		*************************************************************************/
		
	
		api: function( action )
		{	
			//commands on mobile can not be executed if the player was not started manually 
			if(this.isMobile && !this.was_started()) return;
			
			// prevent calling of unbound function
			if(this.options.events.indexOf(action) === -1) return;
			
			// broadcast that the command was executed
			this.$option_container.trigger('av-video-'+action+'-executed');
			
			// calls the function based on action. eg: _html5_play()
			if(typeof this[ '_' + this.type + '_' + action] == 'function')
			{
				this[ '_' + this.type + '_' + action].call(this);
			}
			
			//call generic function eg: _toggle() or _play()
			if(typeof this[ '_' + action] == 'function')
			{
				this[ '_' + action].call(this);
			}
			
		},
		
		was_started: function()
		{
			if(!this.player) return false;
		
			switch(this.type)
			{
				case "html5": 
					if(this.player.getCurrentTime() > 0) return true; 
				break; 
					
				case "vimeo": 	
					if(this.player.api('getCurrentTime') > 0) return true; 
				break;
					
				case "youtube": 
					if(this.player.getPlayerState() !== -1) return true; 
				break;
			}
			
			return false;
		},
		
		/************************************************************************
		Generic Methods, are always executed and usually set variables
		*************************************************************************/
		_play: function()
		{
			this.playing = true;
			this.$option_container.addClass('av-video-playing').removeClass('av-video-paused');
		},
		
		_pause: function()
		{
			this.playing = false;
			this.$option_container.removeClass('av-video-playing').addClass('av-video-paused');
		},
		
		_loop: function()
		{
			this.options.loop = true;
		},
		
		_toggle: function( )
		{
			var command = this.playing == true ? 'pause' : 'play';
			
			this.api(command);
			this.pp.set(command);
		},
		
		
		/************************************************************************
		VIMEO Methods
		*************************************************************************/
		
		_vimeo_play: function( )
		{	
			this.player.api('play');
		},
		
		_vimeo_pause: function( )
		{
			this.player.api('pause');	
		},
		
		_vimeo_mute: function( )
		{
			this.player.api('setVolume', 0);
		},
		
		_vimeo_unmute: function( )
		{
			this.player.api('setVolume', 0.7);
		},
		
		_vimeo_loop: function( )
		{
			// currently throws error, must be set in iframe
			// this.player.api('setLoop', true);
		},
		
		_vimeo_reset: function( )
		{
			this.player.api('seekTo',0);
		},
		
		_vimeo_unload: function()
		{
			this.player.api('unload');
		},
		
		/************************************************************************
		YOUTUBE Methods
		*************************************************************************/		
		
		_youtube_play: function( )
		{
			this.player.playVideo();
		},
		
		_youtube_pause: function( )
		{
			this.player.pauseVideo()
		},
		
		_youtube_mute: function( )
		{
			this.player.mute();
		},
		
		_youtube_unmute: function( )
		{
			this.player.unMute();
		},
		
		_youtube_loop: function( )
		{	
			// does not work properly with iframe api. needs to manual loop on "end" event
			// this.player.setLoop(true); 
			if(this.playing == true) this.player.seekTo(0);
		},
		
		_youtube_reset: function( )
		{
			this.player.stopVideo();
		},
		
		_youtube_unload: function()
		{
			this.player.clearVideo();
		},
		
		/************************************************************************
		HTML5 Methods
		*************************************************************************/
		
		_html5_play: function( )
		{
			//disable stoping of other videos in case the user wants to run section bgs
			if(this.player) 
			{	
				this.player.options.pauseOtherPlayers = false;
				this.player.play();
			}
			
		},
		
		_html5_pause: function( )
		{
			if(this.player) this.player.pause();
		},
		
		_html5_mute: function( )
		{
			if(this.player) this.player.setMuted(true);
		},
		
		_html5_unmute: function( )
		{
			if(this.player) this.player.setVolume(0.7);
		},
		
		_html5_loop: function( )
		{
			if(this.player) this.player.options.loop = true;
		},
		
		_html5_reset: function( )
		{	
			if(this.player) this.player.setCurrentTime(0);	
		},
		
		_html5_unload: function()
		{
			this._html5_pause();
			this._html5_reset();
		}
    }

    //simple wrapper to call the api. makes sure that the api data is not applied twice
    $.fn.aviaVideoApi = function( options , apply_to_parent)
    {
    	return this.each(function()
    	{	
    		// by default save the object as data to the initial video. 
    		// in the case of slideshows its more benefitial to save it to a parent element (eg: the slide)
    		var applyTo = this;
    		
    		if(apply_to_parent)
    		{
    			applyTo = $(this).parents(apply_to_parent).get(0);
    		}
    		
    		var self = $.data( applyTo, 'aviaVideoApi' );
    		
    		if(!self)
    		{
    			self = $.data( applyTo, 'aviaVideoApi', new $.AviaVideoAPI( options, this, applyTo ) );
    		}
    	});
    }
    
})( jQuery );

window.onYouTubeIframeAPIReady = function(){ jQuery('body').trigger('av-youtube-iframe-api-loaded'); };



/*Vimeo Frogaloop API for videos*/
var Froogaloop=function(){function e(a){return new e.fn.init(a)}function g(a,c,b){if(!b.contentWindow.postMessage)return!1;a=JSON.stringify({method:a,value:c});b.contentWindow.postMessage(a,h)}function l(a){var c,b;try{c=JSON.parse(a.data),b=c.event||c.method}catch(e){}"ready"!=b||k||(k=!0);if(!/^https?:\/\/player.vimeo.com/.test(a.origin))return!1;"*"===h&&(h=a.origin);a=c.value;var m=c.data,f=""===f?null:c.player_id;c=f?d[f][b]:d[b];b=[];if(!c)return!1;void 0!==a&&b.push(a);m&&b.push(m);f&&b.push(f);
return 0<b.length?c.apply(null,b):c.call()}function n(a,c,b){b?(d[b]||(d[b]={}),d[b][a]=c):d[a]=c}var d={},k=!1,h="*";e.fn=e.prototype={element:null,init:function(a){"string"===typeof a&&(a=document.getElementById(a));this.element=a;return this},api:function(a,c){if(!this.element||!a)return!1;var b=this.element,d=""!==b.id?b.id:null,e=c&&c.constructor&&c.call&&c.apply?null:c,f=c&&c.constructor&&c.call&&c.apply?c:null;f&&n(a,f,d);g(a,e,b);return this},addEvent:function(a,c){if(!this.element)return!1;
var b=this.element,d=""!==b.id?b.id:null;n(a,c,d);"ready"!=a?g("addEventListener",a,b):"ready"==a&&k&&c.call(null,d);return this},removeEvent:function(a){if(!this.element)return!1;var c=this.element,b=""!==c.id?c.id:null;a:{if(b&&d[b]){if(!d[b][a]){b=!1;break a}d[b][a]=null}else{if(!d[a]){b=!1;break a}d[a]=null}b=!0}"ready"!=a&&b&&g("removeEventListener",a,c)}};e.fn.init.prototype=e.fn;window.addEventListener?window.addEventListener("message",l,!1):window.attachEvent("onmessage",l);return window.Froogaloop=
window.$f=e}();