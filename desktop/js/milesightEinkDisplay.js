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

const ajaxUrl = 'plugins/milesightEinkDisplay/core/ajax/milesightEinkDisplay.ajax.php';

// callPluginAjax = function (_params) {
//     $.ajax({
//         async: _params.async == undefined ? true : _params.async,
//         global: false,
//         type: "POST",
//         url: "plugins/milesightEinkDisplay/core/ajax/milesightEinkDisplay.ajax.php",
//         data: _params.data,
//         dataType: 'json',
//         error: function (request, status, error) {
//             handleAjaxError(request, status, error);
//         },
//         success: function (data) {
//             if (data.state != 'ok') {
//                 $.fn.showAlert({message: data.result, level: 'danger'});
//             } else {
//                 if (typeof _params.success === 'function') {
//                     _params.success(data.result);
//                 }
//             }
//         }
//     });
// };

const searchEquipment = function () {
    const brokerId = $('#jmqttBrkSelector').val();
    const parentObjectSelectorId = $('#parentObjectSelector').val();

    if (brokerId === '' || parentObjectSelectorId === '') {
        return;
    }

    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: `action=getEquipments&brokerId=${brokerId}&parentObjectId=${parentObjectSelectorId}`,
        async: false,
        success: function (data) {
            const returnData = JSON.parse(data);
            if (returnData.state !== 'ok') {
                $.fn.showAlert({message: returnData.result, level: 'error'});
                return;
            }
            const eqpts = returnData.result;
            const $equipmentsSelect = $('#equipmentsSelect');
            $equipmentsSelect.empty();
            $.each(eqpts, function (key, obj) {
                $equipmentsSelect.append($('<option>', {
                    value: obj.id,
                    text: obj.name,
                }));
            });
            $equipmentsSelect.multiselect('rebuild');
            $equipmentsSelect.multiselect('selectAll', false);
            $equipmentsSelect.multiselect('refresh');
            tooggleScreenForm();
        },
        cache: false,
        processData: false,
    });
}

const tooggleScreenForm = function (screenInfo) {
    const $equipmentsSelect = $('#equipmentsSelect');
    if ($equipmentsSelect.value()) {
        $('#screenForm').show();
    } else {
        $('#screenForm').hide();
    }
}

$('.eqLogicAction[data-action=updateScreen]').off('click').on('click', function () {
    let dialog_message = '';

    dialog_message += '<form id="ajaxForm">';
    dialog_message += '<div class="row">';
    dialog_message += '<div class="col-md-6">';

    dialog_message += '<label class="control-label">{{Broker utilisé :}}</label> ';
    dialog_message += '<select class="bootbox-input bootbox-input-select form-control" id="jmqttBrkSelector">';
    dialog_message += '<option value="">{{Aucun}}</option>';
    $.each(eqBrokers, function (key, name) {
        dialog_message += '<option value="' + key + '">' + name + '</option>';
    });
    dialog_message += '</select><br/>';

    dialog_message += '</div>'
    dialog_message += '<div class="col-md-6">';
    dialog_message += '<label class="control-label">{{Objet parent}}</label>';
    dialog_message += '<select class="bootbox-input bootbox-input-select form-control" id="parentObjectSelector">';
    dialog_message += '<option value="">{{Aucun}}</option>';
    $.each(objects, function (key, obj) {
        dialog_message += `<option value="${key}">${'&nbsp;'.repeat(obj.parentNumber)} ${obj.name} </option>`;
    });
    dialog_message += '</select><br/>';
    dialog_message += '</div>'

    dialog_message += '</div>';


    // dialog_message += '<label class="control-label">{{Equipement}}</label>';
    // dialog_message += '<select class="bootbox-input bootbox-input-select form-control" name="eqLogic">';
    // $.each(jMQTTEqpts, function (key, name) {
    //     dialog_message += '<option value="' + key + '">' + name + '</option>';
    // });
    // dialog_message += '</select><br/>';

    dialog_message += `
    <div class="row">
        <div class="col-md-12">
            <label class="control-label">{{Equipements}}</label>
            <select id="equipmentsSelect" multiple="multiple"></select>
        </div>
    </div>
    <br/>
    `;

    dialog_message += '<div id="screenForm" style="display: none;">';

    dialog_message += `
        <div class="row">
            <div class="form-group col-md-3">
                <input class="form-check-input" type="radio" name="template" id="template1" value="1" checked>
                <label class="form-check-label" for="template2">Template 1</label>
            </div>
            <div class="form-group col-md-3">
                <input class="form-check-input" type="radio" name="template" id="template2" value="2" >
                <label class="form-check-label" for="template1">Template 2</label>
            </div>
        </div>
        <br/>
        `;

    // add fields TEXT 1 to 9
    for (let i = 1; i <= 9; i++) {
        if (i % 3 === 1) {
            dialog_message += '<div class="row">';
        }
        dialog_message += '<div class="col-md-4">';
        dialog_message += `<label for="text_${i}" class="control-label">Texte ${i}</label>`;
        dialog_message += `<input type="text" class="bootbox-input bootbox-input-text form-control" autocomplete="nope" id="text_${i}" name="text_${i}" >`;
        dialog_message += '</div>';
        if (i % 3 === 0) {
            dialog_message += '</div>';
        }
    }
    if (9 % 3 !== 0) {
        dialog_message += '</div>';
    }
    dialog_message += `
            <div class="row">
                <div class="col-md-4">
                    <label class="control-label" for="text_10">Texte 10</label>
                    <input type="text" class="bootbox-input bootbox-input-file form-control" autocomplete="nope" id="text_10" name="text_10">
                </div>
                <div class="col-md-8">
                    <label class="control-label" for="qrcode">{{QR Code}}</label>
                    <input type="text" class="bootbox-input bootbox-input-text form-control" id="qrcode" name="qrcode">
               </div>
            </div>
    `;

    dialog_message += '</div>';

    dialog_message += '</form>';

    bootbox.confirm({
        title: "{{Mise à jour des écrans}}",
        message: dialog_message,
        buttons: {
            confirm: {
                label: 'Valider',
                className: 'btn-success'
            },
            cancel: {
                label: 'Annuler',
                className: 'btn-danger'
            }
        },
        onShown: function () {
            $('#jmqttBrkSelector, #parentObjectSelector').bind('change', function () {
                searchEquipment();
            });

            tooggleScreenForm();
            $('#equipmentsSelect').multiselect({
                includeSelectAllOption: true,
            });
        },
        callback: function (result) {
            if (!result) {
                return;
            }

            const $acceptButton = $('.bootbox-accept');
            const $cancelButton = $('.bootbox-cancel');
            [$acceptButton, $cancelButton].forEach(button => {
                button.attr('disabled', true);
                button.addClass('disabled');
            });
            $acceptButton.html('{{Envoi en cours...}}');

            const formData = new FormData(document.forms['ajaxForm']);
            formData.append('action', 'updateDisplay');
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                async: false,
                success: function (data) {
                    const returnData = JSON.parse(data);
                    if (returnData.state !== 'ok') {
                        $.fn.showAlert({message: returnData.result, level: 'error'});
                        [$acceptButton, $cancelButton].forEach(button => {
                            button.removeAttr('disabled');
                            button.removeClass('disabled');
                        });
                        $acceptButton.html('Valider');

                        return;
                    }
                    $.fn.showAlert({message: 'Modifications envoyées', level: 'success'});
                },
                cache: false,
                contentType: false,
                processData: false,
            });
        }
    });
});
