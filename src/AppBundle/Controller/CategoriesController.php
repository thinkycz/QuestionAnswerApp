<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Question;
use AppBundle\Entity\Answer;
use AppBundle\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoriesController extends Controller
{
    /**
     * @Route(path="/category/{slug}/page/{page}", name="showCategory", defaults={"page" = 1})
     * @Template()
     * @param $slug
     * @param int $page
     * @return array
     */
    public function showAction($slug, $page = 1)
    {
        //Variables
        $currentPage = $page;
        $articlesOnOnePage = 8;

        //Sidebar requests
        $totalQuestions = $this->getDoctrine()->getRepository(Question::class)->count();
        $totalAnswers = $this->getDoctrine()->getRepository(Answer::class)->count();
        $categories = $this->getDoctrine()->getRepository(Category::class)->getFirst(8);
        $favoriteQuestions = $this->getDoctrine()->getRepository(Question::class)->getTopXByAnswerCount(8);
        $topUsers = $this->getDoctrine()->getRepository(User::class)->getTopXByKarma(8);

        //Page requests
        $totalQuestionsInCategory = $this->getDoctrine()->getRepository(Question::class)->getCountQuestionsInCategoryBySlug($slug);
        $questions = $this->getDoctrine()->getRepository(Question::class)->getOnePageQuestionsInCategoryBySlug($currentPage, $articlesOnOnePage, $slug);
        $totalPages = ceil($totalQuestionsInCategory/$articlesOnOnePage);
        $currentCategory = $this->getDoctrine()->getRepository('AppBundle:Category')->findBySlug($slug)[0];

        if(!$questions)
        {
            $this->get('thinky.appbundle.sweet_alert')->warning('Nastala chyba', 'Kategorie neexistuje nebo je prázdná.');
            return $this->redirectToRoute('home');
        }

        return compact('questions', 'currentPage', 'totalPages', 'categories', 'totalQuestions', 'totalAnswers', 'favoriteQuestions', 'topUsers', 'slug', 'currentCategory');
    }
}