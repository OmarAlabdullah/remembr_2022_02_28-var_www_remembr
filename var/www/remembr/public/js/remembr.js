// IE (version?) does not understand trim.
if (!String.prototype.trim) {
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, '');
	};
}


angular.module('ngImgLoad', [])
  .directive('ngImgLoad', ['$parse', function ($parse) {
    return {
      link: function (scope, elem, attrs) {
        var fn = $parse(attrs.ngImgLoad);
        elem.on('load', function (event) {
          scope.$apply(function() {
            fn(scope, { $event: event });
          });
        });
      }
    };
  }]
);

var app = angular.module('remembr', ['ngResource', 'ngAnimate', 'wu.masonry', 'ui.router', 'angularFileUpload', 'remembrConfig', 'LocalStorageModule', 'ngImgLoad']);

app.config(function($locationProvider){
	$locationProvider.html5Mode(true).hashPrefix('!');
});

app.run(function($FB){
	window.twttr =
		(function(d,s,id)
		{
			var js;
			var fjs=d.getElementsByTagName(s)[0];
			var t = window.twttr || {};
			if (d.getElementById(id))
				return;
			
			js=d.createElement(s);
			js.id=id;
			js.src="https://platform.twitter.com/widgets.js";
			fjs.parentNode.insertBefore(js,fjs);
			t._e=[];
			t.ready = function(f)
			{
				t._e.push(f);
			};
			return t;
		}(document,"script","twitter-wjs")
		);
});


app.config(['$interpolateProvider', '$compileProvider', function($interpolateProvider, $compileProvider) {
	$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
	$compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|file|javascript):/);
}]);

app.factory('vimeoService', ['$rootScope',  function($rootScope) {
	var vimeoService = {};


	vimeoService.play = function() {
		$('#remembrvid').css('display','block').css('background-position','top center');
		vimeoService.post('play');
	};

	vimeoService.pause = function() {
		vimeoService.post('pause');
	};

	vimeoService.post = function(action, value) {
        var player = $('iframe');
        var url = window.location.protocol + player.attr('src').split('?')[0];
    
		var data = {
			method: action
		};

		if (value) {
			data.value = value;
		}

		var message = JSON.stringify(data);
		player[0].contentWindow.postMessage(message, url);
	};

	return vimeoService;
}]);

app.controller('MemoryDetailCtrl', ['$scope', '$stateParams', '$state', '$timeout', 'Content', 'Comment', 'msgService',
	function($scope, $stateParams, $state, $timeout, Content, Comment, msgService) {
		var m = Content.get({id: $stateParams.id}, function(memory) {
			if (memory.pageurl != $stateParams.page)
			{
				$state.go('root.page', {page: memory.pageurl});
				$timeout(function() {
					$state.go('root.page.fancybox.memory', {page: memory.pageurl, id: memory.id})
				}, 2000);
			}
			else
			{
				$scope.memory = m;
			}
		},
				function(error) {
					$state.go('.^.^');
					msgService.inform("An error occurred retrieving this memory", 'error');
				});

		var comments = Comment.list({id: $stateParams.id}, function() {
			$scope.comments = comments
		});
		$scope.comment = '';
		$scope.create = function() {
			var cmt = new Comment({memory: $scope.memory.id, text: $scope.comment});
			cmt.$create(function(c) {
				$scope.comments.unshift(c);
				$scope.comment = '';
				angular.forEach($scope.$root.page.memories, function(m) {
					if (m.id == $scope.memory.id)
					{
						m.numbercomments++;
					}
				});
			});
		};
	}]);


/**
 * Error handling on httpRequests.
 *
 * generic: use rejection.status like: if (rejection.status === 403) {return $q.reject(rejection);}
 * custom:  check fromState['current']['name'] for state name
 *
 */
app.factory('httpRequestInterceptor', ['$q', '$location', '$injector', '$timeout', '$stateParams', function($q, $location, $injector, $timeout, $stateParams) {

		return {
			'responseError': function(rejection) {
				if (rejection.status === 401)
				{
					var fromState = $injector.get('$state');

					$timeout(function() {
						switch (fromState['current']['name']) {
							case 'root.messages':
								$injector.get('$state').go('root.messages.fancybox.login', $stateParams,
										{location: false});

								break;
							case 'root.page':
							case 'root.page.error':
								$injector.get('$state').go('root.page.fancybox.login', $stateParams,
										{location: false});
								break;
						}
					}, 2000);
				}
				return $q.reject(rejection);
			}
		};
	}]);


app.factory('User', ['$resource', function($resource) {
		return $resource(null, {},
				{
					// dont use responseType: json because IE8 is stupid.
					get: {method: 'GET', url: '/json/user/get'}
				});
	}]);

app.factory('SearchPages', ['$resource', function($resource) {
		return $resource(null, {},
				{
					// dont use responseType: json because IE8 is stupid.
					get: {
						method: 'GET',
						url: '/json/search/get/searchterm/:searchterm',
						params: {searchterm: '@searchterm'}
					},
					getType: {
						method: 'GET',
						url: '/json/search/get/searchterm/:searchterm/searchtype/:searchtype',
						params: {searchterm: '@searchterm', searchtype: '@searchtype'}
					},
					getRecent: {
						method: 'GET',
						url: '/json/search/recent'
					},
					getRandom: {
						method: 'GET',
						url: '/json/search/random'
					},
					getRotators: {
						method: 'GET',
						url: '/json/search/rotators'
					}
				});
	}]);

app.factory('Facebook', ['$resource', function($resource) {
		return $resource(null, {},
				{
					getFriends: {method: 'GET', url: '/json/user/facebook-friends', isArray: true}
				});
	}]);

// social media needs update after adding to account
app.factory('SocialMedia', ['$resource', function($resource) {
		return $resource(null, {},
				{
					get: {method: 'GET', url: '/json/dashboard/social-media'}
				});
	}]);

// shared memories needs update after adding to user
app.factory('SharedMemories', ['$resource', '$rootScope', function($resource, $rootScope) {
		return $resource(null, {},
				{
					get: {method: 'GET', url: '/json/dashboard/get-shared-memories'}
				});
	}]);

app.factory('MessagesInbox', ['$resource', '$rootScope', function($resource, $rootScope) {
		return $resource(null, {},
				{
					get: {method: 'GET', url: '/json/messages/get-inbox'},
					getUnreadMessagesNumber: {method: 'GET', url: '/json/messages/get-unread-messages-number'},
					getNewMessages: {method: 'GET', url: '/json/messages/get-new-messages'}
				});
	}]);

app.factory('Notifications', ['$resource', '$rootScope', function($resource, $rootScope) {
		return $resource(null, {},
				{
					get: {method: 'GET', url: '/json/notifications/get-notifications'},
					//getUnreadNotificationsNumber: {method: 'GET', url: '/json/notifications/get-unread-notifications-number'},
					getNewNotifications: {method: 'GET', url: '/json/notifications/get-new-notifications'},
					getNotificationsHistory: {method: 'GET', url: '/json/notifications/get-notifications-history'}
				});
	}]);

app.factory('MessagesOutbox', ['$resource', '$rootScope', function($resource, $rootScope) {
		return $resource(null, {},
				{
					get: {method: 'GET', url: '/json/messages/get-outbox'},
					setReadDate: {
						method: 'POST',
						url: '/json/messages/set-read-date/id/:id',
						params: {id: '@id'},
						transformResponse: function(data) {
							return data;
						}
					}
				});
	}]);

// usersettings looks like being updated, because checkbox state stays the same
// so we only need to get the data once
app.factory('UserSettings', ['$q', '$http', function($q, $http) {
		var deferred = $q.defer();

		$http.get('/json/dashboard/get-settings').then(function(resp) {
			deferred.resolve(resp.data);
		});

		return deferred.promise;
	}]);


app.factory('Page', ['$resource', '$stateParams', function($resource, $stateParams) {

		var defparams = {
            url: '@url',
            lang: function() {
                return $stateParams.lang || null;
            }
        };

		return $resource(null, {},
				{
					get:    {method: 'GET',  url: '/json/:url', params: {url: '@url'}},
					update: {method: 'POST', url: '/json/:lang/:url/update:part', params: defparams},
					create: {method: 'POST', url: '/json/create-page/create'},
                    updateDraft: {method: 'POST', url: '/json/user/update-draft-page'},
                    deleteDraft: {method: 'GET', url: '/json/user/delete-draft-page'},
                    getDraft: {method: 'GET', url: '/json/user/get-draft-page'},
					invite: {method: 'POST', url: '/json/:url/invite', params: {url: '@url'}},
					requestAccess: {method: 'POST', url: '/json/:lang/:url/request-access', params: defparams},
					grantAccess:   {method: 'POST', url: '/json/:lang/:url/grant-access', params: {url: '@url', params: defparams}},
					urlSuggestions: {isArray: true, method: 'POST', url: '/json/create-page/get-url-suggestions',
						transformResponse: function(data) {
							return angular.fromJson(data).suggestions;
						}
					},
					checkAvailable: {method: 'GET', url: '/json/create-page/check-url-available/url/:url', params: {url: '@url'}}
				});
	}]);

app.factory('Comment', ['$resource', function($resource) {
		return $resource(null, {}, {
			list: {method: 'GET', isArray: true, url: '/json/comment/list/id/:id'},
			create: {method: 'POST', url: '/comment/create'}
		});
	}]);

app.factory('Content', ['$resource', function($resource) {
		return $resource(null, {},
				{
					get: {method: 'GET', url: '/json/content/get/id/:id'},
                    getAnonymous:  {method: 'GET', url: '/json/:url/get-anonymous-condolence/:editkey'},
                    saveAnonymous: {method: 'POST', url: '/json/:url/save-anonymous-condolence/:editkey', isArray: false},
                    deleteAnonymous: {method: 'GET', url: '/json/:url/delete-anonymous-condolence/:editkey', isArray: false},
					save: {method: 'POST', url: '/json/:lang/:url/content/save'},
//				update	: { method : 'POST', url : '/json/content/update/:id' },
					list: {method: 'GET', isArray: true, url: '/json/:url/content/index/:max/:from/:to', transformResponse: function(data) {
							return angular.fromJson(data).memories;
						}} // params : {url : '@url', max: '@max'}  },
//				remove	: { method : 'DELETE', url : '/json/content/remove/:id' }
				});
	}]);

// translator service
app.factory('translatorService', ['$rootScope', '$stateParams', '$http', '$locale', function($rootScope, $stateParams, $http, $locale) {
		var translatorService = {};

		angular.forEach(['nl', 'nl-be', 'en'], function(lang) {
			$http.get('/language/' + lang + '/l10n.js')
					.then(function(res) {
				translatorService[lang] = res.data;
			});
		});

		var getlang = function($stateParams, $rootScope) {
			/* precedence is: url, user-preference, browser, default=en */
			return ($stateParams.lang || $rootScope.$eval('user.language') || navigator.language || navigator.userLanguage || 'en').substring(0, 2);
		}.bind(undefined, $stateParams, $rootScope);

        translatorService.getLang = getlang;

		var lang = getlang();
		$rootScope.$watch(getlang, function(val) {
			lang = val;
		});

		/*
		 * @TODO use translationservice for doing all translations
		 */

		translatorService.translate = function(key) {
			
            lang = getlang();
			if (translatorService[lang] && translatorService[lang][key])
			{
				return translatorService[lang][key];
			}
			return key;
		};


		return translatorService;
	}]);


app.factory('msgTabService', ['$rootScope', 'MessagesInbox', 'MessagesOutbox', function($rootScope, MessagesInbox, MessagesOutbox) {
		var msgTabService = {};
		var tab = "inbox";
		var newto = "";

		msgTabService.set = function(value) {
			tab = value;
		};

		msgTabService.get = function() {
			return tab;
		};

		msgTabService.openTab = function(value) {
			if ($rootScope.currenttab !== value)
			{
				$rootScope.currenttab = value;
				msgTabService.set(value);
			}
		};

		msgTabService.setNewto = function(value) {
			newto = value;
		};

		msgTabService.getNewto = function() {
			return newto;
		};

		return msgTabService;

	}]);



app.factory('pollingService', ['$timeout', 'MessagesInbox', '$rootScope', 'msgTabService', '$state', 'Notifications', 'POLLING_INTERVAL',
	function($timeout, MessagesInbox, $rootScope, msgTabService, $state, Notifications, POLLING_INTERVAL) {
		var pollingService = {};
		var running = false;
		var timer;

		pollingService.start = function() {
			var self = this;
			if (!running) {
				running = true;
				self.run();
			}
		};

		pollingService.stop = function() {
			running = false;
			$timeout.cancel(timer);
		};

		pollingService.run = function() {
			// messages
			if (angular.isObject($rootScope.inbox)) {
				MessagesInbox.getNewMessages({
				}, function(result) {
					/* add new messages to existing message-queue */
                    for (index = 0; index < result.messages.length; ++index) {
                        $rootScope.inbox.messages = 
                            $rootScope.inbox.messages.filter(
                                function(new_id, existing_message) {
                                    return existing_message.id !== new_id;
                                }.bind(null, result.messages[index].id)
                            );
                        $rootScope.inbox.messages.push(
                            result.messages[index]);
                        console.log(result.messages[index]);
					}
				});
			}

			MessagesInbox.getUnreadMessagesNumber({
			}, function(model) {
				$rootScope.newmessagescounter = model.number;
			});

			Notifications.getNewNotifications({
			}, function(result) {
				if (result.notifications.length > 0) {
					/* add new messages to existing notification-queue */
					for (index = 0; index < result.notifications.length; ++index) {
						$rootScope.notifications.notifications.push(result.notifications[index]);
					}
				}
			});

			timer = $timeout(pollingService.run, POLLING_INTERVAL);
		};

		return pollingService;

	}]);

app.factory('logoutService', ['$rootScope', 'User', '$http', '$state', 'pollingService', function($rootScope, User, $http, $state, pollingService) {
		var logoutService = {};

		logoutService.logout = function() {
			var logout_url = '/provider/logout-provider/default';

			$http({
				url: logout_url,
				method: "POST"
			}).success(function(data, status, headers, config) {
				if (data === 'done')
				{
					// stop polling service
					pollingService.stop();
					// we stay on the same page except for these pages
					var pages = ['root.dashboard', 'root.usersettings', 'root.landingpage', 'root.password', 'root.usersettings.email', 'root.newmsg', 'root.messages', 'root.notifications', 'root.usersettings.password'];
					if (pages.indexOf($state.current.name) !== -1) {
						$state.transitionTo('root.home');
					}
					else // reload current page so new rights are in effect.
					{
						$state.go($state.current.name, {}, {reload: true});
					}

					$rootScope.$root.user = User.get();
				}
				else
				{
					// @TODO: check? reload login html?
				}
			}).error(function(data, status, headers, config) {
				$rootScope.status = status;
			});
		};

		return logoutService;

	}]);

// cookies (angular cookieStore is not persistent)
function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	var expires = "expires=" + d.toGMTString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i].trim();
		if (c.indexOf(name) === 0)
			return c.substring(name.length, c.length);
	}
	return "";
}

app.controller('commentsCtrl', ['$scope', '$http', 'msgService', function($scope, $http, msgService) {

		$scope.deletecmt = function(comment) {
			msgService.inform("Are you sure?", "confirm", function(confirm) {

				if (confirm)
				{
					var formdata = {
						'id': comment.id
					};

					$http({
						url: '/comment/delete-comment',
						data: formdata,
						method: "POST"
					}).success(function(result, status, headers, config) {
						switch (result) {
							case 'ok':
								var index = $scope.comments.indexOf(comment);
								$scope.comments.splice(index, 1);
								msgService.inform("The comment is deleted.", "success");
								break;
							case 'error' :
								msgService.inform("Sorry, something went wrong.", "warning");
								break;
						}
					}).error(function(data, status, headers, config) {
						$scope.status = status;
						msgService.inform("Sorry, something went wrong.", "warning");
					});
				}
			});
		};
	}]);

app.controller('cookieCtrl', ['$scope', 'msgService', function($scope, msgService) {

		var acceptCookies = getCookie("acceptCookies");

		if (!acceptCookies) {
			$scope.showCookieMsg = true;
		}

		$scope.removeCookieMsg = function() {
			setCookie('acceptCookies', 'true', 20 * 365);
			$scope.showCookieMsg = false;
		};

		// http://tanalin.com/en/articles/ie-version-js/
		// check for IE9 or older
		if (document.all && !window.atob) {
			msgService.inform("Your internet browser is outdated en is not supported by Remembr. For a better experience we recommend you to update your browser to a newer version.", "error");
		}
	}]);

// message centre
app.controller('messagesCtrl', ['$scope', '$http', 'msgService', '$state', '$rootScope', 'Page',
	'MessagesInbox', 'MessagesOutbox', '$timeout', '$filter', 'msgTabService', 'orderByFilter',
	function($scope, $http, msgService, $state, $rootScope, Page,
			MessagesInbox, MessagesOutbox, $timeout, $filter, msgTabService, orderByFilter) {

		$scope.openTab = function(tab)
		{
			msgTabService.openTab(tab);
		};

		// init inbox
		$scope.openTab(msgTabService.get());

		if (msgTabService.get() === 'new') {
			$scope.newto = msgTabService.getNewto();
		}

		$scope.$watch('user', function(newValue, oldValue) {
			if (newValue === oldValue) {
				return;
			}
			$scope.updated++;
		});

		// show first message
		$scope.openInboxFirst = function() {
			$rootScope.inbox.messages = orderByFilter($rootScope.inbox.messages, 'senddate', 'reverse');
			msgTabService.openTab('inbox');
			if ($scope.inbox.messages.length)
			{
				markAsRead($scope.inbox.messages[0]);
				$state.go('root.messages', {'msgid': $scope.inbox.messages[0].id});
			}
			else
			{
				$state.go('root.messages');
			}
		};

		// set inbox message from header
		$scope.openInbox = function(msg) {
			msgTabService.openTab('inbox');
			markAsRead(msg);
			$state.go('root.messages', {'msgid': msg.id});
		};

		$scope.mailUser = function(user) {
			msgTabService.setNewto(user);
			msgTabService.openTab('new');
			$state.go('root.newmsg');
		};

		$scope.setSelected = function(msg) {
			$scope.selected = msg.id;
			markAsRead(msg);
		};

		markAsRead = function(msg) {
			if (typeof msg !== 'undefined' && msg.readdate === '')
			{
				msg.newmsg = false;
				msg.readdate = MessagesOutbox.setReadDate({'id': msg.id});
				$rootScope.newmessagescounter--;
                $rootScope.inbox.messages.forEach(
                    function(new_id, msg) {
                        if (msg.id === new_id)
                            msg.new=false;
                    }.bind(null, msg.id)
                );
			}
		};

		$scope.deleteMsg = function(msg, box) {
			var formdata = {
				'id': msg.id,
				'box': box
			};
			$http({
				url: '/json/messages/delete',
				data: formdata,
				method: "POST"
			}).success(function(result, status, headers, config) {
				switch (result) {
					case 'done':
						msg.deleted = true;
						switch (box) {
							case 'in':
								// update messages
								var index = $scope.inbox.messages.indexOf(msg);
								$scope.inbox.messages.splice(index, 1);
								break;
							case 'out':
								// update messages
								var index = $scope.outbox.messages.indexOf(msg);
								$scope.outbox.messages.splice(index, 1);
								break;
						}

						break;
					default:
						msgService.inform("We could not delete this e-mail.", "error");
						break;
				}
			}).error(function(data, status, headers, config) {
				msgService.inform("We could not delete this e-mail.", "error");
				$scope.status = status;
			});
		};

		$scope.replyMsg = function(msg) {
			// do not edit original message which is still in inbox
			$scope.reply = angular.copy(msg);
			$scope.reply.title = 'Re: ' + $scope.reply.title;
			$scope.reply.content = '<br />--------------------------------------------------<br />' + $scope.reply.content;
			msgTabService.openTab('reply');
		};

		$scope.cancelReplyForm = function() {
			msgTabService.openTab('inbox');
		};


		$scope.submitNewMsgForm = function() {
			var formdata = {
				'id': $scope.newto.id,
				'title': $scope.newto.title,
				'content': $scope.newto.content
			};

			$http({
				url: '/json/messages/new',
				data: formdata,
				method: "POST"
			}).success(function(result, status, headers, config) {
				/* Any failure should be an error, so if we're here things went well.*/
				msgService.inform("Your message has been sent.", "info");
				// reload outbox
				$rootScope.outbox = MessagesOutbox.get();
				msgTabService.openTab('inbox');
			}).error(function(data, status, headers, config) {
				msgService.inform("We could not send your message.", "error");
				msgTabService.openTab('inbox');
				$scope.status = status;
			});
		};

		$scope.submitReplyForm = function() {
			var formdata = {
				'id': $scope.reply.id,
				'message_id': $scope.reply.message_id,
				'title': $scope.reply.title,
				'content': $scope.reply.content
			};

			$http({
				url: '/json/messages/reply',
				data: formdata,
				method: "POST"
			}).success(function(result, status, headers, config) {
				switch (result) {
					case 'done':
						msgService.inform("Your message has been sent.", "info");
						// reload outbox
						$rootScope.outbox = MessagesOutbox.get();
						msgTabService.openTab('inbox');
						break;
					default:
						msgService.inform("We could not send this message.", "error");
						msgTabService.openTab('inbox');
						break;
				}
			}).error(function(data, status, headers, config) {
				$scope.status = status;
			});
		};

		$scope.grantaccess = function(msg)
		{
			Page.grantAccess({url: msg.extra.pageurl}, {msgid: msg.message_id},
			function(resp) {
				msg.extra.granted = true;
				msgService.inform('The invation has been granted', 'info');
			}, function(resp) {
				msgService.inform('An error occured trying to grant the invitation', 'error');
				console.log(resp);
			}
			);

		}
	}]);

app.controller('sharedMemoriesCtrl', ['$scope', '$timeout', '$state', function($scope, $timeout, $state) {
		$scope.popMemory = function(memory, popup) {

			if (popup) {
				// We have to wait to load the page first and then show the memory: not sure if this can be done a better way?
				$timeout(function() {
					$state.go('root.page.fancybox.memory', {'id': memory.id});
				}, 2000);
			}
		};
	}]);

/*! bzSlider v0.1.0 by Vitalii Savchuk(esvit666@gmail.com) - https://github.com/esvit/bz-slider - New BSD License */
var sliderController = ['$scope', '$timeout', '$parse', '$element', function ($scope, $timeout, $parse, $element) {
    var timeOut = null;

    $scope.slides = $scope.slides || $scope.$root.slides || [];
    $scope.delay = $scope.delay || 5000;
    $scope.$play = false;
    $scope.$slideIndex = 0;
    $scope.slides_found = null;
    
    $scope.html_slides = $('.slide', $element);
    
    $scope.refresh = function() {
        $scope.html_slides.each(function(i, slide) {
            if (i !== $scope.$slideIndex)
                $(slide).addClass('ng-hide');
            else
                
                $(slide).removeClass('ng-hide');
        });
    };
    
    if ($scope.html_slides.length > 0) {
        $scope.slides_found = $scope.html_slides.length;
        $scope.refresh();
    }
    
    $scope.length = function() {
        return $scope.slides_found || $scope.slides.pages.length;
    };

    $scope.play = function() {
        timeOut = $timeout(function() {
            $scope.next();
            $scope.play();
        }, $scope.delay);
        $scope.$play = true;
    };

    $scope.stop = function() {
        $timeout.cancel(timeOut);
        timeOut = null;
        $scope.$play = false;
    };

    $scope.next = function() {
        var total = $scope.length();
        if (total > 0) {
            $scope.$slideIndex = ($scope.$slideIndex === total - 1) ? 0 : $scope.$slideIndex + 1;
        }
        $scope.refresh();
    };

    $scope.prev = function() {
        var total = $scope.length();
        if (total > 0) {
            $scope.$slideIndex = ($scope.$slideIndex === 0) ? total - 1 : $scope.$slideIndex - 1;
        }
        $scope.refresh();
    };

    $scope.setIndex = function(index) {
        $scope.$slideIndex = index;
        $scope.refresh();
    };
}];

app.controller('notificationsCtrl', ['$http', '$scope', '$state', '$timeout', '$rootScope', 'Notifications',
	function($http, $scope, $state, $timeout, $rootScope, Notifications) {

		/**
		 * @param {obj} notification
		 * @param {boolean} true: show popup with comment or memory, false: display page only
		 */
		$scope.readNotification = function(notification, popup) {
			var formdata = {
				'id': notification.id
			};

			if (popup) {
				// We have to wait to load the page first and then show the memory: not sure if this can be done a better way?
				//HW: Going to the root.page.fancybox.memory state directly should work. (Works for me!)
				//HW add.: Huh, apparently it only works when you're in a root.page state! Otherwise I get exiting.locals is null (might have to do with resolves)
				$timeout(function() {
					$state.go('root.page.fancybox.memory', {'id': notification.memory_id});
				}, 2000);
			}

			// Ok, notification has been read
			$http({
				url: '/json/notifications/set-read',
				data: formdata,
				method: "POST"
			}).success(function(result, status, headers, config) {
				// update notifications
				var index = $scope.notifications.notifications.indexOf(notification);
				$scope.notifications.notifications.splice(index, 1);
				$scope.newnotificationscounter--;
			}).error(function(data, status, headers, config) {
				$scope.status = status;
			});
		};


		$scope.markNotifications = function() {
			$http({
				url: '/json/notifications/set-read-all',
				method: "POST"
			}).success(function(result, status, headers, config) {
				// update notifications
				$rootScope.notifications = Notifications.get();
				//$scope.notifications = null;
				//$scope.newnotificationscounter = 0;
			}).error(function(data, status, headers, config) {
				$scope.status = status;
			});
		};

	}]);

app.controller('CreateContentCtrl', ['$scope', 'Content', '$upload', 'imagePreview', 'msgService', '$state', 'uploadService', 'translatorService',
	function($scope, Content, $upload, imagePreview, msgService, $state, uploadService, translatorService) {

		$scope.newContent = {labels: {}, type: 'memory', anonymousEmail: undefined, lang: translatorService.getLang()};
		$scope.currenttab = 'memory';
		$scope.currentform = 'memory';
        
        $scope.openTab = function($scope, tab)
		{
			if ($scope.currenttab !== tab)
			{
				$scope.currenttab = tab;
				$scope.currentform = tab;
				$scope.newContent.type = tab;
				$scope.showAll(tab);
			}
		}.bind(undefined, $scope);
        
        if ($scope.$root != $scope)
            $scope.$root.openTab = $scope.openTab;

		$scope.canocialUrl = function() {
			return 'https://remembr.com/' + $scope.page.url;
		};

		$scope.canocialImageUrl = function() {
			return 'https://remembr.com/minify?files=' + $scope.page.photo + '&resize=w[640]h[640]e[true]';
		};

		$scope.openForm = function(form)
		{
			if (form != 'condolence' && !$scope.user.loggedin)
			{
				//msgService.inform("Please log in to add memories to this page", 'error');
				$state.go('root.page.fancybox.login');
			}
			else if ($scope.currentform !== form)
			{
				$scope.currentform = form;
				$scope.newContent.type = form;
			}
		};

		$scope.submit = function()
		{
			if (($scope.newContent.type === 'media' || $scope.newContent.type === 'photo') && !$scope.selectedFile)
			{
				msgService.inform("Please select an image.", 'warning');
				return;
			}
			if (!$scope.user.loggedin && !$scope.newContent.username)
			{
				msgService.inform("Please enter a username.", 'warning');
				return;
			}
            form_email = $('.condolance form[name=content] input[name=email]').val();
            form_email = form_email !== '' ? form_email : undefined;
            
			if (!$scope.user.loggedin && !$scope.newContent.anonymousEmail || $scope.newContent.anonymousEmail === '')
			{
                var form_email = $('.condolance form[name=content] input[name=email]').val();
                if (form_email === '' || form_email === undefined)
                    msgService.inform("Please enter your email address so you can edit or remove your condolence message.", 'warning');
                else
                    msgService.inform("Please enter a valid email address.", 'warning');
				return;
            }
            
            $scope.ui.active = 'body';
            $scope.ui.readmore = false;
            $scope.saving = true;

            var callback = function() {
                Content.save({url: $scope.page.url, lang: translatorService.getLang()}, $scope.newContent,
                        function(data) {
                            $scope.saving = false;
                            $scope.page.memories.unshift(data); // @TODO check data is valid memory/content
                            $scope.newContent = {labels: {}, type: $scope.newContent.type, anonymousEmail: undefined, lang: translatorService.getLang()};
                            $scope.dataUrl = '';
                            $scope.selectedFile = null;
                        }, function(data) {
                    $scope.saving = false;
                });
            };
            $scope.startupload(callback);     
		};

		$scope.onFileSelect = function($files) {
			if (document.documentElement.scrollWidth < 640)
			{
				window.location.hash = "";
				window.location.hash = "mediaupload";
			}
			
			uploadService.process($files, $scope, false);
		};

		$scope.startupload = function(callback) {
			if (!$scope.selectedFile || ($scope.newContent.type !== 'media' && $scope.newContent.type !== 'photo'))
			{
				callback();
				return;
			}
			$scope.newContent.type = 'photo'; /* @TODO check its not video */

			$scope.progress = 0;
			$scope.upload = $upload.upload({
				url: '/json/' + $scope.page.url + '/file-upload',
				method: 'POST',
				file: $scope.selectedFile,
				fileFormDataName: 'file'
			}).success(function(response) {
				$scope.newContent.photoid = response.photo;
				if (angular.isFunction(callback))
					callback();
			}).error(function(response) {
				msgService.inform("There was an error while uploading the image.", 'error');
				$scope.saving = false;
			}).progress(function(evt) {
				$scope.progress = parseInt(Math.min(100.0, 100.0 * evt.loaded / evt.total));
			});
		};

	}]);

app.controller('mainCtrl', ['$scope', '$window', '$location', 'translatorService', 'Page', 'User','$injector',
	function($scope, $window, $location, translatorService, Page, User,$injector) {
		$scope.$root.ui = {active: 'body', 'readmore': false};

		/**
		 * register statechanges with google analytics
		 */
		$scope.$root.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
			if ($window.ga)
			{
				$window.ga('send', 'pageview', {page: $location.path()});
			}
			if (toState.title)
			{
				var title = angular.isArray(toState.title) ? $injector.invoke(toState.title) : toState.title;
				document.title = title;
			}
//			$scope.$root.transitioning = false;
//			console.log('end transition:', fromState.name, fromParams, toState.name, toParams, $scope.$root.transitioning);
		});
//		$scope.$root.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams){
//			$scope.$root.transitioning = true;
//			console.log('start transition:', fromState.name,fromParams, toState.name, toParams, $scope.$root.transitioning);
//		});
//		$scope.$root.$on('$viewContentLoading', function(event, toState, toParams, fromState, fromParams){
//			$scope.$root.loading = true;
//			console.log('content loading :', event, toState, toParams, fromState, fromParams);
//		});
//		$scope.$root.$on('$viewContentLoaded', function(event, toState, toParams, fromState, fromParams){
//			$scope.$root.loading = false;
//			console.log('content loaded :', event, toState, toParams, fromState, fromParams);
//		});

		// $stateNotFound
		// $stateChangeError

		$scope.$root.getUsername = function(user, deflt)
		{
			if (!user)
			{
				return deflt;
			}
			else if (user.firstname && user.lastname)
			{
				return user.firstname + ' ' + user.lastname;
			}
			else
			{
				return user.firstname || user.lastname || deflt;
			}
		};

		$scope.$root.translate = function(key)
		{
			return translatorService.translate(key);
		};

		$scope.$root.imgresize = function(file, width, height)
		{
            console.log(file);
            if (!file)
			{
				return file;
			}
			if (file.match(/^(?:https?:)?\/\//))
			{
				return file;
			}
			return '/minify?files=' + file + '&resize=w[' + width + ']h[' + height + ']f[true]';
		};
                
                $scope.$root.logvar = function(x) {
                    console.log(x);
                    return '';
                };
                
		$scope.$root.imgcrop = function(file, roi, width, height)
		{
            
            if (!file)
			{
				return file;
			}
			if (file.match(/^(?:https?:)?\/\//))
			{
				return file;
			}
                        if (roi === undefined || roi === null || roi.x === null || roi.y === null || roi.width === null || roi.height === null)
                        {
                            return '/minify?files=' + file + '&resize=w[' + width + ']h[' + height + ']f[true]';
                        }
			return '/minify?files=' + file + '&crop=width[' + roi.width + ']height[' + roi.height + ']x[' + roi.x + ']y[' + roi.y + ']&resize=w[' + width + ']h[' + height + ']';
		};

	}]);

app.animation('.slide', function() {
	return {
		enter: function(element, done) {
			element.hide().slideDown('fast', done);
		},
		leave: function(element, done) {
			element.slideUp('fast', done);
		}
	};
});


app.factory('imagePreview', ['$timeout', function($timeout) {
		if (!window.FileReader)
		{
			return function() {
			};
		}
                
		return function(file, callback) {
			if (file.type.indexOf('image') === -1) {
				return;
			}

			var fileReader = new FileReader();

			fileReader.onload = function(e) {
				$timeout(function() {
					callback(e.target.result);
				});
			};

			fileReader.readAsDataURL(file);
		};
	}]);

app.filter('encodeURIComponent', function() {
    return window.encodeURIComponent;
});

function stringifyCompare(x, y) {
    return JSON.stringify(x) === JSON.stringify(y);
}

app.filter('unsafe', function($sce) { return $sce.trustAsHtml; });

app.filter('dateforlang', ['translatorService', function(translatorService) {
    return function(date, format) {
        if(typeof(date) === 'string') {
            var values = date.split("-").map(function(x) { return parseInt(x); });
            date = new Date(values[0], values[1]-1, values[2]);
        } else if (!date) {
            date = new Date();
        }
        var lang = translatorService.getLang();
        if (lang === "nl-be")
            lang = "nl";
        return moment(date).lang(lang).format(format[lang]);
    };
}]);

app.filter('unsafe', function($sce) {
	return function(val) {
		return $sce.trustAsHtml(val);
	};
});

app.filter('htmlToPlaintext', function() {
	return function(text) {
		return String(text).replace(/<[^>]+>/gm, '').replace('&nbsp;', ' ');
	};
});

app.filter('bytypeandlabel', function() {

	var checktype = function(stypes, vtype)
	{
		return stypes[vtype] === true;
	}

	var checklabels = function(slabels, vlabels)
	{
		for (var i = 0; i < vlabels.length; i++)
		{
			if (slabels[vlabels[i].id] === true) {
				return true;
			}
		}
		return false;
	}

	return function(memories, types, labels) {
		var items = {
			types: types || {},
			labels: labels || {},
			anylabel: true,
			out: []
		};

		angular.forEach(items.labels, function(value) {
			this.anylabel &= !value;
		}, items);

		angular.forEach(memories, function(value, key) {
			if (checktype(this.types, value.type) &&
					(this.anylabel || checklabels(this.labels, value.labels)))
			{
				this.out.push(value);
			}
		}, items);
		return items.out;
	};
});

app.filter('dateplaceholder', ['$rootScope', '$stateParams', function($scope, $stateParams)
	{
		return function(dateformat) {
			if (typeof dateformat !== "undefined") {
				dateformat = dateformat.toUpperCase().replace('YY', 'YYYY');
				if (($scope.user.lang || $stateParams.lang) === 'nl')
				{
					dateformat = dateformat.replace('YYYY', 'JJJJ');
				}
				return dateformat;
			}
		};
	}]);

app.filter('dpToNg', function()
{
	return function(dateformat) {
		if (typeof dateformat !== "undefined") {
			return dateformat.replace('mm', 'MM').replace('yy', 'yyyy');
		}
	};
});


app.filter('startFrom', function() {
	return function(input, start) {
		if (typeof input !== 'undefined') {
			start = +start; //parse to int
			return input.slice(start);
		}
	};
});

/*
 * groupBy
 *
 * Define when a group break occurs in a list of items
 *
 * @param {array}  the list of items
 * @param {String} then name of the field in the item from the list to group by
 * @returns {array} the list of items with an added field name named with "_new"
 *                  appended to the group by field name
 *
 * @example     <div ng-repeat="item in MyList  | groupBy:'groupfield'" >
 *              <h2 ng-if="item.groupfield_CHANGED">{{item.groupfield}}</h2>
 *
 *              Typically you'll want to include Angular's orderBy filter first
 */

app.filter('groupBy', function() {
	return function(list, group_by) {

		var filtered = [];
		var prev_item = null;
		var group_changed = false;
		// this is a new field which is added to each item where we append "_CHANGED"
		// to indicate a field change in the list
		var new_field = group_by + '_CHANGED';

		// loop through each item in the list
		angular.forEach(list, function(item) {

			group_changed = false;

			// if not the first item
			if (prev_item !== null) {

				// check if the group by field changed
				if (prev_item[group_by] !== item[group_by]) {
					group_changed = true;
				}

				// otherwise we have the first item in the list which is new
			} else {
				group_changed = true;
			}

			// if the group changed, then add a new field to the item
			// to indicate this
			if (group_changed) {
				item[new_field] = true;
			} else {
				item[new_field] = false;
			}

			filtered.push(item);
			prev_item = item;

		});

		return filtered;
	};
});

/* @TODO replace in due time with a more complete uploader -- preferably with polyfill/shim for filereader as well (for preview) */
app.controller('UploadCtrl', ['$scope', '$upload', 'imagePreview', function($scope, $upload, imagePreview) {
		$scope.onFileSelect = function($files) {
			$scope.progress = -1;
			if ($scope.upload) {
				$scope.upload.abort();
			}
			$scope.upload = null;

			$scope.uploadResult = null;
			$scope.dataUrl = '';
			if ($files.length)
			{
				imagePreview($files[0], function(url) {
					$scope.dataUrl = url;
				});
				$scope.progress = -1;

				$scope.selectedFile = $files[0];
				$scope.start();
			}
		};

		$scope.start = function() {
			$scope.progress = 0;
			$scope.upload = $upload.upload({
				url: '/json/' + $scope.page.url + '/file-upload',
				method: 'POST',
				file: $scope.selectedFile,
				fileFormDataName: 'file'
			}).success(function(response) {
				$scope.uploadResult = response.data;
			}).error(function(response) {
				$scope.uploadResult = response.data;
			}).progress(function(evt) {
				$scope.progress = parseInt(Math.min(100.0, 100.0 * evt.loaded / evt.total));
			});
		};
	}]);

/**
 * Message service
 */
app.factory('msgService', ['$rootScope', '$http', function($rootScope, $http) {
		var messages = [];
		var msgService = {};
		var translationcache = {};
		var conf;
		var cb_function;
		var l10n;

		/*
		 *
		 * @TODO use a watch on the user language, so language is automagically updated.
		 *
		 */

		// get translations
		msgService.setTranslation = function(lang) {
			var self = this;
			if (lang !== '' && lang !== null) {
				self.loadLanguage(lang);
			}
			else
			{
				$http.get('/ajax/grab-language').then(function(res) {
					lang = res['data'];
					self.loadLanguage(lang);
				});
			}
		};

		msgService.loadLanguage = function(lang)
		{
			if (translationcache[lang])
			{
				l10n = translationcache[lang];
			}
			else
			{
				$http.get('/language/' + lang + '/l10n.js')
						.then(function(res) {
					translationcache[lang] = l10n = res.data;
				});
			}
		};

		// Display only one messsage (no overlapping messages) to prevent confusion.
		msgService.inform = function(msg, type, callback, links) {
			if (links == undefined) links=[];
			
			conf = false;
			if (typeof callback !== "undefined") {
				cb_function = callback;
			}

			msgService.hideAll();

			/* translation is now done in the template via the translation service
			 * * @TODO clean up this service.
			 * */
			// Get right translation. Even if no one is logged in. Please do not remove.
			if (angular.isObject(l10n)) {
				infomsg = l10n[msg] ? l10n[msg] : msg;
			}
			else
			{
				infomsg = msg;
			}
			
			messages.push({
				msg: infomsg,
				type: type,
				conf: type === "confirm",
				links: links
			});

			if (!$rootScope.$$phase) {
				$rootScope.$apply();
			}
		};

		msgService.confirm = function(info, userinput) {
			this.remove(info);
			cb_function(userinput);
		};

		msgService.allInfos = function() {
			return messages;
		};

		msgService.hideAll = function() {
			messages = [];
		};

		msgService.remove = function(info) {
			// indexOf does not work in IE
			if (idx = $.inArray(info, messages) != -1) {
				messages.splice(messages[idx], 1);
			}
		};

		return msgService;
	}]);


app.factory('dashboardVisibility', ['$rootScope', function($rootScope) {
		return {
			set: function(bool) {
				$rootScope.user.$get(function() {
					$rootScope.editdashboard = bool;
					// show dashboard in edit mode if (one of) these fields are not valid @TODO: i think this can be done better
					var checkuserdata =
							!/^(\d{4})\-(\d{2})\-(\d{2})$/.test($rootScope.user.dateofbirth) ||
							!/^[a-zA-Z\u0080-\u024F\s\/\-\)\(\`\.,\"\']+$/.test($rootScope.user.residence) ||
							!/^[a-zA-Z\u0080-\u024F\s\/\-\)\(\`\.,\"\']+$/.test($rootScope.user.country) ||
							$rootScope.user.gender === '' ||
							$rootScope.user.language === '' ||
							$rootScope.user.dateofbirth === null ||
							$rootScope.user.residence === null ||
							$rootScope.user.country === null ||
							$rootScope.user.gender === null ||
							$rootScope.user.language === null
							;
					if (checkuserdata) {
						$rootScope.editdashboard = true;
					}
				});

			}
		};
	}]);

/**
 * controller: searchController
 */
app.controller('searchCtrl', ['$scope', 'msgService', '$state', '$http',
	function($scope, msgService, $state, $http) {

		$scope.submitPageSearchForm = function() {

			if (angular.isObject($scope.search) && $scope.search.searchterm.length > 0) {

				$state.go('root.search', {'searchterm': $scope.search.searchterm, search:'search'});
				// reset form after submit
				$scope.pageSearchForm.$setPristine();
				$scope.search.searchterm = '';
			}
			else
			{
				//msgService.inform("Please provide a search term.", "warning");
				$state.go('root.search', {'searchterm': '', search:'search'});
			}
		};

		$scope.submitExtendedSearch = function() {
			if (typeof $scope.search !== 'undefined')
			{
				var formdata = {
					'firstname': $scope.search.firstname,
					'lastname': $scope.search.lastname,
					'dateofbirth': $scope.search.yearofbirth,
					'dateofdeath': $scope.search.yearofdeath,
					'open': $scope.search.open,
					'private': $scope.search.prive,
					'type' : $scope.search.type,
					'residence' : $scope.search.residence,
					'country' : $scope.search.country,
					'gender' : $scope.search.gender
				};
			}
			else
			{
				var formdata = {
					'type' : 'all'
				};
			}

			$http({
				url: '/json/search/extended',
				data: formdata,
				method: "POST"
			}).success(function(result, status, headers, config) {
				$scope.searchpages.pages = result;
			}).error(function(data, status, headers, config) {
				$scope.status = status;
			});

		};

		$scope.open = function(page, $event)
		{
			if (!page.private || $.inArray(page.url, $scope.user.pageaccess) > -1)
			{
				$state.go('root.page', {page: page.url}); // {relative: $state.$current}
			}
			else
			{
				$state.go('root.search.fancybox.private', {page: page.url});
			}
		};

		$scope.resetFilters = function()
		{
			$scope.search = null;
		};

	}]);

app.controller('footerCtrl', ['$scope', '$http', 'msgService', '$stateParams', function($scope, $http, msgService, $stateParams) {

		// scroll to top on footer links
		$scope.totop = function() {
			$("html, body").animate({scrollTop: 0}, 200);
		};

		$scope.submitNewsletterForm = function(isValid) {
			if (isValid)
			{
				var formdata = {
					'email': $scope.newsletter.email,
					'lang': $stateParams.lang
				};

				$http({
					url: ($stateParams.lang ? '/' + $stateParams.lang : '') + '/ajax/sign-newsletter',
					data: formdata,
					method: "POST"
				}).success(function(result, status, headers, config) {
					switch (result) {
						case 'done':
							msgService.inform("Thank you for subscribing to our newsletter. Please check your e-mail to confirm your subscription.", "info");
							break;
						case 'exists' :
							msgService.inform("This e-mail address is already registered.", "warning");
							break;
						default:
							msgService.inform("We could not add your e-mail address.", "error");
							//msgService.hideAll();
							break;
					}
				}).error(function(data, status, headers, config) {
					$scope.status = status;
				});
				$scope.newsletterForm.$setPristine();
				$scope.newsletter.email = '';
			}
		};
	}]);



/**
 * controller: managementCtrl
 *
 * Used when an user is logged in.
 *
 * - logout
 * -
 */
app.controller('managementCtrl',
		['$scope', 'logoutService', function($scope, logoutService) {

				$scope.someSelected = function(object) {
					return Object.keys(object).some(function(key) {
						return object[key];
					});
				};

				// logout user
				$scope.sign_out = function($event) {
					logoutService.logout();
				};

				$scope.management = function($event) {
					$event.preventDefault();
				};

			}]);

/**
 * uploadService
 *
 */
app.factory('uploadService', ['msgService', 'imagePreview', function(msgService, imagePreview) {
		var uploadService = {};
		var mainCanvas;
		var decodedData;

		uploadService.size = function($file) {
			if ($file['size'] < 5242880) {
				return true;
			}

			msgService.inform("Sorry, this file is to big, it must be smaller then 5Mb", "error");
			return false;
		};

		/**
		 *
		 * @param {array} $files                    The file(s) to handle
		 * @param {type} $scope			The $scope
		 * @param {bool} startUpload	Do we have to start the upload directly
		 */
		uploadService.process = function($files, $scope, startUpload) {
			$scope.progress = -1;
			if ($scope.upload) {
				$scope.upload.abort();
			}
			$scope.upload = null;
			$scope.uploadResult = null;
			$scope.dataUrl = '';
			if ($files.length)
			{
				if (uploadService.size($files[0]))
				{
					imagePreview($files[0], function(url) {
						$scope.dataUrl = url;
					});
					$scope.progress = -1;

					$scope.selectedFile = $files[0];
					if (startUpload) {
						$scope.startupload();
					}
				}
				else
				{
					$scope.file = '';
				}
			}
		};

		return uploadService;

	}]);


/**
 * controller: dashboardCtrl
 *
 * - edit profile
 * - edit password
 */
app.controller('dashboardCtrl',
		['$scope', 'User', '$http', 'msgService', '$state',
			'dashboardVisibility', '$rootScope', 'SocialMedia', 'imagePreview', '$upload', 'MessagesInbox', 'MessagesOutbox', 'logoutService', 'uploadService', function($scope, User, $http, msgService, $state,
					dashboardVisibility, $rootScope, SocialMedia, imagePreview, $upload, MessagesInbox, MessagesOutbox, logoutService, uploadService) {

				$scope.editDashboard = function() {
					dashboardVisibility.set(true);
				};

				$scope.cancelEditDashboard = function() {
					dashboardVisibility.set(false);
				};

				$scope.reloadSocialMedia = function() {
					$rootScope.socialmedia = SocialMedia.get();
				};

				$scope.cancelDeleteAccount = function() {
					$scope.user.deleted = false;
				};

				$scope.someSelected = function(object) {
					return Object.keys(object).some(function(key) {
						return object[key];
					});
				};

				$scope.submitDeleteForm = function(isValid) {
					if (isValid)
					{
						msgService.inform("Are you sure you want to delete your account?", "confirm", function(confirm) {
							if (confirm) {

								msgService.inform("One moment please, your details are being updated...", 'info');
								// delete account for this user
								$http({
									url: '/json/dashboard/delete-account',
									method: "POST"
								}).success(function(result, status, headers, config) {
									$http({
										url: '/provider/logout-provider/default',
										method: "POST"
									}).success(function(result, status, headers, config) {
										$state.transitionTo('root.home');
										msgService.inform("Your account will be removed when removal is confirmed.", 'info');
										$scope.$root.user = User.get();
									}).error(function(data, status, headers, config) {
										$scope.status = status;
									});
								}).error(function(data, status, headers, config) {
									$scope.status = status;
								});
							}
							else
							{
								$scope.user.deleted = false;
							}
						});
					}
				};

				$scope.submitEditPwdForm = function(isValid) {

					if (isValid) {
						var formdata = {
							'password': $scope.user.pw1,
							'password2': $scope.user.pw2
						};

						msgService.inform("One moment please, your details are being updated...", 'info');
						$http({
							url: '/account/update-password',
							data: formdata,
							method: "POST"
						}).success(function(result, status, headers, config) {
							$('#pwdForm').slideUp('fast');
							msgService.inform("Your new password has been saved; please log in again.", 'warning');
							logoutService.logout();
						}).error(function(data, status, headers, config) {
							$scope.status = status;
						});
					}
					else
					{
						msgService.inform("Please make sure all fields are filled out.", "warning");
					}
				};

				$scope.submitEditEmailForm = function(isValid) {

					if (isValid) {
						var formdata = {
							'email': $scope.user.email1,
							'email2': $scope.user.email2
						};

						$http({
							url: '/account/update-email',
							data: formdata,
							method: "POST"
						}).success(function(result, status, headers, config) {
							msgService.inform("One moment please, your data is being updated...", 'info');
							switch (result) {
								case "done":
									$('#emailForm').slideUp('fast');
									msgService.inform("Your new e-mail address has been saved; please log in again.", 'warning');
									logoutService.logout();
									break;
								case "error":
									msgService.inform("This e-mail is already in use.", 'error');
									break;
							}

						}).error(function(data, status, headers, config) {
							$scope.status = status;
						});
					}
					else
					{
						msgService.inform("Please make sure all fields are filled out.", "warning");
					}
				};

				/**
				 * Process form for user data
				 *
				 * @param {boolean} isValid
				 */
				$scope.submitEditAccountForm = function(isValid, gotostate) {

					if (isValid) {

						var formdata = {
							'name': $scope.user.name,
							'residence': $scope.user.residence,
							'dateofbirth': $scope.user.dateofbirth,
							'country': $scope.user.country,
							'gender': $scope.user.gender,
							'language': $scope.user.language
						};

						// update language
						msgService.setTranslation($scope.user.language);

						$http({
							url: '/account/update-user',
							data: formdata,
							method: "POST"
						}).success(function(result, status, headers, config) {
							msgService.inform("Your personal details have been saved.", 'success');
							dashboardVisibility.set(false);
							$state.go(gotostate || 'root.dashboard', {'lang': $scope.user.language});
						}).error(function(data, status, headers, config) {
							$scope.status = status;
						});
					}
					else
					{
						msgService.inform("Please make sure all fields are filled out.", "warning");
					}
				};

				/**
				 * Process form for settings.
				 *
				 */
				$scope.submitSettingsForm = function() {
					var formdata = {
						'receivePageMessages': $scope.usersettings.receivePageMessages ? $scope.usersettings.receivePageMessages : false,
						'receiveCommentMessages': $scope.usersettings.receiveCommentMessages ? $scope.usersettings.receiveCommentMessages : false,
						'receivePrivateMessages': $scope.usersettings.receivePrivateMessages ? $scope.usersettings.receivePrivateMessages : false,
						'receiveUpdates': $scope.usersettings.receiveUpdates ? $scope.usersettings.receiveUpdates : false,
						'receiveTips': $scope.usersettings.receiveTips ? $scope.usersettings.receiveTips : false,
						'mailFrequency': $scope.usersettings.mailFrequency ? $scope.usersettings.mailFrequency : 'weekly'
					};

					$http({
						url: '/json/dashboard/save-settings',
						data: formdata,
						method: "POST"
					}).success(function(result, status, headers, config) {
						$scope.settingsForm.$setPristine();
						msgService.inform("Your settings have been updated.", 'info');
					}).error(function(data, status, headers, config) {
						$scope.status = status;
					});


				};

				$scope.submitProfilePhoto = function()
				{
					$scope.ui.active = 'body';
                    $scope.ui.readmore = false;
					$scope.saving = true;
					$scope.startupload();
				};

				$scope.onFileSelect = function($files) {
					uploadService.process($files, $scope, true);
				};

				$scope.startupload = function() {
					$scope.uploading = true;	// instead of file.length in view, which IE9 does not compute
					$scope.progress = 0;
					$scope.upload = $upload.upload({
						url: '/json/dashboard/file-upload',
						method: 'POST',
						file: $scope.selectedFile,
						fileFormDataName: 'file'
					}).success(function(response) {
						$scope.uploading = false;  // instead of file.length in view, which IE9 does not compute
						$scope.user.profilephoto = response.photo;
						$scope.file = '';
						// update data for MessagesInbox, MessagesOutbox so the new image is displayed there too
						$rootScope.inbox = MessagesInbox.get();
						$rootScope.outbox = MessagesOutbox.get();
					}).error(function(response) {
						/* @TODO show error */
					}).progress(function(evt) {
						$scope.progress = parseInt(Math.min(100.0, 100.0 * evt.loaded / evt.total));
					});
				};

			}]);


app.controller('pagingCtrl', ['$scope', function($scope) {
		$scope.range = function(n) {
			return new Array(n);
		};
		$scope.toPage = function(nr) {
			$scope.currentPage = nr;
		};
	}]);

app.controller('ContactCtrl', ['$scope', '$http', 'msgService', '$state', '$stateParams', function($scope, $http, msgService, $state, $stateParams) {
		$scope.submitContactForm = function(isValid) {
			if (isValid) {
				var formdata = {
					'name': $scope.contact.name,
					'email': $scope.contact.email,
					'comment': $scope.contact.comment
				};

				$http({
					url: '/ajax/contact',
					data: formdata,
					method: "POST"
				}).success(function(result, status, headers, config) {
					switch (result) {
						case 'done':
							var lang = $stateParams.lang ? $stateParams.lang : '';
							$state.transitionTo('root.contact_ok', {'lang': lang});
							break;

						default:
							msgService.hideAll();
							break;
					}

				}).error(function(data, status, headers, config) {
					$scope.status = status;
				});
			}
			else
			{
				msgService.inform("Please fill out all required fields.", "warning");
			}
		};
	}]);

app.controller('LoginCtrl', ['$scope', 'User', '$http', 'msgService', '$rootScope', '$compile', '$state', 'dashboardVisibility', '$stateParams', '$location',
	function($scope, User, $http, msgService, $rootScope, $compile, $state, dashboardVisibility, $stateParams, $location) {
		$scope.remembrmecheckbox = true;
		
		$scope.getLang = function() {
			/* precedence is: url, user-preference, browser, default=en */
			return $stateParams.lang || $rootScope.$eval('user.language') || (navigator.language || navigator.userLanguage || 'en').substring(0, 2);
		};
		
		$scope.postLoginRedirect = function()
		{
			$scope.$root.user = User.get(function() {
				$('.slide').hide();
				msgService.hideAll();

				if (!$scope.$root.user.loggedin) {
					msgService.inform("Unfortunately we could not find your account with these details.", "error");
					return;
				}

				/* redirect if it's set */
				if ($stateParams['redirect'])
				{
					/* TODO see if we can reload without reload to new state/url without reloading the whole page */
					document.location = $stateParams['redirect'].replace(/\|/g, '/');
					return;
				}

				if ($state && $state.current && !$state.current.name.match(/root\.home/))
				{
					/* go to parent state if we're in popup state */
					var parentstate = $state.current.name.replace(/\.fancybox\.login.*/, '');
					if (parentstate)
					{
						$state.go(parentstate, {}, {reload: true});
						return;
					}
				}

				/* load landingspage in other cases */
				$state.transitionTo('root.landingpage', {'lang': $scope.$root.user.language || $stateParams.lang}, {reload: true});

				/* go to dashboard in other cases
				$state.transitionTo('root.dashboard', {'lang': $scope.$root.user.language}, {reload: true});  */
			});
		};

		$scope.remembrtoggle = function() {
			$scope.remembrmecheckbox = !$scope.remembrmecheckbox;
		};

		$scope.providerlogin = function($idp) {
			msgService.inform("One moment please, we process your data...", "info");

			start_auth("?provider=" + $idp);
		};

		// process forgot password
		$scope.submitResetForm = function(isValid, email) {
			if (isValid) {
				msgService.inform("One moment please, we process your data...", "info");

				var formdata = {
					'email': email === undefined ? $scope.user.email : email,
					'lang': $stateParams.lang
				};
				$http({
					url: ($stateParams.lang ? '/' + $stateParams.lang : '') + '/account/forgotpassword',
					data: formdata,
					method: "POST"
				}).success(function(result, status, headers, config) {
					switch (result) {
						case 'done':
							$('.slide').slideUp('fast');
							$.fancybox.close();
							msgService.inform("We have sent you an e-mail with a link to reset your password.", "success");
							break;

						case 'no-user':
							msgService.inform("This e-mail address is not yet registered.", "warning");
							break;

						case 'not-valid':
							msgService.inform("Enter a valid e-mail address.", "warning");
							break;

						default:
							msgService.hideAll();
							break;
					}

				}).error(function(data, status, headers, config) {
					$scope.status = status;
				});
			}
			else
			{
				msgService.inform("A valid e-mail address is required.", "warning");
			}
		};

		
		$scope.resendConfirmationMail = function(isValid, email) {
			if (isValid) {
				lang = this.getLang();
				$http({
					url: (lang === undefined ? "" : "/" + lang ) + "/account/resend-confirmation-mail",
					method: "POST",
					data: {
						'email': email === undefined ? $scope.user.email : email
					}
				}).success(function(result, status, headers, config) {
					switch (result) {
						case 'done' :
							msgService.inform("Please confirm your registration by clicking the link in the e-mail.", "success");
							break;
						case "user-not-found":
							msgService.inform(
								"Sorry, the e-mail address was not found in our database, this is probably our fault. Please register again or ",
								"error",
								undefined,
								[
									{
										lab: "contact us and provide the e-mail address.",
										handler: function() {
											document.location = '/cmscontent/contact';
										}
									}
								]
							);
							break;
						case "already-confirmed":
							msgService.inform("Your registration has already been confirmed and you are able to log in now.", "success");
							break;
						default:
							msgService.inform(
								"Sorry, some unknown error occured.",
								"error",
								undefined,
								[
									{
										lab: "Please contact us.",
										handler: function() {
											document.location = '/cmscontent/contact';
										}
									}
								]
							);
							break;
					}
				});
			}
			else
			{
				msgService.inform("Please make sure all fields are filled out.", "warning");
			}
		};
		
		$scope.submitSignUpForm = function(isValid) {
			var defaultForm = {
				firstname: "",
				lastname: "",
				email: "",
				password: "",
				password2: ""
			};

			if (isValid) {

				if (typeof $scope.user.terms === 'undefined' || $scope.user.terms === false) {
					msgService.inform("To register at Remembr. you need to accept the terms and conditions and privacy and cookie policy.", "warning");
					return;
				}

				if ($scope.user.pw1 === $scope.user.pw2)
				{
					msgService.inform("One moment please, we process your data...", "info");

					var formdata = {
						'firstname': typeof $scope.user.firstname !== "undefined" ? $scope.user.firstname : '',
						'lastname': typeof $scope.user.lastname !== "undefined" ? $scope.user.lastname : '',
						'email': $scope.user.email,
						'password': $scope.user.pw1,
						'password2': $scope.user.pw2,
						'terms': $scope.user.terms
					};

					$http({
						url: ($stateParams.lang ? '/' + $stateParams.lang : '') + '/account/signup',
						data: formdata,
						method: "POST"
					}).success(function(result, status, headers, config) {
						switch (result) {
							case 'duplicate-unconfirmed' : // falling through
							case 'duplicate-confirmed' :
								$('.pwmeter').hide();
								msgService.inform(
									"This e-mail address is already in use" + 
									(result === "duplicate-unconfirmed" ? ", but it was not yet confirmed. Check your e-mail's inbox and spam or " : "."),
									"error",
									undefined,
									[(
										result === "duplicate-unconfirmed"
									?
										{
											lab: "click here to resend the confirmation e-mail.",
											handler: function($scope, email) { return function() { $scope.resendConfirmationMail(true, email); }; }($scope, $scope.user.email)
										}
									:
										{
											lab: "Forgot your password?",
											handler: function($scope, $email) { return function() { $scope.submitResetForm(true, $email); }; }($scope, $scope.user.email)
										}
									)]
								);
								$scope.user = defaultForm;
								break;
							case 'done' :
								$('.slide').slideUp('fast');
								$('#signup-form').slideUp('fast');
								$('.register').hide();
								$.fancybox.close();
								msgService.inform("Please check your e-mail to confirm your registration.", "info");
								$scope.user = defaultForm;
								break;
							case 'not-valid':
								$('.pwmeter').hide();
								msgService.inform("These details are not correct.", "warning");
								$scope.user = defaultForm;
								break;
						}

					}).error(function(data, status, headers, config) {
						$scope.status = status;
					});

				}
				else
				{
					msgService.inform("Passwords do not match.", "warning");
					$scope.user.pw1 = '';
					$scope.user.pw2 = '';
					$('.pwmeter').hide();
				}
			}
			else
			{
				msgService.inform("Please make sure all required fields are filled out.", "warning");
			}
		};

		// function to submit the form after all validation has occurred
		$scope.submitLoginForm = function(isValid) {

			// check to make sure the form is completely valid
			if (isValid) {

				var formdata = {
					'email': $scope.user.email,
					'password': $scope.user.password,
					'rememberme': typeof $scope.user.rememberme !== "undefined" ? $scope.user.rememberme : false
				};

				$http({
					url: "/account/login",
					method: "POST",
					data: formdata
				}).success(function(result, status, headers, config) {
					switch (result) {
						case 'done' :
							$scope.postLoginRedirect();

							break;

						case 'not-verified':
							msgService.inform("Please confirm your registration by clicking the link in the e-mail.", "error",
									undefined,
									[{
											lab: "Click here to resend the confirmation e-mail.",
											handler: function($scope, email) { return function() { $scope.resendConfirmationMail(true, email); }; }($scope, $scope.user.email)
									}]
								);
							break;

						default:
							msgService.inform("You have entered an incorrect e-mail address or password.", "error");
							break;
					}

				}).error(function(data, status, headers, config) {
					$scope.status = status;
				});
			}
			else
			{
				msgService.inform("Please make sure all fields are filled out.", "warning");
			}

		};

	}]);


app.controller('flashCtrl', ['$scope', 'msgService', '$rootScope', function($scope, msgService, $rootScope) {
		$scope.search = function() {
			if ($('#flasherror').html().length)
			{
				var message = $('#flasherror').html();
				msgService.inform(message, 'info');
			}
			;
		};
		$scope.search();
	}]);


app.controller('AnonymousEditCtrl', ['$scope', 'User', '$http', 'msgService', '$rootScope', '$compile', '$state', 'dashboardVisibility', '$stateParams', '$location',
	function($scope, User, $http, msgService, $rootScope, $compile, $state, dashboardVisibility, $stateParams, $location) {
		$scope.submit = function() {
            console.log('submitting');
		};
    }
]);

$('html').removeClass('no-js');