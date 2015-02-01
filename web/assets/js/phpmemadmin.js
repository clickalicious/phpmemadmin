/**
 * phpMemAdmin
 *
 * Namespace: phpmemadmin
 *
 * These functions provide some simple UI for CRUD operations on the existing data.
 *
 * @description phpMemAdmin - JS Tools
 * @file        phpmemadmin.js
 * @author      Benjamin Carl <opensource@clickalicious.de>
 * @copyright   Copyright 2014 - 2015 clickalicious GmbH (i.G.)
 * @version     0.1.0
 */

/**
 * Increments a value by key.
 *
 * @param {string} key The key of the value to increment
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function incrementKey(key)
{
    var defaultValue = '1';

    alertify.custom = alertify.extend('custom');

    alertify.prompt('Increment existing value by:', function (e, value) {
        if (e) {
            alertify.custom = alertify.extend('custom');
            alertify.custom('Incrementing value by <b>' + value + '</b>. Please wait ...');
            window.location.hash = '';
            window.location.href = window.location.href.replace('#', '') + '&increment=' + key + '&value=' + value.replace(/ /g, '%20');
        } else {
            alertify.custom('Action canceled.');
        }
    }, defaultValue);
}

/**
 * Decrements a value by key.
 *
 * @param {string} key The key of the value to decrement
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function decrementKey(key)
{
    var defaultValue = '1';

    alertify.custom = alertify.extend('custom');

    alertify.prompt('Decrement existing value by:', function (e, value) {
        if (e) {
            alertify.custom = alertify.extend('custom');
            alertify.custom('Decrementing value by <b>' + value + '</b>. Please wait ...');
            window.location.hash = '';
            window.location.href = window.location.href.replace('#', '') + '&decrement=' + key + '&value=' + value.replace(/ /g, '%20');
        } else {
            alertify.custom('Action canceled.');
        }
    }, defaultValue);
}

/**
 * Appends a string to an existing value by its key.
 *
 * @param {string} key The key of the value to append data to
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function appendKey(key)
{
    var defaultValue = '';

    alertify.custom = alertify.extend('custom');

    alertify.prompt('Append this value to existing value:', function (e, value) {
        if (e) {
            alertify.custom = alertify.extend('custom');
            alertify.custom('Appending value to key <b>' + key + '</b>. Please wait ...');
            window.location.hash = '';
            window.location.href = window.location.href.replace('#', '') + '&append=' + key + '&value=' + value.replace(/ /g, '%20');
        } else {
            alertify.custom('Action canceled.');
        }
    }, defaultValue);
}

/**
 * Prepends a string to an existing value by its key.
 *
 * @param {string} key The key of the value to prepend data to
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function prependKey(key)
{
    var defaultValue = '';

    alertify.custom = alertify.extend('custom');

    alertify.prompt('Prepend this value to existing value:', function (e, value) {
        if (e) {
            alertify.custom = alertify.extend('custom');
            alertify.custom('Prepending value to key <b>' + key + '</b>. Please wait ...');
            window.location.hash = '';
            window.location.href = window.location.href.replace('#', '') + '&prepend=' + key + '&value=' + value.replace(/ /g, '%20');
        } else {
            alertify.custom('Action canceled.');
        }
    }, defaultValue);
}

/**
 * Edits a value.
 *
 * @param {string} key   The key to edit
 * @param {string} value The value to edited
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function editKey(key, value)
{
    alertify.custom = alertify.extend('custom');

    alertify.prompt('Edit current value:', function (e, value) {
        if (e) {
            alertify.custom = alertify.extend('custom');
            alertify.custom('Editing key <b>' + key + '</b>. Please wait ...');
            window.location.hash = '';
            window.location.href = window.location.href.replace('#', '') + '&replace=' + key + '&value=' + value.replace(/ /g, '%20');
        } else {
            alertify.custom('Action canceled.');
        }
    }, value);
}

/**
 * Sets a value.
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function setKey()
{
    var defaultValue = '';
    alertify.custom = alertify.extend('custom');

    alertify.prompt('Key:', function (e, key) {
        if (e && key) {
            alertify.prompt('Value:', function (e, value) {
                if (e) {
                    alertify.custom('Setting key <b>' + key + '</b>. Please wait ...');
                    window.location.hash = '';
                    window.location.href = window.location.href.replace('#', '') + '&set=' + key + '&value=' + value.replace(/ /g, '%20');
                }
            }, '');
        } else if (e && !key) {
            alertify.custom('Can\'t insert empty key.');
        } else {
            alertify.custom('Action canceled.');
        }
    }, '');
}

/**
 * Flushes all keys from server. Be careful cause there is no undo!
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function flushKeys()
{
    alertify.set(
        {
            buttonFocus: "cancel"
        }
    );
    alertify.custom = alertify.extend('custom');

    alertify.confirm(
        '<span class="glyphicon glyphicon-fire red"></span>&nbsp;Are you really want to FLUSH ALL KEYS from server? THIS CAN\'T BE UNDONE!',
        function (e) {
            if (e) {
                alertify.custom = alertify.extend('custom');
                alertify.custom('Flushing all keys. Please wait ...');
                window.location.hash = '';
                window.location.href = window.location.href.replace('#', '') + '&flush=1';
            } else {
                alertify.custom('Action canceled.');
            }
        }
    );
}

/**
 * Deletes a key by key.
 *
 * @param string key The key to delete
 *
 * @author Benjamin Carl <opensource@clickalicious.de>
 */
function deleteKey(key)
{
    alertify.set(
        {
            buttonFocus: "cancel"
        }
    );

    alertify.custom = alertify.extend('custom');

    alertify.confirm(
        '<span class="glyphicon glyphicon-fire red"></span>&nbsp;Are you sure want to DELETE KEY <b>' + key + '</b> ?',
        function (e) {
            if (e) {
                alertify.custom('Deleting key. Please wait ...');
                window.location.hash = '';
                window.location.href = window.location.href.replace('#', '') + '&delete=' + key;
            } else {
                alertify.custom('Action canceled.');
            }
        }
    );
}
