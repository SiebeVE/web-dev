<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailConfirm extends Notification {
	use Queueable;

	protected $user;

	/**
	 * Create a new notification instance.
	 *
	 * @param User $user
	 */
	public function __construct( User $user ) {
		$this->user = $user;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return array
	 */
	public function via( $notifiable ) {
		return [ 'mail' ];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail( $notifiable ) {
		return ( new MailMessage )
			->subject( 'Confirm your e-mail - '.env("APP_NAME", "Laravel") )
			->greeting("Hi ".$this->user->name .",")
			->line( 'Great, you want to register! Please first confirm your e-mail so you can play this awesome contest.' )
			->action( 'Confirm my email', url("register/confirm/".$this->user->token_mail) );
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return array
	 */
	public function toArray( $notifiable ) {
		return [
			//
		];
	}
}
