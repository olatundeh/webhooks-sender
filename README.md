PHP Webhook Sender
==================
PHP script to process a queue of webhooks, adhering to an exponential backoff strategy for failed deliveries.

Setup:
1. Clone the Repository: git clone https://github.com/olatundeh/webhooks-sender.git
2. Install Dependencies:
cd webhooks-sender__
composer install__
3. Create a .env file in the project root and add the following variables:__
WEBHOOK_QUEUE_FILE=webhooks.txt__
MAX_FAILED_ATTEMPTS=5__
MAX_RETRY_DELAY=60__
4. Create a webhooks.txt file in the same directory as the script.
5. Populate the file with webhook URLs from the Queue, one per line.

Running:
1. Open your terminal or command prompt
2. Execute the index.php script from the terminal using the command php index.php

Design Considerations:
Error Handling: The script handles failed requests and retries with exponential backoff.

Performance and Scalability:
The script is designed to be efficient and scalable, processing webhooks sequentially.
For large-scale operations, consider using a message queue system like RabbitMQ or Kafka.

Security:
Ensure the script is executed on a secure server.
Consider using HTTPS for webhook endpoints to protect sensitive data.