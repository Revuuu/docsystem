<?php

namespace App\Mail;

use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovalPendingNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Approval $approval;

    public function __construct(Approval $approval)
    {
        $this->approval = $approval;
    }

    public function build()
    {
        return $this
            ->subject('Document Approval Required')
            ->view('emails.approval');
    }
}