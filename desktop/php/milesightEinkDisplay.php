<?php

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

/** @var eqLogic[] $jMQTTEqpts */
$jMQTTEqpts = [];
foreach (eqLogic::byType('jMQTT') as $eqLogic) {
    $jMQTTEqpts[$eqLogic->getId()] = $eqLogic->getName();
}
sendVarToJS('jMQTTEqpts', $jMQTTEqpts);

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

    <!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
    <?php
    include_file('desktop', 'milesightEinkDisplay', 'js', 'milesightEinkDisplay'); ?>

    <!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
    <?php
    include_file('core', 'plugin.template', 'js'); ?>
