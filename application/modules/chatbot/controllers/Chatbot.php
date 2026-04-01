<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chatbot extends MX_Controller {
    
    private $whapi;
    private $model;
    
    public function __construct() {
        parent::__construct();
        
        // Chargement des ressources
        $this->load->library('whapi_client');
        $this->load->model('chatbot_model');
        $this->whapi = new Whapi_client();
        $this->model = $this->chatbot_model;
        
        // Charger les helpers
        $this->load->helper('chatbot_helper');
    }
    
    /**
     * Webhook principal - Reçoit les messages WhatsApp
     */
    public function webhook() {
        // Récupérer le body de la requête
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Journaliser l'arrivée
        log_message('info', 'Webhook received: ' . $input);
        
        if (!$data || empty($data['messages'])) {
            $this->output->set_status_header(200)->set_output('OK');
            return;
        }
        
        foreach ($data['messages'] as $message) {
            // Ignorer les messages du bot
            if (isset($message['from_me']) && $message['from_me']) {
                continue;
            }
            
            $this->processMessage($message);
        }
        
        $this->output->set_status_header(200)->set_output('OK');
    }
    
    /**
     * Traite un message reçu
     */
    private function processMessage($message) {
        $from = $message['from'];
        $text = trim($message['text']['body'] ?? '');
        $messageId = $message['id'] ?? null;
        
        // Récupérer ou créer l'utilisateur
        $user = $this->model->getOrCreateUser($from);
        
        // Sauvegarder le message entrant
        $this->model->saveMessage($user->id, $text);
        
        // Générer la réponse
        $response = $this->generateResponse($text, $user);
        
        // Sauvegarder la réponse
        if ($response) {
            $this->model->saveMessage($user->id, $text, $response['message'], 'outgoing');
            
            // Envoyer la réponse via WhatsApp
            $this->sendResponse($from, $response);
        }
    }
    
    /**
     * Génère la réponse en fonction du message
     */
    private function generateResponse($text, $user) {
        $text = strtolower(trim($text));
        
        // Vérifier les commandes personnalisées
        $command = $this->model->getCommand($text);
        if ($command) {
            return $this->formatCommandResponse($command, $user);
        }
        
        // Menu numérique
        if (is_numeric($text)) {
            return $this->handleMenuOption($text, $user);
        }
        
        // Commandes natives
        switch ($text) {
            case '/start':
                return $this->response('Bienvenue sur notre bot WhatsApp ! 👋\n\nTapez /aide pour voir toutes les commandes.');
                
            case '/aide':
            case '/help':
                $command = $this->model->getCommand('/aide');
                return $this->formatCommandResponse($command, $user);
                
            case '/menu':
                return $this->response($this->getMainMenu());
                
            case '/infos':
                return $this->response($this->getUserInfo($user));
                
            case '/image':
                return $this->response('📸 Voici une image !', 'image', 'https://picsum.photos/800/600');
                
            case '/video':
                return $this->response('🎬 Voici une vidéo !', 'video', 'https://www.learningcontainer.com/wp-content/uploads/2020/05/sample-mp4-file.mp4');
                
            case '/document':
                return $this->response('📄 Voici un document !', 'document', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf');
                
            case '/date':
                return $this->response('📅 ' . date('d/m/Y H:i:s'));
                
            case '/blague':
                return $this->response($this->getRandomJoke());
                
            case '/mesgroupes':
                return $this->response($this->getUserGroups());
                
            default:
                // Commande météo
                if (strpos($text, '/meteo') === 0) {
                    $city = trim(substr($text, 6));
                    if ($city) {
                        return $this->response($this->getWeather($city));
                    }
                    return $this->response('🌤️ Utilisation : /meteo [ville]');
                }
                
                return $this->response($this->getMainMenu());
        }
    }
    
    /**
     * Gère les options du menu
     */
    private function handleMenuOption($option, $user) {
        switch ($option) {
            case '1':
                return $this->response($this->getUserInfo($user));
            case '2':
                $menu = "📁 *Menu Médias*\n\n";
                $menu .= "1️⃣ Image\n2️⃣ Vidéo\n3️⃣ Document\n\n";
                $menu .= "Tapez /image, /video ou /document";
                return $this->response($menu);
            case '3':
                return $this->response($this->getUserGroups());
            case '4':
                $menu = "🔧 *Utilitaires*\n\n";
                $menu .= "/date - Date et heure\n";
                $menu .= "/meteo [ville] - Météo\n";
                $menu .= "/blague - Blague aléatoire";
                return $this->response($menu);
            default:
                return null;
        }
    }
    
    /**
     * Envoie la réponse via WhatsApp
     */
    private function sendResponse($to, $response) {
        try {
            switch ($response['type']) {
                case 'image':
                    return $this->whapi->sendImage($to, $response['media'], $response['message']);
                case 'video':
                    return $this->whapi->sendVideo($to, $response['media'], $response['message']);
                case 'document':
                    return $this->whapi->sendDocument($to, $response['media'], $response['message']);
                default:
                    return $this->whapi->sendText($to, $response['message']);
            }
        } catch (Exception $e) {
            log_message('error', 'Send failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Formate la réponse d'une commande
     */
    private function formatCommandResponse($command, $user) {
        $message = $command->response;
        
        // Remplacer les variables
        $replacements = [
            '{name}' => $user->name ?? $user->phone,
            '{phone}' => $user->phone,
            '{total_messages}' => $user->total_messages,
            '{status}' => $user->status,
            '{datetime}' => date('d/m/Y H:i:s')
        ];
        
        $message = str_replace(array_keys($replacements), $replacements, $message);
        
        return [
            'message' => $message,
            'type' => $command->type,
            'media' => $command->media_url
        ];
    }
    
    /**
     * Crée un objet réponse
     */
    private function response($message, $type = 'text', $media = null) {
        $response = ['message' => $message, 'type' => $type];
        if ($media) {
            $response['media'] = $media;
        }
        return $response;
    }
    
    /**
     * Menu principal
     */
    private function getMainMenu() {
        return "🏠 *Menu Principal*\n\n" .
               "1️⃣ Informations\n" .
               "2️⃣ Médias\n" .
               "3️⃣ Groupes\n" .
               "4️⃣ Utilitaires\n\n" .
               "Tapez le numéro du menu ou une commande comme /aide";
    }
    
    /**
     * Informations utilisateur
     */
    private function getUserInfo($user) {
        return "📱 *Vos informations*\n\n" .
               "📞 Téléphone : {$user->phone}\n" .
               "💬 Messages : {$user->total_messages}\n" .
               "📅 Dernière activité : " . date('d/m/Y H:i', strtotime($user->last_message_at)) . "\n" .
               "📊 Statut : " . ucfirst($user->status);
    }
    
    /**
     * Récupère les groupes
     */
    private function getUserGroups() {
        $groups = $this->whapi->getGroups(5);
        
        if (!$groups || empty($groups['groups'])) {
            return "Vous n'êtes dans aucun groupe pour l'instant.";
        }
        
        $text = "👥 *Vos groupes :*\n\n";
        foreach ($groups['groups'] as $group) {
            $text .= "📌 {$group['name']}\n";
            $text .= "   ID: {$group['id']}\n\n";
        }
        
        return $text;
    }
    
    /**
     * Blague aléatoire
     */
    private function getRandomJoke() {
        $jokes = [
            "Pourquoi les plongeurs plongent-ils toujours en arrière ?\nParce que sinon ils tombent dans le bateau !",
            "Que dit un œuf quand il voit un œuf frit ?\n« Tiens, un œuf frit ! »",
            "Pourquoi les girafes ont-elles un long cou ?\nParce qu'elles ont les pieds qui puent !",
            "Quel est le comble pour un électricien ?\nNe pas être au courant !"
        ];
        return $jokes[array_rand($jokes)];
    }
    
    /**
     * Météo (simulation)
     */
    private function getWeather($city) {
        $weathers = ['ensoleillé ☀️', 'nuageux ☁️', 'pluvieux 🌧️', 'orageux ⚡'];
        $temps = $weathers[array_rand($weathers)];
        $temp = rand(15, 35);
        
        return "🌤️ *Météo à {$city}*\n\n" .
               "{$temps} - {$temp}°C\n\n" .
               "💡 Humidité : " . rand(40, 90) . "%\n" .
               "🌬️ Vent : " . rand(5, 30) . " km/h";
    }
}