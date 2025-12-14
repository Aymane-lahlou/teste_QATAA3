<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use App\Repository\RecuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
#[IsGranted('ROLE_CLIENT')]
class ClientController extends AbstractController
{
    #[Route('/dashboard', name: 'app_client_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        return $this->render('client/dashboard.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile', name: 'app_client_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();

        return $this->render('client/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/tickets', name: 'app_client_tickets')]
    public function tickets(TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();
        $tickets = $ticketRepository->findByClient($user->getId());

        return $this->render('client/tickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/recus', name: 'app_client_recus')]
    public function recus(RecuRepository $recuRepository): Response
    {
        $user = $this->getUser();
        $recus = $recuRepository->findByClient($user->getId());

        return $this->render('client/recus.html.twig', [
            'recus' => $recus,
        ]);
    }
    #[Route('/recu/{id}/pdf', name: 'app_client_recu_pdf')]
public function recuPdf(int $id, RecuRepository $recuRepository): Response
{
    $recu = $recuRepository->find($id);

    if (!$recu || $recu->getCommande()->getClient() !== $this->getUser()) {
        throw $this->createNotFoundException('Reçu non trouvé');
    }

    // Générer le contenu HTML du reçu
    $html = $this->renderView('client/recu_pdf.html.twig', [
        'recu' => $recu,
        'commande' => $recu->getCommande(),
    ]);

    // Générer le PDF avec DomPDF
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Retourner le PDF en téléchargement
    return new Response(
        $dompdf->output(),
        Response::HTTP_OK,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="recu-' . $recu->getNumeroRecu() . '.pdf"',
        ]
    );
}

}
