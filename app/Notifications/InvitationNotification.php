<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Invitation $invitation
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = route('invitations.accept', ['token' => $this->invitation->token], absolute: true);

        return (new MailMessage)
            ->subject('Uitnodiging voor je Advent Kalender')
            ->greeting('Hallo!')
            ->line('Je bent uitgenodigd om je persoonlijke advent kalender te bekijken.')
            ->line('Klik op de onderstaande knop om je account aan te maken en je wachtwoord in te stellen.')
            ->action('Account aanmaken', $acceptUrl)
            ->line('Deze uitnodiging verloopt over 7 dagen.')
            ->line('Als je deze uitnodiging niet hebt aangevraagd, kun je deze e-mail negeren.');
    }
}
