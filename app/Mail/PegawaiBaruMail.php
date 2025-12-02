<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PegawaiBaruMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pegawai;
    public $rawPassword;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $pegawai, string $rawPassword)
    {
        $this->pegawai = $pegawai;
        $this->rawPassword = $rawPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Selamat Bergabung di Laptop Store!')
                    ->markdown('emails.pegawai-baru'); // View email
    }
}