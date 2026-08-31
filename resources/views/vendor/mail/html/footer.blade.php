<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    {{ Illuminate\Mail\Markdown::parse($slot) }}

                    <hr style="border:none; border-top:1px solid #edeff2; margin: 20px 0;">
                    
                    <p style="font-size: 11px; color: #b0adc5;">
                        <strong>Security Audit Details:</strong><br>
                        Request IP: {{ request()->ip() ?? 'N/A' }}<br>
                        Sent at: {{ now()->toDayDateTimeString() }} (UTC)
                    </p>

                    {!! \App\Helpers\EmailSignature::html() !!}
                </td>
            </tr>
        </table>
    </td>
</tr>