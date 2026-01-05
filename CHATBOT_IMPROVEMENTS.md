# 🤖 Améliorations du Chatbot Qataa3.ma

## 📋 Nouvelles Fonctionnalités

### 1. ✅ Salutations - "Bonjour"
Le chatbot reconnaît les salutations et répond de manière personnalisée:
- **Français**: "Bonjour", "Salut", "Coucou", "Hello", "Hi", "Allo"
- **Arabe**: "السلام عليكم", "مرحبا", "السلام", "هلا", "أهلا"
- **Darija**: "سلام", "رحبا", "واش خبار", "أشنو الأخبار", "بونجور"

**Réponse**: Message de bienvenue personnalisé dans la langue détectée.

### 2. 💬 Demande d'Aide - "Comment je peux vous aider"
Le chatbot fournit une aide complète sur les services disponibles:
- Recherche d'événements (Concerts, Festivals, Spectacles, Formations, Sports)
- Gestion des tickets
- Informations de paiement
- Contact et support
- FAQ

**Réponse**: Menu détaillé avec toutes les options disponibles.

### 3. 🎫 Comptage des Tickets - "Combien de tickets restent"
Le chatbot affiche les statistiques actuelles sur les tickets:
- **Tickets disponibles**: Nombre de tickets valides en stock
- **Tickets utilisés**: Nombre de tickets déjà consommés
- **Total**: Nombre total de tickets

**Réponse**: Statistiques en temps réel avec suggestion d'achat.

### 4. 🌐 Support Multilingue
Le chatbot supporte maintenant **3 langues**:
- 🇫🇷 **Français**
- 🇸🇦 **Arabe Standard**
- 🇲🇦 **Darija (Arabe Marocain)**

**Fonctionnalités**:
- Détection automatique de la langue du message
- Sélecteur de langue dans l'interface
- Sauvegarde de la préférence linguistique (LocalStorage)
- Tous les messages traduits dans les 3 langues

### 5. 🏢 Redirection vers les Départements
Le chatbot détecte les demandes spécifiques et redirige vers le département approprié:

#### **Contact**
- Mots-clés: contact, contacter, email, adresse, téléphone, whatsapp
- Réponse: Informations de contact avec liens directs

#### **Paiement**
- Mots-clés: paiement, payer, prix, coût, carte, problème paiement
- Réponse: Méthodes de paiement et tarifs

#### **Gestion des Tickets**
- Mots-clés: mon ticket, mon billet, qr code, code qr, utiliser ticket, valider
- Réponse: Lien vers la gestion des tickets personnels

#### **Événements**
- Mots-clés: événement, concert, festival, spectacle, sport, formation
- Réponse: Lien vers la page des événements

### 6. 📊 Fourniture d'Informations Automatisée
Le chatbot fournit automatiquement les informations pertinentes:
- Détection du contexte et type de requête
- Réponses spécifiques selon le contexte
- Suggestions intelligentes après chaque réponse
- Format lisible avec emojis et mise en forme

---

## 🛠️ Architecture Technique

### Fichiers Modifiés

#### 1. **`src/Service/ChatService.php`** (Nouveau)
Service centralisé pour la logique du chatbot:
- Détection de langue basée sur les caractères arabes
- Détection des intentions (salutation, aide, tickets, etc.)
- Génération des réponses multilingues
- Gestion des départements et redirections

**Méthodes principales**:
- `detectLanguage(string $message): string` - Détecte FR/AR/DRJ
- `isGreeting(string $message, string $language): bool`
- `isHelpRequest(string $message, string $language): bool`
- `isTicketsRequest(string $message, string $language): bool`
- `detectDepartment(string $message, string $language): ?string`
- `getTicketsInfo(string $language): array` - Stats des tickets en temps réel

#### 2. **`src/Controller/ChatController.php`** (Amélioré)
Contrôleur principale mise à jour:
- Intégration du ChatService
- Logique d'acheminement des requêtes
- Support multilingue intégral
- Utilisation de la détection automatique de langue

#### 3. **`templates/base.html.twig`** (Amélioré)
Interface du chatbot avec nouvelles fonctionnalités:

**HTML**:
- Sélecteur de langue multilingue
- Boutons de suggestion mis à jour
- Support pour arabe/darija

**JavaScript**:
- Configuration multilingue complète
- Gestion du changement de langue
- Persistance de la préférence (LocalStorage)
- Envoi de la langue sélectionnée au serveur

---

## 🚀 Utilisation

### Pour l'Utilisateur

1. **Ouverture du chat**: Clic sur le bouton chat en bas à droite
2. **Sélection de langue**: Choisir la langue préférée (FR/AR/DRJ)
3. **Interaction**:
   - Taper une question ou cliquer un bouton suggestion
   - Le chatbot répond automatiquement
   - Suggestions affichées après chaque réponse

### Exemples de Requêtes

**Français**:
- "Bonjour"
- "Comment je peux vous aider?"
- "Combien de tickets restent?"
- "Cherche des concerts"

**Arabe**:
- "السلام عليكم"
- "كيف يمكنني مساعدتك?"
- "كم تذكرة متبقية؟"
- "ابحث عن الحفلات"

**Darija**:
- "سلام"
- "كيفاش تقدر تساعدني؟"
- "شحال الجوجة الباقيين؟"
- "دور لي على الحفلات"

---

## 📦 Dépendances

Le service utilise les repositories existants:
- `EvenementRepository` - Pour les événements
- `TicketRepository` - Pour les statistiques des tickets
- `CommandeRepository` - Pour les commandes (optionnel)

---

## 🔄 Flux d'exécution

```
Message utilisateur
       ↓
Détection de langue
       ↓
├─ Salutation? → Réponse bienvenue
├─ Aide? → Menu d'aide
├─ Tickets? → Statistiques
├─ Département? → Redirection
├─ Catégorie? → Événements filtrés
└─ Terme? → Recherche
       ↓
Réponse formatée + Suggestions
```

---

## 📱 Compatibilité

- ✅ Desktop
- ✅ Mobile (responsive)
- ✅ Tablette
- ✅ Navigateurs modernes (Chrome, Firefox, Safari, Edge)
- ✅ Support de l'arabe (RTL)

---

## 💡 Améliorations Futures

- [ ] Intégration avec système de ticket de support
- [ ] Machine Learning pour améliorer les détections
- [ ] Historique des conversations
- [ ] Intégration avec emails/notifications
- [ ] Analytics sur les requêtes
- [ ] Support de plus de langues

---

## 👨‍💻 Développement

Pour ajouter une nouvelle langue:

1. Ajouter la langue dans `ChatService.php` (arrays multilingues)
2. Ajouter les traductions en JavaScript dans `base.html.twig`
3. Ajouter l'option dans le sélecteur de langue HTML

```php
// Exemple pour l'espagnol
'es' => [
    'greeting' => '¡Hola! Soy tu asistente de Qataa3.ma...',
    // ...
]
```

---

**Version**: 2.0
**Dernière mise à jour**: Janvier 2025
**Auteur**: Équipe Développement Qataa3.ma
