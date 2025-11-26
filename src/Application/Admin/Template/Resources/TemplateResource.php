<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Resources;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Resource;
use Src\Domain\Template\Models\Template;
use Src\Domain\User\Enums\UserRole;

class TemplateResource extends Resource
{
    public function __construct(
        public int $id,
        public string $name,
        public string $created_at,
        public ?string $description = null,
        public ?string $google_drive_id = null,
        public ?string $google_drive_file_id = null,
        public ?string $web_view_link = null,
    ) {}

    public static function fromModel(Template $template): self
    {
        $user = Auth::user();
        $canViewLink = $user && $user->hasAnyRole(UserRole::canViewTemplateLinks());

        // Format date in human-readable format using app locale
        $locale = App::getLocale();
        $createdAt = \Illuminate\Support\Facades\Date::parse($template->created_at);
        $createdAt->locale($locale);

        // Use locale-specific format
        $formattedDate = match ($locale) {
            'es' => $createdAt->isoFormat('D [de] MMMM [de] YYYY [a las] HH:mm'),
            'en' => $createdAt->isoFormat('MMMM D, YYYY [at] HH:mm'),
            default => $createdAt->isoFormat('MMMM D, YYYY [at] HH:mm'),
        };

        return new self(
            id: $template->id,
            name: $template->name,
            created_at: $formattedDate,
            description: $template->description,
            google_drive_id: $template->google_drive_id,
            google_drive_file_id: $template->google_drive_file_id,
            web_view_link: $canViewLink ? $template->web_view_link : null,
        );
    }

    /**
     * Convert the resource to an array, excluding web_view_link if user doesn't have permission
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        // Remove web_view_link if it's null (user doesn't have permission)
        if ($array['web_view_link'] === null) {
            unset($array['web_view_link']);
        }

        return $array;
    }
}
