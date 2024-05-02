<?php



namespace App\Controller;

use App\Repository\CommandeClientRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeClientController extends AbstractController
{
    #[Route('/commande/client', name: 'app_commande_client')]
    public function index(CommandeClientRepository $commandeClientRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Construire le queryBuilder pour récupérer toutes les commandes clients
        $queryBuilder = $commandeClientRepository->createQueryBuilder('c');

        // Utiliser Paginator pour paginer les résultats de queryBuilder
        $pagination = $paginator->paginate(
            $queryBuilder, // Requête source
            $request->query->getInt('page', 1), // Numéro de la page courante
            10 // Nombre d'éléments par page
        );

        // Passer les données paginées à la vue
        return $this->render('commande_client/historique.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
