<table class="header">
    <tr>
        <td>
            @if (!empty($logo))
                <img src="{{ $logo }}" alt="Logo" class="logo">
            @endif
            <div><strong>{{ $organisation }}</strong></div>
        </td>
        <td class="right muted">Imprimé le {{ $imprimeLe }}</td>
    </tr>
</table>
