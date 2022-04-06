// sorting on all headers, so we need only one settings  https://mottie.github.io/tablesorter/docs/example-options-headers.html
$(document).ready(function()
{
    $("#adminTable")
            .tablesorter({
        theme: 'remembr',
		//debug: true,
        widthFixed: true,
        widgets: ['zebra']

    })
    .tablesorterPager({
        container: $("#pager"),
        size: 10
    });

});