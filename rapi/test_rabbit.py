import json
import pika
import urllib2

class MsgTemp:
    def __init__(self, temp):
        self.temp = temp
        self.type = "fhem.temp.read"
        self.source = "fhem.reader"
        self.device = "script"

HOST = 'sldev'
CRED = pika.PlainCredentials('admin', 'admin')
connection = pika.BlockingConnection(pika.ConnectionParameters(
    host=HOST, credentials=CRED))

channel = connection.channel()
channel.exchange_declare(exchange='solongo', type='fanout')

response = urllib2.urlopen('http://officepi.solongo.office:8083/fhem?cmd=jsonlist2%20netatmo_office&XHR=1')
data = json.load(response)
temp = data['Results'][0]['Readings']['temperature']['Value']

tempdata = MsgTemp(temp)
message = json.dumps(tempdata, default=lambda o: o.__dict__)

print message

channel.basic_publish(exchange='solongo', routing_key='', body=message)
connection.close()
