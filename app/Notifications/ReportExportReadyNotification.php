<?php

namespace App\Notifications;

use App\Models\ReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ReportExport $export)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SIPR Report Export Ready')
            ->line('Your requested report export is ready.')
            ->line('Type: '.$this->export->type)
            ->line('Status: '.strtoupper($this->export->status));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'report_export_id' => $this->export->id,
            'type' => $this->export->type,
            'status' => $this->export->status,
            'file_path' => $this->export->file_path,
            'completed_at' => optional($this->export->completed_at)?->toDateTimeString(),
        ];
    }
}
