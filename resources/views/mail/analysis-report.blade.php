<x-mail::message>
# CV Agent Analysis Report

**{{ $windowStart->format('j M Y') }}** to **{{ $windowEnd->format('j M Y') }}**

@if($report['summary']['is_heartbeat'] ?? false)
<x-mail::panel>
**No conversations in this period.** The system is running normally.
</x-mail::panel>
@else
## Summary

<x-mail::table>
| Conversations | Messages |
| :-----------: | :------: |
| {{ $report['summary']['conversation_count'] ?? 0 }} | {{ $report['summary']['message_count'] ?? 0 }} |
</x-mail::table>

@if(count($report['summary']['common_topics'] ?? []) > 0)
**Common topics:** {{ implode(', ', $report['summary']['common_topics']) }}
@endif

@if($report['summary']['notable_interactions'] ?? '')
**Notable:** {{ $report['summary']['notable_interactions'] }}
@endif

@if(count($report['gap_analysis'] ?? []) > 0)
## Gap Analysis

@foreach($report['gap_analysis'] as $gap)
**{{ $gap['topic'] }}** · _{{ $gap['severity'] }}_

{{ $gap['description'] }}

> {{ $gap['evidence'] }}

@endforeach
@endif

@if(count($report['prompt_effectiveness'] ?? []) > 0)
## Prompt Effectiveness

@foreach($report['prompt_effectiveness'] as $item)
**{{ $item['observation'] }}**

> {{ $item['example'] }}

**Suggestion:** {{ $item['suggestion'] }}

@endforeach
@endif

@if(count($report['cv_suggestions'] ?? []) > 0)
## CV Content Suggestions

@foreach($report['cv_suggestions'] as $suggestion)
**{{ $suggestion['section'] }}**

{{ $suggestion['recommendation'] }}

_{{ $suggestion['rationale'] }}_

@endforeach
@endif
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
