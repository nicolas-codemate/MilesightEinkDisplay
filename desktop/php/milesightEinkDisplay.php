<?php

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('milesightEinkDisplay');
$jMQTTplugin = plugin::byId('jMQTT');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($jMQTTplugin->getId());

//var_dump($eqLogics);

//$objects = jeeObject::all();

//var_dump($objects);
//$objectsById = [];
//foreach (jeeObject::all() as $object) {
//    $objectsById[$object->getId()] = $object->getName();
//}


/** @var jMQTT[] $eqBrokers */
$eqBrokers = jMQTT::getBrokers();
$eqBrokersName = array();
foreach ($eqBrokers as $id => $eqL) {
    $eqBrokersName[$id] = $eqL->getName();
}

//include_file('desktop', 'jMQTT.globals', 'js', 'jMQTT');
//include_file('desktop', 'jMQTT.functions', 'js', 'jMQTT');


//sendVarToJS('jmqtt_globals.eqBrokers', $eqBrokersName);
//sendVarToJS('jeeObjects', $objectsById);

// TO PUBLISH MESSAGE
/** @var jMQTT $eqpt */
//$eqpt = jMQTT::byId(3);
//var_dump($eqpt);

/** @var eqLogic[] $jMQTTEqpts */
$jMQTTEqpts = eqLogic::byType('jMQTT');

//$compatibleEqpts = [];
//foreach ($jMQTTEqpts as $eqpt) {
//    $eqpt->getTopic();
//}

//var_dump($jMQTTEqpts);


//$eqpt->publish($id, $topic, $payload, $qos = 1, $retain = false)

?>

<div class="row row-overflow">
    <!-- Page d'accueil du plugin -->
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <!-- Boutons de gestion du plugin -->
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br>
                <span>{{Configuration}}</span>
            </div>
        </div>
    </div> <!-- /.eqLogicThumbnailDisplay -->
    <div class="col-xs-12">
        <legend><i class="fas fa-table"></i> {{Mise à jour des écrans}}</legend>
        <form name="sendDisplayUpdate">
            <div class="form-group">
                <label for="eqLogic" class="col-sm-2 control-label">{{Equipement}}</label>
                <label>
                    <select id="eqLogic" name="eqLogic" class="form-control">
                        <option value="">{{Aucun}}</option>
                        <?php
                        foreach ($jMQTTEqpts as $jMQTTEqpt) {
                            echo '<option value="'.$jMQTTEqpt->getId().'">'.$jMQTTEqpt->getName().'</option>';
                        }
                        ?>
                    </select>
                </label>
            </div>
            <div class="form-group">
                <legend class="col-form-label col-sm-2 pt-0">{{Template}}</legend>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="template" id="template1">
                    <label class="form-check-label" for="template1">
                        Template 1
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="template" id="template2">
                        <label class="form-check-label" for="template2">
                            Template 2
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo "<label for=\"text_$i\" class=\"control-label\">{{Text $i}}</label>";
                    echo "<input type=\"text\" class=\"form-control\" id=\"text_1\" name=\"text_1\">";
                }
                ?>
                <label class="control-label" for="qrcode">{{QR Code}}</label>
                <input type="text" class="form-control" id="qrcode" name="qrcode">
            </div>
            <div class="form-group">
                <button type="button" id="submitDisplayUpdate" class="btn btn-success">{{Envoyer}}</button>
            </div>
        </form>
    </div>
</div>

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php
include_file('desktop', 'milesightEinkDisplay', 'js', 'milesightEinkDisplay'); ?>

<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php
include_file('core', 'plugin.template', 'js'); ?>
