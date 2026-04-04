<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalFinalizedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Proposal $proposal,
        private readonly int $yesVotes,
        private readonly int $noVotes,
        private readonly int $quorum,
        private readonly bool $forced
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SIPR Proposal Finalized: '.$this->proposal->title)
            ->line('A proposal has been finalized.')
            ->line('Title: '.$this->proposal->title)
            ->line('Final Status: '.strtoupper($this->proposal->status))
            ->line('Votes (Yes/No): '.$this->yesVotes.'/'.$this->noVotes)
            ->line('Quorum Required: '.$this->quorum)
            ->line('Finalized by force: '.($this->forced ? 'Yes' : 'No'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'title' => $this->proposal->title,
            'status' => $this->proposal->status,
            'yes_votes' => $this->yesVotes,
            'no_votes' => $this->noVotes,
            'quorum' => $this->quorum,
            'forced' => $this->forced,
            'finalized_at' => optional($this->proposal->finalized_at)?->toDateTimeString(),
        ];
    }
}
