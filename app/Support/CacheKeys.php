<?php

namespace App\Support;

final class CacheKeys
{
    // TTL constants (seconds)
    public const TTL_SHORT = 300;    //  5 minutes
    public const TTL_MEDIUM = 1800;   // 30 minutes
    public const TTL_LONG = 86400;  // 24 hours

    // ── Tags ──────────────────────────────────────────────────────

    public static function profileTag(int $profileId): string
    {
        return "profile:{$profileId}";
    }

    public static function profilesTag(): string
    {
        return 'profiles';
    }

    public static function skillsTag(): string
    {
        return 'skills';
    }

    // ── Keys ──────────────────────────────────────────────────────
    public static function profileByUser(int $userId): string
    {
        return "profile:user:{$userId}";
    }

    public static function profileFirst(): string
    {
        return 'profile:first';
    }

    public static function profileFirstWithRelations(): string
    {
        return 'profile:first:with-relations';
    }

    public static function experiences(int $profileId): string
    {
        return "profile:{$profileId}:experiences";
    }

    public static function educations(int $profileId): string
    {
        return "profile:{$profileId}:educations";
    }

    public static function certificates(int $profileId): string
    {
        return "profile:{$profileId}:certificates";
    }

    public static function services(int $profileId): string
    {
        return "profile:{$profileId}:services";
    }

    public static function skillsAll(): string
    {
        return 'skills:all';
    }

    public static function skillsPaginated(int $page, array $filters): string
    {
        return 'skills:page:' . md5(json_encode(array_merge(['page' => $page], $filters)));
    }
}
