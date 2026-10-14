<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agreement {{ $supplier->code }}</title>
    @include('procurement.templates.pdf.purchase-order-styles')
    <style>
        .note { margin-bottom: 6px; padding: 4px 8px; border-left: 3px solid #999; }
        .note-title { font-weight: bold; }
        .note-meta { font-size: 7.5pt; color: #666; }
    </style>
</head>
<body>
<h1 style="font-size: 14pt; margin: 0;">{{ $supplier->name }} ({{ $supplier->code }})</h1>
<div class="label">What was agreed · {{ $organisation?->name ?? 'Every organisation' }} · {{ now()->format('d M Y') }}</div>

<div class="terms">
    <div class="label">Agreed terms</div>
    @if($terms->isEmpty())
        No terms recorded.
    @else
        <table class="agreed-terms">
            @foreach($terms as $term)
                <tr>
                    <td class="agreed-term">{{ $term->term_type->label() }}{{ $term->product_code ? ' ('.$term->product_code.')' : '' }}</td>
                    <td>{{ $term->valueLabel() }}{{ $term->level === 'agent' ? ' (agreed with the agent)' : '' }}</td>
                    <td class="right">{{ $term->agreed_on->format('d M Y') }}{{ $term->agreedBy ? ' · '.$term->agreedBy->contact_name : '' }}{{ $term->agreed_with ? ' with '.$term->agreed_with : '' }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>

<div class="terms">
    <div class="label">Notes</div>
    @forelse($notes as $note)
        <div class="note">
            <div class="note-title">{{ $note->title ?? Str::limit($note->body, 120) }}</div>
            @if($note->title && $note->body)
                <div>{!! nl2br(e($note->body)) !!}</div>
            @endif
            <div class="note-meta">
                {{ $note->category->label() }} · {{ $note->severity->label() }}{{ $note->is_pinned ? ' · Pinned' : '' }}
                @if($note->subject_type === 'SupplierProduct') · {{ $note->subject?->code }} @endif
                @if($note->incident_outcome) · {{ $note->incident_outcome->label() }}{{ $note->incident_amount ? ' '.$note->incidentCurrency?->code.' '.number_format((float) $note->incident_amount, 2) : '' }}{{ $note->purchaseOrder ? ' on '.$note->purchaseOrder->reference : '' }} @endif
                · {{ $note->author?->contact_name ?? $note->author_name ?? '-' }}, {{ $note->created_at?->format('d M Y') }}
            </div>
        </div>
    @empty
        No open notes.
    @endforelse
</div>
</body>
</html>
