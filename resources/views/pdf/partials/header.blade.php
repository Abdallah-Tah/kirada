<div class="pdf-top-band"></div>
<table class="pdf-header">
    <tr>
        <td>
            @if (is_string($pdfLogoPath ?? null) && is_file($pdfLogoPath))
                <img src="{{ $pdfLogoPath }}" class="pdf-logo" alt="Kirada">
            @endif
        </td>
        <td class="pdf-meta">
            <div>
                <span class="pdf-meta-icon"></span>
                <span class="pdf-meta-reference">{{ $pdfReference }}</span>
            </div>
            @if (!empty($pdfDocumentDate))
                <div>{{ $pdfDocumentDate }}</div>
            @endif
        </td>
    </tr>
</table>
