<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Response — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-lg">
        <h1 class="text-xl font-bold text-gray-900 mb-2">{{ $task->title }}</h1>
        @if($task->description)
            <p class="text-gray-600 text-sm mb-6">{{ $task->description }}</p>
        @endif

        <form method="POST" action="{{ route('tasks.respond.store', $task) }}?token={{ request('token') }}">
            @csrf
            <div class="mb-4">
                <label for="response" class="block text-sm font-medium text-gray-700 mb-1">Your Response</label>
                <textarea id="response" name="response" rows="5" required
                          class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Type your response here…"></textarea>
                @error('response')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="w-full py-3 bg-indigo-600 text-white rounded-xl font-semibold text-sm hover:bg-indigo-700 transition">
                Submit Response
            </button>
        </form>
    </div>
</body>
</html>