<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UsersController extends Controller
{
    /**
     * @Route(path="/user/{id}", name="showUser")
     * @Template()
     * @param $id
     * @return array
     */
    public function showAction($id)
    {
       $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if(!$user)
        {
            $this->get('thinky.appbundle.sweet_alert')->error('Nastala chyba', 'Nepovedlo se načíst uživatele.');
            return $this->redirectToRoute('home');
        }

        $numberOfAnswersToMyQuestions = $this->getDoctrine()->getRepository('AppBundle:Answer')->getNumberOfAnswersToUsersQuestions($user);

        return compact('user', 'numberOfAnswersToMyQuestions');
    }

    /**
     * @Route(path="/user/changepass/{id}", name="changePassword")
     * @Method(methods={"POST"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function changePasswordAction(Request $request, $id)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(['id' => $id]);
        $security = $this->get('security.authorization_checker');

        if(!$user) return Response::create('error', 500);
        if(!($security->isGranted('ROLE_ADMIN') or $this->getUser() == $user)) return Response::create('access denied', 401);

        $newPassword = $request->get('password');
        if(empty(trim($newPassword))) return Response::create('error', 400);

        $user->setPlainPassword($newPassword);
        $userManager->updateUser($user);

        return Response::create('success');
    }

    /**
     * @Route(path="/user/deactivate/{id}", name="deactivateAccount")
     * @Method(methods={"POST"})
     * @param $id
     * @return Response
     */
    public function deactivateAccountAction($id)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);
        $security = $this->get('security.authorization_checker');

        if(!$user) return Response::create('error', 500);
        if(!($security->isGranted('ROLE_ADMIN') or $this->getUser() == $user)) return Response::create('access denied', 401);

        $user->setEnabled(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return Response::create('success');
    }
}