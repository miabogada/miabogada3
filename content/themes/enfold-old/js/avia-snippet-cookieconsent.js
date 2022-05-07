(function($) {

    "use strict";

    $(document).ready(function() {

        if (! aviaGetCookie('aviaCookieConsent')){
            $('.avia-cookie-consent').removeClass('cookiebar-hidden');
        }

        $('#avia_cookie_consent').on('click', function() {

            var cookieContents = $(this).attr('data-contents');
            aviaSetCookie('aviaCookieConsent',cookieContents,60);

            $('.avia-cookie-consent').addClass('cookiebar-hidden');
        });


        function aviaSetCookie(CookieName,CookieValue,CookieDays) {
            if (CookieDays) {
                var date = new Date();
                date.setTime(date.getTime()+(CookieDays*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            }
            else var expires = "";
            document.cookie = CookieName+"="+CookieValue+expires+"; path=/";
        }


        function aviaGetCookie(CookieName) {
            var docCookiesStr = CookieName + "=";
            var docCookiesArr = document.cookie.split(';');

            for(var i=0; i < docCookiesArr.length; i++) {
                var thisCookie = docCookiesArr[i];

                while (thisCookie.charAt(0)==' ') {
                    thisCookie = thisCookie.substring(1,thisCookie.length);
                }
                if (thisCookie.indexOf(docCookiesStr) == 0) {
                    var cookieContents = $('#avia_cookie_consent').attr('data-contents');
                    var savedContents = thisCookie.substring(docCookiesStr.length,thisCookie.length);
                    if (savedContents == cookieContents) {
                        return savedContents;
                    }
                }
            }
            return null;
        }

    });

})( jQuery );
