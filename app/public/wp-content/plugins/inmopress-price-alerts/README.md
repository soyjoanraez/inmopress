# Inmopress Price Alerts

Automatic price drop alerts for properties. Detects a price drop on `impress_property`, finds interested clients, applies scoring + anti-spam rules, and sends an email using `impress_email_tpl` with trigger `price_drop` (fallback template included).

## Dependencies

- WordPress
- ACF Pro (already in project)
- Inmopress Core CPTs/fields

## What it does

1. Detects price changes on `acf/save_post` for `impress_property`.
2. If the new price is lower, triggers `inmopress_price_drop`.
3. Builds an interested list from favorites, visits, and matching.
4. Filters by score, opt-in, dedupe, and daily limits.
5. Sends email (immediate or scheduled) and logs it.

## Data model

### ACF fields (added by this plugin)

**Property (`impress_property`)**
- `precio_anterior` (number) - last known price
- `fecha_ultima_bajada` (date_time_picker)
- `porcentaje_ultima_bajada` (number)

**Client (`impress_client`)**
- `alertas_bajada_precio` (true/false) - opt-in (default true)
- `alertas_frecuencia` (select: inmediata/diaria/semanal)
- `alertas_ultima_fecha` (date_time_picker)
- `favoritos` (relationship to `impress_property`)

### Logs table

`{wp_prefix}inmopress_price_alerts`

Columns:
- `id` BIGINT PK
- `property_id`
- `client_id`
- `old_price`
- `new_price`
- `drop_pct`
- `sent_at`
- `channel`

## Matching and scoring

Base score (0-100) from:
- Purpose vs interest (venta/alquiler) must match
- City match from `zona_interes` vs property `impress_city`
- Budget fit: `presupuesto_max`
- Bedrooms, bathrooms, surface minima

Extra scores:
- Favorite: +100
- Visit within 30 days: +80 (90 days: +50, older: +30)
- Client temperature: HOT +20 / WARM +10

Minimum score to notify is configurable (default 50).

## Anti-spam / dedupe

- Per client + property cooldown (default 30 days)
- Daily limit per client (default 3)
- Uses the log table to dedupe

## Email template

Uses `impress_email_tpl` where:
- `email_trigger` = `price_drop`
- `email_status` = `active`

Fallback template: `templates/email-price-drop.php`

### Variables available

- `{{client_name}}`
- `{{property_title}}`
- `{{property_city}}`
- `{{property_url}}`
- `{{property_image}}`
- `{{property_description}}`
- `{{old_price}}`
- `{{new_price}}`
- `{{price_diff}}`
- `{{drop_pct}}`
- `{{agent_name}}`
- `{{agent_phone}}`
- `{{agent_email}}`
- `{{agency_name}}`
- `{{score}}`
- `{{unsubscribe_url}}`

## Hooks

- `do_action('inmopress_price_drop', $property_id, $old_price, $new_price, $context)`

## Filters

- `inmopress_price_alerts_min_drop_pct` (default 5)
- `inmopress_price_alerts_min_drop_amount` (default 0)
- `inmopress_price_alerts_score_threshold` (default 50)
- `inmopress_price_alerts_cooldown_days` (default 30)
- `inmopress_price_alerts_daily_limit` (default 3)
- `inmopress_price_alerts_matching_limit` (default 500)
- `inmopress_price_alerts_daily_hour` (default 9)
- `inmopress_price_alerts_daily_minute` (default 0)
- `inmopress_price_alerts_weekly_hour` (default 9)
- `inmopress_price_alerts_weekly_minute` (default 0)
- `inmopress_price_alerts_weekly_weekday` (default 1, Monday)
- `inmopress_price_alerts_unsubscribe_url` (default empty)

## File structure

```
inmopress-price-alerts/
├── inmopress-price-alerts.php
├── includes/
│   ├── class-acf-fields.php
│   ├── class-price-tracker.php
│   ├── class-alert-matcher.php
│   ├── class-alert-logger.php
│   └── class-alert-sender.php
└── templates/
    └── email-price-drop.php
```

## Notes

- Price is taken from `precio_venta` or `precio_alquiler` based on `proposito` (fallback to any available).
- Property must be `listing_status = active` and not `reservada`.
- Email is sent to client field `correo`.
- Scheduled alerts use WP-Cron with hook `inmopress_price_alerts_send`.
