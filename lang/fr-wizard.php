<?php
	if(!defined('PLX_ROOT')) exit; 
	/**
		* Plugin 			StaticComments
		*
		* @CMS required		PluXml 
		* @page				-wizard.php
		* @version			1.0
		* @date				2024-01-25
		* @author 			G.Cyrille
	**/		
	
	# pas d'affichage dans un autre plugin !	
	if(isset($_GET['p'])&& $_GET['p'] !== 'StaticComments' ) {goto end;}
	
	# on charge la class du plugin pour y accéder
	$plxMotor = plxMotor::getInstance();
	$plxPlugin = $plxMotor->plxPlugins->getInstance( 'StaticComments'); 
	
	# On vide la valeur de session qui affiche le Wizard maintenant qu'il est visible.
	if (isset($_SESSION['justactivatedStaticComments'])) {unset($_SESSION['justactivatedStaticComments']);}
	
	# initialisation des variables propres à chaque lanque 
	$langs = array();
	
	# initialisation des variables communes à chaque langue	
	$var = array();
	
	$var['showLast'] = $plxPlugin->getParam('showLast')=='' ? 1: $plxPlugin->getParam('showLast');	
	$var['intermediaire'] = $plxPlugin->getParam('intermediaire')=='' ? 0: $plxPlugin->getParam('intermediaire');	
	$var['bypage'] = $plxPlugin->getParam('bypage')=='' ? 5: $plxPlugin->getParam('bypage');	
	$var['captcha'] = $plxPlugin->getParam('captcha')=='' ? 1: $plxPlugin->getParam('captcha');
	$var['statics'] = $plxPlugin->getParam('statics')=='null' ? '[]': $plxPlugin->getParam('statics');
	
	# recuperation liste et config page statiques
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
	#affichage
?>
<link rel="stylesheet" href="<?= PLX_PLUGINS ?>StaticComments/css/wizard.css" media="all" />
<input id="closeWizard" type="checkbox">
<div class="wizard">	
	<div class="container">	
		<div class='title-wizard'>
			<h2><?= $plxPlugin->aInfos['title']?><br><?= $plxPlugin->aInfos['version']?></h2>
			<img src="<?php echo PLX_PLUGINS. 'StaticComments'?>/icon.png">
			<div><q> Made in <a href="https://pluxopolis.net/thecrock">PluXopolis</a> By <?= $plxPlugin->aInfos['author']?> </q></div>
		</div>
		<p></p>
		
		<div id="tab-status">
			<span class="tab active">1</span>
		</div>		
		<form action="parametres_plugin.php?p=<?php echo 'StaticComments' ?>"  method="post">
			<div role="tab-list">		
				<div role="tabpanel" id="tab1" class="tabpanel">
					<h2>Bienvenue dans l’extension <b style="font-family:cursive;color:crimson;font-variant:small-caps;font-size:2em;vertical-align:-.5rem;display:inline-block;"><?= $plxPlugin->aInfos['title']?></b></h2>
					<p>Cette extension permet à vos visiteur de commenter vos pages statiques.</p>
					<p>&Agrave; l'activation, il est necessaire de configurer l'extension en choisissant vos pages et le mode d'affichage des commentaires.</p>
					<p></p>
					<p>Ce wiz'aide est là pour vous aider au fil de ces quelques pages.</p>
				</div>	
				<div role="tabpanel" id="tab2" class="tabpanel hidden title">
					<h2>Choix des pages</h2>
					<p>Une liste à cliquer ...</p>
					<!-- Ci-dessous , valide le passage à une autre page si d'autre champs required existe dans le formulaire -->
					<!-- <input type="hidden"  class="form-input" value="keepGoing"> -->
				</div>	
				<div role="tabpanel" id="tab3" class="tabpanel hidden">
					<h2>Choisir</h2>
					<p>Pour séléctionner plusieurs pages statiques , il faut appuyer sur la touche  <kbd> CTRL </kbd> 
						et cliquer sur le nom de la page dans la liste ci-dessous.
					</p>
					<select name="statics[]" id="statics" multiple style="width:100%;">
						<option value><?= $plxPlugin->lang('L_NONE_IN_LIST') ?></option>
						<?= $staticsList ?>
					</select>	
				</div>
				<div role="tabpanel" id="tab4" class="tabpanel hidden title">
					<h2>L'Anti-Spam</h2>
					<p>activé ou pas ?</p>
					<!-- Ci-dessous , valide le passage à une autre page si d'autre champs required existe dans le formulaire -->
					<!-- <input type="hidden"  class="form-input" value="keepGoing"> -->
				</div>	
				<div role="tabpanel" id="tab5" class="tabpanel hidden">
					<h2>Choisir</h2>
					<p>Les zones de commentaires peuvent être une cible de choix pour les robots spammeurs, un captcha permet de limiter leur impacts 
					si vous souhaitez vous en prémunir.</p>
					<br>
					<br>
					<p>
						<label for="captcha"><?= $plxPlugin->getLang('L_PARAMS_CAPTCHA_ACTIVATE') ?>
						<?php plxUtils::printSelect('captcha',array('1'=>L_YES,'0'=>L_NO), $var['captcha']);?></label>		 	
					</p>
				</div>
				<div role="tabpanel" id="tab6" class="tabpanel hidden title">
					<h2>Affichage</h2>
					<p>pagination des commentaires et sens d'affichage.</p>
					<!-- Ci-dessous , valide le passage à une autre page si d'autre champs required existe dans le formulaire -->
					<!-- <input type="hidden"  class="form-input" value="keepGoing"> -->
				</div>	
				<div role="tabpanel" id="tab3" class="tabpanel hidden">
					<h2>Options d'affichages</h2>
					<p>L'affichages des commentaires (et de son formulaire) est trés similaire à ceux des article 
						avec l'avantage d'être distribué sur plusieurs pages lorsqu'ils sont nombreux.
					</p>
					<dl>
						<dt>vous pouvez:</dt>
						<dd>les regroupé par <?php plxUtils::printInput('bypage',$var['bypage'],'text','1-5') ?>	 par pages.</dd>
						<dd> affiché les liens de toutes les pages intermediares 
						<?php plxUtils::printSelect('intermediaire',array('1'=>L_YES,'0'=>L_NO), $var['intermediaire']);?>
						<small>(non = pour seulement suivant/précédent)</small></dd>
						<dd> les trié par odre du plus récent <small>oui</small> 
						<?php plxUtils::printSelect('showLast',array('1'=>L_YES,'0'=>L_NO), $var['showLast']);?></dd>
					</dl>
				</div>
				<div role="tabpanel" id="tabEnd" class="tabpanel hidden title">
					<h2>End Wiz'aide</h2>
					<p>Enregistrer ou fermer</p>
					<!-- Ci-dessous , valide le passage à une autre page si d'autre champs required existe dans le formulaire -->
					<!-- <input type="hidden"  class="form-input" value="keepGoing"> -->
				</div>		
				<div class="pagination">
					<a class="btn hidden" id="prev"><?php $plxPlugin->lang('L_PREVIOUS') ?></a>
					<a class="btn" id="next"><?php $plxPlugin->lang('L_NEXT') ?></a>
					<?php echo plxToken::getTokenPostMethod().PHP_EOL ?>
					<button class="btn btn-submit hidden" id="submit"><?php $plxPlugin->lang('L_SAVE') ?></button>
				</div>
			</div>		
		</form>			
		<p class="idConfig">
			<?php
				if(file_exists(PLX_PLUGINS. 'StaticComments/admin.php')) {echo ' 
				<a href="/core/admin/plugin.php?p= StaticComments">Page d\'administration '. basename(__DIR__ ).'</a>';}
				if(file_exists(PLX_PLUGINS. 'StaticComments/config.php')) {echo ' 	<a href="/core/admin/parametres_plugin.php?p=StaticComments">Page de configuration  StaticComments</a>';}
			?>
			<label for="closeWizard"> Fermer </label>
		</p>	
</div>	
<script src="<?= PLX_PLUGINS ?>StaticComments/js/wizard.js"></script>
</div>
<?php end: // FIN! ?>				
