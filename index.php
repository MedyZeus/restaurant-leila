<?php
// pilote de l'application (ou site web)
// parfois appelé "Routeur" ou "Front Controller"

// inclure la config de configuration
include('config/bd.cfg.php');


$route = "";
if (isset($_GET["route"])) {
  // Exemple plat ou plat/tout ou vin/ajouter ou plat/supprimer/15 ... etc
  $route = $_GET["route"];
}

$routeur = new Routeur($route);

$routeur->invoquerRoute();

class Routeur {
  function __construct($r)
  {
   $this->route = $r;
   
  // Autochargement des fichiers de classes
  spl_autoload_register(function($nomClasse) {
    $nomFichier = "$nomClasse.cls.php"; 
    if(file_exists("modeles/$nomFichier")) {
      include("modeles/$nomFichier");
    }
    else if (file_exists("controleurs/$nomFichier")) {
      include("controleurs/$nomFichier");
    }
    else {
      exit("Problème majeur....");
    }
  });  
  }

  public function invoquerRoute() {
    $module = "accueil"; // autres possibilités : plats, vins, ... etc
    $action = "index";
    $param = "";
    $routeTab = explode('/', $this->route);
    
    // Exemples: pour ca plat/supprimer/15 c'est ['plat','supprimer','17'] ou ['plat','tout']
    if (count($routeTab) > 0 && trim($routeTab[0]) != '') {
      // module sera egal a 'plat' et routeTab sera egal a ['supprimer','17'], en fait c comme shift dans JS
      $module = array_shift($routeTab);
      if (count($routeTab) > 0 && trim($routeTab[0]) != '') {
        // module sera egal a 'supprimer' et routeTab sera egal a ['17'], en fait c comme shift dans JS
        $action = array_shift($routeTab);
        $param = $routeTab;
      }
    }

    //instancier le controleur correspondant au module indiqué et invoquer la méthode de cet objet correspondant à l'action indiquée
    $nomControleur = ucfirst($module).'Controleur'; // comme VinControleur
    $nomModele = ucfirst($module).'Modele'; // comme VinModele

    if (class_exists($nomControleur)) {
      if (!is_callable(array($nomControleur, $action))) {
        $action = 'index';
      }
      $controleur = new $nomControleur($nomModele, $module, $action); // instancier le controleur correspondant au module indiqué
      $controleur->$action($param); // invoquer la méthode de cet objet correspondant à l'action indiquée
    } else {
      $controleur = new AccueilControleur();
    }
  }
}