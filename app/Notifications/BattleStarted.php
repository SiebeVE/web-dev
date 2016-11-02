<?php

namespace App\Notifications;

use App\Battle;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BattleStarted extends Notification
{
    use Queueable;

	private $battle;
	private $user;

	/**
	 * Create a new notification instance.
	 *
	 * @param Battle $battle
	 * @param User $user
	 */
    public function __construct(Battle $battle, User $user)
    {
        $this->battle = $battle;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', 'https://laravel.com')
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'battle'=>$this->battle->toArray(),
            'opponents'=>$this->battle->getOpponents($this->user),
        ];
    }
}
