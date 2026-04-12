<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    private function dummyNotifications(): array
    {
        return [
            [
                'id'         => 'notif-1',
                'type'       => 'success',
                'title'      => 'Role berhasil dibuat',
                'message'    => 'Role "Editor" telah berhasil ditambahkan ke sistem.',
                'read'       => false,
                'action_url' => null,
                'created_at' => now()->subMinutes(5)->toDateTimeString(),
            ],
            [
                'id'         => 'notif-2',
                'type'       => 'info',
                'title'      => 'Permission baru tersedia',
                'message'    => 'Terdapat 3 permission baru yang belum ditetapkan ke role manapun.',
                'read'       => false,
                'action_url' => null,
                'created_at' => now()->subHours(1)->toDateTimeString(),
            ],
            [
                'id'         => 'notif-3',
                'type'       => 'warning',
                'title'      => 'Sesi akan berakhir',
                'message'    => 'Sesi login Anda akan berakhir dalam 30 menit.',
                'read'       => true,
                'action_url' => null,
                'created_at' => now()->subHours(3)->toDateTimeString(),
            ],
            [
                'id'         => 'notif-4',
                'type'       => 'error',
                'title'      => 'Gagal sinkronisasi',
                'message'    => 'Gagal mensinkronisasi data menu. Coba lagi nanti.',
                'read'       => true,
                'action_url' => null,
                'created_at' => now()->subDays(1)->toDateTimeString(),
            ],
            [
                'id'         => 'notif-5',
                'type'       => 'message',
                'title'      => 'Pesan dari sistem',
                'message'    => 'Pembaruan sistem dijadwalkan pada Minggu pukul 02.00 WIB.',
                'read'       => true,
                'action_url' => null,
                'created_at' => now()->subDays(2)->toDateTimeString(),
            ],
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(50, (int) $request->query('per_page', 10)));

        $all   = $this->dummyNotifications();
        $total = count($all);
        $slice = array_slice($all, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'data' => $slice,
            'meta' => [
                'total'        => $total,
                'unread_count' => count(array_filter($all, fn ($n) => ! $n['read'])),
                'current_page' => $page,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $unread = count(array_filter($this->dummyNotifications(), fn ($n) => ! $n['read']));

        return response()->json(['count' => $unread]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }

    public function markAllAsRead(): JsonResponse
    {
        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }
}
