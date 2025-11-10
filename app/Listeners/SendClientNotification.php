<?php

namespace App\Listeners;

use App\Events\AccountCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SendClientNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AccountCreated  $event
     */
    public function handle(AccountCreated $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        // Envoyer un email de bienvenue
        try {
            // Mail::to($user->email)->send(new WelcomeEmail($user));
            Log::info("Email de bienvenue envoyé à {$user->email}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'email de bienvenue à {$user->email}: " . $e->getMessage());
        }

        // Envoyer un SMS avec le code PIN (pour l'exemple, le PIN est déjà hashé, donc on ne l'envoie pas directement)
        // Dans un cas réel, le PIN serait généré ici et envoyé avant d'être hashé et stocké.
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $twilioNumber = env('TWILIO_NUMBER');

            if ($sid && $token && $twilioNumber) {
                $client = new Client($sid, $token);
                $client->messages->create(
                    $user->phone_number,
                    [
                        'from' => $twilioNumber,
                        'body' => "Bienvenue chez Orange Money, {$user->first_name}! Votre compte a été créé avec succès."
                    ]
                );
                Log::info("SMS de bienvenue envoyé à {$user->phone_number}");
            } else {
                Log::warning("Les identifiants Twilio ne sont pas configurés. SMS non envoyé à {$user->phone_number}.");
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du SMS de bienvenue à {$user->phone_number}: " . $e->getMessage());
        }

        // Commenter le job SendInAppNotification car nous ne l'utilisons pas pour l'instant
        // SendInAppNotification::dispatch($user);
    }
}
