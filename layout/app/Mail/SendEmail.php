<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Layout\Website\Services\ThemeService;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;


	public $email;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->email = $email;
		$this->subject($email->subject);
		if(!empty($email->email_reply_to) && !empty($email->email_reply_to_name)){
		    $this->replyTo($email->email_reply_to, $email->email_reply_to_name);
        }elseif(ThemeService::ConfigValue('EMAIL_REPLY_TO')){
		    $this->replyTo(ThemeService::ConfigValue('EMAIL_REPLY_TO'), ThemeService::ConfigValue('NEWSPAPER_NAME'));
        }
		//$this->from($email->senderEmail, $email->senderUserName);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
	public function build()
    {


        if(empty($this->email->senderEmailTitle)){
            $this->email->senderEmailTitle= null;
        }

        $mail = $this->from($this->email->senderEmail,$this->email->senderEmailTitle)->view($this->email->view);

        
        if (isset($this->email->attach)) { 
            $mail = $mail->attach($this->email->attach->getRealPath(), [
                'mime' => $this->email->attach->getClientMimeType(),
                'as' => $this->email->attach->getClientOriginalName()
            ]);
         }
         
        if (isset($this->email->attach2)) { 
            $mail = $mail->attach($this->email->attach2->getRealPath(), [
                'mime' => $this->email->attach2->getClientMimeType(),
                'as' => $this->email->attach2->getClientOriginalName()
            ]);
        }
         
        if (isset($this->email->attachments)) { 
            foreach($this->email->attachments as $file){
                
               $this->attach($file->getRealPath(), [
                    'mime' => $file->getClientMimeType(),
                    'as' => $file->getClientOriginalName()
                ]);  
            }
        }
        return $mail;
        
        
    }
}
