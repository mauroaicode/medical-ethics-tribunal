<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Drive Folder Names
    |--------------------------------------------------------------------------
    |
    | These values are the names of the Google Drive folders:
    | - google_drive_folder_name: Folder where templates are stored and synced from
    | - destination_folder_name: Folder where processed documents (processes) are saved
    |
    */

    'google_drive_folder_name' => env('TEMPLATE_DRIVE_FOLDER_NAME'),
    'destination_folder_name' => env('TEMPLATE_DESTINATION_FOLDER_NAME', 'PROCESOS'),
];

