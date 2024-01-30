<?php if(!defined('PLX_ROOT')) exit;
	/**
		* Plugin 			Static Comments
		*
		* @CMS required			PluXml 
		*
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
	class StaticComments extends plxPlugin {
		
		
		
		const BEGIN_CODE = '<?php' . PHP_EOL;
		const END_CODE = PHP_EOL . '?>';
		public $lang = ''; 
		
		
		public function __construct($default_lang) {
			# appel du constructeur de la classe plxPlugin (obligatoire)
			parent::__construct($default_lang);
			
			
			
			# droits pour accèder à la page config.php du plugin
			$this->setConfigProfil(PROFIL_ADMIN, PROFIL_MANAGER);	
			
			$captcha = $this->getParam('captcha') ==''   ?   '0' : $this->getParam('captcha') ;
			$statics = $this->getParam('statics') ==''   ?   '0' : $this->getParam('statics') ;
			
			
			# Declaration des hooks		
			$this->addHook('AdminTopBottom', 'AdminTopBottom');
			$this->addHook('ThemeEndHead', 'ThemeEndHead');
			$this->addHook('wizard', 'wizard');
			$this->addHook('plxShowStaticContent', 'plxShowStaticContent');
			
			
		}
		
		# Activation / desactivation
		
		public function OnActivate() {
			# code à executer à l’activation du plugin
			# activation du wizard
			$_SESSION['justactivated'.basename(__DIR__)] = true;
		}
		
		public function OnDeactivate() {
			# code à executer à la désactivation du plugin
		}	
		
		
		public function ThemeEndHead() {
			#gestion multilingue
			if(defined('PLX_MYMULTILINGUE')) {		
				$plxMML = is_array(PLX_MYMULTILINGUE) ? PLX_MYMULTILINGUE : unserialize(PLX_MYMULTILINGUE);
				$langues = empty($plxMML['langs']) ? array() : explode(',', $plxMML['langs']);
				$string = '';
			foreach($langues as $k=>$v)	{
			$url_lang="";
			if($_SESSION['default_lang'] != $v) $url_lang = $v.'/';
			$string .= 'echo "\\t<link rel=\\"alternate\\" hreflang=\\"'.$v.'\\" href=\\"".$plxMotor->urlRewrite("?'.$url_lang.$this->getParam('url').'")."\" />\\n";';
			}
			echo '<?php if($plxMotor->mode=="'.$this->getParam('url').'") { '.$string.'} ?>';
			}
			
			
			// ajouter ici vos propre codes (insertion balises link, script , ou autre)
			}
			
			/**
			* Méthode qui affiche un message si le plugin n'a pas la langue du site dans sa traduction
			* Ajout gestion du wizard si inclus au plugin
			* @return	stdio
			* @author	Stephane F
			**/
			public function AdminTopBottom() {
			
			echo '<?php
			$file = PLX_PLUGINS."'.$this->plug['name'].'/lang/".$plxAdmin->aConf["default_lang"].".php";
			if(!file_exists($file)) {
			echo "<p class=\\"warning\\">'.basename(__DIR__).'<br />".sprintf("'.$this->getLang('L_LANG_UNAVAILABLE').'", $file)."</p>";
			plxMsg::Display();
			}
			?>';
			
			# affichage du wizard à la demande
			if(isset($_GET['wizard'])) {$_SESSION['justactivated'.basename(__DIR__)] = true;}
			# fermeture session wizard
			if (isset($_SESSION['justactivated'.basename(__DIR__)])) {
			unset($_SESSION['justactivated'.basename(__DIR__)]);
			$this->wizard();
			}
			
			}
			
			/** 
			* Méthode wizard
			* 
			* Descrition	: Affiche le wizard dans l'administration
			* @author		: G.Cyrille
			* 
			**/
			# insertion du wizard
			public function wizard() {
			# uniquement dans les page d'administration du plugin.
			if(basename(
			$_SERVER['SCRIPT_FILENAME']) 			=='parametres_plugins.php' || 
			basename($_SERVER['SCRIPT_FILENAME']) 	=='parametres_plugin.php' || 
			basename($_SERVER['SCRIPT_FILENAME']) 	=='plugin.php'
			) 	{	
			include_once(PLX_PLUGINS.__CLASS__.'/lang/'.$this->default_lang.'-wizard.php');
			}
			}
			
			/** 
			* Méthode plxShowStaticInclude
			* 
			* Descrition	:
			* @author		: TheCrok
			* 
			**/
			public function plxShowStaticContent() {
			
				# insertion des commentaires et formulaire			
				echo self::BEGIN_CODE;
				?>
				ob_start();
				include( PLX_PLUGINS.'StaticComments/assets/commentaires.php');
				$output .= ob_get_clean();
				<?php
				echo self::END_CODE;
			}
			
			
			}			