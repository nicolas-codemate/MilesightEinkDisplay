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
};

$('.eqLogicAction[data-action=updateScreen]').off('click').on('click', function () {
    let dialog_message = '';
    dialog_message += '<form id="ajaxForm">';
    dialog_message += '<label class="control-label">{{Equipement}}</label>';
    dialog_message += '<select class="bootbox-input bootbox-input-select form-control" name="eqLogic">';
    $.each(jMQTTEqpts, function (key, name) {
        dialog_message += '<option value="' + key + '">' + name + '</option>';
    });
    dialog_message += '</select><br/>';

    dialog_message += `<div class="form-group">
                    <legend class="col-form-label col-sm-2 pt-0">{{Template}}</legend>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="template" id="template1" value="1" checked>
                        <label class="form-check-label" for="template1">
                            Template 1
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="template" id="template2" value="2">
                        <label class="form-check-label" for="template2">
                            Template 2
                        </label>
                    </div>
                </div>`

    // add fields TEXT 1 to 10
    for (let i = 1; i <= 10; i++) {
        if (i % 2 !== 0) {
            dialog_message += '<div class="form-row">';
        }
        dialog_message += '<div class="form-group col-md-6">';
        dialog_message += `<label for="text_${i}" class="control-label">Texte ${i}</label>`;
        dialog_message += `<input type="text" class="bootbox-input bootbox-input-text form-control" autocomplete="nope" id="text_${i}" name="text_${i}" >`;
        dialog_message += '</div>';
        if (i % 2 === 0) {
            dialog_message += '</div>';
        }
    }
    if (10 % 2 !== 0) {
        dialog_message += '</div>';
    }

    dialog_message += `
                <label class="control-label" for="qrcode">{{QR Code}}</label>
                <input type="text" class="bootbox-input bootbox-input-text form-control" id="qrcode" name="qrcode">
        `;

    dialog_message += '</form>'

    bootbox.confirm({
        title: "{{Mise à jour de l'écran}}",
        message: dialog_message,
        callback: function (result) {
            if (result) {
                let formData = new FormData(document.forms['ajaxForm']);
                formData.append('action', 'updateDisplay');
                $.ajax({
                    url: 'plugins/milesightEinkDisplay/core/ajax/milesightEinkDisplay.ajax.php',
                    type: 'POST',
                    data: formData,
                    async: false,
                    success: function (data) {
                        const returnData = JSON.parse(data);
                        if (returnData.state !== 'ok') {
                            $.fn.showAlert({message: returnData.result, level: 'error'});
                            return;
                        }
                        $.fn.showAlert({message: 'Modifications envoyées', level: 'success'});
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                });
            }
        }
    });
});
