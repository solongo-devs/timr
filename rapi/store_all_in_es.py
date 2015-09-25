import pika
import json
from elasticsearch import Elasticsearch

HOST = 'sldev'
CRED = pika.PlainCredentials('admin', 'admin')
connection = pika.BlockingConnection(pika.ConnectionParameters(
    host=HOST, credentials=CRED))

channel = connection.channel()
channel.exchange_declare(exchange='solongo', type='fanout')

result = channel.queue_declare(exclusive=True)
queue_name = result.method.queue

channel.queue_bind(exchange='solongo',
                   queue=queue_name)

print "-- running --"


def callback(ch, method, properties, body):
    print " Empfangen: %r" % (body,)
    es = Elasticsearch([{"host": "sldev", "port": 9200}])
    es.index(index="messages", doc_type="message", body=json.loads(body))

channel.basic_consume(callback, queue=queue_name, no_ack=True)
channel.start_consuming()
