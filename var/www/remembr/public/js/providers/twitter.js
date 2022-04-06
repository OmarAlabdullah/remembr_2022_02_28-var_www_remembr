$(document).ready(function() {
    var username = $("#tweets").data("username");
    var url = "/th-user/ajax/get-tweets";

    $.ajax({
        type: "GET",
        url: url,
        datatype: "json",
        success: function(response) {
            var data = response.tweets;
            var $tweets = $('#tweets');
            $tweets.empty();
            if (data.length !== 0) {
                $.each(data, function(i, tweet)
                {
                    if (tweet.text !== undefined) {
                        $tweets.append($('<li></li>', {text: tweet.text}));
                    }
                });
            } else {
                $tweets.append($('<li></li>', {text: 'No recent tweets'}));
            }
            $('#tweets').linkify();

        },
        error: function() {
            console.log('Ajax error');
        }
    });
});


// Define: Linkify plugin
(function($) {

    var url1 = /(^|&lt;|\s)(www\..+?\..+?)(\s|&gt;|$)/g,
            url2 = /(^|&lt;|\s)(((https?|ftp):\/\/|mailto:).+?)(\s|&gt;|$)/g,
            linkifyThis = function() {
        var childNodes = this.childNodes,
                i = childNodes.length;
        while (i--)
        {
            var n = childNodes[i];
            if (n.nodeType == 3) {
                var html = $.trim(n.nodeValue);
                if (html)
                {
                    html = html.replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(url1, '$1<a href="http://$2">$2</a>$3')
                            .replace(url2, '$1<a href="$2">$2</a>$5');
                    $(n).after(html).remove();
                }
            }
            else if (n.nodeType == 1 && !/^(a|button|textarea)$/i.test(n.tagName)) {
                linkifyThis.call(n);
            }
        }
    };

    $.fn.linkify = function() {
        return this.each(linkifyThis);
    };

})(jQuery);