<?php
session_start();
$idSeminaire = 1;//$_SESSION['idSeminaire'];
require('include/class.pdoSeminaire.inc.php');

$pdo = PdoSeminaire::getInstance();

if(!isset($_REQUEST['action'])){
	$action = 'login';
}
else {
	$action = $_REQUEST['action'];
}

switch($action){
	case 'login' :
		include('vues/v_entete.php');
		include('vues/v_login.php');
		include('vues/v_pied.php');
		break;
	case 'doLogin' :
		$email = (empty($_POST['email']) ? null : $_POST['email']);
		$email = strip_tags($email); 
		$cle   = (empty($_POST['cle']) ? null : $_POST['cle']);
		$cle = strip_tags($cle);
		$okUser = $pdo->getUser($email, $cle);				
		//$okUser[0] = $user object, $okUser[1] = cleok boolean
		if (!$okUser[0]) {
			if (!$okUser[1]) {
				// email inconnu et cle mauvaise....
				header('Location: index.php?action=login');				
				exit(1);
			}else{
				// email inconnu et bonne mauvaise....
				// présente le formulaire d'enregistrement participant (et non les inscriptions !)
				$_SESSION['doLogin_email']=$email;
				$_SESSION['cle'] = $cle;
				header('Location: index.php?action=demandeInscription');
				exit(1);
			}
		} elseif (!$okUser[1]) {
				// email connu mais mauvaise cle ....
				$_SESSION['doLogin_email']=$email;
				$_SESSION['doLogin_cle']='Cle invalide';
				header('Location: index.php?action=login');
				exit(1);			
		}	
		// ok, place l'objet user dans la session
		$_SESSION['user'] = $okUser[0];
		header('Location: index.php?action=seances');
		exit(1);
		break;
			
	case 'seances':
		if (empty($_SESSION['user'])) {
			header('Location: index.php?action=login');
			exit(1);
		}
		$user = $_SESSION['user'];				
		$lesSeances = $pdo->getSeancesBySeminaire($idSeminaire, $user->id);
		$statNbInscr = $pdo->getNombreSeancesInscritesBy($user->id, $idSeminaire);
		include('vues/v_entete.php');
		require('vues/v_seances.php');

		break;
	case 'mesinscriptions':
		if (empty($_SESSION['user'])) {
			header('Location: index.php?action=login');
			exit(1);
		}
		$user = $_SESSION['user'];		
		$lesSeances = $pdo->getMesSeancesBySeminaire($idSeminaire, $user->id);
    $statNbInscr = $pdo->getNombreSeancesInscritesBy($user->id, $idSeminaire);
		include('vues/v_entete.php');
		require('vues/v_seances.php');

		break;
	case 'demandeInscription':
		if ($_SESSION['cle']) {
			$lesAcademies = $pdo->getLesAcademies();
			include('vues/v_entete.php');
			include('vues/v_informations.php');
		}else{
			header('Location: index.php');
			exit(1);
		}			
		break;
	case 'validerDemandeInscription':
		$nom = strip_tags($_POST['nom']);
		$prenom=strip_tags($_POST['prenom']);
		$mail=strip_tags($_POST['mail']);
		$titre=strip_tags($_POST['titre']);
		$academie = strip_tags($_POST['academie']);
		$residencepersonnelle=strip_tags($_POST['residencepersonnelle']);
		$residenceadministrative=strip_tags($_POST['residenceadministrative']);
/*
		if(!verif($mail)){
			$lesAcademies = $pdo->getLesAcademies();
			$lesAteliers = $pdo->getLesJoursCreneauxAteliers();
			include('vues/v_entete.php');
			include('vues/v_informations.php');
			include('vues/v_erreurs.php');
		}
		else {
*/		
			$pdo->enregParticipant($nom,$prenom,$mail,$academie, $residenceadministrative, $residencepersonnelle, $titre);
			//$pdo->envoyerMail($mail);
			$okUser = $pdo->getUser($mail, $_SESSION['cle']);
			if ($okUser[0]) {
			  $_SESSION['user']=$okUser[0];
			  header('Location: index.php?action=seances');
			  exit(1);
			} else {
				$_SESSION['erreur'] = "Echec à l'ennregistemernt";
				header('Location: index.php?action=login');
				exit(1);
			}
	/*	} */		
		break;
	case 'export':
		$lesSeminaires = $pdo->getLesSeminaires();
		include('vues/v_entete.php');
		include('vues/v_export.php');
		break;
	case 'accueil':
		include('vues/v_entete.php');
		include('vues/v_accueil.php');
		break;
	case 'apropos':
		include('vues/v_entete.php');
		include('vues/v_apropos.php');
		break;

	case 'deconnexion' :	
	default :  // deconnexion...
		session_destroy();
		header('location: index.php?action=login');
		exit(1);
		break;
}

