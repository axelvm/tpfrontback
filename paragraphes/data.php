<?php

error_reporting(E_ERROR); // enlever les messages 'deprecated'...

include("config.php"); 
// penser à maj le nom de la base et le mot de passe
include("maLibSQL.pdo.php");

/*
http://.../data.php?action=updateP&id=2&contenu=test
{"feedback":"ok","paragraphes":[]} 

http://.../data.php?action=getP

{"feedback":"ok","paragraphes":[{"id":"1","contenu":"premier","ordre":"1"},{"id":"2","contenu":"test","ordre":"2"},{"id":"3","contenu":"toto","ordre":"3"}]} 

http://.../data.php?action=addP&ordre=3&contenu=toto
{"feedback":"ok","paragraphes":[],"id":4} 

http://.../data.php?action=delP&id=3
{"feedback":"ok","paragraphes":[]} 
*/


$data["feedback"] = "ko"; 
$data["paragraphes"] = array();

if (isset($_GET["action"]))
{
	switch($_GET["action"]) {

		case "delP" : 
		if (isset($_GET["id"])) $id = $_GET["id"];
		if ($id) {
			$SQL = "DELETE FROM paragraphes WHERE id='$id'";
			SQLUpdate($SQL);
			$data["feedback"] = "ok"; 
		}

		// id, contenu, ordre 
		case "addP" : 
			// Ajoute un P. et renvoie son identifiant

			$contenu = $ordre = false;
			if (isset($_GET["contenu"])) $contenu = $_GET["contenu"]; 
			if (isset($_GET["ordre"])) $ordre = $_GET["ordre"];

			if (($ordre !== false) && $contenu) 			
			{
				$SQL = "INSERT INTO paragraphes(ordre, contenu) VALUES ('$ordre','$contenu')"; 
				$nextId = SQLInsert($SQL);
				$data["feedback"] = "ok"; 
				$data["id"] = $nextId; 
			}
		break;

		case "updateP" : 
			// Modifie un P. dont le nom est passé en paramètre
			if (isset($_GET["contenu"])) $contenu = $_GET["contenu"]; 
			if (isset($_GET["id"])) $id = $_GET["id"];

			if ($id && $contenu) {
				$SQL = "UPDATE paragraphes SET contenu='$contenu' WHERE id='$id'";
				SQLUpdate($SQL);
				$data["feedback"] = "ok";
			}
		
		break;

		case "updateOrdre" : 
			// Change l'ordre d'un paragraphe 

			// On indique l'id du paragraphe concerné, 
			// ainsi que le nouveau numéro d'ordre 
			if (isset($_GET["id"])) $id = $_GET["id"];
			if (isset($_GET["ordre"])) $ordre = $_GET["ordre"];

			$SQL = "SELECT id FROM paragraphes WHERE ordre = '$ordre'"; 
			if (SQLGetChamp($SQL)) {
				// Il peut s'agir d'un numéro d'ordre qui est déjà utilisé
				// On va décaler les ordres des paragraphes existants après
				// TODO: SEULEMENT si c'est le CAS (doit être inutile ?)
				$SQL = "UPDATE paragraphes SET ordre = ordre+1 
							WHERE ordre >= '$ordre'"; 
				SQLUpdate($SQL);
			}

			// avant de changer 
			// l'ordre du paragraphe concerné 
			$SQL = "UPDATE paragraphes SET ordre = '$ordre' WHERE id='$id'";
			SQLUpdate($SQL);

			// Il faudrait propager les modifications aux paragraphes du client : 
			// On renvoie TOUT en ne mettant pas BREAK, 
			// ce qui active le traitement du cas dessous 

			// TODO: il faudrait faire une fonction !
			
		// break; 


		case "getP" : 
			// Renvoie tous les paragraphes de la base de données
			$SQL = "SELECT * FROM paragraphes ORDER BY ordre ASC"; 
			$res = parcoursRs(SQLSelect($SQL));
			$data["feedback"] = "ok"; 
			$data["paragraphes"] = $res;
		break;

	}
}

echo json_encode($data);
?>

