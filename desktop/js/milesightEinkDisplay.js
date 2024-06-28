/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

callPluginAjax = function (_params) {
    $.ajax({
        async: _params.async == undefined ? true : _params.async,
        global: false,
        type: "POST",
        url: "plugins/milesightEinkDisplay/core/ajax/milesightEinkDisplay.ajax.php",
        data: _params.data,
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $.fn.showAlert({message: data.result, level: 'danger'});
            } else {
                if (typeof _params.success === 'function') {
                    _params.success(data.result);
                }
            }
        }
    });
}

$('#submitDisplayUpdate').off('click').on('click', function () {
    console.log('je submit');
    let formData = new FormData(document.forms['sendDisplayUpdate']);
    // Display the values
    // for (const value of formData.values()) {
    //     console.log(value);
    // }

    formData.append('action', 'updateDisplay');

    $.ajax({
        url: 'plugins/milesightEinkDisplay/core/ajax/milesightEinkDisplay.ajax.php',
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
            const returnData = JSON.parse(data);
            if (returnData
                .state !== 'ok') {
                $.fn.showAlert({message: returnData.result, level: 'error'});
                return;
            }
            $.fn.showAlert({message: 'Modifications envoyées', level: 'success'});
        },
        cache: false,
        contentType: false,
        processData: false,
    });

});
