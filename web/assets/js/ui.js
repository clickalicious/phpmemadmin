/**
 * phpMemAdmin
 *
 * Namespace: phpmemadmin
 *
 * These functions provide UI tools and helpers.
 *
 * @description phpMemAdmin - JS Tools
 * @file        main.js
 * @author      Benjamin Carl <opensource@clickalicious.de>
 * @copyright   Copyright 2014 - 2015 clickalicious GmbH (i.G.)
 * @version     0.1.0
 */

// Init when jQuery is ready ...
jQuery(document).ready(function() {

    /**
     * All timer elements.
     *
     * @type {*|jQuery|HTMLElement}
     */
    var display;

    /**
     * Starttime
     *
     * @type {int}
     */
    var start;

    /**
     * The active element
     *
     * @type {*|jQuery|HTMLElement}
     */
    var element;

    /**
     * The server time.
     *
     * @type {int}
     */
    var serverTime;

    /**
     * We need the Date.now functionality. Emulated if required.
     */
    if (!Date.now) {
        Date.now = function() { return new Date().getTime(); }
    }

    /**
     * Formats a passed timestamp as dynamic counter counting up.
     *
     * @param int timestamp The timestamp to start from
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    function timeCount(element, timestamp)
    {
        var second   =  1,
            minute   = 60,
            hour     = 60 * minute,
            day      = 24 * hour,
            month    = 30 * day,
            year     = 12 * month,
            rest     = timestamp,
            datetime = [];

        var years = Math.floor(rest / year);
        rest -= (years * year);
        var months  = Math.floor(rest / month);
        rest -= (months * month);
        var days = Math.floor(rest / day);
        rest -= (days * day);
        var hours = Math.floor(rest / hour);
        rest -= (hours * hour);
        var minutes = Math.floor(rest / minute);
        rest -= (minutes * minute);
        var seconds = Math.floor(rest / second);

        if (years > 0) {
            datetime.push(years == 1 ? '1 Y' : years + 'Y');
        }

        if (months > 0) {
            datetime.push(months == 1 ? '1 M' : months + 'M');
        }

        if (days > 0) {
            datetime.push(days == 1 ? '1 d' : days + 'd');
        }

        if (hours > 0) {
            datetime.push(hours == 1 ? '1 h' : hours + 'h');
        }

        if (minutes > 0 || hours > 0) {
            datetime.push(minutes > 1 ? minutes + 'm' : minutes + 'm');
        }

        if (seconds > 0 || minutes > 0 || hours > 0) {
            datetime.push(seconds > 1 ? ((seconds < 10) ? '0' : '') + seconds + 's' : '0' + seconds + 's');
        }

        element.html(datetime.join(' '));
    };

    /**
     * Run Timer - now + continuously ...
     *
     */
    display = $('.timer');
    start   = display.text();

    timeCount(display, start);

    setInterval(function() {
        ++start;
        timeCount(display, start);
    }, 1000);

    /**
     * Run Timer - now + continuously ...
     */
    element    = $('.time');
    serverTime = element.text();
    element.html(new Date(serverTime * 1000).toISOString());

    setInterval(function () {
        ++serverTime;
        element.html(new Date(serverTime * 1000).toISOString());
    }, 1000);

    /**
     * Activate data-table
     */
    $('#storedKeys').dataTable({
        "bFilter": true,
        "bSort": true,
        "dom": '<"top"ilf>rt<"bottom"p><"clear">',
        "responsive": true,
        "columnDefs": [ { "targets": 6, "orderable": false }, { "aTargets": [5], "sType": "numeric" } ],
        "oLanguage": {
            "sEmptyTable": "<span class='glyphicon glyphicon-ban-circle'></span>&nbsp;No data available on Memcached daemon."
        },
        "aoColumns": [
            { sWidth: '27%' },
            { sWidth: '33%' },
            { sWidth: '5%'  },
            { sWidth: '5%'  },
            { sWidth: '5%'  },
            { sWidth: '5%', sSortDataType: 'dom-text', sType: 'numeric' },
            { sWidth: '19%' }
        ]
    });

    /**
     * Init Bootstrap tooltips
     */
    $('.tooltips').tooltip();
});
