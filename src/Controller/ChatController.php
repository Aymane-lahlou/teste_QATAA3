<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chat-api', name: 'app_chat_api', methods: ['POST'])]
    public function ask(Request $request, EvenementRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = strtolower(trim($data['message'] ?? ''));

        if (empty($message)) {
            return new JsonResponse([
                'reply' => 'Dis-moi ce que tu cherches ! 😊',
                'suggestions' => true
            ]);
        }

        // Dictionnaire enrichi des catégories et mots-clés
        $categories = [
            'Concert' => ['concert', 'musique', 'chanteur', 'artiste', 'chanson', 'spectacle musical'],
            'Festival' => ['festival', 'fête', 'festivités', 'grande événement'],
            'Conférence' => ['conférence', 'présentation', 'atelier', 'séminaire', 'formation', 'débat'],
            'Spectacle' => ['spectacle', 'pièce', 'théâtre', 'comédie', 'cirque', 'représentation'],
            'Formation' => ['formation', 'cours', 'apprentissage', 'classe', 'enseignement', 'stage'],
            'Sport' => ['sport', 'match', 'football', 'basket', 'compétition', 'jeux', 'tennis', 'volleyball'],
        ];

        // Déterminer la catégorie recherchée
        $foundCategory = $this->detectCategory($message, $categories);

        // Gestion des demandes spéciales
        if (str_contains($message, 'tout') || str_contains($message, 'tous') || str_contains($message, 'tout les')) {
            return $this->getAllEvents($repo);
        }

        if (str_contains($message, 'aide') || str_contains($message, 'help') || str_contains($message, '?')) {
            return $this->getHelpMessage();
        }

        // Si une catégorie est trouvée
        if ($foundCategory) {
            return $this->getCategoryEvents($repo, $foundCategory);
        }

        // Recherche par terme si pas de catégorie détectée
        if (strlen($message) > 2) {
            return $this->searchEvents($repo, $message);
        }

        return $this->getHelpMessage();
    }

    private function detectCategory(string $message, array $categories): ?string
    {
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $category;
                }
            }
        }
        return null;
    }

    private function getCategoryEvents(EvenementRepository $repo, string $category): JsonResponse
    {
        $events = $repo->findByCategorie($category);
        $events = array_slice($events, 0, 5); // Afficher jusqu'à 5 événements

        if (empty($events)) {
            return new JsonResponse([
                'reply' => "Aucun {$category} trouvé pour le moment. 😔 Essayez une autre catégorie !",
                'suggestions' => true
            ]);
        }

        $eventsList = $this->formatEvents($events);
        return new JsonResponse([
            'reply' => "🎭 Voici les {$category}s à venir :\n\n{$eventsList}",
            'events' => count($events),
            'suggestions' => false
        ]);
    }

    private function getAllEvents(EvenementRepository $repo): JsonResponse
    {
        $events = $repo->findActiveEvents();
        $events = array_slice($events, 0, 10);

        if (empty($events)) {
            return new JsonResponse([
                'reply' => "Aucun événement n'est disponible pour le moment. 😔",
                'suggestions' => true
            ]);
        }

        // Grouper par catégorie
        $grouped = [];
        foreach ($events as $event) {
            $cat = $event->getCategorie();
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $event;
        }

        $reply = "📅 Voici les événements à venir :\n\n";
        foreach ($grouped as $category => $categoryEvents) {
            $reply .= "**{$category}**\n";
            foreach (array_slice($categoryEvents, 0, 2) as $event) {
                $date = $event->getDateEvenement()->format('d/m/Y H:i');
                $reply .= "  • {$event->getTitre()} - {$date}\n";
            }
            $reply .= "\n";
        }

        return new JsonResponse([
            'reply' => $reply,
            'events' => count($events),
            'suggestions' => false
        ]);
    }

    private function searchEvents(EvenementRepository $repo, string $term): JsonResponse
    {
        $events = $repo->searchByTerm($term);
        $events = array_slice($events, 0, 5);

        if (empty($events)) {
            return new JsonResponse([
                'reply' => "Aucun événement ne correspond à '{$term}'. Essayez une catégorie ! 🔍",
                'suggestions' => true
            ]);
        }

        $eventsList = $this->formatEvents($events);
        return new JsonResponse([
            'reply' => "Événements trouvés pour '{$term}' :\n\n{$eventsList}",
            'events' => count($events),
            'suggestions' => false
        ]);
    }

    private function formatEvents(array $events): string
    {
        $list = '';
        foreach ($events as $event) {
            $title = $event->getTitre();
            $date = $event->getDateEvenement()->format('d/m/Y H:i');
            $location = $event->getLieu();
            $list .= "📌 **{$title}**\n";
            $list .= "   📅 {$date}\n";
            $list .= "   📍 {$location}\n\n";
        }
        return $list;
    }

    private function getHelpMessage(): JsonResponse
    {
        return new JsonResponse([
            'reply' => "Bienvenue ! 👋 Je suis ici pour t'aider à trouver des événements.\n\nTu peux me demander :\n• 'Concerts à venir'\n• 'Festivals ce mois'\n• 'Spectacles près d'ici'\n• 'Formation disponible'\n• 'Match de football'\n• 'Tous les événements'\n\nOu simplement chercher par nom ! 🎉",
            'suggestions' => true
        ]);
    }
}
