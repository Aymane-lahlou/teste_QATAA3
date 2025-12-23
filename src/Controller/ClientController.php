<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\TicketRepository;
use App\Repository\RecuRepository;
use App\Service\QRCodeService;
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
        /** @var Client $user */
        $user = $this->getUser();

        return $this->render('client/dashboard.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile', name: 'app_client_profile')]
    public function profile(): Response
    {
        /** @var Client $user */
        $user = $this->getUser();

        return $this->render('client/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/tickets', name: 'app_client_tickets')]
    public function tickets(
        TicketRepository $ticketRepository,
        QRCodeService $qrCodeService
    ): Response {
        /** @var Client $user */
        $user    = $this->getUser();
        $tickets = $ticketRepository->findByClient($user->getId());

        $ticketsWithQR = [];
        foreach ($tickets as $ticket) {
            $ticketsWithQR[] = [
                'ticket' => $ticket,
                'qrCode' => $qrCodeService->generateTicketQRCode($ticket->getCodeTicket()),
            ];
        }

        return $this->render('client/tickets.html.twig', [
            'ticketsWithQR' => $ticketsWithQR,
        ]);
    }

    #[Route('/recus', name: 'app_client_recus')]
    public function recus(RecuRepository $recuRepository): Response
    {
        /** @var Client $user */
        $user  = $this->getUser();
        $recus = $recuRepository->findByClient($user->getId());

        return $this->render('client/recus.html.twig', [
            'recus' => $recus,
        ]);
    }

    #[Route('/recu/{id}/pdf', name: 'app_client_recu_pdf')]
    public function recuPdf(
        int $id,
        RecuRepository $recuRepository,
        TicketRepository $ticketRepository,
        QRCodeService $qrCodeService
    ): Response {
        /** @var Client $user */
        $user = $this->getUser();

        $recu = $recuRepository->find($id);

        if (!$recu || $recu->getCommande()->getClient() !== $user) {
            throw $this->createNotFoundException('Reçu non trouvé');
        }

        $commande      = $recu->getCommande();
        $ticketsWithQR = [];

        foreach ($commande->getLigneCommandes() as $ligne) {
            $tickets = $ticketRepository->findBy(['ligneCommande' => $ligne]);

            foreach ($tickets as $ticket) {
                $data           = $ticket->getCodeTicket();
                $ticketsWithQR[] = [
                    'ticket' => $ticket,
                    'qrCode' => $qrCodeService->generateTicketQRCode($data),
                ];
            }
        }

        $html = $this->renderView('client/recu_pdf.html.twig', [
            'recu'          => $recu,
            'commande'      => $commande,
            'ticketsWithQR' => $ticketsWithQR,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="recu-' . $recu->getNumeroRecu() . '.pdf"',
            ]
        );
    }
}
