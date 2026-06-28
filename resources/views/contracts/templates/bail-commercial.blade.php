@php
    /**
     * French "bail commercial" (commercial lease) body.
     * Receives a single $v array of variables. Missing values fall back to a
     * dotted fill-in line so the printed contract stays legally legible.
     */
    $fill = '……………………………………';
    $show = fn ($key, $fallback = null) => filled($v[$key] ?? null) ? $v[$key] : ($fallback ?? $fill);
    $money = function ($key) use ($v, $fill) {
        $amount = $v[$key] ?? null;
        if (! is_numeric($amount)) {
            return $fill;
        }
        return number_format((float) $amount, 0, ',', ' ').' '.($v['currency'] ?? 'DJF');
    };
    $date = function ($key) use ($v, $fill) {
        $raw = $v[$key] ?? null;
        if (blank($raw)) {
            return $fill;
        }
        try {
            return \Illuminate\Support\Carbon::parse($raw)->format('d/m/Y');
        } catch (\Throwable) {
            return $raw;
        }
    };
@endphp

<h1 class="contract-title">CONTRAT DE BAIL COMMERCIAL</h1>
<p class="contract-subtitle">Soumis aux dispositions applicables aux baux commerciaux (statut des baux commerciaux — 3/6/9)</p>

<h2>Entre les soussignés</h2>

<p>
    <strong>LE BAILLEUR :</strong> {{ $show('bailleur_name') }},
    demeurant à {{ $show('bailleur_address') }}@if(filled($v['bailleur_email'] ?? null)) (courriel : {{ $v['bailleur_email'] }})@endif,
    ci-après dénommé « <strong>le Bailleur</strong> », d'une part,
</p>

<p>
    <strong>ET LE PRENEUR :</strong> {{ $show('preneur_name') }},
    demeurant à {{ $show('preneur_address') }}@if(filled($v['preneur_id'] ?? null)), titulaire de la pièce d'identité n° {{ $v['preneur_id'] }}@endif@if(filled($v['preneur_email'] ?? null)) (courriel : {{ $v['preneur_email'] }})@endif,
    ci-après dénommé « <strong>le Preneur</strong> », d'autre part.
</p>

<p>Il a été convenu et arrêté ce qui suit :</p>

<h2>Article 1 — Désignation des locaux</h2>
<p>
    Le Bailleur donne à bail commercial au Preneur, qui accepte, les locaux ci-après désignés :
    {{ $show('premises_designation') }}, sis à {{ $show('premises_address') }}@if(filled($v['premises_area'] ?? null)), d'une superficie d'environ {{ $v['premises_area'] }} m²@endif.
</p>

<h2>Article 2 — Destination des lieux</h2>
<p>
    Les locaux sont loués en vue de l'exercice de l'activité suivante : <strong>{{ $show('destination') }}</strong>.
    Le Preneur ne pourra exercer dans les lieux loués aucune autre activité sans l'accord préalable et écrit du Bailleur.
</p>

<h2>Article 3 — Durée</h2>
<p>
    Le présent bail est consenti pour une durée de <strong>{{ $show('duration_years', 9) }} années</strong> entières et consécutives,
    qui commencera à courir le <strong>{{ $date('start_date') }}</strong>@if(filled($v['end_date'] ?? null)) pour se terminer le <strong>{{ $date('end_date') }}</strong>@endif.
    Le Preneur aura la faculté de donner congé à l'expiration de chaque période triennale, dans les formes et délais prévus par la loi.
</p>

<h2>Article 4 — Loyer</h2>
<p>
    Le présent bail est consenti et accepté moyennant un loyer annuel de <strong>{{ $money('annual_rent') }}</strong>,
    payable par fractions {{ $show('payment_terms') }}, soit un loyer de <strong>{{ $money('monthly_rent') }}</strong> par mois.
</p>

<h2>Article 5 — Révision du loyer</h2>
<p>
    Le loyer sera révisé de plein droit chaque année à la date anniversaire de la prise d'effet du bail,
    en fonction de la variation de l'{{ $show('index_ref', "Indice des Loyers Commerciaux (ILC)") }}.
</p>

<h2>Article 6 — Charges et conditions accessoires</h2>
<p>
    Outre le loyer principal, le Preneur remboursera au Bailleur sa quote-part des charges, taxes et prestations :
    {{ $show('charges', "selon répartition légale et conditions ci-après convenues") }}.
</p>

<h2>Article 7 — Dépôt de garantie</h2>
<p>
    À la signature des présentes, le Preneur verse au Bailleur la somme de <strong>{{ $money('deposit') }}</strong>
    à titre de dépôt de garantie, qui lui sera restituée en fin de bail déduction faite des sommes éventuellement dues.
</p>

<h2>Article 8 — Obligations du Preneur</h2>
<p>
    Le Preneur s'oblige à jouir des lieux en bon père de famille, à les entretenir, à souscrire les assurances requises,
    à payer le loyer et les charges aux échéances convenues, et à se conformer à la réglementation applicable à son activité.
</p>

<h2>Article 9 — Obligations du Bailleur</h2>
<p>
    Le Bailleur s'oblige à délivrer les locaux en bon état, à en garantir la jouissance paisible,
    et à effectuer les grosses réparations qui lui incombent.
</p>

<h2>Article 10 — Cession et sous-location</h2>
<p>
    Le Preneur ne pourra céder son droit au bail ni sous-louer tout ou partie des locaux sans l'accord écrit du Bailleur,
    sauf cession à l'acquéreur de son fonds de commerce dans les conditions prévues par la loi.
</p>

<h2>Article 11 — Clause résolutoire</h2>
<p>
    À défaut de paiement d'un seul terme de loyer à son échéance, ou d'inexécution d'une seule des conditions du bail,
    et un mois après une mise en demeure restée infructueuse, le présent bail sera résilié de plein droit si bon semble au Bailleur.
</p>

<h2>Article 12 — État des lieux</h2>
<p>
    Un état des lieux contradictoire sera établi lors de la remise des clés et lors de la restitution des locaux.
</p>

@if(filled($v['special_conditions'] ?? null))
<h2>Article 13 — Conditions particulières</h2>
<p>{{ $v['special_conditions'] }}</p>
@endif

<p class="contract-closing">
    Fait à {{ $show('city_signed') }}, le {{ $date('date_signed') }}, en deux exemplaires originaux,
    chacune des parties reconnaissant avoir reçu le sien.
</p>
