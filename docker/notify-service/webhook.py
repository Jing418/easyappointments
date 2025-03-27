from flask import Blueprint, request, jsonify
from aws_utils import send_sns_notification
from api_utils import get_customer_info

bp = Blueprint('webhook', __name__)

@bp.route('/webhook', methods=['POST'])
def handle_webhook():
    data = request.get_json()
    print(f"[Webhook] Received data: {data}", flush=True)

    try:
        action = data["action"]
        appointment = data["payload"]

        appointment_id = appointment["id"]
        start_time = appointment["start_datetime"]
        end_time = appointment["end_datetime"]
        status = appointment["status"]
        customer_id = appointment["id_users_customer"]

        customer = get_customer_info(customer_id)
        if not customer or "phone" not in customer:
            raise ValueError("Failed to retrieve phone number")

        phone = customer["phone"]

        message = (
            f"📢 Appointment {action}\n"
            f"Status: {status}\n"
            f"From: {start_time} to {end_time}\n"
            f"Phone: {phone}"
        )

        send_sns_notification(message)
        return jsonify({"status": "ok"}), 200

    except KeyError as e:
        print(f"[Webhook] Missing field: {e}", flush=True)
        return jsonify({"error": f"Missing field: {e}"}), 400
    except Exception as e:
        print(f"[Webhook] Error: {e}", flush=True)
        return jsonify({"error": str(e)}), 500
