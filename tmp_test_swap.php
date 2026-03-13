<?php

use App\Models\AgendaReserva;
use Illuminate\Support\Facades\DB;

// Simulate the swap logic
function testSwap($idA, $idB) {
    try {
        $reservaA = AgendaReserva::findOrFail($idA);
        $reservaB = AgendaReserva::findOrFail($idB);

        $acoA = $reservaA->colonia_acomodacao_id;
        $acoB = $reservaB->colonia_acomodacao_id;

        echo "Before: A in $acoA, B in $acoB\n";

        DB::transaction(function() use ($reservaA, $reservaB, $acoA, $acoB) {
            $reservaA->update(['colonia_acomodacao_id' => null]);
            $reservaB->update(['colonia_acomodacao_id' => $acoA]);
            $reservaA->update(['colonia_acomodacao_id' => $acoB]);
        });

        $reservaA->refresh();
        $reservaB->refresh();

        echo "After: A in {$reservaA->colonia_acomodacao_id}, B in {$reservaB->colonia_acomodacao_id}\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
