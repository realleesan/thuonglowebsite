# Thuong Lo Website - Environment Configuration

# Copy this file to .env and update with your actual credentials

# ============================================

# SEPAY PAYMENT GATEWAY

# ============================================

# Get your credentials from: https://my.sepay.vn

SEPAY_API_KEY=8SOEMMR4FD1B6WJ9QWONYLGUKJXMDTRH1ES7HWAZISTKNA02KNHL5Y7UK0RQQIPO

SEPAY_API_SECRET= spsk_live_nrpFAN6HK1gLeXekMJ8KdTAWTrq6RXhh

SEPAY_ACCOUNT_NUMBER=0914960029666

SEPAY_WEBHOOK_SECRET=

# ============================================

# PAYOS PAYOUT (DISBURSEMENT)

# ============================================

# Get your credentials from: https://my.payos.vn

PAYOS_CLIENT_ID=f9b2ed6d-6bf3-4089-8043-861b501c17f9

PAYOS_API_KEY=3830d21f-3d96-41b2-8681-f13f35b74da3

PAYOS_CHECKSUM_KEY=57010a911dee1a1aef5cdb5e0110ad77983b04824d6680d64d9d809247eb1ed0

PAYOS_PAYOUT_CHECKSUM_KEY=57010a911dee1a1aef5cdb5e0110ad77983b04824d6680d64d9d809247eb1ed0

PAYOS_WEBHOOK_SECRET=

# ============================================

# EMAIL CONFIGURATION (SMTP)

# ============================================

# For Gmail: Use App Password (not your regular password)

# Enable 2FA and generate app password at: https://myaccount.google.com/apppasswords

SMTP_HOST=smtp.gmail.com

SMTP_PORT=587

SMTP_USERNAME=baominhkpkp@gmail.com

SMTP_PASSWORD=gjvz qdrq pogq sheb

MAIL_FROM_EMAIL=noreply@thuonglo.com

MAIL_FROM_NAME=ThuongLo

# ============================================

# NOTES

# ============================================

# - Never commit .env file to git

# - Keep your credentials secure

# - Use different credentials for local and production

# - Test email sending in local before deploying
