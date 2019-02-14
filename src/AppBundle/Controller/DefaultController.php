<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;

class DefaultController extends Controller
{


    /**
     * @Route("/")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function homeAction(){
        return $this->redirectToRoute('home');
    }




    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction()
    {

        $user = $this->getUser();

        if (empty($user )) {
        $session = [];
        $this->redirectToRoute('home');
    }
        else
            $session['ok'] = 'ok';


        return $this->render('default/profile.html.twig', array(
            'profile' => $user,
            'session' => $session,
        ));
    }





    /**
     * @Route("/home", name="home")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/all_games", name="all games")
     */
    public function allgamesAction()
    {
        $all_games = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Information')
            ->findAll();

        return $this->render('default/all_games.html.twig', array(
            'all_games' => $all_games,
        ));
    }


    /**
     * @Route("/playstation", name="playstation")
     */
    public function playstationAction()
    {
        $playstation = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Information')
            ->findByplatform('playstation');

        return $this->render('default/playstation.html.twig', array(
            'playstation' => $playstation,
        ));


    }

    /**
     * @Route("/xbox", name="xbox")
     */
    public function xboxAction()
    {
        $xbox = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Information')
            ->findByplatform('xbox');

        return $this->render('default/xbox.html.twig', array(
            'xbox' => $xbox,
        ));
    }

    /**
     * @Route("/pc", name="pc")
     */
    public function pcAction()
    {
        $pc = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Information')
            ->findByplatform('pc');

       return $this->render('default/pc.html.twig', array(
       'pc' => $pc,
           ));
    }


    /**
     * @Route("/fiche/{id}", name="fiche")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function ficheAction($id)
    {
        $fiche = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Information')
            ->findOneBygallery($id);

        $user=$this->getUser();

        $comment = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Comment')
            ->findBygallery($id);

        return $this->render('default/fiche.html.twig', array(
            'fiche' => $fiche,
            'user' => $user,
            'comment'=> $comment,
        ));
    }

    /**
     * @Route("/admin/game_gallery", name="game_gallery")
     */
    public function game_galleryAction()
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Gallery')
        ;

        $game_gallery = $repository->findAll();

        return $this->render('default/admin/game_gallery.html.twig',  array('game_gallery' => $game_gallery

    ));
    }

    /**
     * @Route("/admin/game_information", name="game_information")
     */
    public function game_informationAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Information')
        ;

        $game_information = $repository->findAll();
        return $this->render('default/admin/game_information.html.twig', array('game_information' => $game_information

        ));
    }

    /**
     * @Route("/admin/avis_user", name="avis_user")
     */
    public function avis_userAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Comment')
        ;

        $avis_user = $repository->findAll();
        return $this->render('default/admin/avis_user.html.twig', array('avis_user' => $avis_user

        ));
    }

    public function getOrm($appbundle){
        $data = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository($appbundle)
            ->findAll();

        foreach($data as $dat)
            $datas[] = $dat->getId();

        return $datas;
    }

    /**
     * @Route(name="search")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(){

        $request=Request::createFromGlobals();

        $formBuilder=$this->get('form.factory')->createBuilder(formType::class);


        $formBuilder
            ->setAction($this->generateUrl('searchs'))
            ->setMethod('GET')
            ->add('search', SearchType::class)
            ->add('Recherche', SubmitType::class);

        $form = $formBuilder->getForm();
        return $this->render('default/search.html.twig', array('form' => $form->createView(),));

    }

    /**
     * @Route("/search",name="searchs")
     */
    public function searchs(Request $request)
    {
        if($request->isMethod('GET')){
            $title=$request->get('form');
            $title=$title['search'];
            $jeux=$this->getDoctrine()->getManager()->getRepository('AppBundle:Gallery')->findOneBytitle($title);

            if(empty($jeux)){
                return $this->redirectToRoute('home');

            }
            else{
                return $this->redirectToRoute('fiche', ['id'=>$jeux->getId()]);
            }
        }
        else{
            return $this->redirectToRoute('home');
    }
    }


}
