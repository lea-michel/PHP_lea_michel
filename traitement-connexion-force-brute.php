<?php

//connexion à la base de données qui s'appelle "test_protection"
try{
    $myBDD = new PDO('mysql:host=localhost;dbname=test_protection;charset=utf8', 'root', '');
}
catch(Exception $e){
    die('Erreur : ' . $e->getMessage());
}  

if(isset($_POST["mail"]) &&
isset($_POST["mdp"])){
    $mail = $_POST["mail"];
    $mdp = sha1($_POST["mdp"]);
}

    // avant de vérifier si le mot de passe est correcte on vérifie si l'accès au compte est bloqué

    $sql = 'SELECT mail, compteur_co, date_access, compteur_blocage  FROM user WHERE mail=:mail';
    $requete = $myBDD->prepare($sql);
    $requete->bindParam('mail', $mail, PDO::PARAM_STR);
    $requete->execute();
    $tab=$requete->fetch();
    $email=$tab["mail"];
    $nb_tentatives=$tab["compteur_co"];
    $date_access=$tab["date_access"];
    $nb_blocage=$tab["compteur_blocage"];

    // current time à comparer avec timestamp dans base de données (pour vérifier si compte bloqué)
    $time_bdd=strval($date_access);
    $time_converted = strtotime($time_bdd);

    $curtime = time();
        
if ($time_converted>$curtime){
    header("location:connexion.php?error=blocage");
    die();
}

    // on vérifie dans un 2eme temps si l'utilisateur a tenté de se connecter 3 fois ou plus et on le bloque si c'est le cas
    if($nb_tentatives>=3){
        $nb_blocage+=1;
        $nb_tentatives=0;
        $time=$time_converted+(100*1/4*2**($nb_blocage));

        if ($time>24*3600+$curtime){
            $time=24*3600+$curtime;
            $date=date('Y-m-d H:i:s Z',$time);
        }

        //conversion de la nouvelle date d'accès unix en timestamp SQL
        $date = date('Y-m-d H:i:s', $time);
        
        
        //modifications dans la base de données avec la nouvelle date d'accès
        $sql3='UPDATE user SET compteur_blocage=:nb_blocage, compteur_co=:nb_tentatives, date_access=:current_t WHERE mail=:mail';
        $requete3 = $myBDD->prepare($sql3);
        $requete3->bindParam('mail', $mail, PDO::PARAM_STR);
        $requete3->bindParam('nb_blocage', $nb_blocage, PDO::PARAM_INT);
        $requete3->bindParam('nb_tentatives', $nb_tentatives, PDO::PARAM_INT);
        $requete3->bindParam('current_t', $date, PDO::PARAM_STR);
        $requete3->execute();
        header("location:connexion.php?error=Tentative");
        die();
        
    }

    //on cherche dans la base de données si l'email et le mot de passe correspondent à un utilisateur

    $sql4 = 'SELECT * FROM user WHERE mail=:mail AND mdp=:mdp';
    $requete4 = $myBDD->prepare($sql4);
    $requete4->bindParam('mail', $mail, PDO::PARAM_STR);
    $requete4->bindParam('mdp', $mdp, PDO::PARAM_STR);
    $requete4->execute();
    $tab=$requete4->fetch();

    

    if($requete4->rowCount()>0){
        //l'utilisateur est connecté à son compte 
        session_start();
        $_SESSION["user"]=$mail;
        // actualiser dans la base de données compteur de connexion à 0
        $nb_tentatives=0;
        $sql5='UPDATE user SET compteur_co=:nb_tentatives';
        $requete5 = $myBDD->prepare($sql5);
        $requete5->bindParam('nb_tentatives', $nb_tentatives, PDO::PARAM_INT);
        $requete5->execute();
        header("location:accueil.php");
        die();
    }

    else{
        // le mot de passe de l'utilisateur est incorrect
        // augmenter de 1 le compteur des tentatives de connexion
        $nb_tentatives+=1;
        $sql6='UPDATE user SET compteur_co=:nb_tentatives WHERE mail=:mail';
        $requete7 = $myBDD->prepare($sql6);
        $requete7->bindParam('mail', $mail, PDO::PARAM_STR);
        $requete7->bindParam('nb_tentatives', $nb_tentatives, PDO::PARAM_INT);
        $requete7->execute();
        header("location:connexion.php");
        die();
    }









?>