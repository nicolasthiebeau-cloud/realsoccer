<?php 
/*
SMOS - Sport Manager Open Source
http://snyzone.fr/smos/

Le projet est open source - sous license GPL
Vous êtes libre de l'utiliser mais pas à des fins commercial

Codé par Ysn38 - Yannick San Nicolas - ysn38@snyzone.fr
Création : 15/05/09
*/

//Protection contre les injections
define('SMOSPHP', 1);
$CONF = array();
$FORM = array();

//Configuration de base
$CONF['path'] = '.';
$TMPL['version'] = '0.8.4';

//On fusionne les variables GET et POST
$FORM = array_merge($_GET, $_POST);

//Mise à 0 des variables pour le nombre de requête, et le temps de la page
$nombrerequetes = 0;
$timer_requetes = 0;

//Function base de donnée et fichier connexion
include('settings_sql.php');
include('sources/class/class_sql.php');
$sql = new sql; // Classes pour le SQL
$sql->connect();

//On recupère les config de la table
$req = sql::query("SELECT config_name, config_value FROM configuration");
while($settings = mysql_fetch_assoc($req))
{ $CONF[$settings['config_name']] = $settings['config_value']; }

//Function de base
require_once("{$CONF['path']}/sources/class/class_index.php");
$index = new index;

//Choix de la langue via le navigateur
$language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$language = $language{0}.$language{1};
$language = $index->replangindex(strtoupper($language));

//Choix de la langue (à partir de la table comptes)
include("languages/" . $language . ".php");

$page = (!empty($FORM['page'])) ? htmlentities($FORM['page']) : 'login';
$array_pages = array(
	//base
	'login' => 'pages/login.php',
	'newaccount' => 'pages/newaccount.php',
					);
	
if(!array_key_exists($page, $array_pages)) include('pages/login.php');
elseif(!is_file($array_pages[$page])) include('pages/erreur.php');
else include($array_pages[$page]);
?>
<br />
<br />
<div class="global-copyright" align="center"><a href="http://snyzone.fr/smos/" target="new">SMOS - Sport Manager Open Source <?php echo date('Y'); ?> - Tous droits r&eacute;serv&eacute;</a><br />
<?php echo 'Page contenant '.$nombrerequetes.' requ&ecirc;tes mysql execut&eacute;es en '.$timer_requetes.' secondes'; ?>
<br /><br />
<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/2.0/fr/">
<img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/2.0/fr/88x31.png" /></a>
<br /><br />
<a href="http://www.xiti.com/xiti.asp?s=372941" title="WebAnalytics">
<script type="text/javascript">
<!--
Xt_param = 's=372941&p=smos-Serveur';
try {Xt_r = top.document.referrer;}
catch(e) {Xt_r = document.referrer; }
Xt_h = new Date();
Xt_i = '<img width="80" height="15" border="0" alt="" ';
Xt_i += 'src="http://logv3.xiti.com/vcg.xiti?'+Xt_param;
Xt_i += '&hl='+Xt_h.getHours()+'x'+Xt_h.getMinutes()+'x'+Xt_h.getSeconds();
if(parseFloat(navigator.appVersion)>=4)
{Xt_s=screen;Xt_i+='&r='+Xt_s.width+'x'+Xt_s.height+'x'+Xt_s.pixelDepth+'x'+Xt_s.colorDepth;}
document.write(Xt_i+'&ref='+Xt_r.replace(/[<>"]/g, '').replace(/&/g, '$')+'" title="Internet Audience">');
//-->
</script>
<noscript>
<img width="80" height="15" src="http://logv3.xiti.com/vcg.xiti?s=372941&p=smos-Serveur" alt="WebAnalytics" />
</noscript></a></div>
<br />
</body>
</html>