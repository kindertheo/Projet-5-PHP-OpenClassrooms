<?php

namespace App\Controller;


use App\Model\LoginModel;

class LoginController extends MainController
{

    public function DefaultMethod()
    {
        if ($this->isLogged()) $this->redirect('home');
        $view = $this->twig->render('login.twig');
        return $view;
    }

    /**Create password with password_hash('string', PASSWORD_DEFAULT);**/

    public function loginMethod()
    {
        $login = new LoginModel();
        $view = new MainController();
        $post = $this->post->getPostArray();

        if (isset($post['email'])) {
            if ($login->getUser($post['email']) === false) {
                return $view->twig->render('login.twig', ['erreur' => 'Mauvaise adresse mail']);
            }
            $data = $login->getUser($post['email']);
            if (password_verify($post['password'], htmlspecialchars($data['password']))) {
                $sess = new SessionController();
                $sess->login($data); //Ajouter remember me
            }
            return $view->twig->render('login.twig', ['erreur' => 'Mot de passe incorrect']);

        }
        //$this->redirect('home');

    }

    public function logoutMethod()
    {
        $logout = new SessionController();
        $logout->logout();
        $this->redirect('home');
    }

    public function registerMethod()
    {
        $post = $this->post->getPostArray();

        if (!empty($post)) {
            if ($post['password1'] != $post['password2']) return $this->twig->render('register.twig', ['erreur' => 'Les mots de passes sont différents']);

            $post['password'] = password_hash($post['password1'], PASSWORD_DEFAULT);
            $register = new LoginModel();
            $register->createUser($post['pseudo'], $post['email'], $post['password']);
            $this->redirect('home');
        }
        return $this->twig->render('register.twig');
    }

    public function emailForgetMethod(){
        $post = $this->post->getPostVar('email');
        $get = $this->get->getGetArray();
        if(!empty($post)){
            $search = new LoginModel();
            $search = $search->getUser($post); //Vérifie si l'utilisateur est dans la base de données
            if($search === false) return $this->twig->render('forget.twig', ['erreur' => 'Vous n\'avez pas de compte chez nous!']);

            $mail = new MailController();
            $user = array('email' => $search['email'],
                'id_user' => $search['id_user']);
            $mail->sendForgetEmailMethod($user); //Envoi le mail

            if($mail === false) return $this->twig->render('forget.twig', ['erreur' => 'Une erreur est survenue lors de l\'envoi du mail']);
            return $this->twig->render('forget.twig', ['success' => 'Nous vous avons envoyé un lien par email. Il sera actif que 15 minutes.']);
        }/**elseif(empty($post) AND !empty($get['token'])){

            $this->redirect('login&method=changePassword&token='.$get['token'].'&iduser='.$get['iduser']);
            //return $this->twig->render('changePassword.twig', ['token' => $get['token'], 'iduser' => $get['iduser']]); //Renvois a la page pour changer de mot de passe
        }**/
        return $this->twig->render('forget.twig');
    }

    public function changePasswordMethod(){
        $post = $this->post->getPostArray();
        $get = $this->get->getGetArray();
        if(!empty($post)){

            $req = new LoginModel();
            $verif = $req->getUserById($get['iduser']);

            if($verif === false) {
                return $this->twig->render('changePassword.twig', ['erreur' => 'Il y a eu une erreur!', 'token' => $get['token'], 'iduser' => $get['iduser']]);
            } //Si l'on ne trouve pas l'utilisateur
            if($get['token'] != $verif['token']) {
                return $this->twig->render('changePassword.twig', ['erreur' => 'Le token n\'est pas bon!', 'token' => $get['token'], 'iduser' => $get['iduser']]);
            } //Si les token sont différents

            $date_verif = date_create_from_format('Y-m-d H:i:s', $verif['token_expiration']);  //Si le token expire
            $date = new \DateTime("now");
            if($date > $date_verif) return $this->twig->render('changePassword.twig', ['erreur' => 'Le token a expiré!', 'token' => $get['token'], 'iduser' => $get['iduser']]);

            if($post['password1'] == $post['password2']){
                $password = password_hash($post['password1'], PASSWORD_DEFAULT);
                $req->changePassword($password, $get['iduser']);
                return $this->twig->render('login.twig', ['success' => 'Votre mot de passe a bien été modifié!', 'token' => $get['token'], 'iduser' => $get['iduser']]);
            }
            return $this->twig->render('changePassword.twig', ['erreur' => 'Les mots de passes entrés ne sont pas identiques!', 'token' => $get['token'], 'iduser' => $get['iduser']]);
        }
        elseif(empty($post)){
            return $this->twig->render('changePassword.twig',['token' => $get['token'], 'iduser' => $get['iduser']]);

        }
    }
}