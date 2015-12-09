<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * @Route(path="/search/", name="searchAction")
     * @Template()
     * @internal param $queryString
     * @param Request $request
     * @return array
     */
    public function searchAction(Request $request)
    {
        $queryString = $request->get('query_string');

        $categories = $this->getDoctrine()->getRepository('AppBundle:Category')->findCategoryByQueryString($queryString);
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findUserByQueryString($queryString);
        $questions = $this->getDoctrine()->getRepository('AppBundle:Question')->findQuestionByQueryString($queryString);
        $answers = $this->getDoctrine()->getRepository('AppBundle:Answer')->findAnswerByQueryString($queryString);

        return compact('categories', 'users', 'questions', 'answers');
    }
}