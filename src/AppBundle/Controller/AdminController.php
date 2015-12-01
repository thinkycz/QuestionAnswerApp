<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
    /**
     * @Route(path="/admin", name="adminPanel")
     * @Template()
     */
    public function indexAction()
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        $categories = $this->getDoctrine()->getRepository('AppBundle:Category')->findAll();

        return compact('users', 'categories');
    }

    /**
     * @Route(path="/admin/category/create", name="createCategory")
     * @Method(methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createCategory(Request $request)
    {
        $newCategoryName = trim($request->get('name'));

        if(empty($newCategoryName))
        {
            $this->get('thinky.appbundle.sweet_alert')->error('Prázdný název', 'Název kategorie nesmí být prázdný.');
            return $this->redirectToRoute('adminPanel');
        }

        if(!empty($this->getDoctrine()->getRepository('AppBundle:Category')->findBy(['name' => $newCategoryName])))
        {
            $this->get('thinky.appbundle.sweet_alert')->error('Kategorie již existuje', 'Kategorie s tímto názvem již existuje.');
            return $this->redirectToRoute('adminPanel');
        }

        $newCategory = new Category();
        $newCategory->setName($newCategoryName);

        $em = $this->getDoctrine()->getManager();
        $em->persist($newCategory);
        $em->flush();

        $this->get('thinky.appbundle.sweet_alert')->success('Úspěch !', 'Kategorie byla úspěšně přidána.');
        return $this->redirectToRoute('adminPanel');
    }

    /**
     * @Route(path="/admin/category/rename/{id}", name="renameCategory")
     * @Method(methods={"POST"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function renameCategory(Request $request, $id)
    {
        $security = $this->get('security.authorization_checker');

        if(!$security->isGranted('ROLE_ADMIN') or !$security->isGranted('ROLE_MODERATOR')) return Response::create('error', 401);

        $newCategoryName = trim($request->get('name'));
        if(empty($newCategoryName)) return Response::create('error', 400);
        if(!empty($this->getDoctrine()->getRepository('AppBundle:Category')->findBy(['name' => $newCategoryName]))) return Response::create('error', 400);
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);

        if(!$category) return Response::create('error', 400);

        $category->setName($newCategoryName);
        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();

        return Response::create('success');
    }

    /**
     * @Route(path="/admin/category/delete/{id}", name="deleteCategory")
     * @Method(methods={"POST"})
     * @param $id
     * @return Response
     */
    public function deleteCategory($id)
    {
        $security = $this->get('security.authorization_checker');

        if(!$security->isGranted('ROLE_ADMIN') or !$security->isGranted('ROLE_MODERATOR')) return Response::create('error', 401);

        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);
        if(!$category) return Response::create('error', 400);

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return Response::create('success');
    }
}