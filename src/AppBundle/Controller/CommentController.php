<?php
/**
 * Created by PhpStorm.
 * User: antoineschmitz
 * Date: 17/01/19
 * Time: 14:27
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;

use AppBundle\Entity\Information;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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


class CommentController extends Controller
{
    /**
     * @Route("/comment/{id}", name="comment")

     */

    public function addAction($id,Request $request)

    {

        if(empty($this->getUser()))
            return $this->redirectToRoute('home');

        // On crée un objet Advert

        $advert = new Comment();
        $user= $this->getUser();


        $gallery=$this->getDoctrine()->getManager()->getRepository('AppBundle:Gallery')->find($id);

        // J'ai raccourci cette partie, car c'est plus rapide à écrire !
        $form = $this->get('form.factory')->createBuilder(FormType::class, $advert)
            ->add('comment', TextType::class)
            ->add('gallery', EntityType::class,[
                'class'=>'AppBundle:Gallery',
                'choices' => [$gallery],
            ])
                ->add('user', EntityType::class,[
                    'class'=>'AppBundle:User',
                    'choices' => [$user],
                ])
            ->add('save', SubmitType::class)
            ->getForm();


        // Si la requête est en POST

        if ($request->isMethod('POST')) {

            // On fait le lien Requête <-> Formulaire

            // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur

            $form->handleRequest($request);


            // On vérifie que les valeurs entrées sont correctes

            // (Nous verrons la validation des objets en détail dans le prochain chapitre)

            if ($form->isValid()) {

                // On enregistre notre objet $advert dans la base de données, par exemple

                $em = $this->getDoctrine()->getManager();

                $em->persist($advert);

                $em->flush();


                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');


                // On redirige vers la page de visualisation de l'annonce nouvellement créée

                return $this->redirectToRoute('fiche', array('id' => $gallery->getId()));

            }

        }


        // À ce stade, le formulaire n'est pas valide car :

        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire

        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau

        return $this->render('default/comment/form.html.twig', array(

            'form' => $form->createView(),

        ));


    }

    /**
     * @Route("/admin/comment/{id}", name = "commentUpdate")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comment')->find($id);
        $user= $this->getUser();

        $gallery=$this->getDoctrine()->getManager()->getRepository('AppBundle:Gallery')->find($id);

        if (!$comment) {
            throw $this->createNotFoundException(
                'Aucun id trouver pour l\'date numero  ' . $id
            );
        } else {

            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $comment);

            $formBuilder
                ->add('comment', TextType::class)
                ->add('gallery', EntityType::class,[
                    'class'=>'AppBundle:Gallery',
                    'choices' => [$gallery],
                ])
                ->add('user', EntityType::class,[
                    'class'=>'AppBundle:User',
                    'choices' => [$user],
                ])
                ->add('Modifier', SubmitType::class);

            $form = $formBuilder->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $date = $form->getData();
                $em->persist($date);
                $em->flush();

                return $this->redirectToRoute('avis_user');
            }
        }

        return $this->render('default/comment/update.html.twig', array('form' => $form->createView(),));
    }


    /**
     * @Route("/admin/deletecomment/{id}", name = "commentDelete")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:Comment')->find($id);

        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('avis_user');
    }


}