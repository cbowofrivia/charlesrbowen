<?php

namespace App\Mail;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversationAnalysisReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{gap_analysis: array<int, array{topic: string, description: string, evidence: string, severity: string}>, prompt_effectiveness: array<int, array{observation: string, example: string, suggestion: string}>, cv_suggestions: array<int, array{section: string, recommendation: string, rationale: string}>, summary: array{conversation_count: int, message_count: int, common_topics: array<int, string>, notable_interactions: string, is_heartbeat: bool}}  $report
     */
    public function __construct(
        public array $report,
        public CarbonImmutable $windowStart,
        public CarbonImmutable $windowEnd,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CV Agent Analysis Report — '.$this->windowStart->format('j M').' to '.$this->windowEnd->format('j M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.analysis-report',
        );
    }
}
