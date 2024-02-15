<?php  if(!defined('PLX_ROOT')) exit;
	
    # Ajout commentaires dans une page statique genéré	
    # Pagination des commentaires
    # reponses aux commentaires
    # inclusion du capcha natif de PluXml
    # @Author Gcyrillus
	# licence GNU General Public License v3.0 : https://github.com/gcyrillus/scripts-pour-pluxml/blob/main/LICENSE
    # $this is $plxShow
    
	# inclusion class du plugin pour extraction des paramètres de configuration
	$plug = $this->plxMotor->plxPlugins->getInstance('StaticComments');	
	
	#est ce une page statique configurée pour les commentaires ?
	$var['statics'] = $plug->getParam('statics')=='null' ? '[]': $plug->getParam('statics');
	$staticSelected = json_decode($var['statics']);
	
	if(in_array($this->staticId(), $staticSelected)) {
    ?>
    <div id="staticsComments">
        <hr>
        <?php
            # affichage du formulaire et commentaires enregistrés.	
            #########################################
            #configuration
            #########################################
            
            $captcha = $plug->getParam('captcha')=='' ? 1: $plug->getParam('captcha');		
            
            # nombre de Commentaire à afficher par page
            $bypage  = $plug->getParam('bypage')=='' ? 5: $plug->getParam('bypage');
            
            #  1 au lieu de 0 pour afficher les liens de chaque page
            $intermediaire = $plug->getParam('intermediaire')=='' ? 0: $plug->getParam('intermediaire');
            
            #  1 pour afficher la derniere page des commentaires par défaut
            $showLast = $plug->getParam('showLast')=='' ? 1: $plug->getParam('showLast'); 
            
            #########################################
            # FIN configuration
            #########################################
            
            # fichier de stockage des commentaires
            $commentsFile = PLX_ROOT.'/data/statiques/comment-'.$this->staticId().'.json';
            if(!file_exists($commentsFile)) { 
                touch($commentsFile);
                file_put_contents($commentsFile,'[]');
            }
            
            # extraction des commentaires dans un tableau
            $comments =  json_decode(file_get_contents($commentsFile), true);
            $num = count($comments);
            # format du message aprés soumission du formulaire 
            $row ='<p id="com_message" class="#com_class"><strong>#com_message</strong></p>';   
            
            # As t-on de nouveaux commentaires ?
            if(isset($_POST)){
                # color boite message par défaut
                $color = 'orange';
                $level='level-0';
                if(isset($_POST['name']) AND isset($_POST['content'])) {
                    
                    if( $_POST['name'] =='' OR $_POST['content'] =='') {
                        $_SESSION['msgcom'] =  L_NEWCOMMENT_FIELDS_REQUIRED;
                    }           
                    if(!empty($this->aConf['capcha']) AND (empty($_SESSION['capcha_token']) OR  empty($_POST['capcha_token']) or ($_SESSION['capcha_token'] != $_POST['capcha_token']))) {
                        $_SESSION['msgcom'] .= ' '. L_NEWCOMMENT_ERR_ANTISPAM;
                    }       
                    else {      
                        # On vérifie que le capcha est correct
                        if($this->plxMotor->aConf['capcha'] == 0 OR $_SESSION['capcha'] == sha1($_POST['rep'])) {
                            if(isset($_POST['level'])) 
                            {
                                $level = trim($_POST['level']) ;
                                if($level == 'level-5' || $level =='level-max' ) {$level ='level-max';}
                                else {$level++;}
                            }
                            $newcomment[] = array(
                            'num'       => trim(strip_tags($_POST['num'])),
                            'date'      => date('d-m-Y') ,
                            'name'      => trim(strip_tags($_POST['name'])) , 
                            'mail'      => trim(strip_tags($_POST['mail']))  , 
                            'site'      => trim(strip_tags($_POST['site'])), 
                            'content'   => trim(strip_tags($_POST['content'])),
                            'level'     => trim(strip_tags($level))
                            );              
                            
                            $color = 'green';
                            $_SESSION['msgcom'] = L_COM_PUBLISHED;
                            
                            # est ce une reponse à un commentaire particulier?
                            if(isset($_POST['index']))
                            {
                                array_splice($comments, trim($_POST['index'])+1, 0, $newcomment);
                            }
                            else {
                                
                                array_push($comments,$newcomment[0]);
                                
                            }
                            
                            file_put_contents($commentsFile, json_encode($comments,true|JSON_PRETTY_PRINT) );
                        }
                        else {
                            $_SESSION['msgcom'] =  L_NEWCOMMENT_ERR_ANTISPAM;
                        }
                    }
                }
            }
            
            if(count($comments)>0) {
                $tittleComment ="<h3>".count($comments).' '. L_COMMENT ."</h3>";        
                if(count($comments)>1) {
                    $tittleComment ="<h3>".count($comments).' '.  L_COMMENTS ."</h3>";          
                }
                echo $tittleComment ;
                
                # Style barre pagination commentaires
                echo '<style>:where(.page-item.page-link.active) { text-decoration:underline;font-weight:bold; padding:0.3em 2em;}.pagination.text-center.center.bordered {border-radius: 5px;width:max-content;  margin:auto;  border:solid 1px}#com_message:has(strong:empty){display: none;}.content_com {  white-space: pre-wrap;}</style>';
                
                
                #############################
                # extraction et maj variables
                #############################
                
                # extraction de l'url
                $url = $this->plxMotor->urlRewrite('?static' . $this->staticId() . '/' . $this->plxMotor->aStats[str_pad($this->staticId(), 3, '0', STR_PAD_LEFT)]['url'] );
                
                # generation du lien
                $link = $this->plxMotor->urlRewrite($url."/page");
                
                // On calcule le nombre de pages total
                $nbr = count($comments);
                $pages = ceil( $nbr / $bypage);
                $position = 1;
                if($showLast==1) $position = $pages;
                
                # extraction du numéro de page dans l'URL 
                $currentPage = preg_match('#\bpage(\d*)#',$_SERVER['REQUEST_URI'], $capture) ? intval($capture[1]) : $position;
                
                # indice de début, premier article à afficher
                $start = ($currentPage - 1) * $bypage;  
                
                // Calcul du 1er commentaire de la page
                $premier = ($currentPage * $bypage) - $bypage;
                ;       
                
                $pagecomments = array_slice($comments, $premier, $bypage);
                
                foreach($pagecomments as $com =>$val) { # On boucle sur les commentaires    
                    $index=array_search( $val['num'], array_column( $comments, 'num' ) );
                    echo '<div id="id-'.$val['num'].'" class="comment '.$val['level'].'">
                    <div id="com-'.$val['num'].'" data-index="'.$index.'" data-level="'.$val['level'].'">
                    <small>
                    <a class="nbcom" href="'.$_SERVER['REQUEST_URI'].'#com-'.$val['num'].'" title="#commentaire '.$val['num'].'">#'.$val['num'].'</a>&nbsp;
                    <time datetime="'.$val['date'].'">'.$val['date'].'</time> - '.$val['name'].' '. $this->getLang('SAID').':
                    </small>
                    <blockquote>
                    <p class="content_com">'.$val['content'].'</p>
                    </blockquote>';
                    if($this->plxMotor->aConf['allow_com']){
                        echo '<a rel="nofollow" href="#form" onclick="replyCom(\''.trim($val['num']).'\');">'.$this->getLang('REPLY').'</a>';
                    }
                    echo '</div>
                    </div>';                
                }
            ?>      
            <!-- Affichage de la pagination -->
            
            <?php
                ############################
                # Affichage de la pagination
                ############################
                if($pages>1){
                ?>
                <nav>
                    <ul class="pagination text-center center bordered">
                        <!-- Lien vers la page précédente (si on ne se trouve pas sur la 1ère page) -->
                        <?= ($currentPage > 1)  ? "<li class=\"page-item\" ><a href=\"".$link . ($currentPage - 1) ."\" class=\"page-link\">".L_PAGINATION_PREVIOUS."</a></li>" : "" ?>
                        
                        <?php if($intermediaire == 1)  {
                            for($page = 1; $page <= $pages; $page++) {
                                # Lien vers chacune des pages (activé si on se trouve sur la page correspondante
                                echo '<li class="page-item ';
                                if($currentPage == $page)  echo 'active';
                                echo "\"><a href=\"".$link.$page ."\" class=\"page-link\">".$page."</a></li>";
                            }
                        }
                        else {
                            echo "<li class=\"page-item page-link  active \">
                            ".$currentPage." / ". $pages."
                            </li>"; 
                            
                        }   ?>
                        <!-- Lien vers la page suivante (si on ne se trouve pas sur la dernière page) -->
                        <?= ($currentPage < $pages) ? " <li class=\"page-item\"><a href=\"".$link.($currentPage + 1 )."\" class=\"page-link\">".L_PAGINATION_NEXT."</a></li>" : "" ?>
                        
                    </ul>
                </nav>
                <?php   }
            }
            else {
                echo L_NO_COMMENT;
            }
            
            
            if (!empty($_SESSION['msgcom'])) {
                $message=$_SESSION['msgcom'];
                $row = str_replace('#com_class', 'alert ' . $color, $row);
                unset($_SESSION['msgcom']);
            }
            else {
                $message='';
                
            }
            $row = str_replace('#com_message',$message , $row);
        ?>
        <script>
            const myformtpl =`
            <template id="myform">
            <h3>
            <?php $this->lang('WRITE_A_COMMENT') ?>
        </h3>
        <form id="form" action="<?php echo $_SERVER['REQUEST_URI']; ?>#form" method="post">
            
            <fieldset>
                
                <div class="grid">
                    <div class="col sml-12">
                        <label for="id_name"><?php $this->lang('NAME') ?>* :</label>
                        <input id="id_name" name="name" type="text" size="20" value="" maxlength="30" required="required" />
                    </div>
                </div>
                <div class="grid">
                    <div class="col sml-12 lrg-6">
                        <label for="id_mail"><?php $this->lang('EMAIL') ?> :</label>
                        <input id="id_mail" name="mail" type="text" size="20" value="" />
                    </div>
                    <div class="col sml-12 lrg-6">
                        <label for="id_site"><?php $this->lang('WEBSITE') ?> :</label>
                        <input id="id_site" name="site" type="text" size="20" value="" />
                    </div>
                </div>
                <div class="grid">
                    <div class="col sml-12">
                        <div id="id_answer"></div>
                        <label for="id_content" class="lab_com"><?php $this->lang('COMMENT') ?>* :</label>
                        <textarea id="id_content" name="content" cols="35" rows="6" required="required"></textarea>
                    </div>
                </div>
                
                <?php echo $row;        
                if($captcha == 1 ): ?>
                
                <div class="grid">
                    <div class="col sml-12">
                        <label for="id_rep"><strong><?php echo $this->lang('ANTISPAM_WARNING') ?></strong>*</label>
                        <?php
                            $this->plxMotor->plxCapcha = new plxCapcha(); # Création objet captcha
                            $this->capchaQ(); 
                        ?>
                        <input id="id_rep" name="rep" type="text" size="2" maxlength="1" style="width: auto; display: inline;" required="required" />
                    </div>
                </div>
                
                <?php endif; ?>     
                
                <div class="grid">
                    <div class="col sml-12">
                        <input type="hidden" id="num" name="num" value="<?php echo $num + 1 ?>"/>
                        <input type="hidden" id="id_parent" name="parent" value="" />
                        <input class="blue" type="submit" value="<?php $this->lang('SEND') ?>" />
                    </div>
                </div>
                
            </fieldset>
            
        </form>
    </template>`;
</script>

<script>
    window.addEventListener("load", (event) => {
        document.body.insertAdjacentHTML( 'afterbegin', myformtpl);
        const MyForm =document.querySelector('#staticsComments');
        let template = document.getElementById("myform");
        let templateContent = template.content;
        MyForm.appendChild(templateContent);
    });
</script>
<script>
    function replyCom(idCom) {
        document.getElementById('id_answer').innerHTML='<?php $this->lang('REPLY_TO'); ?> :';
        document.getElementById('id_answer').innerHTML+=document.getElementById('com-'+idCom).innerHTML;
        document.getElementById('id_answer').innerHTML+='<input type="hidden" name="index" value="'+document.getElementById('com-'+idCom).getAttribute('data-index') +'">';
        document.getElementById('id_answer').innerHTML+='<input type="hidden" name="level" value="'+document.getElementById('com-'+idCom).getAttribute('data-level') +'">';
        document.getElementById('id_answer').innerHTML+='<a rel="nofollow" href="#form" onclick="cancelCom()"><?php $this->lang('CANCEL'); ?></a>';
        document.getElementById('id_answer').style.display='inline-block';
        document.getElementById('id_parent').value=idCom;
        document.getElementById('id_content').focus();
    }
    function cancelCom() {
        document.getElementById('id_answer').style.display='none';
        document.getElementById('id_parent').value='';
        document.getElementById('com_message').innerHTML='';
    }
    var parent = document.getElementById('id_parent').value;
    if(parent!='') { replyCom(parent) }
</script>
</div>
<?php } ?>
