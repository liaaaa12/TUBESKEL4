<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuktiBayarKonsinyasi extends Mailable
{
    use Queueable, SerializesModels;

    public $pembayaran;
    public $pdf;
    public $soldItems;

    public function __construct($pembayaran, $pdf, $soldItems)
    {
        $this->pembayaran = $pembayaran;
        $this->pdf = $pdf;
        $this->soldItems = $soldItems;
    }

    public function build()
    {
        return $this->subject('Bukti Pembayaran Konsinyasi')
            ->view('emails.bukti-bayar')
            ->with([
                'soldItems' => $this->soldItems,
                'pembayaran' => $this->pembayaran,
            ])
            ->attachData($this->pdf->output(), 'bukti-bayar.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
