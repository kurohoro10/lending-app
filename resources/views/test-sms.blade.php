<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SMS/WhatsApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Test SMS/WhatsApp</h1>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="/test-sms-send" class="space-y-4">
                @csrf

                <!-- Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message Type</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="whatsapp">WhatsApp (Sandbox)</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>

                <!-- Phone Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="text" name="phone"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                           placeholder="+639123456789"
                           value="+639123456789">
                    <p class="mt-1 text-xs text-gray-500">
                        For WhatsApp: Must be joined to sandbox first. For SMS: E.164 format (+639XXXXXXXXX)
                    </p>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea name="message" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                              placeholder="Your test message here...">Hello from LoanFlow CRM! This is a test message.</textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Send Test Message
                </button>
            </form>

            <!-- Instructions -->
            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2">WhatsApp Sandbox Setup:</h3>
                <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
                    <li>Go to Twilio Console → Messaging → Try it out → Send a WhatsApp message</li>
                    <li>Send <code class="bg-blue-100 px-1">join &lt;sandbox-code&gt;</code> to the Twilio WhatsApp number</li>
                    <li>Wait for confirmation message</li>
                    <li>Use your phone number above (must include country code with +)</li>
                </ol>
            </div>

            <!-- Debug Info -->
            <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h3 class="font-semibold text-gray-900 mb-2">Current Config:</h3>
                <div class="text-xs text-gray-600 space-y-1">
                    <p><strong>Twilio SID:</strong> {{ config('twilio.sid') ? '✓ Set' : '✗ Not set' }}</p>
                    <p><strong>Auth Token:</strong> {{ config('twilio.auth_token') ? '✓ Set' : '✗ Not set' }}</p>
                    <p><strong>WhatsApp From:</strong> {{ config('twilio.whatsapp_from') ?? 'Not set' }}</p>
                    <p><strong>SMS From:</strong> {{ config('twilio.sms_from') ?? 'Not set' }}</p>
                </div>
            </div>

            <!-- Check Communications -->
            <div class="mt-6">
                <a href="/admin/dashboard"
                   class="inline-block px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    View Communications Log →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
