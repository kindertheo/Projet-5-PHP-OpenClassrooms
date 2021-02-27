<?php

namespace App\Controller;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class ArticleController
 * @package App\Controller
 */
class ArticleController extends MainController
{
    /**
     *
     */
    const TWIG = 'article.twig';

    /**
     * @return string
     */
    public function defaultMethod()
    {
        $idArticle = $this->get->getGetVar('idArticle');

        if ($this->isArticleExist($idArticle) == false) {
            $this->redirect('article&method=list');
        }

        $article = $this->articleSql->selectArticle($idArticle);
        $comment = $this->articleSql->selectCommentByArticle($idArticle);

        return $this->render(self::TWIG, ['article' => $article, 'comment' => $comment]);
    }

    /**
     * @return string
     */
    public function listMethod()
    {
        $article = $this->articleSql->selectAllArticle();
        //$blog = $blog->selectAllArticle(); //TODO Afficher seulement 10 Articles maximum, Vérifier s'il y minimum 1 article

        return $this->render('listArticle.twig', ['listArticle' => $article]);
    }




    /**
     * @return string
     */
    /* Admin panel */
    public function listAdminMethod()
    {
        $this->session->isAdmin();

        $req = $this->adminSql->selectArticleAdmin();

        return $this->render('admin/admin.twig', ['article' => $req]);
    }

    /**
     * @return string
     */
    /* Admin panel */
    public function editArticleMethod()
    {
        $this->session->isAdmin();
        $post = $this->post->getPostArray();
        if (!empty($post)) {
            /* Si $_POST existe et possède des données, les données sont ajoutées à la bdd */

            $this->adminSql->updateArticle($post['idArticle'], $post['title'], $post['chapo'], $post['content']);

            $this->redirect('article&method=listArticle');
        }

        /*Si $_POST est vide, renvois sur formulaire pour saisir les données à changer */
        $get = $this->get->getGetVar('idArticle');
        if ($get === false) {
            $this->redirect('admin');
        }

        $req = $this->articleSql->selectArticle($get);

        return $this->render('admin/adminUpdateArticle.twig', ['article' => $req]);

    }

    /**
     *
     */
    /* Admin panel */
    public function deleteArticleMethod()
    {
        $this->session->isAdmin();

        $idArticle = $this->get->getGetVar('idArticle');
        $this->adminSql->deleteArticle($idArticle);

        $this->redirect('article&method=listArticle');
    }

    /**
     *
     */
    /* Admin panel */
    public function approveArticleMethod()
    {
        $this->session->isAdmin();

        $get = $this->get->getGetVar('idArticle');
        if ($get == false) {
            $this->redirect('admin');
        }

        $this->adminSql->approveArticle($get); // Set article.validated to 1. 0 is non approuved article
        $this->redirect('article&method=listArticle');
    }


    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createArticleMethod()
    {
        $this->session->isAdmin();

        $twigPage = 'createArticle.twig';

        $post = $this->post->getPostArray();

        if (!empty($post)) {
            $verify = $this->post->verifyPost();
            if ($verify !== true) {
                return $this->renderTwigErr($twigPage, $verify);
                //return $this->render('createArticle.twig', ['erreur' => $verify]); /
            }
            $this->articleSql->createArticle($post['title'], $post['content'], $post['chapo'], $this->session->getUserVar('idUser'));

            return $this->renderTwigSuccess($twigPage, 'Votre article nous a bien été envoyé! Il ne manque plus qu\'à le valider!');
        } elseif (empty($post)) {

            return $this->render($twigPage);
        }
        //$this->redirect('blog&method=createArticle');
    }


    /**
     * @param $idArticle
     * @return mixed
     */
    private function isArticleExist($idArticle)
    {
        if ($idArticle == false) {

            return false;
        }

        $verify = $this->articleSql->selectIdArticle(); //Verify in database if article exist
        if (array_search($idArticle, array_column($verify, 'idArticle', $idArticle)) === false) {

            return false;
        }

        return true;
    }


}