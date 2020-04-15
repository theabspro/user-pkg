<?php

namespace  Abs\UserPkg\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $arr;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($arr) {
        $this->arr = $arr;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->data['user_name']= $this->arr['user_name'];
        return $this->to($this->arr['to_email'])
            ->subject($this->arr['subject'])
            //->markdown('packages/abs/user-pkg/src/views/mail/user_invite_preview')
            //->text('packages/abs/user-pkg/src/views/mail/user_invite_preview')
            //->view('packages/abs/user-pkg/src/views/mail/user_invite_preview')
            ->view('mail/user_invite_preview')
            ->with($this->data);
    }
}
