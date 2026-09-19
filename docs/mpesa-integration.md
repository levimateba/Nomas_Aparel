# M-PESA Daraja STK Push (Sandbox)

Safaricom Daraja **Lipa Na M-PESA Online (STK Push)** is integrated into the existing POS. Cash / card / other checkout paths are unchanged. M-PESA **does not** mark a sale paid until the Daraja **callback** confirms success (`ResultCode = 0`). An STK `ResponseCode: 0` only means the prompt was accepted.

## Required `.env` variables

Fill these **locally** (never commit real secrets; never paste Consumer Secret / Passkey into chat):

```env
MPESA_ENVIRONMENT=sandbox
MPESA_CONSUMER_KEY=YOUR_CONSUMER_KEY
MPESA_CONSUMER_SECRET=YOUR_CONSUMER_SECRET
MPESA_SHORTCODE=YOUR_SANDBOX_SHORTCODE
MPESA_PASSKEY=YOUR_SANDBOX_PASSKEY
MPESA_CALLBACK_URL=https://YOUR-PUBLIC-DOMAIN/api/mpesa/callback
MPESA_TRANSACTION_TYPE=CustomerPayBillOnline
```

Optional: `MPESA_HTTP_TIMEOUT=30`

Config file: `config/mpesa.php` (OAuth + STK URLs switch with `MPESA_ENVIRONMENT`).

## Sandbox setup

1. Create an app on [Daraja](https://developer.safaricom.co.ke/) (e.g. “Elgon POS M-PESA”).
2. Copy Consumer Key / Secret, Sandbox Shortcode, and Passkey into `.env`.
3. Set `MPESA_CALLBACK_URL` to a **public HTTPS** URL ending in `/api/mpesa/callback`.
4. Run:

```bash
php artisan migrate
php artisan config:clear
php artisan route:list | grep -i mpesa
```

## Callback URL (critical)

Daraja **cannot** call `localhost`, `127.0.0.1`, or private IPs.

For local development use a public HTTPS tunnel (ngrok, Cloudflare Tunnel, etc.) pointing at your app, then set:

`MPESA_CALLBACK_URL=https://<tunnel-host>/api/mpesa/callback`

Or deploy to a staging host with HTTPS.

## How to test STK Push (POS)

1. Log into admin POS, add items to the cart.
2. Select **M-Pesa**.
3. Enter phone (e.g. `0712345678` — use Daraja sandbox test numbers as documented by Safaricom).
4. Click **Send M-PESA Request**.
5. Confirm the prompt on the phone (sandbox simulator / test MSISDN).
6. POS polls `GET /api/mpesa/status/{checkoutRequestId}` until `success` / `failed` / `cancelled` / `timeout`.
7. On success, the sale is created, stock is deducted, and the receipt opens.

### OAuth smoke test (sandbox only)

While logged in as a cashier/admin:

`GET /api/mpesa/test-oauth`

Returns whether a token was obtained (**token value is never returned**). Disabled when `MPESA_ENVIRONMENT=production`.

## Routes

| Method | Path | Auth |
|--------|------|------|
| POST | `/api/mpesa/stk-push` | Admin (create_sale) |
| GET | `/api/mpesa/status/{checkoutRequestId}` | Admin (create_sale) |
| GET | `/api/mpesa/test-oauth` | Admin (sandbox only) |
| POST | `/api/mpesa/callback` | Public (Daraja) |

## Password generation

Do **not** copy a static `Password` from the Daraja simulator. Each request builds:

`Base64(BusinessShortCode + Passkey + Timestamp)` with `Timestamp = YmdHis`.

## Switching to production later

When Sandbox STK → callback → paid is verified:

1. Set `MPESA_ENVIRONMENT=production`.
2. Replace key, secret, shortcode/till, and passkey with production values.
3. Set `MPESA_CALLBACK_URL` to your production HTTPS callback.
4. Set `MPESA_TRANSACTION_TYPE` to `CustomerPayBillOnline` or `CustomerBuyGoodsOnline` to match the merchant product.
5. `php artisan config:clear` (or `config:cache` in production).

Do not enable production until Sandbox end-to-end works.

## Security

- Secrets stay in `.env` / server config only — never exposed to JavaScript.
- Logs never include Consumer Secret, Passkey, access tokens, or Authorization headers.
- `.env` is gitignored.
- Callback processing is idempotent (row lock); duplicate callbacks do not create duplicate sales.

## Troubleshooting

| Symptom | Check |
|---------|--------|
| “M-PESA is not configured” | Missing `.env` keys; `config:clear` |
| “callback must be public HTTPS” | `MPESA_CALLBACK_URL` scheme/host |
| STK accepted but sale unpaid | Callback not reaching app (tunnel/firewall); check `storage/logs` for “M-PESA callback” |
| OAuth failed | Consumer Key/Secret; sandbox vs production URLs |
| Invalid phone | Use `07…`, `01…`, `254…`, or `+254…` |
| Customer cancelled | ResultCode 1032 → status `cancelled`; retry Send |

## Automated tests

```bash
php artisan test --filter=DarajaServiceTest
php artisan test --filter=MpesaStkPushTest
```

HTTP calls to Daraja are mocked; tests never hit the live API.
