<?php

namespace App\Controller;
Use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommandeClientRepository;
use App\Entity\CommandeClient;
use App\Entity\CommandeResto;
use App\Entity\Plat;
use App\Repository\PlatRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRestoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierController extends AbstractController
{
    #[Route('/panier/ajouter/{id}', name: 'ajouter_au_panier')]
    public function ajouterAuPanier($id, PlatRepository $platRepository, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        $plat = $platRepository->find($id);

        if (!$plat) {
            throw $this->createNotFoundException('Plat non trouvé.');
        }

        if (!isset($panier[$id])) {
            $panier[$id] = 0;
        }

        $panier[$id]++;

        $session->set('panier', $panier);

        return $this->redirectToRoute('voir_panier');
    }

    #[Route('/panier', name: 'voir_panier')]
    public function voirPanier(PlatRepository $platRepository, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        $detailPanier = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $plat = $platRepository->find($id);

            if (!$plat) {
                continue;
            }

            $prixTotalPlat = $plat->getPrix() * $quantite;
            $total += $prixTotalPlat;

            $detailPanier[] = [
                'plat' => $plat,
                'quantite' => $quantite,
                'prixTotalPlat' => $prixTotalPlat,
            ];
        }

        return $this->render('panier/voir_panier.html.twig', [
            'detailPanier' => $detailPanier,
            'total' => $total,
        ]);
    }

    #[Route('/panier/modifier/{id}', name: 'modifier_quantite_panier', methods: ['POST'])]
    public function modifierQuantitePanier($id, Request $request, SessionInterface $session): Response
    {
    $action = $request->request->get('action');
    $panier = $session->get('panier', []);

    if (!isset($panier[$id])) {
        throw $this->createNotFoundException('Le plat spécifié existe pas dans le panier.');
    }

    if ($action === 'increase') {
        $panier[$id]++;
    } elseif ($action === 'decrease') {
        $panier[$id] = max(1, $panier[$id] - 1); 
    } 
    else
     {
        throw $this->createNotFoundException('Action invalide.');
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('voir_panier');
    }


    #[Route('/panier/supprimer/{id}', name: 'supprimer_du_panier')]
    public function supprimerDuPanier($id, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('voir_panier');
    }


   
#[Route('/confirmation-commande', name: 'confirmation_commande')]

public function passerCommande(SessionInterface $session, PlatRepository $platRepository, ClientRepository $clientRepository,EntityManagerInterface  $entityManager): Response
{
    $panier = $session->get('panier', []);
    if (!$panier) {
        $this->addFlash('error', 'Votre panier est vide.');
        return $this->redirectToRoute('app_plat');
    }

    $client = $clientRepository->find(15);
    if (!$client) {
        throw $this->createNotFoundException('Client non trouvé.');
    }

    // Grouper les plats par restaurant
    $platsParResto = [];
    foreach ($panier as $idPlat => $quantite) {
        $plat = $platRepository->find($idPlat);
        if (!$plat) {
            continue;
        }

        $idResto = $plat->getIdRestaurant()->getId();
        if (!isset($platsParResto[$idResto])) {
            $platsParResto[$idResto] = [];
        }

        $platsParResto[$idResto][] = [
            'plat' => $plat,
            'quantite' => $quantite
        ];
    }

    foreach ($platsParResto as $idResto => $plats) {
        $commandeResto = new CommandeResto();
        $commandeResto->setDateCreation(new \DateTime());
        $commandeResto->setPaymentType("a la livraison");
        $commandeResto->setIdClient($client);
        $totalCommande = 0;

        foreach ($plats as $info) {
            $plat = $info['plat'];
            $quantite = $info['quantite'];

            $commandeClient = new CommandeClient();
            $commandeClient->setIdPlat($plat);
            $commandeClient->setQuantite($quantite);
            $commandeClient->setPrix($plat->getPrix() * $quantite);
            $commandeClient->setStatus("En cours");
            $commandeClient->setDate(new \DateTime());
            $commandeClient->setIdCommande($commandeResto);

            $entityManager->persist($commandeClient);
            $totalCommande += $plat->getPrix() * $quantite;
        }

        $commandeResto->setTotal($totalCommande);
        $entityManager->persist($commandeResto);
    }
  
    $entityManager->flush();
    $this->addFlash('success', 'Votre commande a été passée avec succès.');
    $session->set('panier', []); // Vider le panier
    return $this->redirectToRoute('app_plat');
}
}    