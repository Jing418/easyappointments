import os
import requests

API_TOKEN = os.getenv("EA_API_TOKEN")

def get_customer_info(customer_id):
    try:
        url = f"http://nginx/index.php/api/v1/customers/{customer_id}"
        print(f"[API] GET {url}", flush=True)

        headers = {
            "Authorization": f"Bearer {API_TOKEN}"
        }

        response = requests.get(url, headers=headers, timeout=5)
        response.raise_for_status()

        return response.json()

    except Exception as e:
        print(f"[API] Failed to get customer info: {e}", flush=True)
        return None
