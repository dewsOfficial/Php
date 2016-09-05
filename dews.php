<?php
//connexion a la base de donnée
function important(){
    $bdd = new mysqli('localhost', 'username', 'password', 'database');
    ini_set('display_errors', 1);
    $erreur = "";
    return $bdd;
}
//verification de connexion
function membreco(){
    session_start();
    if (!isset($_SESSION['id']) && !isset($_SESSION['mail']) && !isset($_SESSION['mdp'])){
        header('Location: index.php');
        exit();
    }else{
        $donne = select_particulier("*","Utilisateur","id_utilisateur",$_SESSION['id']);
        return $donne;
    }
}






////////////////
//FONCTION SQL//
////////////////

//Fonction selectionner une donnée
function select($colonne,$table){
    $bdd = important();
    $reponse = $bdd->query("SELECT '".$colonne."' FROM " . $table . " ");
    $donne = $reponse->fetch_array();
    return $donne;
}
//Fonction selectionner une donnée particuliere
function select_particulier($colonne,$table,$apreswhere,$atribut){
    $bdd = important();
    $reponse = $bdd->query("SELECT " .$colonne . " FROM " . $table . " WHERE " . $apreswhere . "='" . $atribut . "'");
    $donne = $reponse->fetch_array();
    return $donne;
}
//Fonction pour inserer des données
function insererdonnee($tableau,$tableau_c,$table){
    $bdd = important();
    $i = 0;
    $b = "";
    $c = "";
    while($i<count ($tableau)){
        $tableau[$i] = "'".htmlentities($tableau[$i])."'";
        $b = $b.$tableau[$i];
        $c = $c.$tableau_c[$i];
        $i++;
        if($i ==count ($tableau)){
            break;
        }else{
            $b = $b.",";
            $c = $c.",";
        }
    }
    $reponse1 = $bdd->prepare("INSERT INTO ".$table." ($c) VALUES ($b)");
    $donnes1 = $reponse1->execute();

}





////////////////////
//FONCTION BOOLEAN//
////////////////////
//True si vide
function vide($colonne,$table){
    $bdd = important();
    $a = select($colonne,$table);
    if($a == 0){
        return true;
    }else{
        return false;
    }
}
//SI true la donnée existe
function ifexist($colonne,$table,$apreswhere,$atribut){
    $bdd = important();
    $a = select_particulier($colonne,$table,$apreswhere,$atribut);
    if($a == 0){
        return true;
    }else{
        return false;
    }
}
//SI true c'est bien un mail
function verifmail($mail){
    if(filter_var($mail, FILTER_VALIDATE_EMAIL)){
        return true;
    }else{
        return false;
    }
}
//SI true c'est bien vide
function verifvide($tableau){
    $a = true;
    foreach($tableau as $j){
        if(empty($j)){
            $a = true;
            break;
        }else{
            $a = false;
        }
    }
    return $a;
}



////////////////////
//FONCTION SPECIAL//
////////////////////

//Inscription
function inscription(){
    $bdd = important();
    $erreur = "";

    if (isset($_POST['sub'])){

        $nom = mysqli_escape_string($bdd,$_POST['nom']) ;
        $prenom = mysqli_escape_string($bdd,$_POST['prenom']) ;
        $identifiant = mysqli_escape_string($bdd,$_POST['identi']) ;
        $mail = mysqli_escape_string($bdd,$_POST['mail']) ;
        $mdp = md5($_POST['mdp']);
        $mdp = mysqli_escape_string($bdd,$mdp) ;

        $nom = ucfirst(strtolower($nom));
        $prenom = ucfirst(strtolower($prenom));
        $a = array($nom,$prenom,$identifiant,$mail,$mdp,date("y-m-d"));
        $b = array("Nom","Prenom","Identifiant","Mail","Mot_de_passe","Date_d_inscription","Admin");
        if(!verifvide($a)){

            if(verifmail($mail)){
                if(ifexist("*","Utilisateur","Mail",$mail) || ifexist("*","Utilisateur","Identifiant",$identifiant)){
                    if(vide("*","Utilisateur")){
                        $a[6] = 1;
                    }
                    insererdonnee($a,$b,"Utilisateur",$bdd);
                    $erreur = "Vous venez de vous inscrire <a href='connexion.php'>Connectez vous</a>";
                }else{
                    $erreur = "L'adresse mail ou le nom d'utilisateur est déja utilisé";
                }
            }else{
                $erreur = "<span>L'adresse mail n'est pas valide</span>";
            }
        }else{
            $erreur = "<span>Un des champs n'a pas été rempli</span>" ;
        }
    }
    return $erreur;
}
//connexion
function connexion(){
    $bdd = important();
    $erreur = "";
    if (isset($_POST['sub'])){
        $identifiant = mysqli_escape_string($bdd,$_POST['identi']);

        $mdp = md5($_POST['mdp']);
        $mdp = mysqli_escape_string($bdd,$mdp) ;
        if((!ifexist("*","Utilisateur","Mail",$identifiant) || !ifexist("*","Utilisateur","Identifiant",$identifiant)) && !ifexist("*","Utilisateur","Mot_de_passe",$mdp)){
            if(!ifexist("*","Utilisateur","Mail",$identifiant)){
                $donne = select_particulier("*","Utilisateur","Mail",$identifiant);
            }
            if(!ifexist("*","Utilisateur","Identifiant",$identifiant)){
                $donne = select_particulier("*","Utilisateur","Identifiant",$identifiant);
            }

            session_start();
            $_SESSION['id'] = $donne['id_utilisateur'];
            $_SESSION['mail'] = $donne['Mail'];
            $_SESSION['mdp'] = $donne['Mot_de_passe'];
            header ('Location: pageperso.php');
            exit();
        }else{
            $erreur = "Cela ne correspond a aucun un compte";
        }

    }
    return $erreur;
}
//Ecrire un article
function ecrire_article($id){
    $bdd = important();
    $erreur = "";

    if(isset($_POST['soumettre'])){
        $titre = mysqli_escape_string($bdd,$_POST['titre']) ;
        $categorie = mysqli_escape_string($bdd,$_POST['catégorie']) ;
        $article = mysqli_escape_string($bdd,nl2br($_POST['article']));
        $article = str_replace('<br />', '</p><p>', $article);
        $a = array($id,$titre,$categorie,$article,date("y-m-d H:i:s"));
        $b = array("id_utilisateur","Titre","Categorie","Contenu","date_publi");
        if(!verifvide($a)){
            insererdonnee($a,$b,"Publication");
            $erreur = "Votre article a bien été soumi</a>";
        }else{
            $erreur = "Une donnée manquante";
        }
    }
    return $erreur;
}
//Affiche les articles en haut
function index_article(){
    $bdd = important();
    $erreur = "";
    $reponse = $bdd->query("SELECT * FROM Publication ORDER BY date_publi DESC");
    $en_haut = "";
    $i = 1;
    while($donne = $reponse->fetch_array()){
        if($i <=2){
            $en_haut = $en_haut."
            <a href='pageweb/article.php?id=".$donne['id_publication']."'>
                <div class='featured bloc".$i." ".couleur_categorie($donne['Categorie'])."'>
                    <div class='titre'><p>".$donne['Titre']."</p></div>
                </div>
            </a>";
            $i++;
        }else{
            break;
        }
    }
    if($i<2){
        $en_haut = $en_haut."Il n'y a pas encore d'article";
    }
    return $en_haut;
}
//Affiche les articles au milieu
function index_article3($categorie){
    $bdd = important();
    $erreur = "";
    $reponse = $bdd->query("SELECT * FROM Publication WHERE Categorie='".$categorie."' ORDER BY date_publi DESC");
    $en_haut = "";
    $i = 1;
    while($donne = $reponse->fetch_array()){
        if($i <=2){
            $en_haut = $en_haut."
            <a href='pageweb/article.php?id=".$donne['id_publication']."'>
                <div class='article ".couleur_categorie($donne['Categorie'])."'><p>".$donne['Titre']."</p></div>
            </a>";
            $i++;
        }else{
            break;
        }
    }
    if($i<2){
        $en_haut = $en_haut."Il n'y a pas encore d'article";
    }
    return $en_haut;
}
//Affiche les articles en bas
function index_article2(){
    $bdd = important();
    $erreur = "";
    $reponse = $bdd->query("SELECT * FROM Publication ORDER BY date_publi DESC");
    $en_haut = "";
    $i = 1;

    while($donne = $reponse->fetch_array()){
        if($i <=6 && $i>2){
            $nom = select_particulier("Prenom","Utilisateur","id_utilisateur",$donne['id_utilisateur']);
            $en_haut = $en_haut."
            <div class='article_top'>
                <a href='pageweb/article.php?id=".$donne['id_publication']."'><div class='img_top ".couleur_categorie($donne['Categorie'])."'><div class='auteur'>".$nom['Prenom'].", le ".$donne['date_publi']."</div></div></a>
                        <div class='titre_top'>".$donne['Titre']."</div>
                        <div class='preview_top'>".substr($donne['Contenu'],1,300);

            if(strlen ($donne['Contenu'] )> 300){
                $en_haut = $en_haut."...<a href='pageweb/article.php?id=".$donne['id_publication']."'>Lire la suite</a>";
            }
            $en_haut = $en_haut."</div>
                    </div>";


        }
        $i++;
    }

    return $en_haut;
}
//Permet de mettre une couleur sur la categorie
function couleur_categorie($categorie){
    $couleur = "";
    if($categorie=="Desserts"){
        $couleur =  "b_rouge";
    }
    if($categorie=="Gateau"){
        $couleur =  "b_bleu";
    }
    if($categorie=="Entrées"){
        $couleur =  "b_vert";
    }
    if($categorie=="Coktail"){
        $couleur =  "b_jaune";
    }
    return $couleur;
}
//Supprimer les articles
function supprimer_article(){
    if(isset($_POST['supprimer'])){
        echo $_POST['supprimer'];
        $bdd = important();
        $sql1 = "DELETE FROM Commentaire WHERE id_publication=".$_POST['supprimer'];
        $sql = "DELETE FROM Publication WHERE id_publication=".$_POST['supprimer'];

        if ($bdd->query($sql1) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $bdd->error;
        }
        if ($bdd->query($sql) === TRUE) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $bdd->error;
        }
        $sql1 ="alter table Utilisateur check constraint all";
        $sql2 = "alter table Commentaire check constraint all";
    }
}
//Afficher tout les articles catégories
function article_cate($categorie){
    $bdd = important();
    $erreur = "";
    $reponse = $bdd->query("SELECT * FROM Publication WHERE Categorie='".$categorie."' ORDER BY date_publi DESC");
    $en_haut = "";

    while($donne = $reponse->fetch_array()){

        $nom = select_particulier("Prenom","Utilisateur","id_utilisateur",$donne['id_utilisateur']);
            $en_haut = $en_haut."
            <div class='article_top'>
                <a href='article.php?id=".$donne['id_publication']."'><div class='img_top ".couleur_categorie($donne['Categorie'])."'><div class='auteur'>".$nom['Prenom'].", le ".$donne['date_publi']."</div></div></a>
                        <div class='titre_top'>".$donne['Titre']."</div>
                        <div class='preview_top'>".substr($donne['Contenu'],1,300);

            if(strlen ($donne['Contenu'] )> 300){
                $en_haut = $en_haut."...<a href='article.php?id=".$donne['id_publication']."'>Lire la suite</a>";
            }
            $en_haut = $en_haut."</div>
                    </div>";
    }
    return $en_haut;
}





?>
