<?php

namespace App\Service;

use App\Repository\EvenementRepository;
use App\Repository\TicketRepository;
use App\Repository\CommandeRepository;

class ChatService
{
    private array $greetings = [
        'fr' => ['bonjour', 'salut', 'coucou', 'hello', 'hi', 'allo'],
        'ar' => ['السلام عليكم', 'مرحبا', 'السلام', 'هلا', 'أهلا'],
        'drj' => ['سلام', 'رحبا', 'واش خبار', 'أشنو الأخبار', 'بونجور']
    ];

    private array $helpKeywords = [
        'fr' => ['aide', 'help', 'comment', 'aidez', 'assistance', 'support'],
        'ar' => ['ساعد', 'مساعدة', 'كيفية', 'كيف', 'مدير'],
        'drj' => ['ساعد', 'كيفاش', 'شنو الخدمات', 'واش كتقدمو']
    ];

    private array $ticketsKeywords = [
        'fr' => ['combien', 'ticket', 'billet', 'disponible', 'reste', 'restant', 'disponibles', 'combien de', 'how many'],
        'ar' => ['كم', 'تذكرة', 'تذاكر', 'متبقي', 'تبقى', 'متوفر'],
        'drj' => ['شحال', 'تذكرة', 'واش تبقات', 'كم واحد']
    ];

    private array $departmentKeywords = [
        'fr' => [
            'contact' => ['contact', 'contacter', 'email', 'adresse', 'téléphone', 'whatsapp'],
            'paiement' => ['paiement', 'payer', 'prix', 'coût', 'carte', 'problème paiement'],
            'ticket' => ['mon ticket', 'mon billet', 'qr code', 'code qr', 'utiliser ticket', 'valider'],
            'événement' => ['événement', 'concert', 'festival', 'spectacle', 'sport', 'formation']
        ],
        'ar' => [
            'contact' => ['اتصال', 'تواصل', 'بريد', 'رقم', 'هاتف'],
            'paiement' => ['دفع', 'سعر', 'بطاقة', 'مشكلة دفع'],
            'ticket' => ['تذكرتي', 'كود', 'تفعيل', 'استخدام'],
            'événement' => ['حدث', 'حفلة', 'مهرجان', 'عرض']
        ],
        'drj' => [
            'contact' => ['اتصل بيا', 'واصل معايا', 'رقم', 'ايميل'],
            'paiement' => ['الفلوس', 'الثمن', 'تسديد', 'مشكلة'],
            'ticket' => ['التذكرة', 'الكود', 'استعمل'],
            'événement' => ['الحدث', 'الحفلة', 'الفرقة']
        ]
    ];

    public function __construct(
        private EvenementRepository $evenementRepository,
        private TicketRepository $ticketRepository,
        private CommandeRepository $commandeRepository
    ) {}

    /**
     * Détecte la langue du message
     */
    public function detectLanguage(string $message): string
    {
        // Vérifie les caractères arabes
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $message)) {
            return 'ar'; // Arabe
        }
        // Par défaut français
        return 'fr';
    }

    /**
     * Détecte le type de salutation
     */
    public function isGreeting(string $message, string $language): bool
    {
        $message = strtolower(trim($message));
        $keywords = $this->greetings[$language] ?? [];
        
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Détecte une demande d'aide
     */
    public function isHelpRequest(string $message, string $language): bool
    {
        $message = strtolower(trim($message));
        $keywords = $this->helpKeywords[$language] ?? [];
        
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Détecte une demande de tickets
     */
    public function isTicketsRequest(string $message, string $language): bool
    {
        $message = strtolower(trim($message));
        $keywords = $this->ticketsKeywords[$language] ?? [];
        
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Détecte le département demandé
     */
    public function detectDepartment(string $message, string $language): ?string
    {
        $message = strtolower(trim($message));
        $departments = $this->departmentKeywords[$language] ?? [];
        
        foreach ($departments as $department => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $department;
                }
            }
        }
        return null;
    }

    /**
     * Obtient un message de salutation
     */
    public function getGreetingMessage(string $language): string
    {
        $messages = [
            'fr' => "Bonjour 👋 Je suis ton assistant virtuel Qataa3.ma ! Comment je peux vous aider ?",
            'ar' => "السلام عليكم ورحمة الله وبركاته 👋 أنا مساعدك الافتراضي في Qataa3.ma ! كيف يمكنني مساعدتك؟",
            'drj' => "سلام 👋 أنا مساعدك الافتراضي في Qataa3.ma ! شنو الخدمة؟"
        ];
        
        return $messages[$language] ?? $messages['fr'];
    }

    /**
     * Obtient un message d'aide
     */
    public function getHelpMessage(string $language): array
    {
        $helpMessages = [
            'fr' => [
                'reply' => "🎯 **Voici comment je peux vous aider :**\n\n" .
                    "📅 **Événements** - Cherchez concerts, festivals, spectacles, formations, sports\n" .
                    "🎫 **Tickets** - Vérifiez combien de tickets restent disponibles\n" .
                    "💳 **Paiement** - Questions sur vos achats et tarifs\n" .
                    "🎟️ **Mes Tickets** - Vérifiez vos codes QR et statuts\n" .
                    "📞 **Contact** - Besoin de nous joindre ?\n" .
                    "❓ **FAQ** - Réponses aux questions fréquentes\n\n" .
                    "Comment je peux vous aider ?",
                'suggestions' => true
            ],
            'ar' => [
                'reply' => "🎯 **إليك كيف يمكنني مساعدتك:**\n\n" .
                    "📅 **الفعاليات** - ابحث عن الحفلات والمهرجانات والعروض والتدريبات\n" .
                    "🎫 **التذاكر** - تحقق من عدد التذاكر المتبقية\n" .
                    "💳 **الدفع** - أسئلة حول مشترياتك والأسعار\n" .
                    "🎟️ **تذاكري** - تحقق من أكوادك وحالتك\n" .
                    "📞 **التواصل** - هل تحتاج للتواصل معنا؟\n" .
                    "❓ **الأسئلة الشائعة** - إجابات على الأسئلة المتكررة\n\n" .
                    "كيف يمكنني مساعدتك؟",
                'suggestions' => true
            ],
            'drj' => [
                'reply' => "🎯 **إليك الخدمات:**\n\n" .
                    "📅 **الفعاليات** - دور على الحفلات والفرق والعروض\n" .
                    "🎫 **التذاكر** - شنو عدد التذاكر الباقيين\n" .
                    "💳 **الفلوس** - الأسعار والدفع\n" .
                    "🎟️ **التذاكر ديالي** - الكود والحالة\n" .
                    "📞 **اتصل بيا** - قصة الاتصال\n\n" .
                    "واش الخدمة؟",
                'suggestions' => true
            ]
        ];
        
        return $helpMessages[$language] ?? $helpMessages['fr'];
    }

    /**
     * Obtient le nombre de tickets restants
     */
    public function getTicketsInfo(string $language): array
    {
        $totalTickets = $this->ticketRepository->count([]);
        $availableTickets = $this->ticketRepository->count(['statutTicket' => 'valide']);
        $usedTickets = $totalTickets - $availableTickets;

        $messages = [
            'fr' => "🎫 **Informations sur les Tickets:**\n\n" .
                "✅ Tickets disponibles: **{$availableTickets}**\n" .
                "✔️ Tickets utilisés: **{$usedTickets}**\n" .
                "📊 Total: **{$totalTickets}**\n\n" .
                "Voulez-vous acheter des tickets ?",
            'ar' => "🎫 **معلومات التذاكر:**\n\n" .
                "✅ التذاكر المتاحة: **{$availableTickets}**\n" .
                "✔️ التذاكر المستخدمة: **{$usedTickets}**\n" .
                "📊 المجموع: **{$totalTickets}**\n\n" .
                "هل تريد شراء تذاكر؟",
            'drj' => "🎫 **معلومات التذاكر:**\n\n" .
                "✅ التذاكر الباقيين: **{$availableTickets}**\n" .
                "✔️ التذاكر المستعملين: **{$usedTickets}**\n" .
                "📊 المجموع: **{$totalTickets}**\n\n" .
                "واش بغيتي تشري تذاكر؟"
        ];

        return [
            'reply' => $messages[$language] ?? $messages['fr'],
            'suggestions' => true
        ];
    }

    /**
     * Obtient un message de redirection vers un département
     */
    public function getDepartmentMessage(string $department, string $language): array
    {
        $messages = [
            'fr' => [
                'contact' => [
                    'reply' => "📞 **Notre équipe Contact:**\n\n" .
                        "Email: support@qataa3.ma\n" .
                        "Téléphone: +212 5XX XXX XXX\n" .
                        "WhatsApp: Disponible 24/7\n" .
                        "Chat: Notre équipe vous répondra rapidement\n\n" .
                        "👉 [Contacter le support](tel:+212XXXXXXXXX)",
                    'suggestions' => true
                ],
                'paiement' => [
                    'reply' => "💳 **Service Paiement:**\n\n" .
                        "Nous acceptons:\n" .
                        "💳 Carte Bancaire\n" .
                        "📱 Portefeuille Mobile (Maroc Telecom, Orange)\n" .
                        "🏦 Virement Bancaire\n\n" .
                        "Frais: Gratuit pour les virements directs\n" .
                        "Délai: Instantané pour cartes et portefeuilles\n\n" .
                        "Des problèmes? Contactez: paiement@qataa3.ma",
                    'suggestions' => true
                ],
                'ticket' => [
                    'reply' => "🎟️ **Gestion de vos Tickets:**\n\n" .
                        "✅ Vérifiez votre code QR\n" .
                        "📌 Votre code personnel\n" .
                        "🔐 Statut de validation\n\n" .
                        "👉 [Accédez à mes tickets](/client-tickets)\n" .
                        "💡 Besoin d'aide? tickets@qataa3.ma",
                    'suggestions' => true
                ],
                'événement' => [
                    'reply' => "🎭 **Découvrez nos Événements:**\n\n" .
                        "🎵 Concerts\n" .
                        "🎪 Festivals\n" .
                        "🎭 Spectacles\n" .
                        "⚽ Sports\n" .
                        "📚 Formations\n\n" .
                        "👉 [Voir tous les événements](/events)",
                    'suggestions' => false
                ]
            ],
            'ar' => [
                'contact' => [
                    'reply' => "📞 **فريق التواصل لدينا:**\n\n" .
                        "البريد: support@qataa3.ma\n" .
                        "الهاتف: +212 5XX XXX XXX\n" .
                        "واتس: متوفر 24/7\n\n" .
                        "👉 [اتصل بنا](tel:+212XXXXXXXXX)",
                    'suggestions' => true
                ],
                'paiement' => [
                    'reply' => "💳 **خدمة الدفع:**\n\n" .
                        "نقبل:\n" .
                        "💳 البطاقة البنكية\n" .
                        "📱 المحفظة الرقمية\n" .
                        "🏦 التحويل البنكي\n\n" .
                        "الرسوم: مجاني",
                    'suggestions' => true
                ],
                'ticket' => [
                    'reply' => "🎟️ **إدارة تذاكرك:**\n\n" .
                        "✅ تحقق من كود QR\n" .
                        "📌 شفرتك الشخصية\n" .
                        "🔐 حالة التفعيل\n\n" .
                        "👉 [أذهب إلى تذاكري](/client-tickets)",
                    'suggestions' => true
                ],
                'événement' => [
                    'reply' => "🎭 **اكتشف فعالياتنا:**\n\n" .
                        "🎵 الحفلات الموسيقية\n" .
                        "🎪 المهرجانات\n" .
                        "🎭 العروض\n" .
                        "⚽ الرياضة\n" .
                        "📚 التدريبات\n\n" .
                        "👉 [شاهد كل الفعاليات](/events)",
                    'suggestions' => false
                ]
            ],
            'drj' => [
                'contact' => [
                    'reply' => "📞 **فريق الاتصال:**\n\n" .
                        "الإيميل: support@qataa3.ma\n" .
                        "الهاتف: +212 5XX XXX XXX\n\n" .
                        "👉 [اتصل بنا](tel:+212XXXXXXXXX)",
                    'suggestions' => true
                ],
                'paiement' => [
                    'reply' => "💳 **خدمة الدفع:**\n\n" .
                        "نقبل:\n" .
                        "💳 البطاقة\n" .
                        "📱 المحفظة الرقمية\n" .
                        "🏦 التحويل\n\n" .
                        "الرسوم: مجاني",
                    'suggestions' => true
                ],
                'ticket' => [
                    'reply' => "🎟️ **التذاكر ديالك:**\n\n" .
                        "✅ شنو الكود\n" .
                        "📌 الحالة\n\n" .
                        "👉 [التذاكر ديالي](/client-tickets)",
                    'suggestions' => true
                ],
                'événement' => [
                    'reply' => "🎭 **الفعاليات:**\n\n" .
                        "🎵 الحفلات\n" .
                        "🎪 الفرق\n" .
                        "🎭 العروض\n\n" .
                        "👉 [كل الفعاليات](/events)",
                    'suggestions' => false
                ]
            ]
        ];

        return $messages[$language][$department] ?? [
            'reply' => "Je ne suis pas sûr de comprendre votre demande. Pouvez-vous reformuler ?",
            'suggestions' => true
        ];
    }
}
