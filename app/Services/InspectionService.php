<?php

namespace App\Services;

use App\Enums\InspectionStatus;
use App\Models\Client;
use App\Models\Contract;
use App\Models\SiteInspection;
use Illuminate\Validation\ValidationException;

class InspectionService
{
    /**
     * Normalise validated inspection input, including the fixed condition checklist and findings rows.
     */
    public function normalise(array $data): array
    {
        $client = Client::findOrFail($data['client_id']);

        if (filled($data['contract_id'] ?? null)) {
            $contract = Contract::find($data['contract_id']);

            if (! $contract || (int) $contract->client_id !== (int) $client->id) {
                throw ValidationException::withMessages([
                    'contract_id' => 'The selected contract does not belong to this client.',
                ]);
            }
        }

        $conditions = [];
        foreach (SiteInspection::CONDITION_LABELS as $index => $label) {
            $row = $data['conditions'][$index] ?? [];
            $status = ($row['status'] ?? '') === 'yes' ? 'yes' : '';
            $area = trim((string) ($row['area'] ?? ''));

            if ($status !== '' || $area !== '') {
                $conditions[] = ['label' => $label, 'status' => $status, 'area' => $area];
            }
        }

        $findings = [];
        foreach ($data['findings'] ?? [] as $row) {
            $finding = [
                'area' => trim((string) ($row['area'] ?? '')),
                'findings' => trim((string) ($row['findings'] ?? '')),
                'action_taken' => trim((string) ($row['action_taken'] ?? '')),
                'remarks' => trim((string) ($row['remarks'] ?? '')),
            ];

            if (implode('', $finding) !== '') {
                $findings[] = $finding;
            }
        }

        $status = $data['inspection_status'];

        return [
            'client_id' => $client->id,
            'contract_id' => $data['contract_id'] ?? null,
            'technician_id' => $data['technician_id'] ?? null,
            'client_name' => $client->client_name,
            'client_email' => $data['client_email'] ?? $client->email,
            'service_address' => $client->address,
            'account_type' => $data['account_type'] ?? $client->client_type->value,
            'inspection_status' => $status,
            'inspection_date' => $data['inspection_date'] ?? null,
            'time_in' => $this->time($data['time_in'] ?? null),
            'time_out' => $this->time($data['time_out'] ?? null),
            'ilt_qty' => (int) ($data['ilt_qty'] ?? 0),
            'ilt_remarks' => $data['ilt_remarks'] ?? null,
            'rat_cage_qty' => (int) ($data['rat_cage_qty'] ?? 0),
            'rat_cage_remarks' => $data['rat_cage_remarks'] ?? null,
            'rat_bait_qty' => (int) ($data['rat_bait_qty'] ?? 0),
            'rat_bait_remarks' => $data['rat_bait_remarks'] ?? null,
            'glue_trap_qty' => (int) ($data['glue_trap_qty'] ?? 0),
            'glue_trap_remarks' => $data['glue_trap_remarks'] ?? null,
            'vermex_representative' => $data['vermex_representative'] ?? null,
            'client_representative' => $data['client_representative'] ?? null,
            'conditions_json' => $conditions,
            'findings_json' => $findings,
            'pests_found' => $status === InspectionStatus::PestsFound->value,
        ];
    }

    private function time(?string $value): ?string
    {
        return $value ? (strlen($value) === 5 ? $value . ':00' : $value) : null;
    }
}
