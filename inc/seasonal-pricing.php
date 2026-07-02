<?php
/**
 * Amarilla Tenerife — sezónní ceny a jednoduchá dostupnost vozidel
 *
 * Doplňuje CPT `vehicle` o:
 *   - opakující se pole „cenová období" (od–do, cena/den, štítek)
 *   - jednoduché blackout-dny (textarea: jeden den nebo rozsah na řádek)
 *
 * Datový model — uloženo jako JSON v meta `_vehicle_pricing_periods`:
 *   [
 *     { "start": "2026-06-01", "end": "2026-09-30", "price": "45", "label": "Vysoká sezóna" },
 *     { "start": "2026-12-15", "end": "2027-01-10", "price": "55", "label": "Vánoce / Nový rok" }
 *   ]
 *
 * Blackouty — `_vehicle_blackouts`, textarea, jeden záznam na řádek:
 *   2026-08-01
 *   2026-08-10 — 2026-08-14
 *
 * Pokud žádné období neodpovídá zadanému datu, použije se základní cena
 * z meta `_vehicle_price` (tj. backward compatible se starší verzí šablony).
 *
 * @package Amarilla
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * 1) Meta box pro sezónní ceny + blackouty
 * ============================================================ */
function amarilla_add_seasonal_pricing_meta_box() {
	add_meta_box(
		'amarilla_vehicle_seasonal',
		__( 'Sezónní ceny a dostupnost', 'amarilla' ),
		'amarilla_render_seasonal_pricing_meta_box',
		'vehicle',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'amarilla_add_seasonal_pricing_meta_box' );

function amarilla_render_seasonal_pricing_meta_box( $post ) {
	wp_nonce_field( 'amarilla_save_seasonal_pricing', 'amarilla_seasonal_nonce' );

	$periods_raw = get_post_meta( $post->ID, '_vehicle_pricing_periods', true );
	$periods     = $periods_raw ? json_decode( $periods_raw, true ) : array();
	if ( ! is_array( $periods ) ) {
		$periods = array();
	}
	$blackouts = get_post_meta( $post->ID, '_vehicle_blackouts', true );
	?>
	<style>
		.amarilla-periods { border-collapse: collapse; width: 100%; margin-bottom: 12px; }
		.amarilla-periods th, .amarilla-periods td { padding: 8px 6px; border-bottom: 1px solid #e5e5e5; text-align: left; vertical-align: middle; }
		.amarilla-periods th { background: #f6f7f7; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; }
		.amarilla-periods input[type=date], .amarilla-periods input[type=text] { width: 100%; box-sizing: border-box; }
		.amarilla-periods .amarilla-period-price { width: 100px; }
		.amarilla-periods .amarilla-period-remove { color: #b32d2e; cursor: pointer; background: none; border: 0; font-size: 18px; line-height: 1; }
		.amarilla-help { color: #757575; font-size: 12px; margin-top: 4px; }
	</style>

	<p class="amarilla-help">
		<?php esc_html_e( 'Definujte období se zvláštní cenou (např. vysoká sezóna, Vánoce). Pokud se datum poptávky do žádného období nevejde, použije se základní cena z bloku „Specifikace vozidla".', 'amarilla' ); ?>
	</p>

	<table class="amarilla-periods" id="amarilla-periods-table">
		<thead>
			<tr>
				<th style="width: 16%;"><?php esc_html_e( 'Od', 'amarilla' ); ?></th>
				<th style="width: 16%;"><?php esc_html_e( 'Do (včetně)', 'amarilla' ); ?></th>
				<th style="width: 18%;"><?php esc_html_e( 'Cena za den', 'amarilla' ); ?></th>
				<th><?php esc_html_e( 'Štítek (pro interní přehled)', 'amarilla' ); ?></th>
				<th style="width: 40px;"></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( $periods ) : foreach ( $periods as $i => $p ) :
				$start = isset( $p['start'] ) ? $p['start'] : '';
				$end   = isset( $p['end'] )   ? $p['end']   : '';
				$price = isset( $p['price'] ) ? $p['price'] : '';
				$label = isset( $p['label'] ) ? $p['label'] : '';
				?>
				<tr>
					<td><input type="date" name="amarilla_period_start[]" value="<?php echo esc_attr( $start ); ?>"></td>
					<td><input type="date" name="amarilla_period_end[]" value="<?php echo esc_attr( $end ); ?>"></td>
					<td><input type="text" class="amarilla-period-price" name="amarilla_period_price[]" value="<?php echo esc_attr( $price ); ?>" placeholder="45 €"></td>
					<td><input type="text" name="amarilla_period_label[]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'např. Vysoká sezóna', 'amarilla' ); ?>"></td>
					<td><button type="button" class="amarilla-period-remove" aria-label="<?php esc_attr_e( 'Odstranit období', 'amarilla' ); ?>">×</button></td>
				</tr>
			<?php endforeach; else : ?>
				<tr>
					<td><input type="date" name="amarilla_period_start[]" value=""></td>
					<td><input type="date" name="amarilla_period_end[]" value=""></td>
					<td><input type="text" class="amarilla-period-price" name="amarilla_period_price[]" value="" placeholder="45 €"></td>
					<td><input type="text" name="amarilla_period_label[]" value="" placeholder="<?php esc_attr_e( 'např. Vysoká sezóna', 'amarilla' ); ?>"></td>
					<td><button type="button" class="amarilla-period-remove" aria-label="<?php esc_attr_e( 'Odstranit období', 'amarilla' ); ?>">×</button></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<p>
		<button type="button" class="button" id="amarilla-add-period">+ <?php esc_html_e( 'Přidat období', 'amarilla' ); ?></button>
	</p>

	<hr style="margin: 20px 0;">

	<p>
		<label for="amarilla_blackouts" style="font-weight: 600; display: block; margin-bottom: 6px;">
			<?php esc_html_e( 'Blackout dny (nedostupnost)', 'amarilla' ); ?>
		</label>
		<textarea id="amarilla_blackouts" name="amarilla_blackouts" rows="3" style="width:100%;font-family:monospace;" placeholder="2026-08-15&#10;2026-09-01 — 2026-09-05"><?php echo esc_textarea( $blackouts ); ?></textarea>
		<span class="amarilla-help">
			<?php esc_html_e( 'Jeden řádek na záznam. Formát: jednotlivé datum (YYYY-MM-DD) nebo rozsah (YYYY-MM-DD — YYYY-MM-DD). Slouží pouze jako poznámka pro vás — formulář na ně neupozorňuje automaticky, ale objeví se v e-mailu o poptávce, pokud datum spadá do blackoutu.', 'amarilla' ); ?>
		</span>
	</p>

	<script>
	(function () {
		var addBtn = document.getElementById('amarilla-add-period');
		var tbody  = document.querySelector('#amarilla-periods-table tbody');
		if (!addBtn || !tbody) return;

		function bindRemove(row) {
			var btn = row.querySelector('.amarilla-period-remove');
			if (btn) btn.addEventListener('click', function () {
				if (tbody.querySelectorAll('tr').length > 1) {
					row.remove();
				} else {
					row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
				}
			});
		}

		Array.prototype.forEach.call(tbody.querySelectorAll('tr'), bindRemove);

		addBtn.addEventListener('click', function () {
			var first = tbody.querySelector('tr');
			var clone = first.cloneNode(true);
			clone.querySelectorAll('input').forEach(function (i) { i.value = ''; });
			tbody.appendChild(clone);
			bindRemove(clone);
		});
	})();
	</script>
	<?php
}

/* ============================================================
 * 2) Uložení
 * ============================================================ */
function amarilla_save_seasonal_pricing( $post_id ) {
	if ( ! isset( $_POST['amarilla_seasonal_nonce'] ) || ! wp_verify_nonce( $_POST['amarilla_seasonal_nonce'], 'amarilla_save_seasonal_pricing' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$starts = isset( $_POST['amarilla_period_start'] ) ? (array) wp_unslash( $_POST['amarilla_period_start'] ) : array();
	$ends   = isset( $_POST['amarilla_period_end'] )   ? (array) wp_unslash( $_POST['amarilla_period_end'] )   : array();
	$prices = isset( $_POST['amarilla_period_price'] ) ? (array) wp_unslash( $_POST['amarilla_period_price'] ) : array();
	$labels = isset( $_POST['amarilla_period_label'] ) ? (array) wp_unslash( $_POST['amarilla_period_label'] ) : array();

	$periods = array();
	$count   = max( count( $starts ), count( $ends ), count( $prices ) );
	for ( $i = 0; $i < $count; $i++ ) {
		$start = isset( $starts[ $i ] ) ? sanitize_text_field( $starts[ $i ] ) : '';
		$end   = isset( $ends[ $i ] )   ? sanitize_text_field( $ends[ $i ] )   : '';
		$price = isset( $prices[ $i ] ) ? sanitize_text_field( $prices[ $i ] ) : '';
		$label = isset( $labels[ $i ] ) ? sanitize_text_field( $labels[ $i ] ) : '';

		// Přeskočit prázdné řádky.
		if ( ! $start && ! $end && ! $price ) {
			continue;
		}
		// Validní datum?
		if ( $start && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) ) {
			continue;
		}
		if ( $end && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end ) ) {
			continue;
		}
		// Pokud chybí end, ber jako jednodenní (start = end).
		if ( $start && ! $end ) {
			$end = $start;
		}
		if ( $end && ! $start ) {
			$start = $end;
		}
		// Pořadí: start ≤ end (jinak je prohodíme).
		if ( strtotime( $start ) > strtotime( $end ) ) {
			$tmp = $start; $start = $end; $end = $tmp;
		}

		$periods[] = array(
			'start' => $start,
			'end'   => $end,
			'price' => $price,
			'label' => $label,
		);
	}

	update_post_meta( $post_id, '_vehicle_pricing_periods', wp_json_encode( $periods, JSON_UNESCAPED_UNICODE ) );

	$blackouts = isset( $_POST['amarilla_blackouts'] ) ? sanitize_textarea_field( wp_unslash( $_POST['amarilla_blackouts'] ) ) : '';
	update_post_meta( $post_id, '_vehicle_blackouts', $blackouts );
}
add_action( 'save_post_vehicle', 'amarilla_save_seasonal_pricing' );

/* ============================================================
 * 3) Helpery — výpočet ceny
 * ============================================================ */

/**
 * Načte všechna definovaná období pro daný vůz.
 *
 * @param int $post_id
 * @return array<int, array{start:string,end:string,price:string,label:string}>
 */
function amarilla_get_vehicle_pricing_periods( $post_id ) {
	$raw = get_post_meta( $post_id, '_vehicle_pricing_periods', true );
	if ( ! $raw ) {
		return array();
	}
	$decoded = json_decode( $raw, true );
	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Nejnižší cena za den (přes všechna období + základní cenu).
 * Vrací číselnou hodnotu (float), nebo null pokud nelze určit.
 *
 * Z ceny si vezme jen číslice + tečku/čárku (např. „45 €" → 45.0).
 *
 * @param int $post_id
 * @return float|null
 */
function amarilla_get_vehicle_lowest_price( $post_id ) {
	$prices = array();

	$base = get_post_meta( $post_id, '_vehicle_price', true );
	$num = amarilla_parse_price_to_float( $base );
	if ( $num !== null ) {
		$prices[] = $num;
	}

	foreach ( amarilla_get_vehicle_pricing_periods( $post_id ) as $p ) {
		$num = amarilla_parse_price_to_float( isset( $p['price'] ) ? $p['price'] : '' );
		if ( $num !== null ) {
			$prices[] = $num;
		}
	}

	return $prices ? min( $prices ) : null;
}

/**
 * Cena za den platná pro konkrétní datum (např. první den půjčky).
 *
 * @param int    $post_id
 * @param string $date   YYYY-MM-DD
 * @return string|null   Originální string z administrace (např. „45 €"), nebo null
 */
function amarilla_get_vehicle_price_for_date( $post_id, $date ) {
	if ( ! $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		return null;
	}
	$ts = strtotime( $date );
	if ( ! $ts ) {
		return null;
	}
	foreach ( amarilla_get_vehicle_pricing_periods( $post_id ) as $p ) {
		$start_ts = isset( $p['start'] ) ? strtotime( $p['start'] ) : false;
		$end_ts   = isset( $p['end'] )   ? strtotime( $p['end'] )   : false;
		if ( $start_ts && $end_ts && $ts >= $start_ts && $ts <= $end_ts && ! empty( $p['price'] ) ) {
			return $p['price'];
		}
	}
	$base = get_post_meta( $post_id, '_vehicle_price', true );
	return $base ?: null;
}

/**
 * Helper: převod stringu na float (snese „25", „25 €", „25,50", „from 25 EUR")
 *
 * @param string $price
 * @return float|null
 */
function amarilla_parse_price_to_float( $price ) {
	if ( $price === '' || $price === null ) {
		return null;
	}
	// Vyhodí vše kromě číslic, tečky a čárky.
	$clean = preg_replace( '/[^0-9.,]/', '', (string) $price );
	if ( $clean === '' ) {
		return null;
	}
	// Normalizace čárky → tečka, ale jen poslední čárka/tečka je desetinná.
	$clean = str_replace( ',', '.', $clean );
	// Pokud je víc teček, ponech jen poslední.
	$dots = substr_count( $clean, '.' );
	if ( $dots > 1 ) {
		$last = strrpos( $clean, '.' );
		$clean = str_replace( '.', '', substr( $clean, 0, $last ) ) . substr( $clean, $last );
	}
	return is_numeric( $clean ) ? (float) $clean : null;
}

/**
 * Kontrola, zda termín spadá (i částečně) do nějakého blackout intervalu.
 *
 * @param int    $post_id
 * @param string $pickup_date YYYY-MM-DD
 * @param string $return_date YYYY-MM-DD
 * @return bool
 */
function amarilla_vehicle_has_blackout_conflict( $post_id, $pickup_date, $return_date ) {
	$raw = get_post_meta( $post_id, '_vehicle_blackouts', true );
	if ( ! $raw ) {
		return false;
	}
	$pickup_ts = strtotime( $pickup_date );
	$return_ts = strtotime( $return_date );
	if ( ! $pickup_ts || ! $return_ts ) {
		return false;
	}
	$lines = preg_split( "/\r\n|\r|\n/", $raw );
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( ! $line ) {
			continue;
		}
		// Rozsah "YYYY-MM-DD — YYYY-MM-DD" / "YYYY-MM-DD - YYYY-MM-DD"
		if ( preg_match( '/^(\d{4}-\d{2}-\d{2})\s*[–—-]\s*(\d{4}-\d{2}-\d{2})$/u', $line, $m ) ) {
			$b_start = strtotime( $m[1] );
			$b_end   = strtotime( $m[2] );
		} elseif ( preg_match( '/^(\d{4}-\d{2}-\d{2})$/', $line, $m ) ) {
			$b_start = strtotime( $m[1] );
			$b_end   = $b_start;
		} else {
			continue;
		}
		// Konflikt = překryv intervalů
		if ( $pickup_ts <= $b_end && $return_ts >= $b_start ) {
			return true;
		}
	}
	return false;
}
