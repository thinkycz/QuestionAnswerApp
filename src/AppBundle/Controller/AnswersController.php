<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Question;
use AppBundle\Entity\Answer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class AnswersController
 * @package AppBundle\Controller
 */
class AnswersController extends Controller
{
    /**
     * @Route(path="/answer/store/{questionId}", name="storeAnswer")
     * @Method(methods={"POST"})
     * @param Request $request
     * @param $questionId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function storeAction(Request $request, $questionId)
    {
        try
        {
            $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);
            $answer = new Answer();
            $answer->setAuthor($this->getUser());
            $answer->setText($request->get('text'));
            $answer->setQuestion($question);

            if(!$this->validateInputForm($request))
            {
                $this->container->get('thinky.appbundle.sweet_alert')->error('Stala se chyba', 'Bohužel se nám nepodařilo přidat příspěvek.');
                return $this->redirectToRoute('showQuestion', ['slug' => $question->getSlug()]);
            }

            if($request->get('parentId'))
            {
                $answer->setParent($this->getDoctrine()->getRepository('AppBundle:Answer')->find($request->get('parentId')));
            }

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($answer);
            $em->flush();

            $this->container->get('thinky.appbundle.sweet_alert')->success('Je to tam !', 'Příspěvek byl úspěšně přidán. Děkujeme.');
            return $this->redirectToRoute('showQuestion', ['slug' => $question->getSlug()]);
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Stala se chyba', 'Bohužel se nám nepodařilo přidat příspěvek.');
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route(path="/answer/upvote/{id}", name="upvoteAnswer")
     * @Method(methods={"POST"})
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function upvoteAction($id)
    {
        try
        {
            $answer = $this->getDoctrine()->getRepository('AppBundle:Answer')->find($id);

            if(!$this->getUser())
            {
                $this->container->get('thinky.appbundle.sweet_alert')->error('Nelze hlasovat', 'Hlasovat mohou pouze registrovaní uživatelé.');
                return $this->redirectToRoute('showQuestion', ['slug' => $answer->getQuestion()->getSlug()]);
            }

            if($answer->getUsersVoted()->contains($this->getUser()))
            {
                $this->container->get('thinky.appbundle.sweet_alert')->warning('Již jsi hlasoval', 'Pro tento příspěvek jsi již hlasoval.');
                return $this->redirectToRoute('showQuestion', ['slug' => $answer->getQuestion()->getSlug()]);
            }

            $answer->setUseful($answer->getUseful() + 1);
            $answer->addUsersVoted($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();
            $this->container->get('thinky.appbundle.sweet_alert')->success('Úspěch !', 'Tvůj hlas byl úspěšně zaregistrován.');
            return $this->redirectToRoute('showQuestion', ['slug' => $answer->getQuestion()->getSlug()]);
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Stala se chyba', 'Bohužel se nám nepodařilo zaregistrovat tvůj hlas.');
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route(path="/answer/downvote/{id}", name="downvoteAnswer")
     * @Method(methods={"POST"})
     */
    public function downvoteAction($id)
    {
        try
        {
            $answer = $this->getDoctrine()->getRepository('AppBundle:Answer')->find($id);

            if(!$this->getUser())
            {
                $this->container->get('thinky.appbundle.sweet_alert')->error('Nelze hlasovat', 'Hlasovat mohou pouze registrovaní uživatelé.');
                return $this->redirectToRoute('showQuestion', ['slug' => $answer->getQuestion()->getSlug()]);
            }

            if($answer->getUsersVoted()->contains($this->getUser()))
            {
                $this->container->get('thinky.appbundle.sweet_alert')->warning('Již jsi hlasoval', 'Pro tento příspěvek jsi již hlasoval.');
                return $this->redirectToRoute('showQuestion', ['slug' => $answer->getQuestion()->getSlug()]);
            }

            $answer->setUseful($answer->getUseful() - 1);
            $answer->addUsersVoted($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();
            $this->container->get('thinky.appbundle.sweet_alert')->success('Úspěch !', 'Tvůj hlas byl úspěšně zaregistrován.');
            return $this->redirectToRoute('showQuestion', ['slug' => $answer->getQuestion()->getSlug()]);
        }
        catch(Exception $e)
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Stala se chyba', 'Bohužel se nám nepodařilo zaregistrovat tvůj hlas.');
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route(path="/answer/delete/{id}", name="deleteAnswer")
     * @Method(methods={"POST"})
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $answer = $this->getDoctrine()->getRepository('AppBundle:Answer')->find($id);

        if(!$answer) return Response::create('error', 404);

        if(!($answer->getAuthor() == $this->getUser() or $this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_MODERATOR')))
            return Response::create('access denied', 401);

        $em = $this->getDoctrine()->getManager();
        $em->remove($answer);
        $em->flush();

        return Response::create('success');
    }


    //Private

    private function validateInputForm(Request $request)
    {
        if(!$this->get('form.csrf_provider')->isCsrfTokenValid('storeAnswer', $request->get('csrf_token')))
        {
            $this->container->get('thinky.appbundle.sweet_alert')->error('Nastala chyba CSRF', 'Bohužel se nám nepodařilo přidat příspěvek.');
            return false;
        }

        if(trim($request->get('text')) == '')
        {
            $this->container->get('thinky.appbundle.sweet_alert')->warning('Prázdný obsah', 'Co je to za odpověď, když nemá obsah???');
            return false;
        }

        return true;
    }
}