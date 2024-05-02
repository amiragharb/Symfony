<?php

// src/Controller/CommandeRestoController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\CommandeResto;
use App\Repository\CommandeRestoRepository;
use App\Repository\CommandeClientRepository;
use App\Entity\Client; 
use App\Repository\ClientRepository;

use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
class CommandeRestoController extends AbstractController
{
    #[Route('/restaurant', name: 'restaurant_commandes')]
    public function index(ManagerRegistry $doctrine, Request $request,PaginatorInterface $paginator, CommandeRestoRepository $commandeRestoRepository, ClientRepository $clientRepository ,  CommandeClientRepository $commandeClientRepository): Response
    {
        // Utilisation de l'ID du restaurant par défaut
        $idRestoDefault = 19; // Mettez l'ID de votre choix ici
        $idClientDefault = 15; // ID du client par défaut

        $client = $clientRepository->find($idClientDefault);
        if (!$client)
        {
            throw $this->createNotFoundException('Client non trouvé.');
        }

        // Supposons que le 'idClient' dans CommandeResto référence un Client et que vous voulez les commandes pour le resto du client
        $commandes = $commandeRestoRepository->findBy(['idClient' => $client]);

        if ($request->isMethod('POST')) 
        {
            $commandeId = $request->request->get('commandeId');
            $newStatus = $request->request->get('status');

            $commande = $commandeRestoRepository->find($commandeId);
            if ($commande) 
            {
               
                $entityManager = $doctrine->getManager();
                $entityManager->flush();

                $this->addFlash('success', 'Le statut de la commande a été mis à jour.');
                return $this->redirectToRoute('restaurant_commandes');
            }
        }
        $commandes = $commandeRestoRepository->findBy(['idClient' => $client]);

        // Pour chaque commande, récupérez les CommandeClient liées
        $commandeClients = [];
        foreach ($commandes as $commande)
         {
          $commandeClients[$commande->getId()] = $commandeClientRepository->findBy(['idCommande' => $commande]);
         }
            // Utiliser Paginator pour paginer les résultats de queryBuilder
            $queryBuilder = $commandeRestoRepository->createQueryBuilder('c')
            ->where('c.idClient = :client')
            ->setParameter('client', $client)
            ->orderBy('c.id', 'DESC') // Ajoutez cet ordre par ID de commande décroissant
             ->getQuery();
        // Pagination
        $pagination = $paginator->paginate(
            $queryBuilder, // Requête source sous forme de QueryBuilder
            $request->query->getInt('page', 1), // Numéro de la page courante
           8 // Nombre d'éléments par page
        );
        // P
    return $this->render('commande_resto/index.html.twig', [
           'commandes' => $commandes,
           'idResto' => $idRestoDefault,
        'commandeClients' => $commandeClients, 'pagination' => $pagination,]);
}

    #[Route('/modifier-statut-commande', name: 'modifier_statut_commande')]
  function modifierStatutCommande(Request $request, CommandeClientRepository $commandeClientRepository, ManagerRegistry $doctrine,CommandeRestoRepository $commandeRestoRepository): Response {
    if ($request->isMethod('POST')) 
    {
        $commandeClientId = $request->request->get('commandeClientId');
        $newStatus = $request->request->get('status');

        $commandeClient = $commandeClientRepository->find($commandeClientId);
        if ($commandeClient) {
            $commandeClient->setStatus($newStatus);
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Le statut de la commande a été mis à jour.');
        }

        return $this->redirectToRoute('restaurant_commandes');
    }

    // Redirection par défaut si la requête n'est pas POST
    return $this->redirectToRoute('restaurant_commandes');

}
/**
 * @Route("/recherche-commande", name="recherche_commande")
 */
public function rechercheCommande(Request $request, CommandeRestoRepository $commandeRestoRepository): Response
{
    $searchTerm = $request->query->get('id');
    $commande = null;

    if ($searchTerm) {
        $commande = $commandeRestoRepository->findOneBy(['id' => $searchTerm]); // Correction faite ici
        if (!$commande) {
            $this->addFlash('danger', "Aucune commande trouvée pour le numéro : $searchTerm");
        }
    }

    // Gérer ici la logique si la commande est trouvée ou non, puis passer les données à la vue
    return $this->render('commande_resto/index.html.twig', [ // Correction du chemin du template
        'commande' => $commande,
        'searchTerm' => $searchTerm
    ]);
}




}

