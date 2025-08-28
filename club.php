<?php
/*
SMOS - Sport Manager Open Source
http://snyzone.fr/smos/

Le projet est open source - sous license GPL
Vous êtes libre de l'utiliser mais pas à des fins commercial

Codé par Ysn38 - Yannick San Nicolas - ysn38@snyzone.fr
15/05/09	Création
*/

//Session
session_start();
if(empty($_SESSION['pseudo'])) header('location: index.php');

//Protection contre les injections
define('SMOSPHP', 1);
$CONF = array();
$FORM = array();

//Configuration de base
$CONF['path'] = '.';

//On fusionne les variables GET et POST et on les protèges
$FORM = array_merge($_GET, $_POST);

//Mise à 0 des variables pour le nombre de requête, et le temps de la page
$nombrerequetes = 0;
$timer_requetes = 0;

//Function base de donnée et fichier connexion
include('settings_sql.php');
include('sources/class/class_sql.php');
$sql = new sql;
$sql->connect();

//On recupère les config de la table
$req = sql::query("SELECT config_name, config_value FROM configuration");
while($settings = mysql_fetch_assoc($req))
{ $CONF[$settings['config_name']] = $settings['config_value']; }

//Function de base
require_once("{$CONF['path']}/sources/class/class_club.php");
$club = new club;

//Zone joueurs en ligne
$ip = ip2long($_SERVER['REMOTE_ADDR']);
if (!isset($_SESSION['id'])) $id=0;
else $id = intval($_SESSION['id']);
mysql_query('INSERT INTO whosonline VALUES('.$id.', '.time().','.$ip.')
ON DUPLICATE KEY UPDATE online_time = '.time().' , online_id = '.$id.'');
$time_max = time() - (60 * 10);
mysql_query('DELETE FROM whosonline WHERE online_time < '.$time_max);

//Changement de canal de minichat
if(isset($FORM['canal'])) $club->changecanal($FORM['canal'], $_SESSION['id']);

//Changement de langue
if(isset($FORM['lang'])) $club->changelanguage($FORM['lang'], $_SESSION['id']);

//Zone auto (toujours avant info management)
$current_day = date('w');
if(isset($CONF['salaire_day']) && $current_day == $CONF['salaire_day']) $club->salaire_hebdo($CONF['salaire_day']); //Retenu hebdomadaire (salaire)
if(isset($CONF['recette_day']) && $current_day == $CONF['recette_day']) $club->recette_hebdo($CONF['recette_day']); //Argent du sponsor
if(isset($CONF['training_day']) && $current_day == $CONF['training_day']) $club->training_hebdo($CONF['training_day']); // Entrainement
$club->purge_demande_ma(); //On enleve les demandes dont la date est expiré
$club->timestamp_suspension(); //Suspension des joueurs
$club->timestamp_construction($_SESSION['team_id']); //Les constructions du stade et des centres

//Zone info Management
$info = $club->info_alljunction($_SESSION['pseudo']);

//Zone match
include("sources/class/class_match_apl.php");
$match_apl = new match_apl;
include("sources/class/class_match.php");
$match = new match;

$match-> match_start($info['compet_id'], NULL, $info['team_id'], 1, $CONF);
$match-> match_action($info['compet_id'], NULL, $info['team_id'], 1, $CONF);

//Choix de la langue (à partir de la table comptes)
include("languages/" . $info['lang'] . ".php");

$page = (!empty($FORM['zone'])) ? htmlentities($FORM['zone']) : 'main';
$array_pages = array(
	//Base
	'main' => 'pages/main.php',
	'deconnexion' => 'pages/logout.php', 
	//Zone
	'bureaumanager' => 'pages/bureaumanager.php', 
	'public' => 'pages/public.php', 
	'management' => 'pages/management.php', 
	'competition' => 'pages/competition.php', 
	'match' => 'pages/match.php', 
	'marchetransfert' => 'pages/marchetransfert.php', 
	'forums' => 'pages/support.php', 
	'support' => 'pages/support.php', 
	'manuel' => 'pages/support.php', 
					);
	
if(!array_key_exists($page, $array_pages)) include('pages/erreur.php');
elseif(!is_file($array_pages[$page])) include('pages/erreur.php');
else
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head> 
<title><?php echo SITE . ' - ' . TT_INDEX;  ?></title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1"> 
<meta http-equiv="Pragma" content="no-cache" />
<meta name="description" content="Projet open source de création d'un script php de management d’équipe qui peut être adapté à n’importe quel sport (d’équipe).">
<meta name="keywords" content="jeu, jeux, php, sql, foot, rugby, hockey, sport, équipe, team, management, manager, script, open, source, free, gratuit, libre">
<meta name="author" content="Yannick San Nicolas">
<meta name=identifier-url content="http://snyzone.fr/smos">
<meta name="reply-to" content="ysn38@emu-fr.com">
<meta name="revisit-after" content="7 days">
<meta name="category" content="Jeux PHP">
<link rel="shortcut icon" href="smos.ico">
<link href="club.css" rel="stylesheet" type="text/css" />
</head>
<body>
<a name="top" id="top"></a>
<div id="page_haut">
  <div style="float: left;">&nbsp;
  <a href="club.php?lang=Francais"><img src="images/icone/fr.gif" width="20" height="13" alt="Francais" border="0" /></a>&nbsp;
  <a href="club.php?lang=English"><img src="images/icone/us.gif" width="20" height="13" alt="English" border="0" /></a></div>
  <div style="float: right;" align="right"><?php echo TITLE; ?>&nbsp;&nbsp;&nbsp;</div>
</div>
<br />
<div id="ipdwrapper"><!-- IPDWRAPPER -->
<!-- TOP TABS -->
<div class="tabwrap-main">
 <div <?php if(isset($FORM['zone'])) echo $club->menutab($FORM['zone'], 'main'); else echo'class="tabon-main"'; ?>><img src="images/club/vide.png" style="vertical-align: middle;"> <a href="club.php"><?php echo 'BUREAU DU MANAGER'; ?></a></div>
 <div <?php if(isset($FORM['zone'])) echo $club->menutab($FORM['zone'], 'management'); else echo'class="taboff-main"'; ?>><img src="images/club/vide.png" style="vertical-align: middle;"> <a href="club.php?zone=management"><?php echo 'MANAGEMENT'; ?></a></div>
 <div <?php if(isset($FORM['zone'])) echo $club->menutab($FORM['zone'], 'competition'); else echo'class="taboff-main"'; ?>><img src="images/club/vide.png" style="vertical-align: middle;"> <a href="club.php?zone=competition"><?php echo 'COMPETITION'; ?></a></div>
 <div <?php if(isset($FORM['zone'])) echo $club->menutab($FORM['zone'], 'marchetransfert'); else echo'class="taboff-main"'; ?>><img src="images/club/vide.png" style="vertical-align: middle;"> <a href="club.php?zone=marchetransfert"><?php echo 'RECRUTEMENT'; ?></a></div>
 <div <?php if(isset($FORM['zone'])) echo $club->menutab($FORM['zone'], 'support'); else echo'class="taboff-main"'; ?>><img src="images/club/help.png" style="vertical-align: middle;"> <a href="club.php?zone=support"><?php echo 'FORUM & SUPPORT'; ?></a></div>
 <div class="logoright"></div>
</div>
<!-- / TOP TABS -->
<div class="sub-tab-strip">
 <div class="global-memberbar">
 <?php echo 'Bienvenue' . ' <strong>' . $_SESSION['pseudo']; ?></strong> [<?php if($_SESSION['rang'] >= 5) echo'<a href="admin/index.php">' . ADMIN . '</a> · '; ?>
 <a href="club.php?zone=forum&amp;page=profil&m=<?php echo $_SESSION['id']; ?>&amp;action=consulter"><?php echo PROFIL; ?></a> · 
 <a href="club.php?zone=deconnexion"><?php echo DECO; ?></a>]</div>
</div>
<div class="outerdiv" id="global-outerdiv"><!-- OUTERDIV -->
 <table class="tablewrap" width="100%" cellpadding="0" cellspacing="8">
   <tbody>
    <tr>
	  <td id="rightblock" valign="top" width="80%">
	  <?php include('pages/minichat.php'); ?>
	  </td>
	  <td id="rightblock" valign="top" width="20%">
	  <div style="border-bottom: 1px solid rgb(237, 237, 237); font-size: 25px; padding-left: 7px; letter-spacing: -2px;">
	  <div align="center">
	  <?php 
	  if($info['fanion'] != NULL AND $info['fanion_valid'] == 1)
	  {
		echo'<img src="upload/fanion/'.$info['fanion'].'" width="150" height="150" style="vertical-align: middle;">';
	  }
	  
	  else { echo'<img src="images/icone/nologo.png" style="vertical-align: middle;">'; } ?>
	  <br />
	  <?php echo $info['team_name'];  ?></div>
	  </div>
	  </td>
	</tr>
   </tbody>
 </table>
<?php include($array_pages[$page]); ?>
 <table class="tablewrap" width="100%" cellpadding="0" cellspacing="8">
   <tbody>
    <tr>
	  <td id="rightblock" valign="top" width="100%">
<?php
//Décompte des membres
$time_max = time() - (60 * 10);
$requete_count_membres = mysql_query('SELECT account_id, pseudo 
FROM whosonline
LEFT JOIN comptes ON account_id = online_id
WHERE online_time > '.$time_max);
$count_membres = mysql_num_rows($requete_count_membres);
$texte_a_afficher = "<strong>Liste des personnes en ligne : </strong><br />";
while ($data_count_membres = mysql_fetch_assoc($requete_count_membres))
{
$texte_a_afficher .= '<a href="./club.php?page=profil&amp;m='.$data_count_membres['account_id'].'&amp;action=consulter">
'.stripslashes(htmlspecialchars($data_count_membres['pseudo'])).'</a> ,';
}
$texte_a_afficher = substr($texte_a_afficher, 0, -1);
echo'<div align="center">'.$texte_a_afficher.'</div>';
?>
	  </td>
	</tr>
   </tbody>
 </table>
</div>
<br />
<br />
<div align="center"><a href="http://snyzone.fr/smos/" target="new"><strong>SMOS - Sport Manager Open Source <?php echo date('Y'); ?> - Tous droits r&eacute;serv&eacute;</strong></a><br />
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
</div><!-- / IPDWRAPPER -->
</body>
</html>
<?php
}
?>