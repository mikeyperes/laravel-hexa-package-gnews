@extends('layouts.app')

@section('title', 'GNews Settings — ' . config('hws.app_name'))
@section('header', 'GNews Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('settings.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Settings</a>
    </div>

    {{-- Install Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-2">Setup Instructions</h2>
        <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
            <li>Go to <a href="https://gnews.io" target="_blank" class="underline font-medium">gnews.io</a> <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></li>
            <li>Create a free account (100 requests/day on free tier)</li>
            <li>Go to your Dashboard and copy your API Token</li>
            <li>Paste the token below and click Save</li>
            <li>Click Test to verify the key works</li>
        </ol>
    </div>

    {{-- API Key --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">API Key</h2>

        <div class="space-y-4">
            <div>
                <label for="gnews_api_key" class="block text-sm font-medium text-gray-700 mb-1">GNews API Token</label>
                <input type="text" id="gnews_api_key" name="api_key" value="{{ $apiKey }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                       placeholder="Enter your GNews API token">
            </div>

            <div class="flex items-center gap-3">
                <button id="btn-save-gnews-key" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                    Save Key
                </button>
                <button id="btn-test-gnews-key" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    Test Key
                </button>
            </div>

            <div id="gnews-settings-result" class="hidden"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Save API Key
    $('#btn-save-gnews-key').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.text();
        $btn.prop('disabled', true).html('<svg class="animate-spin h-4 w-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...');

        $.ajax({
            url: '{{ route("settings.gnews.save") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                api_key: $('#gnews_api_key').val()
            },
            success: function(data) {
                if (data.api_key) {
                    $('#gnews_api_key').val(data.api_key);
                }
                $('#gnews-settings-result').removeClass('hidden').html(
                    '<div class="p-3 rounded-lg text-sm ' + (data.success ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800') + '">' +
                    (data.success ? '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '') +
                    data.message + '</div>'
                );
            },
            error: function(xhr) {
                $('#gnews-settings-result').removeClass('hidden').html(
                    '<div class="p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-800">Error saving API key.</div>'
                );
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Test API Key
    $('#btn-test-gnews-key').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.text();
        $btn.prop('disabled', true).html('<svg class="animate-spin h-4 w-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...');

        $.ajax({
            url: '{{ route("settings.gnews.test") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                api_key: $('#gnews_api_key').val()
            },
            success: function(data) {
                $('#gnews-settings-result').removeClass('hidden').html(
                    '<div class="p-3 rounded-lg text-sm ' + (data.success ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800') + '">' +
                    (data.success ? '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '') +
                    data.message + '</div>'
                );
            },
            error: function(xhr) {
                $('#gnews-settings-result').removeClass('hidden').html(
                    '<div class="p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-800">Error testing API key.</div>'
                );
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
@endpush
@endsection
