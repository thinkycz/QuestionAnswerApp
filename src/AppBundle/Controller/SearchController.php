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
     * @throws \Algolia\AlgoliaSearchBundle\Exception\NotAnAlgoliaEntity
     */
    public function searchAction(Request $request)
    {
        $queryString = $request->get('query_string');
        $algolia = $this->get('algolia.indexer');

        $categories = $algolia->search($this->getDoctrine()->getEntityManager(), 'AppBundle:Category', $queryString)->getHits();
        $users = $algolia->search($this->getDoctrine()->getEntityManager(), 'AppBundle:User', $queryString)->getHits();
        $questions = $algolia->search($this->getDoctrine()->getEntityManager(), 'AppBundle:Question', $queryString)->getHits();
        $answers = $algolia->search($this->getDoctrine()->getEntityManager(), 'AppBundle:Answer', $queryString)->getHits();

        return compact('categories', 'users', 'questions', 'answers');
    }
}