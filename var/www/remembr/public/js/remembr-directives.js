var app = angular.module('remembr'); /* declared in remembr.js */


/**
 * Slideshow
 */
app.directive('slideshow', ['$timeout', '$parse', function($timeout, $parse) {
    return {
        restrict: 'C',
        replace: false,
        link: function(scope, element, attrs) {
            element.addClass('bz-slider');
            scope.$slideIndex = 0;
            scope.$slides = [];

            // watch for slides update
            scope.$watch(attrs.bzSlider, function(value) {
                var arr = [];
                angular.forEach(element.children(), function(item) {
                    if (angular.element(item).hasClass('bz-slide')) {
                        arr.push(item);
                    }
                });
                scope.$slides = arr;
            });
            // delay
            if (angular.isDefined(attrs.delay)) {
                scope.$watch(attrs.delay, function(value) {
                    scope.$delay = value;
                });
            }
            // autoplay
            if (angular.isDefined(attrs.autoplay)) {
                scope.$autoplay = $parse(attrs.autoplay)(scope);
                if (scope.$autoplay) {
                    scope.play();
                }
            }
        }
    };
}]);

/**
 *  shortens text to a given number of lines.
 */
app.directive('shorten', ['$timeout', function($timeout) {
        return {
            restrict: 'A',
            replace: false,
            link: function($scope, element, attrs) {
                onVisibilityOrExpressionChange = function(e) {
                    $timeout(function() {
                        if (element.css('display') === 'none') {
                            element.next().hide();
                            return;
                        }
                        element.height('auto');
                        var h = parseInt(element.css('line-height')) * attrs.shorten;
                        if (element.height() > h || +element.height() === 0)    // @TODO newly added content height is not known (unless we timeout longer?), for now a check op 0
                        {
                            element.height(h).css({overflow: 'hidden'});
                            element.next().show();
                        }
                        else
                        {
                            element.next().hide();
                        }
                    });
                }.bind(element, attrs);
                
                $scope.$watch(function() { return element.is(':visible'); }, onVisibilityOrExpressionChange);
                $scope.$watch(attrs.ngBind, onVisibilityOrExpressionChange);
            }
        };
    }]);

// a directive to collapse long text and add toggle functionality on next element
app.directive('collapse', ['$compile', '$timeout', '$rootScope', function($compile, $timeout, $rootScope) {
        return {
            restrict: 'A',
            replace: false,
            link: function(scope, element, attrs) {
                $rootScope.moreless = false;
                var collapsed = true;
                var h = parseInt(element.css('line-height')) * attrs.collapse;
                element.height(h).css({overflow: 'hidden'});

                $timeout(function() {

                   if (parseInt(element['context'].scrollHeight / parseInt(element.css('line-height'))) > attrs.collapse) {
                       $rootScope.moreless = true;
                   }


                    element.next().on('click', function() {
                        if (collapsed) {
                            element.height(element[0].scrollHeight);
                            collapsed = false;
                        }
                        else
                        {
                            element.height(h).css({overflow: 'hidden'});
                            collapsed = true;
                        }
                    });
                }, 5000);   // Maybe this can be done better. But it takes a while before scrollHeight is known.

            }
        };
    }]);

// a directive to display only a few letters from a text
app.directive('letters', ['$compile', '$timeout', function($compile, $timeout) {
        return {
            restrict: 'A',
            replace: true,
            template: '<span>{{text}}</span>',
            transclude: true,
            scope: {
                text: '='
            },
            link: function(scope, element, attrs) {
                $timeout(function() {
					if (scope.text)
					{
						/* return everything upto the last word-boundary within the limit */
						var reg = new RegExp('.{0,'+ attrs.letters +'}(\\b|$)');
						scope.text = scope.text.match(reg)[0];
					}
                });

            }
        };
    }]);

/**
 * invert checkbox value
 * @link http://stackoverflow.com/a/13926335/1961666
 */
app.directive('inverted', function() {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {
            ngModel.$parsers.push(function(val) {
                return !val;
            });
            ngModel.$formatters.push(function(val) {
                return !val;
            });
        }
    };
});



/**
 *  initialize data by taking it from the attribute or element text.
 *  @TODO extend maybe by parsing initdata as json and merge with $scope.
 */
app.directive('initdata', function() {
	return {
		restrict: 'A',
		link: function($scope, element, attrs) {
			if (attrs.ngBind !== undefined)
			{
				var propspath = attrs.ngBind.split('.');
				var curprop = propspath.shift();
				while (propspath.length)
				{
					if ($scope[curprop] === undefined) {
						$scope[curprop] = {};
					}
					$scope = $scope[curprop];
					curprop = propspath.shift();
				}
				if (!$scope[curprop])
				{
					$scope[curprop] = attrs.initdata ? attrs.initdata : $.trim(element.text());
				}
			}
		}
	};
});

app.directive('checkboxGroup', function() {
	return {
		restrict: 'A',
		scope: true,
		link: function($scope, element, attrs) {
			$scope.state = 'inactive';
			$scope.somechecked = false;
			$scope.allchecked = false;
		}
	};
});

app.directive('master', ['$compile', function($compile) {
        return {
            restrict: 'A',
            replace: true,
            require: '?^form',
            link: function($scope, element, attrs, ctrl) {
                var slavs = element.parents('[checkbox-group]').find('input[type=checkbox]:not([master])');
                var model = [];
                slavs.each(function()
                {
                    model.push($(this).attr('ng-model'));
                });
                var ormodel = model.join(' || ');
                var andmodel = model.join(' && ');
                var assignmodels = model.join(' = !allchecked;') + ' = !allchecked';
                $scope.$watch(ormodel, function(newval) {
                    $scope.somechecked = newval;
                    $scope.setstate();
                });
                $scope.$watch(andmodel, function(newval) {
                    $scope.allchecked = newval;
                    $scope.setstate();
                });

                $scope.setstate = function() {
                    $scope.state = $scope.somechecked !== $scope.allchecked ? 'indeterminate' : ($scope.somechecked ? 'active' : 'inactive');
                };
                $scope.toggle = function() {
                    ctrl.$setDirty(true);
                    $scope.$eval(assignmodels);
                };
            }
        };
    }]);


app.directive("styled", function() {
    return {
        restrict: 'CA',
        link: function(scope, elm, attrs) {
            var t = attrs.inverted == undefined;
            if (attrs.ngModel || attrs.ngChecked)
            {
                scope.$watch(attrs.ngModel || attrs.ngChecked, function(val) {
                    elm.parent().toggleClass('active', val === t);
                });
            }
        }
    };
});

app.directive('userMsg', ['msgTabService', '$state', function(msgTabService, $state) {
        return {
            restrict: 'C',
            replace: true,
            template:
                    '<div ng-if="user.id!=$root.user.id" class="contact_icon">' +
                    '<div>' +
                    '<img class="user_photo" width="65" height="65" alt="" ng-src="/minify?files={[{user.photoid}]}&resize=w[65]h[65]f[true]">' +
                    '<a ng-click="mailUser(user)">{[{user.firstname}]} {[{user.lastname}]}</a>' +
                    '<button class="med_blue_but" ng-click="mailUser(user)">{{ text }}</button>' +
                    '</div>' +
                    '</div>',

            transclude: true,
            scope: {
                user: '=',
                text: '='
            },
            controller: function($scope, $element) {
                $scope.mailUser = function(user) {
                    msgTabService.setNewto(user);
                    msgTabService.openTab('new');
                    $state.go('root.newmsg');
                };

            }
        };
    }]);

app.directive('deleteContent', ['$rootScope', '$http', 'msgService', function($rootScope, $http, msgService) {
        return {
            restrict: 'C',
            replace: true,
            template: '<div>' +
                    '<a ng-click="deletecnt(mem)"></a>' +
                    '</div>',
            transclude: true,
            scope: {
                mem: '='
            },
            controller: function($scope, $element) {
                $scope.deletecnt = function(mem) {

                    // ask confirm
                    msgService.inform("Are you sure?", "confirm", function(confirm) {

                        if (confirm)
                        {
                            var formdata = {
                                'id': mem.id
                            };

                            $http({
                                url: '/content/delete-memory',
                                data: formdata,
                                method: "POST"
                            }).success(function(result, status, headers, config) {
                                switch (result) {
                                    case 'ok':
                                        // remove from memories
                                        var index = $rootScope.page.memories.indexOf(mem);
                                        $rootScope.page.memories.splice(index, 1);
                                        msgService.inform("The message is deleted.", "success");
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
            }
        };
    }]);


app.directive('editContent', ['$rootScope', '$http', 'msgService', function($rootScope, $http, msgService) {
        return {
            restrict: 'C',
            replace: true,
            template: '<div><a ng-click="editcnt(mem)"></a></div>',
            transclude: true,
            scope: {
                mem: '='
            },
            controller: function($scope, $element) {
                $scope.editcnt = function(mem) {
                    $element.next().addClass('editing').removeClass('not-editing');
                    $element.next().find('.edit-content-text-field').focus();
                    mem.editing = true;
                };
            }
        };
    }]).directive('saveEditContent', ['$rootScope', '$http', 'msgService', function($rootScope, $http, msgService) {
        return {
            restrict: 'C',
            controller: function($scope, $element) {
                $scope.saveContent = function(context, mem) {
                    var formdata = { id: mem.id, text: mem.text };
                    $http({
                        url: '/json/content/edit',
                        data: formdata,
                        method: "POST"
                    }).success(function($element, result, status, headers, config) {
                        if (result['changed']) {
                            msgService.inform("The altered message is saved.", "success");
                            $element.parent().parent().parent().parent().removeClass('editing').addClass('not-editing');
                            mem.editing = false;
                            mem.text.$dirty = true;
                        } else {
                            msgService.inform("Sorry, something went wrong, " + result['errors'][0], "warning");
                        }
                    }.bind(undefined, $element)).error(function($element, data, status, headers, config) {
                        $scope.status = status;
                        msgService.inform("Sorry, something went wrong.", "warning");
                    });
                }.bind(undefined, $element);
            }
        };
    }]);

/*
* Replace all spaces to semicolons in an e-mail list if semicolon are not provided by user.
* Replace commas with semicolons too if those are accidently provided by user.
*/
app.directive('space2semicolon', function() {

	return {
		restrict: 'A',
		require: 'ngModel',
		link: function(scope, element, attrs, ngModelCtrl) {
			element.bind('change', function() {
				var newAddresses = scope.invite.recipients.trim().replace(/[,\s]+/g, ';');
				scope.invite.recipients = newAddresses;
			});
		}
	};
});


/**
 * @link http://www.abequar.net/jquery-ui-datepicker-with-angularjs/
 */
app.directive('datepicker', function() {

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attrs, ngModelCtrl) {
            var format = attrs.datepicker || $.datepicker.ISO_8601;
			var lock = false;

            /* in case date-format changes */
            attrs.$observe('datepicker', function(val) {
                format = val || $.datepicker.ISO_8601;
                element.datepicker('option', {dateFormat: format});
            });

            element.datepicker({
                showOn: "both",
                buttonImage: "/images/cal_img.jpg",
                buttonImageOnly: true,
                dateFormat: format,
                changeYear: true,
                yearRange: "1900:+1",
				maxDate: new Date(),
                onSelect: function(date) {
					/* I can't remember why/if/when this is necessary. It seems to work without it. */
                    scope.$apply(function() {
                        ngModelCtrl.$setViewValue(date);
                    });
                },
				onChangeMonthYear: function(year,month,dp) {
					if (! lock)
					{  /* setting the date seems to trigger this callback, so we need the lock to skip recursion */
						lock = true;
						var date = new Date(year, month-1, dp.selectedDay);
						$('#'+dp.id).datepicker('setDate', date);
						setTimeout(function(){lock=false;}, 50);
					}
                }
            });

            /* convert from user's dateformat in view to iso-format in model */
            ngModelCtrl.$parsers.push(function(val)
            {
                if (!val) {
                    return val;
                }

                try
                {
                    var date = $.datepicker.parseDate(format, val);
                    ngModelCtrl.$setValidity('date', true);
                    return $.datepicker.formatDate($.datepicker.ISO_8601, date);
                }
                catch (e)
                {
                    ngModelCtrl.$setValidity('date', false);
                    return undefined;
                }
            });
            ngModelCtrl.$formatters.push(function(val)
            {
                if (!val) {
                    return val;
                }
                var date = $.datepicker.parseDate($.datepicker.ISO_8601, val);
                return $.datepicker.formatDate(format, date);
            });
        }
    };
});


app.directive('regFilter', ['$parse', function($parse) {
        return {
            require: 'ngModel',
            link: function(scope, element, attrs, modelCtrl)
            {
                var params = $parse(attrs.regFilter)();
                var reg = params && params.reg ? new RegExp(params.reg, 'ig') : /\s+/ig;
                var rep = params && params.rep ? params.rep : '';

                modelCtrl.$parsers.push(function(inputValue)
                {
                    if (!inputValue) {
                        return inputValue;
                    }

                    var transformedInput = inputValue.toLowerCase().replace(reg, rep);

                    if (transformedInput !== inputValue)
                    {
                        modelCtrl.$setViewValue(transformedInput);
                        modelCtrl.$render();
                    }

                    return transformedInput;
                });
            }
        };
    }]);


app.directive('condolence', function() {
    return {
        restrict: 'A',
        replace: true,
        scope: {condolence: '=', idx: '='},
        templateUrl: '/tpl/content/condolence-small'
    };
});
app.directive('memory', function() {
    return {
        restrict: 'A',
        replace: true,
        scope: {
            memory: '=',
            idx: '='
        },
        templateUrl: '/tpl/content/memory-small'
    };
});
app.directive('photo', function() {
    return {
        restrict: 'A',
        replace: true,
        scope: {photo: '=', idx: '='},
        templateUrl: '/tpl/content/photo-small'
    };
});

app.directive('video', function() {
    return {
        restrict: 'A',
        replace: true,
        scope: {video: '=', idx: '='},
        templateUrl: '/tpl/content/video-small'
    };
});

app.directive('youtubevideo', ['$sce', function($sce) {
        return {
            restrict: 'A',
            replace: true,
            scope: {video: '='},
            templateUrl: '/tpl/content/youtube-video',
            link: function(scope, elm, attrs) {
                var videoid = attrs.youtubevideo;
                var videourl = 'https://www.youtube.com/embed/' + videoid +
                        '?enablejsapi=1&amp;fs=1&amp;border=0&amp;modestbranding=1&amp;rel=0&amp;showinfo=0&amp;autohide=2' +
                        '&amp;iv_load_policy=3&amp;autoplay=1&amp;wmode=transparent&amp;nologo=1"' +
                        ' frameborder="0" allowfullscreen="" wmode="Opaque"';
                scope.videourl = $sce.trustAsResourceUrl(videourl);
            }
        };
    }]);

/**
 * simple paging when not having too much data
 */
app.directive('simplepaging', ['$timeout', function($timeout) {
        return {
            restrict: 'A',
            replace: true,
            templateUrl: '/tpl/content/simplepaging',
            link: function(scope, elm, attrs) {
                // get or set default values for paging
                scope.currentPage = scope.$eval(attrs.currentPage) || 0;
                scope.pageSize = scope.$eval(attrs.pageSize) || 8;
                // get number of items to page
                scope.nrItems = +attrs.nritems;
                // calculate number of pages
                scope.numberOfPages = Math.ceil(scope.nrItems / scope.pageSize);
                attrs.$observe('nritems', function(value) {
                    scope.nrItems = +value;
                    scope.numberOfPages = Math.ceil(value / scope.pageSize);
                    if (scope.numberOfPages <= scope.currentPage) {
                        scope.currentPage = (scope.numberOfPages - 1) >= 0 ? scope.numberOfPages - 1 : 0;
                    }
                }, true);
            }

        };
    }]);

app.directive('scrollOnClick', function() {
    return {
        restrict: 'A',
        link: function(scope, elm, attrs) {
            var idToScroll = attrs.href;
            elm.on('click', function() {
                var $target;
                if (idToScroll) {
                    $target = $(idToScroll);
                } else {
                    $target = $elm;
                }
                $("body").animate({scrollTop: $target.offset().top}, "slow");
            });
        }
    };
});

/**
 * infomessages
 */
app.directive('infomessages', ['msgService', function(msgService) {
        return {
            restrict: 'A',
            replace: true,
            scope: {infomessages: '='},
            templateUrl: '/tpl/content/infomessages',
            link: function(scope, elm, attrs) {
                scope.allMessages = msgService.allInfos;
                scope.remove = msgService.remove;
                scope.confirm = msgService.confirm; // function
            }
        };
    }]);

/**
 * time out for infomessages
 */
app.directive('msgPostRender', ['$timeout', 'msgService', function($timeout, msgService) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                if (scope.alert.type !== 'error' && scope.alert.type !== 'confirm') {
                    $timeout(function() {
                        msgService.remove(scope.alert);
                    }, 5000);
                }
            }
        };
    }]);

/**
 * Close slide menu after clicking on a link inside.
 * Add "noclose" class if this functionality is not needed on the slide menu.
 */
app.directive('slide', ['$rootScope', function($rootScope) {
        return {
            restrict: 'C',
            link: function(scope, elem, attrs) {
                if (!elem.hasClass('noclose')) {
                    elem.bind('click', function() {
                        $rootScope.ui.active = '';
                        $rootScope.ui.readmore = false;
                    });
                }
            }
        };
    }]);

// {# <input name="" type="button" onclick="sendRequest('{{ 'I have created a memorial page on Remembr.com.' | trans }}', '{{ baseurl }}', '{[{ pagename }]}');" value="{{'Send invitation' |trans}}" class="save_changes"> #}




/**
 * FAQ accordion directive
 */
app.directive('accordion', ['$timeout', function($timeout) {
        return {
            restrict: 'C',
            replace: true,
            template: '<div><i ng-class="{nonActiveIcon:!open, activeIcon:open}"></i><h4>{{title}}</h4>' +
                    '<div class="accordion-content" ng-show="open" ng-transclude></div></div>',
            transclude: true,
            scope: {
                title: '='
            },
            link: function(scope, elm) {
                scope.open = false;

                angular.element(elm.children()[1]).bind('click', function() {
                    scope.$apply('open = !open');

                });
            }
        };
    }]);

/**
 * Slide show
 */
app.animation('.slide-animation',['$window', function ($window) {
    return {
        enter: function (element, done) {
            var startPoint = $window.innerWidth * 0.5,
                tl = new TimelineLite();

            tl.fromTo(element.find('.slide'), 1, { alpha: 0}, {alpha: 1});

        },

        leave: function (element, done) {
            var tl = new TimelineLite();

            tl.to(element, 1, {alpha: 0, onComplete: done});
        }
    };
}]);

/**
 * directive for tinymce
 */
app.directive('uiTinymce', [function() {
        uiTinymceConfig = {};
        var generatedIds = 0;

        return {
            priority: 10,
            require: 'ngModel',
            link: function(scope, elm, attrs, ngModel) {
                var expression, options, tinyInstance,
                        updateView = function() {
                    ngModel.$setViewValue(elm.val());

                    if (!scope.$root.$$phase) {
                        scope.$apply();
                    }
                };

                // generate an ID if not present
                if (!attrs.id) {
                    attrs.$set('id', 'uiTinymce' + generatedIds++);
                }

                if (attrs.uiTinymce) {
                    expression = scope.$eval(attrs.uiTinymce);
                } else {
                    expression = {};
                }

                // make config'ed setup method available
                if (expression.setup) {
                    var configSetup = expression.setup;
                    delete expression.setup;
                }

                options = {
                    // Update model when calling setContent (such as from the source editor popup)
                    setup: function(ed) {
                        var args;
                        ed.on('init', function(args) {
                            ngModel.$render();
                            ngModel.$setPristine();
                        });
                        // Update model on button click
                        ed.on('ExecCommand', function(e) {
                            ed.save();
                            updateView();
                        });
                        // Update model on keypress
                        ed.on('KeyUp', function(e) {
                            ed.save();
                            updateView();
                        });
                        // Update model on change, i.e. copy/pasted text, plugins altering content
                        ed.on('SetContent', function(e) {
                            if (!e.initial && ngModel.$viewValue !== e.content) {
                                ed.save();
                                updateView();
                            }
                        });
                        ed.on('blur', function(e) {
                            elm.blur();
                        });
                        // Update model when an object has been resized (table, image)
                        ed.on('ObjectResized', function(e) {
                            ed.save();
                            updateView();
                        });
                        if (configSetup) {
                            configSetup(ed);
                        }
                    },
                    mode: 'exact',
                    elements: attrs.id
                };

                // extend options with initial uiTinymceConfig and options from directive attribute value
                angular.extend(options, uiTinymceConfig, expression);
                setTimeout(function() {
                    tinymce.init(options);
                });

                ngModel.$render = function() {
                    if (!tinyInstance) {
                        tinyInstance = tinymce.get(attrs.id);
                    }
                    if (tinyInstance) {
                        tinyInstance.setContent(ngModel.$viewValue || '');
                    }
                };

                scope.$on('$destroy', function() {
                    if (!tinyInstance) {
                        tinyInstance = tinymce.get(attrs.id);
                    }
                    if (tinyInstance) {
                        tinyInstance.remove();
                        tinyInstance = null;
                    }
                });
            }
        };
    }]);

/**
 * @link https://gist.github.com/IlanFrumer/8281567
 */
app.directive("uiSrefParams", ['$state', function($state) {
        return {
            link: function(scope, elm, attrs) {
                var params;
                params = scope.$eval(attrs.uiSrefParams);
                return elm.bind("click", function(e) {
                    var button;
                    if (!angular.equals($state.params, params)) {
                        button = e.which || e.button;
                        if ((button === 0 || button === 1) && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                            scope.$evalAsync(function() {
                                return $state.go(".", params);
                            });
                            return e.preventDefault();
                        }
                    }
                });
            }
        };
    }]);

/* copied from angular-ui-router */
function parseStateRef(ref) {
    var parsed = ref.replace(/\n/g, " ").match(/^([^(]+?)\s*(\((.*)\))?$/);
    if (!parsed || parsed.length !== 4)
        throw new Error("Invalid state ref '" + ref + "'");
    return {state: parsed[1], paramExpr: parsed[3] || null};
}

/**
 * build href relative to the current url (instead of state at time of linking)
 * in addition, href is updated when the state changes, so that e.g. language is updated in all links.
 *
 * adapted from ui-sref from angular-ui-router
 */
app.directive("uiRelSref", ['$state', '$timeout', function($state, $timeout) {
        return {
            restrict: 'A',
            require: '?^uiSrefActive',
            link: function(scope, element, attrs, uiSrefActive) {
                var ref = parseStateRef(attrs.uiRelSref);
                var params = null, url = null;

                var update = function(newVal) {
                    if (newVal) {
                        params = newVal;
                    }
                    var newHref = $state.href(ref.state, params, {relative: $state.$current});
                    if (uiSrefActive) {
                        uiSrefActive.$$setStateInfo(ref.state, params);
                    }
                    if (!newHref) {
                        return false;
                    }
                    element[0].href = newHref;
                };

                if (ref.paramExpr) {
                    scope.$watch(ref.paramExpr, function(newVal, oldVal) {
                        if (newVal !== params) {
                            update(newVal);
                        }
                    }, true);
                    params = scope.$eval(ref.paramExpr);
                }

                scope.$root.$on('$stateChangeSuccess', function() {
                    update();
                });

                update();

                element.bind("click", function(e) {
                    var button = e.which || e.button;
                    if ((button === 0 || button == 1) && !e.ctrlKey && !e.metaKey && !e.shiftKey && !element.attr('target')) {
                        // HACK: This is to allow ng-clicks to be processed before the transition is initiated:
                        $timeout(function() {
                            $state.go(ref.state, params, {relative: $state.$current});
                        });
                        e.preventDefault();
                    }
                });
            }
        };
    }]);


/**
 * @link http://stackoverflow.com/a/17472118/1961666
 */
app.directive('ngEnter', function() {
    return function(scope, element, attrs) {
        element.bind("keydown keypress", function(event) {
            if (event.which === 13) {
                scope.$apply(function() {
                    scope.$eval(attrs.ngEnter);
                });

                event.preventDefault();
            }
        });
    };
});



/**
 * Password checker
 */
app.directive('pwCheck', ['$stateParams', '$rootScope', '$injector', function($stateParams, $rootScope, $injector) {

        var alpha_lower = 'abcdefghijklmnopqrstuvwxyz';
        var alpha_upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var digits = '1234567890';
        var shift_digit = '!@#$%^&*()';
        var punctuation = '`~,<.>/?;:\'"[{]}-_=+\\|';

        var calculateEntropy = function()
        {
            // inject optional translator service
            if ($injector.has('translatorService'))
            {
                var translatorService = $injector.get('translatorService');
            }

            var elem = $(this);
            var value = elem.val();
            var pwmeter = elem.next('.pwmeter');

            pwmeter.toggle(!!value);

            var count_al = 0;
            var count_au = 0;
            var count_d = 0;
            var count_sd = 0;
            var count_p = 0;
            var count_o = 0;

            for (var i = 0; i < value.length; i++)
            {
                var c = value.charAt(i);
                if (alpha_lower.indexOf(c) >= 0)
                {
                    count_al++;
                }
                else if (alpha_upper.indexOf(c) >= 0)
                {
                    count_au++;
                }
                else if (digits.indexOf(c) >= 0)
                {
                    count_d++;
                }
                else if (shift_digit.indexOf(c) >= 0)
                {
                    count_sd++;
                }
                else if (punctuation.indexOf(c) >= 0)
                {
                    count_p++;
                }
                else
                {
                    count_o++;
                }
            }

            var approx_entropy = 0;
            for (i = 0; i < Math.max(count_al, count_au, count_d, count_sd, count_p, count_o); i++)
            {
                var range = 0;
                c = 0;
                if (i < count_al)
                {
                    c++;
                    range += 26;
                }
                if (i < count_au)
                {
                    c++;
                    range += 26;
                }
                if (i < count_d)
                {
                    c++;
                    range += 10;
                }
                if (i < count_sd)
                {
                    c++;
                    range += 10;
                }
                if (i < count_p)
                {
                    c++;
                    range += 22;
                }
                if (i < count_o)
                {
                    c++;
                    range += 128;
                }

                approx_entropy += c * Math.log(range);
            }

            approx_entropy /= Math.log(2);

            if (approx_entropy > 120)
            {
                pwmeter.find('.pwrating').html(translatorService ? translatorService.translate('very strong') : 'very strong');
                pwmeter.find('.pwgauge').css('background-color', 'green');
            }
            else if (approx_entropy > 60)
            {
                pwmeter.find('.pwrating').html(translatorService ? translatorService.translate('strong') : 'strong');
                pwmeter.find('.pwgauge').css('background-color', '#5a0');
            }
            else if (approx_entropy > 45)
            {
                pwmeter.find('.pwrating').html(translatorService ? translatorService.translate('good') : 'good');
                pwmeter.find('.pwgauge').css('background-color', '#8d0');
            }
            else if (approx_entropy > 20)
            {
                pwmeter.find('.pwrating').html(translatorService ? translatorService.translate('weak') : 'weak');
                pwmeter.find('.pwgauge').css('background-color', 'orange');
            }
            else
            {
                pwmeter.find('.pwrating').html(translatorService ? translatorService.translate('very weak') : 'very weak');
                pwmeter.find('.pwgauge').css('background-color', 'red');
            }
            pwmeter.find('.pwgauge').height(5).width(Math.max(10, Math.min(100, Math.round(approx_entropy / 1.2))) + '%');
        };

        return {
            require: 'ngModel',
            link: function(scope, elem, attrs, ctrl) {
                /* remove language dependence. */
                elem.after($('<div class="pwmeter"><span class="pwrating">weak</span><div class="pwgauge"></div></div>').width(elem.width()).hide());
                elem.on('keyup', calculateEntropy);
            }
        };
    }]);

/**
 * the main difference with the laterniative below is that we explicitly use
 * the model en view values, and that if you input the placeholder value it
 * doesn't get cleared when you focus on it.
 * @TODO decide whether that small benefit and the added complication is worth it.
 *
 app.directive('placeholder', function() {
 if ( Modernizr.input.placeholder === true ) return {};

 return {
 restrict: 'A',
 require: 'ngModel',
 link: function(scope, element, attrs, ngModelCtrl) {
 var placeholder = attrs.placeholder || '';

 attrs.$observe('placeholder', function(val){ placeholder = val; });

 ngModelCtrl.$formatters.push(function(val)
 {
 return val || placeholder;
 });

 element.bind('focus', function()
 {
 if (! ngModelCtrl.$modelValue)
 {
 ngModelCtrl.$viewValue = '';
 ngModelCtrl.$render();
 }
 });
 element.bind('blur', function()
 {
 if (! ngModelCtrl.$modelValue)
 {
 ngModelCtrl.$viewValue = placeholder;
 ngModelCtrl.$render();
 }
 });
 }
 };
 });

 */

/*
 * Placeholder for non HTML5 browsers
 */
app.directive("placeholder", ['$timeout', function($timeout) {
        if (Modernizr.input.placeholder === true)
            return {};

        return {
            restrict: "A",
            link: function(scope, elem, attrs) {
                var txt = attrs.placeholder;

                elem.bind("focus", function() {
                    if (elem.val() === txt) {
                        elem.val("");
                    }
                    scope.$apply();
                });

                elem.bind("blur", function() {
                    if (elem.val() === "") {
                        elem.val(txt);
                    }
                    scope.$apply();
                });

                // Initialise placeholder
                $timeout(function() {
                    elem.val(txt);
                    scope.$apply();
                });
            }
        };
    }]);

/**
 * Directive for setting default value for a model with the value/content from
 * an input/textarea element.
 * $observe/$watch the value for change, and update when appropriate.
 * $watch does not work on attribute that contains interpolation (i.e., {{}}'s), so i changed it to: attrs.$observe('placeholder',function(value) {
 * @TODO maybe add dirty-check, because if user dirties the variable we don't
 * want to reset to the default if it changes. (Not an issue for current usage)
 */
app.directive('setDefault', ['$parse', function($parse) {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function(scope, element, attrs) {
                if (attrs.ngModel)
                {
                    if (element.prop("tagName") === 'INPUT')
                    {
                        attrs.$observe('setDefault', function(val) {
                            $parse(attrs.ngModel).assign(scope, val);
                        });

                    }
                    else if (element.prop("tagName") === 'TEXTAREA')
                    {
                        attrs.$observe('placeholderedit', function(value) {
                           $parse(attrs.ngModel).assign(scope, value);
                        });

                        attrs.$observe('placeholder', function(value) {
                            return value;
                        }, function() {
                            $parse(attrs.ngModel).assign(scope, value);
                        });

                    }
                }
            }
        };
    }]);


/**
 * Directive for opening a colorbox with the add-provider page.
 */
app.directive('colorboxproviders', ['$http', '$compile', '$rootScope', 'msgService', function($http, $compile, $rootScope, msgService) {
        return {
            restrict: 'AC',
            link: function(scope, element, attrs) {
                element.on("click", function(e) {
                    e.preventDefault();
                    $.colorbox({
                        href: '/provider/add-provider',
                        iframe: 'true',
                        innerWidth: '430',
                        innerHeight: '222',
                        onClosed: function() {
                            msgService.inform("Please wait while site is updating.", 'info');
                        }
                    });
                });
            }
        };
    }]);

/*
 * Check passwords on equality.
 *
 * http://stackoverflow.com/questions/14012239/password-check-directive-in-angularjs
 */
app.directive("passwordVerify", function() {
    return {
        require: "ngModel",
        scope: {
            passwordVerify: '='
        },
        link: function(scope, element, attrs, ctrl) {
            scope.$watch(function() {
                var combined;
                if (scope.passwordVerify || ctrl.$viewValue) {
                    combined = scope.passwordVerify + '_' + ctrl.$viewValue;
                }
                return combined;
            }, function(value) {
                if (value) {
                    /**
                     * This function is added to the list of the $parsers.
                     * It will be executed if the DOM (the view value) change.
                     * Array.unshift() put it in the beginning of the list, so
                     * it will be executed before all the other
                     */
                    ctrl.$parsers.unshift(function(viewValue) {

                        var origin = scope.passwordVerify;
                        if (origin !== viewValue) {
                            ctrl.$setValidity("passwordVerify", false); // Tell the controlller that the value is invalid
                            return undefined;                           // When the value is invalid, we should return `undefined`, as asked by the documentation
                        } else {
                            ctrl.$setValidity("passwordVerify", true);  // Tell the controlller that the value is valid
                            return viewValue;                           // Return this value (it will be put into the model)
                        }
                    });
                }
            });
        }
    };
});

app.directive('totop', [function() {
		return {
			restrict: 'A',
			replace: false,
			link: function(scope, element, attrs) {
				element.bind('click', function(){
					scope.$on('$destroy', function(){ /* @TODO make it work more generally */
						window.scrollTo(0,0);
					});
				});
			}
		};
	}]);

function timeoutUntil(pred, exec, time) {
	if (pred())
		exec();
	else
		setTimeout(
			(function(pred, exec, time) { return function() {
					timeoutUntil(pred, exec, time);
			};})(pred, exec, time), time
		);
};

app.factory('$FB',['$window', function($window){
  return {
    init: function(fbId){
      if(fbId){
        this.fbId = fbId;
        $window.fbAsyncInit = function() {
          FB.init({
            appId: fbId,
            channelUrl: 'app/channel.html',
            status: true,
            xfbml: true
          });
        };
        (function(d){
          var js,
          id = 'facebook-jssdk',
          ref = d.getElementsByTagName('script')[0];
          if (d.getElementById(id)) {
            return;
          }

          js = d.createElement('script');
          js.id = id;
          js.async = true;
          js.src = "//connect.facebook.net/en_US/all.js";

          ref.parentNode.insertBefore(js, ref);

        }(document));
      }
      else{
        throw("FB App Id Cannot be blank");
      }
    }
  };
}]).directive('facebook', ['$timeout','$http', 'translatorService', function($timeout,$http, $translatorService) {
  return {
    scope: {
      shares: '='
    },
    transclude: true,
    link: function(scope, element, attr) {
			var canonicalURLTester =
				function(scope) {
					return function() {
						return scope.$root.page.canonicalUrl !== undefined;
					};
				};
			var facebookButtonCreator =
				function(scope, element) {
					return function() {
						var url = scope.$root.page.canonicalBaseUrl + '/' + scope.$root.lang + '/' + scope.$root.page.url;
						var ifr = $('<iframe src="//www.facebook.com/plugins/share_button.php?href=' +
							encodeURIComponent(url) + '&amp;' +
							'layout=button" scrolling="no" frameborder="0" ' +
							'style="border:none; overflow:hidden;" allowTransparency="true" height="20" width="57"></iframe>'
						);
						$(element).append(ifr);
					};
				};
				
			// The scope variable is initialized in the controller of the 'page' state, however it is impossible to trigger the rendering of a directive after this is initialized.
			// hence we just wait untill it is in steps of 100ms. Ugly, but it works.
			timeoutUntil(canonicalURLTester(scope), facebookButtonCreator(scope,element), 100);
		}
  };
}]).directive('twitter', ['$timeout', '$window', 'translatorService', function($timeout, $window, $translatorService) {
  return {
    link: function(scope, element, attr) {
			var twitterButtonCreator =
				function(scope, element) {
					return function() {
						var firstname = scope.$root.page.firstname;
						var lastname = scope.$root.page.lastname;
						var txt = sprintf($translatorService.translate('View the memorial page of %s %s and join us in sharing memories and condolences.'), firstname, lastname);
						var url = scope.$root.page.canonicalBaseUrl + scope.$root.lang + (scope.$root.lang !== '' ? '/' : '') + scope.$root.page.url;
						twttr.widgets.createShareButton(
							url, element[0], { count: 'none', text: txt }
						);
					};
				};
			var canonicalURLAndTwitterTester =
				function(scope) {
					return function() {
						return scope.$root.page.canonicalUrl !== undefined && twttr.widgets !== undefined;
					};
				};
			
			// The scope variable is initialized in the controller of the 'page' state, however it is impossible to trigger the rendering of a directive after this is initialized.
			// hence we just wait untill it is in steps of 100ms. Ugly, but it works.
			timeoutUntil(canonicalURLAndTwitterTester(scope), twitterButtonCreator(scope, element), 100);
		}
  };
}]).directive('headerReadMore',  ['$rootScope', function($rootScope) {
    return {
        link: function(scope, element, attrs) {
            $rootScope.$on('$stateChangeSuccess', function() {
                $rootScope.ui.readmore = false;
            });
            
            $("div > ul > li, > ul > li", element).removeClass('selected');
            var on_child_menu = false;
            var on_menu = false;
            
            if(!('ontouchstart' in window) && !(navigator.msMaxTouchPoints)) {
                $("div > ul > li, > ul > li", element).bind('mouseenter', function() {
                    //console.log("enter");
                    on_menu = true;
                    $("div > ul > li,  > ul > li", element).removeClass('selected');
                    $(this).addClass('selected');
                });
                $("div > ul > li, > ul > li", element).bind('mouseleave', function() {
                    //console.log("leave");
                    on_menu = false;
                    if(!on_child_menu)
                        $(this).removeClass('selected');
                });
                $("div > ul > li > ul, > ul > li > ul", element).bind('mouseleave', function(event) {
                    //console.log("leave 2");
                    on_child_menu = false;
                    if(!on_menu)
                        $($(this).parent()).removeClass('selected');
                });
                $("div > ul > li > ul, > ul > li > ul", element).bind('mouseenter', function(event) {
                    //console.log("enter 2");
                    on_child_menu = true;
                });
            }
            $("div > ul > li, > ul > li", element).bind('click', function() {
                //console.log("click");
                var is_selected = $(this).hasClass('selected');
                $("div > ul > li,  > ul > li", element).removeClass('selected');
                if(is_selected)
                    $(this).removeClass('selected');
                else
                    $(this).addClass('selected');
                event.stopPropagation();
            });
            $("div > ul > li > ul, > ul > li > ul", element).bind('click', function(event) {
                //console.log("click 2");
                $($(this).parent()).removeClass('selected');
                $rootScope.ui.readmore = false;
                event.stopPropagation();
            });
        }
    };
}]);