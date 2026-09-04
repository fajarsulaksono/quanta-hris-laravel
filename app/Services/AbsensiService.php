<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AbsensiService
{
    /**
     * Get absensi data for multiple karyawan - BATCH OPERATION
     */
    public function getAbsensiDataBatch(Collection $karyawanIds, Carbon $periodeStart, Carbon $periodeEnd): array
    {
        $absensiStats = Absensi::whereIn('karyawan_id', $karyawanIds->toArray())
            ->whereBetween('tanggal', [$periodeStart->format('Y-m-d'), $periodeEnd->format('Y-m-d')])
            ->selectRaw('karyawan_id, status_absensi, COUNT(*) as count')
            ->groupBy(['karyawan_id', 'status_absensi'])
            ->get()
            ->groupBy('karyawan_id');

        $result = [];
        foreach ($karyawanIds as $karyawanId) {
            $stats = $absensiStats->get($karyawanId, collect())->keyBy('status_absensi');

            $result[$karyawanId] = [
                'total_hadir' => $stats->get('Hadir')?->count ?? 0,
                'total_alfa' => $stats->get('Alfa')?->count ?? 0,
                'total_tidak_tepat' => $stats->get('Tidak Tepat')?->count ?? 0,
                'total_cuti' => $stats->get('Cuti')?->count ?? 0,
                'total_izin' => $stats->get('Izin')?->count ?? 0,
                'total_absensi' => $stats->sum('count'),
            ];
        }

        return $result;
    }

    /**
     * Get lembur data for multiple karyawan - BATCH OPERATION
     */
    public function getLemburDataBatch(Collection $karyawanIds, Carbon $periodeStart, Carbon $periodeEnd): array
    {
        // Agregasi durasi dilakukan di PHP karena TIME_TO_SEC()/SEC_TO_TIME()
        // adalah fungsi khusus MySQL dan tidak tersedia di SQLite.
        $lemburRows = Lembur::whereIn('karyawan_id', $karyawanIds->toArray())
            ->whereBetween('tanggal_lembur', [$periodeStart->format('Y-m-d'), $periodeEnd->format('Y-m-d')])
            ->where('status_lembur', 'Disetujui')
            ->get(['karyawan_id', 'durasi_lembur', 'total_insentif'])
            ->groupBy('karyawan_id');

        $result = [];
        foreach ($karyawanIds as $karyawanId) {
            $rows = $lemburRows->get($karyawanId, collect());

            if ($rows->isNotEmpty()) {
                $totalSeconds = $rows->sum(fn ($lembur) => $this->timeToSeconds($lembur->durasi_lembur));
                $totalInsentif = $rows->sum(fn ($lembur) => intval($lembur->total_insentif ?? 0));

                $result[$karyawanId] = [
                    'total_lembur_hours' => round($totalSeconds / 3600, 1),
                    'total_lembur_sessions' => $rows->count(),
                    'total_lembur_insentif' => $totalInsentif,
                ];
            } else {
                $result[$karyawanId] = [
                    'total_lembur_hours' => 0.0,
                    'total_lembur_sessions' => 0,
                    'total_lembur_insentif' => 0,
                ];
            }
        }

        return $result;
    }

    /**
     * Convert a "H:i:s" duration string to total seconds.
     */
    private function timeToSeconds(string $time): int
    {
        $parts = array_map('intval', explode(':', $time));

        return ($parts[0] ?? 0) * 3600 + ($parts[1] ?? 0) * 60 + ($parts[2] ?? 0);
    }

    /**
     * Get absensi data for single karyawan
     */
    public function getAbsensiDataSingle(string $karyawanId, Carbon $periodeStart, Carbon $periodeEnd): array
    {
        return $this->getAbsensiDataBatch(collect([$karyawanId]), $periodeStart, $periodeEnd)[$karyawanId] ?? [
            'total_hadir' => 0,
            'total_alfa' => 0,
            'total_tidak_tepat' => 0,
            'total_cuti' => 0,
            'total_izin' => 0,
            'total_absensi' => 0
        ];
    }

    /**
     * Get lembur data for single karyawan
     */
    public function getLemburDataSingle(string $karyawanId, Carbon $periodeStart, Carbon $periodeEnd): array
    {
        return $this->getLemburDataBatch(collect([$karyawanId]), $periodeStart, $periodeEnd)[$karyawanId] ?? [
            'total_lembur_hours' => 0.0,
            'total_lembur_sessions' => 0,
            'total_lembur_insentif' => 0,
        ];
    }

    /**
     * Get combined attendance and overtime data for multiple karyawan
     */
    public function getCombinedDataBatch(Collection $karyawanIds, Carbon $periodeStart, Carbon $periodeEnd): array
    {
        $absensiData = $this->getAbsensiDataBatch($karyawanIds, $periodeStart, $periodeEnd);
        $lemburData = $this->getLemburDataBatch($karyawanIds, $periodeStart, $periodeEnd);

        $result = [];
        foreach ($karyawanIds as $karyawanId) {
            $result[$karyawanId] = array_merge(
                $absensiData[$karyawanId] ?? [
                    'total_hadir' => 0,
                    'total_alfa' => 0,
                    'total_tidak_tepat' => 0,
                    'total_cuti' => 0,
                    'total_izin' => 0,
                    'total_absensi' => 0
                ],
                $lemburData[$karyawanId] ?? [
                    'total_lembur_hours' => 0.0,
                    'total_lembur_sessions' => 0,
                    'total_lembur_insentif' => 0,
                ]
            );
        }

        return $result;
    }

    /**
     * Get combined attendance and overtime data for single karyawan
     */
    public function getCombinedDataSingle(string $karyawanId, Carbon $periodeStart, Carbon $periodeEnd): array
    {
        return $this->getCombinedDataBatch(collect([$karyawanId]), $periodeStart, $periodeEnd)[$karyawanId] ?? [];
    }
}