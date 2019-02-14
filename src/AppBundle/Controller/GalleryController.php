<?php

// src/OC/PlatformBundle/Controller/AdvertController.php


namespace AppBundle\Controller;


use AppBundle\AppBundle;
use AppBundle\Entity\Gallery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\Extension\Core\Type\FormType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\FileType;


class GalleryController extends Controller

{

    /**
     * @Route("/admin/gallery", name="gallery")
     */

    public function addAction(Request $request)

    {
        $image = new Gallery();

        // On crée un objet Advert

        $advert = new Gallery();


        // J'ai raccourci cette partie, car c'est plus rapide à écrire !

        $form = $this->get('form.factory')->createBuilder(FormType::class, $advert)

            ->add('title', TextType::class)

            ->add('image', TextType::class)

            ->add('images', FileType::class)

            ->add('save', SubmitType::class)

            ->getForm()

        ;


        // Si la requête est en POST

        if ($request->isMethod('POST')) {



            // On fait le lien Requête <-> Formulaire

            // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur

            $form->handleRequest($request);

            if($form->isValid()){
                $someNewFilename = $_POST['form']['image'];
                $directory = '../web/images';
                $explode = explode('.',$_FILES['form']['name']['images']);
                $extension = '.' . $explode[1];
                $directory = $directory . '/' . $someNewFilename . $extension;

                $file = $_FILES['form']['tmp_name']['images'];
                move_uploaded_file($file, $directory);
                $advert->setImage($someNewFilename . $extension);
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'image bien enregistrée.');

                return $this->redirectToRoute('game_gallery');
            }




            // On vérifie que les valeurs entrées sont correctes

            // (Nous verrons la validation des objets en détail dans le prochain chapitre)

            if ($form->isValid()) {

                // On enregistre notre objet $advert dans la base de données, par exemple

                $em = $this->getDoctrine()->getManager();

                $em->persist($advert);

                $em->flush();


                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');


                // On redirige vers la page de visualisation de l'annonce nouvellement créée

                return $this->redirectToRoute('gallery', array('id' => $advert->getId()));

            }

        }


        // À ce stade, le formulaire n'est pas valide car :

        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire

        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau

        return $this->render('default/admin/gallery/form.html.twig', array(

            'form' => $form->createView(),

        ));

    }
    /**
     * @Route("/admin/gallery/{id}", name = "galleryUpdate")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $gallery = $em->getRepository('AppBundle:Gallery')->find($id);

        if (!$gallery) {
            throw $this->createNotFoundException(
                'Aucun id trouver pour l\'date numero  ' . $id
            );
        } else {

            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $gallery);

            $formBuilder
                ->add('title', TextType::class)

                ->add('image', TextType::class)

                ->add('images', FileType::class)

                ->add('Modifier', SubmitType::class);

            $form = $formBuilder->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $advert = $form->getData();
                $someNewFilename = $_POST['form']['image'];
                $directory = '../web/images';
                $explode = explode('.',$_FILES['form']['name']['images']);
                $extension = '.' . $explode[1];
                $directory = $directory . '/' . $someNewFilename . $extension;

                $file = $_FILES['form']['tmp_name']['images'];
                move_uploaded_file($file, $directory);

                $em->persist($advert);

                $em->flush();

                return $this->redirectToRoute('game_gallery');
            }
        }

        return $this->render('default/admin/gallery/update.html.twig', array('form' => $form->createView(),));
    }


    /**
     * @Route("/admin/deletegallery/{id}", name = "galleryDelete")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {



        $em = $this->getDoctrine()->getManager();
        $gallery = $em->getRepository('AppBundle:Gallery')->findOneByid($id);

        $comment= $this->getDoctrine()->getManager()->getRepository('AppBundle:Comment')->findBygallery($id);

        $information= $this->getDoctrine()->getManager()->getRepository('AppBundle:Information')->findBygallery($id);
        $eme = $this->getDoctrine()->getManager();

        foreach ($comment as $delete )
            $eme->remove($delete);

        foreach ($information as $delete )
            $eme->remove($delete);

        $em->remove($gallery);
        $em->flush();

        return $this->redirectToRoute('game_gallery');
    }

}