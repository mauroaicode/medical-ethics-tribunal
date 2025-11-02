@extends('emails.layouts.email')

@section('title', __('auth.account_created_subject'))

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
    .credentials-section {
        background-color: #f8f8f8;
        border-radius: 12px;
        padding: 32px 24px;
        margin: 32px 0;
    }
    .credential-row {
        margin-bottom: 20px;
    }
    .credential-row:last-child {
        margin-bottom: 0;
    }
    .credential-label {
        font-size: 12px;
        color: #666666;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 600;
    }
    .credential-value {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        font-family: 'Courier New', monospace;
        word-break: break-all;
    }
    .password-value {
        font-size: 24px;
        font-weight: 700;
        color: #2caeff;
        letter-spacing: 4px;
        font-family: 'Courier New', monospace;
        line-height: 1.2;
    }
    .warning-section {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 20px;
        margin: 32px 0;
        border-radius: 8px;
    }
    .warning-text {
        font-size: 15px;
        color: #856404;
        line-height: 1.6;
        font-weight: 500;
    }
    .info-text {
        font-size: 14px;
        color: #666666;
        line-height: 1.6;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e5e5e5;
    }
    @media only screen and (max-width: 600px) {
        .title {
            font-size: 24px;
        }
        .password-value {
            font-size: 20px;
            letter-spacing: 2px;
        }
        .credentials-section {
            padding: 24px 20px;
        }
    }
</style>
@endpush

@section('content')
    <div class="title-section">
        <h1 class="title">{{ __('auth.account_created_subject') }}</h1>
    </div>

    <div class="body-text">
        {{ __('auth.account_created_line_1') }}
    </div>

    <div class="credentials-section">
        <div class="credential-row">
            <div class="credential-label">{{ __('data.email') }}</div>
            <div class="credential-value">{{ $email }}</div>
        </div>
        <div class="credential-row">
            <div class="credential-label">{{ __('data.password') }}</div>
            <div class="password-value">{{ $temporaryPassword }}</div>
        </div>
    </div>

    <div class="warning-section">
        <div class="warning-text">
            {{ __('auth.account_created_warning') }}
        </div>
    </div>

    <div class="info-text">
        {{ __('auth.account_created_line_2') }}
    </div>
@endsection

