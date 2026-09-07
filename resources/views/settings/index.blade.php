@extends('layouts.app')

@section('title', 'GNews Settings — ' . config('hws.app_name'))
@section('header', 'GNews Settings')

@section('content')
<div id="gnews-settings"
     data-has-key="{{ $apiKey ? '1' : '0' }}"
     data-save-url="{{ route('settings.gnews.save') }}"
     data-test-url="{{ route('settings.gnews.test') }}"
     class="max-w-4xl mx-auto">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">GNews API Token</label>

                {{-- Masked display (when key exists and not editing) --}}
                <div id="gnews-masked" class="{{ $apiKey ? '' : 'hidden' }}">
                    <input type="password" value="{{ $apiKey }}" disabled
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 mb-2">
                </div>

                {{-- No key message --}}
                <div id="gnews-no-key" class="{{ $apiKey ? 'hidden' : '' }}">
                    <p class="text-xs text-gray-400 italic mb-2">No API key configured.</p>
                </div>

                {{-- Edit field (hidden by default) --}}
                <div id="gnews-edit" class="hidden">
                    <input type="text" id="gnews-api-key-input"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono mb-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Paste your GNews API token">
                </div>
            </div>

            {{-- Buttons: Change / Save / Cancel / Test --}}
            <div class="flex items-center gap-3">
                <button id="btn-gnews-change" class="{{ $apiKey ? '' : 'hidden' }} px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Change API Key
                </button>
                <button id="btn-gnews-set" class="{{ $apiKey ? 'hidden' : '' }} px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Set API Key
                </button>
                <button id="btn-gnews-save" class="hidden px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 inline-flex items-center gap-2">
                    <svg id="spinner-gnews-save" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span id="btn-text-gnews-save">Save</span>
                </button>
                <button id="btn-gnews-cancel" class="hidden text-sm text-gray-500 hover:text-gray-700 px-4 py-2">Cancel</button>
                <button id="btn-gnews-test" class="{{ $apiKey ? '' : 'hidden' }} px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 inline-flex items-center gap-2">
                    <svg id="spinner-gnews-test" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span id="btn-text-gnews-test">Test Key</span>
                </button>
            </div>

            {{-- Result banner --}}
            <div id="gnews-result" class="hidden"></div>
        </div>
    </div>
</div>

@push('scripts')
<x-hexa-package-script package="gnews" :version="config('gnews.version')" asset="settings.js" />
@endpush
@endsection
