<?php
	if(!defined('PLX_ROOT')) exit;
	/**
		* Plugin 			StaticComments
		*
		* @CMS required		PluXml 
		* @page				config.php
		* @version			1.0
		* @date				2024-01-25
		* @author 			G.Cyrille
		░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
		░       ░░  ░░░░░░░  ░░░░  ░  ░░░░  ░░      ░░       ░░░      ░░  ░░░░░░░        ░░      ░░░░░   ░░░  ░        ░        ░
		▒  ▒▒▒▒  ▒  ▒▒▒▒▒▒▒  ▒▒▒▒  ▒▒  ▒▒  ▒▒  ▒▒▒▒  ▒  ▒▒▒▒  ▒  ▒▒▒▒  ▒  ▒▒▒▒▒▒▒▒▒▒  ▒▒▒▒  ▒▒▒▒▒▒▒▒▒▒    ▒▒  ▒  ▒▒▒▒▒▒▒▒▒▒  ▒▒▒▒
		▓       ▓▓  ▓▓▓▓▓▓▓  ▓▓▓▓  ▓▓▓    ▓▓▓  ▓▓▓▓  ▓       ▓▓  ▓▓▓▓  ▓  ▓▓▓▓▓▓▓▓▓▓  ▓▓▓▓▓      ▓▓▓▓▓  ▓  ▓  ▓      ▓▓▓▓▓▓  ▓▓▓▓
		█  ███████  ███████  ████  ██  ██  ██  ████  █  ███████  ████  █  ██████████  ██████████  ████  ██    █  ██████████  ████
		█  ███████        ██      ██  ████  ██      ██  ████████      ██        █        ██      ██  █  ███   █        ████  ████
		█████████████████████████████████████████████████████████████████████████████████████████████████████████████████████████
	**/	
	# Control du token du formulaire
	plxToken::validateFormToken($_POST);
	
	# Liste des langues disponibles et prises en charge par le plugin
	$aLangs = array($plxAdmin->aConf['default_lang']);	
	
	if(!empty($_POST)) {
		
		$plxPlugin->setParam('showLast' 		,$_POST['showLast']									, 'numeric');
		$plxPlugin->setParam('intermediaire' 	,$_POST['intermediaire']							, 'numeric');
		$plxPlugin->setParam('bypage' 			,$_POST['bypage']									, 'numeric');
		$plxPlugin->setParam('captcha' 			,$_POST['captcha']									, 'numeric');
		$plxPlugin->setParam('statics' 			,json_encode($_POST['statics'], JSON_PRETTY_PRINT)	, 'cdata');
		
		$plxPlugin->saveParams();	
		header("Location: parametres_plugin.php?p=".basename(__DIR__));
		exit;
	}
	
	$var['showLast'] = $plxPlugin->getParam('showLast')=='' ? 1: $plxPlugin->getParam('showLast');	
	$var['intermediaire'] = $plxPlugin->getParam('intermediaire')=='' ? 0: $plxPlugin->getParam('intermediaire');	
	$var['bypage'] = $plxPlugin->getParam('bypage')=='' ? 5: $plxPlugin->getParam('bypage');	
	$var['captcha'] = $plxPlugin->getParam('captcha')=='' ? 1: $plxPlugin->getParam('captcha');
	$var['statics'] = $plxPlugin->getParam('statics')=='null' ? '[]': $plxPlugin->getParam('statics');
	$var['template'] = $plxPlugin->getParam('template')=='' ? 'static.php' : $plxPlugin->getParam('template');	
	# initialisation des variables propres à chaque lanque
	$langs = array();
	foreach($aLangs as $lang) {
		# chargement de chaque fichier de langue
		$langs[$lang] = $plxPlugin->loadLang(PLX_PLUGINS.'StaticComments/lang/'.$lang.'.php');
		$var[$lang]['mnuName'] =  $plxPlugin->getParam('mnuName_'.$lang)=='' ? $plxPlugin->getLang('L_DEFAULT_MENU_NAME') : $plxPlugin->getParam('mnuName_'.$lang);
	}
	$staticsList='';
	
	$staticSelected = json_decode($var['statics']);
	global $plxAdmin;
	$staticsArray= $plxAdmin->aStats;
	if(count($staticsArray) > 0 ) {
		foreach($plxAdmin->aStats as $k => $static){
			$ok='';
			if(in_array($k,$staticSelected)) {$ok ='selected="selected"';}
			
			$staticsList .='			<option value="'.$k.'" '.$ok.'>'.$static['name'].'</option>'.PHP_EOL;
		}
	}
	
	# affichage du wizard à la demande
	if(isset($_GET['wizard'])) {$_SESSION['justactivated'.basename(__DIR__)] = true;}
	# fermeture session wizard
	if (isset($_SESSION['justactivated'.basename(__DIR__)])) {
		unset($_SESSION['justactivated'.basename(__DIR__)]);
		$plxPlugin->wizard();
	}
	# On récupère les templates des pages statiques
	$files = plxGlob::getInstance(PLX_ROOT.$plxAdmin->aConf['racine_themes'].$plxAdmin->aConf['style']);
	if ($array = $files->query('/^static(-[a-z0-9-_]+)?.php$/')) {
		foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
	}	
?>
<link rel="stylesheet" href="<?php echo PLX_PLUGINS."StaticComments/css/tabs.css" ?>" media="all" />
<p>Ajoute aux pages statique choisies le formulaire de commentaire et le captcha natif de PluXml.</p>	
<h2><?php $plxPlugin->lang("L_CONFIG") ?></h2>
<a href="parametres_plugin.php?p=<?= basename(__DIR__) ?>&wizard" class="aWizard"><img src="<?= PLX_PLUGINS.basename(__DIR__)?>/img/wizard.png" style="height:2em;vertical-align:middle" alt="Wizard"> Wizard</a>
<div id="tabContainer">
	<form action="parametres_plugin.php?p=<?= basename(__DIR__) ?>" method="post" >
		<div class="tabs">
			<ul>
				<li id="tabHeader_Param"><?php $plxPlugin->lang('L_SELECT_STATIC') ?></li>
				<li id="tabHeader_Activate"><?php $plxPlugin->lang('L_ACTIVATE_CAPCHA') ?></li>
				<li id="tabHeader_Print"><?php $plxPlugin->lang('L_CONFIG_COMMENT') ?></li>
				
			</ul>
		</div>
		<div class="tabscontent">
			<div class="tabpage" id="tabpage_Param">
				<fieldset><legend><?= $plxPlugin->getLang('L_PARAMS_STATIC') ?></legend>
					<select name="statics[]" id="statics" multiple>
						<option value><?= $plxPlugin->lang('L_NONE_IN_LIST') ?></option>
						<?= $staticsList ?>
					</select>	
				</fieldset>
			</div>
			<div class="tabpage" id="tabpage_Activate">
				<fieldset class="grid">
					<legend><?= $plxPlugin->getLang('L_PARAMS_CAPTCHA') ?></legend>	
					<p>
						<label for="captcha"><?= $plxPlugin->getLang('L_PARAMS_CAPTCHA_ACTIVATE') ?></label> 	
					</p>	
						<?php plxUtils::printSelect('captcha',array('1'=>L_YES,'0'=>L_NO), $var['captcha']);?>	
				</fieldset>
			</div>
			<div class="tabpage" id="tabpage_Print">
				<fieldset class="grid">
					<legend><?= $plxPlugin->getLang('L_PARAMS_COMMENTS') ?></legend>	
					<p>
						<label for="bypage"><?= $plxPlugin->getLang('L_PARAMS_BY_PAGE') ?></label> 
						<?php plxUtils::printInput('bypage',$var['bypage'],'text','3-5') ?>		
					</p>		
					<p>
						<label for="intermediaire"><?= $plxPlugin->getLang('L_IN_BETWEEN') ?></label> 
						<?php plxUtils::printSelect('intermediaire',array('1'=>L_YES,'0'=>L_NO), $var['intermediaire']);?>		
					</p>		
					<p>
						<label for="showLast"><?= $plxPlugin->getLang('L_LAST_FIRST') ?></label> 
						<?php plxUtils::printSelect('showLast',array('1'=>L_YES,'0'=>L_NO), $var['showLast']);?>		
					</p>	
				</fieldset>
			</div>
			

				<p class="in-action-bar">
					<?php echo plxToken::getTokenPostMethod() ?><br>
					<input type="submit" name="submit" value="<?= $plxPlugin->getLang('L_SAVE') ?>"/>
				</p>

		</form>
	</div>
<script type="text/javascript" src="<?php echo PLX_PLUGINS."StaticComments/js/tabs.js" ?>"></script>