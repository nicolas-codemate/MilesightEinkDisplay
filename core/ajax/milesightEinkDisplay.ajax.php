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
            $eqLogicId = init('eqLogic');
            $template = init('template');
            $text1 = init('text1');
            $text2 = init('text2');
            $text3 = init('text3');
            $text4 = init('text4');
            $text5 = init('text5');
            $qrCode = init('qrCode');

            /** @var eqLogic|null $eqLogic */
            $eqLogic = eqLogic::byId($eqLogicId);
            if (is_null($eqLogic)) {
                ajax::error(__('Equipement introuvable', __FILE__));

                return;
            }

            if (false === $eqLogic instanceof jMQTT) {
                ajax::error(__('Equipement non compatible', __FILE__));

                return;
            }

            $payload = [
                'text1' => $text1,
                'text2' => $text2,
                'text3' => $text3,
                'text4' => $text4,
                'text5' => $text5,
                'qrCode' => $qrCode,
                'template' => $template,
            ];

            $broker = $eqLogic->getBroker();
            if (!$broker) {
                ajax::error(__('Aucun broker selectionné', __FILE__));

                return;
            }

            if (!$broker->getIsEnable()) {
                ajax::error(__('Le broker sélectionné n\'est pas activé', __FILE__));

                return;
            }

            if (!$eqLogic->getMqttClientState()) {
                if (!$eqLogic::getDaemonAutoMode()) {
                    ajax::error(__('Le démon jMQTT n`est pas activé', __FILE__));

                    return;
                }
                ajax::error(__('Message non publié, car le démon jMQTT n\'est pas démarré', __FILE__));

                return;
            }

            /** @var cmd[]|null $commands */
            $commands = $eqLogic->getCmd();
            $topic = null;

            foreach ($commands as $command) {
                $commandTopic = $command->getConfiguration('topic');
                $lastTopicElement = preg_replace('/.*\//', '', $commandTopic);
                if (null === $lastTopicElement) {
                    continue;
                }

                // we already have a "down" topic, we're going to use the same topic
                if ('down' === $lastTopicElement) {
                    $topic = $commandTopic;
                    break;
                }

                // we have an "up" topic, we simply need to replace "up" by "down"
                if ('up' === $lastTopicElement) {
                    // replace up by down in topic
                    $topic = preg_replace('/\/up$/', '/down', $commandTopic);
                    break;
                }
            }

            if (null === $topic) {
                ajax::error(__('Aucun topic trouvé', __FILE__));

                return;
            }

            $eqLogic->publish('Update display screen', $topic, $payload, 0, 0);

            ajax::success();

            return;
        }
        default:
            throw new RuntimeException(__('Aucune méthode correspondante à', __FILE__).' : '.$action);
    }
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
