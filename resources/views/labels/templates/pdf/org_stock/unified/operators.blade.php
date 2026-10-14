@foreach ($label['operators'] as $operator)
    <tr>
        <td colspan="{{ $columns }}" style="font-size: {{ $size }}pt; line-height: 1.25;@if ($loop->first) border-top: 0.2mm solid #000; padding-top: 0.6mm;@endif">
            @if ($withRole)<b>{{ $operator['code'] }}&nbsp;&nbsp;{{ $operator['role'] }}</b>@else{{ $operator['code'] }}@endif {{ $operator['name'] }}, {{ $operator['address'] }}
        </td>
    </tr>
@endforeach
