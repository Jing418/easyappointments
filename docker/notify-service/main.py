# main.py
from flask import Flask
from threading import Thread
import scheduler
import webhook

app = Flask(__name__)

app.register_blueprint(webhook.bp)

def start_scheduler():
    print("Starting scheduler...")
    scheduler.run()

if __name__ == '__main__':
    Thread(target=start_scheduler, daemon=True).start()

    print("Starting Flask Webhook receiver service...")
    app.run(host='0.0.0.0', port=5000, debug=True)
