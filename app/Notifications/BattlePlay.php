<?php

namespace App\Notifications;

use App\Battle;
use App\Pick;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BattlePlay extends Notification
{
	use Queueable;

	private $battle;
	private $pick;

	/**
	 * Create a new notification instance.
	 *
	 * @param Battle $battle
	 * @param $pick
	 */
	public function __construct (Battle $battle, $pick) {
		$this->battle = $battle;
		$this->pick = $pick;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return array
	 */
	public function via ($notifiable) {
		return ['database'];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail ($notifiable) {
		return (new MailMessage)
			->line('The introduction to the notification.')
			->action('Notification Action', 'https://laravel.com')
			->line('Thank you for using our application!');
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return array
	 */
	public function toArray ($notifiable) {
		return [
			"opponents" => $this->battle->getOpponents(),
			"pick"   => $this->pick,
		];
	}
}
