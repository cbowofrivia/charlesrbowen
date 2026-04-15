<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1a1a1a; line-height: 1.6; max-width: 680px; margin: 0 auto; padding: 20px; }
        h1 { font-size: 22px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
        h2 { font-size: 18px; color: #374151; margin-top: 28px; }
        .meta { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        .severity { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .severity-high { background: #fee2e2; color: #991b1b; }
        .severity-medium { background: #fef3c7; color: #92400e; }
        .severity-low { background: #dbeafe; color: #1e40af; }
        .item { background: #f9fafb; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
        .item-title { font-weight: 600; margin-bottom: 4px; }
        .evidence, .example { font-style: italic; color: #6b7280; margin-top: 6px; font-size: 14px; }
        .stats { display: flex; gap: 24px; margin-bottom: 16px; }
        .stat { text-align: center; }
        .stat-value { font-size: 28px; font-weight: 700; color: #111827; }
        .stat-label { font-size: 13px; color: #6b7280; }
        .topics { margin-top: 8px; }
        .topic-tag { display: inline-block; background: #e5e7eb; padding: 2px 10px; border-radius: 12px; font-size: 13px; margin: 2px 4px 2px 0; }
        .heartbeat { text-align: center; padding: 40px 20px; color: #6b7280; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <h1>CV Agent Analysis Report</h1>
    <p class="meta">{{ $windowStart->format('j M Y') }} &mdash; {{ $windowEnd->format('j M Y') }}</p>

    @if($report['summary']['is_heartbeat'])
        <div class="heartbeat">
            <p><strong>No conversations in this period.</strong> The system is running normally.</p>
        </div>
    @else
        <h2>Summary</h2>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px;">
            <tr>
                <td style="text-align: center; padding: 12px;">
                    <div style="font-size: 28px; font-weight: 700; color: #111827;">{{ $report['summary']['conversation_count'] }} conversations</div>
                </td>
                <td style="text-align: center; padding: 12px;">
                    <div style="font-size: 28px; font-weight: 700; color: #111827;">{{ $report['summary']['message_count'] }} messages</div>
                </td>
            </tr>
        </table>

        @if(count($report['summary']['common_topics']) > 0)
            <div class="topics">
                <strong>Common topics:</strong>
                @foreach($report['summary']['common_topics'] as $topic)
                    <span class="topic-tag">{{ $topic }}</span>
                @endforeach
            </div>
        @endif

        @if($report['summary']['notable_interactions'])
            <p><strong>Notable:</strong> {{ $report['summary']['notable_interactions'] }}</p>
        @endif

        @if(count($report['gap_analysis']) > 0)
            <h2>Gap Analysis</h2>
            @foreach($report['gap_analysis'] as $gap)
                <div class="item">
                    <div class="item-title">
                        {{ $gap['topic'] }}
                        <span class="severity severity-{{ $gap['severity'] }}">{{ $gap['severity'] }}</span>
                    </div>
                    <p>{{ $gap['description'] }}</p>
                    <p class="evidence">{{ $gap['evidence'] }}</p>
                </div>
            @endforeach
        @endif

        @if(count($report['prompt_effectiveness']) > 0)
            <h2>Prompt Effectiveness</h2>
            @foreach($report['prompt_effectiveness'] as $item)
                <div class="item">
                    <div class="item-title">{{ $item['observation'] }}</div>
                    <p class="example">{{ $item['example'] }}</p>
                    <p><strong>Suggestion:</strong> {{ $item['suggestion'] }}</p>
                </div>
            @endforeach
        @endif

        @if(count($report['cv_suggestions']) > 0)
            <h2>CV Content Suggestions</h2>
            @foreach($report['cv_suggestions'] as $suggestion)
                <div class="item">
                    <div class="item-title">{{ $suggestion['section'] }}</div>
                    <p>{{ $suggestion['recommendation'] }}</p>
                    <p class="evidence">{{ $suggestion['rationale'] }}</p>
                </div>
            @endforeach
        @endif
    @endif

    <div class="footer">
        Generated automatically by charlesrbowen.com conversation analysis.
    </div>
</body>
</html>
