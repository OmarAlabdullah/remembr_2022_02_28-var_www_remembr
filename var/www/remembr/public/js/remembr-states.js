var app = angular.module('remembr'); //declared in remembr.js

var countries = ['Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos Islands', 'Colombia', 'Comoros', 'Congo', 'Congo,  Democratic Republic of the', 'Cook Islands', 'Costa Rica', 'Cote d\'Ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard Island and McDonald Islands', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macao', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Norfolk Island', 'North Korea', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestinian Territory', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Pierre and Miquelon', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia and Montenegro', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia', 'South Korea', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard and Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Virgin Islands,  British', 'Virgin Islands,  U.S.', 'Wallis and Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe'];

function templateUrlFunction(regurl, pars)
{
	return function($stateParams){
		var lang = $stateParams.lang ? $stateParams.lang + '/' : '';
		var url = '/tpl/' + lang + regurl;
		if (pars)
		{
			for (var i=0; i < pars.length; i++)
			{
				var part = (pars[i][0] === ':') ? $stateParams[pars[i].substring(1)] : pars[i];
				url += '/' + part;
			}
		}
		return url;
	};
}

app.run(['$rootScope', 'DEBUG_STATES', '$location', function($rootScope, DEBUG_STATES, $location) {
        // get path so we can rerout later on (ie when some one is not logged in)
        $rootScope.locationPath = $location.path();
        $rootScope.isTouchScreen = !(!('ontouchstart' in window) && !(navigator.msMaxTouchPoints));
        
        console.log(['rrrr', $rootScope.isTouchScreen]);
        

// For debugging purposes:
// http://stackoverflow.com/questions/20745761/what-is-the-angular-ui-router-lifecycle-for-debugging-silent-errors
        if (DEBUG_STATES) {

            $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {
                console.log('$stateChangeStart to ' + toState.to + '- fired when the transition begins. toState,toParams : \n', toState, toParams);
            });
            $rootScope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams) {
                console.log('$stateChangeError - fired when an error occurs during transition.');
                console.log(arguments);
            });
            $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
                console.log('$stateChangeSuccess to ' + toState.name + '- fired once the state transition is complete.');
            });
            // $rootScope.$on('$viewContentLoading',function(event, viewConfig){
            //   // runs on individual scopes, so putting it in "run" doesn't work.
            //   console.log('$viewContentLoading - view begins loading - dom not rendered',viewConfig);
            // });
            $rootScope.$on('$viewContentLoaded', function(event) {
                console.log('$viewContentLoaded - fired after dom rendered', event);
            });
            $rootScope.$on('$stateNotFound', function(event, unfoundState, fromState, fromParams) {
                console.log('$stateNotFound ' + unfoundState.to + '  - fired when a state cannot be found by its name.');
                console.log(unfoundState, fromState, fromParams);
            });
        }
        // scroll to top
        /*
       $rootScope.$on('$stateChangeSuccess', function() {
            $("html, body").animate({scrollTop: 0}, 200);
        });
        */

        /*
         $rootScope.$on('$stateChangeError',
         function(event, toState, toParams, fromState, fromParams, error) {
         console.log('stateChangeError');
         console.log(toState, toParams, fromState, fromParams, error);

         console.log(error.status);
         });
         */
    }]);


app.config(['$stateProvider', '$urlRouterProvider', '$locationProvider', '$httpProvider', function($stateProvider, $urlRouterProvider, $locationProvider, $httpProvider)
{
	// generic error handling
	$httpProvider.interceptors.push('httpRequestInterceptor');

	$locationProvider.html5Mode(true);
	$urlRouterProvider.otherwise("/");

	$urlRouterProvider.rule(function ($injector, $location) {
        var path = $location.path(), normalized = path.toLowerCase();

        /* strip trailing slashes */
        var pos = normalized.indexOf('/?');
        if (normalized.length > 1 && ((normalized[normalized.length - 1] === '/') || pos>0 ))
        {
            normalized = pos > 0 ? (normalized.substr(0, pos) + normalized.substr(pos+1)) : normalized.substr(0, normalized.length - 1);
        }
        normalized = normalized.replace(/^\/en\/(?:herdenkingspaginas|search)\b/, '/en/memorialpages')
        normalized = normalized.replace(/^\/nl\/(?:memorialpages|search)\b/, '/nl/herdenkingspaginas')

        if (path !== normalized) { return normalized; }
	});


	$stateProvider
		.state('root', {
			abstract : true,
			url : '/{lang:nl|en|nl\-be|}?',
			onEnter : ['$stateParams', 'msgService', '$rootScope', 'MessagesInbox', 'MessagesOutbox', 'pollingService', 'Notifications', 'User',
						function($stateParams, msgService, $rootScope, MessagesInbox, MessagesOutbox, pollingService, Notifications, User)
						{
							msgService.setTranslation($stateParams.lang);
                            
                            $rootScope.delay = 20000;

							if ($rootScope.user.loggedin)
							{
								$rootScope.inbox = MessagesInbox.get();
								$rootScope.outbox = MessagesOutbox.get();
								$rootScope.notifications = Notifications.get();

								pollingService.start();
							}
							else
							{
								pollingService.stop();
							}
						}],
			resolve : {
				/* don't use resolved user but $root.user, because object may change
				 * this is probably also abuse of the resolve function, but at least it's not surprising it works */
				userpromise: ['$rootScope', 'User', function($rootScope, User) {
					if (! ($rootScope.user instanceof User) )
					{
						$rootScope.user = User.get();
						return $rootScope.user.$promise;
					}
					return $rootScope.user.$get().$promise;
				}]
			},
			views : {
				'header@' : {
					templateUrl: templateUrlFunction('content/header')
				},
                'subheader@' : {
					templateUrl: templateUrlFunction('content/subheader')
				},
				
				'footer@' : {
					templateUrl: templateUrlFunction('content/footer')
				}
			}
		})

		.state('root.home' ,{
			url: '',
			title : ['translatorService',function(translatorService){
					return translatorService.translate('Memorial website and online condolences | Remembr.');
			}],
			onEnter : ['$rootScope', 'SearchPages', '$state', function($rootScope, SearchPages, $state){
					//if ($rootScope.user.loggedin) $state.go('root.landingpage');
					//else 
                    $rootScope.bodyclass='full-width anonymous-landing-page';
                    
                    $rootScope.landingSearchResults = SearchPages.getRecent();
                    
				}],
			views : {
				'@' : {
					templateUrl: templateUrlFunction('cmscontent/home')
				}
			}
		})
		.state('root.landingpage' ,{
			url: '',
			title : ['translatorService',function(translatorService){
					return translatorService.translate('Memorial website and online condolences | Remembr.');
			}],
			onEnter : ['$rootScope', 'SearchPages', '$state', function($rootScope, SearchPages, $state){
					$state.go('root.home', $state);
				}],
			views : {
				'@' : {
					templateUrl: templateUrlFunction('cmscontent/home')
				}
			}
		})
		.state('root.welcome', {
			url : '/home/welcome',
			onEnter: ['$rootScope', '$timeout', function($rootScope,$timeout){
			    localStorage.welcomed = true;
				if (!$rootScope.user.loggedin)
				{
					$timeout(function(){$.fancybox.close();}); /*hack*/
				}
			}],

			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('dashboard/landing-popup'),
					controller : ['$scope', function($scope){
						$scope.close = function(){
							$.fancybox.close();
						};

						localStorage.welcomed = true;
					}]
				}
			}
		})
        .state('root.home.fancybox.showvideo', {
			url : '/home/video',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Login | Remembr.');
			}],
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('landing/video'),
					controller: ['msgService','$scope', '$stateParams', function(msgService,$scope,$stateParams){
						$(window).resize();
						$scope.infoMsgs =  angular.copy(msgService.allInfos());
					}]
				}
			}
		})

		.state('root.basic' ,{
			url: '/{slug:about|faq}',
			onEnter : ['$rootScope',function($rootScope){$rootScope.bodyclass='cms-page';}],
			views : {
				'@' : {
					templateUrl: templateUrlFunction('cmscontent', [':slug'])
				}
			}
		})

		.state('root.cms' ,{
			url: '/cmscontent/:slug',
			title : ['translatorService','$stateParams',function(translatorService,$stateParams){
					var msgs = {
						'conditions': translatorService.translate('Terms and conditions | Remembr.'),
						'privacy'	: translatorService.translate('Privacy and cookie policy | Remembr.'),
						'disclaimer': translatorService.translate('Disclaimer | Remembr.'),
						'contact'	: translatorService.translate('Contact | Remembr.'),
						'contact_ok': translatorService.translate('Thank you for your message | Remembr.'),
						'report'	: translatorService.translate('Report a problem | Remembr.'),
						'faq'		: translatorService.translate('Frequently asked questions | Remembr.'),
						'not-implemented' : translatorService.translate('This feature is not yet available | Remembr.')
					}
					if (msgs[$stateParams.slug])
						return msgs[$stateParams.slug];
					else
						return 'Remembr.';
			}],
			onEnter : ['$rootScope',function($rootScope){$rootScope.bodyclass='cms-page';}],
			views : {
				'@' : {
					templateUrl: templateUrlFunction('cmscontent', [':slug'])
				}
			}
		})
		.state('root.forgotpassword2', {
			url: '/account/forgotpassword2/:key',
			onEnter: ['$rootScope', function($rootScope) {
				$rootScope.bodyclass = 'forgotpassword2';
			}],
			views: {
				'@': {
					templateUrl: templateUrlFunction('account/forgotpassword2', [':key'])
				}
			}
		})
		// search
		.state('root.search', {
			url: '/{search:search|herdenkingspaginas|memorialpages|huisdieren|pets|vips}/:searchterm?',
			title : ['translatorService',function(translatorService){
					return translatorService.translate('Find a memorial page | Remembr.');
			}],
			onEnter: ['$rootScope', 'SearchPages', function($rootScope, SearchPages) {
				$rootScope.bodyclass = 'search';
				$rootScope.user.$get(); /* to update accesslist */

				// set range for select boxes birthyear and deathyear
				var range = [];
				var d = new Date();
				var n = d.getFullYear();
				for (var i = n; i > 1900; i--) {
					range.push(i);
				}
				$rootScope.range = range;
				$rootScope.recent = SearchPages.getRecent();
				$rootScope.slides = SearchPages.getRotators();
				$rootScope.countries = countries;
			}],

			views: {
				'@': {
					templateUrl: templateUrlFunction('search'),
					controller: ['$scope', 'SearchPages', '$stateParams', 'translatorService', function($scope, SearchPages, $stateParams, translatorService) {
						$scope.predicate = '-dateofdeath';
                        $scope.term = $stateParams.searchterm;
						/* using $root because we need it in request-invite. @TODO find cleaner way */
                        
                        if ($.inArray($stateParams.search, ['huisdieren', 'pets']) >= 0) {
                            $scope.$root.searchpages = SearchPages.getType({'searchterm': $stateParams.searchterm, 'searchtype': 'animal'});
                            $("input[value=animal][name=type]").prop('checked', true)
                        } else if ($.inArray($stateParams.search, ['vips']) >= 0) {
                            $scope.$root.searchpages = SearchPages.getType({'searchterm': $stateParams.searchterm, 'searchtype': 'vip'});
                            $("input[value=vip][name=type]").prop('checked', true)
                        } else {
                            $scope.$root.searchpages = SearchPages.get({'searchterm': $stateParams.searchterm});
                        }

						document.title = translatorService.translate('Find a memorial page | Remembr.');
					}]
				}
			}
		})

                .state('root.notifications', {
					url: '/notifications',
					title : ['translatorService',function(translatorService){
						return translatorService.translate('Notifications | Remembr.');
					}],
					onEnter: ['$rootScope', function($rootScope) {
							$rootScope.bodyclass = 'notifications';
						}],
			/* @TODO this is abuse of resolve. I'm surprised it works. Resolve should be a map of strings to functions that return a promise */
					resolve: ['$rootScope', 'Notifications', function($rootScope, Notifications) {
						$rootScope.notificationsHistory = Notifications.getNotificationsHistory();
					}],
					views: {
						'@': {
							templateUrl: templateUrlFunction('notifications')
						}
					}
                })

                .state('root.contact_ok', {
                    url: '/cmscontent/contact_ok',
					title : ['translatorService',function(translatorService){
							return translatorService.translate('Thank you for your message | Remembr.');
					}],
                })

                // User account and settings
                .state('root.dashboard', {
                    url: '/dashboard',
					title : ['translatorService',function(translatorService){
						return translatorService.translate('Dashboard | Remembr.');
					}],
                    onEnter: ['$rootScope', function($rootScope) {
                            $rootScope.bodyclass = 'dashboard';
                            $rootScope.user.$get(); // update because maybe new info is added (ie page photo)
                        }],

			/* @TODO this is abuse of resolve. I'm surprised it works. Resolve should be a map of strings to functions that return a promise */
                    resolve: ['$rootScope', 'dashboardVisibility', 'SharedMemories', 'pollingService', function($rootScope, dashboardVisibility, SharedMemories, pollingService) {
                            if (typeof $rootScope.editdashboard === 'undefined') {
                                dashboardVisibility.set(false);
                            }
                            // cache shared memories @TODO: find out how/when to reload when data has changed
                            if (!$rootScope.sharedmemories) {
                                $rootScope.sharedmemories = SharedMemories.get();
                            }
                            pollingService.start();
                        }],
                    views: {
                        '@': {
                            templateUrl: templateUrlFunction('dashboard')
                        }
                    }
                })

				 // Landingpage
//                .state('root.landingpage', {
//                    url: '/home',
//					title : ['translatorService',function(translatorService){
//							return translatorService.translate('Online memorials and obituaries | Remembr.');
//					}],
//                    onEnter: ['$rootScope', 'SearchPages', '$state','$stateParams', function($rootScope, SearchPages, $state, $stateParams) {
//
//                            $rootScope.bodyclass = 'landingpage';
//							$rootScope.user.$get();
//							$rootScope.landingSearchResults = SearchPages.getRecent();
//                            console.log('sdfasadfcsadkjhfgaskhbfaskjfawsf');
//                            
//							if (!$rootScope.user.loggedin)
//							{
//								$state.go('root.home', $stateParams);
//								return false;
//							}
//                        }],
//
//                    views: {
//                        '@': {
//                            //templateUrl: templateUrlFunction('dashboard/landing-page'),
//                            templateUrl: templateUrlFunction('cmscontent/home'),
//							controller : ['$scope', 'vimeoService', '$state', 'pollingService', function($scope, vimeoService, $state, pollingService)
//							{
//                            	pollingService.start();
//
//								$scope.startVideo = function() {
//									vimeoService.play();
//								};
//
//								/*
//								 *  first time login
//								 *
//								 *  @TODO: open fancybox with dashboard
//								 */
//								if ($scope.$root.user.logins == 1 && ! localStorage.welcomed)
//								{
//									$state.go('root.landingpage.fancybox.welcome');
//								}
//							}]
//                        }
//                    }
//                })

                .state('root.usersettings', {
					url: '/settings',
					title : ['translatorService',function(translatorService){
							return translatorService.translate('Account settings | Remembr.');
					}],
					onEnter: ['$rootScope', function($rootScope) {
							$rootScope.bodyclass = 'usersettings';
						}],
			/* @TODO this is abuse of resolve. I'm surprised it works. Resolve should be a map of strings to functions that return a promise */
				   resolve: ['$rootScope', '$http', 'UserSettings', 'SocialMedia', function($rootScope, $http, UserSettings, SocialMedia) {
						   UserSettings.then(function(data) {
								 $rootScope.usersettings = data;
							});
							if(!$rootScope.socialmedia) {$rootScope.socialmedia = SocialMedia.get();}
						}],
					views: {
						'@': {
							templateUrl: templateUrlFunction('dashboard/settings')
						}
					}
                })

                .state('root.usersettings.password', {
                    url: '/password',
                    onEnter: ['$rootScope', function($rootScope) {
                            $rootScope.bodyclass = 'password';
                        }],
                    views: {
                        '@': {
                            templateUrl: templateUrlFunction('dashboard/password')
                        }
                    }
                })

                .state('root.usersettings.email', {
                    url: '/email',
                    onEnter: ['$rootScope', function($rootScope) {
                            $rootScope.bodyclass = 'email';
                        }],
                    views: {
                        '@': {
                            templateUrl: templateUrlFunction('dashboard/email')
                        }
                    }
                })

                .state('root.messages', {
                    url: '/messages/:msgid?',
					title : ['translatorService',function(translatorService){
							return translatorService.translate('Messages | Remembr.');
					}],
                    onEnter: ['$rootScope', 'MessagesOutbox', '$stateParams', '$state', function($rootScope, MessagesOutbox, $stateParams, $state) {
                            $rootScope.bodyclass = 'messages';

                            if ($stateParams.msgid) {
                                $rootScope.selected = $stateParams.msgid;
                                MessagesOutbox.setReadDate({'id': $stateParams.msgid});
                            }
                        }],
                    views: {
                        '@': {
                            templateUrl: templateUrlFunction('messages')
                        }
                    }
                })

                .state('root.newmsg', {
                    url: '/newmsg',
					title : ['translatorService',function(translatorService){
							return translatorService.translate('Send a new message | Remembr.');
					}],
                    onEnter: ['$rootScope', function($rootScope) {
                            $rootScope.bodyclass = 'messages';
                        }],
                    views: {
                        '@': {
                            templateUrl: templateUrlFunction('messages')
                        }
                    }
                })

		.state('root.page', {
			title : ['$rootScope','translatorService',function($rootScope,translatorService){
				if (! $rootScope.page) return '';
				pagename = $rootScope.page.firstname + ' ' + $rootScope.page.lastname
				return translatorService.translate('%s %s remembrance page and online condolences').replace('%s %s', pagename);
			}],
/* this approach doesn't seem to work when switching to another page.*/
//			resolve: {
//				page: ['$q', '$stateParams', '$rootScope', 'Page', function($q, $stateParams,$rootScope, Page) {
//					var deferred = $q.defer();
//
//					$rootScope.page = Page.get({url : $stateParams.page}, function(page){
//						deferred.resolve($rootScope.page);
//					},function(result){
//						deferred.resolve(result);
//					});
//
//					return deferred.promise;
//				}]
//			},

			//url: "/{page:(?!home)[^/]+}",

			/* /:msgid? needed for "Page messages reminder" cron e-mail */
			/* msgid split off to separate state because it gives all kinds of trouble. */
			url: "/{page:(?!home|create-page|dashboard|messages|newmsg|notifications|email|settings|password|search)[^/]+}",
			onEnter : ['$rootScope',function($rootScope){$rootScope.bodyclass='remembr-page';}],
			onExit : ['$rootScope',function($rootScope){$rootScope.page = {} ;}], /* @TODO maybe it can be done better */
			views : {
				'@' : {
					templateUrl: templateUrlFunction('page'),

					controller: ['$scope','$state','$stateParams','Page', 'Content', 'msgService', 'translatorService', 
						function($scope, $state, $stateParams, Page, Content, msgService, translatorService) {
							$scope.$root.filter = {
								types : {
									'memory' : true,
									'photo' : true,
									'video' : true,
									'condolence' : false
								},
								labels : {}
							};
                            //console.log('in controlelr 2');
							//$scope.$root.lang = $stateParams.lang ? $stateParams.lang.substring(0,2) : '';
                            //console.log(['sp', $stateParams]);
							//$scope.$root.page.url = $stateParams.page;
                            ///console.log('getting page');
							$scope.$root.page = Page.get({url : $stateParams.page}, function(page){
								if ($scope.$root.page.memories === undefined)
								{
									$scope.$root.page.memories = Content.list({url : page.url});
								}

								if (page.labels)
								{
									for(var i =0; i < page.labels.length; i++)
									{
										$scope.$root.filter.labels[page.labels[i].id] = false;
									}
								}
								/* @TODO find a way that the state.title suffices. Changing to another page poses problems with resolve approach*/
								pagename = page.firstname + ' ' + page.lastname
								document.title = translatorService.translate('%s %s remembrance page and online condolences').replace('%s %s', pagename);
							}, function(result){
								if (result.data && result.data.error)
								{
									$scope.$root.page = result.data;
                                    console.log(['result', result]);
									if (result.status == 401)
									{
										if ($scope.user.loggedin)
										{
											$state.go('root.page.fancybox.private');
										}
										else
										{
											msgService.inform('Please log in to view the page. New to Remembr? Please sign up first.', 'login');
											$state.go('root.page.fancybox.login', {}, { location : false});
										}
									}
									else
									{
										//$state.go('root.page.error');
									}
									return;
								}

							});
							
							
							$scope.toggle = function(type, id){
								$scope.$root.filter[type][id] = !$scope.$root.filter[type][id];
								if(id==='condolence')
								{
									$scope.clearLabels();
								}
								else if (type === 'labels')
								{
									$scope.$root.filter.types.condolence=false;
								}
							};
							$scope.showAll = function(type){
								$scope.$root.filter.types['memory'] = !type  || type === 'memory';
								$scope.$root.filter.types['photo']	= !type  || type === 'memory';
								$scope.$root.filter.types['video']	= !type  || type === 'memory';
								$scope.$root.filter.types['condolence'] = !type  || type === 'condolence';
								$scope.clearLabels();
							};
							$scope.allTypesSelected = function()
							{
								var anytype = true;
								angular.forEach($scope.$root.filter.types, function(value){
									anytype &= value;
								});
								return anylabel && anytype;
							};
							$scope.allLabelsSelected = function()
							{
								var anylabel = true;
								angular.forEach($scope.$root.filter.labels, function(value){
									anylabel &= !value;
								});
								return anylabel;
							};
							$scope.allSelected = function()
							{
								return $scope.allTypesSelected() && $scope.allLabelsSelected();
							};
							$scope.clearLabels = function()
							{
								angular.forEach($scope.$root.filter.labels, function(value, key){
									$scope.$root.filter.labels[key] = false;
								});
							};

							$scope.countLabels = function(label)
							{
								var cnt = 0;
								angular.forEach($scope.$root.page.memories, function(m){
									angular.forEach(m.labels, function(l){
										if (l.id == label.id)
										{
											cnt++;
										}
									});
								});
								return cnt;
							};

					}]
				}
			}
		})
		.state('root.page.memoryredirect',
		{
			url: "/{msgid:\\d+}", /* digits only, that way it doesn't conflict with other substates! */
			onEnter : ['$rootScope', '$stateParams', '$state', '$timeout', function($rootScope, $stateParams, $state, $timeout)
			{
				if ($stateParams.msgid && $rootScope.user.loggedin)
				{
					$timeout(function() {
						$state.go('root.page.fancybox.memory', {'id': $stateParams.msgid});
					}, 2000);
				}
			}]
		})
		.state('root.page.error', {
			url: "/error",
			views : {
				'@' : {
					templateUrl: templateUrlFunction('page/error')
				}
			}
		});

		/**
		 *	unfortunately, we need the same childstates from several parent states, and
		 *	angular-ui-router does not provide for this yet, other than copying them.
		 */
		function multiStateProvider(sp, prefixes)
		{
			this.sp = sp;
			this.prefixes = prefixes;
			this.state = function (statename, stateconfig)
			{
				for(var i =0; i < prefixes.length; i++)
				{
					sp.state(prefixes[i] + statename, angular.copy(stateconfig));
				}

				return this;
			};
		}

        // Order is important. With 'root.pages' as last element, all works as expected.
        // I am not sure why though.
        // If 'root.pages' is before ie 'root.dashboard', the fancybox is loaded with data, then closed and opened again without data...
        // @TODO: figure out why?
		var ms = new multiStateProvider($stateProvider,
        ['root.home', 'root.basic', 'root.cms', 'root.password', 'root.email', 'root.usersettings', 'root.newmsg','root.search', 'root.landingpage', 'root.dashboard', 'root.page', 'root.messages', 'root.notifications']);

        // FANCYBOX FOR DETAILED VIEW AND SETTINGS AND CREATEPAGE
		ms.state('.fancybox', {
			url : '',
			abstract : true,

			onExit: function()
			{
				$.fancybox.close(); // triggers: $destroy.fancybox
			},

			onEnter: ['$compile','$rootScope','$timeout','$state', function($compile,$rootScope,$timeout,$state)
			{
				$timeout(function(){
					var scope = $rootScope.$new();
					$.fancybox.open('<div id="fancyboxview" ui-view="fancybox"></div>');
                    console.log($compile(angular.element('#fancyboxview')));
					$compile(angular.element('#fancyboxview'))(scope);
					angular.element('#fancyboxview').parent().one('$destroy.fancybox', function(evt)
					{
						scope.$destroy();

						// go to page state if we're still in a page.fancybox.* state
						// console.log('fancybox: ' + $state.current.name);

						if($state.current.name.match(/\.fancybox(\.|$)/))
						{
							$state.go($state.current.name.replace(/\.fancybox.*$/, ''));
						}
					});
				});
			}]
		})

                // CREATE PAGE
		.state('.fancybox.createpage', {
			url : '/create-page',
			abstract : true,
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('create-page'),

					controller: ['$scope','$state','$stateParams', 'Page', '$upload', 'imagePreview', 'uploadService', 'localStorageService', function($scope, $state, $stateParams, Page, $upload, imagePreview, uploadService, localStorageService)
					{
						$scope.reset = function()
						{
                            $scope.newpage = new Page({
                                currentstep   : 1,
                                maxstep       : 0,
                                firstname     : '',
                                lastname      : '',
                                dateofbirth   : '',
                                roi           : null,
                                dateofdeath   : '',
                                introtext     : '',
                                photo         : '',
                                url           : '',
                                gender        : '',
                                residence     : '',
                                country	      : '',
                                publishnow    : true,
                                invites       : {
                                    facebook  : {},
                                    remembr   : {},
                                    email     : {}
                                }
                            });
                            $scope.newpage.currentstep = 1;
                            $scope.newpage.maxstep = 0;
						};
                        
                        $scope.setdraft = function() {
                            Page.getDraft(function(ret) {
                                if (ret['draft'] != null)
                                    for (var key in ret['draft'])
                                        $scope.newpage[key] = ret['draft'][key];
                                $scope.$watch("newpage", function(value) {
                                    $scope.scheduleUpdateDraft();
                                }, true);
                            });
                        };
                        
                        $scope.last_draft_update = 0;
                        $state.disregard_all_updates = false;
                        $scope.update_pending = false;
						$scope.reset();
                        $scope.setdraft();
                        
                        $scope.isEmpty = function() {
                            return (
                                $scope.newpage.firstname.length +
                                $scope.newpage.lastname.length +
                                $scope.newpage.dateofbirth.length +
                                $scope.newpage.dateofdeath.length +
                                $scope.newpage.introtext.length + 
                                $scope.newpage.photo.length + 
                                $scope.newpage.url.length + 
                                $scope.newpage.gender.length + 
                                $scope.newpage.residence.length + 
                                $scope.newpage.country.length
                            ) == 0;
                        };
                        
                        $scope.__updateDraft = function() {
                            //if ($scope.isEmpty())
                            //    return;
                            if ($state.disregard_all_updates)
                                return;
                            Page.updateDraft($scope.newpage);
                            $scope.last_draft_update = (new Date()).getTime();
                            $scope.update_pending = false;
                        };
                        
                        $scope.scheduleUpdateDraft = function() {
                            if ($state.disregard_all_updates)
                                return;
                            if ($scope.update_pending)
                                return;
                            
                            time_elapsed = (new Date()).getTime() - $scope.last_draft_update;
                            soonest_possible = 5000 - time_elapsed;
                            if (soonest_possible <= 0) {
                                $scope.__updateDraft();
                            } else {
                                $scope.update_pending = true;
                                setTimeout($scope.__updateDraft, soonest_possible);
                            }
                        };

						$scope.setstep = function(num)
						{
							if (!$scope.$root.user.loggedin)
							{
								$state.go('^.^.login', $stateParams);
								return false;
							}

							if ($scope.newpage.maxstep===6)
							{
								$scope.reset();
								$state.go('.info');
								return false;
							}

							if ($scope.newpage.maxstep < num-1)
							{
								switch($scope.newpage.maxstep)
								{
									case 4 : $state.go('^.publish');break;
									case 3 : $state.go('^.url');	break;
									case 2 : $state.go('^.photo');	break;
									case 1 : $state.go('^.info');	break;
									default: $state.go('^.info');	break;
								}
								return false;
							}

							$scope.newpage.currentstep = num;
							$scope.newpage.maxstep = Math.max($scope.newpage.maxstep, num);

							return true;
						};

                        $scope.disableCropper = function () {
                            $('img.actual-image').cropper('destroy');
                        };

                        $scope.ignoreFirstLoad = true;
                        $scope.lastCropperUrl = null;
                        $scope.enableCropper = function() {
                            currentUrl = $("img.actual-image").attr("src");
                            if (! $scope.ignoreFirstLoad && $scope.lastCropperUrl != currentUrl)
                            {
                                $('img.actual-image').cropper({aspectRatio: 1.0, viewMode: 1, guides: false, rotatable: false, zoomable: false,
                                    crop: function($scope, e) {
                                      $scope.newpage.roi = {x: e.x, y: e.y, width: e.width, height: e.height};
                                      $scope.newpage.roi = {x: e.x, y: e.y, width: e.width, height: e.height};
                                    }.bind(undefined, $scope)
                                });
                                $("img.actual-image").cropper('replace', currentUrl); // watch it, causes a new on load event to fire !!!
                                $scope.lastCropperUrl = currentUrl;
                            }
                            $scope.ignoreFirstLoad = false;
                        };

                        $scope.onFileSelect = function($files) {
                            uploadService.process($files, $scope, false);
                        };

                        $scope.onFileSelect = function($files) {
                            uploadService.process($files, $scope, false);
                        };
                        
                        $scope.scaleROI = function(roi, factor) {
                            roi.x      = roi.x      * factor;
                            roi.y      = roi.y      * factor;
                            roi.width  = roi.width  * factor;
                            roi.height = roi.height * factor;
                            return roi;
                        };

                        $scope.startupload = function(callback) {
                            if (! $scope.selectedFile)
                            {
                                callback();
                                return;
                            }

                            $scope.progress = 0;
                            $scope.upload = $upload.upload({
                                url : '/json/page/file-upload',
                                method: 'POST',
                                file: $scope.selectedFile,
                                fileFormDataName: 'file'
                            }).success(function(response) {
                                $scope.newpage.photo = response.photo;
                                $scope.newpage.roi = $scope.scaleROI($scope.newpage.roi, response.scale_factor);
                                
                                if (angular.isFunction(callback)) callback();
                            }).error(function(response) {
                                /* @TODO show error */
                                if (angular.isFunction(callback)) callback();
                            }).progress(function(evt) {
                                $scope.progress = parseInt(Math.min(100.0, 100.0 * evt.loaded / evt.total));
                            });
                        };
					}]
				}
			}
		})
		.state('.fancybox.createpage.info', {
			url : '/step1-info',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create a memorial page step 1 | Remembr.');
			}],
			views : {
				createstep : {
					templateUrl: templateUrlFunction('create-page/step1-info'),

					controller: ['$scope','$state','$stateParams', function($scope, $state, $stateParams)
					{
						if (!$scope.setstep(1)) { return ; }

						$scope.nextStep = function()
						{

							if ($scope.info.$valid)
							{
								$state.go('^.photo');
							}
							else // mark them all as dirty so validation errors are shown.
							{
								$scope.info.gender.$dirty=true;
								$scope.info.firstname.$dirty=true;
								$scope.info.lastname.$dirty=true;
								$scope.info.dateofbirth.$dirty=true;
								$scope.info.dateofdeath.$dirty=true;
								$scope.info.introtext.$dirty=true;
								$scope.info.country.$dirty=true;
								$scope.info.residence.$dirty=true;
							}
						};

						$scope.countries = countries;
					}]
				}
			}
		})

		.state('.fancybox.createpage.photo', {
			url : '/step2-photo',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create a memorial page step 2 | Remembr.');
			}],
			views : {
				createstep : {
					templateUrl: templateUrlFunction('create-page/step2-photo'),
                
					controller: ['$scope', '$rootScope', function($scope, $rootScope)
					{
                        //console.log('set last crop url to null');
                        $scope.$parent.lastCropperUrl = null;
                        //console.log($scope);
                        
						if (!$scope.setstep(2)) { return ; }
					}]
				}
			}
		})
		.state('.fancybox.createpage.url', {
			url : '/step3-url',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create a memorial page step 3 | Remembr.');
			}],
			views : {
				createstep : {
					templateUrl: templateUrlFunction('create-page/step3-url'),

					controller: ['$scope','$state','Page', function($scope, $state, Page)
					{
						if (!$scope.setstep(3)) { return ; }

						$scope.suggestions = Page.urlSuggestions({
							firstname : $scope.newpage.firstname,
							lastname : $scope.newpage.lastname,
							dateofbirth : $scope.newpage.dateofbirth,
							dateofdeath : $scope.newpage.dateofdeath
						});

						$scope.checkAvailable = function(callback)
						{
							Page.checkAvailable({url:$scope.newpage.url},
								function(result)
								{
									$scope.url.url.$setValidity('connect', true);
									$scope.url.url.$setValidity('available', result.available);
									$scope.url.url.$dirty=true;
									if (callback)
									{
										callback();
									}
								},
								function()
								{
									$scope.url.url.$setValidity('connect', false);
									$scope.url.url.$dirty=true;
								},
								callback
							);
						};

						$scope.select = function (suggestion)
						{
                            console.log(['select', $scope]);
							$scope.newpage.url = suggestion.url;
						};

						$scope.nextStep = function(){
							$scope.checkAvailable(function()
							{
								if ($scope.url.$valid)
								{
									$state.go('^.publish');
								}
							});
						};
					}]
				}
			}
		})
		.state('.fancybox.createpage.publish', {
			url : '/step4-publish',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create a memorial page step 4 | Remembr.');
			}],
			views : {
				createstep : {
					templateUrl: templateUrlFunction('create-page/step4-publish'),

					controller: ['$scope','$state', 'Page', 'localStorageService', function($scope,$state,Page, localStorageService)
					{
						if (!$scope.setstep(4)) { return ; }

						// Page can only be published if person is already dead
						$scope.allowpublish = $scope.$parent.newpage.dateofdeath === '' ? false : true;
						$scope.newpage.publishnow = $scope.allowpublish;

						$scope.nextStep = function(){
							if ($scope.newpage.publishnow)
							{
								$state.go('^.invite');
							}
							else
							{
								$scope.setstep(5);

								if (this.lock) return
								this.lock = true;

								/* @TODO  same as after step-5; can we merge it some way and avoid copy-pasted code? */
								var callback = function(){
									Page.create($scope.newpage,function(ret)
									{
										$scope.newpage = ret;
                                        $state.disregard_all_updates = true;
                                        Page.deleteDraft();
										$scope.user.$get();
										$state.go('^.done');
									},function(){
										console.log('oh no, some error has occurred'); /* @TODO error handling */
									});
								};
								$scope.startupload(callback);
							}
						};

					}]
				}
			}
		})
		.state('.fancybox.createpage.invite', {
			url : '/step5-invite',
			title : ['translatorService', function(translatorService){
				return translatorService.translate('Create a memorial page step 5 | Remembr.');
			}],
			views : {
				createstep : {
					templateUrl: templateUrlFunction('create-page/step5-invite'),

					controller: ['$scope','$state', 'Page', 'localStorageService', function($scope, $state, Page, localStorageService)
					{
						if (!$scope.setstep(5)) { return ; }

						$scope.nextStep = function()
						{
							if (this.lock) return
							this.lock = true;
							
							var callback = function(){
								Page.create($scope.newpage,function(ret)
								{
									$scope.newpage = ret;
                                    $state.disregard_all_updates = true;
                                    Page.deleteDraft();
									$scope.user.$get();
									$state.go('^.done');
								},function(){
									console.log('oh no, some error has occurred'); /* @TODO error handling */
								});
							};

							$scope.startupload(callback);
						};

					}]
				}
			}
		})
		.state('.fancybox.createpage.invite-email', {
			url : '/step5c-invite-email',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create a memorial page step 5 e-mail | Remembr.');
			}],
			views : {
				'createstep' : {
					templateUrl: templateUrlFunction('create-page/step5c-invite-email'),
					controller: ['$scope','$state', 'msgService', 'translatorService', function($scope, $state, msgService, translatorService) {
						if (!$scope.setstep(5)) { return ; }

						$scope.invite = $scope.newpage.invites.email;

						$scope.save = function(){
							msgService.inform('Your email invitation will be sent in the final step.', 'info');
							$scope.newpage.invites.email = $scope.invite;
							$state.go('^.invite');
						};

					}]
				}
			}
		})
		.state('.fancybox.createpage.done', {
			url : '/step6-done',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create a memorial page step 6 | Remembr.');
			}],
			views : {
				createstep : {
					templateUrl: templateUrlFunction('create-page/step6-done'),

					controller: ['$scope','$state', 'translatorService', function($scope, $state, translatorService)
					{
						if (!$scope.setstep(6)) { return ; }

						$scope.viewPage = function(){
							$state.go('root.page', {page : $scope.newpage.url, reload : true});
						};
					}]
				}
			}
		})

		// Login
		.state('.fancybox.login', {
			url : '/user/login/:redirect?',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Login | Remembr.');
			}],
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('user/login'),
					controller: ['msgService','$scope', '$stateParams', function(msgService,$scope,$stateParams){
						$(window).resize();
						$scope.infoMsgs =  angular.copy(msgService.allInfos());
					}]
				}
			}
		});

        //EDITING ANONYMOUSLY SUBMITTED CONDOLENCE VIA EMAILED LINK.
        $stateProvider.state('root.page.fancybox.editanonymous', {
            url: '/edit-anonymous/{editkey:[0-9a-zA-Z]{40,40}}',
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('page/edit-anonymous'),
					controller: ['$rootScope', '$scope','$state','$stateParams', 'Content', 'msgService', 'msgTabService', function($rootScope, $scope, $state, $stateParams, Content, msgService, msgTabService) { 
                        $scope.loading=true;
                        Content.getAnonymous({
                            url : $stateParams.page,
                            editkey: $stateParams.editkey
                        }).$promise.then(function(memory) {
                            $scope.found=true;
                            $scope.anonymousEdit = new Content(memory);
                            $scope.loading=false;
                        }).catch(function() {
                            $scope.found=false;
                            $scope.loading=false;
                        });
                        
                        
                        $scope.$root.openTab('condolence');
                        $scope.save = function() {
                            Content.saveAnonymous({ url : $stateParams.page, editkey: $stateParams.editkey}, $scope.anonymousEdit, function(status) {
                                if (status.status == "ok") {
                                    $.fancybox.close();
                                    msgService.inform("Your condolence message has been updated", "info");
                                    $scope.$root.page.memories = Content.list({url : $scope.$root.page.url});
                                    msgTabService.openTab('condolence');
                                } else if (status.status == "no-text")
                                    msgService.inform("Your condolence message may not be empty.", "error");
                                else
                                    msgService.inform("Your condolence message was not found.", "error");
                            });
                        }
                        $scope.delete = function() {
                            Content.deleteAnonymous({ url : $stateParams.page, editkey: $stateParams.editkey}, function(status) {
                                if (status.status == "ok") {
                                    $.fancybox.close();
                                    msgService.inform("Your condolence message has been removed", "info");
                                    $scope.$root.page.memories = Content.list({url : $scope.$root.page.url});
                                    msgTabService.openTab('condolence');
                                } else
                                    msgService.inform("Your condolence message was not found.", "error");
                            });
                        }
                        $scope.close = $.fancybox.close;
                    }]
				}

			}
        });
        
        // SETTINGS
		$stateProvider
		.state('root.page.fancybox.setting', {
			url : '/settings',
			abstract : true,

			resolve : {
				isadmin: ['$http','$q','$stateParams', function($http,$q,$stateParams) {
					var deferred = $q.defer();

					$http.get('/json/' + $stateParams.page + '/settings').success(function(data){
						deferred.resolve(true);
					}).error(function(data){
						deferred.resolve(false);
//						deferred.reject(data);
					});
					return deferred.promise;
				}]
			},

			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('settings'),
					controller: ['$state', 'isadmin', function($state, isadmin) {
						if (!isadmin && ! $state.name == 'root.page.fancybox.setting.qrcode')
						{
							$state.go('root.page.fancybox.setting.error');
						}
					}]
				}
			}
		})
		.state('root.page.fancybox.setting.error', {
			url : '/error',
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/error'),
					controller:[function(){}]
				}
			}
		})

		.state('root.page.fancybox.setting.info', {
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Change page details | Remembr.');
			}],
			url : '/info',
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/info'),

					controller: ['$scope', '$state','$stateParams', 'Page', '$upload', 'imagePreview','isadmin', 'uploadService', 'translatorService',
						function($scope, $state, $stateParams, Page, $upload, imagePreview, isadmin, uploadService, translatorService) {

                            if (!isadmin)
                            {
                                $state.go('root.page.fancybox.setting.error');
                                return;
                            }

                            $scope.countries = countries;
                            $scope.editpage = Page.get({url : $stateParams.page});

                            $scope.close = function(){
                                $.fancybox.close();
                            };
                            
                            $scope.save = function(){
                                if ($scope.info.$invalid)
                                {
                                    $scope.info.gender.$dirty      = true;
                                    $scope.info.firstname.$dirty   = true;
                                    $scope.info.lastname.$dirty    = true;
                                    $scope.info.dateofbirth.$dirty = true;
                                    $scope.info.dateofdeath.$dirty = true;
                                    $scope.info.introtext.$dirty   = true;
                                    $scope.info.country.$dirty     = true;
                                    $scope.info.residence.$dirty   = true;
                                    return;
                                }
                                if (!$scope.info.$dirty)
                                {
                                    // @TODO somehow convey error.
                                    return;
                                }

                                var callback = function(){
                                    $scope.editpage.$update({part:'info'}, function success(data){
                                        angular.forEach(['firstname', 'lastname', 'dateofbirth', 'dateofdeath', 'introtext', 'photo', 'gender', 'residence', 'country'], function(val){
                                            $scope.page[val] = data[val];
                                        });
                                        $scope.page['roi'] = {x: Math.round(data['roi']['x']), y: Math.round(data['roi']['y']), width: Math.round(data['roi']['width']), height: Math.round(data['roi']['height'])};
                                        $scope.info.$setPristine();
                                        // @TODO somehow convey that things went well.
                                    }, function fail(data){
                                            console.log(data.data.error);
                                    });
                                };
                                $scope.startupload(callback);
                            };

                            $scope.disableCropper = function () {
                                $('img.actual-image').cropper('destroy');
                            };
                            
                            $scope.lastCropperUrl = null;
                            $scope.roiOnceSet = false;
                            
                            $scope.enableCropper = function() {
                                currentUrl = $("img.actual-image").attr("src");
                                if ($scope.lastCropperUrl != currentUrl)
                                {
                                    if (! $scope.roiOnceSet )
                                        $scope.storedROI = $scope.editpage.roi;
                                    console.log('starting cropper');
                                    $('img.actual-image').cropper({aspectRatio: 1.0, viewMode: 1, guides: false, rotatable: false, zoomable: false,
                                        crop: function($scope, e) {
                                          $scope.editpage.roi = {x: e.x, y: e.y, width: e.width, height: e.height};
                                          $scope.info.roi     = {x: e.x, y: e.y, width: e.width, height: e.height};
                                          $scope.info.$setDirty();
                                        }.bind(undefined, $scope),
                                        built: function($scope, currentUrl, e) {
                                            if (! $scope.roiOnceSet )
                                                $("img.actual-image").cropper('setData', $scope.storedROI);
                                            $scope.roiOnceSet = true;
                                        }.bind(undefined, $scope, currentUrl)
                                    });
                                    $scope.lastCropperUrl = currentUrl;
                                    $("img.actual-image").cropper('replace', currentUrl); // watch it, causes a new on load event to fire !!!
                                            
                                }
                            };

                            $scope.onFileSelect = function($files) {
                                $scope.info.$setDirty();
                                uploadService.process($files, $scope, false);
                            };
                            
                            $scope.scaleROI = function(roi, factor) {
                                roi.x      = roi.x      * factor;
                                roi.y      = roi.y      * factor;
                                roi.width  = roi.width  * factor;
                                roi.height = roi.height * factor;
                                return roi;
                            };

							$scope.startupload = function(callback) {
								if (! $scope.selectedFile)
								{
									callback();
									return;
								}

								$scope.progress = 0;
								$scope.uploading = true;
								$scope.upload = $upload.upload({
									url : '/json/' + $scope.page.url + '/file-upload',
									method: 'POST',
									file: $scope.selectedFile,
									fileFormDataName: 'file'
								}).success(function(response) {
                                $scope.uploading = false;
									$scope.editpage.photo = response.photo;
                                    $scope.editpage.roi = $scope.scaleROI($scope.editpage.roi, response.scale_factor);
                                    $scope.info.$setDirty();
									if (angular.isFunction(callback)) callback();
								}).error(function(response) {
									$scope.uploading = false;
									/* @TODO show error */
									if (angular.isFunction(callback)) callback();
								}).progress(function(evt) {
									$scope.progress = parseInt(Math.min(100.0, 100.0 * evt.loaded / evt.total));
									console.log($scope.progress);
								});
							};
                        }
                    ]
                }
			}
		})
		.state('root.page.fancybox.setting.admins', {
			url : '/admins',
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/admins'),
					controller: ['$scope','$state','$stateParams', function($scope, $state, $stateParams) { /*console.log($state.current.name); console.log($stateParams);*/ }]
				}

			}
		})
		.state('root.page.fancybox.setting.privacy', {
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Change privacy | Remembr.');
			}],
			url : '/privacy',
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/privacy'),
					controller: ['$scope', '$state','$stateParams', 'Page', 'isadmin', 'translatorService',
						function($scope, $state, $stateParams, Page, isadmin,translatorService) {
							if (!isadmin)
							{
								$state.go('root.page.fancybox.setting.error');
								return;
							}

							$scope.editpage = Page.get({url : $stateParams.page});

							$scope.close = function(){
								$.fancybox.close();
							};

							$scope.save = function(){

								if (!$scope.privacy.$dirty)
								{
									// @TODO somehow convey error.
									return;
								}

								$scope.editpage.$update({part:'privacy'}, function success(data){
									$scope.user.$get(); /* to update accesslist */
									$scope.privacy.$setPristine();
									// @TODO somehow convey that things went well.
								}, function fail(data){
									console.log(data.data.error);
								});
							};
					}]
				}

			}
		})
		.state('root.page.fancybox.setting.block', {
			url : '/block',
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/block'),
					controller: ['$scope','$state','$stateParams', function($scope, $state, $stateParams) { /*console.log($state.current.name); console.log($stateParams);*/ }]
				}
			}
		})
		.state('root.page.fancybox.setting.publish', {
			url : '/publish',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Publish memorial page | Remembr.');
			}],
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/publish'),
					controller: ['$scope', '$state','$stateParams', '$filter', 'Page', 'msgService', 'isadmin', 'translatorService',
						function($scope, $state, $stateParams, $filter, Page, msgService, isadmin, translatorService) {
							
						if (!isadmin)
						{
							$state.go('root.page.fancybox.setting.error');
							return;
						}

						/* start loader */
						$scope.editpage = Page.get({url : $stateParams.page} /* , function(){stop loader} */);

						$scope.close = function(){
							$.fancybox.close();
						};

						$scope.remove = function(){
//							msgService.inform("Are you sure?", "confirm", function(confirm) {
//								if(confirm)
//								{
									$scope.editpage.status = undefined;
									Page.update({part:'publish'}, {url : $stateParams.page, status:'tobedeleted'}, function(data){
										msgService.inform("An email has been sent to your email address to confirm permanent removal of this page", "info");
										$scope.editpage.status = 'tobedeleted';
										$scope.$root.page.status = 'tobedeleted';

									}, function fail(data){
										console.log(data.data.error);
									});
//								}
//							});
						};

						$scope.deactivate = function(){
//								$scope.publish.$setDirty();
							$scope.editpage.status = undefined;
							Page.update({part:'publish'}, {url : $stateParams.page, status:'deactivated'}, function(data){
								//console.log(data);
								$scope.editpage.status = 'deactivated';
								$scope.$root.page.status = 'deactivated';
							}, function fail(data){
								console.log(data.data.error);
							});
						};

						$scope.publishnow = function(){
//								$scope.publish.$setDirty();
							$scope.editpage.status = undefined;
							Page.update({part:'publish'}, {url : $stateParams.page, status:'published'}, function(data){
								console.log(data);
								$scope.editpage.status = 'published';
								$scope.$root.page.status = 'published';
							}, function fail(data){
								console.log(data.data.error);
							});
						};
					}]
				}

			}
		})
		.state('root.page.fancybox.setting.themes', {
			url : '/themes',
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/themes'),
					controller: ['$scope','$state','$stateParams', function($scope, $state, $stateParams) { /*console.log($state.current.name); console.log($stateParams);*/ }]
				}

			}
		})
		.state('root.page.fancybox.setting.labels', {
			url : '/labels',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create labels | Remembr.');
			}],
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/labels'),
					controller: ['$scope', '$state','$stateParams', 'Page', 'isadmin', 'translatorService',
						function($scope, $state, $stateParams, Page, isadmin, translatorService) {
							if (!isadmin)
							{
								$state.go('root.page.fancybox.setting.error');
								return;
							}
							$scope.editpage = Page.get({url : $stateParams.page});
							$scope.newlabel = '';

							$scope.close = function(){
								$.fancybox.close();
							};

							$scope.add = function(){
								if ($.trim($scope.newlabel))
								{
									$scope.editpage.labels.push({ name: $.trim($scope.newlabel), 'new' : true});
									$scope.newlabel = '';
									$scope.labels.$setDirty();
								}
							};

							$scope.deleteToggle = function(label){
								$scope.labels.$setDirty();
								if (label['new']) /* todo don't use new IE sucks*/
								{
									$scope.editpage.labels = $.grep($scope.editpage.labels, function(el){return el!==label;} );
								}
								else
								{
									label['delete'] = ! label['delete']; /* @TODO don't use delete, cause IE still sucks */
								}
							};

							$scope.save = function(){
								if (!$scope.labels.$dirty)
								{
									// @TODO somehow convey error.
									return;
								}

								$scope.editpage.$update({part:'labels'}, function success(data){
									$scope.labels.$setPristine();
									$scope.page.uselabels = data.uselabels;
									$scope.page.labels = data.labels;

									// @TODO somehow convey that things went well.
								}, function fail(data){
									console.log(data.data.error);
								});
							};
					}]
				}

			}
		})
		.state('root.page.fancybox.setting.invite', {
			url : '/invite',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Send invitations | Remembr.');
			}],
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/invite'),
					//templateProvider: function($stateParams){ console.log($stateParams); return 'inline1.html'; },
					controller: ['$state','isadmin','translatorService',  function($state, isadmin, translatorService) {
						if (!isadmin)
						{
							$state.go('root.page.fancybox.setting.error');
							return;
						}
					}]
				}
			}
		})
		.state('root.page.fancybox.setting.invite-email', {
			url : '/invite-email',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Send invitations via e-mail | Remembr.');
			}],
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/invite-email'),
					//templateProvider: function($stateParams){ console.log($stateParams); return 'inline1.html'; },
					controller: ['$scope','$state','$stateParams', 'Page', 'isadmin', 'msgService', 'translatorService',
						function($scope, $state, $stateParams, Page,isadmin, msgService, translatorService) {
						if (!isadmin)
						{
							$state.go('root.page.fancybox.setting.error');
							return;
						}
						$scope.invite = {};
						$scope.loading = false;

						$scope.send = function(){
                            $scope.loading = true;
							$(':focus').blur();

							if (!$scope.inviteemail.$dirty)
							{
								// @TODO somehow convey error.
								return;
							}

							Page.invite({url: $stateParams.page}, {email:$scope.invite}, function success(data){
								//$scope.invite = {};
                                $scope.invite.recipients = '';
								$scope.inviteemail.$setPristine();
								$scope.loading = false;
								msgService.inform('Your email invitation has been sent.', 'info');
							}, function fail(data){
								console.log(data.data.error);
								$scope.loading = false;
							});
						};

					}]
				}

			}
		})

		.state('root.page.fancybox.setting.invite-facebook', {
			url : '/invite-facebook',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Send invitations via Facebook | Remembr.');
			}],
			views : {
				'settings' : {
					templateUrl: templateUrlFunction('settings/invite-facebook'),
					//templateProvider: function($stateParams){ console.log($stateParams); return 'inline1.html'; },
					controller: ['$scope', '$state', 'isadmin', 'Facebook', '$stateParams', 'translatorService', 
						function($scope, $state, isadmin, Facebook, $stateParams, translatorService) {
						if (!isadmin)
						{
							$state.go('root.page.fancybox.setting.error');
							return;
						}
						$scope.friends = Facebook.getFriends();

						$scope.sendRequest = function(title, link, linktxt) {

							// generate valid url for this page on this site
							var thepage = document.location.origin + '/' + $stateParams.page;

							FB.ui({
								method: 'send',
								name: title,
								link: thepage
							});
							/*
							 * Well, this is not working. Anti spam FB
						// Get the list of selected friends
							var sendUIDs = '';
							var mfsForm = document.getElementById('mfsForm');
							for (var i = 0; i < mfsForm.friends.length; i++) {
								if (mfsForm.friends[i].checked) {
									sendUIDs += mfsForm.friends[i].value + ',';
								}
							}

							// Use FB.ui to send the Request(s)

							FB.ui({
								method: 'apprequests',
								to: sendUIDs,
								title: title,
//								message: title + ' <a href="' + link + '/' + $stateParams.page + '">' + linktxt  + '</a>'
								message: title,
								data:  link + '/' + $stateParams.page
							}, callback);

							function requestCallback(response) {
								window.top.location.href = link + '/' + $stateParams.page;
							}*/
						};
					}]
				}
			}
		})

		.state('root.page.fancybox.setting.qrcode', {
			url : '/qrcode',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Create QR code | Remembr.');
			}],
			views : {
				'settings' : {
					templateUrl: function($stateParams){
						var lang = $stateParams.lang ? $stateParams.lang + '/' : '';
						return '/tpl/' + lang +  $stateParams.page + '/settings/qrcode';
					}
				}
			}
		})

// DETAILED VIEW
		.state('root.page.fancybox.memory', {
			url : '/memory/:id',
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('page/show'),
					controller: 'MemoryDetailCtrl'
				}
			},
			onEnter: ['$rootScope', '$state', 'translatorService', function($rootScope,$state,translatorService)
			{
//				console.log($state.current.name);
				var stop = $rootScope.$watch(function(){return $rootScope.page && $rootScope.page.firstname;}, function ()
				{
					/* @TODO find a way to do it via state.title */
					pagename = $rootScope.page.firstname + ' ' + $rootScope.page.lastname;
					document.title = translatorService.translate('%s %s memory | Remembr.').replace('%s %s', pagename);
					stop();
				});
			}]
		})

		.state('root.page.fancybox.confirmremoval', {
			url : '/remove/confirm/:confirmkey',
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('page/remove'),
					controller: ['$scope', '$state','$stateParams', '$http', 'msgService', '$location', function($scope, $state, $stateParams, $http, msgService, $location) {

						if (! $scope.$root.user.loggedin)
						{
							msgService.inform('You need to log in before you can confirm removal of this remembr page', 'login');
							var params = angular.copy($stateParams);
							params.redirect = $location.path().replace(/\//g,'|');
							$state.go('root.page.fancybox.login', params, {location : false});
						}

						$(window).resize();

						$scope.close = function(){
							$.fancybox.close();
						};

						$scope.confirm = function(){ /* @TODO,(?) use Page resource */
							$http({
								url: '/json/'+$stateParams.page+'/confirm-removal',
								data: {confirmkey : $stateParams.confirmkey },
								method: "POST"
							}).success(function(result, status) {
								if (result.removed)
								{
									msgService.inform("The page has been removed.", "success");
									$state.go('root.dashboard');
								}
							}).error(function(result, status) {
								if (result.error)
								{
									if ($scope.page.status == 'deactivated')
									{
										msgService.inform('Your request to remove the page has expired because the setting of the page has in the meanwhile been changed to "deactivated".', "error");
									}
									else if ($scope.page.status == 'published')
									{
										msgService.inform('Your request to remove the page has expired because the setting of the page has in the meanwhile been changed to "published".', "error");
									}
									else
									{
										msgService.inform("An error occurred while trying to remove the page", "error");
									}
								}
								$scope.status = status;
							});
						};
					}]
				}

			}
		})
		.state('root.search.fancybox.private',{
			url : '/requestaccess/:page',
			title : ['translatorService',function(translatorService){
				return translatorService.translate('Send an access request | Remembr.');
			}],
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('page/requestaccess'),
					controller: ['$scope', '$state','$stateParams', '$http', 'msgService', 'Page', '$location', function($scope, $state, $stateParams, $http, msgService, Page, $location) {
						var setpage = function(){
							angular.forEach($scope.searchpages.pages, function(v){
								if (v.url == $stateParams.page)
								{
									$scope.page = v;
								}
							});
						}
						setpage();
						/* so it works at reload. */
						$scope.$watch(function(){return $scope.searchpages.pages;}, setpage);

						$scope.requestaccess = function()
						{
							if( ! $scope.user.loggedin )
							{
								msgService.inform('Please log in to send a request to the administrator', 'login');
								$state.go('root.search.fancybox.login');
							}
							else
							{
								console.log($scope.page);
								Page.requestAccess({url : $scope.page.url}, {},
									function(resp){
										msgService.inform('Your request has been sent to the administrator', 'info');
									},function(resp){
										console.log(resp);
									}
								);

							}
						}
					}]
				}
			}
		})
		.state('root.page.fancybox.private',{ /* very similar to previous :( */
			url : '/requestaccess',
			views : {
				'fancybox@' : {
					templateUrl: templateUrlFunction('page/requestaccess'),
					controller: ['$scope', '$state','$stateParams', '$http', 'msgService', 'Page', function($scope, $state, $stateParams, $http, msgService, Page) {

						$scope.$watch('$root.page.extra.data', function(val){
							$scope.page = val;
						});

						$scope.requestaccess = function()
						{
							if( ! $scope.user.loggedin )
							{
								msgService.inform('Please log in to send a request to the administrator', 'error');
								$state.go('root.search.fancybox.login');
							}
							else
							{
								Page.requestAccess({url : $scope.page.url}, {},
									function(resp){
										msgService.inform('Your request has been sent to the administrator', 'info');
									},function(resp){
										console.log(resp);
									}
								);

							}
						}
					}]
				}
			}
		});
}]);