(function() {
    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.async = true;
    po.src = 'https://apis.google.com/js/client:plusone.js?onload=OnLoadCallback';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
})();

function OnLoadCallback() {
    sessionParams = {
        'client_id': '424527529673-b4837v994tc8n7h9nm0rsb6pbm2t6frl.apps.googleusercontent.com',
        'session_state': null
    };

    gapi.auth.checkSessionState(sessionParams, function(stateMatched) {
        if (stateMatched == true) {
            // not logged in
            window.location = '/provider/reset-provider/google';   // and login again
        } else {
            // logged in

        }
    });
}
