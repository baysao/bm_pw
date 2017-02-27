$(function() {
	window.piwikMediaAnalyticsAsyncInit = function() {
		var MA = Piwik.MediaAnalytics;

		function VideojsPlayer(node, mediaType) {
			if (node.hasPlayerInstance) {
				return;
			}

			node.hasPlayerInstance = true;
			var actualResource = MA.element.getAttribute(node, 'src');
			var resource = MA.element.getMediaResource(node, actualResource);
			var tracker = new MA.MediaTracker('videojs', mediaType, resource);

			tracker.setWidth(node.clientWidth);
			tracker.setHeight(node.clientHeight);
			tracker.setFullscreen(MA.element.isFullscreen(node));

			var title = MA.element.getMediaTitle(node);
			tracker.setMediaTitle(title);

			tracker.setMediaTotalLengthInSeconds(node.duration);

			var useCapture = true;

			node.addEventListener('play', function() {
				tracker.play();
			}, useCapture);

			node.addEventListener('pause', function() {
				tracker.pause();
			}, useCapture);

			node.addEventListener('ended', function() {
				tracker.finish();
			}, useCapture);

			node.addEventListener('timeupdate', function() {
				tracker.setMediaProgressInSeconds(node.currentTime);
				tracker.setMediaTotalLengthInSeconds(node.duration);
				tracker.update();

			}, useCapture);

			node.addEventListener('seeking', function() {
				tracker.seekStart();
			}, useCapture);

			node.addEventListener('seeked', function() {
				tracker.setMediaProgressInSeconds(node.currentTime);
				tracker.setMediaTotalLengthInSeconds(node.duration);
				tracker.seekFinish();
			}, useCapture);

			window.addEventListener('resize', function () {
				tracker.setWidth(node.clientWidth);
				tracker.setHeight(node.clientHeight);
				tracker.setFullscreen(MA.element.isFullscreen(node));
			}, useCapture);

			// check is_videojs then do thing

			// find tag video and init multi instance
			var player = videojs('player-01', {}, function() {
				videojs.log('ready!');
			});

            var player_2 = videojs('player-02', {}, function() {});

			player.on('firstplay', function() {
				var firstplay = player.currentTime();
				this.one('playing', function() {
					var t = player.currentTime();
					var startup_delay = t - firstplay;
					_paq.push(['trackEvent', 'MediaVideo', 'start-delay', title, startup_delay]);
					// videojs.log('Startup delay time: '+'('+t+' - '+firstplay+') ==> '+startup_delay);
				})
			});

			var w = 0;
			player.on('waiting', function() {
				w = player.currentTime();
				this.on('playing', function() {
					var p = player.currentTime();
					var buffer_time = p - w;
					if ( buffer_time > 0 ) {
						_paq.push(['trackEvent', 'MediaVideo','rebuffer-time',title, buffer_time]);
						// videojs.log('Buffer time: '+'('+p+' - '+w+') ==> '+buffer_time);
					}
				})
			});

			player.on('error', function() {
				var err = this.error();
				if ( typeof(this.networkState()) !== 'undefined' ) {
					_paq.push(['trackEvent', 'MediaVideo', 'error', title, err.code]);
					// videojs.log(this.networkState());
					// videojs.log(err.code +' - '+err.message);
				} else {

				}
			});

			tracker.trackUpdate();
		}

		VideojsPlayer.scanForMedia = function(v) {
			var lVideos = v.getElementsByTagName('video');
			for (var i = 0; i < lVideos.length; i++) {
				if (!MA.element.isMediaIgnored(lVideos[i])) {
					new VideojsPlayer(lVideos[i], MA.mediaType.VIDEO);
				}
			}
		};

		MA.removePlayer('html5');
		MA.addPlayer('videojs', VideojsPlayer);
	}
});