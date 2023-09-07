<?php

//connexion à la base de données
try{
    $myBDD = new PDO('mysql:host=localhost;dbname=myBDD;charset=utf8', 'root', '');
}
catch(Exception $e){
    die('Erreur : ' . $e->getMessage());
}  

//on vérifie que les deux champs de connexion sont bien remplis et on les stocke dans deux variables différentes
if(isset($_POST["mail"]) &&
isset($_POST["mdp"])){
    $mail = $_POST["mail"];
    $mdp = sha1($_POST["mdp"]);
}


//on cherche dans la base de données les informations de l'utilisateur
    $sql = 'SELECT * FROM user WHERE mail=:mail AND mdp=:mdp';
    $requete = $myBDD->prepare($sql);
    $requete->bindParam('mail', $mail, PDO::PARAM_STR);
    $requete->bindParam('mdp', $mdp, PDO::PARAM_STR);
    $requete->execute();

    var_dump($requete->rowCount());

    //si la recherche a permi de trouver une ligne (donc un utilisateur déjà inscrit) on démarre une session
    //sinon on le redirige vers la page de connexion
    if($requete->rowCount()>0){
        session_start();
        $_SESSION["user"]=$mail;
        header("location:accueil.php");
        die();
    }
    else{
        header("location:connexion.php");
        die();
    }









?>