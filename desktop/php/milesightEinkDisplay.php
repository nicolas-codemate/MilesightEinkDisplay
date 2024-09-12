<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$eqBrokers = jMQTT::getBrokers();
$eqBrokersName = [];
foreach ($eqBrokers as $brokerId => $broker) {
    $eqBrokersName[$brokerId] = $broker->getName();
}
sendVarToJS('eqBrokers', $eqBrokersName);


/** @var eqLogic[] $jMQTTEqpts */
$jMQTTEqpts = [];
foreach (eqLogic::byType('jMQTT') as $eqLogic) {
    $jMQTTEqpts[$eqLogic->getId()] = $eqLogic->getName();
}
sendVarToJS('jMQTTEqpts', $jMQTTEqpts);


$objects = [];
foreach (jeeObject::buildTree() as $object) {
    $objects[$object->getId()] = [
        'name' => $object->getName(),
        'parentNumber' => $object->getConfiguration('parentNumber'),
    ];
}


sendVarToJS('objects', $objects);

//        <div class="form-group">
//            <label class="col-sm-3 control-label">{{Objet parent}}</label>
//            <div class="col-sm-3">
//                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
//                    <option value="">{{Aucun}}</option>
//                    <?php
//                    foreach ((jeeObject::buildTree(null, false)) as $object) {
//                        echo '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
//                    }
//                    ?>
<!--                </select>-->
<!--            </div>-->
<!--        </div>-->


?>

<div class="row row-overflow">
    <!-- Page d'accueil du plugin -->
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <!-- Boutons de gestion du plugin -->
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="updateScreen">
                <i class="fas fa-plus-circle"></i>
                <br>
                <span>{{Mettre à jour les écrans}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br>
                <span>{{Configuration}}</span>
            </div>
        </div>
    </div> <!-- /.eqLogicThumbnailDisplay -->
</div>

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php
include_file('desktop', 'milesightEinkDisplay', 'js', 'milesightEinkDisplay');
include_file('desktop', 'bootstrap-multiselect', 'js', 'milesightEinkDisplay');
include_file('desktop', 'bootstrap-multiselect', 'css', 'milesightEinkDisplay');
?>

<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php
include_file('core', 'plugin.template', 'js'); ?>
