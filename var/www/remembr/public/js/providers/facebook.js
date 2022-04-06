/*
 * Facebook scripts
 */
$(document).ready(function() {
    $('body').on('click', '.friendmessage', function(event) {
        event.preventDefault();
        uid = $(this).data('uid');
        FB.ui({
            method: 'send',
            link: 'http://www.colorworld.nl',
            to: uid
        });
    });
});

window.fbAsyncInit = function() {
    // init the FB JS SDK
    FB.init({
        appId: 606734562708978, // App ID from the app dashboard
        channelUrl: 'http://remembr.tgho.nl/channel.html', // Channel file for x-domain comms
        cookie: true, // enable cookies to allow the server to access the session
        status: true, // Check Facebook Login status
        xfbml: true, // Look for social plugins on the page
        oauth: true
    });

    /*
     FB.login(function(response) {
     if (response.authResponse) {
     console.log('Welcome!  Fetching your information.... ');
     FB.api('/me', function(response) {
     console.log('Good to see you, ' + response.name + '.');
     });
     } else {
     console.log('User cancelled login or did not fully authorize.');
     }
     });*/

    FB.getLoginStatus(function(response) {

        if (response.authResponse) {
            console.info("Session exists");
        } else {
            console.info("Session empty");
        }

        if (response.authResponse) {
            // the user is logged in and has authenticated your
            // app, and response.authResponse supplies
            // the user's ID, a valid access token, a signed
            // request, and the time the access token
            // and signed request each expire
            var uid = response.authResponse.userID;
            var accessToken = response.authResponse.accessToken;

            // now it is still possible that this user logs out on facebook and
            // logs in again with another account
            // and then two users can get mixed up
            // so check if that is the case
            $.ajax({
                url: "/th-user/ajax/get-fb-uid",
                type: "post",
                success: function(Huid) {
                    if (Huid === uid)
                    {
                        // ok, same person
                        welcome();
                    }
                    else
                    {
                        // someone else
                        window.location = '/provider/resetfb';   // and login again
                    }
                },
                error: function(response) {
                    console.log('Ajax error');

                }
            });

        } else {
            // the user is logged in to Facebook,
            // but has not authenticated your app
            console.info("Session empty");
            window.location = '/provider/resetfb';   // and login again
        }
    }, true);   // turn cache for response object off to force a roundtrip to Facebook

    function loopObject(object)
    {
        var data = '';
        $.each(object, function(key, value) {
            if (typeof value !== 'object')
            {
                data += '<li>' + key + ' : ' + value + '</li>';
            }
        });

        return data;
    }

    /*
     function getFriends() {
     FB.api('/me/friends', function(response) {
     if (response.data) {
     $.each(response.data, function(index, friend) {
     console.log(friend);
     });
     } else {
     alert("Error!");
     }
     });
     }*/

    function welcome() {
        FB.api('/me', function(response) {
            $('.fbname').html(response.name);

            var userData = '<ul>';
            userData += loopObject(response);

            userData += '</ul>';
            userData += '<div class="clearfix" />';

            $('#userData').html(userData).fadeIn();

            var friendsHtml;

            // get friends
            $.ajax({
                url: "/th-user/ajax/facebookfriends",
                type: "post",
                data: {

                },
                success: function(response) {

                    var friendsData = response.friends;

                    $('.nrfbfriends').html(friendsData.length);

                    friendsHtml = "";
                    friendsHtml += '<ul id="friends">';
                    $.each(friendsData, function(key, friend) {
                        friendsHtml += '<li>';
                        friendsHtml += '<p>' + friend.name + '</p>';
                        friendsHtml += '<a class="friendmessage" data-uid="' + friend.uid + '" href="' + friend.profile_url + '" target="_blank">';
                        friendsHtml += '<img src="' + friend.pict + '"< />';
                        friendsHtml += '</a>';
                        friendsHtml += '</li>';
                    });

                    friendsHtml += '</ul>';
                    friendsHtml += '<div class="clearfix" />';

                    $('#friendsData').html(friendsHtml).fadeIn();
                },
                error: function() {
                    console.log('Ajax error');
                }
            });
        });
    }
};

/*
 APIs used in postLike():
 * Call the Graph API from JS:
 *   https://developers.facebook.com/docs/reference/javascript/FB.api
 * The Open Graph og.likes API:
 *   https://developers.facebook.com/docs/reference/opengraph/action-type/og.likes
 * Privacy argument:
 *   https://developers.facebook.com/docs/reference/api/privacy-parameter
 */
function postLike(objectToLike) {
    $('#result').show();    // display ajax loader
    FB.api(
            'https://graph.facebook.com/me/og.likes',
            'post',
            {
                object: objectToLike
                        // privacy: {'value': 'SELF'}  // remove to share to default friend settings
            },
    function(response) {
        if (!response) {
            alert('Error occurred.');
            $('#result').hide();
        } else if (response.error) {
            document.getElementById('result').innerHTML = 'Error: ' + response.error.message;
        } else {
            document.getElementById('result').innerHTML =
                    '<a href=\"https://www.facebook.com/me/activity/' + response.id + '\">' +
                    'Story created.  ID is ' + response.id + '</a>';
        }
    }
    );
}

// message to a selection of friends
function fbMessage(url)
{
    FB.ui({
        method: 'send',
        link: url
    });

}

function fbPost(pict, pid, title)
{
    FB.ui({
        method: 'feed',
        link: 'http://www.colorworld.nl/single_photo?pid=' + pid,
        picture: pict,
        name: 'ColorWorld - Photo',
        caption: 'One of the many photos on ColorWorld',
        description: title
    },
    // callback example
    function(response) {
        if (response && response.post_id) {
            alert('Post was published.');
            // maybe add some extra ajax action here

        } else {
            alert('Post was not published.');
        }
    });
}

// Load the SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/nl_NL/all.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
