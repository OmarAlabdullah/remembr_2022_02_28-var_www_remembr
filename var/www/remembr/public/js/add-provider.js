var idp = null;

function start_auth(params) {

    var start_url = "/provider/add-provider" + params;

    var w = 800;
    var h = 500;
    var left = (screen.width / 2) - (w / 2);
    var top = (screen.height / 2) - (h / 2);

    var win = window.open(
        start_url,
        "hybridauth_social_sign_on",
        "location=0, status=0, scrollbars=0, width = " + w + ", height = " + h + ", top = " + top + ", left = " + left
    );

    // if IE and Vista... @TODO: check for IE > 9
    if (navigator.appName === 'Microsoft Internet Explorer' && navigator.appVersion.indexOf("Windows NT 6.0") !== -1) {
        var timer = setInterval(function() {
            if (win.closed) {
                clearInterval(timer);
                $('body').scope().user.$get();
            }
        }, 1000);
    }

}


ZfUserApi =
        {
            close: function()
            {
                try {
                    $.colorbox.close();
                } catch (err) {

                }
            },
            finishlogin: function(url)
            {
				if ($("#managementCtrl-div").length) {
					// if user added social media, reload those in view
					$('body').scope().user.$get().then(function() {
                        angular.element("#managementCtrl-div").scope().reloadSocialMedia();
					});
				} else if ($("#LoginCtrl-div").length) {
					// if user did login by social media, load dashboard
					angular.element("#LoginCtrl-div").scope().postLoginRedirect();
				}
            },
            faillogin: function(url)
            {
                document.location.href = url;
            }
        };