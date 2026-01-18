<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Service\ChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chat-api', name: 'app_chat_api', methods: ['POST'])]
    public function ask(Request $request, EvenementRepository $repo, ChatService $chatService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            return new JsonResponse([
                'reply' => 'Dis-moi ce que tu cherches ! 😊',
                'suggestions' => true
            ]);
        }

        // Détecte la langue du message
        $language = $chatService->detectLanguage($message);
        $messageLower = strtolower($message);

        // 1. Vérifier si c'est une salutation (Bonjour, Salut, etc.)
        if ($chatService->isGreeting($messageLower, $language)) {
            return new JsonResponse([
                'reply' => $chatService->getGreetingMessage($language),
                'suggestions' => true,
                'language' => $language
            ]);
        }

        // 2. Vérifier si c'est une demande d'aide
        if ($chatService->isHelpRequest($messageLower, $language)) {
            $helpResponse = $chatService->getHelpMessage($language);
            $helpResponse['language'] = $language;
            return new JsonResponse($helpResponse);
        }

        // 3. Vérifier si c'est une demande de tickets
        if ($chatService->isTicketsRequest($messageLower, $language)) {
            $ticketsResponse = $chatService->getTicketsInfo($language);
            $ticketsResponse['language'] = $language;
            return new JsonResponse($ticketsResponse);
        }

        // 4. Détecte un département spécifique
        $department = $chatService->detectDepartment($messageLower, $language);
        if ($department) {
            $departmentResponse = $chatService->getDepartmentMessage($department, $language);
            $departmentResponse['language'] = $language;
            return new JsonResponse($departmentResponse);
        }

        // Dictionnaire enrichi des catégories et mots-clés
        $categories = [
            'Concert' => ['concert', 'musique', 'chanteur', 'artiste', 'chanson', 'spectacle musical', 'حفلة', 'موسيقى', 'حفل'],
            'Festival' => ['festival', 'fête', 'festivités', 'grande événement', 'مهرجان', 'فرقة'],
            'Conférence' => ['conférence', 'présentation', 'atelier', 'séminaire', 'formation', 'débat', 'محاضرة', 'ورشة'],
            'Spectacle' => ['spectacle', 'pièce', 'théâtre', 'comédie', 'cirque', 'représentation', 'عرض'],
            'Formation' => ['formation', 'cours', 'apprentissage', 'classe', 'enseignement', 'stage', 'تدريب', 'دراسة'],
            'Sport' => ['sport', 'match', 'football', 'basket', 'compétition', 'jeux', 'tennis', 'volleyball', 'رياضة', 'مباراة'],
        ];

        // Déterminer la catégorie recherchée
        $foundCategory = $this->detectCategory($messageLower, $categories);

        // Gestion des demandes spéciales
        if (str_contains($messageLower, 'tout') || str_contains($messageLower, 'tous') || str_contains($messageLower, 'tout les') || 
            str_contains($messageLower, 'كل') || str_contains($messageLower, 'جميع')) {
            return $this->getAllEvents($repo, $language);
        }

        // Si une catégorie est trouvée
        if ($foundCategory) {
            return $this->getCategoryEvents($repo, $foundCategory, $language);
        }

        // Recherche par terme si pas de catégorie détectée
        if (strlen($message) > 2) {
            return $this->searchEvents($repo, $message, $language);
        }

        return new JsonResponse(
            $chatService->getHelpMessage($language) + ['language' => $language]
        );
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

    private function getCategoryEvents(EvenementRepository $repo, string $category, string $language): JsonResponse
    {
        $events = $repo->findByCategorie($category);
        $events = array_slice($events, 0, 5); // Afficher jusqu'à 5 événements

        $emptyMessages = [
            'fr' => "Aucun {$category} trouvé pour le moment. 😔 Essayez une autre catégorie !",
            'ar' => "لا توجد {$category} في الوقت الحالي. 😔 جرب فئة أخرى!",
            'drj' => "واحد {$category} ماكايين الآن. 😔 جرب فرقة أخرى!"
        ];

        if (empty($events)) {
            return new JsonResponse([
                'reply' => $emptyMessages[$language] ?? $emptyMessages['fr'],
                'suggestions' => true,
                'language' => $language
            ]);
        }

        $eventsList = $this->formatEvents($events);
        
        $replyMessages = [
            'fr' => "🎭 Voici les {$category}s à venir :\n\n{$eventsList}",
            'ar' => "🎭 إليك {$category} القادمة:\n\n{$eventsList}",
            'drj' => "🎭 إليك {$category} القادمة:\n\n{$eventsList}"
        ];

        return new JsonResponse([
            'reply' => $replyMessages[$language] ?? $replyMessages['fr'],
            'events' => count($events),
            'suggestions' => false,
            'language' => $language
        ]);
    }

    private function getAllEvents(EvenementRepository $repo, string $language): JsonResponse
    {
        $events = $repo->findActiveEvents();
        $events = array_slice($events, 0, 10);

        $noEventsMessages = [
            'fr' => "Aucun événement n'est disponible pour le moment. 😔",
            'ar' => "لا تتوفر أحداث في الوقت الحالي. 😔",
            'drj' => "واحد حدث ماكايين الآن. 😔"
        ];

        if (empty($events)) {
            return new JsonResponse([
                'reply' => $noEventsMessages[$language] ?? $noEventsMessages['fr'],
                'language' => $language
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
        if ($language === 'ar') {
            $reply = "📅 إليك الأحداث القادمة:\n\n";
        } elseif ($language === 'drj') {
            $reply = "📅 إليك الأحداث القادمة:\n\n";
        }

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
            'suggestions' => false,
            'language' => $language
        ]);
    }

    private function searchEvents(EvenementRepository $repo, string $term, string $language): JsonResponse
    {
        $events = $repo->searchByTerm($term);
        $events = array_slice($events, 0, 5);

        $notFoundMessages = [
            'fr' => "Aucun événement ne correspond à '{$term}'. Essayez une catégorie ! 🔍",
            'ar' => "لا توجد أحداث تطابق '{$term}'. جرب فئة أخرى! 🔍",
            'drj' => "واحد حدث ماكايين لـ '{$term}'. جرب فرقة أخرى! 🔍"
        ];

        if (empty($events)) {
            return new JsonResponse([
                'reply' => $notFoundMessages[$language] ?? $notFoundMessages['fr'],
                'suggestions' => true,
                'language' => $language
            ]);
        }

        $eventsList = $this->formatEvents($events);
        
        $foundMessages = [
            'fr' => "Événements trouvés pour '{$term}' :\n\n{$eventsList}",
            'ar' => "أحداث موجودة لـ '{$term}':\n\n{$eventsList}",
            'drj' => "الأحداث لـ '{$term}':\n\n{$eventsList}"
        ];

        return new JsonResponse([
            'reply' => $foundMessages[$language] ?? $foundMessages['fr'],
            'events' => count($events),
            'suggestions' => false,
            'language' => $language
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