<?php
if (!isConnect('admin')) {
    throw new Exception('Error 401 Unauthorized');
}
$plugin = plugin::byId('OneWireExt');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
try {
	$result = OneWireExt::deamon_info();
	if (isset($result['state'])) {
		$controlerState = $result['state'];
	}
} catch (Exception $e) {
	$controlerState = null;
}
switch ($controlerState) {
	case 'ok':
		break;
	case 'nok':
		event::add('jeedom::alert', array(
			'level' => 'danger',
			'page' => 'OneWireExt',
			'message' => __('Le deamon OneWireExt ne semble pas démaré, vérifiez la configuration.', __FILE__),
		));
		break;
}
?>

<div class="row row-overflow">

	<div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>

	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
		<legend>{{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">

			<div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 140px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
				<center>
					<i class="fa fa-wrench" style="font-size : 5em;color:#767676;"></i>
				</center>
			<span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>Configuration</center></span>
			</div>
		</div>

        <legend>{{Mes sondes de température}}</legend>
            <div class="eqLogicThumbnailContainer">
                <?php
                foreach ($eqLogics as $eqLogic) {
                	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
                	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="text-align: center; background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
                	echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
                	echo "<br>";
                	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">' . $eqLogic->getHumanName(true, true) . '</span>';
                	echo '</div>';
                }
                ?>
            </div>
    </div>
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
		<a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
    <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> Configuration avancée</a>

		<ul class="nav nav-tabs" role="tablist">
      		<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> Equipement</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> Commandes</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
      </br>
      <div class="row">
          <div class="col-lg-6">
              <form class="form-horizontal">
                  <fieldset>
                      <div class="form-group">
                          <label class="col-lg-4 control-label">{{Nom de l'équipement}} :</label>
                          <div class="col-lg-4">
                              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
                          </div>
                          <div class="col-lg-4">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-lg-4 control-label" >{{Objet parent :}}</label>
                          <div class="col-lg-4">
                              <select class="eqLogicAttr form-control" data-l1key="object_id">
                                  <option value="">{{Aucun}}</option>
                                  <?php
                                  foreach (object::all() as $object) {
                                      echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                  }
                                  ?>
                              </select>
                          </div>
                          <div class="col-lg-4">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-lg-4 control-label">{{Identifiant du capteur}} :</label>
                          <div class="col-lg-4">
                              <input type="text" class="eqLogicAttr form-control tooltips" title="{{Identifiant du compteur aussi connu sous le nom ADCO.}}" data-l1key="logicalId" placeholder="{{ADCO du compteur}}"/>
                          </div>
                          <div class="col-lg-4">
                          </div>
                      </div>
                      <div class="form-group" style="display:none">
                          <label class="col-lg-4 control-label">{{Catégorie}} :</label>
                          <div class="col-lg-8">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-lg-4 control-label">{{Etat de l'objet}} :</label>
                          <div class="col-lg-8">
                              <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                              <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                          </div>
                      </div>
                  </fieldset>
              </form>
          </div>
      </div>
  </div>
  <div role="tabpanel" class="tab-pane" id="commandtab">
  </br>
  <table id="table_cmd" class="table table-bordered table-condensed">
      <thead>
          <tr>
              <th style="width: 50px;">#</th>
              <th style="width: 150px;">{{Nom}}</th>
              <th style="width: 800px;">{{Donnée}}</th>
              <th style="width: 150px;">{{Paramètres}}</th>
              <th style="width: 150px;"></th>
          </tr>
      </thead>
      <tbody>
      </tbody>
  </table>
  <form class="form-horizontal">
      <fieldset>
          <div class="form-actions">
          </div>
      </fieldset>
  </form>
</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'OneWireExt', 'js', 'OneWireExt'); ?>
<?php include_file('desktop', 'OneWireExt', 'css', 'OneWireExt'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
