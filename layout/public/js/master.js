$(function(){
	 $( window ).on( "load", function() {
		initializeMaster();
		showfooter();
		$(document).keypress(handleKeyPress);

    });
	 $( window ).on( "resize", function() {
		showfooter();
    });

});

function initializeMaster(){
    if($('#cmsArticleId').length ) {
        {
            $.get("../../ajax/article_count/" + $('#cmsArticleId').val() + "?ts=" + Date.now(), function (data) {
            });
        }
    }
}

function showfooter() {
    var marginBottom = $('#rowFooter').height();
    $('body').css('margin-bottom', marginBottom);
}
function submitSearch() {
    var query = $.trim($('#query:visible').val());
    if(query == "")
        return false;
    document.location.href = $('#BASE_URL').val() + "search/" + query;
}

function handleKeyPress(e) {
    var key = e.keyCode || e.which;

    if (key == 13) {
        if ($("#userQuery").is(":focus")) {
            searchUsers();
        }
        else if ($('#query').is(":focus")) {
            submitSearch();
        }
        else if ($('#registeredUserName').is(":focus") || $('#registeredUserPassword').is(":focus")) {
            logIn();
        }
    }
}

// function that returns the cookie by name, used on the article view.
function getCookie(name) {
    var match = document.cookie.match(new RegExp(name + '=([^;]+)'));
    if (match) return match[1];
    return null;
}