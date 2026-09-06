<?php

namespace App\Services;

use App\Enums\ClientStatut;
use App\Enums\OperationSens;
use App\Models\Client;
use App\Models\Operation;
use App\Models\TypeOperation;
use App\Models\User;
use App\Support\ImportReport;
use App\Support\XlsxReader;
use Illuminate\Support\Facades\DB;

class ExcelImportService
{
    public function __construct(
        private readonly XlsxReader $reader,
        private readonly PortefeuilleService $portefeuille,
    ) {}

    public function importTypeOperations(bool $execute = false): ImportReport
    {
        $report = new ImportReport;
        $rows = $this->reader->rows(base_path('BD/TYPE OPERATION.xlsx'));
        $report->lignes = count($rows);

        foreach ($rows as $index => $row) {
            $ligne = $index + 2;
            $id = (int) ($row['IDtype_op'] ?? 0);
            $libelle = trim((string) ($row['type'] ?? ''));
            $sens = (int) ($row['sens'] ?? 0);

            if ($id < 1 || $libelle === '' || ! in_array($sens, [1, 2], true)) {
                $report->addErreur("Ligne {$ligne} : type d’opération invalide.");
                continue;
            }

            $code = $sens === OperationSens::Credit->value
                ? TypeOperation::CODE_CREDIT
                : TypeOperation::CODE_REMBOURSEMENT;

            if (TypeOperation::query()->whereKey($id)->exists()) {
                $report->doublons++;
                $report->ignorees++;
                $report->addAvertissement("Ligne {$ligne} : IDtype_op {$id} déjà présent.");
                continue;
            }

            if ($execute) {
                TypeOperation::query()->create([
                    'id' => $id,
                    'numero_enr' => $row['N° Enr.'] ?? null,
                    'code' => $code,
                    'libelle' => $libelle,
                    'sens' => $sens,
                    'actif' => true,
                ]);
            }

            $report->importees++;
        }

        return $report;
    }

    public function importClients(bool $execute = false): ImportReport
    {
        $report = new ImportReport;
        $rows = $this->reader->rows(base_path('BD/CLIENTS.xlsx'));
        $report->lignes = count($rows);
        $codes = [];

        foreach ($rows as $index => $row) {
            $ligne = $index + 2;
            $id = (int) ($row['IDclient'] ?? 0);
            $code = trim((string) ($row['code'] ?? ''));
            $nom = trim((string) ($row['Nom'] ?? ''));
            $prenom = trim((string) ($row['Prénom'] ?? ''));

            if ($id < 1 || $code === '' || $nom === '') {
                $report->addErreur("Ligne {$ligne} : IDclient, code ou nom manquant.");
                continue;
            }

            if (isset($codes[$code])) {
                $report->doublons++;
                $report->addErreur("Ligne {$ligne} : code client {$code} en double dans le fichier.");
                continue;
            }
            $codes[$code] = true;

            if (Client::query()->where(fn ($q) => $q->whereKey($id)->orWhere('code_client', $code))->exists()) {
                $report->doublons++;
                $report->ignorees++;
                $report->addAvertissement("Ligne {$ligne} : client {$code} déjà présent.");
                continue;
            }

            $telephone = $this->cleanTelephone($row['Téléphone_France'] ?? null);

            if ($execute) {
                $client = new Client([
                    'numero_enr' => $row['N° Enr.'] ?? null,
                    'code_client' => $code,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'telephone' => $telephone,
                    'adresse' => trim((string) ($row['Adresse'] ?? '')) ?: null,
                    'solde' => $this->portefeuille->normalizeMontant($row['solde'] ?? 0),
                    'statut' => ((int) ($row['statut'] ?? 1)) === 1
                        ? ClientStatut::Actif
                        : ClientStatut::Inactif,
                ]);
                $client->id = $id;
                $client->save();
            }

            $report->importees++;
        }

        return $report;
    }

    public function importOperations(bool $execute = false): ImportReport
    {
        $report = new ImportReport;
        $rows = $this->reader->rows(base_path('BD/OPERATIONS.xlsx'));
        $report->lignes = count($rows);
        $userId = User::query()->value('id') ?? 1;
        $typeIds = TypeOperation::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $clientIds = Client::query()->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($rows as $index => $row) {
            $ligne = $index + 2;
            $id = (int) ($row['IDoperation'] ?? 0);
            $clientId = (int) ($row['IDclient'] ?? 0);
            $typeId = (int) ($row['type'] ?? 0);
            $montant = $row['montant'] ?? null;

            if ($clientId === 0) {
                $report->addErreur("Ligne {$ligne} : IDoperation {$id} ignorée (IDclient = 0, client inexistant).");
                continue;
            }

            if ($id < 1 || $typeId < 1 || $montant === null || (float) $montant <= 0) {
                $report->addErreur("Ligne {$ligne} : opération invalide (id, type ou montant).");
                continue;
            }

            if (! in_array($typeId, $typeIds, true)) {
                $report->addErreur("Ligne {$ligne} : type {$typeId} inexistant.");
                continue;
            }

            if (! in_array($clientId, $clientIds, true) && $execute) {
                $report->addErreur("Ligne {$ligne} : client {$clientId} inexistant.");
                continue;
            }

            if (! $execute && $clientId > 0 && ! in_array($clientId, $clientIds, true)) {
                $report->addAvertissement("Ligne {$ligne} : client {$clientId} absent (importer les clients d’abord).");
            }

            if (Operation::query()->where(fn ($q) => $q->whereKey($id)->orWhere('reference', $this->legacyReference($id)))->exists()) {
                $report->doublons++;
                $report->ignorees++;
                continue;
            }

            $date = $this->reader->excelDate($row['date']) ?? now()->toDateString();
            $heure = $this->reader->excelTime($row['heure']);
            $type = TypeOperation::query()->find($typeId);
            $augmente = $type?->incrementeEncours() ?? ($typeId === 1);
            $montantNorm = $this->portefeuille->normalizeMontant($montant);

            if ($execute) {
                $operation = new Operation([
                    'numero_enr' => $row['N° Enr.'] ?? null,
                    'client_id' => $clientId,
                    'type_operation_id' => $typeId,
                    'user_id' => (int) ($row['IDutilisateur'] ?? $userId),
                    'reference' => $this->legacyReference($id),
                    'date_operation' => $date,
                    'heure_operation' => $heure,
                    'montant' => $montantNorm,
                    'solde_avant' => $this->portefeuille->normalizeMontant($row['mt_av'] ?? 0),
                    'solde_apres' => $this->portefeuille->normalizeMontant($row['mt_apres'] ?? 0),
                    'observation' => $this->cleanText($row['observation'] ?? null),
                    'entrees' => $this->portefeuille->normalizeMontant($row['entrees'] ?? ($augmente ? $montant : 0)),
                    'sorties' => $this->portefeuille->normalizeMontant($row['sorties'] ?? ($augmente ? 0 : $montant)),
                    'mode_paiement' => $this->detectModePaiement($row['observation'] ?? null),
                    'est_annulee' => false,
                ]);
                $operation->id = $id;
                $operation->save();
            }

            $report->importees++;
        }

        if ($execute) {
            $this->creerSoldesOuverture($report);
            $this->attribuerReferencesHistoriques();
            $this->portefeuille->recalculerTousLesSoldes(true);
            $this->resetAutoIncrement();
        }

        return $report;
    }

    private function creerSoldesOuverture(ImportReport $report): void
    {
        $typeCredit = TypeOperation::credit();
        $userId = User::query()->value('id') ?? 1;

        if (! $typeCredit) {
            return;
        }

        $clients = Client::query()->whereHas('operations')->get();

        foreach ($clients as $client) {
            $premiere = $client->operations()->orderBy('date_operation')->orderBy('heure_operation')->orderBy('id')->first();
            if (! $premiere || bccomp((string) $premiere->solde_avant, '0', 2) === 0) {
                continue;
            }

            $ouverture = $this->portefeuille->normalizeMontant($premiere->solde_avant);
            $date = $premiere->date_operation?->toDateString() ?? now()->toDateString();

            Operation::query()->create([
                'client_id' => $client->id,
                'type_operation_id' => $typeCredit->id,
                'user_id' => $userId,
                'reference' => 'CR-OUV-'.$client->id,
                'date_operation' => $date,
                'heure_operation' => '00:00:00',
                'montant' => $ouverture,
                'solde_avant' => '0.00',
                'solde_apres' => $ouverture,
                'observation' => 'Solde d’ouverture — crédit absent de l’export Excel',
                'entrees' => $ouverture,
                'sorties' => '0.00',
                'est_annulee' => false,
            ]);

            $report->addAvertissement("Solde d’ouverture {$ouverture} FCFA créé pour {$client->code_client}.");
            $report->importees++;
        }
    }

    private function attribuerReferencesHistoriques(): void
    {
        $operations = Operation::query()->orderBy('date_operation')->orderBy('heure_operation')->orderBy('id')->get();
        $compteurs = [];

        foreach ($operations as $operation) {
            if (str_starts_with((string) $operation->reference, 'CR-OUV-')) {
                continue;
            }

            $type = $operation->typeOperation;
            if (! $type) {
                continue;
            }

            $prefix = $type->incrementeEncours() ? 'CR' : 'RB';
            $datePart = $operation->date_operation?->format('Ymd') ?? '00000000';
            $cle = $prefix.'-'.$datePart;
            $compteurs[$cle] = ($compteurs[$cle] ?? 0) + 1;
            $operation->update([
                'reference' => $cle.'-'.str_pad((string) $compteurs[$cle], 6, '0', STR_PAD_LEFT),
            ]);
        }
    }

    private function legacyReference(int $id): string
    {
        return 'IMP-'.$id;
    }

    private function cleanTelephone(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $tel = trim((string) $value);

        return $tel === '' ? null : $tel;
    }

    private function cleanText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function resetAutoIncrement(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $clients = (int) Client::query()->max('id') + 1;
        $operations = (int) Operation::query()->max('id') + 1;
        DB::statement("ALTER TABLE clients AUTO_INCREMENT = {$clients}");
        DB::statement("ALTER TABLE operations AUTO_INCREMENT = {$operations}");
    }

    private function detectModePaiement(mixed $observation): ?string
    {
        if (! is_string($observation) || $observation === '') {
            return null;
        }

        return str_contains(mb_strtolower($observation), 'cheque')
            || str_contains(mb_strtolower($observation), 'chèque')
            ? 'Chèque'
            : null;
    }
}
