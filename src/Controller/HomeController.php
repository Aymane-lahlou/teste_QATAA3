<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        EvenementRepository $evenementRepository,
        TicketRepository $ticketRepository,
        EntityManagerInterface $em
    ): Response {
        // Événements actifs pour le slider
        $evenements = $evenementRepository->findActiveEvents();

        // 1) Nombre total d'événements
        $totalEvenements = $evenementRepository->count([]);

        // 2) Nombre total de tickets
        $totalTickets = $ticketRepository->count([]);

        // 3) Nombre de villes uniques (champ "lieu" sur Evenement)
        try {
            $villesQuery = $em->createQueryBuilder()
                ->select('COUNT(DISTINCT e.lieu)')
                ->from('App\Entity\Evenement', 'e')
                ->where('e.lieu IS NOT NULL')
                ->getQuery();
            $totalVilles = (int) $villesQuery->getSingleScalarResult();
        } catch (\Exception $e) {
            $totalVilles = 0;
        }

        return $this->render('home/index.html.twig', [
            'evenements'       => $evenements,
            'totalEvenements'  => $totalEvenements,
            'totalTickets'     => $totalTickets,
            'totalVilles'      => $totalVilles,
        ]);
    }

    #[Route('/events', name: 'app_events')]
    public function events(
        Request $request,
        EvenementRepository $evenementRepository
    ): Response {
        $categorie = $request->query->get('categorie');
        $search    = $request->query->get('search');

        if ($search) {
            $evenements = $evenementRepository->searchByTerm($search);
        } elseif ($categorie) {
            $evenements = $evenementRepository->findByCategorie($categorie);
        } else {
            $evenements = $evenementRepository->findActiveEvents();
        }

        $categories = ['Concert', 'Conférence', 'Festival', 'Spectacle', 'Formation', 'Sport'];

        return $this->render('home/events.html.twig', [
            'evenements'        => $evenements,
            'categories'        => $categories,
            'current_categorie' => $categorie,
            'current_search'    => $search,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_detail')]
    public function eventDetail(int $id, EvenementRepository $evenementRepository): Response
    {
        $evenement = $evenementRepository->find($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        return $this->render('home/event_detail.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }

    // ==================== BLOG ROUTES ====================

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        // Données d'exemple (tu pourras les remplacer par une vraie base de données plus tard)
        $articles = [
            [
                'slug' => 'evenements-2026-maroc',
                'title' => 'Les événements incontournables de 2026 au Maroc',
                'excerpt' => 'Découvrez notre sélection des concerts, festivals et événements culturels à ne pas manquer cette année à travers le Royaume.',
                'category' => 'Événements',
                'categoryIcon' => 'calendar-alt',
                'imageUrl' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800',
                'publishedAt' => new \DateTime('2026-01-05'),
                'author' => [
                    'name' => 'Youssef Alami',
                    'initials' => 'YA'
                ]
            ],
            [
                'slug' => 'organiser-evenement-10-etapes',
                'title' => 'Comment organiser un événement réussi en 10 étapes',
                'excerpt' => 'Guide complet pour les organisateurs : de la planification initiale jusqu\'au jour J, tous nos conseils d\'experts.',
                'category' => 'Conseils',
                'categoryIcon' => 'lightbulb',
                'imageUrl' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=800',
                'publishedAt' => new \DateTime('2026-01-03'),
                'author' => [
                    'name' => 'Sara Mansouri',
                    'initials' => 'SM'
                ]
            ],
            [
                'slug' => 'tendances-billetterie-digitale',
                'title' => 'Les nouvelles tendances de la billetterie digitale',
                'excerpt' => 'Analyse des innovations technologiques qui transforment l\'expérience d\'achat de billets en ligne au Maroc.',
                'category' => 'Tendances',
                'categoryIcon' => 'trophy',
                'imageUrl' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800',
                'publishedAt' => new \DateTime('2026-01-01'),
                'author' => [
                    'name' => 'Karim El Amrani',
                    'initials' => 'KE'
                ]
            ],
            [
                'slug' => 'interview-dj-snake',
                'title' => 'Interview exclusive avec DJ Snake avant son concert',
                'excerpt' => 'Rencontre avec l\'artiste international quelques jours avant sa performance exceptionnelle à Casablanca.',
                'category' => 'Interviews',
                'categoryIcon' => 'microphone',
                'imageUrl' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800',
                'publishedAt' => new \DateTime('2025-12-28'),
                'author' => [
                    'name' => 'Leila Benjelloun',
                    'initials' => 'LB'
                ]
            ],
            [
                'slug' => 'mawazine-20-ans',
                'title' => 'Le Mawazine Festival : 20 ans de succès international',
                'excerpt' => 'Retour sur deux décennies d\'un festival qui a su placer le Maroc sur la carte mondiale des événements culturels.',
                'category' => 'Culture',
                'categoryIcon' => 'music',
                'imageUrl' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800',
                'publishedAt' => new \DateTime('2025-12-25'),
                'author' => [
                    'name' => 'Omar Tazi',
                    'initials' => 'OT'
                ]
            ],
            [
                'slug' => 'astuces-ne-jamais-manquer-evenement',
                'title' => '7 astuces pour ne jamais manquer un événement',
                'excerpt' => 'Nos meilleurs conseils pour être informé en temps réel des nouveaux événements et des offres exclusives sur Qataa3.ma.',
                'category' => 'Conseils',
                'categoryIcon' => 'lightbulb',
                'imageUrl' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800',
                'publishedAt' => new \DateTime('2025-12-22'),
                'author' => [
                    'name' => 'Nadia Fassi',
                    'initials' => 'NF'
                ]
            ],
        ];

        return $this->render('home/blog.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/blog/article/{slug}', name: 'app_blog_article')]
    public function blogArticle(string $slug): Response
    {
        // Pour l'instant, on affiche juste le slug
        // Plus tard, tu récupéreras l'article depuis la BDD
        return $this->render('home/blog_article.html.twig', [
            'slug' => $slug,
        ]);
    }
}
