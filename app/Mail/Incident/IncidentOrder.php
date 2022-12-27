<?php

namespace App\Mail\Incident;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Loan;
use App\Models\Incident;

class IncidentOrder extends Mailable
{
    use Queueable, SerializesModels;

    public $incident;                           #Eloquent Model with all the information and relations about the loan
    public $bookingType;                        #Whether the booking/reservation was created or modified
    public $bookingTitle;                       #Whether we are dealing with a booking or reservation

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Incident $incident, $status, $cost)
    {
        $this->incident = $incident;
        $this->incident->totalCost = (string)$cost;
        $this->bookingTitle = "Incident";

        switch($incident->status_id){
            case(0):
                #Outstanding
                $this->bookingType = $status ? "created" : "modified";
                break;
            case(1):
                #Resolved
                $this->bookingType = 'resolved';
                break;
        }

        $emailSubject = $this->bookingTitle . " #" . $this->incident->id . " " . ucfirst($this->bookingType);
        $this->subject($emailSubject);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.incident.order');
    }
}