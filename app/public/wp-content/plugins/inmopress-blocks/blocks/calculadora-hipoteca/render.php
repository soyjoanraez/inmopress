<?php
/**
 * Calculadora Hipoteca Block Template.
 */

$id = 'calculadora-' . $block['id'];
$className = 'inmopress-calculadora';
if (!empty($block['className']))
    $className .= ' ' . $block['className'];

// Default Price
$price = get_field('precio_venta');
if (!$price)
    $price = 200000;
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    <div class="calc-wrapper"
        style="background: #f9fafb; padding: 30px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h3 style="margin-top: 0;">Calculadora de Hipoteca</h3>

        <div class="calc-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Precio del Inmueble (€)</label>
                <input type="number" id="calc-price-<?php echo $block['id']; ?>" value="<?php echo esc_attr($price); ?>"
                    class="calc-input">
            </div>

            <div class="form-group">
                <label>Entrada Inicial (€)</label>
                <input type="number" id="calc-deposit-<?php echo $block['id']; ?>" value="<?php echo $price * 0.20; ?>"
                    class="calc-input">
            </div>

            <div class="form-group">
                <label>Interés Anual (%)</label>
                <input type="number" id="calc-interest-<?php echo $block['id']; ?>" value="3.5" step="0.1"
                    class="calc-input">
            </div>

            <div class="form-group">
                <label>Plazo (Años)</label>
                <input type="number" id="calc-years-<?php echo $block['id']; ?>" value="30" class="calc-input">
            </div>
        </div>

        <div class="calc-result"
            style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="font-size: 1.1rem; color: #6b7280; margin-bottom: 5px;">Cuota Mensual Estimada</p>
            <div id="calc-output-<?php echo $block['id']; ?>"
                style="font-size: 2.5rem; font-weight: 800; color: #1e3a8a;">0 €</div>
        </div>

        <button id="calc-btn-<?php echo $block['id']; ?>" class="button button-primary"
            style="width: 100%; margin-top: 20px; padding: 12px; font-size: 1.1rem;">Calcular</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('calc-btn-<?php echo $block['id']; ?>');

            function calculateMortgage() {
                var price = parseFloat(document.getElementById('calc-price-<?php echo $block['id']; ?>').value) || 0;
                var deposit = parseFloat(document.getElementById('calc-deposit-<?php echo $block['id']; ?>').value) || 0;
                var interest = parseFloat(document.getElementById('calc-interest-<?php echo $block['id']; ?>').value) || 0;
                var years = parseFloat(document.getElementById('calc-years-<?php echo $block['id']; ?>').value) || 0;

                var principal = price - deposit;
                var monthlyInterest = interest / 100 / 12;
                var payments = years * 12;

                var x = Math.pow(1 + monthlyInterest, payments);
                var monthly = (principal * x * monthlyInterest) / (x - 1);

                if (!isFinite(monthly)) monthly = 0;

                document.getElementById('calc-output-<?php echo $block['id']; ?>').innerText = monthly.toFixed(2) + ' €';
            }

            btn.addEventListener('click', calculateMortgage);
            // Calculate on load
            calculateMortgage();
        });
    </script>
</div>