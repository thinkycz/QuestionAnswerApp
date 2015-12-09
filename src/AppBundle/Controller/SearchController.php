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

        $categories = [];
        $users = [];
        $questions = [];
        $answers = [];

        return compact('categories', 'users', 'questions', 'answers');
    }
}