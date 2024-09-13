<?php
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

function sendMessageToUpdateScreen(eqLogic $eqLogic, array $data): void
{
    $payload = [
        'end_device_ids' => [
            'device_id' => $eqLogic->getName(),
//            'application_ids' => [
//                'application_id' => 'display-milesight',
//            ],
        ],
        'downlinks' => [
            [
                'f_port' => 85,
                'decoded_payload' => $data,
                'priority' => 'HIGHEST',
            ],
        ],
    ];

    $topic = findTopicForEqLogic($eqLogic);
    if (null === $topic) {
        milesightEinkDisplay::logger('info', sprintf('Aucun topic trouvé pour l\'équipement %d', $eqLogic->getId()));

        return;
    }

    $eqLogic->publish('Update display screen', $topic, $payload, 1, 0);
}

function sendMessageToRefreshScreen(eqLogic $eqLogic): void
{
    // bytes to refresh screen
    $bytes = [0xFF, 0x3D, 0x02];

    $payload = [
        'end_device_ids' => [
            'device_id' => $eqLogic->getName(),
//            'application_ids' => [
//                'application_id' => 'display-milesight',
//            ],
        ],
        'downlinks' => [
            [
                'f_port' => 85,
                'frm_payload' => base64_encode(pack('C*', ...$bytes)),
                'priority' => 'HIGHEST',
            ],
        ],
    ];

    $topic = findTopicForEqLogic($eqLogic);
    if (null === $topic) {
        milesightEinkDisplay::logger('info', sprintf('Aucun topic trouvé pour l\'équipement %d', $eqLogic->getId()));

        return;
    }


    $eqLogic->publish('Refresh display screen', $topic, $payload, 1, 0);
}

function findTopicForEqLogic(eqLogic $eqLogic): ?string
{
    /** @var cmd[]|null $commands */
    $commands = $eqLogic->getCmd();
    $topic = null;
    foreach ($commands as $command) {
        $commandTopic = $command->getConfiguration('topic');
        $lastTopicElement = preg_replace('/.*\//', '', $commandTopic);
        if (null === $lastTopicElement) {
            continue;
        }

        if ('push' === $lastTopicElement) {
            $topic = $commandTopic;
            break;
        }

        // we already have a "down" topic, we're going to use the same topic
        if ('down' === $lastTopicElement) {
            $topic = $commandTopic.'/push';
            break;
        }

        // we have an "up" topic, we simply need to replace "up" by "down"
        if ('up' === $lastTopicElement) {
            // replace up by down in topic
            $topic = preg_replace('/\/up$/', '/down/push', $commandTopic);
            break;
        }
    }

    return $topic;
}


try {
    require_once dirname(__FILE__).'/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    require_once __DIR__.'/../../core/class/milesightEinkDisplay.class.php';

    $action = init('action');

    switch ($action) {
        case "updateDisplay":
        {
            $selectedEquipments = init('selectedEquipments');
            $template = init('template');

            // create dynamic variable from $text1 to $text10
            for ($i = 1; $i <= 10; $i++) {
                ${'text'.$i} = init('text_'.$i);
            }

            $hasError = false;
            foreach ($selectedEquipments as $eqLogicId) {
                /** @var eqLogic|null $eqLogic */
                $eqLogic = eqLogic::byId($eqLogicId);
                if (is_null($eqLogic)) {
                    $hasError = true;
                    milesightEinkDisplay::logger('warning', sprintf('Equipement introuvable ID: %s', $eqLogicId));

                    continue;
                }

                if (false === $eqLogic instanceof jMQTT) {
                    $hasError = true;
                    milesightEinkDisplay::logger('warnng', 'Equipement non compatible');

                    return;
                }

                $data = [
                    'template' => (int)$template,
                ];

                if (!empty($qrCode)) {
                    $data['qrcode'] = $qrCode;
                }

                foreach (range(1, 10) as $i) {
                    if (empty(${'text'.$i})) {
                        ${'text'.$i} = '';
                    }
                    $data['text_'.$i] = ${'text'.$i};
                }

                $broker = $eqLogic->getBroker();
                if (!$broker) {
                    $hasError = true;
                    milesightEinkDisplay::logger(
                        'warning',
                        sprintf('Aucun broker selectionné pour l\'ID %d', $eqLogicId)
                    );

                    return;
                }

                if (!$broker->getIsEnable()) {
                    $hasError = true;
                    milesightEinkDisplay::logger('warning', 'Le broker sélectionné n\'est pas activé');

                    return;
                }

                if (!$eqLogic->getMqttClientState()) {
                    if (!$eqLogic::getDaemonAutoMode()) {
                        $hasError = true;
                        milesightEinkDisplay::logger('warning', 'Le démon jMQTT n`est pas activé');

                        return;
                    }
                    $hasError = true;
                    milesightEinkDisplay::logger(
                        'warning',
                        'Message non publié, car le démon jMQTT n\'est pas démarré'
                    );

                    return;
                }
                sendMessageToUpdateScreen($eqLogic, $data);
                usleep(1);
                sendMessageToRefreshScreen($eqLogic);
            }

            if (!$hasError) {
                ajax::success();
            } else {
                ajax::error();
            }

            return;
        }
        case "getEquipments":
        {
            $brokerId = init('brokerId');
            $jMQTTs = jMQTT::byBrkId($brokerId);
            if (empty($jMQTTs)) {
                ajax::success([]);

                return;
            }

            $parentObjectId = init('parentObjectId');

            /** @var jeeObject $parentObject */
            $parentObject = jeeObject::byId($parentObjectId);
            if (null === $parentObject) {
                ajax::success([]);
            }

            // collect all child objects ids
            $objectIds = array_map(static function (jeeObject $object) {
                return $object->getId();
            }, $parentObject->getChilds());
            // add parent object id
            $objectIds[] = $parentObject->getId();

            $toReturn = [];
            foreach ($jMQTTs as $jMQTT) {
                if (\in_array($jMQTT->getObject_id(), $objectIds)) {
                    $toReturn[] = [
                        'id' => $jMQTT->getId(),
                        'name' => $jMQTT->getName(),
                    ];
                }
            }

            ajax::success($toReturn);

            return;
        }
        default:
            throw new RuntimeException(__('Aucune méthode correspondante à', __FILE__).' : '.$action);
    }
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
