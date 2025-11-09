@extends('emails.layouts.email')

@php
    use Illuminate\Support\Facades\Lang;
@endphp

@section('title', __('step_up.email_subject'))

@push('styles')
<style>
    .title-section {
        margin-bottom: 24px;
        text-align: center;
    }
    .title {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 16px;
        line-height: 1.2;
    }
    .body-text {
        font-size: 16px;
        color: #4a4a4a;
        line-height: 1.7;
        margin-bottom: 32px;
        text-align: left;
    }
    .code-section {
        background-color: #f8f8f8;
        border-radius: 12px;
        padding: 32px 24px;
        margin: 32px 0;
        text-align: center;
    }
    .code-label {
        font-size: 12px;
        color: #666666;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 600;
    }
    .code {
        font-size: 40px;
        font-weight: 700;
        color: #2caeff;
        letter-spacing: 12px;
        font-family: 'Courier New', monospace;
        line-height: 1.2;
    }
    .info-text {
        font-size: 14px;
        color: #666666;
        line-height: 1.6;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e5e5e5;
    }
    .warning-text {
        font-size: 14px;
        color: #d97706;
        line-height: 1.6;
        margin-top: 24px;
        padding: 16px;
        background-color: #fef3c7;
        border-radius: 8px;
        border-left: 4px solid #d97706;
    }
    @media only screen and (max-width: 600px) {
        .title {
            font-size: 24px;
        }
        .code {
            font-size: 32px;
            letter-spacing: 8px;
        }
        .code-section {
            padding: 24px 20px;
        }
    }
</style>
@endpush

@section('content')
    <div class="title-section">
        <h1 class="title">{{ __('step_up.email_subject') }}</h1>
    </div>

    <div class="body-text">
        {{ __('step_up.email_line_1', ['action' => Lang::get('step_up.actions')[$action] ?? $action]) }}
    </div>

    <div class="code-section">
        <div class="code-label">{{ __('data.code') }}</div>
        <div class="code">{{ $code }}</div>
    </div>

    <div class="info-text">
        {{ __('step_up.email_line_2', ['minutes' => $validityMinutes]) }}
    </div>

    <div class="warning-text">
        {{ __('step_up.email_warning') }}
    </div>
@endsection

