<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Docs MIME Types
    |--------------------------------------------------------------------------
    |
    | These are the MIME types that represent Google Docs and Microsoft Word
    | documents in Google Drive. These are used to filter files when syncing
    | templates and processing documents.
    |
    */

    'mime_types' => [
        // Google Docs (native format)
        'google_docs' => 'application/vnd.google-apps.document',

        // Microsoft Word formats
        'word_docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'word_doc' => 'application/msword',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Document Types for Templates
    |--------------------------------------------------------------------------
    |
    | These are all MIME types that are considered valid template formats.
    | Used when listing and syncing templates from Google Drive.
    |
    */

    'template_mime_types' => [
        'application/vnd.google-apps.document',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
    ],

    /*
    |--------------------------------------------------------------------------
    | Word Document Types
    |--------------------------------------------------------------------------
    |
    | MIME types that represent Word documents (not Google Docs).
    | Used to determine if a file needs conversion to Google Docs format.
    |
    */

    'word_mime_types' => [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
    ],
];

