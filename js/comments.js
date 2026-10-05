(function () {
    var selectors = [
        '#commentform input[type="submit"]',
        '#commentform button[type="submit"]',
        '.comment-form input[type="submit"]',
        '.comment-form button[type="submit"]'
    ];
    function attachTracking() {
        for (var i = 0; i < selectors.length; i++) {
            var button = document.querySelector(selectors[i]);
            if (button && !button.hasAttribute('data-umami-event')) {
                button.setAttribute('data-umami-event', 'comment');
            }
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachTracking);
    } else {
        attachTracking();
    }
})();
