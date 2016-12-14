<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Entity\User;
use AppBundle\Entity\Answer;
use AppBundle\Entity\Question;
use AppBundle\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class QuestionsController extends Controller
{
    /**
     * Shows an index page
     * The second route is used for pagination
     *
     * @Route("/", name="home")
     * @Route("/page/{page}", name="homepage", defaults={"page" = 1})
     * @Template()
     * @param int $page
     * @return array
     */
    public function indexAction($page = 1)
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
        $tags = $this->getDoctrine()->getRepository('AppBundle:Tag')->getTopXByQuestionCount(50);

        //Page requests
        $totalPages = ceil($totalQuestions/$articlesOnOnePage);
        $questions = $this->getDoctrine()->getRepository(Question::class)->getOnePage($currentPage, $articlesOnOnePage);

        return compact('questions', 'totalPages', 'currentPage', 'totalQuestions', 'totalAnswers', 'categories', 'favoriteQuestions', 'topUsers', 'tags');
    }

    /**
     * Shows a single question
     *
     * @param $slug
     *
     * @Route(path="/question/show/{slug}", name="showQuestion")
     * @Template()
     * @return array
     */
    public function showAction($slug)
    {
        /** @var Question $question */
        $question = $this->getDoctrine()->getRepository('AppBundle:Question')->getQuestionBySlug($slug);
        $answers = $this->getDoctrine()->getRepository('AppBundle:Answer')->getDirectAnswersToQuestion($question);

        $question->setViews($question->getViews()+1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($question);
        $em->flush();

        return compact('question', 'answers');
    }

    /**
     * Shows a form to create a new question
     *
     * @Route(path="/question/create", name="createQuestion")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        $title = $request->get('title');
        $text = $request->get('text');
        $categories = $this->getDoctrine()->getRepository('AppBundle:Category')->findAll();
        $selectedCategory = $request->get('selectedCategory');
        $tags = $request->get('tags');

        return compact('categories', 'title', 'text', 'selectedCategory', 'tags');
    }

    /**
     * Shows a form with existing values to edit the question
     *
     * @Route(path="/question/edit/{slug}", name="editQuestion")
     * @Template()
     * @param $slug
     * @return array
     * @internal param Request $request
     */
    public function editAction($slug)
    {
        $categories = $this->getDoctrine()->getRepository('AppBundle:Category')->findAll();
        /** @var Question $question */
        $question = $this->getDoctrine()->getRepository('AppBundle:Question')->getQuestionBySlug($slug);
        $security = $this->get('security.authorization_checker');

        if(!$this->getUser() or ($question->getAuthor() != $this->getUser() and !$security->isGranted('ROLE_ADMIN') and !$security->isGranted('ROLE_MODERATOR')))
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'K editaci tohoto příspěvku nemáte oprávnění.');
            return $this->redirectToRoute('showQuestion', ['slug' => $slug]);
        }

        return compact('categories', 'question');
    }

    /**
     * Listens to POST request, checks that the form is valid and stores data
     *
     * @Route(path="/question/store", name="storeQuestion")
     * @Method(methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function storeAction(Request $request)
    {
        try
        {
            $question = new Question();
            $question->setAuthor($this->getUser());
            $question->setTitle($request->get('title'));
            $question->setText($request->get('text'));

            if(!empty($request->get('tags')))
                $this->processTags($request->get('tags'), $question);

            if(!$this->validateInputForm($request))
                return $this->redirectBackToEditor($request->get('title'), $request->get('text'), $request->get('category'), $request->get('tags'));

            if($request->get('category'))
                $question->setCategory($this->getDoctrine()->getRepository('AppBundle:Category')->find($request->get('category')));

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($question);
            $em->flush();

            $this->container->get('thinky.appbundle.sweet_alert')->success('Je to tam !', 'Příspěvek byl úspěšně přidán. Děkujeme.');
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'Bohužel se nám nepodařilo přidat příspěvek.');
        }


        if($request->get('quick'))
            return $this->redirectToRoute('home');
        else
            return $this->redirectToRoute('showQuestion', ['slug' => $question->getSlug()]);

    }

    /**
     * Listens to POST request, checks that the form is valid and updates data
     *
     * @Route(path="/question/update/{slug}", name="updateQuestion")
     * @Method(methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, $slug)
    {
        if(!$this->validateInputForm($request))
            return $this->redirectBackToEditor($request->get('title'), $request->get('text'), $request->get('category'), $request->get('tags'));

        try
        {
            $question = $this->getDoctrine()->getRepository('AppBundle:Question')->getQuestionBySlug($slug);
            $question->setTitle($request->get('title'));
            $question->setText($request->get('text'));

            if(!empty($request->get('tags')))
                $this->processTags($request->get('tags'), $question);

            $security = $this->get('security.authorization_checker');

            if(!$this->getUser() or ($question->getAuthor() != $this->getUser() and !$security->isGranted('ROLE_ADMIN') and !$security->isGranted('ROLE_MODERATOR')))
            {
                $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'K editaci tohoto příspěvku nemáte oprávnění.');
                return $this->redirectToRoute('showQuestion', ['slug' => $slug]);
            }

            if($request->get('category'))
                $question->setCategory($this->getDoctrine()->getRepository('AppBundle:Category')->find($request->get('category')));

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($question);
            $em->flush();

            $this->container->get('thinky.appbundle.sweet_alert')->success('Je to tam !', 'Příspěvek byl úspěšně editován.');
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'Bohužel se nám nepodařilo editovat příspěvek.');
        }

        if($request->get('quick'))
            return $this->redirectToRoute('home');
        else
            return $this->redirectToRoute('showQuestion', ['slug' => $question->getSlug()]);
    }

    /**
     * Listens to POST request to delete a question
     *
     * @Route(path="/question/delete/{slug}", name="deleteQuestion")
     * @Method(methods={"POST"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($slug)
    {
        try
        {
            $question = $this->getDoctrine()->getRepository('AppBundle:Question')->getQuestionBySlug($slug);
            $security = $this->get('security.authorization_checker');

            if(!$this->getUser() or ($question->getAuthor() != $this->getUser() and !$security->isGranted('ROLE_ADMIN') and !$security->isGranted('ROLE_MODERATOR')))
            {
                $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'K editaci tohoto příspěvku nemáte oprávnění.');
                return $this->redirectToRoute('showQuestion', ['slug' => $slug]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($question);
            $em->flush();
            $this->container->get('thinky.appbundle.sweet_alert')->success('Odstraněno!', 'Příspěvek byl úspěšně smazán.');
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'Bohužel se nám nepodařilo smazat příspěvek.');
        }

        return $this->redirectToRoute('home');
    }

    /**
     * Checks conditions and marks the question as solved
     *
     * @Route(path="/question/solve/{slug}", name="solveQuestion")
     * @Method(methods={"POST"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function solveAction($slug)
    {
        try
        {
            $question = $this->getDoctrine()->getRepository('AppBundle:Question')->getQuestionBySlug($slug);
            $security = $this->get('security.authorization_checker');

            if(!$this->getUser() or ($question->getAuthor() != $this->getUser() and !$security->isGranted('ROLE_ADMIN') and !$security->isGranted('ROLE_MODERATOR')))
            {
                $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'K uzamčení tohoto příspěvku nemáte oprávnění.');
                return $this->redirectToRoute('showQuestion', ['slug' => $slug]);
            }

            $question->setSolved(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();
            $this->container->get('thinky.appbundle.sweet_alert')->success('Skvělé!', 'Jsme rádi, že je problém vyřešen.');
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'Bohužel se něco nepovedlo.');
        }

        return $this->redirectToRoute('showQuestion', compact('slug'));
    }

    /**
     * Checks conditions and opens the question
     * Only ROLE_ADMIN or ROLE_MODERATOR should be able to open the question
     *
     * @Route(path="/question/unsolve/{slug}", name="unsolveQuestion")
     * @Method(methods={"POST"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unsolveAction($slug)
    {
        try
        {
            $question = $this->getDoctrine()->getRepository('AppBundle:Question')->getQuestionBySlug($slug);
            $security = $this->get('security.authorization_checker');

            if(!$this->getUser() or (!$security->isGranted('ROLE_ADMIN') and !$security->isGranted('ROLE_MODERATOR')))
            {
                $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'K odemčení tohoto příspěvku nemáte oprávnění.');
                return $this->redirectToRoute('showQuestion', ['slug' => $slug]);
            }

            $question->setSolved(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();
            $this->container->get('thinky.appbundle.sweet_alert')->success('Odemknuto!', 'Otázka byla úspěšně odemčena.');
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'Bohužel se něco nepovedlo.');
        }

        return $this->redirectToRoute('showQuestion', compact('slug'));
    }

    /**
     * Selects an answer as the solution to the question
     *
     * @Route(path="/question/selectAnswer/{slug}", name="selectAnswer")
     * @Method(methods={"POST"})
     * @param Request $request
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function selectAnswer(Request $request, $slug)
    {
        try
        {
            /** @var Question $question */
            $question = $this->getDoctrine()->getRepository('AppBundle:Question')->getQuestionBySlug($slug);
            $answer = $this->getDoctrine()->getRepository('AppBundle:Answer')->find($request->get('id'));
            $security = $this->get('security.authorization_checker');

            if(!$this->getUser() or ($question->getAuthor() != $this->getUser() and !$security->isGranted('ROLE_ADMIN') and !$security->isGranted('ROLE_MODERATOR')))
                return Response::create('unauthorized', 401);

            if(!$question or !$answer)
                return Response::create('not found', 404);

            $question->setSelectedAnswer($answer);
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();
            return Response::create('success');
        }
        catch(Exception $e)
        {
            return Response::create('error', 400);
        }
    }

    //Private

    /**
     * Validates the form against CSRF
     * Checks if the mandatory fields are empty
     *
     * @param Request $request
     * @return bool
     */
    private function validateInputForm(Request $request)
    {
        $token = $this->get('security.csrf.token_manager')->getToken($request->get('csrf_token'));
        if(!$this->get('security.csrf.token_manager')->isTokenValid($token))
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba CSRF', 'Bohužel se nám nepodařilo přidat příspěvek.');
            return false;
        }

        if(trim($request->get('title')) == '')
        {
            $this->container->get('thinky.appbundle.sweet_alert')->warning('Prázdný titulek', 'Titulek nemůže být prázdný, vyplňte jej prosím. Děkujeme');
            return false;
        }

        if(!preg_match('/[a-zA-Z]/', $request->get('title')))
        {
            $this->container->get('thinky.appbundle.sweet_alert')->warning('Prázdný titulek', 'Titulek musí obsahovat alespoň jedno písmeno.');
            return false;
        }

        if(trim($request->get('text')) == '')
        {
            $this->container->get('thinky.appbundle.sweet_alert')->warning('Prázdný obsah', 'Co je to za otázku, když nemá obsah???');
            return false;
        }

        return true;
    }

    /**
     * Redirects the user back to the create editor with existing data
     * when the input was invalid
     *
     * @param $title
     * @param $text
     * @param $selectedCategory
     * @param $tags
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function redirectBackToEditor($title, $text, $selectedCategory, $tags)
    {
        return $this->redirectToRoute('createQuestion', compact('title', 'text', 'selectedCategory', 'tags'));
    }

    /**
     * Removes all invalid characters from tags
     * Divides tags by spaces
     * Adds new or existing tags to the question
     *
     * @param $string
     * @param Question $question
     */
    private function processTags($string, Question $question)
    {
        $string = preg_replace("/[^a-zA-Z0-9]/", " ", $string);
        $string = preg_replace('/\s+/', ' ', $string);
        $string = strtolower($string);

        $tags = explode(' ', $string);
        $em = $this->getDoctrine()->getManager();
        $i = 0;

        foreach($question->getTags() as $tag)
            $question->removeTag($tag);
        $em->persist($question);
        $em->flush();


        foreach($tags as $tagName)
        {
            if(++$i > 5) break;
            $res = $this->getDoctrine()->getRepository('AppBundle:Tag')->findBy(['name' => $tagName]);
            if(empty(trim($tagName))) break;
            if(empty($res)) {
                $tag = new Tag();
                $tag->setName($tagName);
                $em->persist($tag);
                $em->flush();
            }
            else {
                $tag = $res[0];
            }
            if($question->getTags()->contains($tag)) break;
            $question->addTag($tag);
            $em->persist($question);
            $em->flush();
        }
    }
}
