<?php declare(strict_types=1);

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class CheckMeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'patronymic' => $this->getPatronymic(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
            'email_verified_at' => $this->getEmailVerifiedAt()?->format(
                format: 'Y-m-d'
            ),
            'status' => $this->getStatus(),
            'datetime' => [
                'created_at' => $this->getCreatedAt()->format(
                    format: 'Y-m-d H:i:s'
                ),
                'updated_at' => $this->getUpdatedAt()->format(
                    format: 'Y-m-d H:i:s'
                ),
            ],
        ];
    }
}
