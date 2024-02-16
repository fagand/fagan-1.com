/*
	Hyperspace by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
*/
function changeWindowScheme() {
    window.matchMedia('(prefers-color-scheme: dark)').addListener(function (e) {
        console.log("User has changed colour scheme");
        $('.spotlights > section')
            .each(function () {
                var dm = "dark"; //Variable to store the word dark to add to url. Had to create a darkimages folder.
                var $this = $(this),
                    $image = $this.find('.image'),
                    $img = $image.find('img'),
                    x;
                var src = $img.attr('src');
                var blurred = src.search("blurred");
                // Find position to change url.
                var pos = blurred + 8;
                // Extract image path from URL.
                var sliced = src.slice(pos).replace(/"/g, "");
                // Create new URL with path to larger file size image.
                var newUrl = "images/siteimages/" + sliced;

                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {

                    // Assign image.
                    $image.css('background-image', 'url(' + dm + newUrl + ')');

                    // Set background position.
                    if (x = $img.data('position'))
                        $image.css('background-position', x);

                    // Hide <img>.
                    $img.hide();
                } else {
                    // Assign image.
                    $image.css('background-image', 'url(' + newUrl + ')');

                    // Set background position.
                    if (x = $img.data('position'))
                        $image.css('background-position', x);

                    // Hide <img>.
                    $img.hide();
                }
            });
    })
}
(function ($) {
    changeWindowScheme();
    var $window = $(window),
        $body = $('body'),
        $sidebar = $('#sidebar');

    // Breakpoints.
    breakpoints({
        xlarge: ['1281px', '1680px'],
        large: ['981px', '1280px'],
        medium: ['737px', '980px'],
        small: ['481px', '736px'],
        xsmall: [null, '480px']
    });

    // Hack: Enable IE flexbox workarounds.
    if (browser.name == 'ie')
        $body.addClass('is-ie');

    // Play initial animations on page load.
    $window.on('load', function () {
        window.setTimeout(function () {
            $body.removeClass('is-preload');
        }, 100);
    });

    // Forms.

    // Hack: Activate non-input submits.
    $('form').on('click', '.submit', function (event) {

        // Stop propagation, default.
        event.stopPropagation();
        event.preventDefault();

        // Submit form.
        $(this).parents('form').submit();

    });

    // Sidebar.
    if ($sidebar.length > 0) {

        var $sidebar_a = $sidebar.find('a');

        $sidebar_a
            .addClass('scrolly')
            .on('click', function () {

                var $this = $(this);

                // External link? Bail.
                if ($this.attr('href').charAt(0) != '#')
                    return;

                // Deactivate all links.
                $sidebar_a.removeClass('active');

                // Activate link *and* lock it (so Scrollex doesn't try to activate other links as we're scrolling to this one's section).
                $this
                    .addClass('active')
                    .addClass('active-locked');

            })
            .each(function () {

                var $this = $(this),
                    id = $this.attr('href'),
                    $section = $(id);

                // No section for this link? Bail.
                if ($section.length < 1)
                    return;

                // Scrollex.
                $section.scrollex({
                    mode: 'middle',
                    top: '-20vh',
                    bottom: '-20vh',
                    initialize: function () {

                        // Deactivate section.
                        $section.addClass('inactive');

                    },
                    enter: function () {

                        // Activate section.
                        $section.removeClass('inactive');

                        // No locked links? Deactivate all links and activate this section's one.
                        if ($sidebar_a.filter('.active-locked').length == 0) {

                            $sidebar_a.removeClass('active');
                            $this.addClass('active');

                        }

                        // Otherwise, if this section's link is the one that's locked, unlock it.
                        else if ($this.hasClass('active-locked'))
                            $this.removeClass('active-locked');

                    }
                });

            });

    }

    // Scrolly.
    $('.scrolly').scrolly({
        speed: 1000,
        offset: function () {

            // If <=large, >small, and sidebar is present, use its height as the offset.
            if (breakpoints.active('<=large') &&
                !breakpoints.active('<=small') &&
                $sidebar.length > 0)
                return $sidebar.height();

            return 0;

        }
    });

    // Spotlights.
    $('.spotlights > section')
        .scrollex({
            mode: 'middle',
            top: '-10vh',
            bottom: '-10vh',
            initialize: function () {

                // Deactivate section.
                $(this).addClass('inactive');

            },
            enter: function () {

                // Activate section.
                $(this).removeClass('inactive');

            }
        })
        .each(function () {
            var dm = "dark"; //Variable to store the word dark to add to url. Had to create a darkimages folder.
            var $this = $(this),
                $image = $this.find('.image'),
                $img = $image.find('img'),
                x;

            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {

                // Assign image.
                $image.css('background-image', 'url(' + dm + $img.attr('src') + ')');

                // Set background position.
                if (x = $img.data('position'))
                    $image.css('background-position', x);

                // Hide <img>.
                $img.hide();
            } else {
                // Assign image.
                $image.css('background-image', 'url(' + $img.attr('src') + ')');

                // Set background position.
                if (x = $img.data('position'))
                    $image.css('background-position', x);

                // Hide <img>.
                $img.hide();
            }
        });

    // Features.
    $('.features')
        .scrollex({
            mode: 'middle',
            top: '-20vh',
            bottom: '-20vh',
            initialize: function () {

                // Deactivate section.
                $(this).addClass('inactive');

            },
            enter: function () {

                // Activate section.
                $(this).removeClass('inactive');

            }
        });

    // Code for new photo viewer 

    $main = $('#main'),

        settings = {

            // Keyboard shortcuts.
            keyboardShortcuts: {

                // If true, enables scrolling via keyboard shortcuts.
                enabled: true,

                // Sets the distance to scroll when using the left/right arrow keys.
                distance: 50

            }
        };

    // Items.

    // Assign a random "delay" class to each thumbnail item.
    $('.item.thumb').each(function () {
        $(this).addClass('delay-' + Math.floor((Math.random() * 6) + 1));
    });

    // IE: Fix thumbnail images.
    if (browser.name == 'ie')
        $('.item.thumb').each(function () {

            var $this = $(this),
                $img = $this.find('img');

            $this
                .css('background-image', 'url(' + $img.attr('src') + ')')
                .css('background-size', 'cover')
                .css('background-position', 'center');

            $img
                .css('opacity', '0');

        });

    //    // Poptrox.
    //    $main.poptrox({
    //        onPopupOpen: function () {
    //            $body.addClass('is-poptrox-visible');
    //        },
    //        onPopupClose: function () {
    //            $body.removeClass('is-poptrox-visible');
    //        },
    //        overlayColor: '#1a1f2c',
    //        overlayOpacity: 0.75,
    //        popupCloserText: '',
    //        popupLoaderText: '',
    //        selector: '.item.thumb a.pimage',
    //        caption: function ($a) {
    //            return $a.attr('title');
    //        },
    //        usePopupDefaultStyling: false,
    //        usePopupCloser: false,
    //        usePopupCaption: true,
    //        usePopupNav: true,
    //        windowMargin: 50
    //    });
    //
    //    breakpoints.on('>small', function () {
    //        $main[0]._poptrox.windowMargin = 50;
    //    });
    //
    //    breakpoints.on('<=small', function () {
    //        $main[0]._poptrox.windowMargin = 0;
    //    });

    //    // Keyboard shortcuts.
    //    if (settings.keyboardShortcuts.enabled)
    //        (function () {
    //
    //            $window
    //
    //                // Keypress event.
    //                .on('keydown', function (event) {
    //
    //                    var scrolled = false;
    //
    //                    if ($body.hasClass('is-poptrox-visible'))
    //                        return;
    //
    //                    switch (event.keyCode) {
    //
    //                        // Left arrow.
    //                        case 37:
    //                            $main.scrollLeft($main.scrollLeft() - settings.keyboardShortcuts.distance);
    //                            scrolled = true;
    //                            break;
    //
    //                            // Right arrow.
    //                        case 39:
    //                            $main.scrollLeft($main.scrollLeft() + settings.keyboardShortcuts.distance);
    //                            scrolled = true;
    //                            break;
    //
    //                            // Page Up.
    //                        case 33:
    //                            $main.scrollLeft($main.scrollLeft() - $window.width() + 100);
    //                            scrolled = true;
    //                            break;
    //
    //                            // Page Down, Space.
    //                        case 34:
    //                        case 32:
    //                            $main.scrollLeft($main.scrollLeft() + $window.width() - 100);
    //                            scrolled = true;
    //                            break;
    //
    //                            // Home.
    //                        case 36:
    //                            $main.scrollLeft(0);
    //                            scrolled = true;
    //                            break;
    //
    //                            // End.
    //                        case 35:
    //                            $main.scrollLeft($main.width());
    //                            scrolled = true;
    //                            break;
    //
    //                    }
    //
    //                    // Scrolled?
    //                    if (scrolled) {
    //
    //                        // Prevent default.
    //                        event.preventDefault();
    //                        event.stopPropagation();
    //
    //                        // Stop link scroll.
    //                        $main.stop();
    //
    //                    }
    //
    //                });
    //
    //        })();

})(jQuery);
