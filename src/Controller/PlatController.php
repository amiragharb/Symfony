<?php

namespace App\Controller;

use App\Entity\Plat;

use App\Form\PlatType;
use App\Entity\Gerant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PlatRepository;
use App\Repository\GerantRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
class PlatController extends AbstractController
{
    #[Route('/plat', name: 'app_plat')]
    public function index(PlatRepository $platRepository,GerantRepository $gerantRepository): Response
    {
        $gerants = $gerantRepository->findAll();
        $plats = $platRepository->findAll();  
        return $this->render('plat/index.html.twig', [
            'plats' => $plats,
            'gerants' => $gerants,
        ]);
    }

    #[Route('/new', name: 'plat_new')]
    public function new(Request $request): Response
    {
        $plat = new Plat();
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement de l'image
            $file = $form->get('image')->getData();

            if ($file) {
                $fileName = uniqid().'.'.$file->guessExtension();

                // Déplacer le fichier dans le répertoire où sont stockées les images
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );

                // Mettre à jour le nom de l'image dans l'entité Plat
                $plat->setImage($fileName);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($plat);
            $entityManager->flush();

            return $this->redirectToRoute('app_plat');
        }

        return $this->render('plat/new.html.twig', [
            'plat' => $plat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'plat_detail')]
    public function showPlat($id, PlatRepository $platRepository): Response
   {
     $plat = $platRepository->find($id);
    if (!$plat) 
    {
        
        throw $this->createNotFoundException('Le plat demandé existe pas.');
    }
   
    return $this->render('plat/show.html.twig', [
        'plat' => $plat, 
    ]);
    
    }
   
    #[Route('/{id}', name: 'gerant_show')]
    public function show(int $id, GerantRepository $gerantRepository, PlatRepository $platRepository): Response
    {
        $gerant = $gerantRepository->find($id);
    
        if (!$gerant)
       {
            throw $this->createNotFoundException("Le gérant avec l'id $id n'a pas été trouvé.");
        }
    
        // Supposons que vous avez une méthode dans PlatRepository qui trouve les plats par gerant
        $plats = $platRepository->findByGerant($gerant);
    
        return $this->render('commande_resto/showgerant.html.twig', [ 
            'gerant' => $gerant,
            'plats' => $plats,
        ]);
    }

}

